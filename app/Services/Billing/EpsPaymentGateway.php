<?php

namespace App\Services\Billing;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * EPS (Easy Payment System) gateway adapter.
 *
 * ─── HOW TO WIRE UP YOUR GATEWAY ──────────────────────────────────────────
 * 1. Set these in .env:
 *    EPS_GATEWAY_URL=https://your-eps-gateway.com/pay
 *    EPS_SECRET_KEY=your_secret_key
 *    EPS_MERCHANT_ID=your_merchant_id
 *
 * 2. Fill in initiatePayment() — build the redirect URL/form your gateway needs.
 * 3. Fill in handleCallback() — parse the response your gateway POSTs back.
 * 4. Fill in verifyPayment() — hit your gateway's verification API.
 * ──────────────────────────────────────────────────────────────────────────
 */
class EpsPaymentGateway implements PaymentGatewayInterface
{
    private string $gatewayUrl;
    private string $secretKey;
    private string $merchantId;

    public function __construct()
    {
        $this->gatewayUrl = config('eps.gateway_url', '');
        $this->secretKey  = config('eps.secret_key', '');
        $this->merchantId = config('eps.merchant_id', '');
    }

    /**
     * Build the payment redirect URL for the EPS gateway.
     * TODO: Replace the body of this method with your gateway's API call.
     */
    public function initiatePayment(User $user, Plan $plan): array
    {
        $reference = 'RYAAN-' . strtoupper(Str::random(12));

        // ── TODO: Replace with your EPS gateway call ──────────────────────
        // Example (generic — adapt to your gateway's params):
        //
        // $params = [
        //     'merchant_id'  => $this->merchantId,
        //     'amount'       => $plan->price_monthly,   // cents
        //     'currency'     => $plan->currency,
        //     'reference'    => $reference,
        //     'return_url'   => route('billing.callback') . '?ref=' . $reference,
        //     'customer_email' => $user->email,
        //     'description'  => 'RyaanCMS ' . $plan->name . ' subscription',
        //     'signature'    => hash_hmac('sha256', $reference . $plan->price_monthly, $this->secretKey),
        // ];
        // $redirectUrl = $this->gatewayUrl . '?' . http_build_query($params);
        // ──────────────────────────────────────────────────────────────────

        // Placeholder: redirects back to billing with a simulated success
        $redirectUrl = route('billing.callback') . '?ref=' . $reference . '&status=success&user_id=' . $user->id . '&plan=' . $plan->key;

        return [
            'url'       => $redirectUrl,
            'reference' => $reference,
        ];
    }

    /**
     * Parse the EPS callback/webhook.
     * TODO: Replace the body with your gateway's actual response parsing.
     */
    public function handleCallback(Request $request): array
    {
        // ── TODO: Replace with your EPS gateway's response format ─────────
        // Validate signature if your gateway sends one:
        // $signature = $request->header('X-EPS-Signature');
        // $expected  = hash_hmac('sha256', $request->input('ref'), $this->secretKey);
        // if (!hash_equals($expected, $signature)) {
        //     return ['paid' => false, 'reference' => '', 'user_id' => 0, 'plan_key' => ''];
        // }
        // ──────────────────────────────────────────────────────────────────

        return [
            'paid'      => $request->input('status') === 'success',
            'reference' => $request->input('ref', ''),
            'user_id'   => (int) $request->input('user_id', 0),
            'plan_key'  => $request->input('plan', ''),
        ];
    }

    /**
     * Verify a payment by reference against the gateway.
     * TODO: Replace with your gateway's verification API.
     */
    public function verifyPayment(string $reference): bool
    {
        // ── TODO: Hit your gateway's verification endpoint ────────────────
        // try {
        //     $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->secretKey])
        //         ->get($this->gatewayUrl . '/verify/' . $reference);
        //     return $response->json('status') === 'paid';
        // } catch (\Throwable) {
        //     return false;
        // }
        // ──────────────────────────────────────────────────────────────────

        return true; // Placeholder — always verified in dev mode
    }
}
