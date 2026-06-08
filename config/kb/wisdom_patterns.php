<?php
/**
 * Wisdom Patterns Knowledge Base
 * Accumulated lessons from real-world software projects.
 * This is the seed dataset for the WisdomEngine — static wisdom that compounds
 * as real project autopsies are added to the database.
 *
 * Level 6: Experience Knowledge — "Learn from others' mistakes, not your own."
 */
return [

    'meta_rule' => 'Wisdom is not knowledge — it is knowledge filtered through experience and consequence.',

    // ── eCommerce Wisdom ──────────────────────────────────────────────────────
    'ecommerce' => [
        [
            'lesson'         => 'COD abandonment is real — build payment confirmation before shipping dispatch',
            'why_it_matters' => 'BD eCommerce: 30-40% COD orders never collected. Dispatch confirmation call reduces loss.',
            'recommendation' => 'Add delivery confirmation step before marking COD as complete. Alert on repeated COD fails per customer.',
        ],
        [
            'lesson'         => 'Product images matter more than product descriptions in mobile-first markets',
            'why_it_matters' => 'Users browse on mobile, decide visually. Facebook Commerce trains this behavior.',
            'recommendation' => 'Require minimum 3 images per product. Enable zoom on mobile. Enforce image compression pipeline.',
        ],
        [
            'lesson'         => 'Stock reservation on cart-add leads to unhappy edge cases at scale',
            'why_it_matters' => 'Users add to cart and leave. Reservation blocks other buyers. COD markets have 30%+ cart abandon.',
            'recommendation' => 'Reserve stock on ORDER PLACED, not cart add. Use pessimistic locking on checkout only.',
        ],
        [
            'lesson'         => 'Coupon abuse kills margin when single-use coupons are not enforced server-side',
            'why_it_matters' => 'Client-side coupon validation is always bypassable.',
            'recommendation' => 'Always validate coupon uniqueness, usage limits, and per-user limits in the service layer before payment.',
        ],
        [
            'lesson'         => 'Checkout steps above 3 kill conversion in mobile markets',
            'why_it_matters' => 'Every extra step in checkout reduces conversion. Single-page checkout is standard.',
            'recommendation' => 'Maximum 3 checkout steps: Cart → Address + Payment → Confirmation. Pre-fill returning user data.',
        ],
    ],

    // ── SaaS Wisdom ───────────────────────────────────────────────────────────
    'saas' => [
        [
            'lesson'         => 'Free trials that do not require a credit card have 2-5% conversion. With card: 20%+.',
            'why_it_matters' => 'Intent signal. Users who enter payment info are serious. Those who do not will not convert.',
            'recommendation' => 'Capture card on trial signup even if not charged. Use Stripe SetupIntent. Clear messaging: "Not charged until trial ends."',
        ],
        [
            'lesson'         => 'Churn happens in the first 30 days due to activation failure, not product failure',
            'why_it_matters' => 'Users churn because they did not reach the "first value moment", not because the product is bad.',
            'recommendation' => 'Build activation funnel: Signup → First project → First output. If user does not reach step 3 in 7 days, trigger nudge.',
        ],
        [
            'lesson'         => 'Plan limits enforced client-side only are broken on day one',
            'why_it_matters' => 'Any client-side limit can be bypassed by API calls or developer tools.',
            'recommendation' => 'ALL plan limits enforced at the service layer. Plans table stores limits JSON. Check before every limit-relevant operation.',
        ],
        [
            'lesson'         => 'Downgrade path is always more complex than upgrade path',
            'why_it_matters' => 'Users on Pro with 50 projects downgrading to Starter (5 project limit) need clear UX and data handling.',
            'recommendation' => 'Design the downgrade flow before shipping pricing plans. Decide: archive excess, lock access, or force deletion.',
        ],
    ],

    // ── Architecture Wisdom ───────────────────────────────────────────────────
    'architecture' => [
        [
            'lesson'         => 'Start monolith — refactor to modular when team grows above 5 developers',
            'why_it_matters' => 'Premature microservices add coordination overhead that kills small team velocity.',
            'recommendation' => 'Modular monolith (DDD folder structure) is the right default. Microservices only when specific scaling requirement emerges.',
        ],
        [
            'lesson'         => 'Database schema is the hardest thing to change — get it right first',
            'why_it_matters' => 'Renaming a table column when millions of rows exist requires downtime or complex migrations. Business logic is easy to change; schema is not.',
            'recommendation' => 'Spend disproportionate design time on schema. Normalize correctly. Use advisory locks for column renames in production.',
        ],
        [
            'lesson'         => 'Queues are not optional once email, PDF, or external API calls exist',
            'why_it_matters' => 'Synchronous email adds 300-2000ms to every request that sends it. One slow mailserver kills UX.',
            'recommendation' => 'Any operation > 100ms belongs in a queue. Use Redis driver in production. Add Supervisor to keep workers alive.',
        ],
        [
            'lesson'         => 'Missing indexes cause performance cliffs — fast at 1K rows, broken at 100K',
            'why_it_matters' => 'Queries that return instantly in development become table-scans in production. N+1 + no index = disaster.',
            'recommendation' => 'Add indexes in migrations from day 1. Every FK column, every WHERE column, every ORDER BY column needs an index.',
        ],
    ],

    // ── Security Wisdom ───────────────────────────────────────────────────────
    'security' => [
        [
            'lesson'         => 'IDOR (Insecure Direct Object Reference) is the most common real-world Laravel vulnerability',
            'why_it_matters' => '/api/users/456/invoices — if you only check auth, any authenticated user can access any invoice.',
            'recommendation' => 'Every route that touches user-owned data MUST have a policy or explicit owner check. $this->authorize() is not optional.',
        ],
        [
            'lesson'         => 'Payment webhooks confirmed by redirect URL parameter are always exploitable',
            'why_it_matters' => 'Attackers replay or manipulate redirect parameters to fake successful payment confirmation.',
            'recommendation' => 'ONLY trust payment gateway webhooks with HMAC signature verification. The redirect is for UX only, never for business logic.',
        ],
        [
            'lesson'         => 'Mass assignment vulnerabilities are trivially exploitable and common in Laravel',
            'why_it_matters' => '$model->fill($request->all()) with an is_admin field = instant privilege escalation.',
            'recommendation' => 'Always use $fillable (allowlist) never $guarded. Validate + explicitly assign specific fields. Never fill from request directly.',
        ],
    ],

    // ── Product Wisdom ────────────────────────────────────────────────────────
    'product' => [
        [
            'lesson'         => 'Features built before product-market fit are mostly wasted',
            'why_it_matters' => 'Before PMF, you do not know what users want. After PMF, users tell you. Building too much before PMF wastes months.',
            'recommendation' => 'Ship the smallest version that creates value. Measure. Add the NEXT feature users actually ask for.',
        ],
        [
            'lesson'         => 'Onboarding is the highest-leverage investment for any SaaS or app platform',
            'why_it_matters' => 'Users decide if software is worth their time in the first 5 minutes. 70% of churn is activation failure.',
            'recommendation' => 'Build the happy path first. Remove every step that is not necessary for first value. Every friction point costs activation.',
        ],
        [
            'lesson'         => 'Dashboard with too many KPIs teaches users to ignore all of them',
            'why_it_matters' => 'Cognitive overload causes dashboard blindness. Users stop looking entirely.',
            'recommendation' => 'Maximum 4-6 KPIs above the fold. Use progressive disclosure for detailed metrics. Highlight anomalies, not status quo.',
        ],
        [
            'lesson'         => '"Nice to have" features cause "need to have" features to be delayed',
            'why_it_matters' => 'Every feature in the backlog competes for engineering time. Nice-to-have features can delay critical adoption drivers.',
            'recommendation' => 'Apply opportunity cost thinking to every item. "What are we NOT building by building this?" is the most important question.',
        ],
    ],

    // ── Team & Process Wisdom ─────────────────────────────────────────────────
    'team' => [
        [
            'lesson'         => 'Technical debt grows fastest when there are no code reviews',
            'why_it_matters' => 'Solo developers accumulate debt silently. Team reviews catch N+1, no auth, no validation before they reach production.',
            'recommendation' => 'Even 1 reviewer per PR reduces production incidents significantly. AI code review is better than no review.',
        ],
        [
            'lesson'         => 'Undocumented environment variables cause outages after developer turnover',
            'why_it_matters' => '.env files that are not .env.example-documented leave new developers guessing.',
            'recommendation' => 'Every env variable MUST exist in .env.example with a comment. Never commit real values. Use secrets management in production.',
        ],
    ],

    // ── Bangladesh-Specific Wisdom ─────────────────────────────────────────────
    'bangladesh' => [
        [
            'lesson'         => 'COD (Cash on Delivery) is not just a payment option in Bangladesh — it is the default',
            'why_it_matters' => '60%+ of eCommerce orders in Bangladesh are COD. Without it, conversion drops dramatically.',
            'recommendation' => 'Always add COD as the first payment option in BD eCommerce. Handle COD lifecycle: placed → confirmed → dispatched → delivered/returned.',
        ],
        [
            'lesson'         => 'bKash payment confirmation via only redirect is unreliable — webhooks are essential',
            'why_it_matters' => 'bKash redirects can fail if user closes browser. Without webhook, orders get stuck in pending.',
            'recommendation' => 'Always implement bKash IPN (Instant Payment Notification) webhook alongside redirect handler.',
        ],
        [
            'lesson'         => 'WhatsApp/Facebook Messenger as customer support is expected in BD market',
            'why_it_matters' => 'BD customers prefer mobile messaging apps over traditional email/phone for support.',
            'recommendation' => 'Add WhatsApp Business button on every page. Integrate order updates via WhatsApp API for B2C apps.',
        ],
    ],

];
