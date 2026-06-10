<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\Setting;
use App\Services\Template\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    private const DEFAULT_TEMPLATE = 'template.ryaancms';

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
        $this->publishProjectOnRootDomain($project);

        return response()->json([
            'success' => true,
            'status'  => 'active',
            'message' => "{$template['name']} is now live on your domain.",
            'url'     => route('home'),
        ]);
    }

    // ── Auth: deactivate a template ───────────────────────────────────────────
    public function deactivate(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->update(['status' => 'installed']);

        $this->refreshRootDomainProject($project);

        return response()->json(['success' => true, 'status' => 'installed', 'message' => 'Template deactivated.']);
    }

    // ── Auth: uninstall a template ────────────────────────────────────────────
    public function uninstall(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->delete();

        $this->refreshRootDomainProject($project);

        $template = $this->registry->get($key);
        return response()->json([
            'success' => true,
            'message' => ($template['name'] ?? $key) . ' uninstalled.',
        ]);
    }

    // ── Auth: download template as ZIP ────────────────────────────────────────
    public function download(string $key)
    {
        $template = $this->registry->get($key);
        abort_if(!$template, 404);

        $viewFile = resource_path('views/' . str_replace('.', '/', $template['view']) . '.blade.php');
        $slug     = str_replace('template.', '', $key); // "restaurant"
        $zipName  = 'ryaan-template-' . $slug . '.zip';
        $tmpPath  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

        $manifest = [
            'ryaan_manifest' => '1.0',
            'key'            => $key,
            'name'           => $template['name'],
            'description'    => $template['description'],
            'category'       => $template['category'],
            'type'           => 'template',
            'icon'           => $template['icon'],
            'color'          => $template['color'],
            'tags'           => $template['tags'],
            'version'        => '1.0.0',
            'author'         => 'RyaanCMS',
            'requires'       => 'RyaanCMS >= 1.0',
            'built_at'       => now()->toISOString(),
        ];

        $zip = new \ZipArchive();
        $zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('ryaan-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (file_exists($viewFile)) {
            $zip->addFromString('views/template.blade.php', file_get_contents($viewFile));
        }

        $zip->addFromString('README.md',
            "# {$template['name']}\n\n"
            . "> {$template['category']} Template for RyaanCMS\n\n"
            . "{$template['description']}\n\n"
            . "## Installation\n\n"
            . "1. Go to **Marketplace → Templates** in your RyaanCMS dashboard\n"
            . "2. Select your project from the dropdown\n"
            . "3. Click **Install**, then **Activate** to go live\n\n"
            . "## Live Preview\n\nAfter activation your site is available on your RyaanCMS root domain. `/site/{project-id}` remains available as a direct preview URL.\n\n"
            . "---\n*Powered by [RyaanCMS](https://github.com/RyaanCMS)*\n"
        );

        $zip->close();

        return response()->download($tmpPath, $zipName)->deleteFileAfterSend(true);
    }

    private function publishProjectOnRootDomain(Project $project): void
    {
        Setting::set('system.public_site_project_id', $project->id, 'integer');
    }

    private function refreshRootDomainProject(Project $changedProject): void
    {
        $currentProjectId = (int) Setting::get('system.public_site_project_id', 0);

        if ($currentProjectId !== $changedProject->id) {
            return;
        }

        $stillActive = ProjectModule::where('project_id', $changedProject->id)
            ->where('module_key', 'like', 'template.%')
            ->where('status', 'active')
            ->exists();

        if ($stillActive) {
            return;
        }

        $nextActive = ProjectModule::where('module_key', 'like', 'template.%')
            ->where('status', 'active')
            ->latest('updated_at')
            ->first();

        if ($nextActive) {
            Setting::set('system.public_site_project_id', $nextActive->project_id, 'integer');
            return;
        }

        $this->activateDefaultTemplateFor($changedProject);
    }

    private function activateDefaultTemplateFor(Project $project): void
    {
        ProjectModule::updateOrCreate(
            ['project_id' => $project->id, 'module_key' => self::DEFAULT_TEMPLATE],
            ['status' => 'active']
        );

        Setting::set('system.public_site_project_id', $project->id, 'integer');
    }
}
