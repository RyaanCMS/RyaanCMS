<?php

namespace App\Http\Middleware;

use App\Services\Billing\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins always pass
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Active trial or paid subscription → pass through
        if ($this->subscriptionService->isActive($user)) {
            return $next($request);
        }

        // Expired → redirect to upgrade (allow billing routes to avoid loop)
        if ($request->routeIs('billing.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('billing.upgrade')
            ->with('warning', 'Your free trial has ended. Upgrade to continue using RyaanCMS.');
    }
}
