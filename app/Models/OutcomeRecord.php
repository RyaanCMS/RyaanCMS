<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutcomeRecord extends Model
{
    protected $fillable = [
        'project_id', 'pipeline_run_id', 'user_id', 'domain',
        'files_generated', 'tokens_used', 'ai_cost_estimate', 'ai_cost_saved',
        'build_time_seconds', 'quality_score', 'quality_grade',
        'blueprint_source', 'modules_used', 'components_reused',
        'rules_applied', 'confidence_scores', 'ai_was_used',
        'revenue_impact', 'cost_reduction', 'time_saved_hours', 'notes',
    ];

    protected $casts = [
        'modules_used'      => 'array',
        'components_reused' => 'array',
        'rules_applied'     => 'array',
        'confidence_scores' => 'array',
        'ai_was_used'       => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function pipelineRun(): BelongsTo
    {
        return $this->belongsTo(PipelineRun::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Summarise AI cost savings as a human-readable string */
    public function savingsSummary(): string
    {
        $saved = number_format((float) $this->ai_cost_saved, 4);
        $pct   = $this->tokens_used > 0
            ? round(($this->ai_cost_saved / max($this->ai_cost_estimate + $this->ai_cost_saved, 0.000001)) * 100)
            : 0;

        return "\${$saved} saved ({$pct}% of estimated AI cost)";
    }
}
