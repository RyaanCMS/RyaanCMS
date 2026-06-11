<?php

namespace App\Services\Central;

use App\Models\Central\CentralInstall;
use App\Models\Central\CentralLicense;
use Illuminate\Support\Facades\DB;

class LicenseManager
{
    /**
     * Validate a license key (called by customer installs).
     * Also upserts the install record so you see who's online.
     */
    public function validate(string $licenseKey, string $domain, array $meta = []): array
    {
        $license = CentralLicense::where('license_key', $licenseKey)->first();

        if (!$license) {
            return ['valid' => false, 'error' => 'License key not found.'];
        }

        if ($license->status === 'suspended') {
            return ['valid' => false, 'error' => 'License suspended. Contact support.'];
        }

        if ($license->isExpired()) {
            $license->update(['status' => 'expired']);
            return ['valid' => false, 'error' => 'License expired.'];
        }

        // Bind domain on first use
        if (!$license->domain) {
            $license->update(['domain' => $domain]);
        }

        // Track this install
        CentralInstall::updateOrCreate(
            ['domain' => $domain],
            array_merge([
                'license_key'     => $licenseKey,
                'last_ping'       => now(),
                'last_ip'         => request()->ip(),
                'app_version'     => $meta['app_version'] ?? null,
                'php_version'     => $meta['php_version'] ?? null,
                'laravel_version' => $meta['laravel_version'] ?? null,
                'user_count'      => $meta['user_count'] ?? 1,
                'project_count'   => $meta['project_count'] ?? 0,
            ])
        );

        return $license->toApiResponse();
    }

    /**
     * Issue a new license for a plan.
     */
    public function issue(string $plan, array $attrs = []): CentralLicense
    {
        return CentralLicense::generate($plan, $attrs);
    }

    /**
     * Revoke / suspend a license.
     */
    public function suspend(string $licenseKey, string $reason = ''): bool
    {
        return (bool) CentralLicense::where('license_key', $licenseKey)
            ->update(['status' => 'suspended', 'notes' => $reason]);
    }

    /**
     * Reactivate a suspended license.
     */
    public function activate(string $licenseKey): bool
    {
        return (bool) CentralLicense::where('license_key', $licenseKey)
            ->update(['status' => 'active']);
    }

    /**
     * Summary stats for admin dashboard.
     */
    public function stats(): array
    {
        return [
            'total'      => CentralLicense::count(),
            'active'     => CentralLicense::where('status', 'active')->count(),
            'suspended'  => CentralLicense::where('status', 'suspended')->count(),
            'by_plan'    => CentralLicense::selectRaw('plan, COUNT(*) as count')
                ->groupBy('plan')->pluck('count', 'plan')->toArray(),
            'installs'   => CentralInstall::count(),
            'active_installs' => CentralInstall::active()->count(),
        ];
    }
}
