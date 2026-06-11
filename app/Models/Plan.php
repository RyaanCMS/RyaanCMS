<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key', 'name', 'price_monthly', 'currency',
        'credits_per_month', 'max_projects', 'features',
        'trial_days', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'features'         => 'array',
        'price_monthly'    => 'integer',
        'credits_per_month'=> 'integer',
        'max_projects'     => 'integer',
        'trial_days'       => 'integer',
        'is_active'        => 'boolean',
    ];

    public function isFree(): bool
    {
        return $this->price_monthly === 0;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function formattedPrice(): string
    {
        if ($this->price_monthly === 0) return 'Free';
        return '$' . number_format($this->price_monthly / 100, 0) . '/mo';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public static function find_by_key(string $key): ?static
    {
        return static::where('key', $key)->first();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_key', 'key');
    }
}
