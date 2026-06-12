<?php

namespace App\Services\AI\Pipeline;

use App\Models\OutcomeRecord;
use App\Models\PipelineRun;
use App\Models\Project;
use App\Models\User;
use App\Services\AI\AIManager;
use App\Services\AI\CodeGeneratorService;
use App\Services\AI\KnowledgeBaseService;
use App\Services\AI\SeniorDevKnowledgeBase;
use App\Services\AI\WisdomEngine;
use App\Services\AI\Pipeline\Agents\BackendAgent;
use App\Services\AI\Pipeline\Agents\DatabaseAgent;
use App\Services\AI\Pipeline\Agents\DebuggerAgent;
use App\Services\AI\Pipeline\Agents\ProductManagerAgent;
use App\Services\AI\Pipeline\Agents\QualityReviewerAgent;
use App\Services\AI\Pipeline\Agents\RequirementAnalystAgent;
use App\Services\AI\Pipeline\Agents\SystemArchitectAgent;
use App\Services\AI\Pipeline\Agents\TaskPlannerAgent;
use App\Services\AI\Pipeline\Agents\TestingAgent;
use App\Services\AI\Pipeline\Agents\UiUxDesignerAgent;
use App\Services\AI\Pipeline\Contracts\AgentInterface;

/**
 * Autonomous Build Loop:
 *  PLAN (agents 1-4) → GENERATE (agents 5-7) → RUN (validate)
 *  → if FAIL: FIX (agent 9, debugger) → RETRY (up to 5x with error memory)
 *  → SUCCESS → TEST (agent 8) → REVIEW (agent 10) → DONE
 */
class PipelineOrchestrator
{
    private const MAX_RETRIES = 5;

    public function __construct(
        private AIManager              $aiManager,
        private CodeGeneratorService   $codeGenerator,
        private BuildValidator         $validator,
        private KnowledgeBaseService   $kb,
        private SeniorDevKnowledgeBase $seniorKb,
        private WisdomEngine           $wisdomEngine,
    ) {}

    /**
     * Run the full 10-agent autonomous pipeline with SSE streaming.
     */
    public function run(PipelineRun $run, callable $emit): void
    {
        $project  = $run->project;
        $user     = $run->user;
        $provider = null; // uses user's default
        $model    = null;

        $run->markRunning();

        $emit(['type' => 'pipeline_start', 'run_id' => $run->id, 'total_agents' => 10]);

        // Hydrate the context with the original prompt + any stored config
        $context  = array_merge(['prompt' => $run->prompt, 'pipeline_run_id' => $run->id], $run->context ?? []);
        $provider = $context['provider'] ?? null;
        $model    = $context['model']    ?? null;

        // Pre-seed KB context so all agents have domain knowledge without spending tokens
        $appType = $this->kb->matchAppType($run->prompt);
        $context['kb_app_type']  = $appType;
        $context['kb_rbac']      = $this->kb->getRbac($appType);
        $context['kb_workflow']  = $this->kb->getWorkflow($appType);
        $context['kb_reports']   = $this->kb->getReports($appType);

        // Senior Developer Brief — architecture decisions, security, anti-patterns, DB rules
        // All 10 agents receive this context — they behave like a senior dev team briefed by the CTO
        $seniorScope             = $this->seniorKb->analyzeScope($run->prompt, $appType);
        $context['senior_brief'] = $this->seniorKb->buildSeniorBrief($run->prompt, $appType);

        // Wisdom Brief — past project lessons + similar domain outcomes (closes the learning loop)
        $context['wisdom_brief'] = $this->wisdomEngine->buildWisdomBrief($appType, $run->prompt);
        $context['architecture'] = $seniorScope['architecture'];
        $context['db_choice']    = $seniorScope['db'];
        $context['scale_tier']   = $seniorScope['scale_tier'];
        $context['is_saas']      = $seniorScope['is_saas'];
        $context['has_api']      = $seniorScope['has_api'];
        $context['security_checklist'] = $this->seniorKb->getSecurityChecklist();
        $context['anti_patterns']      = $this->seniorKb->getAntiPatternsFor($appType);
        $context['estimation']         = $this->seniorKb->getEstimate($appType);

        // Callable that saves a file and returns saved file data
        $saveFile = fn(Project $p, string $path, string $content) =>
            $this->codeGenerator->saveGeneratedFile($p, $path, $content);

        try {
            // ── PHASE 1: PLAN (Agents 1-4) ──────────────────────────────────
            $emit(['type' => 'phase', 'phase' => 'PLAN', 'label' => 'Analysing requirements...']);
            foreach ($this->planAgents($user, $provider, $model) as $agent) {
                $context = $this->runAgent($agent, $context, $project, $saveFile, $emit, $run);
            }

            // ── PHASE 2: GENERATE (Agents 5-7) ─────────────────────────────
            $emit(['type' => 'phase', 'phase' => 'GENERATE', 'label' => 'Generating code...']);
            foreach ($this->generateAgents($user, $provider, $model) as $agent) {
                $context = $this->runAgent($agent, $context, $project, $saveFile, $emit, $run);
            }

            // ── PHASE 3: BUILD LOOP (validate → fix → retry) ────────────────
            $context = $this->buildLoop($context, $project, $user, $provider, $model, $saveFile, $emit, $run);

            // ── PHASE 4: TEST (Agent 8) ─────────────────────────────────────
            $emit(['type' => 'phase', 'phase' => 'TEST', 'label' => 'Running tests...']);
            $testAgent = $this->makeAgent(TestingAgent::class, $user, $provider, $model);
            $context   = $this->runAgent($testAgent, $context, $project, $saveFile, $emit, $run);

            // ── PHASE 5: REVIEW (Agent 10) ──────────────────────────────────
            $emit(['type' => 'phase', 'phase' => 'REVIEW', 'label' => 'Reviewing quality...']);
            $reviewAgent = $this->makeAgent(QualityReviewerAgent::class, $user, $provider, $model);
            $context     = $this->runAgent($reviewAgent, $context, $project, $saveFile, $emit, $run);

            // Persist quality report
            $qualityReport = $context['quality_report'] ?? null;
            $run->update([
                'quality_report' => $qualityReport,
                'total_files'    => $run->total_files,
            ]);

            $run->markCompleted();

            $freshRun     = $run->fresh();
            $buildSeconds = $freshRun->started_at
                ? (int) $freshRun->started_at->diffInSeconds(now())
                : 0;
            $qualityReport = $context['quality_report'] ?? null;
            $aiCostEst   = round(($freshRun->total_tokens ?? 0) * 0.000003, 6);
            $tokensSaved = (int) ($context['tokens_saved_total'] ?? 0);
            $aiCostSaved = round($tokensSaved * 0.000003, 6);

            // Record business outcome for trend analysis and reporting
            try {
                OutcomeRecord::create([
                    'project_id'       => $run->project_id,
                    'pipeline_run_id'  => $run->id,
                    'user_id'          => $run->user_id,
                    'domain'           => $context['kb_app_type'] ?? 'general',
                    'files_generated'  => $freshRun->total_files ?? 0,
                    'tokens_used'      => $freshRun->total_tokens ?? 0,
                    'ai_cost_estimate' => $aiCostEst,
                    'ai_cost_saved'    => $aiCostSaved,
                    'build_time_seconds' => $buildSeconds,
                    'quality_score'    => $qualityReport['scores']['overall'] ?? null,
                    'quality_grade'    => $qualityReport['grade'] ?? null,
                    'blueprint_source' => $context['blueprint_source'] ?? 'ai_discovery',
                    'modules_used'     => $context['product']['modules'] ?? null,
                    'components_reused'=> $context['components_used'] ?? null,
                    'rules_applied'    => $context['rules_applied'] ?? null,
                    'confidence_scores'=> $context['confidence_scores'] ?? null,
                    'ai_was_used'      => true,
                ]);
            } catch (\Throwable) {
                // Non-fatal
            }

            // Record pipeline success as a lesson — builds organizational memory
            $this->wisdomEngine->recordLesson([
                'project_id'       => $run->project_id,
                'user_id'          => $run->user_id,
                'domain'           => $context['kb_app_type'] ?? 'general',
                'lesson'           => "Full pipeline completed: " . mb_substr($run->prompt, 0, 200),
                'what_went_right'  => "10-agent pipeline succeeded with {$freshRun->total_files} files",
                'recommendation'   => "Blueprint: " . ($context['architecture'] ?? 'modular') . ", Scale: " . ($context['scale_tier'] ?? 'small'),
                'outcome'          => "success — {$freshRun->total_files} files, {$freshRun->total_tokens} tokens",
                'confidence'       => 0.9,
                'tags'             => array_filter([$context['kb_app_type'] ?? null, 'pipeline', 'success']),
                'ai_cost_estimate' => round(($freshRun->total_tokens ?? 0) * 0.000003, 6),
                'is_rule_candidate'=> false,
            ]);

            $emit([
                'type'         => 'pipeline_done',
                'run_id'       => $run->id,
                'total_tokens' => $freshRun->total_tokens,
                'total_files'  => $freshRun->total_files,
                'quality'      => $qualityReport,
            ]);
        } catch (\Throwable $e) {
            $run->markFailed();

            // Record failure as an autopsy entry — failure patterns are as valuable as successes
            $this->wisdomEngine->recordLesson([
                'project_id'       => $run->project_id,
                'user_id'          => $run->user_id,
                'domain'           => $context['kb_app_type'] ?? 'general',
                'entry_type'       => 'autopsy',
                'lesson'           => "Pipeline failed: " . mb_substr($run->prompt, 0, 150),
                'what_went_wrong'  => $e->getMessage(),
                'recommendation'   => "Review error and adjust pipeline config",
                'confidence'       => 0.7,
                'tags'             => array_filter([$context['kb_app_type'] ?? null, 'pipeline', 'failure']),
                'is_rule_candidate'=> false,
            ]);

            $emit(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Autonomous build loop with intelligent fix memory.
     *
     * Each iteration:
     *  1. RUN   — validate all generated PHP files
     *  2. LEARN — compare to previous errors; mark each fix as resolved/failed
     *  3. FIX   — DebuggerAgent receives full fix-memory context (what worked, what didn't)
     *  4. RETRY — up to MAX_RETRIES; escalates strategy (patch→rewrite→redesign) per error
     */
    private function buildLoop(
        array    $context,
        Project  $project,
        User     $user,
        ?string  $provider,
        ?string  $model,
        callable $saveFile,
        callable $emit,
        PipelineRun $run
    ): array {
        $allGeneratedFiles = array_merge(
            $context['db_files']      ?? [],
            $context['backend_files'] ?? [],
            $context['ui_files']      ?? [],
        );

        // Restore or create a fresh fix-memory instance
        $fixMemory    = new FixMemory($run->error_memory ?? []);
        $errorsBefore = [];

        for ($retry = 0; $retry <= self::MAX_RETRIES; $retry++) {

            // ── RUN: Validate ─────────────────────────────────────────────────
            $emit([
                'type'    => 'phase',
                'phase'   => $retry === 0 ? 'RUN' : 'RETRY',
                'label'   => $retry === 0 ? 'Validating generated code...' : "Retry #{$retry} — re-validating...",
                'attempt' => $retry,
            ]);

            $errorsNow = $this->validator->validate($project, $allGeneratedFiles);

            // ── LEARN: Record outcomes from the previous fix attempt ──────────
            if ($retry > 0 && !empty($errorsBefore)) {
                $fixMemory->recordOutcomes($errorsBefore, $errorsNow);

                // Persist updated memory
                $run->update(['error_memory' => $fixMemory->toArray()]);

                $resolved = count($errorsBefore) - count($errorsNow);
                $emit([
                    'type'           => 'fix_outcome',
                    'attempt'        => $retry,
                    'errors_before'  => count($errorsBefore),
                    'errors_after'   => count($errorsNow),
                    'resolved_count' => max(0, $resolved),
                    'new_count'      => max(0, count($errorsNow) - count($errorsBefore)),
                    'stuck_count'    => count($fixMemory->stuckErrors()),
                ]);
            }

            if (empty($errorsNow)) {
                $emit(['type' => 'build_status', 'status' => 'success', 'attempt' => $retry]);
                break;
            }

            $emit([
                'type'        => 'build_status',
                'status'      => 'fail',
                'attempt'     => $retry,
                'error_count' => count($errorsNow),
                'errors'      => array_map(fn($e) => array_merge($e, [
                    'strategy'    => $fixMemory->strategyFor($e),
                    'failed_fixes'=> $fixMemory->failedFixesFor($e),
                ]), $errorsNow),
                'stuck_count' => count($fixMemory->stuckErrors()),
            ]);

            if ($retry === self::MAX_RETRIES) {
                $emit(['type' => 'build_status', 'status' => 'max_retries',
                       'message' => 'Max retries reached. Proceeding with best effort.']);
                break;
            }

            // ── FIX: Build fix summary for learning, then run debugger ────────
            $strategies = array_unique(array_map(
                fn($e) => $fixMemory->strategyFor($e) . ' on ' . ($e['file'] ?? '?'),
                $errorsNow
            ));
            $fixSummary = 'Attempt #' . ($retry + 1) . ': ' . implode('; ', array_slice($strategies, 0, 4));

            $fixMemory->recordAttempt($retry, $errorsNow, $fixSummary);
            $run->addErrorMemory($retry, $errorsNow);
            $run->increment('retry_count');

            // Pass rich context to the debugger
            $context['current_validation_errors'] = $errorsNow;
            $context['fix_memory_context']        = $fixMemory->toPromptContext($errorsNow);
            $context['retry_count']               = $retry + 1;
            $context['stuck_errors']              = $fixMemory->stuckErrors();

            $errorsBefore = $errorsNow;

            $debugger = $this->makeAgent(DebuggerAgent::class, $user, $provider, $model);
            $stuckCount = count($fixMemory->stuckErrors());
            $emit([
                'type'        => 'agent_start',
                'agent_index' => $debugger->getIndex(),
                'agent_name'  => $debugger->getName(),
                'icon'        => $debugger->getIcon(),
                'description' => 'Fixing ' . count($errorsNow) . ' error(s) — attempt #' . ($retry + 1)
                                 . ($stuckCount > 0 ? " ({$stuckCount} stuck)" : ''),
            ]);

            $startTs  = microtime(true);
            $partial  = $debugger->run($context, $project, $saveFile, $emit);
            $context  = array_merge($context, $partial);

            $run->mergeContext(['debugger_fixes' => $context['debugger_fixes'] ?? []]);
            $run->update(['current_agent' => $debugger->getIndex()]);

            $emit([
                'type'        => 'agent_done',
                'agent_index' => $debugger->getIndex(),
                'agent_name'  => $debugger->getName(),
                'duration_ms' => (int) ((microtime(true) - $startTs) * 1000),
            ]);
        }

        // Persist final fix-memory state; remove transient keys
        $run->update(['error_memory' => $fixMemory->toArray()]);
        unset($context['current_validation_errors'], $context['fix_memory_context'], $context['stuck_errors']);

        return $context;
    }

    /**
     * Run a single agent and emit start/done events.
     */
    private function runAgent(
        AgentInterface $agent,
        array          $context,
        Project        $project,
        callable       $saveFile,
        callable       $emit,
        PipelineRun    $run
    ): array {
        $emit([
            'type'        => 'agent_start',
            'agent_index' => $agent->getIndex(),
            'agent_name'  => $agent->getName(),
            'icon'        => $agent->getIcon(),
            'description' => $agent->getDescription(),
        ]);

        $run->update(['current_agent' => $agent->getIndex()]);

        $startTs = microtime(true);
        $partial  = $agent->run($context, $project, $saveFile, $emit);
        $merged   = array_merge($context, $partial);

        // Count new files generated this step
        $fileKeys  = ['db_files', 'backend_files', 'ui_files', 'test_files'];
        $newFiles  = 0;
        foreach ($fileKeys as $key) {
            $before   = count($context[$key] ?? []);
            $after    = count($merged[$key] ?? []);
            $newFiles += max(0, $after - $before);
        }
        if ($newFiles > 0) $run->incrementFiles($newFiles);

        $run->mergeContext($partial);

        $emit([
            'type'        => 'agent_done',
            'agent_index' => $agent->getIndex(),
            'agent_name'  => $agent->getName(),
            'duration_ms' => (int) ((microtime(true) - $startTs) * 1000),
            'files_saved' => $newFiles,
        ]);

        return $merged;
    }

    // ── Agent factory helpers ─────────────────────────────────────────────────

    /** @return AgentInterface[] */
    private function planAgents(User $user, ?string $provider, ?string $model): array
    {
        return [
            $this->makeAgent(RequirementAnalystAgent::class, $user, $provider, $model),
            $this->makeAgent(ProductManagerAgent::class,     $user, $provider, $model),
            $this->makeAgent(TaskPlannerAgent::class,        $user, $provider, $model),
            $this->makeAgent(SystemArchitectAgent::class,    $user, $provider, $model),
        ];
    }

    /** @return AgentInterface[] */
    private function generateAgents(User $user, ?string $provider, ?string $model): array
    {
        return [
            $this->makeAgent(DatabaseAgent::class,    $user, $provider, $model),
            $this->makeAgent(BackendAgent::class,     $user, $provider, $model),
            $this->makeAgent(UiUxDesignerAgent::class,$user, $provider, $model),
        ];
    }

    private function makeAgent(string $class, User $user, ?string $provider, ?string $model): AgentInterface
    {
        return new $class($this->aiManager, $provider, $model, $user);
    }
}
