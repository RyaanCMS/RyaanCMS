<?php
/**
 * AI Replacement Knowledge Base
 * The most important cost-reduction KB. Explicitly defines what should NEVER hit AI.
 *
 * Philosophy: AI is for novelty. Rules are for repetition.
 * Every time you convert an AI decision to a rule, you eliminate that cost forever.
 *
 * Goal: Reduce external AI usage by 70–95% through intelligent routing.
 * Level 5+: Decision Knowledge — Meta-cognitive system awareness
 */
return [

    // ── Core Principle ────────────────────────────────────────────────────────
    'principle' => 'AI should only handle what cannot be predetermined. Everything else is waste.',

    // ── Never Use AI For (Use Rule Engine / Generator / Cache / Template) ─────
    'never_use_ai_for' => [

        // ── CRUD (saves 70% of AI budget) ─────────────────────────────────────
        'standard_crud_controller' => [
            'use_instead'  => 'MetadataCrudGenerator — generates index, store, update, destroy from field map',
            'savings'      => '~400-600 tokens per controller',
            'quality'      => 'More consistent than AI — same pattern every time',
        ],

        'standard_crud_model' => [
            'use_instead'  => 'MetadataCrudGenerator — generates $fillable, $casts, $hidden, relationships',
            'savings'      => '~200-400 tokens per model',
        ],

        'database_migrations' => [
            'use_instead'  => 'Pattern generator from field definitions — DECIMAL money, FK constraints, indexes auto-added',
            'savings'      => '~300-500 tokens per migration',
            'note'         => 'Migration generator has better FK/index awareness than average AI generation',
        ],

        'api_resources' => [
            'use_instead'  => 'Resource generator from field map — auto-excludes $hidden fields, formats dates',
            'savings'      => '~200-300 tokens per resource',
        ],

        'form_request_validation' => [
            'use_instead'  => 'Rule engine from field types: string→required|string|max:255, email→required|email, etc.',
            'savings'      => '~150-250 tokens per FormRequest',
        ],

        'crud_routes' => [
            'use_instead'  => 'Route::apiResource() / Route::resource() — one line covers all CRUD routes',
            'savings'      => '~100 tokens per resource',
        ],

        'basic_seeder' => [
            'use_instead'  => 'Faker template per domain — predefined realistic data patterns',
            'savings'      => '~200-300 tokens per seeder',
        ],

        'rbac_policy' => [
            'use_instead'  => 'Policy generator — viewAny/view/create/update/delete standard pattern with owner check',
            'savings'      => '~300 tokens per policy',
        ],

        'feature_test_crud' => [
            'use_instead'  => 'Test template from testing_patterns.php — GET/POST/PUT/DELETE assertions with actingAs',
            'savings'      => '~400-600 tokens per test file',
        ],

        // ── Domain Schema / Blueprints (saves 15% of AI budget) ───────────────
        'known_domain_schema' => [
            'use_instead'  => 'Domain pack database schema — CRM, eCommerce, HRM, Hospital, School all pre-defined',
            'savings'      => '~1000-2000 tokens per blueprint request',
            'note'         => 'BlueprintService handles this — AI should NOT redesign known domain tables',
        ],

        'standard_module_blueprint' => [
            'use_instead'  => 'Blueprint Library from mvp_blueprints.php — 11 domains, tables, roles, skip-for-mvp',
            'savings'      => '~500-800 tokens',
        ],

        'repeated_similar_project' => [
            'use_instead'  => 'KbPromptCache — same or similar prompt returns cached blueprint immediately',
            'savings'      => 'Full generation cost eliminated for cached hits',
        ],

        // ── Standard Components (saves 10% of AI budget) ──────────────────────
        'authentication_scaffold' => [
            'use_instead'  => 'Laravel Breeze/Fortify — auth is solved, do not regenerate it',
            'savings'      => '~1000+ tokens',
        ],

        'pagination_logic' => [
            'use_instead'  => 'Always paginate(25) — one-liner, no AI needed',
            'savings'      => 'Avoids AI generating ::all() — the most expensive mistake',
        ],

        'relationship_definitions' => [
            'use_instead'  => 'Relationship pattern generator from entity map — hasMany/belongsTo from FK structure',
            'savings'      => '~100-200 tokens per relationship set',
        ],

        'basic_middleware' => [
            'use_instead'  => 'Template: auth, throttle:60,1, role:admin — standard patterns, no AI needed',
            'savings'      => '~150 tokens',
        ],

        'standard_notification' => [
            'use_instead'  => 'Notification template per channel (mail, database, broadcast) from notification_patterns',
            'savings'      => '~300-400 tokens per notification class',
        ],

        // ── Architecture Decisions (saves AI reasoning tokens) ─────────────────
        'standard_architecture_decision' => [
            'use_instead'  => 'decision_matrix.php — architecture is determined by scale + domain, not AI deliberation',
            'savings'      => '~500-1000 tokens of architectural reasoning',
        ],

        'package_recommendation' => [
            'use_instead'  => 'build_vs_buy.php — package selection is predetermined by category',
            'savings'      => '~300-500 tokens of research',
        ],

        'security_rules' => [
            'use_instead'  => 'security.php always_apply — security rules injected as facts, not AI decisions',
            'savings'      => '~400-600 tokens',
        ],

        // ── Business Rules (saves 5% of AI budget) ────────────────────────────
        'domain_business_rules' => [
            'use_instead'  => 'business_rules.php — accounting double-entry, stock lock, payroll rules are facts',
            'savings'      => '~300-500 tokens of rule reasoning',
        ],

        'mvp_scope_decision' => [
            'use_instead'  => 'mvp_blueprints.php — what is in MVP for each domain is pre-researched',
            'savings'      => '~400-800 tokens of scoping conversation',
        ],

        'country_payment_gateway' => [
            'use_instead'  => 'country_knowledge.php — bKash=BD, Razorpay=IN, Stripe=US — no AI research needed',
            'savings'      => '~200-400 tokens',
        ],
    ],

    // ── Always Use AI For ─────────────────────────────────────────────────────
    'always_use_ai_for' => [
        'novel_business_logic'      => 'Unique rules not matching any existing pattern',
        'cross_module_integration'  => 'Complex interactions between unusual module combinations',
        'complex_debugging'         => 'Multi-layer bugs requiring reasoning across files',
        'unusual_architecture'      => 'Edge cases not covered by decision_matrix.php',
        'natural_language_analysis' => 'Understanding ambiguous or complex user requirements',
        'creative_feature_design'   => 'New product features with no prior pattern',
        'code_review_reasoning'     => 'Identifying subtle security or performance issues',
    ],

    // ── AI Call Routing Decision Tree ─────────────────────────────────────────
    'routing_decision' => [
        'step_1' => 'Is there a cached result for this prompt (KbPromptCache)? → Return cache. STOP.',
        'step_2' => 'Is the domain known (CRM/HRM/eCommerce/Hospital/School)? → Use BlueprintService. STOP.',
        'step_3' => 'Is the task CRUD generation? → Use MetadataCrudGenerator. STOP.',
        'step_4' => 'Is the task architecture selection? → Use decision_matrix.php. STOP.',
        'step_5' => 'Is the task package selection? → Use build_vs_buy.php. STOP.',
        'step_6' => 'Is the task security/patterns? → Inject from KB, AI handles the rest.',
        'step_7' => 'Novel task? → Use AI with full Senior Dev Brief injection.',
    ],

    // ── Cost Reduction Targets ────────────────────────────────────────────────
    'cost_reduction_targets' => [
        '70%' => 'Standard CRUD via MetadataCrudGenerator — zero AI calls',
        '15%' => 'Domain blueprints via BlueprintService + domain packs — zero AI calls',
        '10%' => 'Cached prompts via KbPromptCache — zero AI calls',
        '5%'  => 'Novel/complex logic — unavoidable AI cost, but heavily context-compressed',
        'result' => '95% cost reduction vs naive AI-for-everything approach',
    ],

    // ── Context Compression Rules ─────────────────────────────────────────────
    'context_compression' => [
        'rule_1' => 'Never send full file content if structure summary suffices',
        'rule_2' => 'Send module list + table names instead of full schema for context',
        'rule_3' => 'Inject Senior Dev Brief (~1000 tokens) instead of explaining architecture in prose',
        'rule_4' => 'Use bullet points in system prompt — prose wastes 30-40% tokens',
        'rule_5' => 'Send diff only for modification requests — not full file content',
        'rule_6' => 'Truncate examples to 1 representative sample — AI extrapolates',
        'rule_7' => 'Cache domain knowledge in system prompt, not user messages',
    ],

];
