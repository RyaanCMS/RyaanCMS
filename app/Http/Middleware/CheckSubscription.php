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

        // Admins always pass through
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Always allow billing and logout routes to avoid redirect loops
        if ($request->routeIs('billing.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        try {
            // If user has no subscription at all (pre-existing user from before
            // the SaaS system was added), auto-start a free trial for them now.
            $sub = $this->subscriptionService->currentSubscription($user);
            if (!$sub) {
                $this->subscriptionService->startTrial($user);
                return $next($request);
            }

            if ($this->subscriptionService->isActive($user)) {
                return $next($request);
            }
        } catch (\Throwable) {
            // DB not migrated yet or any other error — let the request through
            // rather than locking every user out of the app.
            return $next($request);
        }

        return redirect()->route('billing.upgrade')
            ->with('warning', 'Your free trial has ended. Upgrade to continue using RyaanCMS.');
    }
}
