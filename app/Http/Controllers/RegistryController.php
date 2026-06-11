<?php

namespace App\Http\Controllers;

use App\Models\LocalQuestionPack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Runs ONLY on the central registry server (REGISTRY_MODE=true in .env).
 * Customer installations never expose these routes.
 */
class RegistryController extends Controller
{
    // ── Question Packs ────────────────────────────────────────────────────

    /**
     * GET /api/registry/question-packs
     * Returns manifest: slug, name, version, industry, country, language, is_premium
     */
    public function index(Request $request): JsonResponse
    {
        $cacheKey = 'registry:manifest:' . md5(serialize($request->query()));

        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = LocalQuestionPack::active()->select([
                'slug', 'name', 'version', 'industry', 'blueprint_slug',
                'country', 'language', 'is_premium', 'visibility', 'synced_at',
            ]);

            if ($request->filled('industry'))       $query->forIndustry($request->industry);
            if ($request->filled('country'))        $query->forCountry($request->country);
            if ($request->filled('language'))       $query->forLanguage($request->language);
            if ($request->filled('blueprint_slug')) $query->forBlueprint($request->blueprint_slug);

            // Unlicensed clients only see free packs
            if (!$this->isLicensed($request)) {
                $query->free();
            }

            return $query->get()->toArray();
        });

        return response()->json(['data' => $data, 'count' => count($data)]);
    }

    /**
     * GET /api/registry/question-packs/{slug}
     * Returns full pack including questions_json and rules_json
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $pack = LocalQuestionPack::active()->where('slug', $slug)->first();

        if (!$pack) {
            return response()->json(['error' => 'Pack not found'], 404);
        }

        if ($pack->is_premium && !$this->isLicensed($request)) {
            return response()->json(['error' => 'Premium pack requires a valid license'], 403);
        }

        return response()->json([
            'uuid'           => $pack->central_pack_uuid,
            'slug'           => $pack->slug,
            'name'           => $pack->name,
            'industry'       => $pack->industry,
            'blueprint_slug' => $pack->blueprint_slug,
            'country'        => $pack->country,
            'language'       => $pack->language,
            'version'        => $pack->version,
            'is_premium'     => $pack->is_premium,
            'visibility'     => $pack->visibility,
            'questions'      => $pack->questions_json,
            'rules'          => $pack->rules_json,
            'metadata'       => $pack->metadata,
        ]);
    }

    /**
     * GET /api/registry/updates?slugs[]=crm-bd-1.0&slugs[]=erp-global-1.2
     * Returns only packs that have newer versions than submitted
     */
    public function updates(Request $request): JsonResponse
    {
        $submitted = $request->input('slugs', []);

        if (empty($submitted)) {
            return response()->json(['data' => [], 'count' => 0]);
        }

        // Build slug → version map from "slug-version" strings
        $clientVersions = [];
        foreach ($submitted as $entry) {
            if (str_contains($entry, '-')) {
                $parts = explode('-', $entry);
                $version = array_pop($parts);
                $slug    = implode('-', $parts);
                $clientVersions[$slug] = $version;
            }
        }

        $slugs  = array_keys($clientVersions);
        $packs  = LocalQuestionPack::active()->whereIn('slug', $slugs)->get();
        $newer  = [];

        foreach ($packs as $pack) {
            $clientVer = $clientVersions[$pack->slug] ?? '0.0.0';
            if ($pack->isOutdated($clientVer) === false && version_compare($pack->version, $clientVer, '>')) {
                $newer[] = [
                    'slug'            => $pack->slug,
                    'name'            => $pack->name,
                    'latest_version'  => $pack->version,
                    'client_version'  => $clientVer,
                    'is_premium'      => $pack->is_premium,
                ];
            }
        }

        return response()->json(['data' => $newer, 'count' => count($newer)]);
    }

    // ── License ───────────────────────────────────────────────────────────

    /**
     * POST /api/registry/license/verify
     * Body: { license_key, domain }
     */
    public function verifyLicense(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain'      => 'nullable|string',
        ]);

        // On the central server this would query a licenses table.
        // Stub: valid if key starts with 'RYAAN-'
        $key   = $request->input('license_key');
        $valid = str_starts_with(strtoupper($key), 'RYAAN-') && strlen($key) >= 20;

        return response()->json([
            'valid' => $valid,
            'plan'  => $valid ? 'professional' : null,
        ]);
    }

    // ── Private ───────────────────────────────────────────────────────────

    private function isLicensed(Request $request): bool
    {
        $key = $request->header('X-License-Key') ?: $request->input('license_key');
        return !empty($key) && strlen($key) >= 20 && str_starts_with(strtoupper($key), 'RYAAN-');
    }
}
