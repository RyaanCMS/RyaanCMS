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
    private const CORE_CMS_PROJECT_SLUG = 'core-cms';

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
        $projects  = Auth::user()->projects()->select('id', 'name', 'slug')->orderBy('name')->get();
        $mainTemplateKey = Setting::get('system.public_site_template_key', self::DEFAULT_TEMPLATE);
        $coreCmsProjectId = $projects->firstWhere('slug', self::CORE_CMS_PROJECT_SLUG)?->id;

        // Map: module_key => [project_id => status]
        $installedMap = ProjectModule::whereIn('project_id', $projects->pluck('id'))
            ->where('module_key', 'like', 'template.%')
            ->get()
            ->groupBy('module_key')
            ->map(fn($rows) => $rows->keyBy('project_id')->map(fn($r) => $r->status));

        return view('marketplace.templates', compact('templates', 'projects', 'installedMap', 'mainTemplateKey', 'coreCmsProjectId'));
    }

    public function activateMain(Request $request, string $key)
    {
        $template = $this->registry->get($key);
        if (!$template || !($template['is_global'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'Main website template not found.'], 404);
        }

        Setting::set('system.public_site_template_key', $key);
        $this->clearPublicSiteProject();

        return response()->json([
            'success' => true,
            'status' => 'active',
            'message' => "{$template['name']} is now active on your main website.",
            'url' => route('home'),
        ]);
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
            ->first();

        if ($exists) {
            if ($this->isCoreCmsProject($project)) {
                return $this->activate($request, $project, $key);
            }

            return response()->json(['success' => false, 'message' => 'Already installed.']);
        }

        ProjectModule::create([
            'project_id' => $project->id,
            'module_key' => $key,
            'status'     => 'installed',
        ]);

        if ($this->isCoreCmsProject($project)) {
            return $this->activate($request, $project, $key);
        }

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
        $isCoreCms = $this->isCoreCmsProject($project);

        if ($isCoreCms) {
            $this->clearMainWebsiteTemplate();
            $this->publishProjectOnRootDomain($project);
        }

        return response()->json([
            'success' => true,
            'status'  => 'active',
            'message' => $isCoreCms
                ? "{$template['name']} is now live on the main CMS website."
                : "{$template['name']} is active for {$project->name}.",
            'url'     => $isCoreCms ? route('home') : route('site.serve', $project),
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

    // ── Auth: Templates management page (WordPress-like) ─────────────────────
    public function manage()
    {
        $user      = Auth::user();
        $templates = $this->registry->all();

        // Resolve the public-site project
        $publicProjectId = (int) Setting::get('system.public_site_project_id', 0);
        $siteProject     = $publicProjectId ? Project::find($publicProjectId) : null;

        $activeKey    = null;
        $statusMap    = []; // key => 'active'|'installed'|'available'

        if ($siteProject && $siteProject->user_id === $user->id) {
            $modules = ProjectModule::where('project_id', $siteProject->id)
                ->where('module_key', 'like', 'template.%')
                ->get()->keyBy('module_key');

            foreach ($templates as $key => $tpl) {
                $mod = $modules->get($key);
                $statusMap[$key] = $mod ? $mod->status : 'available';
                if ($mod && $mod->status === 'active') {
                    $activeKey = $key;
                }
            }
        } else {
            foreach ($templates as $key => $tpl) {
                $statusMap[$key] = 'available';
            }
        }

        $userProjects = $user->projects()->orderBy('name')->get(['id', 'name']);

        return view('templates.manage', compact('templates', 'siteProject', 'activeKey', 'statusMap', 'userProjects'));
    }

    // ── Auth: activate template on the public-site project ────────────────────
    public function activateForSite(Request $request, string $key)
    {
        $template = $this->registry->get($key);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $publicProjectId = (int) Setting::get('system.public_site_project_id', 0);
        if (!$publicProjectId) {
            return response()->json(['success' => false, 'message' => 'No public site project configured. Go to Settings → System Config to set one.'], 422);
        }

        $project = Project::find($publicProjectId);
        if (!$project || $project->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Site project not found or access denied.'], 403);
        }

        return $this->activate($request, $project, $key);
    }

    // ── Auth: deactivate template on the public-site project ─────────────────
    public function deactivateForSite(Request $request, string $key)
    {
        $publicProjectId = (int) Setting::get('system.public_site_project_id', 0);
        if (!$publicProjectId) {
            return response()->json(['success' => false, 'message' => 'No public site project configured.'], 422);
        }

        $project = Project::find($publicProjectId);
        if (!$project || $project->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        return $this->deactivate($request, $project, $key);
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

        $this->activateDefaultMainWebsiteTemplate();
    }

    private function isCoreCmsProject(Project $project): bool
    {
        return $project->slug === self::CORE_CMS_PROJECT_SLUG
            || (bool) data_get($project->settings, 'is_core_cms', false);
    }

    private function activateDefaultMainWebsiteTemplate(): void
    {
        Setting::set('system.public_site_template_key', self::DEFAULT_TEMPLATE);
        $this->clearPublicSiteProject();
    }

    private function clearMainWebsiteTemplate(): void
    {
        Setting::whereNull('user_id')
            ->where('group', 'system')
            ->where('key', 'public_site_template_key')
            ->delete();
    }

    private function clearPublicSiteProject(): void
    {
        Setting::whereNull('user_id')
            ->where('group', 'system')
            ->where('key', 'public_site_project_id')
            ->delete();
    }
}
