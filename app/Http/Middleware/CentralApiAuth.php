<?php

namespace App\Http\Middleware;

use App\Models\Central\CentralLicense;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CentralApiAuth
{
    /**
     * Validate X-License-Key header on incoming central API requests.
     * Only checks that the key exists in the DB — full validation is
     * done by the individual endpoint (LicenseManager::validate()).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-License-Key')
            ?? $request->input('license_key');

        if (!$key) {
            return response()->json([
                'valid' => false,
                'error' => 'Missing license key.',
            ], 401);
        }

        $exists = CentralLicense::where('license_key', $key)->exists();

        if (!$exists) {
            return response()->json([
                'valid' => false,
                'error' => 'License key not found.',
            ], 403);
        }

        return $next($request);
    }
}
