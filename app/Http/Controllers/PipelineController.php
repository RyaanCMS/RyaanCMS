<?php

namespace App\Http\Controllers;

use App\Models\PipelineRun;
use App\Models\Project;
use App\Services\AI\Pipeline\PipelineOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PipelineController extends Controller
{
    public function __construct(private PipelineOrchestrator $orchestrator) {}

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
     * SSE endpoint — streams pipeline events live.
     * Also caches every event so the polling endpoint can serve them.
     */
    public function stream(Request $request, Project $project, PipelineRun $run)
    {
        $this->authorize('update', $project);
        abort_if($run->project_id !== $project->id, 404);
        abort_if($run->user_id   !== Auth::id(),    403);
        abort_if($run->status === 'running',         409, 'Pipeline already running.');

        $user = Auth::user();

        return response()->stream(function () use ($run, $project, $user) {
            // Keep running even if the HTTP client disconnects (shared hosting SSE cut-off)
            ignore_user_abort(true);
            set_time_limit(600);

            $cacheKey = 'pipeline_events_' . $run->id;
            Cache::forget($cacheKey);

            $emit = function (array $event) use ($cacheKey) {
                // Push to SSE stream
                echo 'data: ' . json_encode($event) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();

                // Also cache for polling fallback
                $events   = Cache::get($cacheKey, []);
                $events[] = $event;
                Cache::put($cacheKey, $events, now()->addHours(2));
            };

            try {
                $this->orchestrator->run($run, $emit);
            } catch (\Throwable $e) {
                $errEvent = ['type' => 'error', 'message' => $e->getMessage()];
                echo 'data: ' . json_encode($errEvent) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();

                $events   = Cache::get($cacheKey, []);
                $events[] = $errEvent;
                Cache::put($cacheKey, $events, now()->addHours(2));

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
     * Polling endpoint — returns cached events since a given offset.
     * Used as fallback when SSE is not supported (shared hosting).
     */
    public function poll(Request $request, Project $project, PipelineRun $run)
    {
        $this->authorize('view', $project);
        abort_if($run->project_id !== $project->id, 404);

        $offset = (int) $request->query('offset', 0);
        $cacheKey = 'pipeline_events_' . $run->id;
        $allEvents = Cache::get($cacheKey, []);
        $newEvents = array_slice($allEvents, $offset);

        return response()->json([
            'events'     => array_values($newEvents),
            'offset'     => $offset + count($newEvents),
            'status'     => $run->fresh()->status,
        ]);
    }

    /**
     * Return pipeline run status.
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

    public function destroy(Project $project, PipelineRun $run)
    {
        $this->authorize('update', $project);
        abort_if($run->project_id !== $project->id, 404);
        abort_if($run->user_id   !== Auth::id(),    403);

        $run->delete();

        return response()->json(['success' => true]);
    }
}
