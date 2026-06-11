<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditBalance extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'lifetime_earned', 'lifetime_used',
        'tier', 'tier_expires_at',
    ];

    protected $casts = [
        'balance'          => 'integer',
        'lifetime_earned'  => 'integer',
        'lifetime_used'    => 'integer',
        'tier_expires_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class, 'user_id', 'user_id');
    }

    public function isCreditsUser(): bool
    {
        return in_array($this->tier, ['starter', 'pro', 'enterprise']);
    }

    public function isByok(): bool
    {
        return $this->tier === 'byok';
    }

    public function isTierActive(): bool
    {
        if (!$this->isCreditsUser()) return false;
        return $this->tier_expires_at === null || $this->tier_expires_at->isFuture();
    }
}
