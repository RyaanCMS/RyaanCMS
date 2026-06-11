<?php

namespace App\Services\Credits;

/**
 * Outcome-based Ryaan Credits pricing.
 *
 * Pricing philosophy: $10 = 1,000 Ryaan Credits.
 * Credits map to outcomes (app generations, features) not raw tokens.
 * BYOK users use their own API keys — no credits consumed.
 */
class CreditPricingService
{
    // Credit cost per outcome type
    private const COSTS = [
        // Full app generation (full blueprint-driven system)
        'full_app_generation'    => 100,
        // Single feature / page generation
        'feature_generation'     => 20,
        // Tiny edit (small code change)
        'tiny_edit'              => 3,
        // Blueprint discovery phase
        'blueprint_discovery'    => 5,
        // Marketplace template install
        'template_install'       => 10,
        // Knowledge base query (premium domain packs)
        'kb_query'               => 2,
        // Pipeline agent run (full 10-agent autonomous pipeline)
        'pipeline_run'           => 150,
        // Single pipeline agent step
        'pipeline_agent_step'    => 15,
        // Chat / Q&A (non-generation)
        'chat'                   => 1,
    ];

    // Token-to-credit conversion rate (for metered billing as fallback)
    // 1 credit ≈ 1,000 tokens (input + output combined)
    private const TOKENS_PER_CREDIT = 1000;

    public function costForOutcome(string $outcomeType): int
    {
        return self::COSTS[$outcomeType] ?? self::COSTS['chat'];
    }

    /**
     * Convert raw token count to credits (used when we can't map to a discrete outcome).
     */
    public function tokensToCredits(int $totalTokens): int
    {
        return (int) ceil($totalTokens / self::TOKENS_PER_CREDIT);
    }

    /**
     * Determine the outcome type from task context (maps CodeGeneratorService task types).
     */
    public function outcomeFromTaskType(string $taskType, bool $isTiny = false, bool $isFullSystem = false): string
    {
        if ($isTiny) return 'tiny_edit';
        if ($isFullSystem) return 'full_app_generation';

        return match ($taskType) {
            'generation', 'complex_generation' => 'feature_generation',
            'discovery'                         => 'blueprint_discovery',
            'pipeline'                          => 'pipeline_run',
            'pipeline_agent'                    => 'pipeline_agent_step',
            'chat', 'simple'                    => 'chat',
            default                             => 'feature_generation',
        };
    }

    /**
     * Estimate cost before running (for pre-flight checks).
     */
    public function estimateCost(string $taskType, bool $isTiny = false, bool $isFullSystem = false): int
    {
        return $this->costForOutcome($this->outcomeFromTaskType($taskType, $isTiny, $isFullSystem));
    }

    /**
     * All defined outcome costs (for settings/pricing display).
     */
    public function allCosts(): array
    {
        return self::COSTS;
    }

    /**
     * Dollar value of N credits. $10 = 1000 credits.
     */
    public function creditsToUsd(int $credits): float
    {
        return round($credits * 0.01, 4);
    }
}
