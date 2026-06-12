<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    private string $cloudUrl;
    private string $productId;
    private int    $timeout;

    public function __construct()
    {
        $this->cloudUrl  = rtrim(config('license.cloud_url', 'https://cloud.ryaancms.com'), '/');
        $this->productId = config('license.product_id', 'ryaancms');
        $this->timeout   = (int) config('license.timeout', 10);
    }

    /**
     * Activate a purchase code against RyaanCMSCloud.
     * Returns ['success' => bool, 'message' => string, 'license' => License|null]
     */
    public function activate(string $purchaseCode): array
    {
        $purchaseCode = trim($purchaseCode);

        if (empty($purchaseCode)) {
            return ['success' => false, 'message' => 'Purchase code is required.'];
        }

        $domain = $this->currentDomain();

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->cloudUrl}/api/purchase-codes/verify", [
                    'purchase_code' => $purchaseCode,
                    'domain'        => $domain,
                    'product_id'    => $this->productId,
                ]);

            $data = $response->json();

            if (! $response->successful() || empty($data['success'])) {
                $message = $data['message'] ?? 'Verification failed. Please check your purchase code.';
                return ['success' => false, 'message' => $message];
            }

            // Deactivate any previously active license before saving new one
            License::where('status', 'active')->update(['status' => 'inactive']);

            $license = License::updateOrCreate(
                ['purchase_code' => $purchaseCode],
                [
                    'license_token'    => $data['license_token'] ?? null,
                    'domain'           => $domain,
                    'product_id'       => $data['product_id']   ?? $this->productId,
                    'product_name'     => $data['product_name'] ?? null,
                    'buyer_email'      => $data['buyer_email']  ?? null,
                    'status'           => 'active',
                    'meta'             => $data['meta']         ?? null,
                    'activated_at'     => now(),
                    'expires_at'       => isset($data['expires_at']) ? \Carbon\Carbon::parse($data['expires_at']) : null,
                    'last_verified_at' => now(),
                ]
            );

            return ['success' => true, 'message' => $data['message'] ?? 'License activated successfully.', 'license' => $license];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return ['success' => false, 'message' => 'Could not reach the license server. Please check your internet connection and try again.'];
        } catch (\Throwable $e) {
            Log::error('LicenseService::activate error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An unexpected error occurred during activation.'];
        }
    }

    /**
     * Deactivate the current license (releases the domain slot on the cloud).
     */
    public function deactivate(): array
    {
        $license = License::getActive();

        if (! $license) {
            return ['success' => false, 'message' => 'No active license found.'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->cloudUrl}/api/license/deactivate", [
                    'license_token' => $license->license_token,
                    'domain'        => $this->currentDomain(),
                    'product_id'    => $this->productId,
                ]);

            // Mark inactive locally regardless of cloud response (best effort)
            $license->update(['status' => 'inactive']);

            $data = $response->json();
            $message = $data['message'] ?? 'License deactivated.';

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            // Deactivate locally even if cloud is unreachable
            $license->update(['status' => 'inactive']);
            Log::warning('LicenseService::deactivate cloud unreachable — deactivated locally.', ['error' => $e->getMessage()]);
            return ['success' => true, 'message' => 'License deactivated locally. Cloud sync may be delayed.'];
        }
    }

    /**
     * Send a heartbeat ping to keep the activation alive.
     * Called by scheduler (daily). Returns false silently if cloud is down (grace period applies).
     */
    public function ping(): bool
    {
        $license = License::getActive();

        if (! $license || ! $license->license_token) {
            return false;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->cloudUrl}/api/license/ping", [
                    'license_token' => $license->license_token,
                    'domain'        => $this->currentDomain(),
                    'product_id'    => $this->productId,
                ]);

            $data = $response->json();

            if ($response->successful() && ! empty($data['valid'])) {
                $license->update(['last_verified_at' => now()]);
                return true;
            }

            // Cloud explicitly said invalid — suspend
            if ($response->successful() && isset($data['valid']) && ! $data['valid']) {
                $license->update(['status' => 'suspended']);
                Log::warning('LicenseService::ping — license suspended by cloud.');
            }

            return false;

        } catch (\Throwable $e) {
            Log::warning('LicenseService::ping failed — grace period active.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if this installation has a valid active license.
     * Respects grace period when cloud is temporarily unreachable.
     */
    public function isActivated(): bool
    {
        $license = License::getActive();

        if (! $license) {
            return false;
        }

        return $license->isActive();
    }

    /**
     * Return the active license with status details for display.
     */
    public function status(): array
    {
        $license = License::getActive();

        if (! $license) {
            return [
                'activated'  => false,
                'status'     => 'inactive',
                'domain'     => $this->currentDomain(),
            ];
        }

        return [
            'activated'        => $license->isActive(),
            'status'           => $license->status,
            'purchase_code'    => $license->purchase_code,
            'product_name'     => $license->product_name,
            'buyer_email'      => $license->buyer_email,
            'domain'           => $license->domain,
            'activated_at'     => $license->activated_at?->toDateTimeString(),
            'expires_at'       => $license->expires_at?->toDateTimeString(),
            'last_verified_at' => $license->last_verified_at?->toDateTimeString(),
            'within_grace'     => $license->isWithinGrace(),
        ];
    }

    private function currentDomain(): string
    {
        $url = config('app.url', '');
        $host = parse_url($url, PHP_URL_HOST);
        return $host ?: request()->getHost();
    }
}
