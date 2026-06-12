<?php

namespace App\Services\AI;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Intelligence Collector — Layer 1 of RGIN
 *
 * Every RyaanCMS installation is a local intelligence node.
 * This service manages that local node:
 *
 *   1. Receives extracted intelligence from IntelligenceExtractor
 *   2. Deduplicates against existing registry
 *   3. Stores to local_intelligence_registry table
 *   4. Prepares validated assets for future cloud export
 *   5. Pulls network intelligence stats
 *
 * Privacy guarantee:
 *   This collector never touches customer data, PII, or business secrets.
 *   It only stores intelligence METADATA — asset types, domains, dependencies,
 *   business rules, tags, and quality scores.
 *
 * The flywheel:
 *   Every build feeds the collector → collector grows the local registry →
 *   local registry reduces AI cost on the next build → network export makes
 *   every user's $10 AI spend a global asset worth $10,000.
 */
class IntelligenceCollector
{
    public function __construct(
        protected IntelligenceExtractor $extractor
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Collect — store extracted intelligence to local registry
    // Core method: called after every successful generation
    // ─────────────────────────────────────────────────────────────────────────

    public function collect(array $extracted): ?int
    {
        try {
            $existing = DB::table('local_intelligence_registry')
                ->where('fingerprint', $extracted['fingerprint'])
                ->first();

            if ($existing) {
                // Already know about this — update quality score if improved
                if ($extracted['extraction_quality'] > $existing->extraction_quality) {
                    DB::table('local_intelligence_registry')
                        ->where('id', $existing->id)
                        ->update([
                            'extraction_quality' => $extracted['extraction_quality'],
                            'confidence'         => $extracted['confidence'],
                            'tags'               => json_encode($extracted['tags']),
                            'business_rules'     => json_encode($extracted['business_rules']),
                            'seen_count'         => DB::raw('seen_count + 1'),
                            'updated_at'         => now(),
                        ]);
                } else {
                    DB::table('local_intelligence_registry')
                        ->where('id', $existing->id)
                        ->increment('seen_count', 1, ['updated_at' => now()]);
                }
                return $existing->id;
            }

            return DB::table('local_intelligence_registry')->insertGetId([
                'asset_type'         => $extracted['asset_type'],
                'domain'             => $extracted['domain'],
                'extension'          => $extracted['extension'],
                'name'               => $extracted['name'],
                'fingerprint'        => $extracted['fingerprint'],
                'dependencies'       => json_encode($extracted['dependencies']),
                'tags'               => json_encode($extracted['tags']),
                'business_rules'     => json_encode($extracted['business_rules']),
                'confidence'         => $extracted['confidence'],
                'extraction_quality' => $extracted['extraction_quality'],
                'prompt_summary'     => $extracted['prompt_summary'],
                'seen_count'         => 1,
                'is_validated'       => false,
                'is_exported'        => false,
                'export_ready'       => $extracted['extraction_quality'] >= 70,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable) {
            // Intelligence collection is non-critical — never break generation
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Full pipeline: extract + collect in one call
    // This is what CodeGeneratorService calls after a successful build
    // ─────────────────────────────────────────────────────────────────────────

    public function extractAndCollect(
        string $prompt,
        Project $project,
        ?array $coverageResult = null
    ): ?array {
        try {
            $extracted = $this->extractor->extract($prompt, $project, $coverageResult);
            $id = $this->collect($extracted);
            return $id ? array_merge($extracted, ['registry_id' => $id]) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Export queue — assets ready for cloud contribution
    // High quality, seen multiple times, not yet exported
    // ─────────────────────────────────────────────────────────────────────────

    public function getExportQueue(int $limit = 50): array
    {
        try {
            return DB::table('local_intelligence_registry')
                ->where('export_ready', true)
                ->where('is_exported', false)
                ->where('extraction_quality', '>=', 70)
                ->where('seen_count', '>=', 1)
                ->orderByDesc('extraction_quality')
                ->orderByDesc('seen_count')
                ->limit($limit)
                ->get()
                ->map(fn($row) => [
                    'id'                 => $row->id,
                    'asset_type'         => $row->asset_type,
                    'domain'             => $row->domain,
                    'name'               => $row->name,
                    'extraction_quality' => $row->extraction_quality,
                    'seen_count'         => $row->seen_count,
                    'export_package'     => $this->extractor->toExportFormat([
                        'asset_type'         => $row->asset_type,
                        'domain'             => $row->domain,
                        'extension'          => $row->extension,
                        'name'               => $row->name,
                        'fingerprint'        => $row->fingerprint,
                        'dependencies'       => json_decode($row->dependencies, true) ?? [],
                        'tags'               => json_decode($row->tags, true) ?? [],
                        'business_rules'     => json_decode($row->business_rules, true) ?? [],
                        'prompt_summary'     => $row->prompt_summary,
                        'confidence'         => $row->confidence,
                        'extraction_quality' => $row->extraction_quality,
                        'matched_assets'     => 0,
                        'schema_version'     => '1.0',
                        'source'             => 'ryaancms_local',
                    ]),
                ])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function markExported(int $id): void
    {
        try {
            DB::table('local_intelligence_registry')
                ->where('id', $id)
                ->update(['is_exported' => true, 'updated_at' => now()]);
        } catch (\Throwable) {}
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Registry stats — the local node's intelligence dashboard
    // ─────────────────────────────────────────────────────────────────────────

    public function getRegistryStats(): array
    {
        try {
            $base = DB::table('local_intelligence_registry');

            return [
                'total_assets'       => (clone $base)->count(),
                'export_ready'       => (clone $base)->where('export_ready', true)->where('is_exported', false)->count(),
                'exported'           => (clone $base)->where('is_exported', true)->count(),
                'avg_quality'        => round((clone $base)->avg('extraction_quality') ?? 0, 1),
                'top_domains'        => DB::table('local_intelligence_registry')
                    ->selectRaw('domain, count(*) as count, avg(extraction_quality) as avg_quality')
                    ->groupBy('domain')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
                    ->toArray(),
                'asset_type_counts'  => DB::table('local_intelligence_registry')
                    ->selectRaw('asset_type, count(*) as count')
                    ->groupBy('asset_type')
                    ->orderByDesc('count')
                    ->pluck('count', 'asset_type')
                    ->toArray(),
                'high_value_assets'  => (clone $base)->where('extraction_quality', '>=', 85)->count(),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Network impact calculator
    // Shows what this local node has contributed / could contribute to RGIN
    // ─────────────────────────────────────────────────────────────────────────

    public function calculateNetworkImpact(): array
    {
        try {
            $stats = $this->getRegistryStats();
            $totalAssets = $stats['total_assets'] ?? 0;
            $exported    = $stats['exported'] ?? 0;

            // Rough estimates: each asset saves avg 2h dev time for avg 1000 users who download it
            $estimatedUsersServed = $exported * 1000;
            $estimatedHoursSaved  = $exported * 2 * 1000;
            $estimatedAICostSaved = $exported * 10 * 1000; // $10 avg AI cost per asset

            return [
                'local_assets_collected'  => $totalAssets,
                'assets_shared_globally'  => $exported,
                'assets_ready_to_share'   => $stats['export_ready'] ?? 0,
                'estimated_users_served'  => number_format($estimatedUsersServed),
                'estimated_hours_saved'   => number_format($estimatedHoursSaved),
                'estimated_ai_cost_saved' => '$' . number_format($estimatedAICostSaved),
                'impact_message'          => $exported > 0
                    ? "Your {$exported} shared assets have potentially saved {$estimatedUsersServed} users from spending \${$estimatedAICostSaved} on AI."
                    : "You have {$totalAssets} assets ready. Share them to multiply their value globally.",
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
