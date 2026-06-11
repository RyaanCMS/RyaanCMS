<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_key', 'status',
        'trial_starts_at', 'trial_ends_at',
        'current_period_start', 'current_period_end',
        'payment_reference', 'payment_method', 'amount_paid',
        'cancelled_at',
    ];

    protected $casts = [
        'trial_starts_at'      => 'datetime',
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'cancelled_at'         => 'datetime',
        'amount_paid'          => 'integer',
    ];

    // ── Relations ────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_key', 'key');
    }

    // ── Status helpers ───────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        if ($this->status === 'trial') {
            return $this->trial_ends_at && $this->trial_ends_at->isPast();
        }
        if ($this->status === 'active') {
            return $this->current_period_end && $this->current_period_end->isPast();
        }
        return in_array($this->status, ['expired', 'cancelled']);
    }

    public function trialDaysLeft(): int
    {
        if (!$this->isOnTrial()) return 0;
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function daysUntilRenewal(): int
    {
        if (!$this->current_period_end) return 0;
        return max(0, (int) now()->diffInDays($this->current_period_end, false));
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trial']);
    }

    public function scopeExpired($query)
    {
        return $query->whereIn('status', ['expired', 'cancelled']);
    }

    public function scopeTrials($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'active');
    }
}
