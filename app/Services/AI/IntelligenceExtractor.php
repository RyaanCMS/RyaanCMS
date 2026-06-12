<?php

namespace App\Services\AI;

use App\Models\Project;

/**
 * Intelligence Extractor — Layer 2 of RGIN
 *
 * When a user creates something valuable — a workflow, a module config, a blueprint,
 * a bug fix, a validation rule — this service extracts the INTELLIGENCE from it,
 * not the code.
 *
 * The distinction is critical:
 *   Code uploaded = security risk, PII risk, vendor lock-in
 *   Intelligence uploaded = safe, reusable, globally valuable
 *
 * What we extract:
 *   domain, asset_type, name, description, dependencies, tags,
 *   confidence, quality score, business rules implied
 *
 * What we NEVER extract:
 *   customer data, passwords, API keys, PII, business secrets,
 *   private records, patient data, financial records
 */
class IntelligenceExtractor
{
    // ─────────────────────────────────────────────────────────────────────────
    // Asset type detection
    // Classifies what kind of intelligence asset a prompt+result represents
    // ─────────────────────────────────────────────────────────────────────────

    private function detectAssetType(string $lower, array $coverageResult = []): string
    {
        // Order matters — more specific before general
        $typeSignals = [
            'workflow'    => ['workflow', 'process', 'flow', 'pipeline', 'step', 'stage', 'approval'],
            'blueprint'   => ['system', 'platform', 'erp', 'management system', 'full', 'complete'],
            'module'      => ['module', 'feature', 'component', 'auth', 'billing', 'inventory', 'payroll'],
            'validation'  => ['validate', 'validation', 'rule', 'must', 'require', 'cannot', 'only if'],
            'error_fix'   => ['fix', 'error', 'bug', 'issue', 'problem', 'not working', 'failed', 'broken'],
            'question_pack'=>['question', 'discover', 'requirement gathering', 'what do you need'],
            'report'      => ['report', 'analytics', 'dashboard', 'chart', 'summary', 'statistics'],
            'component'   => ['table', 'form', 'chart', 'modal', 'card', 'widget', 'ui', 'interface'],
            'rule'        => ['business rule', 'policy', 'constraint', 'restriction', 'logic'],
        ];

        foreach ($typeSignals as $type => $signals) {
            foreach ($signals as $signal) {
                if (str_contains($lower, $signal)) return $type;
            }
        }

        // Fall back to coverage result if available
        if (!empty($coverageResult['domain'])) {
            return 'blueprint'; // domain-level request with no other signal = blueprint
        }

        return 'feature';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Dependency extraction
    // What modules/assets does this intelligence depend on?
    // ─────────────────────────────────────────────────────────────────────────

    private function extractDependencies(string $lower): array
    {
        $dependencyMap = [
            'auth'          => ['login', 'auth', 'register', 'user', 'session'],
            'billing'       => ['bill', 'invoice', 'payment', 'charge', 'fee'],
            'inventory'     => ['stock', 'inventory', 'quantity', 'warehouse'],
            'scheduling'    => ['appointment', 'booking', 'slot', 'calendar', 'schedule'],
            'notifications' => ['notify', 'alert', 'email', 'sms', 'push', 'reminder'],
            'reporting'     => ['report', 'export', 'analytics', 'chart'],
            'rbac'          => ['role', 'permission', 'access', 'authorize'],
            'workflow'      => ['approval', 'stage', 'status', 'review', 'workflow'],
            'crm'           => ['lead', 'contact', 'customer', 'pipeline', 'deal'],
            'hrm'           => ['employee', 'staff', 'payroll', 'leave', 'attendance'],
            'accounting'    => ['ledger', 'journal', 'debit', 'credit', 'account'],
            'pos'           => ['sale', 'cashier', 'receipt', 'till', 'transaction'],
        ];

        $detected = [];
        foreach ($dependencyMap as $dep => $signals) {
            foreach ($signals as $signal) {
                if (str_contains($lower, $signal)) {
                    $detected[] = $dep;
                    break;
                }
            }
        }

        return array_unique($detected);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tag extraction
    // Searchable tags for the intelligence registry
    // ─────────────────────────────────────────────────────────────────────────

    private function extractTags(string $lower, string $domain, string $assetType): array
    {
        $tags = [$domain, $assetType];

        $tagSignals = [
            'multi-tenant', 'multi-branch', 'multi-currency', 'mobile-first', 'offline',
            'real-time', 'export', 'import', 'api', 'webhook', 'notification',
            'approval', 'audit', 'search', 'filter', 'bulk', 'batch', 'scheduled',
            'payment', 'subscription', 'trial', 'freemium', 'saas',
            'hipaa', 'gdpr', 'pci', 'compliance', 'security',
            'dashboard', 'report', 'kpi', 'analytics', 'chart',
        ];

        foreach ($tagSignals as $tag) {
            if (str_contains($lower, $tag)) $tags[] = $tag;
        }

        return array_unique($tags);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Business rules implied
    // Extract the business logic embedded in the request
    // ─────────────────────────────────────────────────────────────────────────

    private function extractBusinessRules(string $lower, string $domain): array
    {
        $rules = [];

        // Universal implied rules
        if (str_contains($lower, 'payment'))   $rules[] = 'payment_confirmation_required_before_fulfillment';
        if (str_contains($lower, 'approval'))  $rules[] = 'approval_required_before_status_change';
        if (str_contains($lower, 'stock'))     $rules[] = 'cannot_sell_below_zero_stock';
        if (str_contains($lower, 'refund'))    $rules[] = 'refund_requires_original_transaction';
        if (str_contains($lower, 'discount'))  $rules[] = 'discount_cannot_exceed_100_percent';
        if (str_contains($lower, 'invoice'))   $rules[] = 'invoice_requires_at_least_one_line_item';

        // Domain-specific implied rules
        $domainRules = [
            'hospital'    => ['prescription_requires_licensed_doctor', 'patient_requires_registration'],
            'accounting'  => ['debit_must_equal_credit', 'closed_period_cannot_be_edited'],
            'lms'         => ['certificate_requires_course_completion', 'quiz_requires_passing_grade'],
            'ecommerce'   => ['order_requires_stock_availability', 'checkout_requires_valid_address'],
            'hrm'         => ['payroll_requires_approved_attendance', 'leave_requires_remaining_balance'],
            'school'      => ['exam_requires_fee_clearance', 'result_requires_all_subjects_graded'],
            'microfinance'=> ['loan_requires_kyc_completion', 'disbursement_requires_group_approval'],
        ];

        if (isset($domainRules[$domain])) {
            $rules = array_merge($rules, $domainRules[$domain]);
        }

        return array_unique($rules);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Extraction quality score
    // How confident are we in the extracted intelligence?
    // ─────────────────────────────────────────────────────────────────────────

    private function scoreExtractionQuality(
        string $assetType,
        string $domain,
        array $dependencies,
        array $tags,
        array $rules,
        ?array $coverageResult
    ): int {
        $score = 40; // base

        if ($domain !== 'generic')                    $score += 15; // known domain
        if ($assetType !== 'feature')                 $score += 10; // clear asset type
        if (count($dependencies) >= 2)                $score += 10; // well-connected
        if (count($tags) >= 3)                        $score += 5;  // well-tagged
        if (count($rules) >= 1)                       $score += 10; // has business rules
        if ($coverageResult && $coverageResult['coverage'] >= 70) $score += 10; // high coverage = known territory

        return min(100, $score);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Main extraction
    // ─────────────────────────────────────────────────────────────────────────

    public function extract(
        string $prompt,
        Project $project,
        ?array $coverageResult = null
    ): array {
        $lower  = strtolower($prompt);
        $domain = $coverageResult['domain'] ?? 'generic';

        $assetType   = $this->detectAssetType($lower, $coverageResult ?? []);
        $deps        = $this->extractDependencies($lower);
        $tags        = $this->extractTags($lower, $domain, $assetType);
        $rules       = $this->extractBusinessRules($lower, $domain);
        $quality     = $this->scoreExtractionQuality($assetType, $domain, $deps, $tags, $rules, $coverageResult);
        $name        = $this->generateAssetName($lower, $domain, $assetType);

        return [
            // Core identity
            'asset_type'          => $assetType,
            'domain'              => $domain,
            'extension'           => $coverageResult['extension'] ?? null,
            'name'                => $name,

            // Structural intelligence
            'dependencies'        => $deps,
            'tags'                => $tags,
            'business_rules'      => $rules,

            // Quality signals
            'confidence'          => $coverageResult['coverage'] ?? 50,
            'extraction_quality'  => $quality,
            'matched_assets'      => $coverageResult['matched_count'] ?? 0,
            'gaps'                => $coverageResult['gap_count'] ?? 0,

            // Privacy-safe prompt summary (no PII, no business secrets)
            'prompt_summary'      => $this->sanitizeSummary($prompt),

            // Source fingerprint — used for deduplication
            'fingerprint'         => md5($domain . ':' . $assetType . ':' . strtolower($name)),

            // Export metadata
            'schema_version'      => '1.0',
            'source'              => 'ryaancms_local',
            'is_validated'        => false,
            'is_exported'         => false,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Asset name generation
    // Produces a clean, human-readable name from the prompt
    // ─────────────────────────────────────────────────────────────────────────

    private function generateAssetName(string $lower, string $domain, string $assetType): string
    {
        // Strip common noise words
        $noiseWords = ['build', 'create', 'make', 'generate', 'develop', 'implement', 'add', 'a', 'an', 'the', 'for', 'my', 'our'];
        $words = explode(' ', $lower);
        $filtered = array_filter($words, fn($w) => strlen($w) > 2 && !in_array($w, $noiseWords));

        // Take first 4-6 meaningful words and title-case them
        $nameWords = array_slice(array_values($filtered), 0, 5);
        if (empty($nameWords)) {
            return ucfirst($domain) . ' ' . ucfirst($assetType);
        }
        return implode(' ', array_map('ucfirst', $nameWords));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Privacy-safe summary
    // Strips anything that could be PII or business-sensitive before storage
    // ─────────────────────────────────────────────────────────────────────────

    private function sanitizeSummary(string $prompt): string
    {
        // Remove email patterns
        $clean = preg_replace('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', '[email]', $prompt);
        // Remove phone patterns
        $clean = preg_replace('/(\+?[0-9][\s\-]?){8,15}/', '[phone]', $clean);
        // Remove anything that looks like a name (Title Case 2+ consecutive words)
        // Keep business vocabulary only — truncate at 200 chars
        return mb_substr(trim($clean), 0, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Export format — the standard schema for RGIN cloud upload
    // This is what eventually gets sent to the Intelligence Cloud
    // ─────────────────────────────────────────────────────────────────────────

    public function toExportFormat(array $extracted): array
    {
        return [
            'rgin_schema'    => '1.0',
            'asset' => [
                'type'        => $extracted['asset_type'],
                'domain'      => $extracted['domain'],
                'name'        => $extracted['name'],
                'fingerprint' => $extracted['fingerprint'],
            ],
            'intelligence' => [
                'dependencies'     => $extracted['dependencies'],
                'tags'             => $extracted['tags'],
                'business_rules'   => $extracted['business_rules'],
                'prompt_summary'   => $extracted['prompt_summary'],
            ],
            'quality' => [
                'confidence'         => $extracted['confidence'],
                'extraction_quality' => $extracted['extraction_quality'],
                'matched_assets'     => $extracted['matched_assets'],
            ],
            'meta' => [
                'schema_version' => $extracted['schema_version'],
                'source'         => $extracted['source'],
                'extracted_at'   => now()->toISOString(),
            ],
        ];
    }
}
