<?php

namespace App\Services;

use App\Models\UpdateHistory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class UpdateService
{
    private const CACHE_KEY = 'ryaan_update_manifest_v1';
    private string $updatesDir;

    public function __construct()
    {
        $this->updatesDir = storage_path('updates');
    }

    // ──────────────────────────────────────────────
    // Manifest
    // ──────────────────────────────────────────────

    public function getManifest(bool $force = false): array
    {
        if (!$force && Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        $url = config('version.manifest_url');

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->ok()) {
                throw new \RuntimeException('Update server returned HTTP ' . $response->status());
            }

            $manifest = $response->json();

            if (!isset($manifest['versions']) || !is_array($manifest['versions'])) {
                throw new \RuntimeException('Invalid manifest format from update server.');
            }

            Cache::put(self::CACHE_KEY, $manifest, now()->addHours(6));
            return $manifest;

        } catch (\Exception $e) {
            // Return cached copy if available even after expiry
            if (Cache::has(self::CACHE_KEY)) {
                return Cache::get(self::CACHE_KEY);
            }
            throw $e;
        }
    }

    public function forgetManifestCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ──────────────────────────────────────────────
    // Version helpers
    // ──────────────────────────────────────────────

    public function getCurrentVersion(): string
    {
        $envVersion = $this->readEnvVersion();
        if ($envVersion !== null) {
            return $envVersion;
        }

        $configured = trim((string) config('version.current', ''));

        return $this->getBundledVersion() ?? ($configured !== '' ? $configured : '1.0.0');
    }

    public function getLatestVersion(): string
    {
        return $this->getManifest()['latest'] ?? $this->getCurrentVersion();
    }

    public function hasUpdates(): bool
    {
        try {
            return version_compare($this->getLatestVersion(), $this->getCurrentVersion(), '>');
        } catch (\Exception) {
            return false;
        }
    }

    /** Returns versions newer than $current, sorted ascending. */
    public function getPendingUpdates(string $current = null): array
    {
        $current ??= $this->getCurrentVersion();
        $manifest  = $this->getManifest();

        return $this->sortVersions(collect($manifest['versions'])
            ->filter(fn($v) => version_compare($v['version'], $current, '>'))
            ->values()
            ->toArray());
    }

    /** Versions between $from (exclusive) and $to (inclusive), sorted ascending. */
    public function getUpdatePath(string $from, string $to): array
    {
        $manifest = $this->getManifest();

        return $this->sortVersions(collect($manifest['versions'])
            ->filter(fn($v) =>
                version_compare($v['version'], $from, '>') &&
                version_compare($v['version'], $to,   '<=')
            )
            ->values()
            ->toArray());
    }

    // ──────────────────────────────────────────────
    // Plugin version helpers
    // ──────────────────────────────────────────────

    public function getPluginVersions(string $pluginKey): array
    {
        $manifest = $this->getManifest();
        return $manifest['plugins'][$pluginKey] ?? [];
    }

    public function getPendingPluginUpdates(string $pluginKey, string $currentVersion): array
    {
        $info = $this->getPluginVersions($pluginKey);
        if (empty($info['versions'])) return [];

        return $this->sortVersions(collect($info['versions'])
            ->filter(fn($v) => version_compare($v['version'], $currentVersion, '>'))
            ->values()
            ->toArray());
    }

    // ──────────────────────────────────────────────
    // Apply update
    // ──────────────────────────────────────────────

    /**
     * Download, extract, copy, migrate one version step.
     * Throws on any failure; records status in update_history.
     */
    public function applyVersion(array $versionInfo, string $type = 'core', ?string $packageKey = null): void
    {
        $version     = $versionInfo['version'];
        $zipPath     = $this->updatesDir . DIRECTORY_SEPARATOR . "ryaancms-{$version}.zip";
        $extractPath = $this->updatesDir . DIRECTORY_SEPARATOR . "extracted-{$version}";

        if (!is_dir($this->updatesDir)) {
            mkdir($this->updatesDir, 0755, true);
        }

        $record = $this->startUpdateRecord($version, $type, $packageKey, $versionInfo['changelog'] ?? '');

        try {
            // 1. Download
            $this->downloadZip($versionInfo['download_url'], $zipPath);

            $this->markUpdateRecord($record, ['status' => 'extracting']);

            // 2. Extract
            $this->extractZip($zipPath, $extractPath);

            $this->markUpdateRecord($record, ['status' => 'applying']);

            // 3. Copy files (preserve .env, user storage, .git)
            [$appSource, $publicSource] = $this->resolvePackageSources($extractPath);
            $this->copyFiles($appSource, base_path());

            if ($publicSource !== null) {
                $this->copyFiles($publicSource, $this->detectPublicRoot(), ['storage']);
            }

            $this->markUpdateRecord($record, ['status' => 'migrating']);

            // 4. Migrate
            if ($versionInfo['requires_migration'] ?? true) {
                Artisan::call('migrate', ['--force' => true]);
            }

            // 5. Bump version in .env before clearing cached config
            $this->updateEnvVersion($version);

            // 6. Clear caches
            $this->clearCaches();

            $this->markUpdateRecord($record, [
                'status'       => 'success',
                'completed_at' => now(),
            ]);

        } catch (\Throwable $e) {
            $this->markUpdateRecord($record, [
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);
            throw $e;

        } finally {
            if (file_exists($zipPath))   @unlink($zipPath);
            if (is_dir($extractPath))    $this->deleteDirectory($extractPath);
        }
    }

    // ──────────────────────────────────────────────
    // Internals
    // ──────────────────────────────────────────────

    private function downloadZip(string $url, string $dest): void
    {
        $fallback = 'https://github.com/RyaanCMS/RyaanCMS/archive/refs/heads/main.zip';
        $urls     = array_values(array_unique([$url, $fallback]));

        $lastError = null;
        foreach ($urls as $tryUrl) {
            $fp = fopen($dest, 'wb');
            if (!$fp) {
                throw new \RuntimeException("Cannot create download file at: {$dest}");
            }
            try {
                $response = Http::timeout(300)->withOptions(['sink' => $fp])->get($tryUrl);
                if ($response->ok()) {
                    return;
                }
                $lastError = "HTTP {$response->status()} from {$tryUrl}";
            } finally {
                fclose($fp);
            }
        }

        throw new \RuntimeException("Download failed — tried {$urls[0]}" . (count($urls) > 1 ? " and fallback {$urls[1]}" : '') . ". Last error: {$lastError}");
    }

    private function extractZip(string $zipPath, string $extractPath): void
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive PHP extension is not available on this server. Contact your hosting provider.');
        }

        $zip    = new \ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            throw new \RuntimeException("Cannot open ZIP file (ZipArchive error: {$result})");
        }

        if (!is_dir($extractPath)) mkdir($extractPath, 0755, true);

        $zip->extractTo($extractPath);
        $zip->close();

        // GitHub archives nest files inside RepoName-tagname/
        // Detect and unwrap single top-level directory automatically
        $items = array_values(array_diff(scandir($extractPath), ['.', '..']));
        if (count($items) === 1 && is_dir("{$extractPath}/{$items[0]}")) {
            $subDir = "{$extractPath}/{$items[0]}";
            $this->moveDirectoryContents($subDir, $extractPath);
            @rmdir($subDir);
        }
    }

    private function moveDirectoryContents(string $from, string $to): void
    {
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iter as $item) {
            $dest = $to . DIRECTORY_SEPARATOR . $item->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($dest)) mkdir($dest, 0755, true);
            } else {
                $destDir = dirname($dest);
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                rename($item->getPathname(), $dest);
            }
        }
    }

    private function resolvePackageSources(string $extractPath): array
    {
        $legacyApp = $extractPath . DIRECTORY_SEPARATOR . 'ryaancms';
        $legacyPublic = $extractPath . DIRECTORY_SEPARATOR . 'public_html';

        if (is_dir($legacyApp) && file_exists($legacyApp . DIRECTORY_SEPARATOR . 'artisan')) {
            return [$legacyApp, is_dir($legacyPublic) ? $legacyPublic : null];
        }

        if (file_exists($extractPath . DIRECTORY_SEPARATOR . 'artisan')) {
            return [$extractPath, null];
        }

        throw new \RuntimeException('Invalid update package: Laravel app root was not found.');
    }

    private function detectPublicRoot(): string
    {
        $base = base_path();

        if (file_exists($base . DIRECTORY_SEPARATOR . 'index.php')) {
            return $base;
        }

        $siblingPublicHtml = dirname($base) . DIRECTORY_SEPARATOR . 'public_html';
        if (is_dir($siblingPublicHtml)) {
            return $siblingPublicHtml;
        }

        return public_path();
    }

    private function copyFiles(string $sourcePath, string $destRoot, array $extraSkips = []): void
    {
        // Never overwrite these — user data / env config
        $neverOverwrite = array_merge(['.env', 'storage/app', 'storage/logs', '.git', 'node_modules'], $extraSkips);

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iter as $item) {
            $relative = $item->getSubPathname();

            foreach ($neverOverwrite as $skip) {
                if ($relative === $skip || str_starts_with($relative, $skip . '/') || str_starts_with($relative, $skip . DIRECTORY_SEPARATOR)) {
                    continue 2;
                }
            }

            $dest = $destRoot . DIRECTORY_SEPARATOR . $relative;

            if ($item->isDir()) {
                if (!is_dir($dest)) mkdir($dest, 0755, true);
            } else {
                $destDir = dirname($dest);
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                copy($item->getPathname(), $dest);
            }
        }
    }

    private function clearCaches(): void
    {
        // Invalidate PHP bytecode cache so newly-copied PHP files are loaded immediately.
        // Without this, opcache can serve stale bytecode after a file update on LiteSpeed/cPanel.
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        foreach (['config:clear', 'cache:clear', 'view:clear', 'route:clear'] as $cmd) {
            try { Artisan::call($cmd); } catch (\Throwable) {}
        }
    }

    private function sortVersions(array $versions): array
    {
        usort($versions, fn($a, $b) => version_compare($a['version'] ?? '0.0.0', $b['version'] ?? '0.0.0'));

        return array_values($versions);
    }

    private function readEnvVersion(): ?string
    {
        $candidates = [];

        // Source 1: APP_VERSION in .env (may be stale if .env is read-only on cPanel)
        $envPath = base_path('.env');
        if (is_file($envPath)) {
            $content = (string) file_get_contents($envPath);
            if (preg_match('/^APP_VERSION=(.+)$/m', $content, $matches) === 1) {
                $v = trim($matches[1], " \t\n\r\0\x0B\"'");
                if ($v !== '') $candidates[] = $v;
            }
        }

        // Source 2: storage/app/.app_version (writable even when .env is 444/644)
        $storageFile = storage_path('app/.app_version');
        if (is_file($storageFile)) {
            $v = trim((string) file_get_contents($storageFile));
            if ($v !== '') $candidates[] = $v;
        }

        // Source 3: versions.json "installed" field — most reliable write target because
        // versions.json lives in the app root which is always writable by the web process.
        $versionsFile = base_path('versions.json');
        if (is_file($versionsFile)) {
            $data = json_decode((string) file_get_contents($versionsFile), true);
            if (!empty($data['installed'])) $candidates[] = (string) $data['installed'];
        }

        if (empty($candidates)) return null;

        // Return the newest version found across all sources.
        usort($candidates, 'version_compare');
        return end($candidates);
    }

    private function getBundledVersion(): ?string
    {
        $versionsFile = base_path('versions.json');
        if (!is_file($versionsFile)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($versionsFile), true);

        return is_array($data) && !empty($data['latest']) ? (string) $data['latest'] : null;
    }

    private function startUpdateRecord(string $version, string $type, ?string $packageKey, string $changelog): ?UpdateHistory
    {
        try {
            if (!Schema::hasTable('update_history')) {
                return null;
            }

            return UpdateHistory::updateOrCreate(
                ['version' => $version, 'type' => $type, 'package_key' => $packageKey],
                [
                    'status'     => 'downloading',
                    'changelog'  => $changelog,
                    'started_at' => now(),
                    'error_message' => null,
                    'completed_at'  => null,
                ]
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function markUpdateRecord(?UpdateHistory $record, array $values): void
    {
        if ($record === null) {
            return;
        }

        try {
            $record->update($values);
        } catch (\Throwable) {
            //
        }
    }

    private function updateEnvVersion(string $version): void
    {
        // 1. Update APP_VERSION in .env (may fail if .env is read-only on cPanel)
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';
        if (str_contains($content, 'APP_VERSION=')) {
            $content = preg_replace('/^APP_VERSION=.*/m', "APP_VERSION={$version}", $content);
        } else {
            $content = rtrim($content) . "\nAPP_VERSION={$version}\n";
        }
        $envWritten = file_put_contents($envPath, $content);

        // 2. Write to storage/app/.app_version (writable even when .env is 444/644)
        $storageDir = storage_path('app');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        file_put_contents(storage_path('app/.app_version'), $version);

        // 3. Write installed version into versions.json — most reliable target because the
        // app root is always writable by the web process (used as tiebreaker in readEnvVersion).
        $versionsFile = base_path('versions.json');
        if (is_file($versionsFile)) {
            $data = json_decode((string) file_get_contents($versionsFile), true) ?? [];
        } else {
            $data = [];
        }
        $data['installed'] = $version;
        file_put_contents($versionsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($envWritten === false) {
            \Illuminate\Support\Facades\Log::warning("UpdateService: could not write APP_VERSION to .env; version stored in storage/app/.app_version and versions.json.");
        }

        config(['version.current' => $version]);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
