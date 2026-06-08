<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineRun extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'prompt', 'status',
        'current_agent', 'retry_count', 'context',
        'quality_report', 'error_memory', 'total_tokens',
        'total_files', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'context'        => 'array',
        'quality_report' => 'array',
        'error_memory'   => 'array',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mergeContext(array $partial): void
    {
        $current = $this->context ?? [];
        $this->update(['context' => array_merge($current, $partial)]);
        $this->context = array_merge($current, $partial);
    }

    public function addErrorMemory(int $retryIndex, array $errors): void
    {
        $memory = $this->error_memory ?? [];
        $memory["retry_{$retryIndex}"] = $errors;
        $this->update(['error_memory' => $memory]);
        $this->error_memory = $memory;
    }

    public function markRunning(): void
    {
        $this->update(['status' => 'running', 'started_at' => now()]);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function markFailed(): void
    {
        $this->update(['status' => 'failed', 'completed_at' => now()]);
    }

    public function incrementTokens(int $tokens): void
    {
        $this->increment('total_tokens', $tokens);
    }

    public function incrementFiles(int $count): void
    {
        $this->increment('total_files', $count);
    }
}
