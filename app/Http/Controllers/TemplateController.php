<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectModule;
use App\Services\Template\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function __construct(private TemplateRegistry $registry) {}

    // ── Public: serve active template ─────────────────────────────────────────
    public function serve(Project $project)
    {
        $active = ProjectModule::where('project_id', $project->id)
            ->where('module_key', 'like', 'template.%')
            ->where('status', 'active')
            ->first();

        if (!$active) {
            return view('templates.not-active', compact('project'));
        }

        $template = $this->registry->get($active->module_key);

        if (!$template || !view()->exists($template['view'])) {
            return view('templates.not-active', compact('project'));
        }

        return view($template['view'], compact('project', 'template'));
    }

    // ── Auth: browse all templates in marketplace ─────────────────────────────
    public function browse(Request $request)
    {
        $templates = $this->registry->all();
        $projects  = Auth::user()->projects()->select('id', 'name')->orderBy('name')->get();

        // Map: module_key => [project_id => status]
        $installedMap = ProjectModule::whereIn('project_id', $projects->pluck('id'))
            ->where('module_key', 'like', 'template.%')
            ->get()
            ->groupBy('module_key')
            ->map(fn($rows) => $rows->keyBy('project_id')->map(fn($r) => $r->status));

        return view('marketplace.templates', compact('templates', 'projects', 'installedMap'));
    }

    // ── Auth: install a template for a project ────────────────────────────────
    public function install(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $template = $this->registry->get($key);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $exists = ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Already installed.']);
        }

        ProjectModule::create([
            'project_id' => $project->id,
            'module_key' => $key,
            'status'     => 'installed',
        ]);

        return response()->json([
            'success' => true,
            'message' => "✅ {$template['name']} installed.",
        ]);
    }

    // ── Auth: activate a template (deactivates all others for this project) ───
    public function activate(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $template = $this->registry->get($key);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        // Install silently if not installed
        $pm = ProjectModule::firstOrCreate(
            ['project_id' => $project->id, 'module_key' => $key],
            ['status' => 'installed']
        );

        // Deactivate all other templates for this project
        ProjectModule::where('project_id', $project->id)
            ->where('module_key', 'like', 'template.%')
            ->where('module_key', '!=', $key)
            ->update(['status' => 'installed']);

        $pm->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'status'  => 'active',
            'message' => "🎉 {$template['name']} is now live!",
            'url'     => route('site.serve', $project),
        ]);
    }

    // ── Auth: deactivate a template ───────────────────────────────────────────
    public function deactivate(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->update(['status' => 'installed']);

        return response()->json(['success' => true, 'status' => 'installed', 'message' => 'Template deactivated.']);
    }

    // ── Auth: uninstall a template ────────────────────────────────────────────
    public function uninstall(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->delete();

        $template = $this->registry->get($key);
        return response()->json([
            'success' => true,
            'message' => ($template['name'] ?? $key) . ' uninstalled.',
        ]);
    }
}
