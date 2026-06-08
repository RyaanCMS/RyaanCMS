<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'target', 'status',
        'log', 'config', 'url', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'config'       => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) return null;
        $seconds = $this->started_at->diffInSeconds($this->completed_at);
        return $seconds < 60 ? "{$seconds}s" : round($seconds / 60, 1).'m';
    }
}
