<?php

namespace App\Console\Commands;

use App\Services\AI\RGINClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IntelligenceSync extends Command
{
    protected $signature = 'intelligence:sync
                            {--since= : Only sync assets updated after this datetime (ISO 8601)}
                            {--dry-run : Show what would be synced without saving}
                            {--stats : Show network stats after sync}';

    protected $description = 'Pull validated intelligence assets from the RGIN Central Cloud';

    public function handle(RGINClient $client): int
    {
        $this->info('RyaanCMS Global Intelligence Network — Sync');
        $this->line('');

        if (!$client->isConfigured()) {
            $this->error('RGIN not configured. Add to .env:');
            $this->line('  RGIN_ENABLED=true');
            $this->line('  RGIN_CLOUD_URL=https://cloud.ryaancms.com');
            $this->line('  RGIN_API_KEY=your_api_key');
            return self::FAILURE;
        }

        $this->line('Connecting to RGIN Cloud...');
        if (!$client->ping()) {
            $this->error('Cannot reach RGIN Cloud.');
            return self::FAILURE;
        }
        $this->line('<fg=green>✓</> Connected');
        $this->line('');

        // Determine since timestamp
        $since = $this->option('since');
        if (!$since) {
            // Use last sync time from cache/config
            $since = cache('rgin_last_sync_at');
        }

        $sinceLabel = $since ? "since {$since}" : 'all time';
        $this->line("Pulling validated assets ({$sinceLabel})...");

        $result = $client->sync($since);

        if (!$result['success']) {
            $this->error('Sync failed: ' . $result['error']);
            return self::FAILURE;
        }

        $assets = $result['assets'] ?? [];
        $total  = $result['total']  ?? 0;

        if (empty($assets)) {
            $this->info("Already up to date. No new assets since last sync.");
            return self::SUCCESS;
        }

        $this->line("Received <fg=yellow>{$total}</> validated assets from the network.");
        $this->line('');

        if ($this->option('dry-run')) {
            $this->table(['Domain', 'Type', 'Name', 'Quality'], array_map(fn($a) => [
                $a['domain'] ?? '—',
                $a['asset_type'] ?? '—',
                $a['name'] ?? '—',
                ($a['quality']['extraction_quality'] ?? 0) . '%',
            ], array_slice($assets, 0, 20)));
            $this->warn('Dry run — nothing saved.');
            return self::SUCCESS;
        }

        // Merge assets into local intelligence registry
        $saved = 0;
        $bar   = $this->output->createProgressBar(count($assets));
        $bar->start();

        foreach ($assets as $asset) {
            try {
                $fingerprint = $asset['asset']['fingerprint'] ?? null;
                if (!$fingerprint) { $bar->advance(); continue; }

                $exists = DB::table('local_intelligence_registry')
                    ->where('fingerprint', $fingerprint)
                    ->exists();

                if (!$exists) {
                    DB::table('local_intelligence_registry')->insert([
                        'asset_type'         => $asset['asset']['type']         ?? 'feature',
                        'domain'             => $asset['asset']['domain']        ?? 'generic',
                        'extension'          => $asset['asset']['extension']     ?? null,
                        'name'               => $asset['asset']['name']          ?? '',
                        'fingerprint'        => $fingerprint,
                        'dependencies'       => json_encode($asset['intelligence']['dependencies'] ?? []),
                        'tags'               => json_encode($asset['intelligence']['tags']         ?? []),
                        'business_rules'     => json_encode($asset['intelligence']['business_rules'] ?? []),
                        'prompt_summary'     => $asset['intelligence']['prompt_summary'] ?? null,
                        'confidence'         => $asset['quality']['confidence']          ?? 50,
                        'extraction_quality' => $asset['quality']['extraction_quality']  ?? 0,
                        'seen_count'         => 1,
                        'export_ready'       => false,  // already came from cloud — no re-export
                        'is_validated'       => true,   // cloud-validated asset
                        'is_exported'        => true,   // already in cloud
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    $saved++;
                }
            } catch (\Throwable) {
                // Non-critical — skip bad records
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('');

        // Save last sync time
        cache(['rgin_last_sync_at' => $result['synced_at'] ?? now()->toISOString()], now()->addWeek());

        $this->info("Sync complete.");
        $this->table(['Metric', 'Count'], [
            ['Assets received',    $total],
            ['New assets saved',   $saved],
            ['Already had',        $total - $saved],
        ]);

        if ($result['has_more'] ?? false) {
            $this->line('');
            $this->warn('More assets available. Run intelligence:sync again to get the next batch.');
        }

        // Optional network stats
        if ($this->option('stats')) {
            $this->line('');
            $this->line('Network Statistics:');
            $stats = $client->getNetworkStats();
            if (!empty($stats['stats'])) {
                $s = $stats['stats'];
                $this->table(['Metric', 'Value'], [
                    ['Total network assets',   number_format($s['total_assets']    ?? 0)],
                    ['Total nodes',            number_format($s['total_nodes']     ?? 0)],
                    ['Assets validated today', number_format($s['validated_today'] ?? 0)],
                    ['AI calls avoided',       number_format($s['ai_avoided']      ?? 0)],
                    ['Dev hours saved',        number_format($s['hours_saved']     ?? 0)],
                ]);
            }
        }

        return self::SUCCESS;
    }
}
