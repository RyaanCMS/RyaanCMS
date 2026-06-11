<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment for a plan upgrade.
     * Returns ['url' => redirect URL, 'reference' => unique payment reference].
     */
    public function initiatePayment(User $user, Plan $plan): array;

    /**
     * Handle a payment callback/webhook from the gateway.
     * Returns ['paid' => bool, 'reference' => string, 'user_id' => int, 'plan_key' => string].
     */
    public function handleCallback(Request $request): array;

    /**
     * Verify a payment by reference (for manual re-check).
     */
    public function verifyPayment(string $reference): bool;
}
