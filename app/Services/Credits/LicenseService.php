<?php

namespace App\Services\Credits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Validates license keys against api.ryaancms.com and maintains a local cache.
 * Offline grace period: 7 days (so the app works without internet).
 */
class LicenseService
{
    private const API_BASE     = 'https://api.ryaancms.com/v1';
    private const CACHE_PREFIX = 'ryaan_license_';
    private const CACHE_TTL    = 3600 * 6;   // 6 hours for valid licenses
    private const GRACE_TTL    = 3600 * 168;  // 7 days offline grace period
    private const TIMEOUT      = 5;           // seconds — fast fail, don't block UX

    /**
     * Validate and return license data for a key.
     * Returns ['valid' => true/false, 'tier' => 'starter|pro|enterprise', ...].
     */
    public function validate(string $licenseKey, bool $force = false): array
    {
        $cacheKey = self::CACHE_PREFIX . md5($licenseKey);

        if (!$force && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders(['X-License-Key' => $licenseKey])
                ->get(self::API_BASE . '/license/validate', [
                    'key'     => $licenseKey,
                    'domain'  => request()->getHost(),
                    'version' => config('app.version', '1.0.0'),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = [
                    'valid'      => $data['valid'] ?? false,
                    'tier'       => $data['tier'] ?? 'byok',
                    'expires_at' => $data['expires_at'] ?? null,
                    'credits'    => $data['credits'] ?? 0,
                    'features'   => $data['features'] ?? [],
                    'checked_at' => now()->toIso8601String(),
                    'offline'    => false,
                ];

                // Cache valid licenses for 6h; invalid ones for 15 min
                $ttl = $result['valid'] ? self::CACHE_TTL : 900;
                Cache::put($cacheKey, $result, $ttl);

                // Update grace-period copy (only for valid results)
                if ($result['valid']) {
                    Cache::put($cacheKey . '_grace', $result, self::GRACE_TTL);
                }

                return $result;
            }
        } catch (\Throwable) {
            // Network failure — fall through to grace period cache
        }

        // Offline: return cached grace-period data if available
        if (Cache::has($cacheKey . '_grace')) {
            $cached = Cache::get($cacheKey . '_grace');
            $cached['offline'] = true;
            return $cached;
        }

        return ['valid' => false, 'tier' => 'byok', 'offline' => true];
    }

    /**
     * Quick check: is the user's stored license key valid?
     */
    public function isValid(string $licenseKey): bool
    {
        return $this->validate($licenseKey)['valid'] ?? false;
    }

    /**
     * Get tier from license key.
     */
    public function getTier(string $licenseKey): string
    {
        $result = $this->validate($licenseKey);
        return $result['valid'] ? ($result['tier'] ?? 'byok') : 'byok';
    }

    /**
     * Clear cached validation for a key (call after purchase/upgrade).
     */
    public function clearCache(string $licenseKey): void
    {
        $cacheKey = self::CACHE_PREFIX . md5($licenseKey);
        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_grace');
    }
}
