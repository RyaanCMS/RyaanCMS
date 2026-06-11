<?php

namespace App\Services\Central;

use App\Models\Central\CentralInstall;
use App\Models\Central\CentralTelemetry;
use Illuminate\Support\Facades\Log;

class TelemetryAggregator
{
    /**
     * Record a single telemetry event from a customer install.
     * All data must be anonymized before calling this method.
     */
    public function record(array $data): void
    {
        try {
            // Domain hash: SHA-256 of domain — never store raw domain
            $domainHash = isset($data['domain'])
                ? hash('sha256', strtolower(trim($data['domain'])))
                : null;

            CentralTelemetry::create([
                'domain_hash'     => $domainHash,
                'license_key'     => $data['license_key'] ?? null,
                'intent_domain'   => $data['intent_domain'] ?? null,
                'entities'        => $data['entities'] ?? [],
                'functions'       => $data['functions'] ?? [],
                'language'        => $data['language'] ?? null,
                'country'         => $data['country'] ?? null,
                'generation_type' => $data['generation_type'] ?? 'full',
                'app_version'     => $data['app_version'] ?? null,
                'used_ai'         => $data['used_ai'] ?? true,
                'recorded_at'     => now(),
            ]);

            // Increment prompt counters on the install record
            if (isset($data['domain'])) {
                CentralInstall::where('domain', $data['domain'])->increment('prompts_total');
            }
        } catch (\Throwable $e) {
            // Never let telemetry errors affect the customer's workflow
            Log::warning('Telemetry record failed: ' . $e->getMessage());
        }
    }

    /**
     * Aggregate summary for the admin dashboard.
     */
    public function summary(int $days = 30): array
    {
        return [
            'total_events'       => CentralTelemetry::where('recorded_at', '>=', now()->subDays($days))->count(),
            'unique_domains'     => CentralTelemetry::where('recorded_at', '>=', now()->subDays($days))
                ->distinct('domain_hash')->count('domain_hash'),
            'top_domains'        => CentralTelemetry::topDomains(10, $days),
            'top_entities'       => CentralTelemetry::topEntities(20, $days),
            'language_dist'      => CentralTelemetry::languageDistribution($days),
            'country_dist'       => CentralTelemetry::countryDistribution($days),
            'ai_usage_rate'      => $this->aiUsageRate($days),
            'events_today'       => CentralTelemetry::today()->count(),
            'events_this_week'   => CentralTelemetry::thisWeek()->count(),
        ];
    }

    private function aiUsageRate(int $days): float
    {
        $total = CentralTelemetry::where('recorded_at', '>=', now()->subDays($days))->count();
        if ($total === 0) return 0.0;
        $withAi = CentralTelemetry::where('recorded_at', '>=', now()->subDays($days))
            ->where('used_ai', true)->count();
        return round(($withAi / $total) * 100, 1);
    }
}
