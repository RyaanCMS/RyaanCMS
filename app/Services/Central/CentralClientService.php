<?php

namespace App\Services\Central;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Used by customer installs to phone home to the central server.
 * Handles license validation, telemetry, credit deduction, and heartbeat pings.
 */
class CentralClientService
{
    private string $baseUrl;
    private string $licenseKey;
    private string $domain;
    private int $timeout = 10;

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('registry.url', 'https://registry.ryaancms.com'), '/');
        $this->licenseKey = config('registry.license_key', '');
        $this->domain     = config('app.url', '');
    }

    /**
     * Validate this install's license with the central server.
     * Called on boot and cached for 1 hour.
     */
    public function validateLicense(): array
    {
        if (!$this->licenseKey) {
            return ['valid' => false, 'error' => 'No license key configured.'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['X-License-Key' => $this->licenseKey])
                ->post("{$this->baseUrl}/api/central/v1/license/validate", [
                    'license_key'     => $this->licenseKey,
                    'domain'          => $this->domain,
                    'app_version'     => config('app.version', '1.0.0'),
                    'php_version'     => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'user_count'      => \App\Models\User::count(),
                    'project_count'   => class_exists(\App\Models\Project::class)
                        ? \App\Models\Project::count() : 0,
                ]);

            return $response->json() ?? ['valid' => false, 'error' => 'Empty response.'];
        } catch (\Throwable $e) {
            Log::warning('License validation failed: ' . $e->getMessage());
            // On network failure, allow usage — don't block the customer
            return ['valid' => true, 'plan' => 'unknown', 'offline' => true];
        }
    }

    /**
     * Deduct credits from the central server for an AI generation.
     */
    public function deductCredits(int $amount, string $description = '', string $reference = ''): array
    {
        if (!$this->licenseKey) {
            return ['ok' => true, 'offline' => true];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['X-License-Key' => $this->licenseKey])
                ->post("{$this->baseUrl}/api/central/v1/credits/deduct", [
                    'license_key' => $this->licenseKey,
                    'amount'      => $amount,
                    'domain'      => $this->domain,
                    'description' => $description,
                    'reference'   => $reference,
                ]);

            return $response->json() ?? ['ok' => false, 'error' => 'Empty response.'];
        } catch (\Throwable $e) {
            Log::warning('Credit deduction failed: ' . $e->getMessage());
            return ['ok' => true, 'offline' => true];
        }
    }

    /**
     * Send anonymized telemetry to the central server (fire-and-forget).
     */
    public function sendTelemetry(array $data): void
    {
        if (!$this->licenseKey) {
            return;
        }

        // Strip any PII before sending
        $safe = [
            'license_key'     => $this->licenseKey,
            'domain'          => $this->domain,
            'intent_domain'   => $data['intent_domain'] ?? null,
            'entities'        => array_slice((array) ($data['entities'] ?? []), 0, 20),
            'functions'       => array_slice((array) ($data['functions'] ?? []), 0, 20),
            'language'        => $data['language'] ?? null,
            'country'         => $data['country'] ?? null,
            'generation_type' => $data['generation_type'] ?? 'full',
            'app_version'     => config('app.version', '1.0.0'),
            'used_ai'         => $data['used_ai'] ?? true,
        ];

        try {
            Http::timeout(5)
                ->withHeaders(['X-License-Key' => $this->licenseKey])
                ->post("{$this->baseUrl}/api/central/v1/telemetry", $safe);
        } catch (\Throwable) {
            // Silent — telemetry is best-effort
        }
    }

    /**
     * Send a heartbeat ping to the central server (every 6 hours).
     */
    public function ping(): void
    {
        if (!$this->licenseKey) {
            return;
        }

        try {
            Http::timeout(5)
                ->withHeaders(['X-License-Key' => $this->licenseKey])
                ->post("{$this->baseUrl}/api/central/v1/install/ping", [
                    'license_key'     => $this->licenseKey,
                    'domain'          => $this->domain,
                    'app_version'     => config('app.version', '1.0.0'),
                    'php_version'     => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'user_count'      => \App\Models\User::count(),
                    'project_count'   => class_exists(\App\Models\Project::class)
                        ? \App\Models\Project::count() : 0,
                    'prompts_today'   => $this->promptsToday(),
                ]);
        } catch (\Throwable) {
            // Silent
        }
    }

    private function promptsToday(): int
    {
        try {
            // Count from WisdomEngine/Intelligence Ledger if available
            return \App\Models\Central\CentralTelemetry::today()->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
