<?php

namespace App\Services\Central;

use App\Models\Central\CentralCreditLedger;
use App\Models\Central\CentralLicense;
use Illuminate\Support\Facades\DB;

class CreditManager
{
    /**
     * Check remaining credits for a license key.
     */
    public function check(string $licenseKey): array
    {
        $license = CentralLicense::where('license_key', $licenseKey)->first();

        if (!$license || !$license->isActive()) {
            return ['ok' => false, 'error' => 'Invalid or inactive license.'];
        }

        return [
            'ok'        => true,
            'remaining' => $license->creditsRemaining(),
            'included'  => $license->credits_included,
            'used'      => $license->credits_used,
            'plan'      => $license->plan,
        ];
    }

    /**
     * Deduct credits atomically. Returns success/failure.
     */
    public function deduct(string $licenseKey, int $amount, string $domain = '', string $description = '', string $reference = ''): array
    {
        return DB::transaction(function () use ($licenseKey, $amount, $domain, $description, $reference) {
            $license = CentralLicense::where('license_key', $licenseKey)
                ->lockForUpdate()
                ->first();

            if (!$license || !$license->isActive()) {
                return ['ok' => false, 'error' => 'Invalid or inactive license.'];
            }

            if ($license->creditsRemaining() < $amount) {
                return [
                    'ok'        => false,
                    'error'     => 'Insufficient credits.',
                    'remaining' => $license->creditsRemaining(),
                ];
            }

            $newUsed = $license->credits_used + $amount;
            $license->update(['credits_used' => $newUsed]);

            CentralCreditLedger::create([
                'license_key'  => $licenseKey,
                'domain'       => $domain,
                'type'         => 'usage',
                'amount'       => -$amount,
                'balance_after' => $license->credits_included - $newUsed,
                'description'  => $description ?: "AI generation",
                'reference'    => $reference,
                'recorded_at'  => now(),
            ]);

            return [
                'ok'        => true,
                'remaining' => $license->credits_included - $newUsed,
                'deducted'  => $amount,
            ];
        });
    }

    /**
     * Add (top-up) credits to a license.
     */
    public function topup(string $licenseKey, int $amount, string $type = 'topup', string $description = ''): array
    {
        return DB::transaction(function () use ($licenseKey, $amount, $type, $description) {
            $license = CentralLicense::where('license_key', $licenseKey)
                ->lockForUpdate()
                ->first();

            if (!$license) {
                return ['ok' => false, 'error' => 'License not found.'];
            }

            $newIncluded = $license->credits_included + $amount;
            $license->update(['credits_included' => $newIncluded]);

            CentralCreditLedger::create([
                'license_key'  => $licenseKey,
                'type'         => $type,
                'amount'       => $amount,
                'balance_after' => $newIncluded - $license->credits_used,
                'description'  => $description ?: "Credit top-up",
                'recorded_at'  => now(),
            ]);

            return [
                'ok'        => true,
                'remaining' => $newIncluded - $license->credits_used,
                'added'     => $amount,
            ];
        });
    }

    /**
     * Total credits used across all licenses today / this month / all time.
     */
    public function globalStats(): array
    {
        return [
            'total_included'  => CentralLicense::sum('credits_included'),
            'total_used'      => CentralLicense::sum('credits_used'),
            'used_today'      => CentralCreditLedger::where('type', 'usage')
                ->whereDate('recorded_at', today())->sum(DB::raw('ABS(amount)')),
            'used_this_month' => CentralCreditLedger::where('type', 'usage')
                ->whereMonth('recorded_at', now()->month)->sum(DB::raw('ABS(amount)')),
            'topups_this_month' => CentralCreditLedger::where('type', 'topup')
                ->whereMonth('recorded_at', now()->month)->sum('amount'),
        ];
    }
}
