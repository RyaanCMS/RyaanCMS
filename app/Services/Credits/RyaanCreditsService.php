<?php

namespace App\Services\Credits;

use App\Models\CreditBalance;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RyaanCreditsService
{
    public function getBalance(User $user): CreditBalance
    {
        try {
            return CreditBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'lifetime_earned' => 0, 'lifetime_used' => 0, 'tier' => 'byok']
            );
        } catch (\Throwable) {
            // Table doesn't exist yet (migration not run) — return safe BYOK default
            $default = new CreditBalance();
            $default->user_id = $user->id;
            $default->balance = 0;
            $default->lifetime_earned = 0;
            $default->lifetime_used = 0;
            $default->tier = 'byok';
            $default->tier_expires_at = null;
            $default->exists = false;
            return $default;
        }
    }

    public function getBalanceAmount(User $user): int
    {
        return $this->getBalance($user)->balance;
    }

    public function getTier(User $user): string
    {
        return $this->getBalance($user)->tier;
    }

    public function isCreditsUser(User $user): bool
    {
        $balance = $this->getBalance($user);
        return $balance->isCreditsUser() && $balance->isTierActive();
    }

    /**
     * Add credits (purchase, bonus, refund).
     */
    public function add(User $user, int $amount, string $type, string $description, array $meta = []): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $meta) {
            $balance = $this->getBalance($user);
            $balance->increment('balance', $amount);
            $balance->increment('lifetime_earned', $amount);
            $balance->refresh();

            return CreditTransaction::create([
                'user_id'      => $user->id,
                'type'         => $type,
                'amount'       => $amount,
                'balance_after' => $balance->balance,
                'description'  => $description,
                'meta'         => $meta,
            ]);
        });
    }

    /**
     * Deduct credits for AI usage. Returns false if insufficient balance.
     */
    public function deduct(
        User $user,
        int $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $meta = []
    ): bool {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId, $meta) {
            $balance = CreditBalance::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$balance || $balance->balance < $amount) {
                return false;
            }

            $balance->decrement('balance', $amount);
            $balance->increment('lifetime_used', $amount);
            $balance->refresh();

            CreditTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'usage',
                'amount'         => -$amount,
                'balance_after'  => $balance->balance,
                'description'    => $description,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'meta'           => $meta,
            ]);

            return true;
        });
    }

    /**
     * Check if user has enough credits for an operation.
     */
    public function canAfford(User $user, int $amount): bool
    {
        return $this->getBalanceAmount($user) >= $amount;
    }

    /**
     * Upgrade user tier.
     */
    public function setTier(User $user, string $tier, ?\DateTimeInterface $expiresAt = null): void
    {
        $balance = $this->getBalance($user);
        $balance->update([
            'tier'            => $tier,
            'tier_expires_at' => $expiresAt,
        ]);
    }

    /**
     * Recent transaction history.
     */
    public function history(User $user, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return CreditTransaction::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Usage summary for current calendar month.
     */
    public function monthlyUsage(User $user): array
    {
        $used = CreditTransaction::where('user_id', $user->id)
            ->where('type', 'usage')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum(DB::raw('ABS(amount)'));

        return [
            'credits_used'  => (int) $used,
            'month'         => now()->format('F Y'),
        ];
    }
}