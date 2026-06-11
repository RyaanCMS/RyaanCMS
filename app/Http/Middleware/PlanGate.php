<?php

namespace App\Http\Middleware;

use App\Services\Billing\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanGate
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    /**
     * Usage in routes: ->middleware('plan:pipeline_agents')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->subscriptionService->hasFeature($user, $feature)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error'   => 'This feature requires a higher plan.',
                'feature' => $feature,
                'upgrade' => route('billing.upgrade'),
            ], 403);
        }

        return redirect()->route('billing.upgrade')
            ->with('warning', 'This feature is not available on your current plan. Upgrade to unlock it.');
    }
}
