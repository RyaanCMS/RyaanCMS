<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class CentralTelemetry extends Model
{
    protected $table = 'central_telemetry';

    public $timestamps = false;

    protected $fillable = [
        'domain_hash', 'license_key', 'intent_domain', 'entities', 'functions',
        'language', 'country', 'generation_type', 'app_version', 'used_ai', 'recorded_at',
    ];

    protected $casts = [
        'entities'    => 'array',
        'functions'   => 'array',
        'used_ai'     => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->where('recorded_at', '>=', now()->subWeek());
    }

    public function scopeForDomain($query, string $domain)
    {
        return $query->where('intent_domain', $domain);
    }

    public static function topDomains(int $limit = 10, int $days = 30): array
    {
        return static::where('recorded_at', '>=', now()->subDays($days))
            ->whereNotNull('intent_domain')
            ->selectRaw('intent_domain, COUNT(*) as count')
            ->groupBy('intent_domain')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'intent_domain')
            ->toArray();
    }

    public static function topEntities(int $limit = 20, int $days = 30): array
    {
        // Extract all entities from JSON columns and count them
        $rows = static::where('recorded_at', '>=', now()->subDays($days))
            ->whereNotNull('entities')
            ->pluck('entities');

        $counts = [];
        foreach ($rows as $list) {
            foreach ((array) $list as $e) {
                $counts[$e] = ($counts[$e] ?? 0) + 1;
            }
        }

        arsort($counts);
        return array_slice($counts, 0, $limit, true);
    }

    public static function languageDistribution(int $days = 30): array
    {
        return static::where('recorded_at', '>=', now()->subDays($days))
            ->whereNotNull('language')
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->orderByDesc('count')
            ->pluck('count', 'language')
            ->toArray();
    }

    public static function countryDistribution(int $days = 30): array
    {
        return static::where('recorded_at', '>=', now()->subDays($days))
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->pluck('count', 'country')
            ->toArray();
    }
}
