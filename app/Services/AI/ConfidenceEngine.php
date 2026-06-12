<?php

namespace App\Services\AI;

/**
 * ConfidenceEngine — Transparent recommendation scoring.
 *
 * Every recommendation RyaanCMS makes (blueprint, module, component, workflow,
 * rules) now carries a confidence score, a human-readable reason, and a
 * declaration of which source assets were used and whether AI was involved.
 *
 * Core Principle: "Every recommendation must show confidence, reason, source."
 */
class ConfidenceEngine
{
    /** Blueprint source → baseline confidence */
    private const BLUEPRINT_CONFIDENCE = [
        'blueprint_library' => 0.97,
        'problem_mapper'    => 0.95,
        'genome_engine'     => 0.88,
        'ai_discovery'      => 0.72,
        'custom'            => 0.50,
    ];

    /** Domain match type → component confidence */
    private const COMPONENT_CONFIDENCE = [
        'exact_keyword'     => 0.99,
        'partial_keyword'   => 0.85,
        'search_result'     => 0.70,
        'fallback'          => 0.50,
    ];

    /**
     * Confidence for a blueprint selection.
     *
     * @param  string $source          'blueprint_library' | 'problem_mapper' | 'genome_engine' | 'ai_discovery'
     * @param  float  $intentConfidence  0.0–1.0 from IntentEngine::detect()
     * @param  array  $blueprint        The assembled blueprint
     */
    public function forBlueprint(string $source, float $intentConfidence, array $blueprint = []): array
    {
        $base   = self::BLUEPRINT_CONFIDENCE[$source] ?? 0.60;
        // Boost if intent and genome confidence agree
        $score  = min(1.0, round(($base * 0.7) + ($intentConfidence * 0.3), 2));
        $aiUsed = $source === 'ai_discovery';

        $reasons = [
            'blueprint_library' => 'Exact match in pre-built library of 100 money blueprints',
            'problem_mapper'    => 'Matched a known business problem pattern (zero AI tokens)',
            'genome_engine'     => 'Assembled from 500-industry genome engine (zero AI tokens)',
            'ai_discovery'      => 'Novel domain — AI discovery used (~300 tokens)',
            'custom'            => 'No strong match found; custom blueprint created',
        ];

        return [
            'score'       => $score,
            'percent'     => (int) ($score * 100),
            'reason'      => $reasons[$source] ?? 'Unknown source',
            'source'      => $source,
            'ai_used'     => $aiUsed,
            'tokens_used' => $aiUsed ? ($blueprint['tokens_used'] ?? 300) : 0,
            'library_key' => $blueprint['library_key'] ?? null,
            'app_type'    => $blueprint['app_type'] ?? 'custom',
        ];
    }

    /**
     * Confidence for a component selection.
     *
     * @param  string $matchType  'exact_keyword' | 'partial_keyword' | 'search_result' | 'fallback'
     * @param  string $componentKey  The matched component key
     * @param  int    $tokensSaved  How many tokens this component saves
     */
    public function forComponent(string $matchType, string $componentKey, int $tokensSaved = 500): array
    {
        $score = self::COMPONENT_CONFIDENCE[$matchType] ?? 0.60;

        $reasons = [
            'exact_keyword'   => "Direct keyword match to pre-built component '{$componentKey}' — no AI needed",
            'partial_keyword' => "Partial keyword match to component '{$componentKey}'",
            'search_result'   => "Found via component search for '{$componentKey}'",
            'fallback'        => "Closest available component selected",
        ];

        return [
            'score'        => $score,
            'percent'      => (int) ($score * 100),
            'reason'       => $reasons[$matchType] ?? 'Component registry match',
            'source'       => 'component_registry',
            'ai_used'      => false,
            'tokens_saved' => $tokensSaved,
            'component'    => $componentKey,
        ];
    }

    /**
     * Confidence for a workflow selection.
     *
     * @param  string $domain      Domain key ('ecommerce', 'hospital', etc.)
     * @param  bool   $predefined  True if the workflow is from KnowledgeBaseService::WORKFLOWS
     * @param  int    $stepsCount  Number of steps in the workflow
     */
    public function forWorkflow(string $domain, bool $predefined, int $stepsCount = 0): array
    {
        $score = $predefined ? 0.93 : 0.65;

        return [
            'score'      => $score,
            'percent'    => (int) ($score * 100),
            'reason'     => $predefined
                ? "Pre-defined workflow for '{$domain}' domain ({$stepsCount} steps) — battle-tested pattern"
                : "Workflow inferred from genome for '{$domain}' — not pre-validated",
            'source'     => $predefined ? 'knowledge_base' : 'genome_inference',
            'ai_used'    => false,
            'domain'     => $domain,
        ];
    }

    /**
     * Confidence for module selection.
     *
     * @param  string $source  'blueprint_library' | 'genome_engine' | 'ai_generated' | 'domain_pack'
     * @param  string $module  Module name
     * @param  bool   $required  Whether this module is in the blueprint's required_modules list
     */
    public function forModule(string $source, string $module, bool $required = true): array
    {
        $scores = [
            'blueprint_library' => 0.97,
            'domain_pack'       => 0.93,
            'genome_engine'     => 0.86,
            'ai_generated'      => 0.68,
        ];
        $score = $scores[$source] ?? 0.65;
        if (!$required) $score = max(0.40, $score - 0.10);

        $reasons = [
            'blueprint_library' => "Module '{$module}' is part of a pre-built blueprint — proven for this domain",
            'domain_pack'       => "Module '{$module}' included in domain pack — best-practice selection",
            'genome_engine'     => "Module '{$module}' derived from 500-industry genome engine",
            'ai_generated'      => "Module '{$module}' suggested by AI for this specific request",
        ];

        return [
            'score'    => $score,
            'percent'  => (int) ($score * 100),
            'reason'   => $reasons[$source] ?? "Module '{$module}' selected",
            'source'   => $source,
            'ai_used'  => $source === 'ai_generated',
            'module'   => $module,
            'required' => $required,
        ];
    }

    /**
     * Confidence for applying a set of business rules to a domain.
     *
     * @param  string $domain    Domain key
     * @param  int    $rulesCount  Number of rules available for this domain
     * @param  int    $appliedCount  Number of rules actually applied/verified
     */
    public function forRules(string $domain, int $rulesCount, int $appliedCount = 0): array
    {
        $coverage = $rulesCount > 0 ? round($appliedCount / $rulesCount, 2) : 0.0;
        $score    = $rulesCount > 0 ? min(1.0, 0.75 + ($coverage * 0.25)) : 0.50;

        return [
            'score'          => $score,
            'percent'        => (int) ($score * 100),
            'reason'         => $rulesCount > 0
                ? "Applied {$appliedCount}/{$rulesCount} business rules for '{$domain}' domain"
                : "No pre-defined rules found for '{$domain}' domain",
            'source'         => 'business_rules_config',
            'ai_used'        => false,
            'domain'         => $domain,
            'rules_total'    => $rulesCount,
            'rules_applied'  => $appliedCount,
            'coverage_pct'   => (int) ($coverage * 100),
        ];
    }

    /**
     * Build a summary confidence score across all dimensions.
     * Returns an aggregate used in OutcomeRecord::confidence_scores.
     */
    public function aggregate(array $dimensionScores): array
    {
        $scores = array_column($dimensionScores, 'score');
        $avg    = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0.0;
        $aiUsed = in_array(true, array_column($dimensionScores, 'ai_used'), true);

        return [
            'overall'       => $avg,
            'overall_pct'   => (int) ($avg * 100),
            'ai_used'       => $aiUsed,
            'dimensions'    => $dimensionScores,
            'explanation'   => $this->buildExplanation($dimensionScores, $avg),
        ];
    }

    /**
     * Human-readable explanation of why a set of recommendations was made.
     * Implements the Trust Engine — "explain every recommendation."
     */
    public function buildExplanation(array $dimensionScores, float $overallScore): string
    {
        $lines   = [];
        $aiUsed  = false;

        foreach ($dimensionScores as $dim) {
            if (!empty($dim['reason'])) {
                $icon    = ($dim['ai_used'] ?? false) ? '🤖' : '⚡';
                $lines[] = "{$icon} {$dim['reason']}";
            }
            if ($dim['ai_used'] ?? false) $aiUsed = true;
        }

        $grade   = match (true) {
            $overallScore >= 0.95 => 'Excellent',
            $overallScore >= 0.85 => 'Very Good',
            $overallScore >= 0.75 => 'Good',
            $overallScore >= 0.60 => 'Acceptable',
            default               => 'Low confidence',
        };

        $aiNote = $aiUsed
            ? 'AI was used for parts of this recommendation.'
            : 'No AI tokens were consumed for this recommendation.';

        array_unshift($lines, "Overall confidence: {$grade} (" . (int)($overallScore * 100) . "%) — {$aiNote}");

        return implode("\n", $lines);
    }
}
