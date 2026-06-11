<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CentralLicense extends Model
{
    protected $table = 'central_licenses';

    protected $fillable = [
        'license_key', 'plan', 'email', 'name', 'domain',
        'status', 'credits_included', 'credits_used',
        'expires_at', 'issued_at', 'notes', 'meta',
    ];

    protected $casts = [
        'credits_included' => 'integer',
        'credits_used'     => 'integer',
        'expires_at'       => 'datetime',
        'issued_at'        => 'datetime',
        'meta'             => 'array',
    ];

    // ── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }

    // ── Helpers ──────────────────────────────────────────

    public function creditsRemaining(): int
    {
        return max(0, $this->credits_included - $this->credits_used);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function toApiResponse(): array
    {
        return [
            'valid'             => $this->isActive(),
            'plan'              => $this->plan,
            'status'            => $this->status,
            'credits_remaining' => $this->creditsRemaining(),
            'credits_included'  => $this->credits_included,
            'expires_at'        => $this->expires_at?->toISOString(),
            'features'          => $this->planFeatures(),
        ];
    }

    public function planFeatures(): array
    {
        return match($this->plan) {
            'enterprise' => ['senior_dev_wisdom', 'pipeline_agents', 'premium_kb', 'priority_routing', 'advanced_arch'],
            'pro'        => ['senior_dev_wisdom', 'pipeline_agents', 'premium_kb'],
            'starter'    => ['premium_kb'],
            default      => [],
        };
    }

    // ── Factory ──────────────────────────────────────────

    public static function generate(string $plan = 'starter', array $attrs = []): static
    {
        $key = 'RYAAN-' . implode('-', str_split(strtoupper(Str::random(16)), 4));

        $creditsMap = ['free' => 100, 'starter' => 500, 'pro' => 2000, 'enterprise' => 10000];

        return static::create(array_merge([
            'license_key'      => $key,
            'plan'             => $plan,
            'status'           => 'active',
            'credits_included' => $creditsMap[$plan] ?? 100,
            'credits_used'     => 0,
            'issued_at'        => now(),
        ], $attrs));
    }

    // ── Relations ────────────────────────────────────────

    public function installs()
    {
        return $this->hasMany(CentralInstall::class, 'license_key', 'license_key');
    }

    public function ledger()
    {
        return $this->hasMany(CentralCreditLedger::class, 'license_key', 'license_key');
    }

    public function telemetry()
    {
        return $this->hasMany(CentralTelemetry::class, 'license_key', 'license_key');
    }
}
