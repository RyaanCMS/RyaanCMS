<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class CentralInstall extends Model
{
    protected $table = 'central_installs';

    protected $fillable = [
        'license_key', 'domain', 'app_version', 'php_version', 'laravel_version',
        'user_count', 'project_count', 'prompts_today', 'prompts_total',
        'last_ip', 'first_seen', 'last_ping',
    ];

    protected $casts = [
        'user_count'    => 'integer',
        'project_count' => 'integer',
        'prompts_today' => 'integer',
        'prompts_total' => 'integer',
        'first_seen'    => 'datetime',
        'last_ping'     => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('last_ping', '>=', now()->subDays(7));
    }

    public function isStale(): bool
    {
        return !$this->last_ping || $this->last_ping->lt(now()->subDays(30));
    }

    public function license()
    {
        return $this->belongsTo(CentralLicense::class, 'license_key', 'license_key');
    }
}
