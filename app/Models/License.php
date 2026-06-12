<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'purchase_code',
        'license_token',
        'domain',
        'product_id',
        'product_name',
        'buyer_email',
        'status',
        'meta',
        'activated_at',
        'expires_at',
        'last_verified_at',
    ];

    protected $casts = [
        'meta'             => 'array',
        'activated_at'     => 'datetime',
        'expires_at'       => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isWithinGrace(): bool
    {
        if (! $this->last_verified_at) {
            return false;
        }

        $graceHours = config('license.grace_hours', 72);

        return $this->last_verified_at->diffInHours(now()) <= $graceHours;
    }

    public static function getActive(): ?static
    {
        return static::where('status', 'active')->latest('activated_at')->first();
    }
}
