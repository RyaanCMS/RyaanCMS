<?php

namespace App\Services\AI;

class AIRouter
{
    // Patterns ordered: most expensive first so they short-circuit correctly
    private array $patterns = [
        'architecture' => [
            'build system', 'create app', 'full system', 'complete platform',
            'saas platform', 'marketplace system', 'erp system', 'crm system',
            'build full', 'entire application', 'full stack', 'whole system',
            'build a crm', 'build a saas', 'build a marketplace', 'develop a',
        ],
        'business_logic' => [
            'payment logic', 'checkout flow', 'accounting', 'invoice system',
            'billing system', 'workflow', 'approval process', 'multi-tenant',
            'subscription plan', 'commission', 'tax calculation', 'ledger',
            'journal entry', 'payroll', 'bank reconciliation',
        ],
        'optimization' => [
            'optimize', 'performance', 'slow query', 'speed up', 'db index',
            'query optimization', 'refactor', 'n+1', 'memory usage', 'bottleneck',
        ],
        'security' => [
            'security audit', 'vulnerability', 'rbac', 'permission system',
            'role system', 'access control', 'authorization rule', 'audit trail',
            'sql injection', 'xss', 'csrf', 'sanitize',
        ],
        'simple_logic' => [
            'add function', 'helper method', 'simple validation', 'add middleware',
            'format date', 'send email', 'add event', 'add listener', 'add command',
            'add scope', 'add accessor', 'add mutator',
        ],
        'ui_form' => [
            'create form', 'add form', 'input field', 'form validation',
            'modal form', 'dialog', 'dropdown select', 'checkbox group',
            'form component', 'login form', 'registration form',
        ],
        'ui_scaffold' => [
            'navbar', 'sidebar', 'header component', 'footer', 'layout',
            'landing page', 'home page', 'card component', 'list component',
            'table component', 'dashboard layout', 'admin panel layout',
        ],
        'crud' => [
            'create crud', 'generate crud', 'new table', 'add migration',
            'new model', 'crud for', 'resource controller', 'simple crud',
            'add column', 'new entity', 'generate resource',
        ],
    ];

    // tier: none = no AI (template/module handles it)
    //       free = Groq/Gemini free tier
    //       cheap = Claude Haiku / GPT mini
    //       premium = Claude Sonnet / GPT-4
    private array $tiers = [
        'architecture'   => ['tier' => 'premium', 'max_tokens' => 16000, 'history_turns' => 3],
        'business_logic' => ['tier' => 'premium', 'max_tokens' => 8000,  'history_turns' => 3],
        'optimization'   => ['tier' => 'cheap',   'max_tokens' => 4000,  'history_turns' => 2],
        'security'       => ['tier' => 'cheap',   'max_tokens' => 4000,  'history_turns' => 2],
        'simple_logic'   => ['tier' => 'free',    'max_tokens' => 3000,  'history_turns' => 1],
        'ui_form'        => ['tier' => 'free',    'max_tokens' => 2500,  'history_turns' => 1],
        'ui_scaffold'    => ['tier' => 'none',    'max_tokens' => 0,     'history_turns' => 0],
        'crud'           => ['tier' => 'none',    'max_tokens' => 0,     'history_turns' => 0],
        'unknown'        => ['tier' => 'cheap',   'max_tokens' => 4000,  'history_turns' => 2],
    ];

    public function classifyTask(string $prompt): string
    {
        $lower = mb_strtolower($prompt);
        foreach ($this->patterns as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) return $type;
            }
        }
        return 'unknown';
    }

    public function tier(string $taskType): string
    {
        return $this->tiers[$taskType]['tier'] ?? 'cheap';
    }

    public function tokenBudget(string $taskType): int
    {
        return $this->tiers[$taskType]['max_tokens'] ?? 4000;
    }

    public function historyTurns(string $taskType): int
    {
        return $this->tiers[$taskType]['history_turns'] ?? 2;
    }

    public function requiresAI(string $taskType): bool
    {
        return ($this->tiers[$taskType]['tier'] ?? 'cheap') !== 'none';
    }

    /** Returns preferred model config override for the task tier, or null (= use user default) */
    public function preferredModel(string $taskType): ?array
    {
        $tier = $this->tier($taskType);
        $cfg  = config('ai.cost_reduction', []);

        return match ($tier) {
            'none'    => null,
            'free'    => $cfg['free_providers']['groq'] ?? $cfg['free_providers']['gemini_free'] ?? null,
            'cheap'   => $cfg['cheap_models']['claude'] ?? null,
            'premium' => null, // use the user's highest-quality default
            default   => null,
        };
    }

    /** Single entry point: classify + resolve all routing metadata */
    public function route(string $prompt): array
    {
        $taskType = $this->classifyTask($prompt);
        return [
            'task_type'     => $taskType,
            'tier'          => $this->tier($taskType),
            'requires_ai'   => $this->requiresAI($taskType),
            'max_tokens'    => $this->tokenBudget($taskType),
            'history_turns' => $this->historyTurns($taskType),
            'model_hint'    => $this->preferredModel($taskType),
        ];
    }
}
