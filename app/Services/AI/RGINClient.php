<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RGIN Client — RyaanCMS Global Intelligence Network HTTP Client
 *
 * Connects every local RyaanCMS installation to the Central Intelligence Cloud.
 * All communication is one-directional intelligence metadata — never customer data.
 *
 * Config (.env):
 *   RGIN_CLOUD_URL   — Central Cloud API base URL
 *   RGIN_API_KEY     — Installation API key (issued by cloud on registration)
 *   RGIN_ENABLED     — true/false toggle (default: false, opt-in)
 *   RGIN_TIMEOUT     — HTTP timeout in seconds (default: 15)
 */
class RGINClient
{
    private string $baseUrl;
    private string $apiKey;
    private bool   $enabled;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl  = rtrim((string) config('rgin.cloud_url', ''), '/');
        $this->apiKey   = (string) config('rgin.api_key', '');
        $this->enabled  = (bool)   config('rgin.enabled', false);
        $this->timeout  = (int)    config('rgin.timeout', 15);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Connectivity
    // ─────────────────────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->baseUrl) && !empty($this->apiKey);
    }

    public function ping(): bool
    {
        if (!$this->isConfigured()) return false;
        try {
            $response = Http::timeout(5)
                ->withHeaders($this->headers())
                ->get("{$this->baseUrl}/api/ping");
            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Node registration
    // Called once when RGIN is first enabled on an installation
    // ─────────────────────────────────────────────────────────────────────────

    public function register(): array
    {
        if (!$this->isConfigured()) return $this->disabled();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers())
                ->post("{$this->baseUrl}/api/nodes/register", [
                    'app_url'     => config('app.url'),
                    'app_name'    => config('app.name'),
                    'ryaan_version' => config('app.version', '1.0'),
                    'php_version' => PHP_VERSION,
                    'registered_at' => now()->toISOString(),
                ]);

            return $response->successful()
                ? ['success' => true, 'node_id' => $response->json('node_id'), 'message' => $response->json('message')]
                : ['success' => false, 'error' => $response->json('message', 'Registration failed')];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Push — submit local intelligence assets to the cloud
    // Only sends export-ready, quality-scored metadata. Never customer data.
    // ─────────────────────────────────────────────────────────────────────────

    public function push(array $assets): array
    {
        if (!$this->isConfigured()) return $this->disabled();
        if (empty($assets)) return ['success' => true, 'submitted' => 0, 'message' => 'Nothing to push'];

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers())
                ->post("{$this->baseUrl}/api/intelligence/submit", [
                    'assets'      => $assets,
                    'node_id'     => config('rgin.node_id'),
                    'submitted_at'=> now()->toISOString(),
                    'batch_size'  => count($assets),
                ]);

            if ($response->successful()) {
                return [
                    'success'   => true,
                    'submitted' => $response->json('accepted', 0),
                    'rejected'  => $response->json('rejected', 0),
                    'duplicates'=> $response->json('duplicates', 0),
                    'message'   => $response->json('message', 'Assets submitted'),
                ];
            }

            Log::warning('RGIN push failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'error' => $response->json('message', 'Push failed'), 'submitted' => 0];
        } catch (\Throwable $e) {
            Log::error('RGIN push exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'submitted' => 0];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sync — pull validated assets from the cloud
    // Downloads new/updated intelligence since last sync
    // ─────────────────────────────────────────────────────────────────────────

    public function sync(?string $since = null): array
    {
        if (!$this->isConfigured()) return $this->disabled();

        try {
            $params = ['node_id' => config('rgin.node_id')];
            if ($since) $params['since'] = $since;

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers())
                ->get("{$this->baseUrl}/api/intelligence/sync", $params);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'assets'     => $response->json('assets', []),
                    'total'      => $response->json('total', 0),
                    'synced_at'  => $response->json('synced_at'),
                    'has_more'   => $response->json('has_more', false),
                ];
            }

            return ['success' => false, 'error' => $response->json('message', 'Sync failed'), 'assets' => []];
        } catch (\Throwable $e) {
            Log::error('RGIN sync exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'assets' => []];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Gaps — pull global gap intelligence
    // What assets are most requested but missing — roadmap data
    // ─────────────────────────────────────────────────────────────────────────

    public function getGlobalGaps(int $limit = 50): array
    {
        if (!$this->isConfigured()) return $this->disabled();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers())
                ->get("{$this->baseUrl}/api/intelligence/gaps", [
                    'limit'   => $limit,
                    'node_id' => config('rgin.node_id'),
                ]);

            return $response->successful()
                ? ['success' => true, 'gaps' => $response->json('gaps', [])]
                : ['success' => false, 'error' => $response->json('message', 'Failed'), 'gaps' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'gaps' => []];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Network stats — global intelligence network metrics
    // ─────────────────────────────────────────────────────────────────────────

    public function getNetworkStats(): array
    {
        if (!$this->isConfigured()) return $this->disabled();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers())
                ->get("{$this->baseUrl}/api/network/stats");

            return $response->successful()
                ? ['success' => true, 'stats' => $response->json('stats', [])]
                : ['success' => false, 'stats' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'stats' => []];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'X-RGIN-Version'=> '1.0',
            'X-Node-ID'     => (string) config('rgin.node_id', ''),
        ];
    }

    private function disabled(): array
    {
        return ['success' => false, 'error' => 'RGIN not enabled. Set RGIN_ENABLED=true in .env', 'assets' => [], 'gaps' => []];
    }
}
