<?php

namespace App\Services\AI;

use App\Models\ProjectWisdom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * WisdomEngine — Organizational Memory & Learning System
 *
 * Turns every project into accumulated intelligence:
 * - Stores project lessons (autopsies) to DB
 * - Retrieves relevant past lessons for new builds
 * - Extracts patterns across projects
 * - Builds compact wisdom briefs for AI injection
 * - Records decision outcomes and promotes patterns to rules
 *
 * Fallback-safe: if project_wisdom table doesn't exist, uses static config wisdom only.
 * The Intelligence Ledger / Decision Outcomes system is also handled here.
 */
class WisdomEngine
{
    private array $wisdomPatterns;
    private array $mentalModels;
    private array $aiReplacement;
    private array $intelligenceLibrary;
    private bool  $dbAvailable;

    public function __construct()
    {
        $this->wisdomPatterns = config('kb.wisdom_patterns', []);
        $this->mentalModels   = config('kb.mental_models', []);
        $this->aiReplacement  = config('kb.ai_replacement', []);
        $this->intelligenceLibrary = config('kb.intelligence_library', []);
        $this->dbAvailable    = $this->checkDbAvailability();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC API — WISDOM RETRIEVAL
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build a compact wisdom brief for AI injection (~300-500 tokens).
     * Combines static wisdom patterns + relevant DB lessons.
     */
    public function buildWisdomBrief(string $domain, string $prompt = ''): string
    {
        $lines = ["=== WISDOM BRIEF (from real project experience) ==="];

        // Static domain wisdom
        $staticLessons = $this->getStaticLessons($domain, $prompt);
        if ($staticLessons) {
            $lines[] = "DOMAIN LESSONS ({$domain}):";
            foreach (array_slice($staticLessons, 0, 4) as $lesson) {
                $lines[] = "  ✦ " . $lesson['lesson'];
                $lines[] = "    → " . $lesson['recommendation'];
            }
        }

        // Cross-domain universal patterns
        $architectureLessons = array_slice($this->wisdomPatterns['architecture'] ?? [], 0, 2);
        if ($architectureLessons) {
            $lines[] = "ARCHITECTURE LESSONS:";
            foreach ($architectureLessons as $lesson) {
                $lines[] = "  ✦ " . $lesson['lesson'];
            }
        }

        $intelligencePatterns = $this->getRelevantIntelligencePatterns($prompt);
        if ($intelligencePatterns) {
            $lines[] = 'BUSINESS INTELLIGENCE PATTERNS:';
            foreach ($intelligencePatterns as $pattern) {
                $lines[] = '  - ' . $pattern;
            }
        }

        // DB-backed lessons if available
        if ($this->dbAvailable) {
            $dbLessons = $this->getDbLessons($domain, 3);
            if ($dbLessons->count() > 0) {
                $lines[] = "FROM PAST PROJECTS (this domain):";
                foreach ($dbLessons as $w) {
                    if ($w->lesson) {
                        $lines[] = "  ✦ " . $w->lesson;
                        if ($w->recommendation) $lines[] = "    → " . $w->recommendation;
                    }
                }
            }
        }

        // Mental model for this decision
        $model = $this->suggestMentalModel($domain, $prompt);
        if ($model) {
            $lines[] = "APPLY THIS MENTAL MODEL: {$model['name']} — {$model['question']}";
        }

        $lines[] = "=== END WISDOM BRIEF ===";

        return implode("\n", $lines);
    }

    /**
     * Get lessons relevant to a domain and optional prompt.
     */
    public function getLessonsForDomain(string $domain): array
    {
        $static = $this->getStaticLessons($domain);
        $db     = $this->dbAvailable ? $this->getDbLessons($domain, 10)->toArray() : [];
        return array_merge($static, $db);
    }

    /**
     * Return compact static intelligence patterns relevant to the current prompt.
     */
    private function getRelevantIntelligencePatterns(string $prompt): array
    {
        $lower = strtolower($prompt);
        $lines = [];

        foreach ($this->intelligenceLibrary['success_patterns'] ?? [] as $key => $pattern) {
            if ($this->patternMatchesPrompt($key, $pattern, $lower)) {
                $lines[] = $pattern['pattern'] ?? '';
            }
        }

        foreach ($this->intelligenceLibrary['failure_patterns'] ?? [] as $key => $pattern) {
            if ($this->patternMatchesPrompt($key, $pattern, $lower)) {
                $lines[] = 'Avoid: ' . ($pattern['mistake'] ?? '') . ' Fix: ' . ($pattern['fix'] ?? '');
            }
        }

        return array_values(array_filter(array_slice($lines, 0, 4)));
    }

    private function patternMatchesPrompt(string $key, array $pattern, string $lowerPrompt): bool
    {
        $haystack = strtolower($key . ' ' . implode(' ', array_filter([
            $pattern['pattern'] ?? '',
            $pattern['mistake'] ?? '',
            $pattern['fix'] ?? '',
        ])));

        foreach (explode(' ', str_replace(['_', '-'], ' ', $key)) as $word) {
            if (strlen($word) > 3 && str_contains($lowerPrompt, $word)) {
                return true;
            }
        }

        foreach (['crm', 'inventory', 'stock', 'return', 'ecommerce', 'cod', 'lead', 'follow'] as $signal) {
            if (str_contains($lowerPrompt, $signal) && str_contains($haystack, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the AI routing decision - should this go to AI or to a rule/cache?
     */
    public function getAiRoutingDecision(string $taskType): array
    {
        $neverAi = $this->aiReplacement['never_use_ai_for'] ?? [];
        if (isset($neverAi[$taskType])) {
            return [
                'use_ai'       => false,
                'use_instead'  => $neverAi[$taskType]['use_instead'] ?? 'Rule engine',
                'savings'      => $neverAi[$taskType]['savings'] ?? 'Tokens saved',
                'routing_step' => $this->getRoutingStep($taskType),
            ];
        }

        $alwaysAi = $this->aiReplacement['always_use_ai_for'] ?? [];
        if (isset($alwaysAi[$taskType])) {
            return [
                'use_ai'       => true,
                'reason'       => $alwaysAi[$taskType],
                'routing_step' => 7,
            ];
        }

        return ['use_ai' => true, 'routing_step' => 7, 'reason' => 'Default: use AI for unknown task types'];
    }

    /**
     * Get mental model suggestion for a domain + decision context.
     */
    public function suggestMentalModel(string $domain, string $context = ''): ?array
    {
        $pairings = $this->mentalModels['decision_matrix_pairing'] ?? [];
        $models   = $this->mentalModels['models'] ?? [];

        // Map domain to decision type
        $decisionType = match(true) {
            str_contains($context, 'architecture') || str_contains($context, 'design') => 'architecture_choice',
            str_contains($context, 'feature') || str_contains($context, 'priorit')      => 'feature_prioritization',
            str_contains($context, 'platform') || str_contains($context, 'marketplace') => 'platform_strategy',
            str_contains($context, 'debt') || str_contains($context, 'refactor')        => 'technical_debt_decision',
            str_contains($context, 'buy') || str_contains($context, 'package')          => 'build_vs_buy_decision',
            str_contains($context, 'mvp') || str_contains($context, 'scope')            => 'mvp_scoping',
            str_contains($context, 'scal') || str_contains($context, 'performance')     => 'scaling_decision',
            default                                                                       => 'feature_prioritization',
        };

        $modelNames = explode(' + ', $pairings[$decisionType] ?? '');
        $modelNames = array_map(fn($m) => trim(str_replace('Use:', '', $m)), $modelNames);
        $firstName  = strtolower(str_replace([' ', '-'], '_', $modelNames[0] ?? ''));

        return $models[$firstName] ?? ($models['opportunity_cost'] ?? null);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC API — INTELLIGENCE LEDGER
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Record a project lesson / autopsy entry to the Intelligence Ledger.
     */
    public function recordLesson(array $data): ?object
    {
        if (!$this->dbAvailable) return null;

        try {
            return ProjectWisdom::create([
                'id'             => (string) Str::uuid(),
                'project_id'     => $data['project_id'] ?? null,
                'user_id'        => $data['user_id'] ?? null,
                'domain'         => $data['domain'] ?? 'general',
                'entry_type'     => $data['entry_type'] ?? 'lesson',
                'lesson'         => $data['lesson'] ?? null,
                'what_went_right'=> $data['what_went_right'] ?? null,
                'what_went_wrong'=> $data['what_went_wrong'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'outcome'        => $data['outcome'] ?? null,
                'confidence'     => $data['confidence'] ?? 0.7,
                'tags'           => array_values(array_filter((array) ($data['tags'] ?? []))),
                'is_rule_candidate' => $data['is_rule_candidate'] ?? false,
                'rule_trigger'   => $data['rule_trigger'] ?? null,
                'ai_cost_estimate' => $data['ai_cost_estimate'] ?? null,
                'ai_cost_saved'  => $data['ai_cost_saved'] ?? null,
                'reuse_count'    => 0,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Record a decision + expected outcome (Intelligence Ledger entry).
     * Accepts 'lesson' or 'decision' key for the lesson field (aliases).
     */
    public function recordDecision(array $data): ?object
    {
        if (!$this->dbAvailable) return null;

        try {
            return ProjectWisdom::create([
                'id'               => (string) Str::uuid(),
                'project_id'       => $data['project_id'] ?? null,
                'user_id'          => $data['user_id'] ?? null,
                'domain'           => $data['domain'] ?? 'general',
                'entry_type'       => 'decision',
                'lesson'           => $data['lesson'] ?? $data['decision'] ?? null,
                'recommendation'   => $data['recommendation'] ?? $data['reason'] ?? null,
                'outcome'          => $data['outcome'] ?? $data['expected_outcome'] ?? null,
                'confidence'       => $data['confidence'] ?? 0.8,
                'tags'             => array_values(array_filter((array) ($data['tags'] ?? []))),
                'ai_cost_estimate' => $data['ai_cost_estimate'] ?? null,
                'ai_cost_saved'    => $data['ai_cost_saved'] ?? null,
                'reuse_count'      => 0,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Update the actual outcome of a recorded decision (closes the learning loop).
     */
    public function recordOutcome(string $wisdomId, string $actualOutcome, float $confidence = 0.9): bool
    {
        if (!$this->dbAvailable) return false;

        try {
            ProjectWisdom::where('id', $wisdomId)->update([
                'outcome'       => $actualOutcome,
                'confidence'    => $confidence,
                'is_rule_candidate' => true,
            ]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Find similar past projects and their lessons.
     */
    public function findSimilarProjectLessons(string $domain, int $limit = 5): array
    {
        if (!$this->dbAvailable) {
            return $this->getStaticLessons($domain, '', $limit);
        }

        try {
            $db = $this->getDbLessons($domain, $limit);
            $static = $this->getStaticLessons($domain, '', 3);
            return array_merge($db->toArray(), $static);
        } catch (\Throwable) {
            return $this->getStaticLessons($domain, '', $limit);
        }
    }

    /**
     * Get all rule candidates — lessons that have been validated and could become rules.
     */
    public function getRuleCandidates(string $domain = ''): array
    {
        if (!$this->dbAvailable) return [];

        try {
            $query = ProjectWisdom::where('is_rule_candidate', true)
                ->where('confidence', '>=', 0.8)
                ->orderByDesc('reuse_count');

            if ($domain) {
                $query->where('domain', $domain);
            }

            return $query->limit(20)->get()->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Get statistics about accumulated wisdom.
     */
    public function getWisdomStats(): array
    {
        $static = [
            'static_lessons'    => collect($this->wisdomPatterns)->flatten(1)->count(),
            'mental_models'     => count($this->mentalModels['models'] ?? []),
            'ai_replacement_rules' => count($this->aiReplacement['never_use_ai_for'] ?? []),
        ];

        if (!$this->dbAvailable) {
            return array_merge($static, ['db_lessons' => 0, 'rule_candidates' => 0]);
        }

        try {
            return array_merge($static, [
                'db_lessons'      => ProjectWisdom::count(),
                'rule_candidates' => ProjectWisdom::where('is_rule_candidate', true)->count(),
                'domains_covered' => ProjectWisdom::distinct('domain')->count('domain'),
                'total_ai_saved'  => ProjectWisdom::sum('ai_cost_saved'),
            ]);
        } catch (\Throwable) {
            return array_merge($static, ['db_lessons' => 0, 'rule_candidates' => 0]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function getStaticLessons(string $domain, string $prompt = '', int $limit = 6): array
    {
        $lessons = [];

        // Domain-specific static wisdom
        $domainLessons = $this->wisdomPatterns[$domain] ?? [];
        $lessons       = array_merge($lessons, $domainLessons);

        // Security wisdom always relevant
        $security = array_slice($this->wisdomPatterns['security'] ?? [], 0, 2);
        $lessons  = array_merge($lessons, $security);

        // Bangladesh-specific if prompt mentions BD context
        $lp = strtolower($prompt);
        if (str_contains($lp, 'bangladesh') || str_contains($lp, 'bkash') || str_contains($lp, 'nagad')) {
            $bdLessons = $this->wisdomPatterns['bangladesh'] ?? [];
            $lessons   = array_merge($lessons, $bdLessons);
        }

        return array_slice($lessons, 0, $limit);
    }

    private function getDbLessons(string $domain, int $limit = 5): object
    {
        return ProjectWisdom::where('domain', $domain)
            ->whereNotNull('lesson')
            ->orderByDesc('reuse_count')
            ->orderByDesc('confidence')
            ->limit($limit)
            ->get();
    }

    private function checkDbAvailability(): bool
    {
        try {
            DB::connection()->getPdo();
            return DB::getSchemaBuilder()->hasTable('project_wisdom');
        } catch (\Throwable) {
            return false;
        }
    }

    private function getRoutingStep(string $taskType): int
    {
        $crudTypes = ['standard_crud_controller', 'standard_crud_model', 'database_migrations', 'api_resources'];
        if (in_array($taskType, $crudTypes)) return 3;

        $blueprintTypes = ['known_domain_schema', 'standard_module_blueprint', 'repeated_similar_project'];
        if (in_array($taskType, $blueprintTypes)) return 2;

        return 4;
    }
}
