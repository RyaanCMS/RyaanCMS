<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LocalQuestionPack extends Model
{
    protected $fillable = [
        'central_pack_uuid', 'name', 'slug', 'industry', 'blueprint_slug',
        'country', 'language', 'version', 'status', 'visibility', 'is_premium',
        'questions_json', 'rules_json', 'metadata', 'synced_at', 'license_status',
    ];

    protected $casts = [
        'is_premium'     => 'boolean',
        'questions_json' => 'array',
        'rules_json'     => 'array',
        'metadata'       => 'array',
        'synced_at'      => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeForIndustry(Builder $q, string $industry): Builder
    {
        return $q->where('industry', $industry);
    }

    public function scopeForBlueprint(Builder $q, string $slug): Builder
    {
        return $q->where('blueprint_slug', $slug);
    }

    public function scopeForCountry(Builder $q, string $country): Builder
    {
        return $q->where(fn($s) => $s->where('country', $country)->orWhere('country', 'ALL'));
    }

    public function scopeForLanguage(Builder $q, string $lang): Builder
    {
        return $q->where(fn($s) => $s->where('language', $lang)->orWhere('language', 'en'));
    }

    public function scopeFree(Builder $q): Builder
    {
        return $q->where('is_premium', false);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function questions(): array
    {
        return $this->questions_json ?? [];
    }

    public function isOutdated(string $latestVersion): bool
    {
        return version_compare($this->version, $latestVersion, '<');
    }

    public function isSyncedFromCentral(): bool
    {
        return $this->central_pack_uuid !== null;
    }

    public static function safeCount(): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('local_question_packs')) {
            return 0;
        }
        return self::active()->count();
    }

    // ── Static lookup ─────────────────────────────────────────────────────

    /**
     * Best-match pack for a given industry/blueprint/country/language.
     * Priority: blueprint+country > blueprint > industry+country > industry
     */
    public static function bestMatch(
        string $industry,
        ?string $blueprintSlug = null,
        string $country = 'ALL',
        string $language = 'en'
    ): ?self {
        $q = self::active();

        if ($blueprintSlug) {
            $exact = (clone $q)
                ->forBlueprint($blueprintSlug)
                ->forCountry($country)
                ->forLanguage($language)
                ->orderByRaw("CASE WHEN country = ? THEN 0 ELSE 1 END", [$country])
                ->orderByRaw("CASE WHEN language = ? THEN 0 ELSE 1 END", [$language])
                ->first();

            if ($exact) return $exact;
        }

        return (clone $q)
            ->forIndustry($industry)
            ->forCountry($country)
            ->forLanguage($language)
            ->orderByRaw("CASE WHEN country = ? THEN 0 ELSE 1 END", [$country])
            ->orderByRaw("CASE WHEN language = ? THEN 0 ELSE 1 END", [$language])
            ->first();
    }
}
