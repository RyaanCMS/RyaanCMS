<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Billing\EpsPaymentGateway;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private EpsPaymentGateway $gateway,
    ) {}

    /**
     * Customer billing dashboard.
     */
    public function index()
    {
        $user         = auth()->user();
        $subscription = $this->subscriptionService->currentSubscription($user);
        $plan         = $this->subscriptionService->getUserPlan($user);
        $plans        = Plan::active()->get();
        $isOnTrial    = $this->subscriptionService->isOnTrial($user);
        $trialDays    = $this->subscriptionService->trialDaysLeft($user);

        return view('billing.index', compact(
            'subscription', 'plan', 'plans', 'isOnTrial', 'trialDays'
        ));
    }

    /**
     * Show plan upgrade page.
     */
    public function upgrade()
    {
        $plans       = Plan::active()->where('key', '!=', 'free_trial')->get();
        $currentPlan = $this->subscriptionService->getUserPlanKey(auth()->user());
        return view('billing.upgrade', compact('plans', 'currentPlan'));
    }

    /**
     * Initiate payment for a plan upgrade.
     */
    public function initiate(Request $request, string $planKey)
    {
        $result = $this->subscriptionService->initiateUpgrade(
            auth()->user(),
            $planKey,
            $this->gateway
        );

        if (!$result['ok']) {
            return back()->with('error', $result['error']);
        }

        return redirect($result['redirect_url']);
    }

    /**
     * Handle EPS payment callback (GET redirect after payment).
     */
    public function callback(Request $request)
    {
        $data = $this->gateway->handleCallback($request);

        if (!$data['paid']) {
            return redirect()->route('billing.index')
                ->with('error', 'Payment was not completed. Please try again.');
        }

        if (!$data['user_id'] || !$data['plan_key']) {
            return redirect()->route('billing.index')
                ->with('error', 'Invalid payment response. Contact support.');
        }

        $this->subscriptionService->activateSubscription(
            $data['user_id'],
            $data['plan_key'],
            $data['reference']
        );

        return redirect()->route('billing.index')
            ->with('success', 'Payment successful! Your plan has been upgraded.');
    }

    /**
     * Handle EPS payment webhook (POST from gateway server).
     */
    public function webhook(Request $request)
    {
        $data = $this->gateway->handleCallback($request);

        if ($data['paid'] && $data['user_id'] && $data['plan_key']) {
            $this->subscriptionService->activateSubscription(
                $data['user_id'],
                $data['plan_key'],
                $data['reference']
            );
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request)
    {
        $this->subscriptionService->cancel(auth()->user());
        return back()->with('success', 'Your subscription has been cancelled. Access continues until the end of the current period.');
    }
}
