<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intelligence Ledger — stores accumulated wisdom from every project.
 * This is the organizational memory that makes RyaanCMS smarter over time.
 *
 * @property string  $id
 * @property int|null $project_id
 * @property int|null $user_id
 * @property string  $domain
 * @property string  $entry_type       (lesson|decision|outcome|autopsy)
 * @property string|null $lesson
 * @property string|null $what_went_right
 * @property string|null $what_went_wrong
 * @property string|null $recommendation
 * @property string|null $outcome
 * @property float   $confidence
 * @property array   $tags
 * @property bool    $is_rule_candidate
 * @property string|null $rule_trigger
 * @property float|null  $ai_cost_estimate
 * @property float|null  $ai_cost_saved
 * @property int     $reuse_count
 */
class ProjectWisdom extends Model
{
    protected $table      = 'project_wisdom';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'id',
        'project_id',
        'user_id',
        'domain',
        'entry_type',
        'lesson',
        'what_went_right',
        'what_went_wrong',
        'recommendation',
        'outcome',
        'confidence',
        'tags',
        'is_rule_candidate',
        'rule_trigger',
        'ai_cost_estimate',
        'ai_cost_saved',
        'reuse_count',
    ];

    protected $casts = [
        'tags'               => 'array',
        'is_rule_candidate'  => 'boolean',
        'confidence'         => 'float',
        'ai_cost_estimate'   => 'float',
        'ai_cost_saved'      => 'float',
        'reuse_count'        => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForDomain($query, string $domain): object
    {
        return $query->where('domain', $domain);
    }

    public function scopeRuleCandidates($query): object
    {
        return $query->where('is_rule_candidate', true)->where('confidence', '>=', 0.8);
    }

    public function scopeHighConfidence($query): object
    {
        return $query->where('confidence', '>=', 0.75);
    }

    public function scopeWithLesson($query): object
    {
        return $query->whereNotNull('lesson');
    }

    // ── Methods ───────────────────────────────────────────────────────────────

    public function incrementReuse(): void
    {
        $this->increment('reuse_count');
    }

    public function markAsRuleCandidate(string $trigger = ''): void
    {
        $this->update([
            'is_rule_candidate' => true,
            'rule_trigger'      => $trigger ?: $this->rule_trigger,
        ]);
    }
}
