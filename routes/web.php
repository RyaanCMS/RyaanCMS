<?php

use App\Http\Controllers\AIBuilderController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\WisdomController;
use Illuminate\Support\Facades\Route;

// Installation Wizard (no auth, no installed-check)
Route::get('/install',           [InstallController::class, 'welcome'])->name('install.welcome');
Route::get('/install/database',  [InstallController::class, 'database'])->name('install.database');
Route::post('/install/database', [InstallController::class, 'saveDatabase'])->name('install.database.save');
Route::get('/install/migrate',   [InstallController::class, 'migrate'])->name('install.migrate');
Route::post('/install/migrate',  [InstallController::class, 'runMigrate'])->name('install.migrate.run');
Route::get('/install/admin',     [InstallController::class, 'admin'])->name('install.admin');
Route::post('/install/admin',    [InstallController::class, 'saveAdmin'])->name('install.admin.save');
Route::get('/install/complete',  [InstallController::class, 'complete'])->name('install.complete');

// Public site serving — active template for a project (no auth required)
Route::get('/site/{project}', [TemplateController::class, 'serve'])->name('site.serve');

// Welcome / Landing Page — serves the active public site template if configured
Route::get('/', function () {
    $mainTemplateKey = \App\Models\Setting::get('system.public_site_template_key');
    if ($mainTemplateKey) {
        $template = app(\App\Services\Template\TemplateRegistry::class)->get($mainTemplateKey);

        if ($template && ($template['is_global'] ?? false) && view()->exists($template['view'])) {
            return view($template['view'], compact('template'));
        }
    }

    $project = null;
    $projectId = \App\Models\Setting::get('system.public_site_project_id');

    if ($projectId) {
        $project = \App\Models\Project::find((int) $projectId);

        $hasActiveTemplate = $project && \App\Models\ProjectModule::where('project_id', $project->id)
            ->where('module_key', 'like', 'template.%')
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveTemplate) {
            $project = null;
        }
    }

    if (!$project) {
        $activeTemplate = \App\Models\ProjectModule::with('project')
            ->where('module_key', 'like', 'template.%')
            ->where('status', 'active')
            ->latest('updated_at')
            ->first();

        $project = $activeTemplate?->project;
    }

    if ($project) {
        return app(\App\Http\Controllers\TemplateController::class)->serve($project);
    }

    return view('templates.ryaancms');
})->name('home');

// Legal Pages
Route::get('/terms', fn() => view('pages.terms'))->name('terms');
Route::get('/privacy', fn() => view('pages.privacy'))->name('privacy');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/',                   [ProjectController::class, 'index'])->name('index');
        Route::get('/create',             [ProjectController::class, 'create'])->name('create');
        Route::post('/',                  [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}',          [ProjectController::class, 'show'])->name('show');
        Route::put('/{project}/settings', [ProjectController::class, 'updateSettings'])->name('update-settings');
        Route::delete('/{project}',       [ProjectController::class, 'destroy'])->name('destroy');
    });

    // Autonomous Pipeline
    Route::prefix('pipeline')->name('pipeline.')->group(function () {
        Route::get('/{project}',                         [PipelineController::class, 'show'])->name('show');
        Route::post('/{project}/start',                  [PipelineController::class, 'start'])->name('start')->middleware('throttle:3,1');
        Route::get('/{project}/runs/{run}/stream',       [PipelineController::class, 'stream'])->name('stream');
        Route::get('/{project}/runs/{run}/poll',         [PipelineController::class, 'poll'])->name('poll');
        Route::get('/{project}/runs/{run}/status',       [PipelineController::class, 'status'])->name('status');
        Route::delete('/{project}/runs/{run}',           [PipelineController::class, 'destroy'])->name('destroy');
    });

    // AI Builder
    Route::prefix('builder')->name('builder.')->group(function () {
        Route::get('/domain-packs',                       [AIBuilderController::class, 'domainPacks'])->name('domain_packs')->middleware('throttle:60,1');
        Route::get('/components',                         [AIBuilderController::class, 'components'])->name('components')->middleware('throttle:60,1');
        Route::get('/{project}',                          [AIBuilderController::class, 'show'])->name('show');
        // AI API calls — throttled to 20 requests/minute per user to prevent credit abuse
        Route::post('/{project}/chat',                    [AIBuilderController::class, 'chat'])->name('chat')->middleware('throttle:20,1');
        Route::post('/{project}/stream',                  [AIBuilderController::class, 'streamChat'])->name('stream')->middleware('throttle:20,1');
        Route::get('/{project}/files/{file}',             [AIBuilderController::class, 'getFile'])->name('file.get');
        Route::put('/{project}/files/{file}',             [AIBuilderController::class, 'saveFile'])->name('file.save')->middleware('throttle:90,1');
        Route::post('/{project}/files',                   [AIBuilderController::class, 'createFile'])->name('file.create')->middleware('throttle:60,1');
        Route::delete('/{project}/files/{file}',          [AIBuilderController::class, 'deleteFile'])->name('file.delete')->middleware('throttle:60,1');
        Route::post('/{project}/conversations',           [AIBuilderController::class, 'newConversation'])->name('conversation.new')->middleware('throttle:30,1');
        Route::get('/{project}/preview',                  [AIBuilderController::class, 'previewHtml'])->name('preview');
        // Blueprint-Driven Development endpoints (near-zero AI cost)
        Route::post('/{project}/discover',                [AIBuilderController::class, 'discover'])->name('discover')->middleware('throttle:10,1');
        Route::get('/{project}/blueprint',                [AIBuilderController::class, 'getBlueprint'])->name('blueprint.get');
        Route::post('/{project}/generate-crud',           [AIBuilderController::class, 'generateCrud'])->name('crud.generate')->middleware('throttle:20,1');
        // Component Registry (zero AI cost)
        Route::post('/{project}/components/{key}/insert', [AIBuilderController::class, 'insertComponent'])->name('component.insert')->middleware('throttle:40,1');
        // Smart Questions Engine
        Route::get('/{project}/smart-questions',          [AIBuilderController::class, 'smartQuestions'])->name('smart_questions')->middleware('throttle:60,1');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',                              [SettingsController::class, 'index'])->name('index');
        Route::put('/profile',                       [SettingsController::class, 'updateProfile'])->name('profile');
        Route::put('/password',                      [SettingsController::class, 'updatePassword'])->name('password');
        Route::put('/preferences',                   [SettingsController::class, 'updatePreferences'])->name('preferences');
        Route::post('/branding',                     [SettingsController::class, 'saveBranding'])->name('branding');
        Route::post('/branding/upload',              [SettingsController::class, 'uploadBrandingAsset'])->name('branding.upload');
        Route::post('/ai-providers',                                       [SettingsController::class, 'saveAIProvider'])->name('ai-provider.save');
        Route::delete('/ai-providers/{aiProvider}',                        [SettingsController::class, 'deleteAIProvider'])->name('ai-provider.delete');
        Route::post('/ai-providers/test',                                  [SettingsController::class, 'testAIProvider'])->name('ai-provider.test');
        // Per-provider key management (multiple keys / failover)
        Route::post('/ai-providers/{aiProvider}/keys',                     [SettingsController::class, 'addProviderKey'])->name('ai-provider.key.add');
        Route::delete('/ai-providers/{aiProvider}/keys/{key}',             [SettingsController::class, 'deleteProviderKey'])->name('ai-provider.key.delete');
        Route::patch('/ai-providers/{aiProvider}/keys/{key}/primary',      [SettingsController::class, 'setPrimaryProviderKey'])->name('ai-provider.key.primary');
        Route::patch('/ai-providers/{aiProvider}/keys/{key}/toggle',       [SettingsController::class, 'toggleProviderKey'])->name('ai-provider.key.toggle');
        Route::patch('/ai-providers/{aiProvider}/toggle-active',           [SettingsController::class, 'toggleAIProviderActive'])->name('ai-provider.toggle-active');
        Route::post('/system-config',                                      [SettingsController::class, 'saveSystemConfig'])->name('system-config');
        Route::post('/profile/avatar',                                     [SettingsController::class, 'uploadAvatar'])->name('profile.avatar');
        Route::post('/branding/global',                                    [SettingsController::class, 'saveBrandingGlobal'])->name('branding.global');
        // Team CRUD
        Route::get('/team',                                                [SettingsController::class, 'teamIndex'])->name('team.index');
        Route::post('/team',                                               [SettingsController::class, 'storeTeamMember'])->name('team.store');
        Route::put('/team/{user}',                                         [SettingsController::class, 'updateTeamMember'])->name('team.update');
        Route::delete('/team/{user}',                                      [SettingsController::class, 'destroyTeamMember'])->name('team.destroy');

        // System Updates
        Route::get('/updates',              [UpdateController::class, 'index'])->name('updates');
        Route::post('/updates/check',       [UpdateController::class, 'check'])->name('updates.check');
        Route::post('/updates/apply',       [UpdateController::class, 'apply'])->name('updates.apply');
        Route::post('/updates/plugin/{key}', [UpdateController::class, 'applyPlugin'])->name('updates.plugin');
    });

    // Menu Category Management
    Route::prefix('menu-categories')->name('menu-categories.')->group(function () {
        Route::get('/',                         [MenuCategoryController::class, 'index'])->name('index');
        Route::post('/',                        [MenuCategoryController::class, 'store'])->name('store');
        Route::put('/{menuCategory}',           [MenuCategoryController::class, 'update'])->name('update');
        Route::delete('/{menuCategory}',        [MenuCategoryController::class, 'destroy'])->name('destroy');
    });

    // Menu Management
    Route::prefix('menus')->name('menus.')->group(function () {
        Route::get('/',                             [MenuController::class, 'index'])->name('index');
        Route::get('/create',                       [MenuController::class, 'create'])->name('create');
        Route::post('/',                            [MenuController::class, 'store'])->name('store');
        Route::get('/{menu}',                       [MenuController::class, 'edit'])->name('edit');
        Route::put('/{menu}',                       [MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}',                    [MenuController::class, 'destroy'])->name('destroy');
        Route::post('/{menu}/items',                [MenuController::class, 'storeItem'])->name('items.store');
        Route::put('/{menu}/items/{item}',          [MenuController::class, 'updateItem'])->name('items.update');
        Route::delete('/{menu}/items/{item}',       [MenuController::class, 'destroyItem'])->name('items.destroy');
        Route::post('/{menu}/items/reorder',        [MenuController::class, 'reorderItems'])->name('items.reorder');
    });

    // Intelligence Ledger (Wisdom Dashboard)
    Route::prefix('wisdom')->name('wisdom.')->group(function () {
        Route::get('/',              [WisdomController::class, 'index'])->name('index');
        Route::get('/stats',         [WisdomController::class, 'stats'])->name('stats');
        Route::get('/{wisdom}',      [WisdomController::class, 'show'])->name('show');
        Route::post('/{wisdom}/promote', [WisdomController::class, 'promote'])->name('promote');
        Route::delete('/{wisdom}',   [WisdomController::class, 'destroy'])->name('destroy');
    });

    // Marketplace
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/',                                  [MarketplaceController::class, 'index'])->name('index');
        Route::get('/my-items',                          [MarketplaceController::class, 'myItems'])->name('my-items');
        Route::post('/submit',                           [MarketplaceController::class, 'submitItem'])->name('submit');
        Route::get('/upload-install',                    [MarketplaceController::class, 'uploadInstallForm'])->name('upload-install');
        Route::post('/upload-install',                   [MarketplaceController::class, 'uploadInstall'])->name('upload-install.store');
        Route::get('/installed',                         [MarketplaceController::class, 'installed'])->name('installed');
        Route::post('/activate/{installation}',          [MarketplaceController::class, 'activate'])->name('activate');
        // Built-in Module Registry (zero AI cost)
        Route::get('/modules',                             [MarketplaceController::class, 'modules'])->name('modules');
        Route::post('/modules/{key}/install',              [MarketplaceController::class, 'installModule'])->name('module.install')->middleware('throttle:30,1');
        Route::patch('/modules/{key}/toggle',              [MarketplaceController::class, 'toggleModule'])->name('module.toggle');
        Route::delete('/modules/{key}/uninstall',          [MarketplaceController::class, 'uninstallModule'])->name('module.uninstall');
        // Marketplace installation management
        Route::patch('/installations/{installation}/toggle',  [MarketplaceController::class, 'toggleInstallation'])->name('installation.toggle');
        Route::delete('/installations/{installation}',         [MarketplaceController::class, 'uninstallInstallation'])->name('installation.uninstall');
        Route::get('/agents',                              [MarketplaceController::class, 'agents'])->name('agents');
        // Templates browser + ZIP download
        Route::get('/templates',                           [TemplateController::class, 'browse'])->name('templates');
        Route::post('/templates/{key}/activate-main',       [TemplateController::class, 'activateMain'])->name('template.activate-main');
        Route::get('/templates/{key}/download',            [TemplateController::class, 'download'])->name('template.download');
        // Admin approval panel (admin role only — enforced inside controller)
        Route::get('/admin/panel',                         [MarketplaceController::class, 'adminPanel'])->name('admin.panel');
        Route::post('/admin/{item}/approve',               [MarketplaceController::class, 'approveItem'])->name('admin.approve');
        Route::post('/admin/{item}/reject',                [MarketplaceController::class, 'rejectItem'])->name('admin.reject');
        // Must be last — catch-all wildcard
        Route::get('/{item}',                              [MarketplaceController::class, 'show'])->name('show');
        Route::post('/{item}/install',                     [MarketplaceController::class, 'install'])->name('install');
    });

    // Template install / activate / uninstall per project
    Route::prefix('projects/{project}/templates')->name('template.')->group(function () {
        Route::post('/{key}/install',   [TemplateController::class, 'install'])->name('install');
        Route::post('/{key}/activate',  [TemplateController::class, 'activate'])->name('activate');
        Route::post('/{key}/deactivate',[TemplateController::class, 'deactivate'])->name('deactivate');
        Route::delete('/{key}',         [TemplateController::class, 'uninstall'])->name('uninstall');
    });

    // Project packaging (developer export) + Unified Installed + WP-style management
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/{project}/package',          [ProjectController::class, 'packageForm'])->name('package');
        Route::post('/{project}/package/build',   [ProjectController::class, 'buildPackage'])->name('package.build');
        Route::get('/{project}/package/download', [ProjectController::class, 'downloadPackage'])->name('package.download');
        Route::get('/{project}/installed',        [ProjectController::class, 'installedPackages'])->name('installed');

        // WordPress-style management pages
        Route::get('/{project}/themes',           [ProjectController::class, 'themes'])->name('themes');
        Route::get('/{project}/plugins',          [ProjectController::class, 'plugins'])->name('plugins');
    });

});
