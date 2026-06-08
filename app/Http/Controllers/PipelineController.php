<?php

namespace App\Http\Controllers;

use App\Models\PipelineRun;
use App\Models\Project;
use App\Services\AI\Pipeline\PipelineOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PipelineController extends Controller
{
    public function __construct(private PipelineOrchestrator $orchestrator) {}

    /**
     * Show the pipeline builder page for a project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $providers = Auth::user()->aiProviders()->where('is_active', true)->get();
        $runs      = PipelineRun::where('project_id', $project->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(10)
            ->get();

        return view('pipeline.show', compact('project', 'providers', 'runs'));
    }

    /**
     * Create a new pipeline run record and return its ID.
     */
    public function start(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'prompt'   => ['required', 'string', 'min:10', 'max:5000'],
            'provider' => ['nullable', 'string'],
            'model'    => ['nullable', 'string'],
        ]);

        $run = PipelineRun::create([
            'project_id' => $project->id,
            'user_id'    => Auth::id(),
            'prompt'     => $request->prompt,
            'status'     => 'pending',
            'context'    => [
                'provider' => $request->input('provider'),
                'model'    => $request->input('model'),
            ],
        ]);

        return response()->json(['run_id' => $run->id]);
    }

    /**
     * SSE endpoint — runs the full 10-agent pipeline and streams events.
     */
    public function stream(Request $request, Project $project, PipelineRun $run)
    {
        $this->authorize('update', $project);
        abort_if($run->project_id !== $project->id, 404);
        abort_if($run->user_id   !== Auth::id(),    403);
        abort_if($run->status === 'running',         409, 'Pipeline already running.');

        $user = Auth::user();

        return response()->stream(function () use ($run, $project, $user) {
            // Bump PHP execution limit for the pipeline (10 AI calls can take 3-5 min)
            set_time_limit(600);

            $emit = function (array $event) {
                echo 'data: ' . json_encode($event) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            try {
                $this->orchestrator->run($run, $emit);
            } catch (\Throwable $e) {
                $emit(['type' => 'error', 'message' => $e->getMessage()]);
                $run->markFailed();
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Return pipeline run status (for polling fallback).
     */
    public function status(Project $project, PipelineRun $run)
    {
        $this->authorize('view', $project);
        abort_if($run->project_id !== $project->id, 404);

        return response()->json([
            'id'             => $run->id,
            'status'         => $run->status,
            'current_agent'  => $run->current_agent,
            'retry_count'    => $run->retry_count,
            'total_tokens'   => $run->total_tokens,
            'total_files'    => $run->total_files,
            'quality_report' => $run->quality_report,
            'started_at'     => $run->started_at?->toISOString(),
            'completed_at'   => $run->completed_at?->toISOString(),
        ]);
    }

    /**
     * Delete a pipeline run record.
     */
    public function destroy(Project $project, PipelineRun $run)
    {
        $this->authorize('update', $project);
        abort_if($run->project_id !== $project->id, 404);
        abort_if($run->user_id   !== Auth::id(),    403);

        $run->delete();

        return response()->json(['success' => true]);
    }
}
