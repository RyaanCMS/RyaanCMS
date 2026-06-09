<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateHistory extends Model
{
    protected $table = 'update_history';

    protected $fillable = [
        'version', 'type', 'package_key', 'status',
        'changelog', 'error_message', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) return null;
        $secs = $this->started_at->diffInSeconds($this->completed_at);
        return $secs < 60 ? "{$secs}s" : round($secs / 60, 1) . 'm';
    }
}
