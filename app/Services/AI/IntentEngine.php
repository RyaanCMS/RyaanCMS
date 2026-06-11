<?php

namespace App\Services\AI;

/**
 * IntentEngine — Blueprint Genome System, Phase 1
 *
 * Converts ANY user prompt (any language, any script) into a structured genome intent
 * using zero AI tokens — pure PHP pattern matching against config/genome/intent_patterns.php.
 *
 * Output drives BlueprintGenomeEngine which assembles the rich blueprint context.
 *
 * Formula: 500 industries × 1000 functions × patterns → billions of possible intents
 */
class IntentEngine
{
    private array $patterns;

    public function __construct()
    {
        $this->patterns = config('genome.intent_patterns', require config_path('genome/intent_patterns.php'));
    }

    /**
     * Primary entry point.
     * Returns a structured intent array ready for BlueprintGenomeEngine.
     *
     * @param  string $prompt  Raw user input, any language
     * @return array{
     *   action: string,
     *   industries: string[],
     *   features: string[],
     *   complexity: string,
     *   confidence: float,
     *   raw_scores: array,
     *   normalized_prompt: string,
     * }
     */
    public function detect(string $prompt): array
    {
        $normalized = $this->normalize($prompt);

        $actionScores   = $this->scoreSection($normalized, $this->patterns['actions']   ?? []);
        $industryScores = $this->scoreSection($normalized, $this->patterns['industries'] ?? []);
        $featureScores  = $this->scoreSection($normalized, $this->patterns['features']  ?? []);
        $scaleScores    = $this->scoreSection($normalized, $this->patterns['scale']     ?? []);

        $action     = $this->topKey($actionScores)   ?: 'build';
        $industries = $this->topKeys($industryScores, 3);
        $features   = $this->topKeys($featureScores,  5);
        $complexity = $this->resolveComplexity($scaleScores, $industries);

        // Map industry keys to genome industry IDs
        $mappedIndustries = $this->mapIndustries($industries, $industryScores);

        // Map feature keys to genome function IDs
        $mappedFeatures = $this->mapFeatures($features, $featureScores);

        $topScore   = max(array_merge(array_values($industryScores), [0.01]));
        $confidence = min(round($topScore / 3.0, 2), 1.0);

        return [
            'action'            => $action,
            'industries'        => $mappedIndustries,
            'features'          => $mappedFeatures,
            'complexity'        => $complexity,
            'confidence'        => $confidence,
            'raw_scores'        => [
                'actions'    => $actionScores,
                'industries' => $industryScores,
                'features'   => $featureScores,
                'scale'      => $scaleScores,
            ],
            'normalized_prompt' => $normalized,
        ];
    }

    /**
     * Lightweight quick-check: just return the top industry ID or null.
     */
    public function quickIndustry(string $prompt): ?string
    {
        $normalized = $this->normalize($prompt);
        $scores     = $this->scoreSection($normalized, $this->patterns['industries'] ?? []);
        $top        = $this->topKey($scores);
        return $top ? ($this->patterns['industries'][$top]['maps_to'] ?? $top) : null;
    }

    /**
     * Normalise prompt for matching: lowercase + collapse whitespace + keep Unicode.
     * Preserves Bengali/Arabic/Hindi scripts intact so patterns match them correctly.
     */
    public function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Score each key in a pattern section against the normalized prompt.
     * Returns [ key => score ] sorted descending.
     */
    private function scoreSection(string $text, array $section): array
    {
        $scores = [];
        foreach ($section as $key => $entry) {
            $patterns = $entry['patterns'] ?? [];
            $weight   = (float) ($entry['weight'] ?? 1.0);
            $score    = 0.0;

            foreach ($patterns as $pattern) {
                $p = mb_strtolower((string) $pattern, 'UTF-8');
                if ($p === '') continue;

                // Full word / substring match: each match adds to score
                if (mb_strpos($text, $p) !== false) {
                    // Longer patterns are more specific → higher bonus
                    $bonus = max(1.0, mb_strlen($p) / 4);
                    $score += $bonus;
                }
            }

            if ($score > 0) {
                $scores[$key] = round($score * $weight, 3);
            }
        }

        arsort($scores);
        return $scores;
    }

    private function topKey(array $scores): ?string
    {
        return array_key_first($scores);
    }

    /** Return top N keys from a score array */
    private function topKeys(array $scores, int $n): array
    {
        return array_slice(array_keys($scores), 0, $n);
    }

    /** Map industry score keys to genome industry IDs */
    private function mapIndustries(array $keys, array $scores): array
    {
        $mapped = [];
        foreach ($keys as $k) {
            if (isset($this->patterns['industries'][$k])) {
                $id = $this->patterns['industries'][$k]['maps_to'] ?? $k;
                if ($id && !in_array($id, $mapped, true)) {
                    $mapped[] = $id;
                }
            }
        }
        return $mapped;
    }

    /** Map feature score keys to genome function IDs */
    private function mapFeatures(array $keys, array $scores): array
    {
        $mapped = [];
        foreach ($keys as $k) {
            if (isset($this->patterns['features'][$k])) {
                $id = $this->patterns['features'][$k]['maps_to'] ?? $k;
                if ($id && !in_array($id, $mapped, true)) {
                    $mapped[] = $id;
                }
            }
        }
        return $mapped;
    }

    private function resolveComplexity(array $scaleScores, array $industries): string
    {
        // Enterprise industries default to 'large'
        $enterpriseIndustries = ['manufacturing', 'hospital', 'university', 'saas', 'microfinance', 'garments'];
        foreach ($industries as $ind) {
            if (in_array($ind, $enterpriseIndustries, true)) {
                return isset($scaleScores['small']) ? 'medium' : 'large';
            }
        }

        if (isset($scaleScores['large']) && ($scaleScores['large'] ?? 0) > ($scaleScores['small'] ?? 0)) {
            return 'large';
        }
        if (isset($scaleScores['small'])) {
            return 'small';
        }
        return 'medium';
    }
}
