<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;

class CheckLicense
{
    public function __construct(private LicenseService $license) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $this->license->isActivated()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'License not activated. Please activate your purchase code.',
                    'redirect' => route('license.index'),
                ], 403);
            }

            return redirect()->route('license.index')
                ->with('warning', 'Please activate your license to continue.');
        }

        return $next($request);
    }
}
