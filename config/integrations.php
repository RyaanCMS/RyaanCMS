<?php

/**
 * RyaanCMS Integration Library — Worldwide
 *
 * Covers 70+ integrations: payments, shipping, SMS, email, maps, auth, storage, push.
 * KnowledgeBaseService injects the relevant ones into AI prompts so the AI never
 * has to guess package names, env keys, or API patterns.
 *
 * Structure per entry:
 *  name, regions[], category, description, trigger_keywords[], env_keys[],
 *  laravel_package (null = manual), webhook_support, quick_setup (injected to AI)
 */
return [

    // ══════════════════════════════════════════════════════════════════════════
    // PAYMENT GATEWAYS
    // ══════════════════════════════════════════════════════════════════════════

    // ── Bangladesh ────────────────────────────────────────────────────────────
    'bkash' => [
        'name'             => 'bKash',
        'regions'          => ['BD'],
        'category'         => 'payment',
        'description'      => "Bangladesh's largest mobile financial service. Supports payment, refund, and tokenized checkout.",
        'trigger_keywords' => ['bkash', 'bKash', 'mobile banking bd'],
        'env_keys'         => ['BKASH_USERNAME', 'BKASH_PASSWORD', 'BKASH_APP_KEY', 'BKASH_APP_SECRET', 'BKASH_SANDBOX'],
        'laravel_package'  => 'karim007/laravel-bkash-tokenize',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require karim007/laravel-bkash-tokenize. Set BKASH_USERNAME, BKASH_PASSWORD, BKASH_APP_KEY, BKASH_APP_SECRET in .env. Use BkashTokenize facade: BkashTokenize::createPayment($amount, $orderId, $callbackUrl). After redirect: BkashTokenize::executePayment($paymentID). Sandbox: BKASH_SANDBOX=true.',
    ],

    'nagad' => [
        'name'             => 'Nagad',
        'regions'          => ['BD'],
        'category'         => 'payment',
        'description'      => 'Bangladesh Post Office digital financial service. API-based payment gateway.',
        'trigger_keywords' => ['nagad', 'naagad'],
        'env_keys'         => ['NAGAD_MERCHANT_ID', 'NAGAD_PUBLIC_KEY', 'NAGAD_PRIVATE_KEY', 'NAGAD_SANDBOX'],
        'laravel_package'  => 'sslcommerz/sslcommerz-laravel',
        'webhook_support'  => true,
        'quick_setup'      => 'Set NAGAD_MERCHANT_ID, NAGAD_PUBLIC_KEY, NAGAD_PRIVATE_KEY. POST to /api/dfs/checkout/initialize/{merchantId}/{orderId} to init. POST to /api/dfs/checkout/complete/{paymentReferenceId} to execute. Use RSA encryption for sensitive_data using NAGAD_PUBLIC_KEY.',
    ],

    'rocket_dbbl' => [
        'name'             => 'Rocket (Dutch-Bangla Bank)',
        'regions'          => ['BD'],
        'category'         => 'payment',
        'description'      => 'Dutch-Bangla Bank mobile banking (Rocket). Merchant payment API.',
        'trigger_keywords' => ['rocket', 'dbbl', 'dutch bangla', 'rocket mobile banking'],
        'env_keys'         => ['ROCKET_MERCHANT_NO', 'ROCKET_API_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'Contact DBBL for merchant credentials. POST to Rocket payment API with merchant_no, customer_msisdn, amount, and invoice_no. Verify payment status via Rocket merchant portal API.',
    ],

    'sslcommerz' => [
        'name'             => 'SSLCommerz',
        'regions'          => ['BD'],
        'category'         => 'payment',
        'description'      => "Bangladesh's leading payment gateway supporting cards, mobile banking, and internet banking.",
        'trigger_keywords' => ['sslcommerz', 'ssl commerz', 'sslc', 'payment gateway bd'],
        'env_keys'         => ['SSLC_STORE_ID', 'SSLC_STORE_PASSWORD', 'SSLC_SANDBOX'],
        'laravel_package'  => 'sslcommerz/sslcommerz-laravel',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require sslcommerz/sslcommerz-laravel. Publish config: php artisan vendor:publish. Set SSLC_STORE_ID, SSLC_STORE_PASSWORD. SSLCommerz::makePayment($postData) returns redirect URL. Verify IPN at /payment/ipn route via SSL::orderValidate($requestData).',
    ],

    'aamarpay' => [
        'name'             => 'AamarPay',
        'regions'          => ['BD'],
        'category'         => 'payment',
        'description'      => 'Bangladeshi payment gateway with bKash, Nagad, card, and internet banking support.',
        'trigger_keywords' => ['aamarpay', 'aamar pay', 'aamarpay.io'],
        'env_keys'         => ['AAMARPAY_STORE_ID', 'AAMARPAY_SIGNATURE_KEY', 'AAMARPAY_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST to https://sandbox.aamarpay.com/jsonpost.php with store_id, signature_key, amount, currency, tran_id, success_url, fail_url, cancel_url, cus_name, cus_email, cus_phone. Verify at /payment/verify with pay_status == "Successful".',
    ],

    'shurjopay' => [
        'name'             => 'ShurjoPay',
        'regions'          => ['BD'],
        'category'         => 'payment',
        'description'      => 'Bangladeshi payment gateway supporting all local payment channels.',
        'trigger_keywords' => ['shurjopay', 'shurjo pay', 'shurjomukhi'],
        'env_keys'         => ['SHURJOPAY_USERNAME', 'SHURJOPAY_PASSWORD', 'SHURJOPAY_PREFIX', 'SHURJOPAY_RETURN_URL', 'SHURJOPAY_CANCEL_URL'],
        'laravel_package'  => 'shurjomukhi/shurjopay-laravel',
        'webhook_support'  => false,
        'quick_setup'      => 'composer require shurjomukhi/shurjopay-laravel. Configure SHURJOPAY_USERNAME, SHURJOPAY_PASSWORD. ShurjoPay::makePayment($amount, $orderId) returns checkout_url. Verify with ShurjoPay::verifyPayment($orderId).',
    ],

    // ── South Asia ────────────────────────────────────────────────────────────
    'razorpay' => [
        'name'             => 'Razorpay',
        'regions'          => ['IN', 'SouthAsia'],
        'category'         => 'payment',
        'description'      => "India's leading payment gateway. Cards, UPI, NetBanking, wallets, EMI.",
        'trigger_keywords' => ['razorpay', 'india payment', 'upi payment'],
        'env_keys'         => ['RAZORPAY_KEY', 'RAZORPAY_SECRET', 'RAZORPAY_WEBHOOK_SECRET'],
        'laravel_package'  => 'razorpay/razorpay',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require razorpay/razorpay. Set RAZORPAY_KEY, RAZORPAY_SECRET. Create order: $api = new Api($key, $secret); $order = $api->order->create(["amount"=>$paise,"currency"=>"INR","receipt"=>$id]). Render Razorpay checkout button with data-order_id. Verify signature on return.',
    ],

    'paytm' => [
        'name'             => 'Paytm',
        'regions'          => ['IN'],
        'category'         => 'payment',
        'description'      => "India's popular mobile payment and UPI platform.",
        'trigger_keywords' => ['paytm', 'paytm business'],
        'env_keys'         => ['PAYTM_MID', 'PAYTM_KEY', 'PAYTM_WEBSITE', 'PAYTM_CHANNEL', 'PAYTM_INDUSTRY_TYPE'],
        'laravel_package'  => 'anandsiddharth/laravel-paytm-wallet',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require anandsiddharth/laravel-paytm-wallet. Set PAYTM_MID, PAYTM_KEY. PaytmWallet::with(PaytmWallet::PURCHASE)->prepare(["order"=>$id,"user"=>$uid,"mobile_number"=>$phone,"email"=>$email,"amount"=>$amount,"callback_url"=>url("/payment/status")]). ->receive() on callback.',
    ],

    'phonepe' => [
        'name'             => 'PhonePe',
        'regions'          => ['IN'],
        'category'         => 'payment',
        'description'      => "India's largest UPI-based payment platform.",
        'trigger_keywords' => ['phonepe', 'phone pe', 'upi'],
        'env_keys'         => ['PHONEPE_MERCHANT_ID', 'PHONEPE_SALT_KEY', 'PHONEPE_SALT_INDEX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST to PhonePe API /pg/v1/pay. Payload: {"merchantId":$mid,"merchantTransactionId":$txnId,"amount":$paise,"redirectUrl":$cbUrl,"paymentInstrument":{"type":"PAY_PAGE"}}. Encode as base64, append /saltkey and sha256 hash as X-VERIFY header.',
    ],

    'payhere' => [
        'name'             => 'PayHere',
        'regions'          => ['LK'],
        'category'         => 'payment',
        'description'      => "Sri Lanka's leading payment gateway.",
        'trigger_keywords' => ['payhere', 'sri lanka payment'],
        'env_keys'         => ['PAYHERE_MERCHANT_ID', 'PAYHERE_SECRET', 'PAYHERE_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST to https://sandbox.payhere.lk/pay/checkout with merchant_id, return_url, cancel_url, notify_url, order_id, items, currency, amount, first_name, last_name, email, phone, address. Verify hash on notify_url.',
    ],

    // ── Southeast Asia ────────────────────────────────────────────────────────
    'grabpay' => [
        'name'             => 'GrabPay',
        'regions'          => ['SG', 'MY', 'PH', 'ID', 'TH', 'VN'],
        'category'         => 'payment',
        'description'      => "Southeast Asia's super-app payment solution.",
        'trigger_keywords' => ['grabpay', 'grab pay', 'grab wallet'],
        'env_keys'         => ['GRABPAY_CLIENT_ID', 'GRABPAY_CLIENT_SECRET', 'GRABPAY_MERCHANT_ID'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'Use Grab Partner API. POST /grabid/v1/oauth2/token for token. POST /grabpay/partner/v2/charge with partner_tx_id, amount, currency, description. Redirect user to payment_url. Verify at callback with GET /grabpay/partner/v2/charge?partnerTxID=.',
    ],

    'touchngo' => [
        'name'             => "Touch 'n Go (TNG eWallet)",
        'regions'          => ['MY'],
        'category'         => 'payment',
        'description'      => "Malaysia's leading e-wallet and payment platform.",
        'trigger_keywords' => ['touch n go', 'tng', 'tng ewallet', 'malaysia payment'],
        'env_keys'         => ['TNG_CLIENT_ID', 'TNG_CLIENT_SECRET', 'TNG_MERCHANT_ID'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'Use TnG eWallet Partner API. OAuth2 flow for token. Create payment via /payment/v1/charge. Redirect to payment_url. Verify status via /payment/v1/charge/{chargeId} GET request.',
    ],

    // ── Africa ────────────────────────────────────────────────────────────────
    'mpesa' => [
        'name'             => 'M-Pesa (Safaricom)',
        'regions'          => ['KE', 'TZ', 'GH', 'Africa'],
        'category'         => 'payment',
        'description'      => "Africa's most widely used mobile money platform (Safaricom Kenya).",
        'trigger_keywords' => ['mpesa', 'm-pesa', 'safaricom', 'kenya payment', 'africa payment'],
        'env_keys'         => ['MPESA_CONSUMER_KEY', 'MPESA_CONSUMER_SECRET', 'MPESA_SHORTCODE', 'MPESA_PASSKEY', 'MPESA_SANDBOX'],
        'laravel_package'  => 'safaricom/mpesa',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require safaricom/mpesa. Generate token via /oauth/v1/generate. STK Push: POST /mpesa/stkpush/v1/processrequest with BusinessShortCode, Password(base64(shortcode+passkey+timestamp)), Timestamp, Amount, PartyA(phone), PartyB(shortcode), PhoneNumber, CallBackURL. Verify on callback URL.',
    ],

    'flutterwave' => [
        'name'             => 'Flutterwave',
        'regions'          => ['NG', 'GH', 'KE', 'ZA', 'Africa', 'Global'],
        'category'         => 'payment',
        'description'      => "Pan-African payment infrastructure supporting 30+ African countries.",
        'trigger_keywords' => ['flutterwave', 'flutter wave', 'africa payment', 'nigeria payment'],
        'env_keys'         => ['FLUTTERWAVE_PUBLIC_KEY', 'FLUTTERWAVE_SECRET_KEY', 'FLUTTERWAVE_ENCRYPTION_KEY'],
        'laravel_package'  => 'kingflamez/laravelrave',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require kingflamez/laravelrave. Set FLW_PUBLIC_KEY, FLW_SECRET_KEY, FLW_ENCRYPTION_KEY. Rave::initializePayment(["email"=>$e,"amount"=>$a,"tx_ref"=>$ref]). toJson() or toArray(). Verify: Rave::verifyPayment($txRef).',
    ],

    'paystack' => [
        'name'             => 'Paystack',
        'regions'          => ['NG', 'GH', 'ZA', 'Africa'],
        'category'         => 'payment',
        'description'      => "Leading payment gateway for Nigeria, Ghana, South Africa.",
        'trigger_keywords' => ['paystack', 'nigeria payment', 'west africa payment'],
        'env_keys'         => ['PAYSTACK_PUBLIC_KEY', 'PAYSTACK_SECRET_KEY'],
        'laravel_package'  => 'unicodeveloper/laravel-paystack',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require unicodeveloper/laravel-paystack. Set PAYSTACK_PUBLIC_KEY, PAYSTACK_SECRET_KEY. Paystack::getAuthorizationUrl()->redirectNow(). Verify: Paystack::getPaymentData() on callback. Amount is in kobo (×100).',
    ],

    // ── MENA ──────────────────────────────────────────────────────────────────
    'hyperpay' => [
        'name'             => 'HyperPay',
        'regions'          => ['SA', 'AE', 'EG', 'JO', 'MENA'],
        'category'         => 'payment',
        'description'      => 'Middle East and North Africa payment gateway.',
        'trigger_keywords' => ['hyperpay', 'hyper pay', 'saudi payment', 'gulf payment'],
        'env_keys'         => ['HYPERPAY_ACCESS_TOKEN', 'HYPERPAY_ENTITY_ID', 'HYPERPAY_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /v1/checkouts with authentication.entityId and amount, currency, paymentType=DB. Use returned checkout ID to render hosted form. GET /v1/checkouts/{id}/payment with shopperResultUrl to verify.',
    ],

    'myfatoorah' => [
        'name'             => 'MyFatoorah',
        'regions'          => ['KW', 'SA', 'AE', 'BH', 'OM', 'QA', 'Gulf'],
        'category'         => 'payment',
        'description'      => 'Payment gateway for Kuwait and GCC countries.',
        'trigger_keywords' => ['myfatoorah', 'my fatoorah', 'kuwait payment', 'gcc payment'],
        'env_keys'         => ['MYFATOORAH_API_KEY', 'MYFATOORAH_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /v2/InitiatePayment with InvoiceAmount, CurrencyIso. POST /v2/ExecutePayment with PaymentMethodId, CustomerName, CustomerEmail, InvoiceValue, CallBackUrl, ErrorUrl. Verify: GET /v2/GetPaymentStatus with key=paymentId&KeyType=PaymentId.',
    ],

    'fawry' => [
        'name'             => 'Fawry',
        'regions'          => ['EG'],
        'category'         => 'payment',
        'description'      => "Egypt's most popular payment network with 250,000+ outlets.",
        'trigger_keywords' => ['fawry', 'egypt payment', 'fawry code'],
        'env_keys'         => ['FAWRY_MERCHANT_CODE', 'FAWRY_SECURITY_KEY', 'FAWRY_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /api/payments/charge with merchantCode, merchantRefNum, customerProfileId, chargeItems[], paymentMethod=CashOnDelivery|CARD|FAWRY_CODE, amount. Sign with sha256(merchantCode+refNum+itemId+itemQty+itemPrice+securityKey).',
    ],

    // ── Europe ────────────────────────────────────────────────────────────────
    'mollie' => [
        'name'             => 'Mollie',
        'regions'          => ['NL', 'BE', 'DE', 'Europe'],
        'category'         => 'payment',
        'description'      => 'Leading European payment gateway: iDEAL, Bancontact, SEPA, cards.',
        'trigger_keywords' => ['mollie', 'europe payment', 'ideal', 'bancontact', 'netherlands payment'],
        'env_keys'         => ['MOLLIE_KEY'],
        'laravel_package'  => 'mollie/laravel-mollie',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require mollie/laravel-mollie. Set MOLLIE_KEY. mollie()->payments->create(["amount"=>["currency"=>"EUR","value"=>"10.00"],"description"=>$desc,"redirectUrl"=>$url,"webhookUrl"=>$webhook,"metadata"=>["order_id"=>$id]]). Verify: mollie()->payments->get($paymentId)->isPaid().',
    ],

    'klarna' => [
        'name'             => 'Klarna',
        'regions'          => ['SE', 'DE', 'GB', 'US', 'Europe'],
        'category'         => 'payment',
        'description'      => 'Buy now, pay later and installments for Europe and US.',
        'trigger_keywords' => ['klarna', 'buy now pay later', 'bnpl', 'installments'],
        'env_keys'         => ['KLARNA_USERNAME', 'KLARNA_PASSWORD', 'KLARNA_REGION'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /payments/v1/sessions with order_amount, order_lines[], purchase_country, purchase_currency, locale. Use client_token in frontend Klarna.Payments.load(). POST /payments/v1/authorizations/{authorizationToken}/order to finalize.',
    ],

    'payu' => [
        'name'             => 'PayU',
        'regions'          => ['PL', 'CZ', 'TR', 'IN', 'ZA', 'CO', 'Global'],
        'category'         => 'payment',
        'description'      => 'Payment gateway serving Central/Eastern Europe, India, Latin America.',
        'trigger_keywords' => ['payu', 'pay u', 'poland payment', 'turkey payment'],
        'env_keys'         => ['PAYU_CLIENT_ID', 'PAYU_CLIENT_SECRET', 'PAYU_POS_ID', 'PAYU_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /api/v2_1/orders with customerIp, merchantPosId, description, currencyCode, totalAmount, buyer{}, products[]. Auth via Bearer token (POST /pl/standard/user/oauth/authorize with grant_type=client_credentials).',
    ],

    // ── Global ────────────────────────────────────────────────────────────────
    'stripe' => [
        'name'             => 'Stripe',
        'regions'          => ['Global'],
        'category'         => 'payment',
        'description'      => "World's leading payment platform. Cards, SEPA, Apple/Google Pay, subscriptions.",
        'trigger_keywords' => ['stripe', 'stripe payment', 'stripe checkout', 'stripe subscription'],
        'env_keys'         => ['STRIPE_KEY', 'STRIPE_SECRET', 'STRIPE_WEBHOOK_SECRET'],
        'laravel_package'  => 'stripe/stripe-php or laravel/cashier',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require stripe/stripe-php. Set STRIPE_KEY, STRIPE_SECRET. \Stripe\Stripe::setApiKey(env("STRIPE_SECRET")). $session = \Stripe\Checkout\Session::create(["line_items"=>[["price_data"=>["currency"=>"usd","product_data"=>["name"=>$name],"unit_amount"=>$cents],"quantity"=>1]],"mode"=>"payment","success_url"=>$ok,"cancel_url"=>$cancel]). Redirect to $session->url. Verify webhooks with \Stripe\Webhook::constructEvent().',
    ],

    'paypal' => [
        'name'             => 'PayPal',
        'regions'          => ['Global'],
        'category'         => 'payment',
        'description'      => "Global leader in online payments. PayPal Checkout, Braintree, Venmo.",
        'trigger_keywords' => ['paypal', 'paypal checkout', 'paypal express'],
        'env_keys'         => ['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET', 'PAYPAL_SANDBOX'],
        'laravel_package'  => 'srmklive/laravel-paypal',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require srmklive/laravel-paypal. Set PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET. $provider = new PayPalClient; $provider->setApiCredentials(config("paypal")). $token = $provider->getAccessToken(). $order = $provider->createOrder(["CAPTURE","purchase_units"=>[["amount"=>["value"=>"10.00","currency_code"=>"USD"]]]]). Capture with $provider->capturePaymentOrder($orderId).',
    ],

    'square' => [
        'name'             => 'Square',
        'regions'          => ['US', 'CA', 'GB', 'AU', 'JP'],
        'category'         => 'payment',
        'description'      => 'US-focused payment platform with POS and online payments.',
        'trigger_keywords' => ['square', 'square payment', 'squareup'],
        'env_keys'         => ['SQUARE_ACCESS_TOKEN', 'SQUARE_LOCATION_ID', 'SQUARE_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /v2/payments with source_id (card nonce from Square Web Payments SDK), amount_money["amount" in cents,"currency"=>"USD"], location_id, idempotency_key=Str::uuid(). SDK: <script src="https://sandbox.web.squarecdn.com/v1/square.js">.',
    ],

    'braintree' => [
        'name'             => 'Braintree (PayPal)',
        'regions'          => ['Global'],
        'category'         => 'payment',
        'description'      => "PayPal's developer-friendly payment platform for cards and wallets.",
        'trigger_keywords' => ['braintree', 'braintree payment'],
        'env_keys'         => ['BRAINTREE_ENV', 'BRAINTREE_MERCHANT_ID', 'BRAINTREE_PUBLIC_KEY', 'BRAINTREE_PRIVATE_KEY'],
        'laravel_package'  => 'braintree/braintree_php or laravel/cashier-braintree',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require braintree/braintree_php. Braintree\Configuration::environment($env); Set merchant_id, public_key, private_key. $token = Braintree\ClientToken::generate(). Use in Drop-in UI. On submit: Braintree\Transaction::sale(["amount"=>$a,"paymentMethodNonce"=>$nonce,"options"=>["submitForSettlement"=>true]]).',
    ],

    'mercadopago' => [
        'name'             => 'Mercado Pago',
        'regions'          => ['BR', 'AR', 'MX', 'CO', 'CL', 'LATAM'],
        'category'         => 'payment',
        'description'      => "Latin America's largest payment ecosystem.",
        'trigger_keywords' => ['mercado pago', 'mercadopago', 'latin america payment', 'brazil payment', 'argentina payment'],
        'env_keys'         => ['MP_ACCESS_TOKEN', 'MP_PUBLIC_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'composer require mercadopago/dx-php. MP::setAccessToken($token). $preference = new MP\Preference; $preference->items->add(["id"=>$id,"title"=>$name,"unit_price"=>$price,"quantity"=>1]); $preference->back_urls->set_success($url); $pref = $preference->save(); Redirect to $pref->init_point.',
    ],

    // ── Crypto ────────────────────────────────────────────────────────────────
    'coinbase_commerce' => [
        'name'             => 'Coinbase Commerce',
        'regions'          => ['Global'],
        'category'         => 'payment',
        'description'      => 'Accept Bitcoin, Ethereum, USDC, and other cryptocurrencies.',
        'trigger_keywords' => ['crypto payment', 'bitcoin payment', 'ethereum payment', 'coinbase', 'usdc'],
        'env_keys'         => ['COINBASE_COMMERCE_API_KEY', 'COINBASE_WEBHOOK_SECRET'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /charges with name, description, local_price["amount"=>$amt,"currency"=>"USD"], pricing_type="fixed_price", metadata["order_id"=>$id]. Auth: X-CC-Api-Key header. Redirect to hosted_url. Verify webhook: compute hmac_sha256($webhookSecret, $rawBody) == X-CC-Webhook-Signature.',
    ],

    'nowpayments' => [
        'name'             => 'NOWPayments',
        'regions'          => ['Global'],
        'category'         => 'payment',
        'description'      => 'Accept 300+ cryptocurrencies with auto-conversion.',
        'trigger_keywords' => ['nowpayments', 'now payments', 'crypto gateway'],
        'env_keys'         => ['NOWPAYMENTS_API_KEY', 'NOWPAYMENTS_IPN_SECRET'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /v1/invoice with price_amount, price_currency="USD", pay_currency="BTC", order_id, ipn_callback_url, success_url, cancel_url. Auth: x-api-key header. Redirect to invoice_url. Verify IPN: ksort($payload); sha512_hmac matches x-nowpayments-sig header.',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // SHIPPING & LOGISTICS
    // ══════════════════════════════════════════════════════════════════════════

    'pathao' => [
        'name'             => 'Pathao Courier',
        'regions'          => ['BD'],
        'category'         => 'shipping',
        'description'      => "Bangladesh's leading same-day and next-day delivery service.",
        'trigger_keywords' => ['pathao', 'pathao courier', 'pathao delivery'],
        'env_keys'         => ['PATHAO_CLIENT_ID', 'PATHAO_CLIENT_SECRET', 'PATHAO_USERNAME', 'PATHAO_PASSWORD', 'PATHAO_SANDBOX'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /aladdin/api/v1/issue-token with client_id, client_secret, username, password, grant_type=password. POST /aladdin/api/v1/orders with store_id, merchant_order_id, recipient_name, recipient_phone, recipient_city, item_type=2, item_description, item_weight, item_quantity, amount_to_collect.',
    ],

    'redx' => [
        'name'             => 'RedX',
        'regions'          => ['BD'],
        'category'         => 'shipping',
        'description'      => 'RedX courier service for Bangladesh eCommerce.',
        'trigger_keywords' => ['redx', 'red x', 'redx courier'],
        'env_keys'         => ['REDX_API_TOKEN'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'POST /api/v1/parcel with customer_name, customer_phone, delivery_area, delivery_address, merchant_invoice_id, cash_collection_amount, parcel_weight, instruction. Auth: Bearer REDX_API_TOKEN. Track: GET /api/v1/parcel/{trackingId}.',
    ],

    'steadfast' => [
        'name'             => 'Steadfast Courier',
        'regions'          => ['BD'],
        'category'         => 'shipping',
        'description'      => 'Steadfast nationwide delivery network for Bangladesh.',
        'trigger_keywords' => ['steadfast', 'steadfast courier', 'steadfast delivery'],
        'env_keys'         => ['STEADFAST_API_KEY', 'STEADFAST_SECRET_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'POST /api/v1/create_order with invoice, recipient_name, recipient_phone, recipient_address, cod_amount, note. Auth: Api-Key and Secret-Key headers. Track: POST /api/v1/status_by_invoice with invoice.',
    ],

    'dhl' => [
        'name'             => 'DHL Express',
        'regions'          => ['Global'],
        'category'         => 'shipping',
        'description'      => 'Global express delivery and logistics in 220+ countries.',
        'trigger_keywords' => ['dhl', 'dhl express', 'dhl shipping', 'international shipping'],
        'env_keys'         => ['DHL_API_KEY', 'DHL_API_SECRET', 'DHL_ACCOUNT_NUMBER'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /ships/v1/shipments with plannedShippingDateAndTime, pickup, productCode=P, accounts[type=shipper], customerReferences[], content, outputImageProperties. Auth: Basic base64(apiKey:apiSecret). GET /tracking/v1/shipments?trackingNumber= for tracking.',
    ],

    'fedex' => [
        'name'             => 'FedEx',
        'regions'          => ['US', 'Global'],
        'category'         => 'shipping',
        'description'      => 'Global courier delivery and logistics services.',
        'trigger_keywords' => ['fedex', 'fedex shipping', 'federal express'],
        'env_keys'         => ['FEDEX_CLIENT_ID', 'FEDEX_CLIENT_SECRET', 'FEDEX_ACCOUNT_NUMBER'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /oauth/token with grant_type=client_credentials, client_id, client_secret. POST /ship/v1/shipments with shipper, recipient, shippingChargesPayment, requestedPackageLineItems[], serviceType=FEDEX_GROUND. Track: POST /track/v1/trackingnumbers.',
    ],

    'shipstation' => [
        'name'             => 'ShipStation',
        'regions'          => ['US', 'CA', 'GB', 'AU'],
        'category'         => 'shipping',
        'description'      => 'Multi-carrier shipping and order fulfillment platform.',
        'trigger_keywords' => ['shipstation', 'multi carrier shipping', 'order fulfillment'],
        'env_keys'         => ['SHIPSTATION_API_KEY', 'SHIPSTATION_API_SECRET'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'Auth: Basic base64(apiKey:apiSecret). POST /orders/createorder with orderNumber, orderDate, orderStatus, billTo{}, shipTo{}, items[{lineItemKey,sku,name,quantity,unitPrice}]. POST /shipments/createlabel with orderId, carrierId, serviceCode, packageCode, weight, dimensions.',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // SMS & MESSAGING
    // ══════════════════════════════════════════════════════════════════════════

    'twilio' => [
        'name'             => 'Twilio',
        'regions'          => ['Global'],
        'category'         => 'sms',
        'description'      => "World's leading cloud communications platform. SMS, WhatsApp, Voice.",
        'trigger_keywords' => ['twilio', 'sms notification', 'whatsapp business', 'otp sms'],
        'env_keys'         => ['TWILIO_SID', 'TWILIO_TOKEN', 'TWILIO_FROM'],
        'laravel_package'  => 'twilio/sdk',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require twilio/sdk. $twilio = new \Twilio\Rest\Client(env("TWILIO_SID"), env("TWILIO_TOKEN")). $twilio->messages->create($to, ["from"=>env("TWILIO_FROM"),"body"=>$msg]). WhatsApp: $to="whatsapp:+1234567890", $from="whatsapp:+14155238886".',
    ],

    'vonage' => [
        'name'             => 'Vonage (Nexmo)',
        'regions'          => ['Global'],
        'category'         => 'sms',
        'description'      => 'SMS, Voice, WhatsApp API platform (formerly Nexmo).',
        'trigger_keywords' => ['vonage', 'nexmo', 'nexmo sms'],
        'env_keys'         => ['VONAGE_API_KEY', 'VONAGE_API_SECRET', 'VONAGE_FROM'],
        'laravel_package'  => 'vonage/client-core',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require vonage/client-core. $client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic($key, $secret)). $client->sms()->send(new \Vonage\SMS\Message\SMS($to, $from, $text)). 160 char/message, auto-split for longer.',
    ],

    'aws_sns' => [
        'name'             => 'AWS SNS',
        'regions'          => ['Global'],
        'category'         => 'sms',
        'description'      => "Amazon's Simple Notification Service for SMS and push notifications.",
        'trigger_keywords' => ['aws sns', 'amazon sns', 'aws sms'],
        'env_keys'         => ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION'],
        'laravel_package'  => 'aws/aws-sdk-php-laravel',
        'webhook_support'  => false,
        'quick_setup'      => 'composer require aws/aws-sdk-php-laravel. $sns = AWS::createClient("sns"). $sns->publish(["Message"=>$text,"PhoneNumber"=>$e164phone,"MessageAttributes"=>["AWS.SNS.SMS.SMSType"=>["DataType"=>"String","StringValue"=>"Transactional"]]])',
    ],

    'messagebird' => [
        'name'             => 'MessageBird (Bird)',
        'regions'          => ['Global'],
        'category'         => 'sms',
        'description'      => 'Omnichannel messaging: SMS, WhatsApp, email via single API.',
        'trigger_keywords' => ['messagebird', 'bird messaging', 'sms api global'],
        'env_keys'         => ['MESSAGEBIRD_API_KEY'],
        'laravel_package'  => 'messagebird/php-rest-api',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require messagebird/php-rest-api. $client = new \MessageBird\Client($apiKey). $msg = new \MessageBird\Objects\Message; $msg->originator=$from; $msg->recipients->items[]=$phone; $msg->body=$text. $client->messages->create($msg).',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // EMAIL SERVICES
    // ══════════════════════════════════════════════════════════════════════════

    'sendgrid' => [
        'name'             => 'SendGrid (Twilio)',
        'regions'          => ['Global'],
        'category'         => 'email',
        'description'      => 'Transactional and marketing email delivery at scale.',
        'trigger_keywords' => ['sendgrid', 'send grid', 'transactional email', 'email delivery'],
        'env_keys'         => ['SENDGRID_API_KEY', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'],
        'laravel_package'  => 'laravel/mail with sendgrid driver',
        'webhook_support'  => true,
        'quick_setup'      => 'In .env: MAIL_MAILER=sendgrid, SENDGRID_API_KEY=xxx. In config/mail.php add sendgrid driver using Laravel HTTP client. Or: composer require s-ichikawa/laravel-sendgrid-driver. Use Mail::to($email)->send(new Mailable). Track opens/clicks via SendGrid webhooks.',
    ],

    'mailgun' => [
        'name'             => 'Mailgun',
        'regions'          => ['Global'],
        'category'         => 'email',
        'description'      => 'Email API for developers with powerful analytics.',
        'trigger_keywords' => ['mailgun', 'mail gun', 'email api'],
        'env_keys'         => ['MAILGUN_DOMAIN', 'MAILGUN_SECRET', 'MAILGUN_ENDPOINT'],
        'laravel_package'  => 'symfony/mailgun-mailer',
        'webhook_support'  => true,
        'quick_setup'      => 'MAIL_MAILER=mailgun in .env. Set MAILGUN_DOMAIN and MAILGUN_SECRET. composer require symfony/mailgun-mailer symfony/http-client. Use standard Laravel Mail::send(). MAILGUN_ENDPOINT=api.eu.mailgun.net for EU region.',
    ],

    'aws_ses' => [
        'name'             => 'Amazon SES',
        'regions'          => ['Global'],
        'category'         => 'email',
        'description'      => 'High-volume, cost-effective transactional email service.',
        'trigger_keywords' => ['aws ses', 'amazon ses', 'ses email'],
        'env_keys'         => ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION', 'MAIL_FROM_ADDRESS'],
        'laravel_package'  => 'aws/aws-sdk-php-laravel',
        'webhook_support'  => true,
        'quick_setup'      => 'MAIL_MAILER=ses in .env. Set AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION. composer require aws/aws-sdk-php-laravel. Laravel handles SES natively via ses mail driver. Verify sender domain in SES console.',
    ],

    'mailchimp' => [
        'name'             => 'Mailchimp',
        'regions'          => ['Global'],
        'category'         => 'email',
        'description'      => 'Email marketing platform with automation and audience management.',
        'trigger_keywords' => ['mailchimp', 'email marketing', 'newsletter', 'email campaign'],
        'env_keys'         => ['MAILCHIMP_API_KEY', 'MAILCHIMP_LIST_ID'],
        'laravel_package'  => 'mailchimp/marketing',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require mailchimp/marketing. $client = new \MailchimpMarketing\ApiClient; $client->setConfig(["apiKey"=>$key,"server"=>"us6"]). Add subscriber: $client->lists->addListMember($listId, ["email_address"=>$email,"status"=>"subscribed","merge_fields"=>["FNAME"=>$name]])',
    ],

    'brevo' => [
        'name'             => 'Brevo (Sendinblue)',
        'regions'          => ['Global', 'Europe'],
        'category'         => 'email',
        'description'      => 'Email, SMS, and CRM platform with GDPR compliance.',
        'trigger_keywords' => ['brevo', 'sendinblue', 'send in blue', 'gdpr email'],
        'env_keys'         => ['BREVO_API_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST /v3/smtp/email with sender{"name","email"}, to[{"email","name"}], subject, htmlContent. Auth: api-key header. Or use transactional templates: POST /v3/smtp/email with templateId and params{}.',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // MAPS & LOCATION
    // ══════════════════════════════════════════════════════════════════════════

    'google_maps' => [
        'name'             => 'Google Maps Platform',
        'regions'          => ['Global'],
        'category'         => 'maps',
        'description'      => 'Maps, Geocoding, Places, Directions, Distance Matrix APIs.',
        'trigger_keywords' => ['google maps', 'map', 'location', 'geocoding', 'directions', 'distance'],
        'env_keys'         => ['GOOGLE_MAPS_API_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'Add Google Maps JS: <script src="https://maps.googleapis.com/maps/api/js?key={GOOGLE_MAPS_API_KEY}&libraries=places">. Geocoding: GET https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$key. Distance: GET /distancematrix/json?origins=$from&destinations=$to&key=$key.',
    ],

    'mapbox' => [
        'name'             => 'Mapbox',
        'regions'          => ['Global'],
        'category'         => 'maps',
        'description'      => 'Customizable maps, navigation, and location search.',
        'trigger_keywords' => ['mapbox', 'custom map', 'map tiles'],
        'env_keys'         => ['MAPBOX_TOKEN'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'Include mapbox-gl.js and mapbox-gl.css CDN. mapboxgl.accessToken = "pk.xxx". new mapboxgl.Map({container:"map",style:"mapbox://styles/mapbox/dark-v10",center:[$lng,$lat],zoom:12}). Geocoding: GET https://api.mapbox.com/geocoding/v5/mapbox.places/$query.json?access_token=$token.',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // SOCIAL AUTHENTICATION
    // ══════════════════════════════════════════════════════════════════════════

    'google_oauth' => [
        'name'             => 'Google OAuth / Sign In with Google',
        'regions'          => ['Global'],
        'category'         => 'social_auth',
        'description'      => 'Sign in with Google using Laravel Socialite.',
        'trigger_keywords' => ['google login', 'sign in with google', 'google oauth', 'google auth'],
        'env_keys'         => ['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_REDIRECT'],
        'laravel_package'  => 'laravel/socialite',
        'webhook_support'  => false,
        'quick_setup'      => 'composer require laravel/socialite. In config/services.php: "google"=>["client_id"=>env("GOOGLE_CLIENT_ID"),"client_secret"=>env("GOOGLE_CLIENT_SECRET"),"redirect"=>env("GOOGLE_REDIRECT")]. Route::get("/auth/google", fn()=>Socialite::driver("google")->redirect()). Route::get("/auth/google/callback", fn()=>Socialite::driver("google")->user()). Create/find user by email.',
    ],

    'facebook_oauth' => [
        'name'             => 'Facebook / Meta Login',
        'regions'          => ['Global'],
        'category'         => 'social_auth',
        'description'      => 'Login with Facebook using Laravel Socialite.',
        'trigger_keywords' => ['facebook login', 'login with facebook', 'meta login', 'facebook oauth'],
        'env_keys'         => ['FACEBOOK_CLIENT_ID', 'FACEBOOK_CLIENT_SECRET', 'FACEBOOK_REDIRECT'],
        'laravel_package'  => 'laravel/socialite',
        'webhook_support'  => false,
        'quick_setup'      => 'Same setup as Google OAuth but driver="facebook". Add "facebook" to config/services.php with app_id and app_secret. Socialite::driver("facebook")->redirect() and ->user() on callback.',
    ],

    'github_oauth' => [
        'name'             => 'GitHub OAuth',
        'regions'          => ['Global'],
        'category'         => 'social_auth',
        'description'      => 'Sign in with GitHub for developer-focused apps.',
        'trigger_keywords' => ['github login', 'login with github', 'github oauth'],
        'env_keys'         => ['GITHUB_CLIENT_ID', 'GITHUB_CLIENT_SECRET', 'GITHUB_REDIRECT'],
        'laravel_package'  => 'laravel/socialite',
        'webhook_support'  => false,
        'quick_setup'      => 'Same as Google OAuth but driver="github". Register app at github.com/settings/applications/new. Socialite::driver("github")->scopes(["user:email"])->redirect().',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // CLOUD STORAGE
    // ══════════════════════════════════════════════════════════════════════════

    'aws_s3' => [
        'name'             => 'Amazon S3',
        'regions'          => ['Global'],
        'category'         => 'storage',
        'description'      => "World's most widely used object storage service.",
        'trigger_keywords' => ['aws s3', 'amazon s3', 's3 bucket', 'file upload cloud', 'cloud storage'],
        'env_keys'         => ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION', 'AWS_BUCKET', 'AWS_URL'],
        'laravel_package'  => 'league/flysystem-aws-s3-v3',
        'webhook_support'  => false,
        'quick_setup'      => 'composer require league/flysystem-aws-s3-v3. Set FILESYSTEM_DISK=s3 and AWS credentials in .env. Storage::disk("s3")->put($path, $fileContents). Storage::disk("s3")->url($path) for public URL. Storage::disk("s3")->temporaryUrl($path, now()->addMinutes(5)) for signed URLs.',
    ],

    'cloudinary' => [
        'name'             => 'Cloudinary',
        'regions'          => ['Global'],
        'category'         => 'storage',
        'description'      => 'Media management: upload, transform, optimize, and deliver images and videos.',
        'trigger_keywords' => ['cloudinary', 'image upload', 'image optimization', 'media cdn'],
        'env_keys'         => ['CLOUDINARY_URL', 'CLOUDINARY_CLOUD_NAME', 'CLOUDINARY_API_KEY', 'CLOUDINARY_API_SECRET'],
        'laravel_package'  => 'cloudinary-labs/cloudinary-laravel',
        'webhook_support'  => true,
        'quick_setup'      => 'composer require cloudinary-labs/cloudinary-laravel. Set CLOUDINARY_URL=cloudinary://{API_KEY}:{API_SECRET}@{CLOUD_NAME}. cloudinary()->upload($request->file("image")->getRealPath())->getSecurePath(). Transform: cloudinary()->image($publicId)->resize(Resize::crop()->width(300)->height(300))->toUrl()',
    ],

    'bunny_cdn' => [
        'name'             => 'Bunny CDN / BunnyStor',
        'regions'          => ['Global'],
        'category'         => 'storage',
        'description'      => 'Affordable, fast CDN and object storage with global PoPs.',
        'trigger_keywords' => ['bunny cdn', 'bunnycdn', 'bunny storage', 'cdn storage'],
        'env_keys'         => ['BUNNY_STORAGE_ZONE', 'BUNNY_STORAGE_PASSWORD', 'BUNNY_CDN_URL'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'PUT https://storage.bunnycdn.com/{storageZone}/{path}/{filename} with AccessKey header = BUNNY_STORAGE_PASSWORD and file body. Public URL: {BUNNY_CDN_URL}/{path}/{filename}. Delete: DELETE same URL. List: GET https://storage.bunnycdn.com/{zone}/{path}/.',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // PUSH NOTIFICATIONS
    // ══════════════════════════════════════════════════════════════════════════

    'firebase_fcm' => [
        'name'             => 'Firebase FCM',
        'regions'          => ['Global'],
        'category'         => 'push',
        'description'      => 'Google Firebase Cloud Messaging for mobile and web push notifications.',
        'trigger_keywords' => ['firebase', 'fcm', 'push notification', 'mobile notification', 'firebase notification'],
        'env_keys'         => ['FIREBASE_SERVER_KEY', 'FIREBASE_PROJECT_ID'],
        'laravel_package'  => 'kreait/laravel-firebase',
        'webhook_support'  => false,
        'quick_setup'      => 'composer require kreait/laravel-firebase. Set FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json. $factory = (new Factory)->withServiceAccount($credentials). $messaging = $factory->createMessaging(). $message = CloudMessage::withTarget("token",$deviceToken)->withNotification(["title"=>$t,"body"=>$b]). $messaging->send($message).',
    ],

    'onesignal' => [
        'name'             => 'OneSignal',
        'regions'          => ['Global'],
        'category'         => 'push',
        'description'      => 'Cross-platform push notifications for web, iOS, Android.',
        'trigger_keywords' => ['onesignal', 'one signal', 'push notification web'],
        'env_keys'         => ['ONESIGNAL_APP_ID', 'ONESIGNAL_REST_API_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'POST https://onesignal.com/api/v1/notifications with app_id, headings{"en":$title}, contents{"en":$body}, and either included_player_ids[$deviceId] or filters[]. Auth: Authorization: Basic {ONESIGNAL_REST_API_KEY}. Client: include OneSignal SDK, call OneSignal.init({appId:$appId}).',
    ],

    'pusher' => [
        'name'             => 'Pusher Channels',
        'regions'          => ['Global'],
        'category'         => 'realtime',
        'description'      => 'Real-time WebSocket events: chat, notifications, live updates.',
        'trigger_keywords' => ['pusher', 'websocket', 'real-time', 'live notification', 'realtime chat'],
        'env_keys'         => ['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'],
        'laravel_package'  => 'pusher/pusher-php-server',
        'webhook_support'  => false,
        'quick_setup'      => 'BROADCAST_DRIVER=pusher in .env. composer require pusher/pusher-php-server. Server: event(new OrderUpdated($order)) — extends ShouldBroadcast. Client: import Echo from "laravel-echo"; window.Pusher=require("pusher-js"); Echo.channel("orders").listen("OrderUpdated", e=>console.log(e)).',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // VIDEO & COMMUNICATION
    // ══════════════════════════════════════════════════════════════════════════

    'zoom' => [
        'name'             => 'Zoom',
        'regions'          => ['Global'],
        'category'         => 'video',
        'description'      => 'Video conferencing and webinar platform with meeting creation API.',
        'trigger_keywords' => ['zoom', 'video call', 'video meeting', 'online meeting', 'webinar'],
        'env_keys'         => ['ZOOM_CLIENT_ID', 'ZOOM_CLIENT_SECRET', 'ZOOM_ACCOUNT_ID'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'Server-to-Server OAuth: POST /oauth/token?grant_type=account_credentials&account_id={ACCOUNT_ID} with Basic base64(clientId:clientSecret). Create meeting: POST /users/me/meetings with topic, type=2, start_time, duration, settings{host_video,participant_video}. Returns join_url and start_url.',
    ],

    'agora' => [
        'name'             => 'Agora',
        'regions'          => ['Global'],
        'category'         => 'video',
        'description'      => 'Real-time voice, video, and interactive live streaming SDK.',
        'trigger_keywords' => ['agora', 'video sdk', 'live streaming', 'video call sdk', 'telemedicine video'],
        'env_keys'         => ['AGORA_APP_ID', 'AGORA_APP_CERTIFICATE'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'Include agora-rtc-sdk CDN in frontend. Backend token: use RtcTokenBuilder::buildTokenWithUserAccount($appId, $cert, $channelName, $uid, $role, $expireTime). Frontend: agoraClient.join($appId, $channel, $token, $uid). Publish/subscribe to audio-video tracks.',
    ],

    // ══════════════════════════════════════════════════════════════════════════
    // UTILITY
    // ══════════════════════════════════════════════════════════════════════════

    'google_recaptcha' => [
        'name'             => 'Google reCAPTCHA',
        'regions'          => ['Global'],
        'category'         => 'security',
        'description'      => 'Bot protection for forms using invisible or checkbox reCAPTCHA.',
        'trigger_keywords' => ['recaptcha', 'captcha', 'bot protection', 'form security', 'spam prevention'],
        'env_keys'         => ['RECAPTCHA_SITE_KEY', 'RECAPTCHA_SECRET_KEY'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'Frontend: <script src="https://www.google.com/recaptcha/api.js">. <div class="g-recaptcha" data-sitekey="{RECAPTCHA_SITE_KEY}"></div>. Backend: POST https://www.google.com/recaptcha/api/siteverify with secret and response. Check $result["success"] === true before processing form.',
    ],

    'google_analytics' => [
        'name'             => 'Google Analytics 4',
        'regions'          => ['Global'],
        'category'         => 'analytics',
        'description'      => 'Website traffic and user behavior analytics.',
        'trigger_keywords' => ['google analytics', 'analytics', 'ga4', 'tracking'],
        'env_keys'         => ['GA_MEASUREMENT_ID'],
        'laravel_package'  => null,
        'webhook_support'  => false,
        'quick_setup'      => 'Add to <head>: <script async src="https://www.googletagmanager.com/gtag/js?id={GA_MEASUREMENT_ID}"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config","{GA_MEASUREMENT_ID}")</script>.',
    ],

    'slack' => [
        'name'             => 'Slack Notifications',
        'regions'          => ['Global'],
        'category'         => 'notification',
        'description'      => 'Send system alerts and notifications to Slack channels via webhooks.',
        'trigger_keywords' => ['slack', 'slack notification', 'slack webhook', 'team notification'],
        'env_keys'         => ['SLACK_WEBHOOK_URL'],
        'laravel_package'  => 'laravel/slack-notification-channel',
        'webhook_support'  => false,
        'quick_setup'      => 'composer require laravel/slack-notification-channel. In Notification: use SlackMessage. return $notif->slack()->content($text)->attachment(fn($a)=>$a->title($title)->content($body)). Or simple: Http::post(env("SLACK_WEBHOOK_URL"), ["text"=>$message]).',
    ],

    'telegram' => [
        'name'             => 'Telegram Bot API',
        'regions'          => ['Global'],
        'category'         => 'notification',
        'description'      => 'Send messages, alerts, and notifications via Telegram bots.',
        'trigger_keywords' => ['telegram', 'telegram bot', 'telegram notification'],
        'env_keys'         => ['TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID'],
        'laravel_package'  => null,
        'webhook_support'  => true,
        'quick_setup'      => 'Http::post("https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage", ["chat_id"=>env("TELEGRAM_CHAT_ID"),"text"=>$message,"parse_mode"=>"HTML"]). Get chat_id by messaging your bot and calling getUpdates. Or use irazasyed/telegram-bot-sdk package.',
    ],

];
