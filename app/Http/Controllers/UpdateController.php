<?php

namespace App\Http\Controllers;

use App\Models\UpdateHistory;
use App\Services\UpdateService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private UpdateService $updater) {}

    public function index()
    {
        $currentVersion = $this->updater->getCurrentVersion();

        try {
            $history = UpdateHistory::latest('started_at')->limit(30)->get();
        } catch (\Exception) {
            $history = collect();
        }

        try {
            $manifest   = $this->updater->getManifest();
            $pending    = $this->updater->getPendingUpdates($currentVersion);
            $latest     = $manifest['latest'] ?? $currentVersion;
            $hasUpdates = !empty($pending);
            $error      = null;
        } catch (\Exception $e) {
            $manifest   = [];
            $pending    = [];
            $latest     = $currentVersion;
            $hasUpdates = false;
            $error      = $e->getMessage();
        }

        // PHP / server info
        $serverInfo = [
            'php'        => PHP_VERSION,
            'laravel'    => app()->version(),
            'zip'        => class_exists('ZipArchive'),
            'writable'   => is_writable(storage_path()),
        ];

        return view('settings.updates', compact(
            'currentVersion', 'latest', 'pending', 'hasUpdates', 'history', 'error', 'serverInfo'
        ));
    }

    /** Force-refresh the manifest cache and return current update status. */
    public function check(Request $request)
    {
        $this->updater->forgetManifestCache();

        try {
            $manifest = $this->updater->getManifest(true);
            $pending  = $this->updater->getPendingUpdates();

            return response()->json([
                'success'    => true,
                'current'    => $this->updater->getCurrentVersion(),
                'latest'     => $manifest['latest'],
                'hasUpdates' => !empty($pending),
                'count'      => count($pending),
                'pending'    => $pending,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Apply ONE pending update step.
     * The frontend loops — calling this endpoint repeatedly until remaining === 0.
     * One step per request keeps each request within PHP's max_execution_time.
     */
    public function apply(Request $request)
    {
        // Allow long-running downloads on restrictive shared hosts
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $request->validate([
            'version' => ['nullable', 'string', 'max:20'],
        ]);

        $targetVersion = $request->input('version', 'latest');
        $current       = $this->updater->getCurrentVersion();

        if ($targetVersion === 'latest') {
            $allSteps = $this->updater->getPendingUpdates($current);
        } else {
            $allSteps = $this->updater->getUpdatePath($current, $targetVersion);
        }

        if (empty($allSteps)) {
            return response()->json([
                'success' => false,
                'message' => 'RyaanCMS is already up to date.',
            ], 422);
        }

        // Prevent concurrent updates via a simple lock
        $lockFile = storage_path('updates/.updating');
        if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 600) {
            return response()->json([
                'success' => false,
                'message' => 'An update is already in progress. Please wait.',
            ], 409);
        }

        if (!is_dir(storage_path('updates'))) mkdir(storage_path('updates'), 0755, true);
        file_put_contents($lockFile, date('c'));

        // Apply only the FIRST step — frontend loops for each remaining step
        $step = $allSteps[0];

        try {
            $this->updater->applyVersion($step);
        } catch (\Throwable $e) {
            @unlink($lockFile);
            return response()->json([
                'success' => false,
                'applied' => [],
                'message' => "v{$step['version']}: " . $e->getMessage(),
                'hint'    => 'Update failed. Check server error logs for details.',
            ], 422);
        }

        @unlink($lockFile);

        $newVersion = $step['version'];
        $remaining  = count($allSteps) - 1;

        return response()->json([
            'success'    => true,
            'applied'    => [$newVersion],
            'newVersion' => $newVersion,
            'remaining'  => $remaining,
            'message'    => $remaining > 0
                ? "Applied v{$newVersion} — {$remaining} more update" . ($remaining > 1 ? 's' : '') . ' pending.'
                : "Successfully updated to v{$newVersion}.",
        ]);
    }

    /** Apply a plugin/template update. */
    public function applyPlugin(Request $request, string $pluginKey)
    {
        $request->validate([
            'current_version' => ['required', 'string'],
        ]);

        $currentVersion = $request->input('current_version');
        $pending        = $this->updater->getPendingPluginUpdates($pluginKey, $currentVersion);

        if (empty($pending)) {
            return response()->json(['success' => false, 'message' => 'Plugin is already up to date.'], 422);
        }

        $applied = [];
        $failed  = null;

        try {
            foreach ($pending as $step) {
                $this->updater->applyVersion($step, 'plugin', $pluginKey);
                $applied[] = $step['version'];
            }
        } catch (\Throwable $e) {
            $failed = $e->getMessage();
        }

        if ($failed) {
            return response()->json(['success' => false, 'applied' => $applied, 'message' => $failed], 422);
        }

        return response()->json([
            'success' => true,
            'applied' => $applied,
            'message' => 'Plugin updated successfully.',
        ]);
    }
}
