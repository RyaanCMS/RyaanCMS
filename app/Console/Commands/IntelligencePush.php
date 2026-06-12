<?php

namespace App\Console\Commands;

use App\Services\AI\IntelligenceCollector;
use App\Services\AI\RGINClient;
use Illuminate\Console\Command;

class IntelligencePush extends Command
{
    protected $signature = 'intelligence:push
                            {--dry-run : Show what would be pushed without actually pushing}
                            {--limit=50 : Maximum assets to push in one batch}
                            {--force : Push even assets below quality threshold}';

    protected $description = 'Push locally collected intelligence assets to the RGIN Central Cloud';

    public function handle(IntelligenceCollector $collector, RGINClient $client): int
    {
        $this->info('RyaanCMS Global Intelligence Network — Push');
        $this->line('');

        // Check connectivity
        if (!$client->isConfigured()) {
            $this->error('RGIN not configured. Add to .env:');
            $this->line('  RGIN_ENABLED=true');
            $this->line('  RGIN_CLOUD_URL=https://cloud.ryaancms.com');
            $this->line('  RGIN_API_KEY=your_api_key');
            return self::FAILURE;
        }

        $this->line('Checking cloud connectivity...');
        if (!$client->ping()) {
            $this->error('Cannot reach RGIN Cloud. Check RGIN_CLOUD_URL and network connection.');
            return self::FAILURE;
        }
        $this->line('<fg=green>✓</> Cloud reachable');
        $this->line('');

        // Get export queue
        $limit = (int) $this->option('limit');
        $queue = $collector->getExportQueue($limit);

        if (empty($queue)) {
            $this->info('Nothing to push — no export-ready assets in local registry.');
            $this->line('Tip: Run some builds first. Intelligence is collected automatically.');
            return self::SUCCESS;
        }

        $this->line("Found <fg=yellow>{$limit}</> export-ready assets (quality ≥ 70).");
        $this->line('');

        // Show table of what will be pushed
        $tableRows = array_map(fn($a) => [
            $a['domain'],
            $a['asset_type'],
            $a['name'],
            $a['extraction_quality'] . '%',
            $a['seen_count'] . 'x seen',
        ], $queue);

        $this->table(['Domain', 'Type', 'Name', 'Quality', 'Seen'], $tableRows);
        $this->line('');

        if ($this->option('dry-run')) {
            $this->warn('Dry run — nothing pushed. Remove --dry-run to push for real.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Push {$limit} assets to RGIN Cloud?", true)) {
            $this->line('Cancelled.');
            return self::SUCCESS;
        }

        // Extract the export packages
        $packages = array_column($queue, 'export_package');

        $this->line('Pushing to cloud...');
        $result = $client->push($packages);

        if (!$result['success']) {
            $this->error('Push failed: ' . $result['error']);
            return self::FAILURE;
        }

        // Mark pushed assets as exported
        $pushed = $result['submitted'] ?? 0;
        $ids    = array_column(array_slice($queue, 0, $pushed), 'id');
        foreach ($ids as $id) {
            $collector->markExported($id);
        }

        $this->line('');
        $this->info("Push complete.");
        $this->table(['Metric', 'Count'], [
            ['Submitted',  $result['submitted']  ?? 0],
            ['Rejected',   $result['rejected']   ?? 0],
            ['Duplicates', $result['duplicates'] ?? 0],
        ]);

        // Show network impact after push
        $impact = $collector->calculateNetworkImpact();
        if (!empty($impact['impact_message'])) {
            $this->line('');
            $this->line("<fg=cyan>{$impact['impact_message']}</>");
        }

        return self::SUCCESS;
    }
}
