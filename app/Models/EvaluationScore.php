<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    protected $fillable = [
        'project_id', 'pipeline_run_id', 'user_id', 'domain',
        'ui_score', 'backend_score', 'security_score', 'performance_score', 'overall_score',
        'grade', 'strengths', 'issues', 'recommendations',
        'total_files', 'tokens_used',
    ];

    protected $casts = [
        'strengths'       => 'array',
        'issues'          => 'array',
        'recommendations' => 'array',
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

    /** Build from QualityReviewerAgent quality_report array */
    public static function fromQualityReport(
        int     $projectId,
        int     $userId,
        array   $report,
        ?int    $pipelineRunId = null,
        string  $domain        = 'general',
        int     $totalFiles    = 0,
        int     $tokensUsed    = 0,
    ): self {
        $scores = $report['scores'] ?? [];

        return self::create([
            'project_id'       => $projectId,
            'pipeline_run_id'  => $pipelineRunId,
            'user_id'          => $userId,
            'domain'           => $domain,
            'ui_score'         => (int) ($scores['ui']          ?? 0),
            'backend_score'    => (int) ($scores['backend']     ?? 0),
            'security_score'   => (int) ($scores['security']    ?? 0),
            'performance_score'=> (int) ($scores['performance'] ?? 0),
            'overall_score'    => (int) ($scores['overall']     ?? 0),
            'grade'            => (string) ($report['grade']    ?? 'C'),
            'strengths'        => $report['strengths']       ?? [],
            'issues'           => $report['issues']          ?? [],
            'recommendations'  => $report['recommendations'] ?? [],
            'total_files'      => $totalFiles,
            'tokens_used'      => $tokensUsed,
        ]);
    }
}
