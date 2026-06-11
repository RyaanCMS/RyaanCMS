<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrySyncLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'registry_url', 'action', 'packs_checked', 'packs_updated',
        'packs_skipped', 'success', 'error_message', 'details', 'duration_ms', 'synced_at',
    ];

    protected $casts = [
        'success'    => 'boolean',
        'details'    => 'array',
        'synced_at'  => 'datetime',
    ];

    public static function record(
        string $registryUrl,
        string $action,
        bool $success,
        array $counts = [],
        ?string $error = null,
        array $details = [],
        ?int $durationMs = null
    ): self {
        return self::create([
            'registry_url'   => $registryUrl,
            'action'         => $action,
            'packs_checked'  => $counts['checked']  ?? 0,
            'packs_updated'  => $counts['updated']  ?? 0,
            'packs_skipped'  => $counts['skipped']  ?? 0,
            'success'        => $success,
            'error_message'  => $error,
            'details'        => $details ?: null,
            'duration_ms'    => $durationMs,
            'synced_at'      => now(),
        ]);
    }

    public static function lastSync(): ?self
    {
        return self::where('action', 'sync')->where('success', true)->latest('synced_at')->first();
    }
}
