<?php

namespace App\Services\Registry;

use App\Models\LocalQuestionPack;
use App\Models\RegistrySyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrySyncService
{
    private string $registryUrl;
    private ?string $licenseKey;
    private int $timeoutSeconds = 15;

    public function __construct()
    {
        $this->registryUrl = rtrim(config('registry.url', 'https://registry.ryaancms.com'), '/');
        $this->licenseKey  = config('registry.license_key');
    }

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Full sync: check for updates, download changed packs, record log.
     */
    public function sync(bool $forceAll = false): array
    {
        $start = microtime(true);
        $counts = ['checked' => 0, 'updated' => 0, 'skipped' => 0];

        try {
            $remote = $this->fetchManifest();

            if (empty($remote)) {
                return $this->log('sync', false, $counts, 'Empty manifest received from registry.', [], $start);
            }

            $counts['checked'] = count($remote);

            foreach ($remote as $packMeta) {
                $result = $this->processPack($packMeta, $forceAll);
                if ($result === 'updated') $counts['updated']++;
                else $counts['skipped']++;
            }

            return $this->log('sync', true, $counts, null, ['registry' => $this->registryUrl], $start);

        } catch (\Throwable $e) {
            Log::error('[RegistrySyncService] sync failed', ['error' => $e->getMessage()]);
            return $this->log('sync', false, $counts, $e->getMessage(), [], $start);
        }
    }

    /**
     * Check if any installed pack has a newer version available on the registry.
     * Returns list of {slug, current_version, latest_version} for outdated packs.
     */
    public function checkForUpdates(): array
    {
        try {
            $manifest = $this->fetchManifest();
            $outdated = [];

            foreach ($manifest as $remote) {
                $local = LocalQuestionPack::where('slug', $remote['slug'])->first();
                if ($local && $local->isOutdated($remote['version'])) {
                    $outdated[] = [
                        'slug'            => $remote['slug'],
                        'name'            => $remote['name'],
                        'current_version' => $local->version,
                        'latest_version'  => $remote['version'],
                        'is_premium'      => $remote['is_premium'] ?? false,
                    ];
                }
            }

            return $outdated;
        } catch (\Throwable $e) {
            Log::warning('[RegistrySyncService] checkForUpdates failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Verify a license key against the central registry.
     * Returns ['valid' => bool, 'plan' => string|null, 'error' => string|null]
     */
    public function verifyLicense(string $licenseKey): array
    {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->post("{$this->registryUrl}/api/registry/license/verify", [
                    'license_key' => $licenseKey,
                    'domain'      => request()->getHost(),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return ['valid' => $data['valid'] ?? false, 'plan' => $data['plan'] ?? null, 'error' => null];
            }

            return ['valid' => false, 'plan' => null, 'error' => 'Registry returned ' . $response->status()];
        } catch (\Throwable $e) {
            return ['valid' => false, 'plan' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Download a single pack by slug directly (useful for on-demand install).
     */
    public function downloadPack(string $slug): ?LocalQuestionPack
    {
        try {
            $response = $this->registryGet("/api/registry/question-packs/{$slug}");

            if (!$response) return null;

            return $this->upsertPack($response);
        } catch (\Throwable $e) {
            Log::warning('[RegistrySyncService] downloadPack failed', ['slug' => $slug, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Private Helpers ───────────────────────────────────────────────────

    private function fetchManifest(): array
    {
        $response = $this->registryGet('/api/registry/question-packs');

        return $response['data'] ?? $response ?? [];
    }

    private function processPack(array $packMeta, bool $force): string
    {
        $slug  = $packMeta['slug'] ?? null;
        if (!$slug) return 'skipped';

        $local = LocalQuestionPack::where('slug', $slug)->first();

        // Skip free packs that are already current
        if (!$force && $local && !$local->isOutdated($packMeta['version'] ?? '1.0.0')) {
            return 'skipped';
        }

        // Premium packs require a valid license
        if (($packMeta['is_premium'] ?? false) && !$this->hasLicense()) {
            return 'skipped';
        }

        // Fetch full pack data
        $fullPack = $this->registryGet("/api/registry/question-packs/{$slug}");
        if (!$fullPack) return 'skipped';

        $this->upsertPack($fullPack);
        return 'updated';
    }

    private function upsertPack(array $data): LocalQuestionPack
    {
        return LocalQuestionPack::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'central_pack_uuid' => $data['uuid']            ?? null,
                'name'              => $data['name']            ?? $data['slug'],
                'industry'          => $data['industry']        ?? 'general',
                'blueprint_slug'    => $data['blueprint_slug']  ?? null,
                'country'           => $data['country']         ?? 'ALL',
                'language'          => $data['language']        ?? 'en',
                'version'           => $data['version']         ?? '1.0.0',
                'status'            => $data['status']          ?? 'active',
                'visibility'        => $data['visibility']      ?? 'public',
                'is_premium'        => $data['is_premium']      ?? false,
                'questions_json'    => $data['questions']       ?? $data['questions_json'] ?? [],
                'rules_json'        => $data['rules']           ?? $data['rules_json']     ?? null,
                'metadata'          => $data['metadata']        ?? null,
                'synced_at'         => now(),
                'license_status'    => ($data['is_premium'] ?? false) ? 'licensed' : 'free',
            ]
        );
    }

    private function registryGet(string $path): ?array
    {
        $headers = ['Accept' => 'application/json'];
        if ($this->licenseKey) {
            $headers['X-License-Key'] = $this->licenseKey;
        }

        $response = Http::timeout($this->timeoutSeconds)
            ->withHeaders($headers)
            ->get($this->registryUrl . $path);

        if ($response->failed()) {
            Log::debug('[RegistrySyncService] GET failed', ['path' => $path, 'status' => $response->status()]);
            return null;
        }

        return $response->json();
    }

    private function hasLicense(): bool
    {
        return !empty($this->licenseKey);
    }

    private function log(
        string $action,
        bool $success,
        array $counts,
        ?string $error,
        array $details,
        float $startTime
    ): array {
        $ms = (int) ((microtime(true) - $startTime) * 1000);

        RegistrySyncLog::record(
            $this->registryUrl,
            $action,
            $success,
            $counts,
            $error,
            $details,
            $ms
        );

        return [
            'success'       => $success,
            'packs_checked' => $counts['checked'],
            'packs_updated' => $counts['updated'],
            'packs_skipped' => $counts['skipped'],
            'duration_ms'   => $ms,
            'error'         => $error,
        ];
    }
}
