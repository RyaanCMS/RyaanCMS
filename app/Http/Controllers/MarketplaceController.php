<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceInstallation;
use App\Models\MarketplaceItem;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Services\Module\ModuleInstaller;
use App\Services\Module\ModuleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MarketplaceController extends Controller
{
    public function __construct(
        private ModuleRegistry $moduleRegistry,
        private ModuleInstaller $moduleInstaller,
    ) {}

    public function index(Request $request)
    {
        $query = MarketplaceItem::published()->with('developer');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items      = $query->latest()->paginate(12)->withQueryString();
        $featured   = MarketplaceItem::published()->featured()->limit(4)->get();
        $categories = config('ryaan.marketplace_categories');

        // Built-in module registry data for the Modules tab
        $modules  = $this->moduleRegistry->all();
        $agents   = $this->moduleRegistry->agents();
        $projects = Auth::user()->projects()->select('id', 'name')->orderBy('name')->get();

        // Track which modules are already installed across user projects (with status + project name)
        $installedKeys = ProjectModule::whereIn(
            'project_id', $projects->pluck('id')
        )->pluck('module_key')->unique()->values();

        // Richer map: module_key => [{project_id, project_name, status, pm_id}]
        $installedModules = ProjectModule::whereIn('project_id', $projects->pluck('id'))
            ->with('project:id,name')
            ->get()
            ->groupBy('module_key')
            ->map(fn($rows) => $rows->map(fn($r) => [
                'pm_id'        => $r->id,
                'project_id'   => $r->project_id,
                'project_name' => $r->project->name ?? '—',
                'status'       => $r->status,
                'active'       => in_array($r->status, ['installed', 'active']),
            ])->values());

        return view('marketplace.index', compact(
            'items', 'featured', 'categories', 'modules', 'agents', 'projects', 'installedKeys', 'installedModules'
        ));
    }

    // ── Built-in Module Install ──────────────────────────────────────────────

    public function modules()
    {
        $modules  = $this->moduleRegistry->byCategory();
        $agents   = $this->moduleRegistry->agents();
        $projects = Auth::user()->projects()->select('id', 'name')->get();
        return view('marketplace.modules', compact('modules', 'agents', 'projects'));
    }

    public function installModule(Request $request, string $key)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);

        $project = Project::where('id', $request->project_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result = $this->moduleInstaller->install($project, $key);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'] ?? 'Install failed'], 422);
        }

        $menuItems  = $result['menu_items'] ?? [];
        $menuNote   = count($menuItems)
            ? ' Add ' . count($menuItems) . ' nav item(s) from the menu guide below.'
            : '';

        return response()->json([
            'success'    => true,
            'message'    => "✅ {$result['module']['name']} installed — {$result['file_count']} files generated (0 AI tokens).{$menuNote}",
            'file_count' => $result['file_count'],
            'ai_tokens'  => 0,
            'files'      => $result['files'],
            'menu_items' => $menuItems,
        ]);
    }

    public function toggleModule(Request $request, string $key)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);

        $project = Project::where('id', $request->project_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $pm = ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->firstOrFail();

        $newStatus = in_array($pm->status, ['active', 'installed']) ? 'inactive' : 'active';
        $pm->update(['status' => $newStatus]);

        $module = $this->moduleRegistry->get($key);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
            'active'  => $newStatus === 'active',
            'message' => ($module['name'] ?? $key) . ' is now ' . $newStatus . '.',
        ]);
    }

    public function uninstallModule(Request $request, string $key)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);

        $project = Project::where('id', $request->project_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        ProjectModule::where('project_id', $project->id)
            ->where('module_key', $key)
            ->delete();

        $module = $this->moduleRegistry->get($key);

        return response()->json([
            'success' => true,
            'message' => ($module['name'] ?? $key) . ' uninstalled from ' . $project->name . '.',
        ]);
    }

    public function toggleInstallation(MarketplaceInstallation $installation)
    {
        abort_unless($installation->user_id === Auth::id(), 403);

        $newStatus = $installation->status === 'active' ? 'inactive' : 'active';
        $installation->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
            'active'  => $newStatus === 'active',
            'message' => ($installation->item->name ?? 'Item') . ' is now ' . $newStatus . '.',
        ]);
    }

    public function uninstallInstallation(MarketplaceInstallation $installation)
    {
        abort_unless($installation->user_id === Auth::id(), 403);

        $name = $installation->item->name ?? 'Item';
        $installation->delete();

        return response()->json([
            'success' => true,
            'message' => $name . ' has been uninstalled.',
        ]);
    }

    public function agents()
    {
        $agents   = $this->moduleRegistry->agents();
        $projects = Auth::user()->projects()->select('id', 'name')->get();
        return view('marketplace.agents', compact('agents', 'projects'));
    }

    public function show(MarketplaceItem $item)
    {
        if (!$item->is_published) abort(404);

        $item->load('developer', 'reviews.user');
        $related = MarketplaceItem::published()
            ->where('category', $item->category)
            ->where('id', '!=', $item->id)
            ->limit(4)
            ->get();

        return view('marketplace.show', compact('item', 'related'));
    }

    public function install(Request $request, MarketplaceItem $item)
    {
        $request->validate(['project_id' => ['required', 'exists:projects,id']]);

        $project = Project::where('id', $request->project_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $installation = $project->marketplaceInstallations()->updateOrCreate(
            ['marketplace_item_id' => $item->id],
            [
                'user_id'     => Auth::id(),
                'version'     => $item->version,
                'status'      => 'installed',
                'license_key' => (string) Str::uuid(),
                'domain'      => $request->getHost(),
                'activated_at'=> now(),
            ]
        );

        $item->increment('downloads');

        return back()->with('success', "{$item->name} installed. License: {$installation->license_key}");
    }

    // ── Upload & install a downloaded .zip package ──────────────────────────

    public function uploadInstallForm()
    {
        $projects = Auth::user()->projects()->orderBy('name')->get();
        $installed = MarketplaceInstallation::where('user_id', Auth::id())
            ->with(['item', 'project'])
            ->latest()
            ->get();

        return view('marketplace.upload-install', compact('projects', 'installed'));
    }

    public function uploadInstall(Request $request)
    {
        $request->validate([
            'package'    => ['required', 'file', 'mimes:zip', 'max:51200'], // 50 MB
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        $project = Project::where('id', $request->project_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $file    = $request->file('package');
        $tmpPath = $file->getPathname();

        // Extract manifest from zip
        $zip = new ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            return back()->withErrors(['package' => 'Invalid or corrupted zip file.']);
        }

        $manifestJson = $zip->getFromName('ryaan-manifest.json');
        $zip->close();

        if (!$manifestJson) {
            return back()->withErrors(['package' => 'Not a valid RyaanCMS package (missing ryaan-manifest.json).']);
        }

        $manifest = json_decode($manifestJson, true);
        if (!$manifest || empty($manifest['name']) || empty($manifest['license_key'])) {
            return back()->withErrors(['package' => 'Package manifest is invalid or missing required fields.']);
        }

        $licenseKey  = $manifest['license_key'];
        $currentHost = $request->getHost();

        // Domain-locking check
        $existing = MarketplaceInstallation::where('license_key', $licenseKey)->first();
        if ($existing) {
            if ($existing->domain && $existing->domain !== $currentHost) {
                return back()->withErrors([
                    'package' => "This license key is already activated on domain [{$existing->domain}]. "
                               . "A package can only be used on one domain.",
                ]);
            }
            $installation = $existing;
        } else {
            $installation = new MarketplaceInstallation();
        }

        $storedPath = $file->store('packages', 'local');
        $item = MarketplaceItem::where('slug', $manifest['slug'] ?? '')->first();

        $installation->fill([
            'user_id'              => Auth::id(),
            'project_id'           => $project->id,
            'marketplace_item_id'  => $item?->id ?? 0,
            'version'              => $manifest['version'] ?? '1.0.0',
            'status'               => 'active',
            'license_key'          => $licenseKey,
            'domain'               => $currentHost,
            'activated_at'         => now(),
            'package_path'         => $storedPath,
        ]);
        $installation->save();

        if ($item) $item->increment('downloads');

        return redirect()->route('marketplace.upload-install')
            ->with('success', "✅ \"{$manifest['name']}\" installed and activated on {$currentHost}.");
    }

    // ── Activate an existing installation with a license key ─────────────────

    public function activate(Request $request, MarketplaceInstallation $installation)
    {
        abort_unless($installation->user_id === Auth::id(), 403);

        $request->validate(['license_key' => ['required', 'string']]);

        if ($installation->license_key !== $request->license_key) {
            return back()->withErrors(['license_key' => 'Invalid license key.']);
        }

        $currentHost = $request->getHost();

        if ($installation->domain && $installation->domain !== $currentHost) {
            return back()->withErrors([
                'license_key' => "This license is locked to [{$installation->domain}] and cannot be activated on [{$currentHost}].",
            ]);
        }

        $installation->update([
            'domain'       => $currentHost,
            'activated_at' => now(),
            'status'       => 'active',
        ]);

        return back()->with('success', 'License activated successfully on '.$currentHost.'.');
    }

    // ── List all installed items for the current user ────────────────────────

    public function installed()
    {
        $installed = MarketplaceInstallation::where('user_id', Auth::id())
            ->with(['item', 'project'])
            ->latest()
            ->paginate(20);

        return view('marketplace.installed', compact('installed'));
    }

    // ── My Items (developer view) ─────────────────────────────────────────────

    public function myItems()
    {
        $items = Auth::user()->marketplaceItems()->latest()->paginate(10);
        return view('marketplace.my-items', compact('items'));
    }

    public function submitItem(Request $request)
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['required', 'string', 'max:500'],
            'category'         => ['required', 'string'],
            'type'             => ['required', 'string'],
            'price'            => ['nullable', 'numeric', 'min:0'],
            'version'          => ['required', 'string'],
            'icon'             => ['nullable', 'string', 'max:10'],
            'demo_url'         => ['nullable', 'url', 'max:255'],
            'package_file'     => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $packagePath = null;
        if ($request->hasFile('package_file')) {
            $packagePath = $request->file('package_file')->store('marketplace-submissions', 'local');
        }

        Auth::user()->marketplaceItems()->create([
            'name'                => $request->name,
            'slug'                => Str::slug($request->name).'-'.uniqid(),
            'description'         => $request->description,
            'category'            => $request->category,
            'type'                => $request->type,
            'price'               => $request->price ?? 0,
            'is_free'             => !$request->price || $request->price == 0,
            'version'             => $request->version,
            'is_published'        => false,
            'status'              => 'pending',
            'icon'                => $request->icon ?? '📦',
            'demo_url_submission' => $request->demo_url,
            'package_file'        => $packagePath,
        ]);

        return redirect()->route('marketplace.my-items')
            ->with('success', '✅ Item submitted for review. You\'ll be notified once it\'s approved.');
    }

    // ── Admin Approval Panel ──────────────────────────────────────────────────

    public function adminPanel(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $filter       = $request->get('filter', 'pending');
        $pendingCount = MarketplaceItem::where('status', 'pending')->count();

        // Built-in Templates view — show TemplateRegistry with usage stats
        if ($filter === 'templates') {
            $registry     = app(\App\Services\Template\TemplateRegistry::class);
            $allTemplates = $registry->all();

            $usage = \DB::table('project_modules')
                ->where('module_key', 'like', 'template.%')
                ->select(
                    'module_key',
                    \DB::raw('count(*) as install_count'),
                    \DB::raw('sum(status = "active") as active_count')
                )
                ->groupBy('module_key')
                ->get()
                ->keyBy('module_key');

            return view('marketplace.admin', [
                'filter'       => $filter,
                'pendingCount' => $pendingCount,
                'items'        => collect(),
                'allTemplates' => $allTemplates,
                'usage'        => $usage,
            ]);
        }

        $query = MarketplaceItem::with('developer')->latest();
        if ($filter === 'pending')  $query->where('status', 'pending');
        elseif ($filter === 'approved') $query->where('status', 'approved');
        elseif ($filter === 'rejected') $query->where('status', 'rejected');

        $items = $query->paginate(20)->withQueryString();

        return view('marketplace.admin', [
            'filter'       => $filter,
            'pendingCount' => $pendingCount,
            'items'        => $items,
            'allTemplates' => [],
            'usage'        => collect(),
        ]);
    }

    public function approveItem(MarketplaceItem $item)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $item->update([
            'status'       => 'approved',
            'is_published' => true,
            'published_at' => now(),
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "\"{$item->name}\" approved and published to the marketplace.",
        ]);
    }

    public function rejectItem(Request $request, MarketplaceItem $item)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $item->update([
            'status'           => 'rejected',
            'is_published'     => false,
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "\"{$item->name}\" rejected.",
        ]);
    }
}
