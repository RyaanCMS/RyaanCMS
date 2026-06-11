<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionService
{
    /**
     * Start a 14-day free trial for a newly registered user.
     */
    public function startTrial(User $user): Subscription
    {
        // Don't create duplicate trials
        $existing = Subscription::where('user_id', $user->id)->latest()->first();
        if ($existing) return $existing;

        $plan = Plan::where('key', 'free_trial')->first();
        $days = $plan ? $plan->trial_days : 14;

        return Subscription::create([
            'user_id'          => $user->id,
            'plan_key'         => 'free_trial',
            'status'           => 'trial',
            'trial_starts_at'  => now(),
            'trial_ends_at'    => now()->addDays($days),
        ]);
    }

    /**
     * Get the user's current subscription (latest active or trial).
     */
    public function currentSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial', 'pending_payment'])
            ->latest()
            ->first();
    }

    /**
     * Check if a user has an active subscription or trial.
     */
    public function isActive(User $user): bool
    {
        if ($user->role === 'admin') return true;

        $sub = $this->currentSubscription($user);
        return $sub && !$sub->isExpired();
    }

    /**
     * Check if the user is currently on trial.
     */
    public function isOnTrial(User $user): bool
    {
        $sub = $this->currentSubscription($user);
        return $sub && $sub->isOnTrial();
    }

    /**
     * Days left in current trial.
     */
    public function trialDaysLeft(User $user): int
    {
        $sub = $this->currentSubscription($user);
        return $sub ? $sub->trialDaysLeft() : 0;
    }

    /**
     * Get the user's current plan key.
     */
    public function getUserPlanKey(User $user): string
    {
        if ($user->role === 'admin') return 'enterprise';
        $sub = $this->currentSubscription($user);
        return $sub ? $sub->plan_key : 'free_trial';
    }

    /**
     * Get the user's Plan model.
     */
    public function getUserPlan(User $user): ?Plan
    {
        return Plan::where('key', $this->getUserPlanKey($user))->first();
    }

    /**
     * Check if the user's current plan includes a specific feature.
     */
    public function hasFeature(User $user, string $feature): bool
    {
        if ($user->role === 'admin') return true;

        $plan = $this->getUserPlan($user);
        if (!$plan) return false;

        // During trial, give full access to all features
        if ($this->isOnTrial($user)) return true;

        return $plan->hasFeature($feature);
    }

    /**
     * Initiate payment — creates a pending subscription and returns the gateway redirect.
     */
    public function initiateUpgrade(User $user, string $planKey, EpsPaymentGateway $gateway): array
    {
        $plan = Plan::where('key', $planKey)->where('is_active', true)->first();
        if (!$plan) {
            return ['ok' => false, 'error' => 'Plan not found.'];
        }

        // Create a pending subscription record
        Subscription::where('user_id', $user->id)
            ->where('status', 'pending_payment')
            ->delete();

        $payment = $gateway->initiatePayment($user, $plan);

        Subscription::create([
            'user_id'           => $user->id,
            'plan_key'          => $planKey,
            'status'            => 'pending_payment',
            'payment_reference' => $payment['reference'],
        ]);

        return ['ok' => true, 'redirect_url' => $payment['url'], 'reference' => $payment['reference']];
    }

    /**
     * Activate a paid subscription after payment confirmed.
     */
    public function activateSubscription(int $userId, string $planKey, string $reference, int $amountPaid = 0): Subscription
    {
        // Cancel any existing active/trial subs
        Subscription::where('user_id', $userId)
            ->whereIn('status', ['active', 'trial', 'pending_payment'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return Subscription::create([
            'user_id'               => $userId,
            'plan_key'              => $planKey,
            'status'                => 'active',
            'current_period_start'  => now(),
            'current_period_end'    => now()->addMonth(),
            'payment_reference'     => $reference,
            'payment_method'        => 'eps',
            'amount_paid'           => $amountPaid,
        ]);
    }

    /**
     * Cancel a user's active subscription.
     */
    public function cancel(User $user): void
    {
        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    /**
     * Stats for admin revenue dashboard.
     */
    public function revenueStats(): array
    {
        $activeTrials = Subscription::where('status', 'trial')
            ->where('trial_ends_at', '>=', now())->count();

        $activePaid = Subscription::where('status', 'active')
            ->where('current_period_end', '>=', now())->count();

        $mrr = Subscription::where('status', 'active')
            ->where('current_period_end', '>=', now())
            ->join('plans', 'subscriptions.plan_key', '=', 'plans.key')
            ->sum('plans.price_monthly');

        $newThisMonth = Subscription::where('status', 'active')
            ->whereMonth('created_at', now()->month)->count();

        $trialToday = Subscription::where('status', 'trial')
            ->whereDate('created_at', today())->count();

        $byPlan = Subscription::where('status', 'active')
            ->where('current_period_end', '>=', now())
            ->selectRaw('plan_key, COUNT(*) as count')
            ->groupBy('plan_key')
            ->pluck('count', 'plan_key')
            ->toArray();

        return [
            'active_trials'   => $activeTrials,
            'active_paid'     => $activePaid,
            'mrr_cents'       => (int) $mrr,
            'mrr'             => '$' . number_format($mrr / 100, 0),
            'arr'             => '$' . number_format($mrr * 12 / 100, 0),
            'new_this_month'  => $newThisMonth,
            'trials_today'    => $trialToday,
            'by_plan'         => $byPlan,
        ];
    }
}
