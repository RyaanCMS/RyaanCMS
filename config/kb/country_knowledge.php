<?php
/**
 * Country / Region Knowledge Base
 * Localization, payment gateways, UX, compliance, cultural context.
 * AI auto-applies this based on project location or domain context.
 * Competitive advantage: most AI tools ignore country-specific needs.
 */
return [

    // ── Bangladesh ──────────────────────────────────────────────────────────────
    'BD' => [
        'name'     => 'Bangladesh',
        'currency' => 'BDT (৳)',
        'locale'   => 'bn_BD',
        'timezone' => 'Asia/Dhaka',

        'payment_gateways' => [
            'mobile_banking' => [
                'bkash'   => ['package' => 'karim007/laravel-bkash-tokenize', 'market_share' => '50M+ users', 'type' => 'tokenize'],
                'nagad'   => ['package' => 'custom HTTP API', 'note' => 'Official merchant API + sandbox'],
                'rocket'  => ['note' => 'DBBL mobile banking — smaller user base'],
                'upay'    => ['note' => 'UCB mobile banking'],
            ],
            'card_gateway'   => [
                'sslcommerz' => ['package' => 'sslcommerz/sslcommerz-laravel', 'note' => 'Widest BD bank coverage'],
                'shurjopay'  => ['note' => 'Growing — supports cards + mobile banking'],
                'aamarpay'   => ['note' => 'Local gateway, Visa/MC + MFS'],
            ],
            'bank_transfer'  => 'BEFTN / NPSB for B2B — not common for retail',
            'cash_on_delivery' => 'ESSENTIAL for BD eCommerce — 60%+ orders are COD',
        ],

        'ecommerce_specifics' => [
            'cod_mandatory'          => true,
            'sms_otp_common'         => true,
            'mobile_number_as_login' => true,
            'facebook_commerce'      => 'Facebook/Instagram is primary discovery channel — add social sharing',
            'delivery_partners'      => ['Pathao', 'Shohoz', 'RedX', 'Paperfly', 'Sundarban Courier'],
            'delivery_api'           => 'Pathao has developer API for parcel booking integration',
            'typical_delivery_time'  => 'Dhaka 1-2 days, outside Dhaka 3-5 days',
        ],

        'sms_providers' => [
            'ssl_wireless'  => 'Most popular bulk SMS in Bangladesh',
            'infobip'       => 'International with BD local routing',
            'twilio'        => 'Available but expensive for BD numbers',
            'clickatell'    => 'Available — supports BD',
        ],

        'legal_compliance' => [
            'vat'           => '15% VAT on goods — NBR requirement for businesses',
            'mushak_challan'=> 'VAT invoice format required — MUSHAK 6.3',
            'digital_security_act' => 'Data sovereignty — sensitive data should stay in BD servers',
            'company_reg'   => 'RJSC registration required for businesses',
        ],

        'ux_culture' => [
            'language'            => 'Bangla (বাংলা) + English bilingual — offer both',
            'mobile_first'        => 'Over 90% traffic is mobile — responsive is non-negotiable',
            'whatsapp_support'    => 'WhatsApp chat button expected on every business website',
            'facebook_login'      => 'Facebook login very common, Google second',
            'image_heavy'         => 'Users prefer product image galleries — multiple images expected',
            'price_sensitivity'   => 'Show discount %, original vs sale price prominently',
        ],

        'hosting' => [
            'local'     => ['Exonhost', 'SSD Hosting BD', 'HosterBD'],
            'popular'   => 'Many use DigitalOcean Singapore region for low latency',
            'cdn'       => 'Cloudflare free tier widely used',
        ],
    ],

    // ── United Arab Emirates ────────────────────────────────────────────────────
    'UAE' => [
        'name'     => 'United Arab Emirates',
        'currency' => 'AED (د.إ)',
        'locale'   => 'ar_AE',
        'timezone' => 'Asia/Dubai',

        'payment_gateways' => [
            'primary'  => ['Stripe', 'PayTabs', 'Telr', 'Network International', 'Checkout.com'],
            'local'    => ['Tabby (BNPL)', 'Tamara (BNPL)', 'Apple Pay (very common)', 'Google Pay'],
            'note'     => 'BNPL (Buy Now Pay Later) is HUGE in UAE — integrate Tabby or Tamara',
        ],

        'legal_compliance' => [
            'vat'              => '5% VAT since Jan 2018 — mandatory for businesses > AED 375K revenue',
            'vat_display'      => 'Display price inclusive OR exclusive with "VAT inclusive" label',
            'trn_number'       => 'TRN must appear on all tax invoices',
            'data_residency'   => 'Financial data should be UAE-hosted (regulatory preference)',
        ],

        'ux_culture' => [
            'rtl_support'     => 'Arabic RTL required — use dir="rtl" and RTL-aware CSS',
            'bilingual'       => 'Arabic + English — toggle option in header',
            'luxury_branding' => 'High-end aesthetics expected — clean, premium design',
            'expat_market'    => 'Large English-only expat audience — English UX must be excellent',
            'whatsapp'        => 'WhatsApp business integration almost mandatory for customer service',
        ],

        'popular_services' => [
            'delivery'   => ['Deliveroo', 'Talabat', 'Zomato', 'Fetchr for parcels'],
            'cloud'      => 'AWS Middle East (Bahrain), Azure UAE North — use for compliance',
        ],
    ],

    // ── United States ───────────────────────────────────────────────────────────
    'US' => [
        'name'     => 'United States',
        'currency' => 'USD ($)',
        'locale'   => 'en_US',
        'timezone' => 'Multi-timezone (use UTC internally, display user timezone)',

        'payment_gateways' => [
            'primary'    => 'Stripe (best developer experience, webhooks, subscriptions)',
            'alternative'=> ['PayPal', 'Square', 'Braintree', 'Authorize.Net'],
            'bnpl'       => ['Affirm', 'Klarna', 'Afterpay'],
            'note'       => 'Stripe + Stripe Connect for marketplace/platform splits',
        ],

        'legal_compliance' => [
            'sales_tax'   => 'Sales tax varies by state — use TaxJar or Avalara API',
            'gdpr_like'   => 'CCPA (California) — privacy policy required, opt-out for data sale',
            'hipaa'       => 'Healthcare apps MUST comply with HIPAA — encrypted, audited, BAA',
            'pci_dss'     => 'Never store raw card numbers — use tokenized payment (Stripe handles this)',
            'ada'         => 'ADA/WCAG 2.1 AA accessibility compliance recommended for public-facing apps',
        ],

        'ux_culture' => [
            'date_format'   => 'MM/DD/YYYY — US convention',
            'phone_format'  => '+1 (XXX) XXX-XXXX',
            'trust_signals' => 'SSL badge, money-back guarantee, reviews crucial for conversion',
            'email_signup'  => 'Email still primary — welcome drip sequence expected',
            'dark_patterns' => 'Avoid dark patterns — increasing legal risk (FTC guidance)',
        ],
    ],

    // ── India ────────────────────────────────────────────────────────────────────
    'IN' => [
        'name'     => 'India',
        'currency' => 'INR (₹)',
        'locale'   => 'en_IN',
        'timezone' => 'Asia/Kolkata',

        'payment_gateways' => [
            'upi'        => 'UPI is DOMINANT — PhonePe, GPay, Paytm — integrate Razorpay UPI',
            'primary'    => 'Razorpay (best for India — UPI + cards + netbanking + wallets)',
            'alternative'=> ['PayU', 'Cashfree', 'PayTM Payment Gateway', 'CCAvenue'],
            'note'       => 'RBI mandates two-factor auth for cards — built into Indian gateways',
        ],

        'legal_compliance' => [
            'gst'         => '18% GST on software services — mandatory registration if > ₹20L turnover',
            'invoice'     => 'GST invoice must include GSTIN, HSN/SAC code',
            'it_act'      => 'IT Act 2000 + PDPB compliance for data collection',
            'rbi_rules'   => 'Recurring payments need mandate with RBI E-NACH integration',
        ],

        'ux_culture' => [
            'mobile_first'  => 'India is mobile-first — lightweight pages critical (low-end devices)',
            'regional_lang' => 'Hindi + English minimum; regional language support for Tier 2/3 cities',
            'whatsapp'      => 'WhatsApp Business API important for order updates',
            'price_display' => 'Show EMI options for items > ₹5000',
        ],
    ],

    // ── Saudi Arabia ─────────────────────────────────────────────────────────────
    'SA' => [
        'name'     => 'Saudi Arabia',
        'currency' => 'SAR (ر.س)',
        'locale'   => 'ar_SA',
        'timezone' => 'Asia/Riyadh',

        'payment_gateways' => [
            'primary'   => ['Hyperpay', 'Moyasar', 'PayTabs', 'Tamara (BNPL)'],
            'local'     => 'Mada cards (local debit network) — MUST support for Saudi market',
            'apple_pay' => 'Apple Pay very popular — integrate via gateway',
        ],

        'legal_compliance' => [
            'vat'       => '15% VAT — mandatory registration for businesses > SAR 375K',
            'zatca'     => 'ZATCA e-invoicing mandatory for B2B — Phase 2 requires API integration',
            'data'      => 'PDPL (Personal Data Protection Law) — consent required',
        ],

        'ux_culture' => [
            'rtl'       => 'Arabic RTL mandatory',
            'hijri'     => 'Support Hijri calendar alongside Gregorian — many users prefer it',
            'bilingual' => 'Arabic primary, English secondary',
        ],
    ],

    // ── United Kingdom ──────────────────────────────────────────────────────────
    'UK' => [
        'name'     => 'United Kingdom',
        'currency' => 'GBP (£)',
        'locale'   => 'en_GB',
        'timezone' => 'Europe/London',

        'payment_gateways' => ['Stripe', 'GoCardless (direct debit)', 'PayPal', 'Square'],

        'legal_compliance' => [
            'vat'   => '20% standard VAT — mandatory for businesses > £90K turnover',
            'gdpr'  => 'UK GDPR (post-Brexit) — cookie consent, privacy policy, DPA',
            'ico'   => 'ICO registration required if processing personal data',
        ],

        'ux_culture' => [
            'date_format' => 'DD/MM/YYYY',
            'spellings'   => 'Colour, Favourite, Authorise (British English if targeting UK market)',
        ],
    ],

    // ── Detect Country Hints ─────────────────────────────────────────────────────
    'detection_hints' => [
        'keywords' => [
            'BD'  => ['bkash', 'nagad', 'bangladesh', 'dhaka', 'bangladeshi', 'taka', 'bd taka', 'pathao'],
            'UAE' => ['uae', 'dubai', 'abu dhabi', 'emirati', 'dirham', 'aed'],
            'IN'  => ['india', 'indian', 'rupee', 'inr', 'razorpay', 'upi', 'paytm'],
            'SA'  => ['saudi', 'riyadh', 'jeddah', 'mada', 'sar', 'zatca'],
            'US'  => ['usa', 'american', 'dollar', 'usd', 'us market'],
            'UK'  => ['uk', 'british', 'england', 'pound', 'gbp'],
        ],
    ],

];
