<?php

namespace App\Console\Commands;

use App\Services\Registry\RegistrySyncService;
use Illuminate\Console\Command;

class SyncRegistryCommand extends Command
{
    protected $signature   = 'ryaan:sync-registry {--force : Force re-download of all packs even if version is current}';
    protected $description = 'Sync Question Packs from the central RyaanCMS registry';

    public function handle(RegistrySyncService $sync): int
    {
        $force = $this->option('force');

        $this->info('Connecting to registry: ' . config('registry.url'));

        $result = $sync->sync($force);

        if (!$result['success']) {
            $this->error('Sync failed: ' . ($result['error'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        $this->table(
            ['Checked', 'Updated', 'Skipped', 'Duration (ms)'],
            [[
                $result['packs_checked'],
                $result['packs_updated'],
                $result['packs_skipped'],
                $result['duration_ms'],
            ]]
        );

        $this->info('Registry sync complete.');

        // Show available updates for any packs not yet downloaded
        $outdated = $sync->checkForUpdates();
        if (!empty($outdated)) {
            $this->warn(count($outdated) . ' pack(s) have updates available:');
            foreach ($outdated as $p) {
                $badge = $p['is_premium'] ? ' [premium]' : '';
                $this->line("  {$p['slug']}: {$p['current_version']} → {$p['latest_version']}{$badge}");
            }
        }

        return self::SUCCESS;
    }
}
