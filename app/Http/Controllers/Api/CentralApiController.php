<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Central\CreditManager;
use App\Services\Central\LicenseManager;
use App\Services\Central\TelemetryAggregator;
use App\Models\Central\CentralInstall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CentralApiController extends Controller
{
    public function __construct(
        private LicenseManager $licenseManager,
        private CreditManager $creditManager,
        private TelemetryAggregator $telemetry,
    ) {}

    /**
     * POST /api/central/v1/license/validate
     * Called by customer installs on boot / hourly ping.
     */
    public function validateLicense(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain'      => 'required|string|max:255',
        ]);

        $result = $this->licenseManager->validate(
            $request->input('license_key'),
            $request->input('domain'),
            $request->only(['app_version', 'php_version', 'laravel_version', 'user_count', 'project_count'])
        );

        $status = ($result['valid'] ?? false) ? 200 : 403;
        return response()->json($result, $status);
    }

    /**
     * POST /api/central/v1/credits/check
     */
    public function checkCredits(Request $request): JsonResponse
    {
        $request->validate(['license_key' => 'required|string']);
        $result = $this->creditManager->check($request->input('license_key'));
        return response()->json($result, ($result['ok'] ?? false) ? 200 : 403);
    }

    /**
     * POST /api/central/v1/credits/deduct
     */
    public function deductCredits(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string',
            'amount'      => 'required|integer|min:1|max:1000',
            'domain'      => 'sometimes|string',
            'description' => 'sometimes|string|max:255',
            'reference'   => 'sometimes|string|max:255',
        ]);

        $result = $this->creditManager->deduct(
            $request->input('license_key'),
            $request->input('amount'),
            $request->input('domain', ''),
            $request->input('description', ''),
            $request->input('reference', '')
        );

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 402);
    }

    /**
     * POST /api/central/v1/telemetry
     * Accepts anonymized usage data from customer installs.
     */
    public function ingestTelemetry(Request $request): JsonResponse
    {
        $request->validate([
            'license_key'     => 'sometimes|string',
            'domain'          => 'sometimes|string|max:255',
            'intent_domain'   => 'sometimes|string|max:100',
            'entities'        => 'sometimes|array',
            'functions'       => 'sometimes|array',
            'language'        => 'sometimes|string|max:10',
            'country'         => 'sometimes|string|max:2',
            'generation_type' => 'sometimes|string|max:50',
            'app_version'     => 'sometimes|string|max:20',
            'used_ai'         => 'sometimes|boolean',
        ]);

        $this->telemetry->record($request->all());

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/central/v1/install/ping
     * Heartbeat from customer installs (every 6h).
     */
    public function ping(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain'      => 'required|string|max:255',
        ]);

        CentralInstall::updateOrCreate(
            ['domain' => $request->input('domain')],
            [
                'license_key'     => $request->input('license_key'),
                'last_ping'       => now(),
                'last_ip'         => $request->ip(),
                'app_version'     => $request->input('app_version'),
                'php_version'     => $request->input('php_version'),
                'laravel_version' => $request->input('laravel_version'),
                'user_count'      => $request->input('user_count', 1),
                'project_count'   => $request->input('project_count', 0),
                'prompts_today'   => $request->input('prompts_today', 0),
            ]
        );

        return response()->json(['ok' => true, 'timestamp' => now()->toIso8601String()]);
    }
}
