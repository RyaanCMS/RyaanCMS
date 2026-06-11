<?php

namespace App\Services\AI;

/**
 * Senior Developer Knowledge Base
 *
 * Distils 10–15 years of accumulated software architecture experience into
 * a compact, token-efficient "Senior Dev Brief" that is injected into every
 * AI prompt — making the builder behave like a Senior Dev + Architect + CTO.
 *
 * Knowledge Pyramid (Level 1–6):
 *   L1 Code     → coding standards, patterns, Laravel conventions
 *   L2 Arch     → architecture selection, security, performance
 *   L3 Business → domain workflows, business processes
 *   L4 Product  → UI/UX, monetization, user experience
 *   L5 Decision → decision matrix, when/why to use what
 *   L6 Experience → anti-patterns, failure patterns, estimation
 */
class SeniorDevKnowledgeBase
{
    private array $decisionMatrix;
    private array $security;
    private array $antiPatterns;
    private array $database;
    private array $codingStandards;
    private array $performance;
    private array $apiDesign;
    private array $saasPatterns;
    private array $businessProcesses;
    private array $estimation;
    private array $uiPatterns;
    private array $testing;
    // ── Extended KB (new) ──────────────────────────────────────────────────────
    private array $buildVsBuy;
    private array $countryKnowledge;
    private array $questionIntelligence;
    private array $kpiAnalytics;
    private array $riskAssessment;
    private array $upgradePaths;
    private array $featureDependency;
    private array $businessRules;
    private array $mvpBlueprints;
    private array $aiReplacement;
    private array $mentalModels;
    private array $wisdomPatterns;
    private array $businessOsAssetLayers;
    private array $problemSolvingLibrary;
    private array $intelligenceLibrary;
    private array $autonomousBusinessOperator;

    public function __construct()
    {
        $this->decisionMatrix      = config('kb.decision_matrix', []);
        $this->security            = config('kb.security', []);
        $this->antiPatterns        = config('kb.anti_patterns', []);
        $this->database            = config('kb.database_patterns', []);
        $this->codingStandards     = config('kb.coding_standards', []);
        $this->performance         = config('kb.performance', []);
        $this->apiDesign           = config('kb.api_design', []);
        $this->saasPatterns        = config('kb.saas_patterns', []);
        $this->businessProcesses   = config('kb.business_processes', []);
        $this->estimation          = config('kb.estimation', []);
        $this->uiPatterns          = config('kb.ui_patterns', []);
        $this->testing             = config('kb.testing_patterns', []);
        $this->buildVsBuy          = config('kb.build_vs_buy', []);
        $this->countryKnowledge    = config('kb.country_knowledge', []);
        $this->questionIntelligence= config('kb.question_intelligence', []);
        $this->kpiAnalytics        = config('kb.kpi_analytics', []);
        $this->riskAssessment      = config('kb.risk_assessment', []);
        $this->upgradePaths        = config('kb.upgrade_paths', []);
        $this->featureDependency   = config('kb.feature_dependency', []);
        $this->businessRules       = config('kb.business_rules', []);
        $this->mvpBlueprints       = config('kb.mvp_blueprints', []);
        $this->aiReplacement       = config('kb.ai_replacement', []);
        $this->mentalModels        = config('kb.mental_models', []);
        $this->wisdomPatterns      = config('kb.wisdom_patterns', []);
        $this->businessOsAssetLayers = config('kb.business_os_asset_layers', []);
        $this->problemSolvingLibrary = config('kb.problem_solving_library', []);
        $this->intelligenceLibrary = config('kb.intelligence_library', []);
        $this->autonomousBusinessOperator = config('kb.autonomous_business_operator', []);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build the full Senior Dev Brief (~900–1200 tokens) for injection into AI prompts.
     * Automatically selects the most relevant sections based on the prompt.
     */
    public function buildSeniorBrief(string $prompt, string $appType = ''): string
    {
        $scope = $this->analyzeScope($prompt, $appType);

        $sections = [];

        // ── L5: Decision (always included) ──────────────────────────────────
        $sections[] = $this->buildArchitectureSection($scope);
        $sections[] = $this->buildDatabaseSection($scope);

        // ── L2: Security (always included — most AI builders skip this) ──────
        $sections[] = $this->buildSecuritySection($scope);

        // ── L1: Standards (always included) ──────────────────────────────────
        $sections[] = $this->buildCodingStandardsSection();

        // ── L6: Anti-patterns (always included) ───────────────────────────────
        $sections[] = $this->buildAntiPatternsSection($scope);

        // ── L2: Performance (if scale matters) ────────────────────────────────
        if ($scope['needs_performance']) {
            $sections[] = $this->buildPerformanceSection($scope);
        }

        // ── L5: API Design (if API project) ───────────────────────────────────
        if ($scope['has_api']) {
            $sections[] = $this->buildApiSection();
        }

        // ── L2: SaaS (if SaaS type) ───────────────────────────────────────────
        if ($scope['is_saas']) {
            $sections[] = $this->buildSaasSection($scope);
        }

        // ── L3: Business Process (domain-specific) ────────────────────────────
        $process = $this->getBusinessProcess($scope['domain']);
        if ($process) {
            $sections[] = $process;
        }

        // ── L3: Business Rules (domain invariants) ────────────────────────────
        $businessRules = $this->buildBusinessRulesSection($scope);
        if ($businessRules) {
            $sections[] = $businessRules;
        }

        $businessProblem = $this->buildProblemSolvingSection($prompt);
        if ($businessProblem) {
            $sections[] = $businessProblem;
        }

        $intelligenceSection = $this->buildIntelligenceLibrarySection($prompt);
        if ($intelligenceSection) {
            $sections[] = $intelligenceSection;
        }

        $operatorSection = $this->buildAutonomousOperatorSection($prompt);
        if ($operatorSection) {
            $sections[] = $operatorSection;
        }

        // ── L5: Build vs Buy (key packages) ──────────────────────────────────
        $sections[] = $this->buildBuildVsBuySection($scope);

        // ── L4: KPI Dashboard (if reports) ────────────────────────────────────
        if ($scope['has_reports']) {
            $sections[] = $this->buildKpiSection($scope);
        }

        // ── L6: Risk Assessment ───────────────────────────────────────────────
        $sections[] = $this->buildRiskSection($scope);

        // ── L4: MVP Blueprint ─────────────────────────────────────────────────
        $mvpSection = $this->buildMvpSection($scope);
        if ($mvpSection) {
            $sections[] = $mvpSection;
        }

        // ── L5: Country-specific rules ────────────────────────────────────────
        $countrySection = $this->buildCountrySection($prompt);
        if ($countrySection) {
            $sections[] = $countrySection;
        }

        // ── L6+: AI Routing (reduces hallucination + cost) ───────────────────
        $sections[] = $this->buildAiRoutingSection();

        // ── L6: Estimation ────────────────────────────────────────────────────
        $sections[] = $this->buildEstimationSection($scope);

        $brief = implode("\n\n", array_filter($sections));

        return "=== SENIOR DEVELOPER BRIEF ===\n{$brief}\n=== END BRIEF ===";
    }

    private function buildProblemSolvingSection(string $prompt): ?string
    {
        $matches = $this->matchBusinessProblems($prompt);
        if (empty($matches)) {
            return null;
        }

        $lines = ['BUSINESS PROBLEM SOLVER MODE:'];
        $lines[] = 'The user may describe a business pain instead of asking for an app. Diagnose first, then recommend and implement the smallest complete system that solves it.';

        foreach (array_slice($matches, 0, 3) as $match) {
            $problem = $match['problem'];
            $lines[] = "- Problem: {$match['domain']} / {$match['key']}";
            $lines[] = "  Diagnosis: " . ($problem['diagnosis'] ?? 'Root cause needs workflow, ownership, KPI, and automation clarity.');
            if (!empty($problem['solutions'])) {
                $lines[] = "  Proven fixes: " . implode('; ', array_slice($problem['solutions'], 0, 5));
            }
            if (!empty($problem['modules'])) {
                $lines[] = "  Build modules: " . implode(', ', $problem['modules']);
            }
            if (!empty($problem['kpis'])) {
                $lines[] = "  Measure: " . implode(', ', $problem['kpis']);
            }
        }

        $lines[] = 'When generating files, include dashboards/reports that prove the problem is improving.';

        return implode("\n", $lines);
    }

    private function buildIntelligenceLibrarySection(string $prompt): ?string
    {
        if (!$this->isBusinessProblemPrompt($prompt) && !$this->isCompleteBuildPrompt($prompt)) {
            return null;
        }

        $lines = ['INTELLIGENCE LIBRARY:'];
        $lines[] = 'Apply decision, success, failure, and optimization patterns before generating code.';

        foreach (array_slice($this->intelligenceLibrary['decision_patterns'] ?? [], 0, 3) as $key => $pattern) {
            $lines[] = "- Decision {$key}: " . ($pattern['rule'] ?? '');
        }

        foreach (array_slice($this->intelligenceLibrary['success_patterns'] ?? [], 0, 2) as $key => $pattern) {
            $lines[] = "- Success {$key}: " . ($pattern['pattern'] ?? '');
        }

        foreach (array_slice($this->intelligenceLibrary['failure_patterns'] ?? [], 0, 2) as $key => $pattern) {
            $lines[] = "- Avoid {$key}: " . ($pattern['mistake'] ?? '') . ' Fix: ' . ($pattern['fix'] ?? '');
        }

        $targets = $this->businessOsAssetLayers['long_term_targets'] ?? [];
        if ($targets) {
            $lines[] = 'Strategic asset goal: every build should add reusable blueprint/module/component/problem/decision knowledge.';
        }

        return implode("\n", array_filter($lines));
    }

    private function buildAutonomousOperatorSection(string $prompt): ?string
    {
        if (!$this->isAutonomousOperatorPrompt($prompt)) {
            return null;
        }

        $lines = ['AUTONOMOUS BUSINESS OPERATOR MODE:'];
        $lines[] = 'Treat outcome-level prompts as business operation tasks, not generic software requests.';

        $loop = $this->autonomousBusinessOperator['operating_loop'] ?? [];
        if ($loop) {
            $lines[] = 'Operating loop:';
            foreach ($loop as $step => $description) {
                $lines[] = '- ' . str_replace('_', ' ', (string) $step) . ': ' . $description;
            }
        }

        $contract = $this->autonomousBusinessOperator['response_contract'] ?? [];
        if ($contract) {
            $lines[] = 'Internal response contract: ' . implode(', ', $contract) . '.';
        }

        $memoryTables = array_keys($this->autonomousBusinessOperator['memory_tables'] ?? []);
        if ($memoryTables) {
            $lines[] = 'When building durable systems, include organization/decision/meeting/project/customer/process memory where relevant.';
        }

        $lines[] = 'If live company data is not connected, state assumptions and request the minimum missing evidence. Do not invent data.';
        $lines[] = 'When implementing a recommendation, add analytics events, dashboards, alerts, and review cycles so results can be monitored.';
        $lines[] = 'For digital-twin or pricing/growth simulations, label impact as estimated and include assumptions, risk, and confidence.';
        $lines[] = 'Keep internal graph, marketplace, routing, and intelligence-network mechanics hidden from user-facing copy.';

        return implode("\n", $lines);
    }

    private function matchBusinessProblems(string $prompt): array
    {
        $lower = strtolower($prompt);
        $matches = [];

        foreach ($this->problemSolvingLibrary as $domain => $problems) {
            foreach ($problems as $key => $problem) {
                foreach ($problem['triggers'] ?? [] as $trigger) {
                    if ($trigger !== '' && str_contains($lower, strtolower($trigger))) {
                        $matches[] = [
                            'domain' => (string) $domain,
                            'key' => (string) $key,
                            'problem' => $problem,
                        ];
                        break;
                    }
                }
            }
        }

        return $matches;
    }

    private function isBusinessProblemPrompt(string $prompt): bool
    {
        if (!empty($this->matchBusinessProblems($prompt))) {
            return true;
        }

        $lower = strtolower($prompt);
        $signals = [
            'problem', 'issue', 'low ', 'high ', 'delay', 'mismatch', 'leakage',
            'churn', 'retention', 'return rate', 'stockout', 'late payment',
            'not following', 'absence', 'turnover', 'fake order',
        ];

        foreach ($signals as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    private function isAutonomousOperatorPrompt(string $prompt): bool
    {
        $lower = strtolower($prompt);

        foreach ($this->autonomousBusinessOperator['stages'] ?? [] as $stage) {
            foreach ($stage['trigger_signals'] ?? [] as $signal) {
                if ($signal !== '' && str_contains($lower, strtolower($signal))) {
                    return true;
                }
            }
        }

        $signals = [
            'revenue dropped', 'revenue down', 'sales down', 'profit down',
            'company growing slowly', 'growth slow', 'business not growing',
            'what should i focus', 'focus this month', 'priority this month',
            'detected bottleneck', 'bottleneck', 'estimated impact',
            'increase price', 'optimize pricing', 'simulate', 'digital twin',
            'ceo assistant', 'reduce churn', 'reduce inventory holding',
        ];

        foreach ($signals as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    private function isCompleteBuildPrompt(string $prompt): bool
    {
        $lower = strtolower($prompt);
        foreach (['complete', 'full', 'management system', 'erp', 'crm', 'platform'] as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get only the security checklist (for quick injection).
     */
    public function getSecurityChecklist(): string
    {
        $always = $this->security['always_apply'] ?? [];
        $lines  = [];
        foreach ($always as $key => $rule) {
            $lines[] = "• [{$key}] {$rule}";
        }
        return "SECURITY MUST-APPLY:\n" . implode("\n", array_slice($lines, 0, 8));
    }

    /**
     * Get anti-patterns relevant to a project domain.
     */
    public function getAntiPatternsFor(string $domain): string
    {
        $critical = [
            'fat_controller'        => $this->antiPatterns['laravel']['fat_controller']['fix'] ?? '',
            'n_plus_1_queries'      => $this->antiPatterns['laravel']['n_plus_1_queries']['fix'] ?? '',
            'missing_authorization' => $this->antiPatterns['laravel']['missing_authorization']['fix'] ?? '',
            'missing_transactions'  => $this->antiPatterns['laravel']['missing_transactions']['fix'] ?? '',
            'no_pagination'         => $this->antiPatterns['database']['no_pagination']['fix'] ?? '',
            'exposing_all_fields'   => $this->antiPatterns['api']['exposing_all_fields']['fix'] ?? '',
        ];
        $lines = ["AVOID THESE ANTI-PATTERNS:"];
        foreach ($critical as $name => $fix) {
            if ($fix) $lines[] = "• {$name}: {$fix}";
        }
        return implode("\n", $lines);
    }

    /**
     * Get the recommended architecture for a given scope.
     */
    public function getArchitectureRecommendation(string $prompt, string $appType = ''): array
    {
        $scope = $this->analyzeScope($prompt, $appType);
        return [
            'architecture' => $scope['architecture'],
            'database'     => $scope['db'],
            'cache'        => $scope['cache'],
            'queue'        => $scope['queue'],
            'frontend'     => $scope['frontend'],
            'scale'        => $scope['scale_tier'],
            'rationale'    => $scope['rationale'],
        ];
    }

    /**
     * Get project estimation for a given app type.
     */
    public function getEstimate(string $appType): ?array
    {
        $estimates = $this->estimation['project_estimates'] ?? [];

        $map = [
            'crm'        => 'small_crm',
            'ecommerce'  => 'ecommerce_basic',
            'hrm'        => 'hrm_system',
            'erp'        => 'erp_system',
            'hospital'   => 'hospital_management',
            'saas'       => 'saas_starter',
            'pos'        => 'pos_system',
            'school'     => 'school_management',
            'marketplace'=> 'marketplace',
        ];

        $key = $map[$appType] ?? null;
        return $key ? ($estimates[$key] ?? null) : null;
    }

    /**
     * Get the business process flow for a domain.
     */
    public function getBusinessProcessFlow(string $domain): ?array
    {
        return $this->businessProcesses[$domain] ?? null;
    }

    /**
     * Get smart clarifying questions for a given domain before building.
     * Checks local_question_packs DB first; falls back to config file.
     */
    public function getSmartQuestions(string $domain): array
    {
        // DB-backed pack (synced from central registry or locally seeded)
        try {
            $pack = \App\Models\LocalQuestionPack::bestMatch($domain);
            if ($pack) {
                $questions = $pack->questions();
                usort($questions, fn($a, $b) => ($b['critical'] ?? false) <=> ($a['critical'] ?? false));
                return array_slice($questions, 0, 7);
            }
        } catch (\Throwable) {
            // DB unavailable during install — fall through to config
        }

        $domainQs  = $this->questionIntelligence['by_domain'][$domain] ?? [];
        $generalQs = $this->questionIntelligence['by_domain']['general'] ?? [];
        $all       = array_merge($domainQs, $generalQs);

        usort($all, fn($a, $b) => ($b['critical'] ?? false) <=> ($a['critical'] ?? false));

        return array_slice($all, 0, 7);
    }

    /**
     * Get build vs buy recommendation for a specific need.
     */
    public function getBuildVsBuyAdvice(string $need): ?array
    {
        return $this->buildVsBuy['always_use_package'][$need] ?? null;
    }

    /**
     * Detect country from prompt text.
     */
    public function detectCountry(string $prompt): ?string
    {
        $lp      = strtolower($prompt);
        $hints   = $this->countryKnowledge['detection_hints']['keywords'] ?? [];

        foreach ($hints as $country => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lp, $kw)) return $country;
            }
        }

        return null;
    }

    /**
     * Get country-specific context (payments, UX, legal) for a given country code.
     */
    public function getCountryContext(string $countryCode): ?array
    {
        return $this->countryKnowledge[strtoupper($countryCode)] ?? null;
    }

    /**
     * Get KPI dashboard suggestions for a domain.
     */
    public function getKpiSuggestions(string $domain): ?array
    {
        return $this->kpiAnalytics[$domain] ?? null;
    }

    /**
     * Get critical risk flags for a scope.
     */
    public function getCriticalRisks(): array
    {
        $risks    = $this->riskAssessment['technical'] ?? [];
        $critical = [];
        foreach ($risks as $key => $risk) {
            if (($risk['risk'] ?? 0) >= 5) {
                $critical[$key] = $risk;
            }
        }
        return $critical;
    }

    /**
     * Get MVP blueprint for a domain.
     */
    public function getMvpBlueprint(string $domain): ?array
    {
        return $this->mvpBlueprints[$domain] ?? null;
    }

    /**
     * Get the required features for a project type.
     */
    public function getRequiredFeatures(string $domain): ?array
    {
        return $this->featureDependency['project_required_features'][$domain] ?? null;
    }

    /**
     * Get business rules / invariants for a domain.
     */
    public function getBusinessRules(string $domain): ?array
    {
        return $this->businessRules[$domain] ?? null;
    }

    /**
     * Get the upgrade path recommendation for an architecture evolution.
     */
    public function getUpgradePath(string $category, string $path): ?array
    {
        return $this->upgradePaths[$category][$path] ?? null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SCOPE ANALYSIS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Analyse the project prompt to determine architectural scope.
     * This is the "decision engine" — it reads the intent and recommends the stack.
     */
    public function analyzeScope(string $prompt, string $appType = ''): array
    {
        $lp = strtolower($prompt);

        // ── Domain detection ──────────────────────────────────────────────────
        $domain = $this->detectDomain($lp, $appType);

        // ── Feature detection ─────────────────────────────────────────────────
        $hasApi         = $this->contains($lp, ['api', 'rest', 'mobile app', 'flutter', 'react native', 'android', 'ios', 'app']);
        $isSaas         = $this->contains($lp, ['saas', 'multi-tenant', 'subscription', 'billing', 'plan', 'workspace', 'tenant']) || $domain === 'saas';
        $hasRealtime    = $this->contains($lp, ['real-time', 'realtime', 'chat', 'notification', 'websocket', 'live', 'broadcast']);
        $hasFinancial   = $this->contains($lp, ['payment', 'invoice', 'accounting', 'payroll', 'billing', 'finance', 'ledger']);
        $hasMultiRole   = $this->contains($lp, ['admin', 'manager', 'role', 'permission', 'staff', 'hr', 'doctor', 'teacher']);
        $hasSearch      = $this->contains($lp, ['search', 'filter', 'fulltext', 'full-text', 'elasticsearch', 'meilisearch']);
        $hasReports     = $this->contains($lp, ['report', 'analytics', 'dashboard', 'statistics', 'chart', 'export']);
        $hasIntegration = $this->contains($lp, ['bkash', 'nagad', 'stripe', 'paypal', 'sms', 'email', 'webhook', 'integration', 'gateway']);

        // ── Scale estimation ──────────────────────────────────────────────────
        $scaleTier = 'medium';
        if ($this->contains($lp, ['startup', 'mvp', 'simple', 'basic', 'small'])) $scaleTier = 'small';
        if ($this->contains($lp, ['enterprise', 'large', 'scale', 'million', 'national', 'corporate'])) $scaleTier = 'large';
        if ($isSaas && $domain !== 'simple') $scaleTier = 'medium';

        $scaleData   = $this->decisionMatrix['scale'][$scaleTier]   ?? $this->decisionMatrix['scale']['medium'];

        // ── Architecture recommendation ───────────────────────────────────────
        $architecture = $scaleData['arch'];
        if ($isSaas && $domain === 'erp')  $architecture = 'modular_monolith';
        if ($hasRealtime && !$isSaas)       $architecture = 'event_driven';
        if ($hasApi && !$isSaas)            $architecture = 'api_spa';

        // ── DB recommendation ─────────────────────────────────────────────────
        $db = $scaleData['db'];
        if ($isSaas || $hasFinancial || $hasSearch) $db = 'PostgreSQL';
        if ($scaleTier === 'small' && !$isSaas)     $db = 'MySQL 8';

        // ── Frontend recommendation ───────────────────────────────────────────
        $frontend = 'Blade + Livewire';
        if ($hasApi)                          $frontend = 'Laravel API + React/Next.js';
        if ($hasRealtime)                     $frontend = 'Blade + Alpine.js + Livewire (with Reverb)';
        if ($isSaas && !$hasApi)              $frontend = 'Blade + Livewire + Alpine.js';

        // ── Rationale ─────────────────────────────────────────────────────────
        $rationale = $this->buildRationale($domain, $architecture, $db, $scaleTier, $isSaas, $hasApi);

        return [
            'domain'            => $domain,
            'scale_tier'        => $scaleTier,
            'architecture'      => $architecture,
            'db'                => $db,
            'cache'             => $scaleData['cache'],
            'queue'             => $scaleData['queue'],
            'frontend'          => $frontend,
            'is_saas'           => $isSaas,
            'has_api'           => $hasApi,
            'has_realtime'      => $hasRealtime,
            'has_financial'     => $hasFinancial,
            'has_multi_role'    => $hasMultiRole,
            'has_search'        => $hasSearch,
            'has_reports'       => $hasReports,
            'has_integration'   => $hasIntegration,
            'needs_performance' => $scaleTier !== 'small' || $isSaas || $hasReports,
            'rationale'         => $rationale,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SECTION BUILDERS
    // ──────────────────────────────────────────────────────────────────────────

    private function buildArchitectureSection(array $scope): string
    {
        $dm   = $this->decisionMatrix;
        $arch = $scope['architecture'];
        $archData = $dm['architecture'][$arch] ?? [];

        $lines = [
            "ARCHITECTURE DECISION:",
            "  Pattern   : " . strtoupper($arch),
            "  Stack     : " . ($archData['stack'] ?? $scope['frontend']),
            "  Database  : " . $scope['db'],
            "  Cache     : " . $scope['cache'],
            "  Queue     : " . $scope['queue'],
            "  Frontend  : " . $scope['frontend'],
        ];

        if ($scope['is_saas']) {
            $mt = $dm['multitenancy']['default_recommendation'] ?? '';
            $lines[] = "  Tenancy   : {$mt}";
        }

        if ($scope['has_search']) {
            $searchRule = '10K–500K records → Laravel Scout + Meilisearch (self-hosted)';
            $lines[] = "  Search    : {$searchRule}";
        }

        $lines[] = "  RATIONALE : " . $scope['rationale'];

        return implode("\n", $lines);
    }

    private function buildDatabaseSection(array $scope): string
    {
        $rules = $this->database['design_rules'] ?? [];
        $indexing = $this->database['indexing'] ?? [];

        $lines = [
            "DATABASE DESIGN RULES (apply without exception):",
            "  • " . ($rules['money']      ?? 'Store money as DECIMAL(15,2) — never FLOAT'),
            "  • " . ($rules['primary_keys']?? 'BIGINT auto-increment default; UUID for public APIs'),
            "  • " . ($rules['foreign_keys']?? 'ALWAYS define FK constraints with cascade rules'),
            "  • " . ($rules['indexes']    ?? 'Index all FK, WHERE, ORDER BY columns'),
            "  • " . ($rules['timestamps'] ?? 'created_at + updated_at on ALL tables'),
            "  • " . ($rules['soft_delete']?? 'SoftDeletes on users, orders, invoices, products'),
            "  • " . ($indexing['rule_5']  ?? 'Full-text index for searchable text columns'),
        ];

        if ($scope['is_saas']) {
            $mt = $this->database['multi_tenant'] ?? [];
            $lines[] = "  • MULTI-TENANT: " . ($mt['every_table_needs'] ?? 'tenant_id on all tables + global scope');
        }

        if ($scope['has_financial']) {
            $fin = $this->database['financial'] ?? [];
            $lines[] = "  • FINANCIAL: " . ($fin['immutable_records'] ?? 'Financial records NEVER updated — void+reissue');
            $lines[] = "  • FINANCIAL: " . ($fin['double_entry']      ?? 'Double-entry pattern for accounting modules');
        }

        return implode("\n", $lines);
    }

    private function buildSecuritySection(array $scope): string
    {
        $always = $this->security['always_apply'] ?? [];
        $owasp  = $this->security['owasp_checklist'] ?? [];

        $topRules = [
            'authorization'    => $always['authorization']    ?? '',
            'sql_injection'    => $always['sql_injection']    ?? '',
            'xss'              => $always['xss']              ?? '',
            'mass_assignment'  => $always['mass_assignment']  ?? '',
            'input_validation' => $always['input_validation'] ?? '',
            'file_upload'      => $always['file_upload']      ?? '',
            'env_secrets'      => $always['env_secrets']      ?? '',
            'password_hash'    => $always['password_hash']    ?? '',
        ];

        $lines = ["SECURITY REQUIREMENTS (non-negotiable):"];
        foreach ($topRules as $k => $v) {
            if ($v) $lines[] = "  • {$k}: {$v}";
        }

        if ($scope['has_api']) {
            $api = $this->security['api'] ?? [];
            $lines[] = "  • rate_limiting: " . ($api['rate_limiting'] ?? '60/min public, 600/min authed');
            $lines[] = "  • api_versioning: " . ($api['versioning']   ?? '/api/v1/ prefix required');
        }

        if ($scope['has_financial']) {
            $dp = $this->security['data_protection'] ?? [];
            $lines[] = "  • audit_log: " . ($dp['audit_log'] ?? 'Log who did what, when, from which IP');
        }

        return implode("\n", $lines);
    }

    private function buildCodingStandardsSection(): string
    {
        $solid   = $this->codingStandards['solid']    ?? [];
        $naming  = $this->codingStandards['laravel_conventions']['naming'] ?? [];
        $service = $this->codingStandards['laravel_conventions']['service_layer'] ?? [];
        $eloquent= $this->codingStandards['laravel_conventions']['eloquent_best_practices'] ?? [];

        return implode("\n", [
            "CODING STANDARDS (Senior Dev Level):",
            "  S → " . ($solid['S_single_responsibility']['laravel'] ?? 'Thin controllers, logic in services'),
            "  O → " . ($solid['O_open_closed']['laravel']           ?? 'Interface + DI for swappable implementations'),
            "  D → " . ($solid['D_dependency_inversion']['laravel']  ?? 'Inject interfaces, not concrete classes'),
            "  naming: controllers=PascalCase singular, tables=snake_case plural, routes=kebab-case",
            "  service: " . ($service['pattern'] ?? 'thin controller → service → return value/exception'),
            "  eloquent: " . ($eloquent['eager_loading'] ?? 'with() always — NEVER lazy in loops'),
            "  casts: " . ($eloquent['casts']            ?? 'Cast JSON→array, booleans, dates in \$casts'),
        ]);
    }

    private function buildAntiPatternsSection(array $scope): string
    {
        $la = $this->antiPatterns['laravel'] ?? [];
        $db = $this->antiPatterns['database'] ?? [];

        $critical = [
            'fat_controller'        => $la['fat_controller']['fix']        ?? '',
            'n_plus_1'              => $la['n_plus_1_queries']['fix']      ?? '',
            'missing_authorization' => $la['missing_authorization']['fix'] ?? '',
            'missing_transactions'  => $la['missing_transactions']['fix']  ?? '',
            'hardcoded_credentials' => $la['hardcoded_credentials']['fix'] ?? '',
            'no_pagination'         => $db['no_pagination']['fix']         ?? '',
        ];

        $lines = ["ANTI-PATTERNS — NEVER GENERATE:"];
        foreach ($critical as $name => $fix) {
            if ($fix) $lines[] = "  ✗ {$name}: {$fix}";
        }
        return implode("\n", $lines);
    }

    private function buildPerformanceSection(array $scope): string
    {
        $always = $this->performance['always_apply'] ?? [];
        $queue  = $this->performance['queues']       ?? [];
        $cache  = $this->performance['caching']      ?? [];

        $lines = [
            "PERFORMANCE REQUIREMENTS:",
            "  • " . ($always['eager_loading']   ?? 'ALWAYS with() — never lazy-load in loops'),
            "  • " . ($always['pagination']       ?? 'paginate(25) everywhere — never ::all()'),
            "  • " . ($always['cache_config']     ?? 'Cache expensive reads with Redis'),
            "  • " . ($always['queue_heavy_ops']  ?? 'Email, PDF, CSV export → always queue Job'),
            "  • Queue driver: " . ($queue['driver'] ?? 'Redis for production'),
        ];

        if ($scope['has_reports']) {
            $lines[] = "  • Reports: Cache::remember for aggregated report data (TTL 1hr+)";
        }

        return implode("\n", $lines);
    }

    private function buildApiSection(): string
    {
        $rest  = $this->apiDesign['rest_conventions'] ?? [];
        $resp  = $this->apiDesign['response_format']  ?? [];
        $rate  = $this->apiDesign['rate_limiting']     ?? [];
        $res   = $this->apiDesign['api_resources']     ?? [];

        return implode("\n", [
            "API DESIGN STANDARDS:",
            "  • Always use /api/v1/ prefix (versioning required)",
            "  • Response format: {success, message, data, errors} — consistent always",
            "  • HTTP status: 200 GET/PUT, 201 POST, 204 DELETE, 422 validation, 403 forbidden",
            "  • " . ($res['rule']          ?? 'Always use API Resources — never return raw models'),
            "  • rate_limit: " . ($rate['auth_routes']   ?? '600/min per user, 30/min public'),
            "  • pagination: cursorPaginate() for feeds, paginate() for admin tables",
            "  • Always separate: GET (query) from POST/PUT (command) — CQRS-lite",
        ]);
    }

    private function buildSaasSection(array $scope): string
    {
        $fund  = $this->saasPatterns['fundamentals']       ?? [];
        $bill  = $this->saasPatterns['billing']            ?? [];
        $iso   = $this->saasPatterns['isolation_checklist']?? [];
        $limit = $this->saasPatterns['feature_limits']     ?? [];

        return implode("\n", [
            "SAAS ARCHITECTURE:",
            "  tenancy   : " . ($fund['tenancy_model']    ?? 'Row-level: tenant_id on all tables'),
            "  package   : " . ($fund['package']          ?? 'stancl/tenancy or spatie/laravel-multitenancy'),
            "  billing   : " . ($bill['package']          ?? 'laravel/cashier + Stripe'),
            "  isolation : BelongsToTenant global scope + tenant_id FK on all tables",
            "  cache_keys: Prefix all cache keys with tenant_id to prevent bleed",
            "  jobs      : Store tenant_id in job payload — restore context in handle()",
            "  limits    : " . ($limit['implementation']  ?? 'Store limits in plans.limits JSON column'),
            "  onboarding: Register → Verify → Setup workspace → Invite team → First value",
        ]);
    }

    private function buildEstimationSection(array $scope): string
    {
        $estimate  = $this->getEstimate($scope['domain']);
        $phased    = $this->estimation['phased_delivery'] ?? [];

        $lines = ["PROJECT ESTIMATION:"];

        if ($estimate) {
            $lines[] = "  Complexity: " . ($estimate['complexity'] ?? 'Medium');
            $lines[] = "  Modules   : ~" . ($estimate['modules'] ?? '8–12');
            $lines[] = "  Timeline  : " . ($estimate['timeline'] ?? 'depends on scope');
            if (!empty($estimate['tech_stack'])) {
                $lines[] = "  Stack     : " . $estimate['tech_stack'];
            }
        }

        $lines[] = "  Delivery  : " . ($phased['rule'] ?? 'For > 3 month projects, recommend phased delivery');
        $lines[] = "  Phase 1   : Core entities + auth + RBAC — deploy to staging first";

        return implode("\n", $lines);
    }

    private function getBusinessProcess(string $domain): ?string
    {
        $map = [
            'ecommerce'  => 'ecommerce_order',
            'crm'        => 'crm_sales_pipeline',
            'hrm'        => 'hrm_workflows',
            'hospital'   => 'hospital',
            'inventory'  => 'inventory',
            'accounting' => 'accounting',
        ];

        $key     = $map[$domain] ?? null;
        $process = $key ? ($this->businessProcesses[$key] ?? null) : null;
        if (!$process) return null;

        $lines = ["BUSINESS PROCESS (for domain: {$domain}):"];

        if (isset($process['flow'])) {
            foreach ($process['flow'] as $step => $desc) {
                $lines[] = "  {$step}: {$desc}";
            }
        }
        if (isset($process['states'])) {
            $lines[] = "  States: " . implode(' → ', $process['states']);
        }
        if (isset($process['events'])) {
            $lines[] = "  Events: " . implode(', ', $process['events']);
        }

        return implode("\n", $lines);
    }

    private function buildBusinessRulesSection(array $scope): ?string
    {
        $domain = $scope['domain'];
        $rules  = $this->businessRules[$domain] ?? null;
        if (!$rules) return null;

        $lines = ["BUSINESS RULES ({$domain}) — must never be violated:"];
        foreach (array_slice($rules, 0, 6) as $key => $rule) {
            $lines[] = "  ✓ {$key}: {$rule}";
        }
        return implode("\n", $lines);
    }

    private function buildBuildVsBuySection(array $scope): string
    {
        $packages = $this->buildVsBuy['always_use_package'] ?? [];

        $relevant = [];
        if ($scope['has_multi_role'])  $relevant['role_permissions'] = $packages['role_permissions'] ?? null;
        if ($scope['has_financial'])   $relevant['pdf_generation']   = $packages['pdf_generation']   ?? null;
        if ($scope['has_reports'])     $relevant['excel_export_import']= $packages['excel_export_import'] ?? null;
        if ($scope['has_search'])      $relevant['full_text_search']  = $packages['full_text_search']  ?? null;
        if ($scope['is_saas'])         $relevant['multitenancy']      = $packages['multitenancy']      ?? null;
        if ($scope['has_integration']) $relevant['email']             = $packages['email']             ?? null;

        // Always include media + activity log
        $relevant['media_files']       = $packages['media_files']       ?? null;
        $relevant['activity_audit_log']= $packages['activity_audit_log'] ?? null;

        $lines = ["USE THESE PACKAGES (don't build from scratch):"];
        foreach (array_filter($relevant) as $key => $pkg) {
            if ($pkg) {
                $lines[] = "  • {$key}: use " . ($pkg['use'] ?? '') . ' — ' . ($pkg['why'] ?? '');
            }
        }
        return implode("\n", $lines);
    }

    private function buildKpiSection(array $scope): string
    {
        $domain = $scope['domain'];
        $kpis   = $this->kpiAnalytics[$domain] ?? null;
        if (!$kpis) return '';

        $primary = $kpis['primary_kpis']    ?? [];
        $widgets = $kpis['dashboard_widgets'] ?? [];

        $lines = ["KPI DASHBOARD ({$domain}):"];
        foreach (array_slice(array_keys($primary), 0, 4) as $k) {
            $lines[] = "  • {$k}: {$primary[$k]}";
        }
        if ($widgets) {
            $lines[] = "  Widgets: " . implode(' | ', array_slice($widgets, 0, 4));
        }
        return implode("\n", $lines);
    }

    private function buildRiskSection(array $scope): string
    {
        $technical = $this->riskAssessment['technical'] ?? [];

        // Always flag the critical ones regardless of scope
        $criticalKeys = [
            'no_pagination', 'missing_transactions', 'no_authorization_checks',
            'storing_secrets_in_code', 'synchronous_payment_confirmation',
        ];

        $lines = ["RISK FLAGS — address these proactively:"];
        foreach ($criticalKeys as $key) {
            if (isset($technical[$key])) {
                $r = $technical[$key];
                $lines[] = "  ⚠ [{$key}] Risk:{$r['risk']}/5 — {$r['mitigation']}";
            }
        }

        // Domain-specific additions
        if ($scope['has_financial']) {
            $lines[] = "  ⚠ [financial] Use DECIMAL(15,2) for money. Wrap payment flows in transactions.";
        }
        if ($scope['is_saas']) {
            $lines[] = "  ⚠ [saas] Enforce plan limits server-side. Tenant data isolation is non-negotiable.";
        }

        return implode("\n", $lines);
    }

    private function buildMvpSection(array $scope): ?string
    {
        $domain    = $scope['domain'];
        $blueprint = $this->mvpBlueprints[$domain] ?? null;
        if (!$blueprint || $domain === 'general') return null;

        $modules = $blueprint['mvp_modules'] ?? [];
        $skip    = $blueprint['skip_for_mvp'] ?? [];

        $lines = ["MVP SCOPE ({$domain}):"];
        if ($modules) {
            $lines[] = "  Build first: " . implode(', ', array_slice($modules, 0, 5));
        }
        if ($skip) {
            $lines[] = "  Skip for MVP: " . implode(', ', array_slice($skip, 0, 3));
        }
        if (!empty($blueprint['first_value'])) {
            $lines[] = "  First value: " . $blueprint['first_value'];
        }
        return implode("\n", $lines);
    }

    private function buildAiRoutingSection(): string
    {
        $never = $this->aiReplacement['never_use_ai_for'] ?? [];
        $routing = $this->aiReplacement['routing_decision'] ?? [];

        $lines = ["AI ROUTING (do NOT use AI for these — use generators/caches/rules):"];
        $neverKeys = ['standard_crud_controller', 'database_migrations', 'known_domain_schema',
                      'standard_module_blueprint', 'authentication_scaffold', 'api_resources'];
        foreach ($neverKeys as $key) {
            if (isset($never[$key])) {
                $lines[] = "  ✗ {$key}: → " . $never[$key]['use_instead'];
            }
        }
        if (!empty($routing['step_1'])) {
            $lines[] = "  ROUTING: " . $routing['step_1'];
        }
        return implode("\n", $lines);
    }

    private function buildCountrySection(string $prompt): ?string
    {
        $country     = $this->detectCountry($prompt);
        if (!$country) return null;

        $ctx = $this->countryKnowledge[$country] ?? null;
        if (!$ctx) return null;

        $lines = ["COUNTRY CONTEXT ({$ctx['name']}):"];

        if (!empty($ctx['payment_gateways'])) {
            $gateways = $ctx['payment_gateways'];
            if (is_array($gateways)) {
                $top = is_string($gateways['primary'] ?? null) ? $gateways['primary'] : json_encode($gateways['primary'] ?? []);
                $lines[] = "  Payments: " . $top;
            }
        }

        if (!empty($ctx['legal_compliance'])) {
            $legal = $ctx['legal_compliance'];
            $first = array_key_first($legal);
            if ($first) $lines[] = "  Legal: [{$first}] " . $legal[$first];
        }

        if (!empty($ctx['ux_culture'])) {
            $ux = $ctx['ux_culture'];
            if (!empty($ux['mobile_first'])) $lines[] = "  UX: mobile-first mandatory";
            if (!empty($ux['rtl_support']) || !empty($ux['rtl'])) $lines[] = "  UX: RTL (Arabic) support required";
        }

        return implode("\n", $lines);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function detectDomain(string $lp, string $appType = ''): string
    {
        if ($appType && $appType !== 'unknown') return $appType;

        $domains = [
            'ecommerce'  => ['shop', 'store', 'ecommerce', 'product', 'cart', 'order', 'checkout', 'inventory'],
            'crm'        => ['crm', 'customer', 'lead', 'deal', 'pipeline', 'sales', 'contact'],
            'hrm'        => ['hrm', 'hr', 'human resource', 'employee', 'payroll', 'attendance', 'leave', 'recruitment'],
            'erp'        => ['erp', 'enterprise', 'accounting', 'procurement', 'manufacturing', 'supply chain'],
            'hospital'   => ['hospital', 'clinic', 'patient', 'doctor', 'medical', 'appointment', 'prescription'],
            'school'     => ['school', 'student', 'teacher', 'class', 'exam', 'grade', 'fee', 'education'],
            'restaurant' => ['restaurant', 'food', 'menu', 'kitchen', 'reservation', 'table', 'waiter'],
            'inventory'  => ['warehouse', 'stock', 'inventory', 'barcode', 'sku', 'shelf', 'receiving'],
            'saas'       => ['saas', 'multi-tenant', 'subscription', 'workspace', 'billing', 'plan'],
            'accounting' => ['accounting', 'ledger', 'journal', 'balance sheet', 'profit loss', 'payable', 'receivable'],
            'pos'        => ['pos', 'point of sale', 'cashier', 'receipt', 'till', 'retail'],
            'marketplace'=> ['marketplace', 'vendor', 'multi-vendor', 'seller', 'commission', 'platform'],
            'real_estate'=> ['real estate', 'property', 'house', 'apartment', 'rent', 'lease'],
            'hotel'      => ['hotel', 'resort', 'booking', 'room', 'check-in', 'check-out', 'hospitality'],
        ];

        foreach ($domains as $domain => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lp, $kw)) return $domain;
            }
        }

        return 'general';
    }

    private function buildRationale(string $domain, string $arch, string $db, string $scale, bool $isSaas, bool $hasApi): string
    {
        $parts = [];
        $parts[] = ucfirst($domain) . " ({$scale} scale)";
        $parts[] = $isSaas  ? "SaaS multi-tenant" : null;
        $parts[] = $hasApi  ? "API-first"         : null;
        $parts   = array_filter($parts);

        $recommendation = "→ {$arch} + {$db} is optimal";

        return implode(', ', $parts) . ' ' . $recommendation;
    }

    private function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) return true;
        }
        return false;
    }
}
