<?php

namespace App\Services\AI\Pipeline;

use App\Models\Project;
use App\Models\ProjectFile;

/**
 * Validates generated project files without running a server.
 *
 * Checks:
 *  1. PHP syntax via `php -l` on all .php files
 *  2. Missing namespace declarations
 *  3. Missing closing braces (rough brace-balance check)
 *  4. Missing class declarations in files that should have them
 *  5. Route file references to non-existent controller classes
 *  6. Business rules coverage — domain rules present in generated code
 */
class BuildValidator
{
    private const PHP_BINARY = 'C:\\laragon\\bin\\php\\php-8.3.30-Win32-vs16-x64\\php.exe';

    public function validate(Project $project, array $filePaths, string $domain = ''): array
    {
        $errors = [];

        foreach ($filePaths as $path) {
            $pf = ProjectFile::where('project_id', $project->id)->where('path', $path)->first();
            if (!$pf) continue;

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($ext === 'php') {
                $phpErrors = $this->validatePhp($path, $pf->content);
                $errors    = array_merge($errors, $phpErrors);
            }

            if ($ext === 'php' && str_contains($path, 'routes/')) {
                $routeErrors = $this->validateRouteReferences($path, $pf->content, $project);
                $errors      = array_merge($errors, $routeErrors);
            }
        }

        // Business rules coverage check (soft warnings, non-blocking)
        if ($domain) {
            $ruleWarnings = $this->validateBusinessRules($project, $filePaths, $domain);
            $errors       = array_merge($errors, $ruleWarnings);
        }

        return $errors;
    }

    /**
     * Run PHP syntax check via php -l on a temp file.
     */
    private function validatePhp(string $path, string $content): array
    {
        $errors = [];

        // Try php -l first
        if (file_exists(self::PHP_BINARY)) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'ryaan_validate_');
            file_put_contents($tmpFile, $content);

            $output     = [];
            $returnCode = 0;
            exec('"' . self::PHP_BINARY . '" -l "' . $tmpFile . '" 2>&1', $output, $returnCode);
            @unlink($tmpFile);

            if ($returnCode !== 0) {
                $syntaxError = implode(' ', $output);
                // Clean up the temp path from the error message
                $syntaxError = str_replace($tmpFile, $path, $syntaxError);
                $errors[] = ['file' => $path, 'type' => 'syntax', 'message' => trim($syntaxError)];
                return $errors; // stop further checks if syntax is broken
            }
        } else {
            // Fallback: static analysis when php binary not found
            $errors = array_merge($errors, $this->staticPhpChecks($path, $content));
        }

        return $errors;
    }

    /**
     * Static checks when PHP binary is unavailable.
     */
    private function staticPhpChecks(string $path, string $content): array
    {
        $errors = [];

        // Must start with <?php
        if (!str_starts_with(trim($content), '<?php')) {
            $errors[] = ['file' => $path, 'type' => 'missing_open_tag', 'message' => "File does not start with <?php"];
        }

        // Check brace balance
        $open  = substr_count($content, '{');
        $close = substr_count($content, '}');
        if ($open !== $close) {
            $errors[] = [
                'file'    => $path,
                'type'    => 'brace_mismatch',
                'message' => "Unbalanced braces: {$open} opening vs {$close} closing",
            ];
        }

        // Controllers/Models should declare a class
        if ((str_contains($path, 'Controller') || str_contains($path, 'Models/')) && !preg_match('/\bclass\s+\w+/i', $content)) {
            $errors[] = ['file' => $path, 'type' => 'missing_class', 'message' => "No class declaration found in {$path}"];
        }

        // Namespace should exist in app/ files
        if (str_starts_with($path, 'app/') && !preg_match('/^namespace\s+/m', $content)) {
            $errors[] = ['file' => $path, 'type' => 'missing_namespace', 'message' => "Missing namespace declaration in {$path}"];
        }

        // Check for common undefined references
        if (str_contains($content, '->where(') && !str_contains($content, 'use Illuminate') && !str_contains($content, 'extends Model')) {
            // Possible missing Eloquent use — soft warning only
        }

        return $errors;
    }

    /**
     * Check that controller classes referenced in routes actually exist in the project.
     */
    private function validateRouteReferences(string $path, string $content, Project $project): array
    {
        $errors = [];

        // Extract controller class references like [ProductController::class, 'method']
        preg_match_all('/\[(\w+Controller)::class/', $content, $matches);

        foreach (array_unique($matches[1] ?? []) as $controllerClass) {
            // Check if the controller exists as a ProjectFile
            $exists = ProjectFile::where('project_id', $project->id)
                ->where('path', 'like', "%Controllers/{$controllerClass}.php")
                ->exists();

            if (!$exists) {
                $errors[] = [
                    'file'    => $path,
                    'type'    => 'missing_controller',
                    'message' => "Route references {$controllerClass} but no matching controller file found in project",
                ];
            }
        }

        return $errors;
    }

    /**
     * Business rules coverage check.
     *
     * For each domain rule, we define the code-level concept that MUST appear
     * in at least one generated file. Missing concepts become 'rule_violation'
     * warnings (type = 'rule_warning') — soft, non-blocking — so the DebuggerAgent
     * can act on them in the fix loop.
     */
    public function validateBusinessRules(Project $project, array $filePaths, string $domain): array
    {
        $rules = config("kb.business_rules.{$domain}", []);
        if (empty($rules)) return [];

        // Rule key → code concept keywords that should appear in generated PHP
        $conceptMap = [
            // ecommerce
            'stock_lock_on_order'        => ['stock', 'inventory', 'quantity', 'reserve'],
            'stock_restore_on_cancel'    => ['cancel', 'restore', 'stock', 'quantity'],
            'payment_before_fulfillment' => ['payment', 'paid', 'status', 'fulfil'],
            'price_at_order_time'        => ['price', 'unit_price', 'order_item'],
            'order_status_flow'          => ['status', 'pending', 'confirmed', 'shipped'],
            'coupon_once_per_user'       => ['coupon', 'used', 'usage'],
            // accounting
            'double_entry_required'      => ['debit', 'credit', 'journal', 'entry'],
            'immutable_posted_entries'   => ['posted', 'void', 'immutable', 'locked'],
            'audit_every_change'         => ['audit', 'log', 'history'],
            'period_lock'                => ['period', 'closed', 'fiscal'],
            // hrm
            'payroll_immutable'          => ['payroll', 'published', 'void'],
            'leave_balance_check'        => ['leave', 'balance', 'check'],
            'attendance_before_payroll'  => ['attendance', 'payroll', 'period'],
            // inventory
            'no_negative_stock'          => ['stock', 'quantity', 'negative', 'backorder'],
            'grn_before_stock_increase'  => ['grn', 'goods', 'received', 'stock'],
            // hospital
            'patient_data_privacy'       => ['patient', 'auth', 'permission', 'policy'],
            'prescription_by_doctor_only'=> ['prescription', 'doctor', 'policy'],
            'billing_after_service'      => ['bill', 'unpaid', 'discharge', 'payment'],
            // saas
            'tenant_data_isolation'      => ['tenant', 'tenant_id', 'scope'],
            'feature_limit_enforce'      => ['plan', 'limit', 'feature', 'quota'],
            // restaurant
            'kitchen_order_flow'         => ['order', 'kitchen', 'status', 'table'],
            // real estate
            'property_status_flow'       => ['property', 'status', 'available', 'sold'],
        ];

        // Collect all PHP content into one searchable blob for this project's files
        $allContent = '';
        foreach ($filePaths as $path) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') continue;
            $pf = ProjectFile::where('project_id', $project->id)->where('path', $path)->first();
            if ($pf) $allContent .= ' ' . strtolower($pf->content);
        }

        if (empty(trim($allContent))) return [];

        $warnings = [];
        foreach ($rules as $ruleKey => $ruleDescription) {
            $concepts = $conceptMap[$ruleKey] ?? [];
            if (empty($concepts)) continue;

            // Rule passes if at least one concept keyword appears in the codebase
            $found = false;
            foreach ($concepts as $keyword) {
                if (str_contains($allContent, $keyword)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $warnings[] = [
                    'file'    => "domain/{$domain}",
                    'type'    => 'rule_warning',
                    'rule'    => $ruleKey,
                    'message' => "Business rule not enforced: [{$ruleKey}] — {$ruleDescription}",
                ];
            }
        }

        return $warnings;
    }

    /**
     * Summarize errors as a human-readable string for SSE emission.
     */
    public function summarize(array $errors): string
    {
        if (empty($errors)) return 'No errors found.';

        $lines = [];
        foreach ($errors as $err) {
            $lines[] = "[{$err['type']}] {$err['file']}: {$err['message']}";
        }

        return implode("\n", $lines);
    }
}
