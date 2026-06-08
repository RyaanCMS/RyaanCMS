<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class QualityReviewerAgent extends BaseAgent
{
    public function getName(): string        { return 'Quality Reviewer'; }
    public function getIndex(): int          { return 10; }
    public function getIcon(): string        { return '⭐'; }
    public function getDescription(): string { return 'Scoring UI, Backend, Security, and Performance'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $appName        = $context['frs']['app_name'] ?? $project->name;
        $totalFiles     = count($context['db_files'] ?? []) + count($context['backend_files'] ?? []) + count($context['ui_files'] ?? []);
        $requirements   = implode(', ', $context['frs']['functional_requirements'] ?? []);
        $modules        = collect($context['product']['modules'] ?? [])->pluck('name')->implode(', ');
        $retryCount     = $context['retry_count'] ?? 0;
        $validationOk   = empty($context['current_validation_errors'] ?? []);
        $fixesApplied   = count($context['debugger_fixes'] ?? []);
        $hasAuth      = $context['frs']['auth_required'] ?? true;
        $hasDashboard = in_array('dashboard', $context['frs']['pages_needed'] ?? []);
        $dbFileList   = $context['db_files']      ? implode(', ', (array) $context['db_files'])      : 'none';
        $beFileList   = $context['backend_files'] ? implode(', ', (array) $context['backend_files']) : 'none';
        $uiFileList   = $context['ui_files']      ? implode(', ', (array) $context['ui_files'])      : 'none';

        $systemPrompt = <<<SYS
You are a Senior Quality Reviewer for software projects. {$this->stackContext()}

Score the generated application across four dimensions (0–100 each).

SCORING GUIDE:
- UI (0-100): Views exist for all modules? Layout clean and consistent? Preview.html present?
- Backend (0-100): All controllers generated? Models with relationships? Routes file generated? Validation in place?
- Security (0-100): Auth middleware applied? Input validation in requests? No raw SQL? CSRF protection?
- Performance (0-100): Pagination used? Eager loading used? Indexes on foreign keys? No N+1 queries?

OUTPUT: Respond ONLY with a JSON object:
{
  "scores": {
    "ui": 82,
    "backend": 88,
    "security": 75,
    "performance": 70,
    "overall": 79
  },
  "strengths": ["Complete CRUD for all modules", "Auth implemented"],
  "issues": ["Missing eager loading in ProductController", "No input sanitization on search"],
  "recommendations": ["Add withCount() for dashboard stats", "Add index on products.status column"],
  "grade": "B+"
}
SYS;

        $summary = <<<SUMMARY
App: {$appName}
Total files generated: {$totalFiles}
Modules: {$modules}
Requirements covered: {$requirements}
Retry count: {$retryCount}
Validation passed: {$validationOk}
Debugger fixes applied: {$fixesApplied}
Has authentication: {$hasAuth}
Has dashboard: {$hasDashboard}
DB files: {$dbFileList}
Backend files: {$beFileList}
UI files: {$uiFileList}
SUMMARY;

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Reviewing code quality and scoring...']);

        $raw    = $this->chat($systemPrompt, $summary, 2000);
        $report = $this->extractJson($raw) ?? [
            'scores'          => ['ui' => 70, 'backend' => 70, 'security' => 65, 'performance' => 65, 'overall' => 68],
            'strengths'       => [],
            'issues'          => [],
            'recommendations' => [],
            'grade'           => 'C+',
        ];

        // Ensure overall is computed if missing
        if (!isset($report['scores']['overall'])) {
            $s = $report['scores'];
            $report['scores']['overall'] = (int) round(
                ($s['ui'] + $s['backend'] + $s['security'] + $s['performance']) / 4
            );
        }

        return ['quality_report' => $report];
    }
}
