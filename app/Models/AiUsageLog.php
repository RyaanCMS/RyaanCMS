<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id', 'project_id', 'provider', 'model', 'task_type',
        'input_tokens', 'output_tokens', 'total_tokens', 'credits_used',
        'response_time_ms', 'from_cache', 'tier_at_time', 'meta',
    ];

    protected $casts = [
        'from_cache'       => 'boolean',
        'meta'             => 'array',
        'input_tokens'     => 'integer',
        'output_tokens'    => 'integer',
        'total_tokens'     => 'integer',
        'credits_used'     => 'integer',
        'response_time_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
