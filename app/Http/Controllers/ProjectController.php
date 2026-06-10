<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceInstallation;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectModule;
use App\Models\Setting;
use App\Services\Module\ModuleRegistry;
use App\Services\Template\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ProjectController extends Controller
{
    private const DEFAULT_TEMPLATE = 'template.ryaancms';

    public function index()
    {
        $projects = Auth::user()->projects()
            ->withCount('files')
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $types     = config('ryaan.project_types');
        $templates = config('ryaan.templates');
        return view('projects.create', compact('types', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'type'           => ['required', 'string'],
            'description'    => ['nullable', 'string', 'max:500'],
            'initial_prompt' => ['nullable', 'string', 'max:20000'],
            'build_type'     => ['nullable', 'string'],
            'template'       => ['nullable', 'string'],
        ]);

        $project = Auth::user()->projects()->create([
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description ?? $request->initial_prompt,
            'tech_stack'  => $this->getTechStack($request->type),
            'settings'    => ['build_type' => $request->build_type ?? 'full_stack'],
        ]);

        // Create initial README
        ProjectFile::create([
            'project_id' => $project->id,
            'name'       => 'README.md',
            'path'       => 'README.md',
            'type'       => 'file',
            'extension'  => 'md',
            'content'    => "# {$project->name}\n\n" . ($project->description ?? '') . "\n\nBuilt with RyaanCMS AI Builder.",
            'size'       => 0,
        ]);

        ProjectModule::create([
            'project_id' => $project->id,
            'module_key' => self::DEFAULT_TEMPLATE,
            'status'     => 'active',
        ]);

        if (!Setting::get('system.public_site_project_id')) {
            Setting::set('system.public_site_project_id', $project->id, 'integer');
        }

        // If an initial prompt was given, go straight to builder with autostart
        if ($request->filled('initial_prompt')) {
            $url = route('builder.show', $project)
                 . '?prompt=' . urlencode($request->initial_prompt)
                 . '&autostart=1';
            return redirect($url)->with('success', 'Project created! AI is starting...');
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created! Describe what you want to build.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $files = $project->files()->orderBy('path')->get();

        return view('projects.show', compact('project', 'files'));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    public function updateSettings(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'github_repo'    => ['nullable', 'string'],
            'github_branch'  => ['nullable', 'string'],
        ]);

        $project->update($request->only('name', 'description', 'github_repo', 'github_branch'));

        return back()->with('success', 'Project settings updated.');
    }

    // ── Developer: Package & Publish ─────────────────────────────────────────

    public function packageForm(Project $project)
    {
        abort_unless($project->user_id === Auth::id(), 403);
        $files      = $project->files()->where('type', 'file')->get();
        $categories = config('ryaan.marketplace_categories', []);
        return view('projects.package', compact('project', 'files', 'categories'));
    }

    public function buildPackage(Request $request, Project $project)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $request->validate([
            'pkg_name'    => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'version'     => ['required', 'string', 'max:20'],
            'type'        => ['required', 'string'],
            'category'    => ['required', 'string'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'icon'        => ['nullable', 'string', 'max:10'],
            'icon_color'  => ['nullable', 'string', 'max:20'],
            'menu_items'  => ['nullable', 'json'],
        ]);

        $slug        = Str::slug($request->pkg_name);
        $licenseKey  = (string) Str::uuid();
        $menuItems   = $request->menu_items ? json_decode($request->menu_items, true) : [];

        $manifest = [
            'ryaan_manifest' => '1.0',
            'name'           => $request->pkg_name,
            'slug'           => $slug,
            'version'        => $request->version,
            'type'           => $request->type,
            'category'       => $request->category,
            'description'    => $request->description,
            'author'         => Auth::user()->name,
            'author_email'   => Auth::user()->email,
            'icon'           => $request->icon ?? '🔌',
            'icon_color'     => $request->icon_color ?? '#6366f1',
            'price'          => (float) ($request->price ?? 0),
            'menu_items'     => $menuItems,
            'license_key'    => $licenseKey,
            'built_at'       => now()->toISOString(),
        ];

        // Build zip in memory
        $zipName    = $slug.'-'.$request->version.'.zip';
        $tmpZipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$zipName;

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['pkg_name' => 'Could not create zip file. Check server permissions.']);
        }

        // Add manifest
        $zip->addFromString('ryaan-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        // Add all project files
        $files = $project->files()->where('type', 'file')->get();
        foreach ($files as $f) {
            $zip->addFromString('src/'.ltrim($f->path, '/'), $f->content ?? '');
        }

        $zip->close();

        // Store zip and save path on project settings
        $storedPath = 'packages/'.$zipName;
        Storage::disk('local')->put($storedPath, file_get_contents($tmpZipPath));
        @unlink($tmpZipPath);

        $settings = $project->settings ?? [];
        $settings['package_file']    = $storedPath;
        $settings['package_manifest'] = $manifest;
        $project->update(['settings' => $settings]);

        return redirect()->route('projects.package', $project)
            ->with('success', "Package \"{$zipName}\" built successfully. License key: {$licenseKey}");
    }

    public function downloadPackage(Project $project)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $storedPath = $project->settings['package_file'] ?? null;
        if (!$storedPath || !Storage::disk('local')->exists($storedPath)) {
            return back()->withErrors(['error' => 'No package built yet. Build the package first.']);
        }

        $fullPath = Storage::disk('local')->path($storedPath);
        return response()->download($fullPath, basename($storedPath));
    }

    // ── Unified Installed Packages ───────────────────────────────────────────
    public function installedPackages(Project $project)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $projectModules = ProjectModule::where('project_id', $project->id)->get();

        $templates     = $projectModules->filter(fn($m) => str_starts_with($m->module_key, 'template.'));
        $modules       = $projectModules->filter(fn($m) => !str_starts_with($m->module_key, 'template.'));
        $installations = MarketplaceInstallation::where('project_id', $project->id)
            ->with(['item', 'purchase'])
            ->latest()
            ->get();

        $allTemplates = app(TemplateRegistry::class)->all();
        $allModules   = app(ModuleRegistry::class)->all();

        return view('projects.installed', compact(
            'project', 'templates', 'modules', 'installations', 'allTemplates', 'allModules'
        ));
    }

    // ── WordPress-style Themes page ─────────────────────────────────────────
    public function themes(Project $project)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $allTemplates     = app(TemplateRegistry::class)->all();
        $installedModules = ProjectModule::where('project_id', $project->id)
            ->where('module_key', 'like', 'template.%')
            ->get()
            ->keyBy('module_key');

        $activeTemplate     = $installedModules->first(fn($m) => $m->status === 'active');
        $installedKeys      = $installedModules->keys();
        $availableTemplates = collect($allTemplates)->filter(
            fn($t, $k) => !$installedKeys->contains($k)
        );

        return view('projects.themes', compact(
            'project', 'allTemplates', 'installedModules',
            'activeTemplate', 'availableTemplates'
        ));
    }

    // ── WordPress-style Plugins page ─────────────────────────────────────────
    public function plugins(Project $project)
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $allModules     = app(ModuleRegistry::class)->all();
        $installations  = MarketplaceInstallation::where('project_id', $project->id)
            ->with(['item', 'purchase'])->latest()->get();
        $projectModules = ProjectModule::where('project_id', $project->id)
            ->where('module_key', 'not like', 'template.%')
            ->get()
            ->keyBy('module_key');

        return view('projects.plugins', compact(
            'project', 'allModules', 'projectModules', 'installations'
        ));
    }

    protected function getTechStack(string $type): array
    {
        return match($type) {
            'laravel'   => ['PHP', 'Laravel', 'MySQL', 'Tailwind CSS', 'Alpine.js'],
            'react'     => ['React', 'JavaScript', 'Tailwind CSS', 'Vite'],
            'nextjs'    => ['Next.js', 'React', 'TypeScript', 'Tailwind CSS'],
            'static'    => ['HTML', 'CSS', 'JavaScript'],
            'saas'      => ['PHP', 'Laravel', 'MySQL', 'React', 'Tailwind CSS'],
            'ecommerce' => ['PHP', 'Laravel', 'MySQL', 'Stripe', 'Tailwind CSS'],
            'crm'       => ['PHP', 'Laravel', 'MySQL', 'Livewire', 'Tailwind CSS'],
            'erp'       => ['PHP', 'Laravel', 'MySQL', 'Livewire', 'Alpine.js'],
            'api'       => ['PHP', 'Laravel', 'MySQL', 'Sanctum'],
            default     => ['PHP', 'Laravel', 'MySQL'],
        };
    }
}
