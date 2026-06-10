<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\Setting;
use App\Services\Template\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    private const DEFAULT_TEMPLATE = 'template.ryaancms';
    private const CORE_CMS_PROJECT_SLUG = 'core-cms';
    private const MAX_UPLOADED_TEMPLATE_CHARS = 500000;

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
            $uploaded = $this->uploadedTemplateFromModule($active);
            if ($uploaded) {
                return Blade::render($uploaded['content'], [
                    'project'  => $project,
                    'template' => $uploaded['template'],
                ]);
            }

            return view('templates.not-active', compact('project'));
        }

        return view($template['view'], compact('project', 'template'));
    }

    // ── Auth: browse all templates in marketplace ─────────────────────────────
    public function browse(Request $request)
    {
        $projects  = Auth::user()->projects()->select('id', 'name', 'slug')->orderBy('name')->get();
        $templates = array_replace($this->registry->all(), $this->uploadedTemplatesForProjects($projects));
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
    public function uploadInstall(Request $request)
    {
        $request->validate([
            'package'    => ['required', 'file', 'mimes:zip', 'max:51200'],
            'project_id' => ['required', 'exists:projects,id'],
            'activate'   => ['nullable', 'boolean'],
        ]);

        $project = Auth::user()->projects()->findOrFail($request->integer('project_id'));
        $zipPath = $request->file('package')->getRealPath();

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return back()->withErrors(['package' => 'Could not open the uploaded template ZIP.']);
        }

        $manifestRaw = $zip->getFromName('ryaan-manifest.json');
        if ($manifestRaw === false) {
            $zip->close();
            return back()->withErrors(['package' => 'Template ZIP must include ryaan-manifest.json.']);
        }

        $manifest = json_decode($manifestRaw, true);
        if (!is_array($manifest)) {
            $zip->close();
            return back()->withErrors(['package' => 'Template manifest is not valid JSON.']);
        }

        if (strtolower((string) ($manifest['type'] ?? 'template')) !== 'template') {
            $zip->close();
            return back()->withErrors(['package' => 'This ZIP is not a template package.']);
        }

        $viewContent = $this->extractTemplateViewFromZip($zip);
        $zip->close();

        if ($viewContent === null || trim($viewContent) === '') {
            return back()->withErrors(['package' => 'Template ZIP must include views/template.blade.php.']);
        }

        if (strlen($viewContent) > self::MAX_UPLOADED_TEMPLATE_CHARS) {
            return back()->withErrors(['package' => 'Template view is too large. Keep it under 500 KB.']);
        }

        $templateName = trim((string) ($manifest['name'] ?? pathinfo($request->file('package')->getClientOriginalName(), PATHINFO_FILENAME)));
        $templateName = $templateName !== '' ? $templateName : 'Uploaded Template';
        $baseSlug = Str::slug($manifest['key'] ?? $manifest['slug'] ?? $templateName) ?: 'uploaded-template';
        $hash = substr(sha1($viewContent), 0, 10);
        $moduleKey = 'template.uploaded.' . $baseSlug . '-' . $hash;
        $sourcePath = "uploaded-templates/{$project->user_id}/{$project->id}/{$baseSlug}-{$hash}.blade.php";

        Storage::disk('local')->put($sourcePath, $viewContent);

        $options = [
            'uploaded_template' => true,
            'source_path' => $sourcePath,
            'manifest' => $this->normalizeUploadedManifest($manifest, $moduleKey, $templateName),
        ];

        $projectModule = ProjectModule::updateOrCreate(
            ['project_id' => $project->id, 'module_key' => $moduleKey],
            ['status' => 'installed', 'options' => $options]
        );

        if ($request->boolean('activate', true)) {
            ProjectModule::where('project_id', $project->id)
                ->where('module_key', 'like', 'template.%')
                ->where('module_key', '!=', $moduleKey)
                ->update(['status' => 'installed']);

            $projectModule->update(['status' => 'active', 'options' => $options]);

            if ($this->isCoreCmsProject($project)) {
                $this->clearMainWebsiteTemplate();
                $this->publishProjectOnRootDomain($project);
            }
        }

        return redirect()
            ->route('marketplace.templates')
            ->with('success', "{$options['manifest']['name']} uploaded and installed.");
    }

    public function install(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $uploadedSource = $this->uploadedTemplateModule($key, $project) ?? $this->uploadedTemplateModule($key);
        $template = $this->registry->get($key) ?? ($uploadedSource ? $this->uploadedTemplateMetaFromModule($uploadedSource) : null);
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
            'options'    => $uploadedSource?->options,
        ]);

        if ($this->isCoreCmsProject($project)) {
            return $this->activate($request, $project, $key);
        }

        return response()->json([
            'success' => true,
            'message' => "{$template['name']} installed.",
        ]);
    }

    // ── Auth: activate a template (deactivates all others for this project) ───
    public function activate(Request $request, Project $project, string $key)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $uploadedSource = $this->uploadedTemplateModule($key, $project) ?? $this->uploadedTemplateModule($key);
        $template = $this->registry->get($key) ?? ($uploadedSource ? $this->uploadedTemplateMetaFromModule($uploadedSource) : null);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        // Install silently if not installed
        $pm = ProjectModule::firstOrCreate(
            ['project_id' => $project->id, 'module_key' => $key],
            ['status' => 'installed', 'options' => $uploadedSource?->options]
        );

        // Deactivate all other templates for this project
        ProjectModule::where('project_id', $project->id)
            ->where('module_key', 'like', 'template.%')
            ->where('module_key', '!=', $key)
            ->update(['status' => 'installed']);

        $update = ['status' => 'active'];
        if ($uploadedSource) {
            $update['options'] = $uploadedSource->options;
        }

        $pm->update($update);
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

        $uploadedSource = $this->uploadedTemplateModule($key, $project) ?? $this->uploadedTemplateModule($key);
        $template = $this->registry->get($key) ?? ($uploadedSource ? $this->uploadedTemplateMetaFromModule($uploadedSource) : null);

        ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->delete();

        $this->refreshRootDomainProject($project);

        return response()->json([
            'success' => true,
            'message' => ($template['name'] ?? $key) . ' uninstalled.',
        ]);
    }

    // ── Auth: download template as ZIP ────────────────────────────────────────
    public function download(string $key)
    {
        $uploadedSource = null;
        $viewContent = null;
        $template = $this->registry->get($key);

        if (!$template) {
            $uploadedSource = $this->uploadedTemplateModule($key);
            $template = $uploadedSource ? $this->uploadedTemplateMetaFromModule($uploadedSource) : null;
        }

        abort_if(!$template, 404);

        if ($uploadedSource) {
            $sourcePath = data_get($uploadedSource->options, 'source_path');
            abort_unless($sourcePath && Storage::disk('local')->exists($sourcePath), 404);
            $viewContent = Storage::disk('local')->get($sourcePath);
        }

        $viewFile = $uploadedSource ? null : resource_path('views/' . str_replace('.', '/', $template['view']) . '.blade.php');
        $slug     = Str::slug(str_replace('template.', '', $key)) ?: 'uploaded-template';
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

        if ($viewContent !== null) {
            $zip->addFromString('views/template.blade.php', $viewContent);
        } elseif ($viewFile && file_exists($viewFile)) {
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
            $templates = array_replace($templates, $this->uploadedTemplatesForProjects(collect([$siteProject])));

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
        $publicProjectId = (int) Setting::get('system.public_site_project_id', 0);
        if (!$publicProjectId) {
            return response()->json(['success' => false, 'message' => 'No public site project configured. Go to Settings → System Config to set one.'], 422);
        }

        $project = Project::find($publicProjectId);
        if (!$project || $project->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Site project not found or access denied.'], 403);
        }

        $template = $this->registry->get($key);
        if (!$template && !$this->uploadedTemplateModule($key, $project)) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
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

    private function uploadedTemplateFromModule(ProjectModule $module): ?array
    {
        $template = $this->uploadedTemplateMetaFromModule($module);
        $sourcePath = data_get($module->options, 'source_path');

        if (!$template || !$sourcePath || !Storage::disk('local')->exists($sourcePath)) {
            return null;
        }

        return [
            'template' => $template,
            'content' => Storage::disk('local')->get($sourcePath),
        ];
    }

    private function uploadedTemplateMetaFromModule(ProjectModule $module): ?array
    {
        if (!data_get($module->options, 'uploaded_template')) {
            return null;
        }

        $sourcePath = data_get($module->options, 'source_path');
        if (!$sourcePath || !Storage::disk('local')->exists($sourcePath)) {
            return null;
        }

        $manifest = data_get($module->options, 'manifest', []);
        if (!is_array($manifest)) {
            $manifest = [];
        }

        return [
            'key' => $module->module_key,
            'name' => (string) ($manifest['name'] ?? 'Uploaded Template'),
            'description' => (string) ($manifest['description'] ?? 'Uploaded custom website template.'),
            'category' => (string) ($manifest['category'] ?? 'Uploaded Template'),
            'type' => 'template',
            'icon' => (string) ($manifest['icon'] ?? 'T'),
            'color' => $this->normalizeUploadedColor($manifest['color'] ?? '#6366f1'),
            'view' => null,
            'tags' => $this->normalizeUploadedTags($manifest['tags'] ?? []),
            'version' => (string) ($manifest['version'] ?? '1.0.0'),
            'is_uploaded' => true,
            'is_global' => false,
        ];
    }

    private function uploadedTemplateModule(string $key, ?Project $project = null): ?ProjectModule
    {
        $query = ProjectModule::where('module_key', $key)
            ->where('module_key', 'like', 'template.uploaded.%');

        if ($project) {
            $query->where('project_id', $project->id);
        } else {
            $user = Auth::user();
            if (!$user) {
                return null;
            }

            $projectIds = $user->projects()->pluck('id');
            if ($projectIds->isEmpty()) {
                return null;
            }

            $query->whereIn('project_id', $projectIds);
        }

        return $query->latest('updated_at')
            ->get()
            ->first(fn($module) => (bool) data_get($module->options, 'uploaded_template'));
    }

    private function uploadedTemplatesForProjects($projects): array
    {
        $projectIds = $projects->pluck('id')->filter()->values();
        if ($projectIds->isEmpty()) {
            return [];
        }

        $templates = [];
        $modules = ProjectModule::whereIn('project_id', $projectIds)
            ->where('module_key', 'like', 'template.uploaded.%')
            ->latest('updated_at')
            ->get();

        foreach ($modules as $module) {
            if (isset($templates[$module->module_key])) {
                continue;
            }

            $template = $this->uploadedTemplateMetaFromModule($module);
            if ($template) {
                $templates[$module->module_key] = $template;
            }
        }

        return $templates;
    }

    private function normalizeUploadedManifest(array $manifest, string $moduleKey, string $templateName): array
    {
        return [
            'key' => $moduleKey,
            'name' => $templateName,
            'description' => (string) ($manifest['description'] ?? 'Uploaded custom website template.'),
            'category' => (string) ($manifest['category'] ?? 'Uploaded Template'),
            'type' => 'template',
            'icon' => (string) ($manifest['icon'] ?? 'T'),
            'color' => $this->normalizeUploadedColor($manifest['color'] ?? '#6366f1'),
            'tags' => $this->normalizeUploadedTags($manifest['tags'] ?? ['uploaded', 'template']),
            'version' => (string) ($manifest['version'] ?? '1.0.0'),
            'author' => (string) ($manifest['author'] ?? 'Uploaded'),
            'uploaded_at' => now()->toISOString(),
        ];
    }

    private function normalizeUploadedTags(mixed $tags): array
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        if (!is_array($tags)) {
            $tags = [];
        }

        $normalized = array_values(array_filter(array_map(
            fn($tag) => Str::limit(trim((string) $tag), 24, ''),
            $tags
        )));

        return $normalized ?: ['uploaded', 'template'];
    }

    private function normalizeUploadedColor(mixed $color): string
    {
        $color = trim((string) $color);

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)
            ? $color
            : '#6366f1';
    }

    private function extractTemplateViewFromZip(\ZipArchive $zip): ?string
    {
        foreach (['views/template.blade.php', 'template.blade.php'] as $path) {
            $content = $zip->getFromName($path);
            if ($content !== false) {
                return $content;
            }
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i);
            $name = (string) ($entry['name'] ?? '');

            if ($name === '' || str_ends_with($name, '/') || !str_ends_with($name, '.blade.php')) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                return $content;
            }
        }

        return null;
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
