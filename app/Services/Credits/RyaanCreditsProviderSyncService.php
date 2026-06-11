<?php

namespace App\Services\Credits;

use App\Models\AIProvider;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class RyaanCreditsProviderSyncService
{
    public const PROVIDER = 'ryaancms_credits';

    public function __construct(private readonly RyaanCreditsService $credits) {}

    public function sync(User $user): ?AIProvider
    {
        if (!Schema::hasTable('ai_providers') || !Schema::hasTable('credit_balances')) {
            return null;
        }

        $provider = AIProvider::where('user_id', $user->id)
            ->where('provider', self::PROVIDER)
            ->first();

        if (!$this->credits->isCreditsUser($user)) {
            if ($provider && ($provider->is_active || $provider->is_default)) {
                $provider->update(['is_active' => false, 'is_default' => false]);
            }

            return $provider;
        }

        $config = config('ai.providers.' . self::PROVIDER, []);
        $hasOtherDefault = AIProvider::where('user_id', $user->id)
            ->where('provider', '!=', self::PROVIDER)
            ->where('is_active', true)
            ->where('is_default', true)
            ->exists();
        $shouldBeDefault = !$hasOtherDefault;

        if ($shouldBeDefault) {
            AIProvider::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $settings = array_merge((array) ($provider?->settings ?? []), [
            'billing'       => 'credits',
            'managed_by'    => 'system',
            'readonly'      => true,
            'system_provider' => true,
        ]);

        return AIProvider::updateOrCreate(
            ['user_id' => $user->id, 'provider' => self::PROVIDER],
            [
                'name'          => $config['name'] ?? 'RyaanCMS Intelligence Credits',
                'api_key'       => null,
                'api_url'       => $config['api_url'] ?? null,
                'default_model' => $config['default_model'] ?? null,
                'settings'      => $settings,
                'is_active'     => true,
                'is_default'    => $shouldBeDefault || (bool) ($provider?->is_default),
            ]
        );
    }

    public static function isManagedProvider(?string $provider): bool
    {
        return $provider === self::PROVIDER;
    }
}
