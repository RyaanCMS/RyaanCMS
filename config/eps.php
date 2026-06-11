<?php

return [
    /*
     | EPS (Easy Payment System) gateway configuration.
     | Set these in .env when you receive your gateway API credentials.
     */
    'gateway_url' => env('EPS_GATEWAY_URL', ''),
    'secret_key'  => env('EPS_SECRET_KEY', ''),
    'merchant_id' => env('EPS_MERCHANT_ID', ''),

    /*
     | Callback URL that the gateway will redirect to after payment.
     | Should be: https://ryaancms.com/billing/callback
     */
    'callback_url' => env('EPS_CALLBACK_URL', env('APP_URL', '') . '/billing/callback'),

    /*
     | Webhook URL for server-to-server payment confirmation.
     | Should be: https://ryaancms.com/billing/webhook
     */
    'webhook_url'  => env('EPS_WEBHOOK_URL', env('APP_URL', '') . '/billing/webhook'),
];
