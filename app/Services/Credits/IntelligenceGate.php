<?php

namespace App\Services\Credits;

use App\Models\User;

/**
 * Intelligence Gate — decides which assets/features a user can access.
 *
 * Tier matrix:
 *   byok (BYOK)        → basic code gen, basic domain packs, community templates
 *   starter/pro/enterprise → + premium industry packs, senior-dev wisdom, full KB,
 *                              pipeline agents, advanced blueprints, priority AI routing
 */
class IntelligenceGate
{
    public function __construct(
        private readonly RyaanCreditsService $credits,
        private readonly LicenseService $license
    ) {}

    // ── Tier resolution ───────────────────────────────────────────────────────

    public function tier(User $user): string
    {
        $balance = $this->credits->getBalance($user);

        // Check DB tier first (set during onboarding/purchase)
        if ($balance->isCreditsUser() && $balance->isTierActive()) {
            return $balance->tier;
        }

        // License key fallback (for self-hosted installs)
        $key = $this->userLicenseKey($user);
        if ($key) {
            $licenseTier = $this->license->getTier($key);
            if ($licenseTier !== 'byok') {
                return $licenseTier;
            }
        }

        return 'byok';
    }

    public function isCreditsUser(User $user): bool
    {
        return $this->tier($user) !== 'byok';
    }

    public function isByok(User $user): bool
    {
        return $this->tier($user) === 'byok';
    }

    // ── Feature gates ─────────────────────────────────────────────────────────

    public function canAccessPremiumKnowledgeBase(User $user): bool
    {
        return $this->isCreditsUser($user);
    }

    public function canAccessSeniorDevWisdom(User $user): bool
    {
        return $this->isCreditsUser($user);
    }

    public function canRunPipelineAgents(User $user): bool
    {
        return in_array($this->tier($user), ['pro', 'enterprise']);
    }

    public function canAccessPremiumBlueprints(User $user): bool
    {
        return $this->isCreditsUser($user);
    }

    public function canAccessPremiumIndustryPacks(User $user): bool
    {
        return $this->isCreditsUser($user);
    }

    public function canAccessPriorityAiRouting(User $user): bool
    {
        return $this->isCreditsUser($user);
    }

    public function canUseAdvancedModels(User $user): bool
    {
        return in_array($this->tier($user), ['pro', 'enterprise']);
    }

    // ── Credit checks ─────────────────────────────────────────────────────────

    /**
     * For Credits-tier users: check they have enough credits for an operation.
     * BYOK users always return true (they use their own API keys, no credits charged).
     */
    public function canAffordOperation(User $user, int $creditCost): bool
    {
        if ($this->isByok($user)) {
            return true; // BYOK users are never gated by credits
        }

        return $this->credits->canAfford($user, $creditCost);
    }

    /**
     * Full gate check: is the user allowed to run this operation?
     * Returns ['allowed' => bool, 'reason' => string|null]
     */
    public function checkOperation(User $user, string $outcomeType, int $creditCost): array
    {
        if ($this->isByok($user)) {
            return ['allowed' => true, 'reason' => null, 'tier' => 'byok'];
        }

        if (!$this->credits->canAfford($user, $creditCost)) {
            $balance = $this->credits->getBalanceAmount($user);
            return [
                'allowed' => false,
                'reason'  => "Insufficient Ryaan Credits. You have {$balance} credits but this operation costs {$creditCost}. Top up at ryaancms.com/credits.",
                'tier'    => $this->tier($user),
                'balance' => $balance,
                'cost'    => $creditCost,
            ];
        }

        return ['allowed' => true, 'reason' => null, 'tier' => $this->tier($user)];
    }

    // ── Knowledge base asset filtering ───────────────────────────────────────

    /**
     * Filter a list of KB assets to only those accessible by this user.
     * Each asset should have a 'tier' key: 'basic' | 'premium'.
     */
    public function filterAssets(User $user, array $assets): array
    {
        if ($this->isCreditsUser($user)) {
            return $assets; // Credits users get everything
        }

        // BYOK users get basic assets only
        return array_values(array_filter($assets, fn($a) => ($a['tier'] ?? 'basic') === 'basic'));
    }

    /**
     * Resolve which system prompt sections are available for this user.
     */
    public function systemPromptSections(User $user): array
    {
        $sections = ['core', 'ui_patterns', 'laravel_conventions'];

        if ($this->isCreditsUser($user)) {
            $sections = array_merge($sections, [
                'senior_dev_wisdom',
                'industry_patterns',
                'advanced_architecture',
                'security_hardening',
                'performance_patterns',
            ]);
        }

        return $sections;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function userLicenseKey(User $user): ?string
    {
        // License key stored in user settings
        $setting = $user->settings()->where('key', 'ryaan_license_key')->first();
        return $setting?->value ?: null;
    }
}
