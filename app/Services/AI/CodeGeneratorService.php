<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use App\Models\AiUsageLog;
use App\Services\AI\ComponentRegistry;
use App\Services\AI\DesignVariantService;
use App\Services\AI\KnowledgeBaseService;
use App\Services\AI\SeniorDevKnowledgeBase;
use App\Services\AI\WisdomEngine;
use App\Services\Credits\CreditPricingService;
use App\Services\Credits\IntelligenceGate;
use App\Services\Credits\RyaanCreditsService;

class CodeGeneratorService
{
    public function __construct(
        protected AIManager              $aiManager,
        protected BlueprintService       $blueprintService,
        protected MetadataCrudGenerator  $crudGenerator,
        protected AIRouter               $aiRouter,
        protected MultilingualNormalizer $normalizer,
        protected DesignVariantService   $designVariants,
        protected KnowledgeBaseService   $kb,
        protected SeniorDevKnowledgeBase $seniorKb,
        protected WisdomEngine           $wisdomEngine,
        protected ComponentRegistry      $componentRegistry,
        protected ?IntelligenceGate      $gate = null,
        protected ?RyaanCreditsService   $credits = null,
        protected ?CreditPricingService  $pricing = null,
    ) {}

    /**
     * Strip prompt-injection attempts from user input before sending to the AI.
     * Removes patterns that try to override the system prompt or impersonate system roles.
     */
    private function sanitizeUserPrompt(string $prompt): string
    {
        // Remove attempts to override system instructions
        $injectionPatterns = [
            '/ignore\s+(all\s+)?(previous|above|prior)\s+instructions?/i',
            '/you\s+are\s+now\s+(a\s+)?/i',
            '/disregard\s+(all\s+)?(previous|prior)\s+/i',
            '/forget\s+(everything|all)\s+(you\s+)?(were\s+)?told/i',
            '/\[SYSTEM\]/i',
            '/\[INST\]/i',
            '/<\|system\|>/i',
            '/###\s*system\s*prompt/i',
        ];
        foreach ($injectionPatterns as $pattern) {
            $prompt = preg_replace($pattern, '[removed]', $prompt);
        }
        // Cap at 20k chars — the controller validates max:20000 but double-check here
        return mb_substr(trim($prompt), 0, 20000);
    }

    private function chatWithFallback(array $messages, array $options, ?string $selectedProvider, User $user): array
    {
        $candidates = $this->aiManager->providerFallbackCandidates($selectedProvider, $user);
        $lastError = null;

        foreach ($candidates as $index => $candidate) {
            try {
                $candidateOptions = $this->optionsForCandidate($options, $candidate['provider'], $selectedProvider);
                $response = $candidate['driver']->chat($messages, $candidateOptions);
                $response['provider'] = $candidate['provider'];

                if ($index > 0) {
                    $response['fallback_used'] = true;
                    $response['fallback_provider'] = $candidate['provider'];
                }

                return $response;
            } catch (\Throwable $e) {
                $lastError = $e;

                if (!$this->shouldFallbackForAIError($e) || $index === array_key_last($candidates)) {
                    throw $e;
                }
            }
        }

        throw $lastError ?: new \RuntimeException('No configured AI provider is available.');
    }

    private function discoverWithFallback(string $prompt, ?string $selectedProvider, User $user): array
    {
        $candidates = $this->aiManager->providerFallbackCandidates($selectedProvider, $user);
        $lastError = null;

        foreach ($candidates as $index => $candidate) {
            try {
                return $this->blueprintService->discover($prompt, $candidate['driver']);
            } catch (\Throwable $e) {
                $lastError = $e;

                if (!$this->shouldFallbackForAIError($e) || $index === array_key_last($candidates)) {
                    throw $e;
                }
            }
        }

        throw $lastError ?: new \RuntimeException('No configured AI provider is available.');
    }

    private function streamWithFallback(
        array $messages,
        callable $callback,
        array $options,
        ?string $selectedProvider,
        User $user,
        ?callable $onFallback = null
    ): array {
        $candidates = $this->aiManager->providerFallbackCandidates($selectedProvider, $user);
        $lastError = null;

        foreach ($candidates as $index => $candidate) {
            $emitted = false;

            try {
                $candidateOptions = $this->optionsForCandidate($options, $candidate['provider'], $selectedProvider);
                $candidate['driver']->stream($messages, function (string $chunk) use ($callback, &$emitted) {
                    $emitted = true;
                    $callback($chunk);
                }, $candidateOptions);

                return $candidate;
            } catch (\Throwable $e) {
                $lastError = $e;

                if ($emitted || !$this->shouldFallbackForAIError($e) || $index === array_key_last($candidates)) {
                    throw $e;
                }

                if ($onFallback) {
                    $onFallback($candidate, $e);
                }
            }
        }

        throw $lastError ?: new \RuntimeException('No configured AI provider is available.');
    }

    private function optionsForCandidate(array $options, string $candidateProvider, ?string $selectedProvider): array
    {
        if (!isset($options['model'])) {
            return $options;
        }

        if ($selectedProvider && $candidateProvider === $selectedProvider) {
            return $options;
        }

        $models = array_keys(config("ai.providers.{$candidateProvider}.models", []));
        if (in_array($options['model'], $models, true)) {
            return $options;
        }

        unset($options['model']);
        return $options;
    }

    private function shouldFallbackForAIError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        foreach ([
            // Rate / quota / billing
            '429', 'quota', 'billing', 'insufficient_quota', 'rate limit', 'rate_limit',
            'limit reached', 'limit exceeded', 'exceeded', 'too many requests',
            'resource_exhausted', 'balance', 'payment', 'capacity', 'overloaded',
            // Content-filter: all Gemini retry strategies exhausted — try next provider
            'content policy blocked', 'content filtering',
        ] as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function generate(
        string $prompt,
        Project $project,
        User $user,
        AIConversation $conversation,
        ?string $provider = null,
        ?string $model = null,
        ?string $visiblePrompt = null
    ): array {
        // $rawPrompt is stored in conversation (user sees this — no [Normalized intent] clutter).
        // $prompt is the AI-facing version with optional English intent suffix.
        $aiPromptRaw = $this->sanitizeUserPrompt($prompt);
        $rawPrompt   = $this->sanitizeUserPrompt($visiblePrompt ?? $prompt);
        $prompt      = $this->normalizer->enrichPrompt($aiPromptRaw);
        $isBusinessProblem = $this->isBusinessProblemRequest($prompt);
        $isTiny     = !$isBusinessProblem && $this->isTinyEdit($prompt);
        $isTemplateCustomization = str_contains(strtolower($prompt), 'ryaan template customization context');

        // AIRouter: classify task and resolve model tier + token budget
        $route    = $this->aiRouter->route($prompt);
        $taskType = $route['task_type'];

        // Component Registry check: match standard UI components before touching AI
        $componentKey = $isTemplateCustomization ? null : $this->componentRegistry->matchComponent($prompt);
        if ($componentKey) {
            $component = $this->componentRegistry->get($componentKey);
            $saved     = $this->componentRegistry->tokensSaved($componentKey);
            $this->autoTitleConversation($conversation, $rawPrompt);
            $conversation->addMessage('user', $rawPrompt);
            $templateCode = $component['template'] ?? '';
            $message = "⚡ **Component Registry** — `{$component['label']}` inserted from registry ({$saved} tokens saved).\n\n```blade\n{$templateCode}\n```\n\n**Customise** field names, labels, and routes to match your data model.";
            $message = "**{$component['label']}** is ready.\n\n```blade\n{$templateCode}\n```\n\nCustomize field names, labels, and routes to match your data model.";
            $conversation->addMessage('assistant', $message, ['tokens_used' => 0, 'model' => 'component_registry']);
            return [
                'message'         => $message,
                'files_generated' => [],
                'tokens_used'     => 0,
                'tokens_saved'    => $saved,
                'model'           => 'component_registry',
                'task_type'       => $taskType,
                'component_key'   => $componentKey,
            ];
        }

        // Wisdom routing: check if this task can bypass AI entirely
        $wisdomRoute = $isTemplateCustomization
            ? ['use_ai' => true, 'routing_step' => 7, 'reason' => 'Template customization requires AI.']
            : $this->wisdomEngine->getAiRoutingDecision($taskType);
        if (!$wisdomRoute['use_ai']) {
            $this->autoTitleConversation($conversation, $rawPrompt);
            $conversation->addMessage('user', $rawPrompt);
            $ruleMsg = "⚡ **Routed to {$wisdomRoute['use_instead']}** — {$wisdomRoute['savings']} " .
                       "(AI call skipped — this task type has a deterministic solution)";
            $ruleMsg = 'This request is ready to handle with RyaanCMS built-in tools. Continue with the next instruction or use the matching builder action.';
            $conversation->addMessage('assistant', $ruleMsg, ['tokens_used' => 0, 'model' => 'rule_engine']);
            return [
                'message'         => $ruleMsg,
                'files_generated' => [],
                'tokens_used'     => 0,
                'tokens_saved'    => 8000,
                'model'           => 'rule_engine',
                'task_type'       => $taskType,
                'routed_to'       => $wisdomRoute['use_instead'],
            ];
        }

        // Cost opt: pick the lightest system prompt that fits the task
        $systemPrompt = $isTiny
            ? $this->buildTinySystemPrompt()        // ~150 tokens vs ~8 000
            : $this->buildSystemPrompt($project);

        $historyTurns = $isTiny ? 1 : min(2, $route['history_turns']);
        $history      = $this->trimHistory($conversation->getMessagesForAPI(), $historyTurns);

        // Route large "build a complete system" prompts through Blueprint-Driven generation
        // Blueprint runs invisibly: discover → auto-CRUD → AI for complex parts only
        $isMultiPhase = $this->isFullSystemRequest($prompt);
        if ($isMultiPhase) {
            $this->autoTitleConversation($conversation, $rawPrompt);
            $conversation->addMessage('user', $rawPrompt);
            return $this->generateBlueprintDriven(
                $prompt, $project, $user, $conversation, $systemPrompt, $history, $provider, $model
            );
        }

        // ── Single-phase generation ──────────────────────────────────────────

        // COST OPT: inject fewer/smaller files for tiny edits
        $enrichedPrompt = $isTiny
            ? $this->buildPromptWithFileContext($prompt, $project, maxFiles: 2, maxChars: 4000)
            : $this->buildPromptWithFileContext($prompt, $project);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $enrichedPrompt]]
        );

        // COST OPT: response cache — skip AI call entirely for identical requests
        $cacheKey = $this->buildResponseCacheKey($enrichedPrompt, $project, $provider ?? 'default', $model ?? 'default');
        $cached   = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) {
            $this->autoTitleConversation($conversation, $rawPrompt);
            $conversation->addMessage('user', $rawPrompt);
            $parsed = $this->parseResponse($cached, $project);
            $conversation->addMessage('assistant', $cached, ['tokens_used' => 0, 'model' => 'cache', 'cache_hit' => true]);
            return [
                'message'         => '[Cached] ' . $parsed['message'],
                'files_generated' => $parsed['files'],
                'tokens_used'     => 0,
                'tokens_saved'    => $isTiny ? 2000 : 9000,
                'model'           => 'cache',
                'cache_hit'       => true,
                'task_type'       => $taskType,
            ];
        }

        $this->autoTitleConversation($conversation, $rawPrompt);
        $conversation->addMessage('user', $rawPrompt);

        // Use AIRouter token budget — smarter than one-size-fits-all adaptive budget
        $tokenBudget  = $isTiny ? 6000 : $route['max_tokens'];
        $startTime    = microtime(true);
        $response     = $this->chatWithFallback(
            $messages,
            array_merge($model ? ['model' => $model] : [], ['max_tokens' => $tokenBudget]),
            $provider,
            $user
        );
        $rawContent   = $response['content'];
        $tokensUsed   = $response['tokens_used'];
        $responseTime = (int) ((microtime(true) - $startTime) * 1000);

        // Store in response cache (30 min TTL)
        $cacheTtl = (int) config('ai.cost_reduction.response_cache_ttl', 1800);
        \Illuminate\Support\Facades\Cache::put($cacheKey, $rawContent, $cacheTtl);

        $parsed         = $this->parseResponse($rawContent, $project);
        $generatedFiles = $parsed['files'];
        $displayMessage = $parsed['message'];

        // Warn when AI returned no files but the prompt clearly implies a code change
        if (empty($generatedFiles) && $this->impliesCodeChange($prompt)) {
            $displayMessage .= "\n\n⚠️ **No changes were made.** Try describing what you want more specifically — for example: \"change the login page background to dark blue\" or \"add a logout button to the header\".";
        }

        $conversation->addMessage('assistant', $rawContent, [
            'tokens_used'      => $tokensUsed,
            'model'            => $response['model'],
            'response_time_ms' => $responseTime,
            'generated_files'  => array_column($generatedFiles, 'path'),
        ]);

        $project->increment('ai_tokens_used', $tokensUsed);

        // Record this decision in the Intelligence Ledger for future routing improvements
        $this->wisdomEngine->recordDecision([
            'project_id' => $project->id,
            'user_id'    => $project->user_id,
            'domain'     => $project->type ?? 'general',
            'lesson'     => "Generated {$taskType} using AI: " . mb_substr($rawPrompt, 0, 200),
            'outcome'    => count($generatedFiles) . ' files generated in ' . $responseTime . 'ms',
            'confidence' => count($generatedFiles) > 0 ? 0.85 : 0.5,
            'tags'       => [$taskType, $response['model'] ?? 'ai', $project->type ?? 'general'],
            'ai_cost_estimate' => round($tokensUsed * 0.000003, 6),
        ]);

        return [
            'message'         => $displayMessage,
            'files_generated' => $generatedFiles,
            'tokens_used'     => $tokensUsed,
            'model'           => $response['model'],
            'task_type'       => $taskType,
        ];
    }

    /**
     * Deterministic cache key: provider + model + project file fingerprint + prompt.
     * Changing any project file invalidates the cache automatically.
     */
    private function buildResponseCacheKey(string $prompt, Project $project, string $provider, string $model): string
    {
        $fileFingerprint = $project->files()
            ->where('type', 'file')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->pluck('updated_at')
            ->implode(',');

        $userId = auth()->id() ?? 0;
        return 'ai_resp:' . md5("{$userId}|{$provider}|{$model}|{$project->id}|{$prompt}|{$fileFingerprint}");
    }

    /**
     * Ultra-minimal system prompt for tiny edits (~150 tokens vs ~8 000 for full prompt).
     * Saves ~98% on system prompt tokens for simple fix/change/rename requests.
     */
    private function buildTinySystemPrompt(): string
    {
        return <<<'TINY'
You are RyaanCMS AI Builder. Apply ONLY the requested change, nothing else.

RULES:
• Return ONLY changed files. Every file COMPLETE — no "// rest stays same", no truncation.
• Preserve ALL existing code. Change only exactly what was requested.
• Security: @csrf on every form. {{ }} for all Blade output. Auth middleware on protected routes.
• JSON output ONLY — no prose before or after:

```json
{"files":[{"path":"path/to/file.ext","content":"COMPLETE FILE CONTENT"}],"summary":"What changed and why.","next_steps":[]}
```
TINY;
    }

    /**
     * 3-tier output token cap:
     *   tiny edit  → 2 500   patch / small change, runs on Haiku
     *   feature    → 6 000   add module/page, runs on Sonnet
     *   full build → 10 000  complete system per phase, runs on Sonnet
     */
    private function adaptiveMaxTokens(string $prompt, bool $isMultiPhase): int
    {
        if ($isMultiPhase)            return 10000;
        if ($this->isTinyEdit($prompt)) return 2500;
        return 6000;
    }

    /**
     * Return the cheapest model that can handle the given task for a provider.
     * Used to auto-downgrade tiny edits without requiring user configuration.
     */
    private function cheapModelFor(?string $provider): ?string
    {
        return match ($provider ?? config('ai.default', 'claude')) {
            'claude'  => 'claude-haiku-4-5-20251001',
            'openai'  => 'gpt-4.1-mini',
            'gemini'  => 'gemini-2.0-flash',
            default   => null,
        };
    }

    /**
     * Returns true for any targeted edit/change request that does NOT require building a
     * complete new system. These get the 150-token tiny system prompt + 1 history turn
     * + 6k-token output cap instead of the full 10k-token system prompt.
     *
     * Rule: anything ≤ 500 chars that is not a "build complete system" request is tiny.
     * Full-system requests are caught by isFullSystemRequest() before this is checked.
     */
    private function isTinyEdit(string $prompt): bool
    {
        if (strlen($prompt) > 500) return false;

        $lower = strtolower($prompt);

        // Explicit "build a complete" phrases → NOT tiny (handled by full pipeline)
        $bigBuildPhrases = ['build a complete', 'create a complete', 'build complete', 'complete system',
                            'full system', 'full application', 'full app', 'management system',
                            'saas platform', 'e-commerce', 'from scratch'];
        foreach ($bigBuildPhrases as $phrase) {
            if (str_contains($lower, $phrase)) return false;
        }

        // Everything else under 500 chars is a targeted edit
        // (add feature, fix bug, change style, click handler, redirect, rename, etc.)
        return true;
    }

    private function isBusinessProblemRequest(string $prompt): bool
    {
        $lower = strtolower($prompt);
        $problemSignals = [
            'low conversion', 'lead leakage', 'follow-up', 'follow up', 'sales pipeline chaos',
            'quote delay', 'low roas', 'high cac', 'poor retention', 'abandoned cart',
            'low engagement', 'employee absence', 'attendance problem', 'high turnover',
            'payroll error', 'recruitment delay', 'cash flow', 'late payment',
            'revenue leakage', 'expense tracking', 'inventory mismatch', 'stock mismatch',
            'order delay', 'stockout', 'process bottleneck', 'cod verification',
            'high return', 'return rate', 'fake order', 'repeat purchase', 'user churn',
            'low activation', 'feature adoption', 'problem', 'mismatch', 'not following',
            'revenue dropped', 'revenue down', 'sales down', 'profit down',
            'company growing slowly', 'growth slow', 'business not growing',
            'what should i focus', 'focus this month', 'priority this month',
            'increase price', 'optimize pricing', 'simulate', 'digital twin',
            'ceo assistant', 'reduce churn', 'reduce inventory holding',
        ];

        foreach ($problemSignals as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when the prompt strongly implies the user wants code changed/created.
     * Used to warn when the AI returns 0 files — likely a context-miss.
     */
    private function impliesCodeChange(string $prompt): bool
    {
        $lower = strtolower($prompt);
        $changeVerbs = [
            // English
            'change', 'fix', 'update', 'modify', 'edit', 'redirect', 'add', 'remove',
            'delete', 'rename', 'replace', 'move', 'convert', 'make', 'set', 'create',
            'implement', 'should', 'need to', 'want to', 'show', 'hide',
            // Normalized Bangla (already normalized by enrichPrompt, but catch originals too)
            'korbay', 'korbe', 'korbo', 'dao', 'hatao', 'dekhao', 'banao', 'pathao',
            // Normalized other languages
            'créer', 'crear', 'criar', 'erstellen', 'oluştur', 'buat',
            'redirigir', 'rediriger', 'redirecionar', 'yönlendir',
        ];
        foreach ($changeVerbs as $v) {
            if (str_contains($lower, $v)) return true;
        }
        return false;
    }

    /**
     * Keep only the last N conversation turns to avoid sending stale history tokens.
     * Each turn = 1 user message + 1 assistant message (2 array entries).
     */
    private function trimHistory(array $history, int $maxTurns = 3): array
    {
        $maxMessages = $maxTurns * 2;
        if (count($history) <= $maxMessages) return $history;
        return array_slice($history, -$maxMessages);
    }

    /**
     * Validate and sanitize a file path generated by AI.
     * Returns null if the path is dangerous or not allowed.
     */
    private function sanitizeFilePath(string $path): ?string
    {
        $path = ltrim(trim($path), '/\\');
        $path = str_replace('\\', '/', $path);

        // Block directory traversal
        if (str_contains($path, '..')) return null;

        // Block absolute paths
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path)) return null;

        // Block sensitive files
        $blockedNames = ['.env', '.env.local', '.env.production', '.env.staging',
                         '.htaccess', '.htpasswd', 'wp-config.php'];
        if (in_array(strtolower(basename($path)), $blockedNames)) return null;

        // ── RyaanCMS core protection ─────────────────────────────────────────
        // The AI builder MUST NOT overwrite any part of the RyaanCMS platform.
        // Block entire directory trees first, then specific files.
        if ($this->isCoreRyaanCMSPath($path)) return null;

        // Only allow safe extensions (empty extension = OK for dotfiles like .gitignore)
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext !== '') {
            $allowed = ['php', 'blade', 'js', 'ts', 'jsx', 'tsx', 'css', 'scss', 'sass',
                        'html', 'htm', 'json', 'xml', 'yaml', 'yml', 'sql', 'txt', 'md',
                        'svg', 'vue', 'py', 'rb', 'go', 'rs', 'sh', 'gitignore', 'env',
                        'lock', 'stub', 'csv', 'neon', 'toml'];
            if (!in_array($ext, $allowed)) return null;
        }

        return $path;
    }

    /**
     * Returns true when the given path belongs to the RyaanCMS platform itself
     * and must never be written by the AI builder.
     *
     * Two-tier check:
     *  1. Prefix directories — block the entire subtree.
     *  2. Exact file paths  — block individual platform files.
     */
    private function isCoreRyaanCMSPath(string $path): bool
    {
        // ── 1. Blocked directory prefixes (whole tree) ────────────────────────
        $blockedPrefixes = [
            // AI engine + module system — never touchable
            'app/Services/AI/',
            'app/Services/Module/',
            // Framework plumbing
            'app/Providers/',
            'app/Policies/',
            'app/Http/Middleware/',
            // Core config (ai.php, ryaan.php, domain_packs.php, …)
            'config/',
            // Laravel bootstrap
            'bootstrap/',
            // Core platform views
            'resources/views/layouts/',
            'resources/views/builder/',
            'resources/views/marketplace/',
            'resources/views/dashboard/',
            'resources/views/projects/',
            'resources/views/settings/',
            'resources/views/menus/',
            'resources/views/pages/',
            'resources/views/install/',
        ];

        foreach ($blockedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) return true;
        }

        // ── 2. Exact blocked files ────────────────────────────────────────────
        $blockedExact = [
            // Core routes
            'routes/web.php',
            'routes/api.php',
            'routes/console.php',
            // Platform entry-points
            'artisan',
            'public/index.php',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            // Platform controllers
            'app/Http/Controllers/AIBuilderController.php',
            'app/Http/Controllers/MarketplaceController.php',
            'app/Http/Controllers/DashboardController.php',
            'app/Http/Controllers/MenuController.php',
            'app/Http/Controllers/ProjectController.php',
            'app/Http/Controllers/SettingsController.php',
            'app/Http/Controllers/InstallController.php',
            'app/Http/Controllers/Controller.php',
            // Platform models
            'app/Models/Project.php',
            'app/Models/ProjectFile.php',
            'app/Models/ProjectModule.php',
            'app/Models/AIMessage.php',
            'app/Models/AIConversation.php',
            'app/Models/AIProvider.php',
            'app/Models/Setting.php',
            'app/Models/Deployment.php',
            'app/Models/Menu.php',
            'app/Models/MenuItem.php',
            'app/Models/MarketplaceItem.php',
            'app/Models/MarketplaceInstallation.php',
            'app/Models/MarketplaceReview.php',
            'app/Models/User.php',
        ];

        return in_array($path, $blockedExact);
    }

    /**
     * Scan PHP/Blade content for dangerous function calls that should never
     * appear in AI-generated code (arbitrary code/shell execution).
     * Returns true if dangerous patterns found.
     */
    private function scanForDangerousContent(string $content, string $ext): bool
    {
        if (!in_array($ext, ['php', 'blade'])) return false;

        $patterns = ['eval(', 'exec(', 'system(', 'passthru(', 'shell_exec(',
                     'proc_open(', 'popen(', 'pcntl_exec(', 'assert('];
        $lower = strtolower(str_replace([' ', "\t"], '', $content));
        foreach ($patterns as $p) {
            if (str_contains($lower, $p)) return true;
        }
        return false;
    }

    /**
     * Detect prompts requesting a full application/system that need multi-phase generation.
     */
    private function isFullSystemRequest(string $prompt): bool
    {
        $lower = strtolower($prompt);

        if (str_contains($lower, 'ryaan template customization context')) {
            return false;
        }

        $actionWords = [
            'create', 'build', 'make', 'generate', 'develop', 'implement',
            'setup', 'set up', 'design', 'write', 'code', 'scaffold',
        ];

        $systemWords = [
            // Full applications
            'system', 'management', 'platform', 'portal', 'application', 'app',
            'website', 'site', 'shop', 'store', 'ecommerce', 'e-commerce',
            'crm', 'erp', 'cms', 'saas', 'booking', 'inventory', 'dashboard',
            'complete', 'full stack', 'fullstack', 'full-stack', 'full', 'entire',
            // Domain-specific system types (no "system" keyword needed)
            'hotel', 'hospital', 'clinic', 'pharmacy', 'healthcare',
            'school', 'university', 'college', 'academy', 'institute',
            'restaurant', 'cafe', 'bakery', 'food delivery',
            'gym', 'fitness', 'sports club',
            'library', 'archive',
            'real estate', 'property', 'rental',
            'warehouse', 'logistics', 'fleet', 'supply chain',
            'hr system', 'payroll', 'hrms', 'attendance',
            'construction', 'project management',
            'law firm', 'legal', 'nonprofit', 'charity', 'church',
            'travel', 'tourism', 'airline', 'car rental',
            // Frontend / UI builds
            'landing page', 'landing', 'homepage', 'home page', 'template',
            'theme', 'ui kit', 'design system', 'component library',
            // Smaller build types
            'plugin', 'module', 'widget', 'extension', 'component', 'page',
            'form', 'table', 'chart', 'modal', 'sidebar', 'navbar',
            // API / backend
            'api', 'rest api', 'graphql', 'backend', 'microservice', 'webhook',
            // Other
            'portfolio', 'blog', 'admin', 'panel', 'wizard', 'calculator',
            'calendar', 'scheduler', 'chat', 'notification', 'report',
        ];

        $hasAction = false;
        $hasSystem = false;
        foreach ($actionWords as $w) { if (str_contains($lower, $w)) { $hasAction = true; break; } }
        foreach ($systemWords as $w) { if (str_contains($lower, $w)) { $hasSystem = true; break; } }

        return $hasAction && $hasSystem;
    }

    /**
     * Two-phase generation: Phase 1 = backend (migrations/models/controllers/routes),
     * Phase 2 = frontend (all Blade views). Produces a complete, working application.
     */
    private function generateMultiPhase(
        string $prompt,
        Project $project,
        AIConversation $conversation,
        mixed $aiProvider,
        string $systemPrompt,
        array $history,
        ?string $model
    ): array {
        $totalTokens   = 0;
        $responseModel = '';
        $allFiles      = [];
        $options       = array_merge($model ? ['model' => $model] : [], ['max_tokens' => 16000]);

        // ── Phase 1: Backend ──────────────────────────────────────────────────
        $phase1Prompt = $this->buildPhase1Prompt($prompt);
        $messages1    = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $phase1Prompt]]
        );

        $start1        = microtime(true);
        $response1     = $aiProvider->chat($messages1, $options);
        $time1         = (int) ((microtime(true) - $start1) * 1000);
        $parsed1       = $this->parseResponse($response1['content'], $project);
        $allFiles      = $parsed1['files'];
        $totalTokens  += $response1['tokens_used'];
        $responseModel = $response1['model'];

        // ── Phase 2: Frontend Views ───────────────────────────────────────────
        // Use a compact summary instead of full Phase 1 JSON (~16k tokens) to save input tokens
        $generatedList  = implode("\n", array_map(fn($f) => '  • ' . $f['path'], $allFiles));
        $phase1Summary  = "Phase 1 complete. Generated " . count($allFiles) . " backend files:\n" . $generatedList;
        $phase2Prompt   = $this->buildPhase2Prompt($prompt, $generatedList);
        $messages2      = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user',      'content' => $phase1Prompt]],
            [['role' => 'assistant', 'content' => $phase1Summary]],
            [['role' => 'user',      'content' => $phase2Prompt]]
        );

        $start2        = microtime(true);
        $response2     = $aiProvider->chat($messages2, $options);
        $time2         = (int) ((microtime(true) - $start2) * 1000);
        $parsed2       = $this->parseResponse($response2['content'], $project);
        $allFiles      = array_merge($allFiles, $parsed2['files']);
        $totalTokens  += $response2['tokens_used'];
        $responseModel = $response2['model'];

        $fileCount      = count($allFiles);
        $summaryMsg     = $parsed2['message'] ?: $parsed1['message'] ?: 'Backend and frontend fully generated.';
        $displayMessage = "Complete system generated — {$fileCount} files created.\n\n{$summaryMsg}";

        $conversation->addMessage('assistant', $response2['content'], [
            'tokens_used'      => $totalTokens,
            'model'            => $responseModel,
            'response_time_ms' => $time1 + $time2,
            'generated_files'  => array_column($allFiles, 'path'),
        ]);

        $project->increment('ai_tokens_used', $totalTokens);

        return [
            'message'         => $displayMessage,
            'files_generated' => $allFiles,
            'tokens_used'     => $totalTokens,
            'model'           => $responseModel,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Blueprint-Driven Generation (replaces multi-phase for full-system builds)
    // Invisible to user — no button, no tab. Fires automatically.
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Blueprint-Driven flow (non-streaming):
     *   Step 1: AI Discovery   — tiny call (~300 tokens) → structured blueprint JSON
     *   Step 2: Auto-CRUD      — MetadataCrudGenerator for all entities (zero AI cost)
     *   Step 3: AI Complex     — single AI call for auth, dashboard, integrations
     *
     * vs old multi-phase: 2 × 16 000 token calls = ~32 000 tokens
     * Blueprint-Driven:   300 + 0 + ~10 000 = ~10 300 tokens  (68% saving)
     */
    private function generateBlueprintDriven(
        string $prompt,
        Project $project,
        User $user,
        AIConversation $conversation,
        string $systemPrompt,
        array $history,
        ?string $provider,
        ?string $model
    ): array {
        $totalTokens = 0;
        $allFiles    = [];

        // ── Step 1: Discovery (~300 tokens) ─────────────────────────────────
        $blueprint    = $this->discoverWithFallback($prompt, $provider, $user);
        $this->blueprintService->store($project, $blueprint);
        $totalTokens += $blueprint['tokens_used'] ?? 0;

        // ── Step 2: Auto-generate standard CRUD (zero AI) ────────────────────
        $entities      = $blueprint['suggested_entities'] ?? $blueprint['entities'] ?? [];
        $autoGenerated = [];
        foreach ($entities as $entity) {
            if (empty($entity['name']) || empty($entity['fields'])) continue;
            $files = $this->crudGenerator->generate($entity);
            foreach ($files as $f) {
                $saved = $this->saveFileToProject($project, $f['path'], $f['content']);
                if ($saved) $allFiles[] = $saved;
            }
            $autoGenerated[] = $entity['name'];
        }

        // ── Step 3: AI call for complex parts only ────────────────────────────
        // Reload system prompt now that blueprint is stored — it gets injected
        $systemPrompt   = $this->buildSystemPrompt($project);
        $reducedPrompt  = $this->buildBlueprintReducedPrompt($prompt, $blueprint, $autoGenerated);
        $messages       = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $reducedPrompt]]
        );

        $startTime    = microtime(true);
        $response     = $this->chatWithFallback(
            $messages,
            array_merge($model ? ['model' => $model] : [], ['max_tokens' => 14000]),
            $provider,
            $user
        );
        $responseTime = (int) ((microtime(true) - $startTime) * 1000);
        $totalTokens += $response['tokens_used'];

        $parsed = $this->parseResponse($response['content'], $project);
        foreach ($parsed['files'] as $f) { $allFiles[] = $f; }

        // ── Save conversation record ──────────────────────────────────────────
        $conversation->addMessage('assistant', $response['content'], [
            'tokens_used'      => $totalTokens,
            'model'            => $response['model'],
            'response_time_ms' => $responseTime,
            'generated_files'  => array_column($allFiles, 'path'),
        ]);
        $project->increment('ai_tokens_used', $totalTokens);

        // Build user-visible summary
        $autoCount   = count($autoGenerated);
        $aiCount     = count($parsed['files']);
        $fileCount   = count($allFiles);
        $displayMsg  = $parsed['message']
            ?: "Built {$project->name} successfully: {$fileCount} files created, including {$autoCount} business modules and {$aiCount} supporting files.";

        return [
            'message'         => $displayMsg,
            'files_generated' => $allFiles,
            'tokens_used'     => $totalTokens,
            'model'           => $response['model'],
        ];
    }

    /**
     * Blueprint-Driven streaming flow — same 3 steps but with SSE progress events.
     * User sees: "Analyzing..." → "Auto-generating Customer..." → "Building complex features..." → streaming AI output.
     */
    private function streamBlueprintDriven(
        string $prompt,
        Project $project,
        AIConversation $conversation,
        mixed $aiProvider,
        string $systemPrompt,
        array $history,
        ?string $model,
        callable $onEvent
    ): void {
        $totalTokens = 0;
        $allFiles    = [];

        // ── Step 1: Discovery ─────────────────────────────────────────────────
        $onEvent(['type' => 'activity', 'icon' => '🔍', 'text' => 'Analyzing requirements...']);
        $blueprint    = $this->blueprintService->discover($prompt, $aiProvider);
        $this->blueprintService->store($project, $blueprint);
        $totalTokens += $blueprint['tokens_used'] ?? 0;

        $packNames = implode(' + ', $blueprint['matched_packs'] ?? [($blueprint['app_type'] ?? 'custom')]);
        $onEvent(['type' => 'activity', 'icon' => '🗺️',
                  'text' => "Planning {$blueprint['name']} workspace"]);

        // ── Step 2: Auto-CRUD (zero AI) ───────────────────────────────────────
        $entities      = $blueprint['suggested_entities'] ?? $blueprint['entities'] ?? [];
        $autoGenerated = [];
        foreach ($entities as $entity) {
            if (empty($entity['name']) || empty($entity['fields'])) continue;
            $entityName = $entity['name'];
            $onEvent(['type' => 'activity', 'icon' => '⚡',
                      'text' => "Preparing {$entityName} module"]);
            $files      = $this->crudGenerator->generate($entity);
            $savedBatch = [];
            foreach ($files as $f) {
                $saved = $this->saveFileToProject($project, $f['path'], $f['content']);
                if ($saved) { $savedBatch[] = $saved; $allFiles[] = $saved; }
            }
            if (!empty($savedBatch)) {
                $onEvent(['type' => 'files', 'files' => $savedBatch]);
            }
            $autoGenerated[] = $entityName;
        }

        // ── Step 3: Stream AI for complex parts ───────────────────────────────
        $onEvent(['type' => 'activity', 'icon' => '🤖',
                  'text' => 'Building workspace, dashboard, and business logic...']);

        $systemPrompt  = $this->buildSystemPrompt($project);
        $reducedPrompt = $this->buildBlueprintReducedPrompt($prompt, $blueprint, $autoGenerated);
        $messages      = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $reducedPrompt]]
        );

        $accumulated = '';
        try {
            $aiProvider->stream(
                $messages,
                function (string $chunk) use ($onEvent, &$accumulated) {
                    $accumulated .= $chunk;
                    $onEvent(['type' => 'chunk', 'text' => $chunk]);
                },
                array_merge($model ? ['model' => $model] : [], ['max_tokens' => 14000])
            );
        } catch (\Throwable $e) {
            $onEvent(['type' => 'error', 'message' => 'AI error: ' . $e->getMessage()]);
            return;
        }

        $parsed = $this->parseResponse($accumulated, $project);
        foreach ($parsed['files'] as $f) { $allFiles[] = $f; }

        // ── Save conversation ─────────────────────────────────────────────────
        $conversation->addMessage('assistant', $accumulated, [
            'tokens_used'     => $totalTokens,
            'model'           => 'RyaanCMS',
            'generated_files' => array_column($allFiles, 'path'),
        ]);
        $project->increment('ai_tokens_used', $totalTokens);

        $onEvent([
            'type'        => 'done',
            'message'     => $parsed['message'] ?: 'Application built successfully.',
            'model'       => 'RyaanCMS',
            'files'       => $allFiles,
            'tokens_used' => $totalTokens,
        ]);
    }

    /**
     * Public entry point for external services (e.g. PipelineOrchestrator).
     * Delegates to saveFileToProject with the same security checks applied.
     */
    public function saveGeneratedFile(Project $project, string $path, string $content): ?array
    {
        return $this->saveFileToProject($project, $path, $content);
    }

    /**
     * Save a single file (path + content) to the project, applying the same
     * security scanning and watermarking used by parseResponse().
     * Returns the saved file data array, or null if security check fails.
     */
    private function saveFileToProject(Project $project, string $path, string $content): ?array
    {
        $path = $this->sanitizeFilePath($this->normalizePath($path));
        if ($path === null) return null;

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($this->scanForDangerousContent($content, $extension)) return null;

        $content     = $this->injectWatermark($content, $extension);
        $projectFile = ProjectFile::updateOrCreate(
            ['project_id' => $project->id, 'path' => $path],
            [
                'name'      => basename($path),
                'type'      => 'file',
                'extension' => $extension,
                'content'   => $content,
                'size'      => strlen($content),
            ]
        );

        return [
            'id'      => $projectFile->id,
            'path'    => $path,
            'name'    => basename($path),
            'content' => $content,
        ];
    }

    /**
     * Build a focused prompt for the AI's "complex parts" phase.
     * The auto-generated entities are already saved — AI must NOT recreate them.
     * AI focuses only on: auth, dashboard, nav, layout, integrations, business logic.
     */
    private function buildBlueprintReducedPrompt(string $originalPrompt, array $blueprint, array $autoGenerated): string
    {
        $entityList   = empty($autoGenerated) ? 'none' : implode(', ', $autoGenerated);
        $features     = implode(', ', $blueprint['features'] ?? []);
        $users        = implode(', ', $blueprint['users'] ?? ['admin', 'user']);
        $platform     = implode(', ', $blueprint['platform'] ?? ['web']);
        $packs        = implode(', ', $blueprint['matched_packs'] ?? []);
        $outputs      = $this->summarizeBlueprintList($blueprint['requested_outputs'] ?? []);
        $pages        = $this->summarizeBlueprintList($blueprint['pages'] ?? []);
        $sections     = $this->summarizeBlueprintList($blueprint['sections'] ?? []);
        $forms        = $this->summarizeBlueprintList($blueprint['forms'] ?? []);
        $workflows    = $this->summarizeBlueprintList($blueprint['workflows'] ?? []);
        $reports      = $this->summarizeBlueprintList($blueprint['reports'] ?? []);
        $dashboards   = $this->summarizeBlueprintList($blueprint['dashboards'] ?? []);
        $automations  = $this->summarizeBlueprintList($blueprint['automations'] ?? []);
        $integrations = $blueprint['integrations'] ?? [];
        $integStr     = empty($integrations) ? 'none specified' : implode(', ', $integrations);

        // KB context: RBAC + workflow + reports + architecture + integration setup
        $appType      = $blueprint['app_type'] ?? $this->kb->matchAppType($originalPrompt);
        $businessModel= $blueprint['business_model'] ?? 'default';
        $kbContext    = $this->kb->buildKbContextFor($appType, $integrations, $businessModel);

        // Senior Developer Brief — architecture, security, anti-patterns, DB rules, estimation
        $seniorBrief  = $this->seniorKb->buildSeniorBrief($originalPrompt, $appType);

        // Wisdom Brief — accumulated project lessons + AI routing decisions
        $wisdomBrief  = $this->wisdomEngine->buildWisdomBrief($appType, $originalPrompt);

        return <<<PROMPT
ORIGINAL REQUEST: {$originalPrompt}

BLUEPRINT: {$blueprint['name']} | Type: {$appType} | Pack: {$packs}
Platform: {$platform} | Users: {$users} | Features: {$features}
Output modes: {$outputs}
Pages: {$pages}
Sections: {$sections}
Forms: {$forms}
Workflows: {$workflows}
Dashboards: {$dashboards}
Reports: {$reports}
Automations: {$automations}
Integrations required: {$integStr}

{$kbContext}

{$seniorBrief}

{$wisdomBrief}

ALREADY AUTO-GENERATED — DO NOT RECREATE (these files exist):
{$entityList}

YOUR JOB — generate ONLY what is NOT in the list above:
1. User authentication (login, register, logout, middleware guards) with the ROLES defined in KB
2. Main dashboard (KPI stats using the REPORTS listed in KB, recent activity, links to all modules)
3. Navigation layout matching the ARCHITECTURE pattern from KB
4. App layout file (layouts/app.blade.php wrapping all views)
5. Routes (web.php with resource routes for each module + auth routes + role middleware)
6. Business logic matching the WORKFLOW steps from KB
7. Seeders with realistic demo data for all roles and entities
8. Integration files for any requested integrations (use exact setup from KB integrations block)
9. Public website and/or landing page when Output modes includes website or landing_page
10. User portal pages when Output modes includes portal
11. Complete preview.html that demonstrates the requested output modes
12. docs/srs.md with complete Software Requirements Specification:
    system overview, actors/roles, module requirements, data dictionary,
    workflows, business rules, reports/KPIs, integrations, non-functional requirements,
    assumptions, and out-of-scope items

VISIBILITY RULE:
Never reveal internal blueprint, routing, token, cost, cache, or module-generation mechanics in user-facing copy or summaries.

APPLY Senior Dev Brief above — security, anti-patterns, architecture decisions are mandatory.
Return complete, working files. Do NOT reference or re-output the auto-generated entity files.
PROMPT;
    }

    /**
     * Keyword → file path segment mappings.
     * When a user says "update the dashboard", we find files whose path
     * contains any of the mapped segments and inject their content.
     */
    private array $fileKeywords = [
        // UI / layout
        'dashboard'      => ['dashboard'],
        'layout'         => ['layouts', 'app.blade'],
        'nav'            => ['layouts', 'nav', 'sidebar', 'menu'],
        'navigation'     => ['layouts', 'nav', 'sidebar', 'menu'],
        'sidebar'        => ['layouts', 'sidebar', 'nav'],
        'header'         => ['layouts', 'header', 'nav'],
        'footer'         => ['layouts', 'footer'],
        'menu'           => ['layouts', 'nav', 'menu', 'sidebar'],
        // Auth — login
        'login'          => ['LoginController', 'login'],
        'sign in'        => ['LoginController', 'login'],
        'signin'         => ['LoginController', 'login'],
        // Auth — register
        'register'       => ['RegisterController', 'register'],
        'sign up'        => ['RegisterController', 'register'],
        'signup'         => ['RegisterController', 'register'],
        // Auth — logout (was MISSING — caused the "logout redirect" bug)
        'logout'         => ['LoginController', 'AuthController', 'login'],
        'log out'        => ['LoginController', 'AuthController', 'login'],
        'signout'        => ['LoginController', 'AuthController', 'login'],
        'sign out'       => ['LoginController', 'AuthController', 'login'],
        // Redirect — always inject routes + relevant controller
        'redirect'       => ['LoginController', 'AuthController', 'routes/web'],
        'redirects to'   => ['LoginController', 'routes/web'],
        'after login'    => ['LoginController', 'routes/web'],
        'after logout'   => ['LoginController', 'routes/web'],
        'after register' => ['RegisterController', 'routes/web'],
        // Auth middleware / guards
        'middleware'     => ['Kernel', 'Middleware', 'routes/web'],
        'auth guard'     => ['Kernel', 'Middleware', 'routes/web'],
        'protected'      => ['Middleware', 'routes/web'],
        // Landing / home
        'landing'        => ['welcome', 'landing', 'home', 'index'],
        'landing page'   => ['welcome', 'landing', 'home', 'index', 'routes/web'],
        'home'           => ['welcome', 'home', 'index'],
        'home page'      => ['welcome', 'home', 'index', 'routes/web'],
        'hero'           => ['welcome', 'landing', 'home', 'index'],
        'homepage'       => ['welcome', 'home', 'index'],
        'welcome'        => ['welcome'],
        'welcome page'   => ['welcome', 'routes/web'],
        // Password
        'password'       => ['ForgotPassword', 'ResetPassword', 'login'],
        'forgot'         => ['ForgotPassword', 'login'],
        'reset'          => ['ResetPassword'],
        // Payment / billing
        'payment'        => ['PaymentController', 'payment'],
        'checkout'       => ['PaymentController', 'checkout', 'payment'],
        'invoice'        => ['invoice', 'payment'],
        'subscription'   => ['subscription', 'plan'],
        // Notifications
        'notification'   => ['NotificationController', 'notification'],
        'bell'           => ['notification-bell', 'notification'],
        // Roles / permissions
        'role'           => ['RoleController', 'role', 'permission'],
        'permission'     => ['RoleController', 'role', 'permission', 'Middleware'],
        'rbac'           => ['RoleController', 'role', 'permission'],
        // API
        'api'            => ['routes/api', 'Api/'],
        'token'          => ['routes/api', 'Sanctum', 'AuthController'],
        // Common CRUD views
        'index'          => ['index.blade'],
        'create'         => ['create.blade'],
        'edit'           => ['edit.blade'],
        'show'           => ['show.blade'],
        'form'           => ['create.blade', 'edit.blade'],
        'table'          => ['index.blade'],
        'list'           => ['index.blade'],
        // Routes
        'route'          => ['routes/web', 'routes/api'],
        'routes'         => ['routes/web', 'routes/api'],
        'named route'    => ['routes/web'],
        // Models / Controllers / Requests
        'model'          => ['app/Models'],
        'controller'     => ['Controllers'],
        'migration'      => ['migrations'],
        'seeder'         => ['seeders'],
        'request'        => ['Requests'],
        'validation'     => ['Requests'],
        // Settings / config
        'config'         => ['config/', 'settings'],
        'setting'        => ['settings', 'config/'],
        'env'            => ['.env', 'config/'],
        // Email / mail
        'email'          => ['Mail', 'Notification', 'notification'],
        'mail'           => ['Mail', 'mailable'],
        'smtp'           => ['config/mail'],
        // Storage / upload
        'upload'         => ['MediaController', 'media', 'Storage'],
        'file upload'    => ['MediaController', 'media'],
        'image'          => ['MediaController', 'media'],
        // Audit / logs
        'audit'          => ['AuditLog', 'audit'],
        'log'            => ['AuditLog', 'audit'],
        'activity'       => ['AuditLog', 'audit'],
    ];

    /**
     * Inject current file contents + file tree + preview.html (when relevant) into the
     * user message. Keeping dynamic context here (not in system prompt) ensures the
     * system prompt stays identical across requests → Anthropic prompt cache always hits
     * → 90% savings on those ~10k system-prompt tokens.
     *
     * Defaults: 3 files × 6k chars. Callers may override for tiny/large tasks.
     */
    protected function buildPromptWithFileContext(string $prompt, Project $project, int $maxFiles = 3, int $maxChars = 6000): string
    {
        $files = $project->files()->where('type', 'file')->get();
        if ($files->isEmpty()) return $prompt;

        $lower   = strtolower($prompt);
        $scored  = [];

        foreach ($files as $file) {
            if (!$file->content) continue;

            $filePath = strtolower($file->path ?? $file->name);
            $baseName = strtolower(pathinfo($file->name, PATHINFO_FILENAME));
            $score    = 0;

            // Exact filename or base name in prompt — highest priority
            if (str_contains($lower, strtolower($file->name))) { $score += 20; }
            elseif (strlen($baseName) > 3 && str_contains($lower, $baseName)) { $score += 15; }

            // Path segment match (e.g. "students" in prompt matches "students/index.blade.php")
            foreach (array_filter(explode('/', $filePath)) as $seg) {
                if (strlen($seg) > 4 && str_contains($lower, strtolower(pathinfo($seg, PATHINFO_FILENAME)))) {
                    $score += 8;
                    break;
                }
            }

            // Keyword map match
            foreach ($this->fileKeywords as $keyword => $segments) {
                if (str_contains($lower, $keyword)) {
                    foreach ($segments as $seg) {
                        if (str_contains($filePath, $seg)) {
                            $score += 5;
                            break;
                        }
                    }
                }
            }

            if ($score > 0) {
                $scored[$file->id] = ['file' => $file, 'score' => $score];
            }
        }

        // No keyword matches — fall back to the most recently modified files so the
        // AI always has actual file content and doesn't respond conversationally.
        if (empty($scored)) {
            $fallback = $files->filter(fn($f) => $f->content)
                              ->sortByDesc('updated_at')
                              ->take(min(3, $maxFiles));
            if ($fallback->isEmpty()) return $prompt;
            foreach ($fallback as $f) {
                $scored[$f->id] = ['file' => $f, 'score' => 1];
            }
        }

        usort($scored, fn($a, $b) => $b['score'] - $a['score']);
        $topFiles = array_slice($scored, 0, $maxFiles);

        // ── File tree (compact, paths only) ──────────────────────────────────
        $allCount = $files->count();
        $fileTree = $files->sortBy('path')->pluck('path')
            ->map(fn($p) => '  • ' . $p)->implode("\n");
        if ($allCount > 60) $fileTree .= "\n  … and " . ($allCount - 60) . " more";

        // ── preview.html (HEAD + TAIL so app() function is always visible) ────
        $previewSection = '';
        $previewFile    = $files->firstWhere('name', 'preview.html');
        $wantsPreview   = $previewFile && $this->promptNeedsPreviewContext($prompt);
        if ($wantsPreview && $previewFile->content) {
            $raw    = $previewFile->content;
            $rawLen = mb_strlen($raw);
            if ($rawLen <= 22000) {
                $previewContent = $raw;
                $note = "({$rawLen} chars — full file)";
            } else {
                $head    = mb_substr($raw, 0, 12000);
                $tail    = mb_substr($raw, -8000);
                $omitted = $rawLen - 12000 - 8000;
                $previewContent = $head
                    . "\n\n<!-- ═══ " . number_format($omitted) . " chars omitted — use patches ═══ -->\n\n"
                    . $tail;
                $note = "({$rawLen} chars total — HEAD 12k + TAIL 8k)";
            }
            $previewSection = "\n\nCURRENT PREVIEW.HTML {$note} — use \"patches\" for targeted edits, \"files\" only to fully rebuild:\n```html\n{$previewContent}\n```";
        }

        // ── Specific file contents ─────────────────────────────────────────────
        $injected = [];
        foreach ($topFiles as $item) {
            $f = $item['file'];
            if ($f->name === 'preview.html' && $wantsPreview) continue; // already injected above
            $content    = mb_substr($f->content, 0, $maxChars);
            $injected[] = "Current content of `{$f->path}` (make ONLY the requested changes, keep everything else exactly the same):\n```\n{$content}\n```";
        }

        $parts = ["EXISTING FILES ({$allCount} total):\n{$fileTree}"];
        if ($previewSection)    $parts[] = $previewSection;
        if (!empty($injected))  $parts[] = "Files to edit:\n\n" . implode("\n\n", $injected);

        return $prompt
            . "\n\n---\n"
            . implode("\n\n", $parts)
            . "\n\nIMPORTANT: Read the files above. Make ONLY the requested changes. Never rewrite from scratch unless asked.";
    }

    /** True when the prompt is about visual/UI/preview content that needs preview.html injected. */
    private function promptNeedsPreviewContext(string $prompt): bool
    {
        $lower = strtolower($prompt);
        $uiTerms = [
            'preview', 'logo', 'click', 'landing', 'login', 'dashboard', 'screen',
            'page', 'button', 'color', 'colour', 'background', 'navbar', 'navigation',
            'modal', 'form', 'card', 'table', 'chart', 'sidebar', 'footer', 'header',
            'design', 'style', 'layout', 'animation', 'redirect', 'show', 'hide',
            'dark', 'light', 'theme', 'font', 'icon', 'image', 'responsive', 'mobile',
        ];
        foreach ($uiTerms as $term) {
            if (str_contains($lower, $term)) return true;
        }
        return false;
    }

    protected function buildSystemPrompt(Project $project, bool $withDocs = false): string
    {
        // Check if user has auto-docs enabled
        if (!$withDocs && $project->user_id) {
            $withDocs = (bool) \App\Models\Setting::get('ai_builder.auto_docs', false, $project->user_id);
        }

        $stack = implode(', ', $project->tech_stack ?? ['Laravel', 'PHP', 'Tailwind CSS', 'Alpine.js']);

        // Inject blueprint context if available — replaces needing to read all project files
        $blueprintContext = '';
        if ($project->blueprint) {
            $bp       = $project->blueprint;
            $entities = collect($bp['entities'] ?? $bp['suggested_entities'] ?? [])
                ->map(fn($e) => '  • ' . ($e['name'] ?? ''))
                ->implode("\n");
            $features = implode(', ', $bp['features'] ?? []);
            $packs    = implode(', ', $bp['matched_packs'] ?? []);
            $domain   = $bp['business_domain'] ?? $bp['industry'] ?? '';
            $level    = $bp['blueprint_level'] ?? 'professional';
            $outputs  = $this->summarizeBlueprintList($bp['requested_outputs'] ?? []);
            $pages    = $this->summarizeBlueprintList($bp['pages'] ?? []);
            $forms    = $this->summarizeBlueprintList($bp['forms'] ?? []);
            $reports  = $this->summarizeBlueprintList($bp['reports'] ?? []);
            $flows    = $this->summarizeBlueprintList($bp['workflows'] ?? []);
            $blueprintContext = <<<BP

═══════════════════════════════════════════════
PROJECT BLUEPRINT (follow this architecture exactly)
═══════════════════════════════════════════════
App Type   : {$bp['app_type']}
App Name   : {$bp['name']}
Domain     : {$domain}
Level      : {$level}
Outputs    : {$outputs}
Platform   : {$this->joinArr($bp['platform'] ?? [])}
Users      : {$this->joinArr($bp['users'] ?? [])}
Features   : {$features}
Domain Packs: {$packs}
Pages      : {$pages}
Forms      : {$forms}
Workflows  : {$flows}
Reports    : {$reports}
Entities   :
{$entities}

Rule: Build only what the blueprint defines. Do not expose blueprint, routing, token, cache, or cost mechanics to the user.
BP;
        }

        // Pick a fresh random design variant for this generation.
        // Goes into the USER message (not system prompt) so prompt caching still hits
        // on the static system prompt while design varies per request.
        $design = $this->designVariants->pick();

        // Senior Dev Brief — injected once per project session (applies to all messages)
        $seniorBrief = $this->seniorKb->buildSeniorBrief(
            $project->description ?? $project->name,
            $project->type ?? ''
        );

        // Wisdom Brief — accumulated project lessons for this domain (only static patterns here;
        // DB-backed lessons are injected per-request via buildPromptWithFileContext)
        $wisdomBrief = $this->wisdomEngine->buildWisdomBrief(
            $project->type ?? 'general',
            $project->description ?? ''
        );

        $docsSection = '';
        if ($withDocs) {
            $docsSection = <<<DOCS

═══════════════════════════════════════════════
AUTO DOCUMENTATION (MANDATORY — user has enabled auto-docs)
═══════════════════════════════════════════════
You MUST include a file at path `docs/index.html` in EVERY response that generates or modifies code.
This file is a living, self-contained HTML documentation page. Requirements:
• Single file, zero external dependencies (inline CSS + JS only)
• Dark-mode-aware: use `prefers-color-scheme` media query
• Sections (include whichever are relevant):
  1. Overview — app name, description, tech stack badges
  2. Pages / Routes — table: Route | Method | Description | Auth?
  3. Components — card per component with usage example
  4. API Endpoints — if any REST routes exist (Method | URL | Params | Response)
  5. Data Models — entity name, fields table (field | type | description)
  6. Getting Started — numbered steps to install and run
  7. Environment Variables — table of required .env keys
• Style: clean, professional, readable — use a system-ui font stack
• Update docs for whatever files changed in this generation — do not regenerate unchanged sections
• Include breadcrumb navigation at the top so developers can jump between sections
DOCS;
        }

        return config('ai.system_prompt') . "\n\n" . <<<CONTEXT
═══════════════════════════════════════════════
CURRENT PROJECT
═══════════════════════════════════════════════
Name:        {$project->name}
Type:        {$project->type}
Description: {$project->description}
Tech Stack:  {$stack}
{$blueprintContext}
{$design['prompt_block']}

{$seniorBrief}

{$wisdomBrief}
{$docsSection}

═══════════════════════════════════════════════
UI CARD DESIGN RULES (mandatory)
═══════════════════════════════════════════════
• KPI / stat cards MUST be square: use `aspect-ratio:1` + `display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;`
• Feature / module cards: use `.sys-card` with `style="--c:<hex>"` for per-card hover color
• Never use fixed `height` on cards — let `aspect-ratio:1` (stat) or natural content height (feature) drive sizing
• Card accent: `border-left:3px solid var(--c,var(--brand))` on hover; shadow: `0 10px 36px color-mix(in srgb,var(--c,var(--brand)) 20%,transparent)`
• Icon badge: `width:40px;height:40px;border-radius:11px;background:color-mix(in srgb,var(--c,var(--brand)) 10%,#fff)`
• Action buttons: `background:color-mix(in srgb,var(--c,var(--brand)) 10%,#fff);color:var(--c,var(--brand))` → solid on hover

═══════════════════════════════════════════════
DATATABLE RULES (mandatory for every list/table view)
═══════════════════════════════════════════════
ALL data tables MUST use the global `.dt-*` class system and `dtMixin()` helper (both are pre-loaded globally):

HTML structure:
  <div class="dt-wrap">
    <div class="dt-head">
      <div>
        <h2 class="dt-title">Records Table</h2>
        <p class="dt-subtitle">Search, filter, and manage records.</p>
      </div>
    </div>
    <div class="dt-toolbar">
      <div class="dt-search">
        <svg class="dt-search-ico">…search icon…</svg>
        <input x-model="search" @input="page=1" class="dt-search-input" placeholder="Search…">
        <button x-show="search" @click="search='';page=1" class="dt-clear">×</button>
      </div>
      <div class="dt-per-page">
        <span>Show</span>
        <select x-model.number="perPage" @change="page=1">
          <template x-for="n in perPageOpts"><option :value="n" x-text="n"></option></template>
        </select>
      </div>
      <span class="dt-count" x-text="filtered.length + ' results'"></span>
    </div>
    <div class="overflow-x-auto">
      <table class="dt-table">
        <thead><tr>
          <th class="dt-th">Col</th> …
          <th class="dt-th" style="text-align:right">Actions</th>
        </tr></thead>
        <tbody>
          <template x-if="paginated.length === 0">
            <tr><td colspan="N" class="dt-empty">No results found</td></tr>
          </template>
          <template x-for="(row, i) in paginated" :key="row.id">
            <tr class="dt-tr">
              <td class="dt-td" x-html="dtHighlight(row.name, search)">…</td>
              …
              <td class="dt-td" style="text-align:right">…actions…</td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div class="dt-foot">
      <span class="dt-foot-info" x-text="dtInfo(filtered.length, page, perPage)"></span>
      <div class="dt-pages" x-show="totalPages > 1">
        <button class="dt-page-btn" @click="page=Math.max(1,page-1)" :disabled="page===1">‹</button>
        <template x-for="p in pageRange" :key="p+'-'+page">
          <template x-if="p==='…'"><span class="dt-page-dot">…</span></template>
          <template x-if="p!=='…'">
            <button class="dt-page-btn" :class="p===page?'dt-page-on':''" @click="page=p" x-text="p"></button>
          </template>
        </template>
        <button class="dt-page-btn" @click="page=Math.min(totalPages,page+1)" :disabled="page===totalPages">›</button>
      </div>
    </div>
  </div>

Alpine JS data function — spread dtMixin first, then override:
  function myTable() {
    return {
      ...dtMixin({ perPage: 10 }),  // provides: search, page, perPage, perPageOpts, dtSearch(), dtPageRange(), dtInfo(), dtHighlight()
      allRows: [],  // populated from @json($items)
      init() { this.$watch('perPage', () => { this.page = 1; }); },
      get filtered() {
        let list = this.allRows;
        // apply any extra filters…
        return this.dtSearch(list, ['name', 'email', 'status']);  // pass searchable fields
      },
      get paginated() { const s=(this.page-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
      get totalPages() { return Math.max(1, Math.ceil(this.filtered.length/this.perPage)); },
      get pageRange()  { return this.dtPageRange(this.totalPages, this.page); },
    };
  }

• Use `dtHighlight(row.field, search)` (returns HTML with `<mark class="dt-mark">`) inside `x-html` directives
• Every data table MUST have a visible heading block above the toolbar using `.dt-head`, `.dt-title`, and `.dt-subtitle`
• Default perPage is 10; perPageOpts is [10,20,50,100] — always expose the selector
• Always use `dtInfo(filtered.length, page, perPage)` for the footer "Showing X–Y of Z" text
• Never build custom pagination or search from scratch when dtMixin is available

═══════════════════════════════════════════════
LOW-CODE PROBLEM SOLVING (mandatory approach)
═══════════════════════════════════════════════
You are a low-code expert. When solving problems or implementing features, always follow this decision hierarchy:

1. USE WHAT EXISTS FIRST
   - Before writing custom code, check if the framework / ecosystem already solves it
   - Laravel: leverage Eloquent scopes, mutators, casts, policies, form requests, observers, jobs, events
   - Alpine.js: $store, $dispatch, $watch, magic methods — avoid vanilla JS duplication
   - Tailwind: utility classes over custom CSS — only write custom CSS for things Tailwind can't express

2. REACH FOR PACKAGES BEFORE CUSTOM CODE
   - Well-maintained Laravel packages (Spatie, Laravel itself) over hand-rolling: roles/perms → spatie/laravel-permission, media → spatie/laravel-medialibrary, etc.
   - Only suggest custom implementations when a package would be overkill (< 20 lines) or doesn't exist

3. COMPOSITION OVER DUPLICATION
   - Extract repeated HTML into Blade components or @include partials
   - Extract repeated JS into Alpine stores or named functions
   - Re-use the project's existing helpers, traits, base classes — never reinvent something already in the codebase

4. PROBLEM DECOMPOSITION
   - When given a vague "build X" request: list the sub-problems first, then solve each with the simplest tool available
   - Prefer incremental working solutions (get it working → then optimize) over complex upfront abstractions
   - Comment WHY a decision was made when it's non-obvious (e.g., why a particular package was chosen)

5. DATA FLOW CLARITY
   - Single source of truth: keep state in one place (DB → controller → view → Alpine)
   - Never duplicate state; pass data through @json() / x-data injection, not hidden inputs spread across templates
   - Form submits → redirect with session flash OR AJAX with JSON — never mix both patterns in the same flow

6. ERROR HANDLING MINIMUM
   - Validate at boundaries (form requests, API endpoints)
   - Show user-friendly error messages (not stack traces)
   - Use Laravel's built-in error bag and @error directives in Blade — no custom error display boilerplate

═══════════════════════════════════════════════
20-YEAR UI/UX DESIGN EXPERTISE (mandatory for every UI)
═══════════════════════════════════════════════
You design with the standards of a 20-year veteran UI/UX professional. Every interface you generate MUST meet these non-negotiable quality standards:

VISUAL HIERARCHY
• Every page has ONE primary action — highest contrast CTA, prominent placement
• Secondary actions are visually quieter (lower contrast, smaller, bordered)
• Headings: 3 max levels on a single page — h1 (page), h2 (section), h3 (card/subsection)
• White space is not waste — use generous padding (16px–24px inner, 32px+ between sections)

TYPOGRAPHY
• Font sizes: body 13–14px, labels 11–12px, headings 18–26px, hero 32–48px
• Line height: body 1.5–1.6, headings 1.2–1.3
• Font weight: regular (400) for body, semibold (600) for labels/subheadings, bold (700–800) for headings and CTAs
• Never use more than 2 font families in a single project
• Text contrast: minimum 4.5:1 ratio against its background (WCAG AA)

COLOR USAGE
• Use the project's `--brand` CSS variable as the single source of truth for the primary color
• Accent colors: max 3 semantic colors beyond brand (success #10b981, warning #f59e0b, danger #ef4444)
• Backgrounds: layered — page bg → card bg → input bg → overlay (each one step lighter/raised)
• Never use pure #000 or #fff — use near-blacks (#0f172a) and near-whites (#f8fafc)

SPACING SYSTEM (follow strictly — no arbitrary values)
• 4px grid: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64px
• Component padding: sm=8px 12px, md=10px 16px, lg=14px 20px
• Card border-radius: 12–16px outer, 8–10px inner elements
• Button border-radius: 8–10px for normal, 99px for pill/badge

COMPONENT QUALITY STANDARDS
• Buttons: must have hover state (color shift + subtle transform), focus ring (3px brand-ring), disabled state (40% opacity)
• Inputs: clear focus ring, error state (red border + error message below), placeholder lighter than text
• Cards: subtle shadow (box-shadow: 0 1px 4px rgba(0,0,0,.06) 0 4px 16px rgba(0,0,0,.04)), hover elevation lift
• Empty states: icon + heading + description + CTA — never just "No results found"
• Loading states: skeleton screens or spinner — never leave the user wondering if the page is broken
• Modals: backdrop blur (backdrop-filter: blur(4px)), centered with max-width, ESC to close
• Tables: alternating hover, sticky header if > 10 rows, right-aligned numeric columns, sortable headers with chevron icons
• Forms: group related fields, logical tab order, labels always visible (never placeholder-only), submit at bottom-right

INTERACTION QUALITY
• All state transitions have animations: 120–200ms ease for micro, 250–350ms for panels/modals
• Hover effects on every interactive element — no bare unresponsive elements
• Loading feedback within 100ms of user action (button spinner, disabled state)
• Success/error feedback within 200ms of response (toast, inline validation)
• Touch targets: minimum 44×44px for mobile, 32×32px desktop

RESPONSIVE DESIGN
• Mobile-first: design for 320px, enhance for 768px, 1024px, 1440px
• Never use horizontal scroll at mobile widths
• Stack multi-column layouts vertically on mobile
• Font sizes don't shrink below 12px on mobile

ACCESSIBILITY
• All images have meaningful alt text
• Icon-only buttons must have aria-label or title
• Color alone never conveys meaning — always pair with text or icon
• Form inputs have associated labels (not just placeholders)

When modifying existing files: read the file content provided in the user message,
make only the requested changes, and return the complete updated file.
Never skip unchanged sections with "..." or "// rest stays same".
CONTEXT;
    }

    private function joinArr(array $arr): string
    {
        return $this->summarizeBlueprintList($arr);
    }

    private function summarizeBlueprintList(mixed $items): string
    {
        if (!is_array($items)) {
            $items = array_filter([(string) $items]);
        }

        $summary = array_map(function ($item) {
            if (is_array($item)) {
                return (string) ($item['name'] ?? $item['title'] ?? $item['label'] ?? json_encode($item));
            }

            return (string) $item;
        }, $items);

        return implode(', ', array_values(array_filter($summary)));
    }

    protected function parseResponse(string $content, Project $project): array
    {
        $generatedFiles = [];

        // Multi-strategy JSON extraction — robust against complex nested file content
        $json = $this->extractJson($content);

        // Build display message
        $displayMessage = $this->buildDisplayMessage($content, $json);

        $hasFiles   = isset($json['files'])   && is_array($json['files']);
        $hasPatches = isset($json['patches']) && is_array($json['patches']);
        if (!$hasFiles && !$hasPatches) {
            return ['message' => $displayMessage, 'files' => []];
        }

        $seenPaths = [];
        foreach (($hasFiles ? $json['files'] : []) as $file) {
            if (empty($file['path']) || !isset($file['content'])) continue;

            // Normalize common AI path mistakes (e.g. Models/ → app/Models/)
            $normalizedPath = $this->normalizePath($file['path']);

            // Security: validate and sanitize the path before touching the DB
            $path = $this->sanitizeFilePath($normalizedPath);
            if ($path === null) continue;

            // Deduplication — keep first occurrence if AI returned same path twice
            if (isset($seenPaths[$path])) continue;
            $seenPaths[$path] = true;

            $name      = basename($path);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            // Security: reject files containing dangerous PHP code patterns
            if ($this->scanForDangerousContent($file['content'], $extension)) continue;

            $fileContent = $this->injectWatermark($file['content'], $extension);

            $projectFile = ProjectFile::updateOrCreate(
                ['project_id' => $project->id, 'path' => $path],
                [
                    'name'      => $name,
                    'type'      => 'file',
                    'extension' => $extension,
                    'content'   => $fileContent,
                    'size'      => strlen($fileContent),
                ]
            );

            $generatedFiles[] = [
                'id'      => $projectFile->id,
                'path'    => $path,
                'name'    => $name,
                'content' => $fileContent,
            ];
        }

        // Apply surgical patches — for large files like preview.html where returning the
        // full file would exceed the context window.
        if (isset($json['patches']) && is_array($json['patches'])) {
            foreach ($json['patches'] as $patch) {
                $patchedFile = $this->applyPatch($patch, $project);
                if ($patchedFile) {
                    // Replace or append to generatedFiles (patch wins over full-file if same path)
                    $existing = array_search($patchedFile['path'], array_column($generatedFiles, 'path'));
                    if ($existing !== false) {
                        $generatedFiles[$existing] = $patchedFile;
                    } else {
                        $generatedFiles[] = $patchedFile;
                    }
                }
            }
        }

        $totalSize = $project->files()->sum('size');
        $project->update(['storage_used' => $totalSize]);

        return ['message' => $displayMessage, 'files' => $generatedFiles];
    }

    /**
     * Apply a surgical patch to an existing project file.
     * The patch contains a "search" string (exact, must be unique) and a "replace" string.
     * Returns the saved file data array, or null if the patch could not be applied.
     */
    private function applyPatch(array $patch, Project $project): ?array
    {
        $rawPath = $patch['path'] ?? '';
        $search  = $patch['search'] ?? '';
        $replace = $patch['replace'] ?? '';

        if (!$rawPath || !$search) return null;

        $path = $this->sanitizeFilePath($this->normalizePath($rawPath));
        if ($path === null) return null;

        $projectFile = $project->files()->where('path', $path)->first();
        if (!$projectFile || !$projectFile->content) return null;

        $newContent = str_replace($search, $replace, $projectFile->content, $count);
        if ($count === 0) return null; // search string not found — skip silently

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($this->scanForDangerousContent($newContent, $extension)) return null;

        $projectFile->update([
            'content' => $newContent,
            'size'    => strlen($newContent),
        ]);

        return [
            'id'      => $projectFile->id,
            'path'    => $path,
            'name'    => basename($path),
            'content' => $newContent,
            'type'    => 'file',
        ];
    }

    /**
     * Multi-strategy JSON extraction. Tries 3 approaches in order of reliability:
     * 1. Fenced ```json block (most reliable — AI is instructed to use this)
     * 2. Balanced-bracket search starting at the "files" key
     * 3. Any valid JSON object containing "files" anywhere in the response
     */
    private function extractJson(string $content): ?array
    {
        // Strategy 1: ```json ... ``` fenced block
        if (preg_match('/```json\s*([\s\S]+?)\s*```/i', $content, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) return $decoded;
        }

        // Strategy 2: Find "files" or "patches" key and walk back to the enclosing {
        foreach (['"files"', '"patches"'] as $anchor) {
            $offset = 0;
            while (($keyPos = strpos($content, $anchor, $offset)) !== false) {
                $before   = substr($content, 0, $keyPos);
                $bracePos = strrpos($before, '{');
                if ($bracePos !== false) {
                    $json = $this->extractBalancedJson(substr($content, $bracePos));
                    if ($json !== null && (isset($json['files']) || isset($json['patches']))) return $json;
                }
                $offset = $keyPos + 1;
            }
        }

        // Strategy 3: Try every { in the response (last few most likely to be the main JSON)
        preg_match_all('/\{/', $content, $m, PREG_OFFSET_CAPTURE);
        foreach (array_reverse(array_slice($m[0], 0, 10)) as $match) {
            $json = $this->extractBalancedJson(substr($content, $match[1]));
            if ($json !== null && (isset($json['files']) || isset($json['patches']))) return $json;
        }

        return null;
    }

    /**
     * Parse a string starting at the first { and return the decoded JSON object
     * when the braces are balanced, or null if parsing fails.
     */
    private function extractBalancedJson(string $str): ?array
    {
        $depth    = 0;
        $inString = false;
        $escape   = false;
        $len      = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $c = $str[$i];
            if ($escape)             { $escape = false; continue; }
            if ($c === '\\' && $inString) { $escape = true; continue; }
            if ($c === '"')          { $inString = !$inString; continue; }
            if ($inString)           { continue; }
            if ($c === '{' || $c === '[') $depth++;
            elseif ($c === '}' || $c === ']') {
                $depth--;
                if ($depth === 0) {
                    $decoded = json_decode(substr($str, 0, $i + 1), true);
                    return is_array($decoded) ? $decoded : null;
                }
            }
        }
        return null;
    }

    /**
     * Fix common AI path mistakes before security validation.
     * e.g.  Models/User.php          → app/Models/User.php
     *       dashboard.blade.php       → resources/views/dashboard.blade.php
     *       migrations/create_x.php  → database/migrations/create_x.php
     */
    private function normalizePath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');

        // app/ prefix — PHP class directories
        if (preg_match('/^(Models|Http|Services|Jobs|Events|Listeners|Mail|Notifications|Policies|Providers|Rules|Console|Exceptions|Observers|Repositories)\//', $path)) {
            return 'app/' . $path;
        }

        // resources/views/ — any .blade.php not yet under resources/
        if (str_ends_with($path, '.blade.php') && !str_starts_with($path, 'resources/') && !str_starts_with($path, 'app/')) {
            return 'resources/views/' . $path;
        }

        // database/ prefix
        if (preg_match('/^(migrations|seeders|factories)\//', $path)) {
            return 'database/' . $path;
        }

        // routes/ prefix
        if (in_array($path, ['web.php', 'api.php', 'console.php', 'channels.php'])) {
            return 'routes/' . $path;
        }

        return $path;
    }

    /**
     * Map file extension to language identifier for editor syntax highlighting.
     */
    private function detectLanguage(string $ext): string
    {
        return match($ext) {
            'php'              => 'php',
            'blade'            => 'php',
            'js', 'jsx', 'mjs' => 'javascript',
            'ts', 'tsx'        => 'typescript',
            'vue'              => 'vue',
            'css'              => 'css',
            'scss', 'sass'     => 'scss',
            'html', 'htm'      => 'html',
            'json'             => 'json',
            'xml'              => 'xml',
            'yaml', 'yml'      => 'yaml',
            'sql'              => 'sql',
            'md', 'mdx'        => 'markdown',
            'sh', 'bash'       => 'shell',
            'py'               => 'python',
            'go'               => 'go',
            'rs'               => 'rust',
            'rb'               => 'ruby',
            'java'             => 'java',
            default            => 'plaintext',
        };
    }

    /**
     * Set a meaningful conversation title from the first user message.
     * Only runs when the conversation still has the default "New Conversation" title.
     */
    private function autoTitleConversation(AIConversation $conversation, string $prompt): void
    {
        if (!in_array($conversation->title, ['New Conversation', '', null], true)) return;
        $title = mb_substr(trim(preg_replace('/\s+/', ' ', $prompt)), 0, 65);
        if (mb_strlen($prompt) > 65) $title = rtrim($title) . '…';
        $conversation->update(['title' => $title]);
    }

    private function buildDisplayMessage(string $rawContent, ?array $json): string
    {
        // Use the structured summary if the AI provided one
        if (!empty($json['summary'])) {
            $msg = trim($json['summary']);
            if (!empty($json['next_steps']) && is_array($json['next_steps'])) {
                // Filter out dev-environment instructions (npm, composer, localhost) —
                // users deploy via web domain, not local dev servers
                $devPatterns = ['npm ', 'composer ', 'localhost', 'artisan serve', 'npm install',
                                'npm run', 'npm start', 'npm build', 'php artisan', 'localhost:'];
                $steps = array_filter($json['next_steps'], function ($s) use ($devPatterns) {
                    $lower = strtolower($s);
                    foreach ($devPatterns as $p) {
                        if (str_contains($lower, $p)) return false;
                    }
                    return true;
                });
                if (!empty($steps)) {
                    $msg .= "\n\nNext steps:\n" . implode("\n", array_map(fn($s) => '• ' . $s, $steps));
                }
            }
            return $msg;
        }

        // Strip the JSON block from the response and return surrounding text
        $text = preg_replace('/```json[\s\S]*?```/i', '', $rawContent);
        $text = preg_replace('/\{\s*"files"\s*:[\s\S]*\}/s', '', $text);
        $text = trim($text);

        if ($text !== '') return $text;

        // Fallback
        $fileCount = isset($json['files']) ? count($json['files']) : 0;
        return $fileCount > 0
            ? "Generated {$fileCount} file(s) successfully."
            : "Done.";
    }

    private function injectWatermark(string $content, string $ext): string
    {
        $watermarkHtml = <<<'HTML'

<!-- Powered by RyaanCMS -->
<div id="ryaancms-attr" style="position:fixed;bottom:12px;right:12px;z-index:9999;display:flex;align-items:center;gap:6px;padding:5px 11px;background:rgba(255,255,255,0.95);border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.08);font-family:system-ui,-apple-system,sans-serif;font-size:11px;font-weight:500;color:#64748b;backdrop-filter:blur(8px);pointer-events:none;user-select:none;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Powered by RyaanCMS v1.0.0</div>
HTML;

        $phpComment = "// Generated by RyaanCMS AI Builder v1.0.0 — https://ryaancms.com\n";
        $jsComment  = "// Generated by RyaanCMS AI Builder v1.0.0 — https://ryaancms.com\n";

        // HTML / Blade views — inject before </body>
        if (in_array($ext, ['html', 'htm', 'blade', 'php']) && str_contains(strtolower($content), '</body>')) {
            if (!str_contains($content, 'ryaancms-attr') && !str_contains($content, 'Powered by RyaanCMS')) {
                $content = preg_replace('/<\/body>/i', $watermarkHtml . "\n</body>", $content, 1);
            }
            return $content;
        }

        // PHP files — add comment after <?php opening line
        if ($ext === 'php' && !str_contains($content, 'Generated by RyaanCMS')) {
            $content = preg_replace('/^<\?php\s*/m', "<?php\n" . $phpComment, $content, 1);
            return $content;
        }

        // JS / TS files — prepend comment
        if (in_array($ext, ['js', 'ts', 'jsx', 'tsx']) && !str_contains($content, 'Generated by RyaanCMS')) {
            $content = $jsComment . $content;
        }

        return $content;
    }

    // Alias kept for backward compatibility
    protected function parseGeneratedFiles(string $content, Project $project): array
    {
        return $this->parseResponse($content, $project)['files'];
    }

    /**
     * Streaming generation with structured progress events.
     * Emitted event types:
     *   activity — { type, icon, text }              — progress step label
     *   chunk    — { type, text }                    — raw text chunk from Claude
     *   files    — { type, files[] }                 — batch of saved files
     *   done     — { type, message, model, files[] } — generation complete
     *   error    — { type, message }                 — fatal error
     */
    public function streamGenerate(
        string $prompt,
        Project $project,
        User $user,
        AIConversation $conversation,
        callable $onEvent,
        ?string $provider = null,
        ?string $model = null,
        ?string $visiblePrompt = null
    ): void {
        try {
            // $rawPrompt stored in conversation (user-visible — clean, no [Normalized intent]).
            // $prompt is the AI-facing version with optional English intent hint.
            $aiPromptRaw  = $this->sanitizeUserPrompt($prompt);
            $rawPrompt    = $this->sanitizeUserPrompt($visiblePrompt ?? $prompt);
            $prompt       = $this->normalizer->enrichPrompt($aiPromptRaw);
            $isBusinessProblem = $this->isBusinessProblemRequest($prompt);
            $isTiny       = !$isBusinessProblem && $this->isTinyEdit($prompt);
            $isFullSystem = $this->isFullSystemRequest($prompt);
            $aiProvider   = $this->aiManager->provider($provider, $user);

            // Intelligence Gate: pre-flight credit check for Credits-tier users
            if ($this->gate && $this->pricing) {
                $outcomeType = $this->pricing->outcomeFromTaskType('generation', $isTiny, $isFullSystem);
                $creditCost  = $this->pricing->costForOutcome($outcomeType);
                $check       = $this->gate->checkOperation($user, $outcomeType, $creditCost);
                if (!$check['allowed']) {
                    $onEvent(['type' => 'error', 'message' => $check['reason']]);
                    return;
                }
            }

            $systemPrompt = $isTiny
                ? $this->buildTinySystemPrompt()
                : $this->buildSystemPrompt($project);
            $history      = $this->trimHistory($conversation->getMessagesForAPI(), $isTiny ? 1 : 2);

            $this->autoTitleConversation($conversation, $rawPrompt);
            $conversation->addMessage('user', $rawPrompt);

            $startMs = (int) (microtime(true) * 1000);

            if ($isFullSystem) {
                $this->streamBlueprintDriven(
                    $prompt, $project, $conversation,
                    $aiProvider, $systemPrompt, $history, $model, $onEvent
                );
            } else {
                $this->streamSinglePhase(
                    $prompt, $project, $conversation,
                    $aiProvider, $systemPrompt, $history, $model, $onEvent, $isTiny
                );
            }

            // Deduct credits and log usage after successful generation
            $this->recordUsageAfterStream($user, $project, $provider, $model, $isTiny, $isFullSystem, $startMs);

        } catch (\Throwable $e) {
            $onEvent(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function recordUsageAfterStream(
        User $user, Project $project, ?string $provider, ?string $model,
        bool $isTiny, bool $isFullSystem, int $startMs
    ): void {
        if (!$this->gate || !$this->credits || !$this->pricing) return;

        $outcomeType = $this->pricing->outcomeFromTaskType('generation', $isTiny, $isFullSystem);
        $creditCost  = $this->pricing->costForOutcome($outcomeType);
        $responseMs  = (int) (microtime(true) * 1000) - $startMs;

        // Deduct credits for Credits-tier users (BYOK users are not charged)
        if ($this->gate->isCreditsUser($user)) {
            $this->credits->deduct(
                $user, $creditCost,
                "AI generation: {$outcomeType}",
                Project::class, $project->id,
                ['outcome' => $outcomeType, 'response_ms' => $responseMs]
            );
        }

        // Log usage for all users
        AiUsageLog::create([
            'user_id'          => $user->id,
            'project_id'       => $project->id,
            'provider'         => $provider ?? 'unknown',
            'model'            => $model ?? 'default',
            'task_type'        => 'generation',
            'credits_used'     => $this->gate->isCreditsUser($user) ? $creditCost : 0,
            'response_time_ms' => $responseMs,
            'tier_at_time'     => $this->gate->tier($user),
        ]);
    }

    private function streamSinglePhase(
        string $prompt,
        Project $project,
        AIConversation $conversation,
        mixed $aiProvider,
        string $systemPrompt,
        array $history,
        ?string $model,
        callable $onEvent,
        bool $isTiny = false
    ): void {
        $enrichedPrompt = $isTiny
            ? $this->buildPromptWithFileContext($prompt, $project, maxFiles: 2, maxChars: 4000)
            : $this->buildPromptWithFileContext($prompt, $project);
        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $enrichedPrompt]]
        );

        $onEvent(['type' => 'activity', 'icon' => '🔍', 'text' => 'Reading project context & existing files']);
        $onEvent(['type' => 'activity', 'icon' => '🧠', 'text' => 'Analyzing requirements & planning changes']);
        $onEvent(['type' => 'activity', 'icon' => '⚡', 'text' => 'Writing production-quality code']);

        $fullContent = '';
        $options     = array_merge($model ? ['model' => $model] : [], ['max_tokens' => $this->adaptiveMaxTokens($prompt, false)]);

        $milestones = [
            'database/migrations'  => ['icon' => '🗄️', 'text' => 'Creating database migrations'],
            'app/Models'           => ['icon' => '🏗️', 'text' => 'Building Eloquent models & relationships'],
            'app/Http/Controllers' => ['icon' => '🔧', 'text' => 'Writing resource controllers'],
            'app/Http/Requests'    => ['icon' => '✅', 'text' => 'Adding form validation rules'],
            'routes/web.php'       => ['icon' => '🛣️', 'text' => 'Registering routes & middleware'],
            'views/layouts'        => ['icon' => '🎨', 'text' => 'Designing app layout (sidebar, nav)'],
            'views/dashboard'      => ['icon' => '📊', 'text' => 'Building dashboard with KPI cards'],
            '/index.blade.php'     => ['icon' => '📋', 'text' => 'Generating list & table views'],
            '/create.blade.php'    => ['icon' => '➕', 'text' => 'Building create forms'],
            '/edit.blade.php'      => ['icon' => '✏️', 'text' => 'Building edit pages'],
            'database/seeders'     => ['icon' => '🌱', 'text' => 'Seeding realistic sample data'],
            'Chart.js'             => ['icon' => '📈', 'text' => 'Adding data visualizations'],
            'preview.html'         => ['icon' => '🖼️', 'text' => 'Building live SPA preview'],
        ];
        $fired = [];

        $aiProvider->stream($messages, function (string $chunk) use (&$fullContent, $onEvent, &$milestones, &$fired) {
            $fullContent .= $chunk;
            $onEvent(['type' => 'chunk', 'text' => $chunk]);
            foreach ($milestones as $key => $m) {
                if (!isset($fired[$key]) && str_contains($fullContent, $key)) {
                    $fired[$key] = true;
                    $onEvent(['type' => 'activity', 'icon' => $m['icon'], 'text' => $m['text']]);
                }
            }
        }, $options);

        $onEvent(['type' => 'activity', 'icon' => '💾', 'text' => 'Saving generated files']);

        $parsed = $this->parseResponse($fullContent, $project);
        $files  = $parsed['files'];

        if (!empty($files)) {
            $onEvent(['type' => 'files', 'files' => $files]);
        }

        // Auto-generate preview.html if the AI didn't include one
        $files = $this->ensurePreviewHtml($project, $files, $aiProvider, $model, $onEvent);

        $tokensUsed = (int) ceil(strlen($fullContent) / 4);
        $project->increment('ai_tokens_used', $tokensUsed);

        // Record this interaction in the Intelligence Ledger (closes the learning loop for streaming path)
        $this->wisdomEngine->recordDecision([
            'project_id'       => $project->id,
            'user_id'          => $project->user_id,
            'domain'           => $project->type ?? 'general',
            'lesson'           => 'Streaming generate: ' . mb_substr($prompt, 0, 200),
            'outcome'          => count($files) . ' files generated (streaming)',
            'confidence'       => count($files) > 0 ? 0.85 : 0.5,
            'tags'             => array_filter(['streaming', $project->type ?? 'general']),
            'ai_cost_estimate' => round($tokensUsed * 0.000003, 6),
        ]);

        $conversation->addMessage('assistant', $fullContent, [
            'generated_files' => array_column($files, 'path'),
            'tokens_used'     => $tokensUsed,
        ]);

        $doneMessage = $parsed['message'] ?: (count($files) > 0 ? count($files) . ' file(s) generated.' : 'Done.');

        // Warn when AI returned no files but the prompt clearly implies a code change
        if (empty($files) && $this->impliesCodeChange($prompt)) {
            $doneMessage .= "\n\n⚠️ **No changes were made.** Try describing what you want more specifically — for example: \"change the login page background to dark blue\" or \"add a logout button to the header\".";
        }

        $onEvent([
            'type'        => 'done',
            'message'     => $doneMessage,
            'model'       => 'RyaanCMS',
            'files'       => $files,
            'tokens_used' => $tokensUsed,
        ]);
    }

    private function streamMultiPhase(
        string $prompt,
        Project $project,
        AIConversation $conversation,
        mixed $aiProvider,
        string $systemPrompt,
        array $history,
        ?string $model,
        callable $onEvent
    ): void {
        $options  = array_merge($model ? ['model' => $model] : [], ['max_tokens' => 16000]);
        $allFiles = [];

        // ── Phase 1: Backend ─────────────────────────────────────────────────
        $onEvent(['type' => 'activity', 'icon' => '🔍', 'text' => 'Analyzing requirements & use cases']);
        $onEvent(['type' => 'activity', 'icon' => '🗺️', 'text' => 'Designing system architecture & data model']);
        $onEvent(['type' => 'activity', 'icon' => '🗄️', 'text' => 'Planning database schema, relationships & indexes']);
        $onEvent(['type' => 'activity', 'icon' => '⚙️', 'text' => 'Phase 1 of 2 — Building backend (migrations, models, controllers, routes)']);

        $phase1Prompt = $this->buildPhase1Prompt($prompt);
        $messages1    = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $phase1Prompt]]
        );

        $phase1Content  = '';
        $phase1Fired    = [];
        $phase1Milestones = [
            'database/migrations'  => ['icon' => '🗄️', 'text' => 'Creating database migrations'],
            'app/Models'           => ['icon' => '🏗️', 'text' => 'Building Eloquent models'],
            'app/Http/Controllers' => ['icon' => '🔧', 'text' => 'Writing resource controllers'],
            'app/Http/Requests'    => ['icon' => '✅', 'text' => 'Adding validation rules'],
            'routes/web.php'       => ['icon' => '🛣️', 'text' => 'Registering routes & middleware'],
            'database/seeders'     => ['icon' => '🌱', 'text' => 'Setting up sample data'],
        ];

        $aiProvider->stream($messages1, function (string $chunk) use (&$phase1Content, $onEvent, &$phase1Fired, &$phase1Milestones) {
            $phase1Content .= $chunk;
            $onEvent(['type' => 'chunk', 'text' => $chunk]);
            foreach ($phase1Milestones as $key => $m) {
                if (!isset($phase1Fired[$key]) && str_contains($phase1Content, $key)) {
                    $phase1Fired[$key] = true;
                    $onEvent(['type' => 'activity', 'icon' => $m['icon'], 'text' => $m['text']]);
                }
            }
        }, $options);

        $parsed1  = $this->parseResponse($phase1Content, $project);
        $allFiles = $parsed1['files'];

        if (!empty($allFiles)) {
            $onEvent(['type' => 'activity', 'icon' => '✅', 'text' => 'Backend complete — ' . count($allFiles) . ' files saved']);
            $onEvent(['type' => 'files', 'files' => $allFiles]);
        }

        // ── Phase 2: Frontend Views ───────────────────────────────────────────
        // Compact summary saves ~15k input tokens vs passing full phase1Content
        $generatedList = implode("\n", array_map(fn($f) => '  • ' . $f['path'], $allFiles));
        $phase1Summary = "Phase 1 complete. Generated " . count($allFiles) . " backend files:\n" . $generatedList;
        $phase2Prompt  = $this->buildPhase2Prompt($prompt, $generatedList);
        $messages2     = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user',      'content' => $phase1Prompt]],
            [['role' => 'assistant', 'content' => $phase1Summary]],
            [['role' => 'user',      'content' => $phase2Prompt]]
        );

        $onEvent(['type' => 'activity', 'icon' => '🎨', 'text' => 'Phase 2 of 2 — Designing UI & Blade views']);

        $phase2Content  = '';
        $phase2Fired    = [];
        $phase2Milestones = [
            'views/layouts'     => ['icon' => '🏠', 'text' => 'Creating app layout (sidebar, topbar, nav)'],
            'views/dashboard'   => ['icon' => '📊', 'text' => 'Building dashboard with KPI cards & charts'],
            '/create.blade.php' => ['icon' => '➕', 'text' => 'Designing create forms with validation'],
            '/edit.blade.php'   => ['icon' => '✏️', 'text' => 'Building edit pages with pre-filled data'],
            '/show.blade.php'   => ['icon' => '👁️', 'text' => 'Adding detail view & related records'],
            '/index.blade.php'  => ['icon' => '📋', 'text' => 'Generating searchable, paginated table views'],
            'Chart.js'          => ['icon' => '📈', 'text' => 'Adding charts & data visualizations'],
            'preview.html'      => ['icon' => '🖼️', 'text' => 'Building fully functional SPA preview'],
        ];

        $aiProvider->stream($messages2, function (string $chunk) use (&$phase2Content, $onEvent, &$phase2Fired, &$phase2Milestones) {
            $phase2Content .= $chunk;
            $onEvent(['type' => 'chunk', 'text' => $chunk]);
            foreach ($phase2Milestones as $key => $m) {
                if (!isset($phase2Fired[$key]) && str_contains($phase2Content, $key)) {
                    $phase2Fired[$key] = true;
                    $onEvent(['type' => 'activity', 'icon' => $m['icon'], 'text' => $m['text']]);
                }
            }
        }, $options);

        $parsed2     = $this->parseResponse($phase2Content, $project);
        $phase2Files = $parsed2['files'];

        if (!empty($phase2Files)) {
            $allFiles = array_merge($allFiles, $phase2Files);
            $onEvent(['type' => 'activity', 'icon' => '✅', 'text' => 'Frontend complete — ' . count($phase2Files) . ' views created']);
            $onEvent(['type' => 'files', 'files' => $phase2Files]);
        }

        // Auto-generate preview.html if neither phase included one
        $allFiles = $this->ensurePreviewHtml($project, $allFiles, $aiProvider, $model, $onEvent);

        $fileCount      = count($allFiles);
        $summaryMsg     = $parsed2['message'] ?: $parsed1['message'] ?: 'Backend and frontend fully generated.';
        $displayMessage = "Complete system generated — {$fileCount} files created.\n\n{$summaryMsg}";

        $tokensUsed = (int) ceil((strlen($phase1Content ?? '') + strlen($phase2Content)) / 4);
        $project->increment('ai_tokens_used', $tokensUsed);

        $conversation->addMessage('assistant', $phase2Content, [
            'generated_files' => array_column($allFiles, 'path'),
            'tokens_used'     => $tokensUsed,
        ]);

        $onEvent([
            'type'        => 'done',
            'message'     => $displayMessage,
            'model'       => 'RyaanCMS',
            'files'       => $allFiles,
            'tokens_used' => $tokensUsed,
        ]);
    }

    /**
     * If no preview.html was produced by the main generation, fire a focused
     * follow-up AI call that generates just that one file. Non-fatal on failure.
     * Only triggers when 5+ files were generated (full system build) to avoid
     * burning extra API credits on small single-file changes.
     */
    private function ensurePreviewHtml(
        Project $project,
        array $allFiles,
        mixed $aiProvider,
        ?string $model,
        callable $onEvent
    ): array {
        $hasPreview = collect($allFiles)->contains(
            fn($f) => strtolower(basename($f['path'] ?? $f['name'] ?? '')) === 'preview.html'
        );

        if ($hasPreview) return $allFiles;

        // Skip for small edits — only auto-generate preview for real system builds (5+ files)
        if (count($allFiles) < 5) return $allFiles;

        // Also skip if a preview.html already exists in the project from a previous build
        if ($project->files()->where('name', 'preview.html')->where('type', 'file')->exists()) {
            return $allFiles;
        }

        $onEvent(['type' => 'activity', 'icon' => '🖼️', 'text' => 'Generating live preview…']);

        $fileList    = collect($allFiles)->pluck('path')->take(20)->implode(', ');
        $projectName = $project->name;
        $projectType = $project->type ?? 'web application';

        $messages = [
            ['role' => 'system', 'content' => config('ai.system_prompt')],
            ['role' => 'user',   'content' => <<<PPROMPT
Project: {$projectName} ({$projectType})
Generated files: {$fileList}

Create a preview.html — a FULLY FUNCTIONAL Single Page Application that demonstrates the complete system.
Runs entirely in the browser. No PHP server required.

━━━ REQUIRED FEATURES ━━━
1. LOGIN SCREEN
   • Email: admin@demo.com  Password: password
   • Show validation error for wrong credentials
   • Store auth state in localStorage

2. DASHBOARD (landing page after login)
   • KPI cards: count of each entity with icon and color
   • "Recent [Entity]" table showing last 5 records
   • Quick action buttons to add new records
   • Bar or line chart using Chart.js CDN

3. ENTITY PAGES (one per main entity in the system)
   • Data table with: search bar (live filter), status badges, action buttons
   • Add button → opens modal form with all fields + validation
   • Edit button → opens same modal pre-populated
   • Delete button → shows "Are you sure?" confirmation modal
   • Shows record count "Showing X of Y records"

4. DATA QUALITY
   • 6-10 realistic, domain-specific sample records per entity
   • Use real names, real-looking data (not "John Doe" / "test@test.com")
   • Records persist to localStorage (survive page refresh)

5. UI QUALITY
   • Sidebar: app logo + all nav links with icons (active state highlighted)
   • Topbar: breadcrumb showing current page + admin avatar + logout
   • Mobile responsive with hamburger menu
   • Consistent indigo/slate color scheme throughout

CDN (include these exact tags):
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

Use Alpine.js x-data="app()" for all state management.
Return ONLY valid JSON (no markdown, no explanation outside the JSON):
{"files":[{"path":"preview.html","content":"<!DOCTYPE html>...COMPLETE HTML..."}],"summary":"Fully functional SPA preview with login, dashboard, complete CRUD for all entities, and Chart.js analytics."}
PPROMPT],
        ];

        $options = array_merge($model ? ['model' => $model] : [], ['max_tokens' => 12000]);

        try {
            $response = $aiProvider->chat($messages, $options);
            $parsed   = $this->parseResponse($response['content'], $project);

            if (!empty($parsed['files'])) {
                $allFiles = array_merge($allFiles, $parsed['files']);
                $onEvent(['type' => 'files',    'files' => $parsed['files']]);
                $onEvent(['type' => 'activity', 'icon' => '✅', 'text' => 'Live preview ready']);
            }
        } catch (\Throwable) {
            // Preview failure is non-fatal — main build already succeeded
        }

        return $allFiles;
    }

    private function buildPhase1Prompt(string $prompt): string
    {
        $isComplete = $this->isCompleteSystemRequest($prompt);
        $isFe       = $this->isFrontendOnlyRequest($prompt);

        if ($isFe) {
            return $prompt . "\n\n" . <<<'PHASE1FE'

══════════════════════════════════════════════════
🎨  GENERATION PHASE 1 OF 2 — STRUCTURE & FOUNDATION
══════════════════════════════════════════════════
This is a frontend / UI build. Generate the foundational files:
  • Main HTML file (index.html or preview.html) — complete structure, hero section, navigation
  • CSS foundation — Tailwind CDN + any custom styles needed
  • Core JS — Alpine.js CDN + any interactivity setup
  • All static assets references (fonts, icons via CDN)

Make it visually complete and professional. Phase 2 will add remaining sections.
PHASE1FE;
        }

        $completeExtra = $isComplete ? "\n\n⚠️  COMPLETE SYSTEM DETECTED — AUTH IS MANDATORY IN THIS PHASE. Do not skip LoginController, RegisterController, User model with roles, auth migrations, or auth routes. These MUST be in this response.\n" : '';

        return $prompt . $completeExtra . "\n\n" . <<<'PHASE1'

══════════════════════════════════════════════════
⚙️  GENERATION PHASE 1 OF 2 — BACKEND + AUTH FOUNDATION
══════════════════════════════════════════════════
Generate ONLY the following backend files in this response:

AUTHENTICATION (MANDATORY — include for EVERY complete system):
  • database/migrations/xxxx_create_users_table.php — columns: id, name, email, password,
    role (enum: admin, staff/domain-specific roles), phone, is_active, email_verified_at, timestamps
  • app/Models/User.php — role enum constants, isAdmin()/hasRole() methods, fillable, casts, relationships
  • app/Http/Controllers/Auth/LoginController.php — login with validation + role-based redirect after login
  • app/Http/Controllers/Auth/RegisterController.php — registration with default role assignment
  • app/Http/Controllers/Auth/ForgotPasswordController.php — password reset email request
  • database/seeders/DatabaseSeeder.php — creates admin user: admin@demo.com / password123
    PLUS 5-8 realistic records per entity (hotel names, real guest names, real prices, etc.)

CORE BUSINESS ENTITIES:
  • database/migrations/ — one timestamped migration per entity (up() + down(), FK constraints, indexes)
  • app/Models/ — one Eloquent model per entity ($fillable, $casts, all relationships, local scopes)
  • app/Http/Controllers/ — one ResourceController per entity (ALL 7 methods, full implementation)
  • app/Http/Requests/ — StoreRequest + UpdateRequest per entity (complete validation rules + messages)
  • routes/web.php — COMPLETE routes file:
      Route::get('/', fn() => view('welcome'))->name('home');       // public landing
      Auth::routes(); OR manual auth routes (login, register, logout, forgot-password)
      Route::middleware('auth')->group(function () {
          Route::get('/dashboard', ...)->name('dashboard');
          Route::resource('rooms', RoomController::class); // etc. for every entity
      });

All PHP code must be 100% complete and production-ready.
Do NOT generate Blade views, landing page, or preview.html yet — those come in Phase 2.
PHASE1;
    }

    private function buildPhase2Prompt(string $prompt, string $generatedList): string
    {
        $isComplete     = $this->isCompleteSystemRequest($prompt);
        $isFrontendOnly = $this->isFrontendOnlyRequest($prompt);

        if ($isFrontendOnly) {
            return $prompt . "\n\n" . <<<PHASE2FE

══════════════════════════════════════════════════
🎨  GENERATION PHASE 2 OF 2 — COMPLETE UI & CONTENT
══════════════════════════════════════════════════
Files already generated:
{$generatedList}

Now complete the full UI. Generate ALL remaining sections and files:
  • All page sections not yet built (features, pricing, testimonials, FAQ, footer, etc.)
  • Any additional pages (about, contact, blog list, etc.)
  • preview.html (or update index.html) — COMPLETE, fully functional with all sections
  • All interactivity: mobile menu, accordions, tabs, modals, form validation
  • Professional animations: scroll reveal, hover effects, transitions
  • Real compelling copy — no placeholders anywhere

Every file must be 100% complete and production-ready.
PHASE2FE;
        }

        $completeMandate = $isComplete ? "\n⚠️  THIS IS A COMPLETE SYSTEM REQUEST — YOU MUST GENERATE:\n  ✅ resources/views/welcome.blade.php (public landing page — NO skipping, NO deferring)\n  ✅ resources/views/auth/login.blade.php\n  ✅ resources/views/auth/register.blade.php\n  ✅ resources/views/auth/forgot-password.blade.php\n  ✅ ALL entity index+create+edit+show views\n  ✅ preview.html with working landing page + login + full CRUD\n  Omitting ANY of these is a critical failure.\n" : '';

        return $prompt . $completeMandate . "\n\n" . <<<PHASE2

══════════════════════════════════════════════════
🎨  GENERATION PHASE 2 OF 2 — COMPLETE FRONTEND
══════════════════════════════════════════════════
Backend files already generated in Phase 1:
{$generatedList}

Now generate ALL frontend files — 100% complete, production-ready, no skeletons:

PUBLIC LANDING PAGE (MANDATORY — do this first):
  • resources/views/welcome.blade.php — complete standalone public marketing page:
      Sections (ALL required):
        1. Sticky navigation — logo + "Login" and "Register" buttons (links to auth routes)
        2. Hero — bold headline + compelling subheadline + dual CTA buttons + hero image/illustration
        3. Features — 6+ features in a responsive grid (icon + title + description per feature)
        4. How It Works — numbered 3-step process with icons
        5. Statistics/Social proof — 3-4 impressive real numbers (e.g. "10,000+ rooms managed")
        6. Testimonials — 3-4 realistic named quotes with role and company
        7. CTA section — full-width background + "Get Started Free" → route('register')
        8. Footer — logo + nav links + contact info + social icons + copyright
      Style: Tailwind CDN + Alpine.js CDN, gradient backgrounds, hover animations, mobile-first
      Does NOT use @extends('layouts.app') — it is publicly accessible

AUTH VIEWS (MANDATORY):
  • resources/views/auth/login.blade.php — professional login form:
      Logo + system name at top, email + password fields, "Remember me" checkbox,
      "Forgot your password?" link → route('password.request'),
      Submit button with loading state, "Don't have an account? Register" link
      Centered card layout, gradient background, does NOT extend layouts.app
  • resources/views/auth/register.blade.php — registration form:
      All fields from users migration (name, email, password, confirm password, role if applicable),
      terms checkbox, Submit + "Already have an account? Login" link
  • resources/views/auth/forgot-password.blade.php — email request form with success state

LAYOUT & SHELL:
  • resources/views/layouts/app.blade.php — fixed sidebar with:
      Logo at top + system name, ALL entity nav links with Font Awesome icons,
      active state highlighting (current route detection), collapse on mobile (hamburger),
      Topbar: breadcrumb left + user avatar/name/role right + logout dropdown
      Flash messages: success (green) + error (red) + info (blue) alert banners
      @yield('content') main area, @stack('scripts') for per-page JS

DASHBOARD:
  • resources/views/dashboard.blade.php — professional analytics dashboard:
      4+ KPI stat cards (real DB queries: count, sum, today's records, etc.) with trend % badge
      Chart.js line/area chart (last 30 days data, real query) in a card
      Chart.js doughnut/bar chart (distribution by type/status) in a card
      Two "Recent [Entity]" tables showing latest 5-8 records with status badges + action links
      Quick-action button row for the most common tasks

PER ENTITY — generate ALL four views for EVERY entity in Phase 1:
  • resources/views/{entity}/index.blade.php:
      Page header (title + "Add New" button), search input (Alpine.js live filter),
      responsive data table (sortable headers, status badges, edit/show/delete per row),
      pagination links, bulk select + bulk delete button, export CSV button, empty state
  • resources/views/{entity}/create.blade.php:
      Breadcrumb, form card, ALL fields with labels + validation error display,
      select/radio/checkbox for enum/FK fields, cancel button → index route
  • resources/views/{entity}/edit.blade.php:
      Same as create but with @method('PUT'), pre-filled via {{ old('field', \$model->field) }}
  • resources/views/{entity}/show.blade.php:
      Detail card showing all fields with labels, related records if applicable,
      Edit + Delete action buttons with @method('DELETE') confirm form

PREVIEW SPA:
  • preview.html — MANDATORY fully functional SPA (Alpine.js + Tailwind CDN + Chart.js):
      1. PUBLIC LANDING (shown by default when not logged in):
           Renders the welcome page — hero, features, how-it-works, testimonials
           "Login" button → switches to login screen
      2. LOGIN SCREEN — admin@demo.com / password123 → navigates to dashboard
      3. APP SHELL — sidebar + topbar + main content area (same as Blade layout)
      4. ALL MODULE PAGES — full CRUD with localStorage persistence, search, pagination
      5. DASHBOARD — live KPI counts from localStorage, Chart.js charts with sample data

ALL Blade views must:
  • @extends('layouts.app'), @section('content'), @endsection
  • Tailwind utility classes — professional, consistent design
  • Alpine.js for: delete confirmation, live search, dropdown menus
  • @csrf on every form, @method('PUT'/'DELETE') where needed
  • route() helpers for ALL links — never hardcoded paths
  • Mobile-responsive with sm:/md:/lg: breakpoints
PHASE2;
    }

    private function isFrontendOnlyRequest(string $prompt): bool
    {
        $lower = strtolower($prompt);
        // Only mark as frontend-only when the request is clearly for static files.
        // "blog" and "portfolio" are intentionally excluded — they should be full Laravel apps.
        $frontendTerms = [
            'landing page', 'landing', 'homepage', 'home page',
            'template', 'theme', 'plugin', 'widget', 'ui kit',
            'email template', 'newsletter', 'html component',
        ];
        foreach ($frontendTerms as $term) {
            if (str_contains($lower, $term)) return true;
        }
        return false;
    }

    /**
     * Detect when the user explicitly wants a COMPLETE system (auth + landing page + all modules).
     */
    private function isCompleteSystemRequest(string $prompt): bool
    {
        $lower = strtolower($prompt);

        // Explicit completeness signals
        $completenessWords = ['complete', 'full', 'entire', 'comprehensive', 'all-in-one', 'end-to-end'];
        foreach ($completenessWords as $w) {
            if (str_contains($lower, $w)) return true;
        }

        // Domain nouns that inherently imply a full system
        $domainSystems = [
            'hotel management', 'hospital management', 'school management', 'clinic management',
            'inventory management', 'restaurant management', 'hr management', 'payroll system',
            'crm system', 'erp system', 'booking system', 'reservation system',
            'pharmacy system', 'library system', 'gym management', 'real estate system',
        ];
        foreach ($domainSystems as $d) {
            if (str_contains($lower, $d)) return true;
        }

        return false;
    }
}
