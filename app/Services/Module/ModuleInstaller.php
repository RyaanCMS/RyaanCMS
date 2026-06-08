<?php

namespace App\Services\Module;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectModule;

class ModuleInstaller
{
    public function __construct(private ModuleRegistry $registry) {}

    public function install(Project $project, string $moduleKey): array
    {
        $module = $this->registry->get($moduleKey);
        if (!$module) {
            return ['success' => false, 'error' => "Module '{$moduleKey}' not found"];
        }

        // Auto-install missing dependencies first
        foreach ($module['dependencies'] as $dep) {
            if (!ProjectModule::where('project_id', $project->id)->where('module_key', $dep)->exists()) {
                $this->install($project, $dep);
            }
        }

        $files = $this->generate($moduleKey);
        $saved = [];
        foreach ($files as $file) {
            $record = ProjectFile::updateOrCreate(
                ['project_id' => $project->id, 'path' => $file['path']],
                [
                    'name'     => basename($file['path']),
                    'type'     => 'file',
                    'content'  => $file['content'],
                    'language' => $file['language'] ?? 'php',
                ]
            );
            $saved[] = ['path' => $record->path, 'id' => $record->id];
        }

        ProjectModule::updateOrCreate(
            ['project_id' => $project->id, 'module_key' => $moduleKey],
            ['status' => 'installed']
        );

        return [
            'success'    => true,
            'module'     => array_merge($module, ['key' => $moduleKey]),
            'files'      => $saved,
            'file_count' => count($saved),
            'ai_tokens'  => 0,
            'menu_items' => $module['menu_items'] ?? [],
        ];
    }

    private function generate(string $key): array
    {
        return match ($key) {
            'auth'          => $this->authFiles(),
            'rbac'          => $this->rbacFiles(),
            'payments'      => $this->paymentsFiles(),
            'notifications' => $this->notificationsFiles(),
            'reports'       => $this->reportsFiles(),
            'media'         => $this->mediaFiles(),
            'audit'         => $this->auditFiles(),
            'api'           => $this->apiFiles(),
            'inventory'     => $this->inventoryFiles(),
            'orders'        => $this->ordersFiles(),
            'subscriptions' => $this->subscriptionsFiles(),
            'multi_tenant'  => $this->multiTenantFiles(),
            default         => [],
        };
    }

    // ─── AUTH MODULE ─────────────────────────────────────────────────────────

    private function authFiles(): array
    {
        return [
            ['path' => 'app/Http/Controllers/Auth/LoginController.php',         'content' => $this->loginController()],
            ['path' => 'app/Http/Controllers/Auth/RegisterController.php',      'content' => $this->registerController()],
            ['path' => 'app/Http/Controllers/Auth/ForgotPasswordController.php','content' => $this->forgotPasswordController()],
            ['path' => 'resources/views/auth/login.blade.php',                  'content' => $this->loginView(),          'language' => 'blade'],
            ['path' => 'resources/views/auth/register.blade.php',               'content' => $this->registerView(),       'language' => 'blade'],
            ['path' => 'resources/views/auth/forgot-password.blade.php',        'content' => $this->forgotPasswordView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/auth_routes.php',                          'content' => $this->authRoutes()],
        ];
    }

    private function loginController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
PHP;
    }

    private function registerController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
PHP;
    }

    private function forgotPasswordController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
PHP;
    }

    private function loginView(): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-white mb-1">Welcome back</h1>
                <p class="text-gray-400 text-sm">Sign in to your account</p>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Password</label>
                        <input type="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 text-sm text-gray-400 cursor-pointer">
                            <input type="checkbox" name="remember"
                                   class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-indigo-600 focus:ring-indigo-500">
                            <span>Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}"
                           class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                            Forgot password?
                        </a>
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Sign in
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Sign up</a>
            </p>
        </div>
    </div>
</body>
</html>
BLADE;
    }

    private function registerView(): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-white mb-1">Create account</h1>
                <p class="text-gray-400 text-sm">Start building your next project</p>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm space-y-1">
                    @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Full name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Password</label>
                        <input type="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Confirm password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Create account
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
BLADE;
    }

    private function forgotPasswordView(): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-white mb-1">Reset password</h1>
                <p class="text-gray-400 text-sm">We'll send a reset link to your email</p>
            </div>

            @if(session('status'))
                <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-xl p-3 text-green-400 text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Send reset link
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Back to sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
BLADE;
    }

    private function authRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Auth Routes ─────────────────────────────────────────────────────────────
// Paste into routes/web.php (outside any middleware group)
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::middleware('guest')->group(function () {
    Route::get('/login',            [LoginController::class, 'showForm'])->name('login');
    Route::post('/login',           [LoginController::class, 'login']);
    Route::get('/register',         [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',        [RegisterController::class, 'register']);
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
PHP;
    }

    // ─── RBAC MODULE ─────────────────────────────────────────────────────────

    private function rbacFiles(): array
    {
        return [
            ['path' => 'app/Models/Role.php',                                            'content' => $this->roleModel()],
            ['path' => 'app/Models/Permission.php',                                      'content' => $this->permissionModel()],
            ['path' => 'app/Traits/HasRoles.php',                                        'content' => $this->hasRolesTrait()],
            ['path' => 'app/Http/Middleware/CheckPermission.php',                        'content' => $this->checkPermissionMiddleware()],
            ['path' => 'app/Http/Controllers/Admin/RoleController.php',                  'content' => $this->roleController()],
            ['path' => 'database/migrations/2024_01_01_100001_create_roles_table.php',          'content' => $this->rolesMigration()],
            ['path' => 'database/migrations/2024_01_01_100002_create_permissions_table.php',    'content' => $this->permissionsMigration()],
            ['path' => 'database/migrations/2024_01_01_100003_create_role_user_table.php',      'content' => $this->roleUserMigration()],
            ['path' => 'database/migrations/2024_01_01_100004_create_permission_role_table.php','content' => $this->permissionRoleMigration()],
            ['path' => 'resources/views/admin/roles/index.blade.php',                    'content' => $this->rolesIndexView(), 'language' => 'blade'],
        ];
    }

    private function roleModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }
}
PHP;
    }

    private function permissionModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'module'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
PHP;
    }

    private function hasRolesTrait(): string
    {
        return <<<'PHP'
<?php

namespace App\Traits;

use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($r) => $r->permissions)
            ->pluck('slug')
            ->contains($permission);
    }

    public function assignRole(string $slug): void
    {
        $role = Role::where('slug', $slug)->firstOrFail();
        $this->roles()->syncWithoutDetaching($role);
    }

    public function removeRole(string $slug): void
    {
        $role = Role::where('slug', $slug)->first();
        if ($role) $this->roles()->detach($role);
    }
}
PHP;
    }

    private function checkPermissionMiddleware(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user()?->hasPermission($permission)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Forbidden'], 403)
                : abort(403, 'You do not have permission to perform this action.');
        }
        return $next($request);
    }
}
PHP;
    }

    private function roleController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles       = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|unique:roles|max:100|regex:/^[a-z0-9_-]+$/',
            'description' => 'nullable|string|max:255',
        ]);
        $role = Role::create($data);
        if ($request->permissions) {
            $role->permissions()->sync(
                Permission::whereIn('slug', $request->permissions)->pluck('id')
            );
        }
        return back()->with('success', "Role '{$role->name}' created.");
    }

    public function update(Request $request, Role $role)
    {
        $role->update($request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]));
        if ($request->permissions !== null) {
            $role->permissions()->sync(
                Permission::whereIn('slug', $request->permissions)->pluck('id')
            );
        }
        return back()->with('success', "Role '{$role->name}' updated.");
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }
}
PHP;
    }

    private function rolesMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('roles'); }
};
PHP;
    }

    private function permissionsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('permissions'); }
};
PHP;
    }

    private function roleUserMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('role_user'); }
};
PHP;
    }

    private function permissionRoleMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('permission_role'); }
};
PHP;
    }

    private function rolesIndexView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')

<div class="max-w-5xl mx-auto space-y-6">
    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl p-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-4">Roles ({{ $roles->count() }})</h2>
            <div class="space-y-3">
                @foreach($roles as $role)
                <div class="flex items-center justify-between p-4 bg-gray-800/40 rounded-xl border border-gray-700/40">
                    <div>
                        <p class="font-medium text-white text-sm">{{ $role->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $role->users_count }} users &middot; {{ $role->permissions->count() }} permissions
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-400 text-xs rounded-full font-mono">{{ $role->slug }}</span>
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs transition-colors">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-4">New Role</h2>
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="space-y-3">
                    <input type="text" name="name" placeholder="Role name" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="slug" placeholder="role-slug" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                    <input type="text" name="description" placeholder="Description (optional)"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-xl text-sm font-medium transition-colors">
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
BLADE;
    }

    // ─── PAYMENTS MODULE ─────────────────────────────────────────────────────

    private function paymentsFiles(): array
    {
        return [
            ['path' => 'app/Models/Payment.php',                                  'content' => $this->paymentModel()],
            ['path' => 'app/Services/PaymentService.php',                         'content' => $this->paymentService()],
            ['path' => 'app/Http/Controllers/PaymentController.php',              'content' => $this->paymentController()],
            ['path' => 'database/migrations/2024_01_01_200001_create_payments_table.php', 'content' => $this->paymentsMigration()],
            ['path' => 'resources/views/payments/checkout.blade.php',             'content' => $this->checkoutView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/payment_routes.php',                         'content' => $this->paymentRoutes()],
        ];
    }

    private function paymentModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'payable_type', 'payable_id', 'gateway',
        'transaction_id', 'amount', 'currency', 'status', 'paid_at', 'metadata',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'paid_at'  => 'datetime',
        'metadata' => 'array',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function payable() { return $this->morphTo(); }

    public function scopePaid($q)    { return $q->where('status', 'paid'); }
    public function scopePending($q) { return $q->where('status', 'pending'); }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency ?? 'USD');
    }
}
PHP;
    }

    private function paymentService(): string
    {
        return <<<'PHP'
<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentService
{
    public function createPending(
        User $user,
        float $amount,
        string $currency = 'USD',
        string $gateway = 'stripe'
    ): Payment {
        return Payment::create([
            'user_id'        => $user->id,
            'gateway'        => $gateway,
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'amount'         => $amount,
            'currency'       => $currency,
            'status'         => 'pending',
        ]);
    }

    public function markPaid(Payment $payment, string $transactionId): Payment
    {
        $payment->update([
            'transaction_id' => $transactionId,
            'status'         => 'paid',
            'paid_at'        => now(),
        ]);
        return $payment->fresh();
    }

    public function markFailed(Payment $payment, string $reason = ''): Payment
    {
        $payment->update([
            'status'   => 'failed',
            'metadata' => array_merge($payment->metadata ?? [], ['failure_reason' => $reason]),
        ]);
        return $payment->fresh();
    }

    public function refund(Payment $payment): Payment
    {
        $payment->update(['status' => 'refunded']);
        return $payment->fresh();
    }
}
PHP;
    }

    private function paymentController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function checkout(Request $request)
    {
        return view('payments.checkout', [
            'amount'   => $request->float('amount', 0),
            'currency' => $request->string('currency', 'USD'),
        ]);
    }

    public function history()
    {
        $payments = auth()->user()->payments()->latest()->paginate(20);
        return view('payments.history', compact('payments'));
    }

    public function callback(Request $request, string $gateway)
    {
        // TODO: implement gateway-specific callback verification
        return redirect()->route('payments.history')->with('success', 'Payment processed.');
    }
}
PHP;
    }

    private function paymentsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('payable');
            $table->string('gateway');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};
PHP;
    }

    private function checkoutView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Checkout')
@section('content')

<div class="max-w-md mx-auto">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
        <h2 class="text-xl font-bold text-white mb-1">Complete Payment</h2>
        <p class="text-gray-400 text-sm mb-6">Choose a payment method to continue</p>

        <div class="bg-gray-800/60 rounded-xl p-4 mb-6 border border-gray-700/60">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Total amount</span>
                <span class="text-2xl font-bold text-white">{{ number_format($amount, 2) }} {{ $currency }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <button class="w-full flex items-center justify-center space-x-3 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl transition-colors font-medium text-sm">
                <span class="text-lg">💳</span><span>Pay with Stripe</span>
            </button>
            <button class="w-full flex items-center justify-center space-x-3 bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl transition-colors font-medium text-sm">
                <span class="text-lg">📱</span><span>Pay with bKash</span>
            </button>
            <button class="w-full flex items-center justify-center space-x-3 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl transition-colors font-medium text-sm">
                <span class="text-lg">🏦</span><span>Pay with SSLCommerz</span>
            </button>
        </div>

        <p class="mt-5 text-center text-xs text-gray-600">🔒 Payments are 256-bit encrypted and secure</p>
    </div>
</div>

@endsection
BLADE;
    }

    private function paymentRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Payment Routes ───────────────────────────────────────────────────────────
// Paste into routes/web.php inside auth middleware group
use App\Http\Controllers\PaymentController;

Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/checkout',            [PaymentController::class, 'checkout'])->name('checkout');
    Route::get('/history',             [PaymentController::class, 'history'])->name('history');
    Route::get('/callback/{gateway}',  [PaymentController::class, 'callback'])->name('callback');
});
PHP;
    }

    // ─── NOTIFICATIONS MODULE ────────────────────────────────────────────────

    private function notificationsFiles(): array
    {
        return [
            ['path' => 'app/Http/Controllers/NotificationController.php',          'content' => $this->notificationController()],
            ['path' => 'resources/views/components/notification-bell.blade.php',   'content' => $this->notificationBellComponent(), 'language' => 'blade'],
            ['path' => 'routes/stubs/notification_routes.php',                     'content' => $this->notificationRoutes()],
        ];
    }

    private function notificationController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function data()
    {
        $user = auth()->user();
        return response()->json([
            'notifications' => $user->notifications()->latest()->take(10)->get()->map(fn($n) => [
                'id'             => $n->id,
                'data'           => $n->data,
                'read_at'        => $n->read_at,
                'created_at_human' => $n->created_at->diffForHumans(),
            ]),
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        return response()->json(['count' => auth()->user()->unreadNotifications()->count()]);
    }

    public function destroy(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        return back();
    }
}
PHP;
    }

    private function notificationBellComponent(): string
    {
        return <<<'BLADE'
@auth
<div x-data="notificationBell()" x-init="init()" class="relative">
    <button @click="open = !open"
            class="relative p-2 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unread > 0" x-text="unread > 9 ? '9+' : unread"
              class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] leading-none rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" x-transition
         class="absolute right-0 mt-2 w-80 bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
            <span class="text-sm font-semibold text-white">Notifications</span>
            <button @click="markAllRead()" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                Mark all read
            </button>
        </div>
        <div class="max-h-72 overflow-y-auto divide-y divide-gray-800/60">
            <template x-for="n in notifications" :key="n.id">
                <div class="px-4 py-3 hover:bg-gray-800/40 transition-colors cursor-pointer"
                     :class="n.read_at ? 'opacity-50' : ''">
                    <p class="text-sm text-white leading-snug"
                       x-text="n.data.message || n.data.title || 'New notification'"></p>
                    <p class="text-xs text-gray-500 mt-1" x-text="n.created_at_human"></p>
                </div>
            </template>
            <div x-show="!notifications.length"
                 class="px-4 py-8 text-center text-sm text-gray-500">
                No notifications yet
            </div>
        </div>
        <div class="border-t border-gray-800 px-4 py-2.5">
            <a href="{{ route('notifications.index') }}"
               class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                View all notifications →
            </a>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        unread: 0,
        notifications: [],
        init() {
            this.fetch();
            // Poll every 30 seconds for new notifications
            setInterval(() => this.fetchCount(), 30000);
        },
        async fetch() {
            try {
                const res = await fetch('/notifications/data');
                const d   = await res.json();
                this.notifications = d.notifications;
                this.unread        = d.unread;
            } catch {}
        },
        async fetchCount() {
            try {
                const res = await fetch('/notifications/unread-count');
                const d   = await res.json();
                this.unread = d.count;
            } catch {}
        },
        async markAllRead() {
            try {
                await fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                });
                this.notifications.forEach(n => n.read_at = new Date().toISOString());
                this.unread = 0;
            } catch {}
        },
    };
}
</script>
@endauth
BLADE;
    }

    private function notificationRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Notification Routes ──────────────────────────────────────────────────────
// Paste into routes/web.php inside auth middleware group
use App\Http\Controllers\NotificationController;

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',               [NotificationController::class, 'index'])->name('index');
    Route::get('/data',           [NotificationController::class, 'data'])->name('data');
    Route::get('/unread-count',   [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read',     [NotificationController::class, 'markRead'])->name('read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    Route::delete('/{id}',        [NotificationController::class, 'destroy'])->name('destroy');
});
PHP;
    }

    // ─── AUDIT MODULE ────────────────────────────────────────────────────────

    private function auditFiles(): array
    {
        return [
            ['path' => 'app/Models/AuditLog.php',                                          'content' => $this->auditLogModel()],
            ['path' => 'app/Traits/Auditable.php',                                         'content' => $this->auditableTrait()],
            ['path' => 'app/Http/Middleware/LogActivity.php',                              'content' => $this->logActivityMiddleware()],
            ['path' => 'app/Http/Controllers/Admin/AuditLogController.php',                'content' => $this->auditLogController()],
            ['path' => 'database/migrations/2024_01_01_300001_create_audit_logs_table.php','content' => $this->auditLogsMigration()],
            ['path' => 'resources/views/admin/audit/index.blade.php',                      'content' => $this->auditIndexView(), 'language' => 'blade'],
        ];
    }

    private function auditLogModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()      { return $this->belongsTo(User::class); }
    public function auditable() { return $this->morphTo(); }
}
PHP;
    }

    private function auditableTrait(): string
    {
        return <<<'PHP'
<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn($m) => static::writeLog('created', $m, [], $m->getAttributes()));
        static::updated(fn($m) => static::writeLog('updated', $m, $m->getOriginal(), $m->getChanges()));
        static::deleted(fn($m) => static::writeLog('deleted', $m, $m->getAttributes(), []));
    }

    private static function writeLog(string $action, $model, array $old, array $new): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => $action,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'old_values'     => $old ?: null,
            'new_values'     => $new ?: null,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'created_at'     => now(),
        ]);
    }
}
PHP;
    }

    private function logActivityMiddleware(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class LogActivity
{
    private array $skipMethods = ['GET', 'HEAD', 'OPTIONS'];
    private array $skipPaths   = ['notifications/data', 'notifications/unread-count'];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!in_array($request->method(), $this->skipMethods)
            && auth()->check()
            && !$this->shouldSkip($request)) {
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => strtolower($request->method()) . ':' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        foreach ($this->skipPaths as $path) {
            if (str_contains($request->path(), $path)) return true;
        }
        return false;
    }
}
PHP;
    }

    private function auditLogController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action,  fn($q) => $q->where('action', 'like', "%{$request->action}%"))
            ->when($request->from,    fn($q) => $q->where('created_at', '>=', $request->from))
            ->when($request->to,      fn($q) => $q->where('created_at', '<=', $request->to . ' 23:59:59'))
            ->latest('created_at')
            ->paginate(50);

        return view('admin.audit.index', compact('logs'));
    }
}
PHP;
    }

    private function auditLogsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
            $table->index('auditable_type');
        });
    }

    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
PHP;
    }

    private function auditIndexView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="p-5 border-b border-gray-800">
        <form class="flex flex-wrap items-center gap-3" method="GET">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action..."
                   class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48">
            <input type="date" name="from" value="{{ request('from') }}"
                   class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['action','from','to']))
                <a href="{{ route('admin.audit.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Clear</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-800 text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-5 py-3">Time</th>
                    <th class="text-left px-5 py-3">User</th>
                    <th class="text-left px-5 py-3">Action</th>
                    <th class="text-left px-5 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/40">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-800/20 transition-colors">
                    <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                        <span title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-300 text-xs">{{ $log->user?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-white font-mono text-xs">{{ $log->action }}</td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-12 text-center text-gray-500 text-sm">No audit records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4">{{ $logs->withQueryString()->links() }}</div>
</div>

@endsection
BLADE;
    }

    // ─── API MODULE ──────────────────────────────────────────────────────────

    private function apiFiles(): array
    {
        return [
            ['path' => 'app/Http/Controllers/Api/BaseApiController.php',   'content' => $this->baseApiController()],
            ['path' => 'app/Http/Controllers/Api/V1/AuthController.php',   'content' => $this->apiAuthController()],
            ['path' => 'app/Http/Resources/UserResource.php',              'content' => $this->userResource()],
            ['path' => 'routes/stubs/api_routes.php',                      'content' => $this->apiRoutes()],
        ];
    }

    private function baseApiController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function success(mixed $data, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    protected function paginated($resource): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $resource->items(),
            'meta'    => [
                'current_page' => $resource->currentPage(),
                'last_page'    => $resource->lastPage(),
                'per_page'     => $resource->perPage(),
                'total'        => $resource->total(),
            ],
        ]);
    }
}
PHP;
    }

    private function apiAuthController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials.', 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return $this->success(new UserResource($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully.');
    }
}
PHP;
    }

    private function userResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
PHP;
    }

    private function apiRoutes(): string
    {
        return <<<'PHP'
<?php
// ── API Routes ───────────────────────────────────────────────────────────────
// Paste into routes/api.php
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me',      [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});
PHP;
    }

    // ─── REPORTS MODULE ──────────────────────────────────────────────────────

    private function reportsFiles(): array
    {
        return [
            ['path' => 'app/Http/Controllers/ReportController.php',       'content' => $this->reportController()],
            ['path' => 'resources/views/reports/dashboard.blade.php',     'content' => $this->reportsDashboardView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/report_routes.php',                  'content' => $this->reportRoutes()],
        ];
    }

    private function reportController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dashboard(Request $request)
    {
        $days = max(7, min(365, (int) $request->get('days', 30)));
        $from = now()->subDays($days)->startOfDay();
        $to   = now()->endOfDay();

        $stats = [
            'total_users'  => User::count(),
            'new_users'    => User::whereBetween('created_at', [$from, $to])->count(),
            'active_users' => User::where('updated_at', '>=', now()->subDays(7))->count(),
        ];

        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.dashboard', compact('stats', 'userGrowth', 'days'));
    }
}
PHP;
    }

    private function reportsDashboardView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Total Users</p>
        <p class="text-3xl font-bold text-white">{{ number_format($stats['total_users']) }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">New ({{ $days }} days)</p>
        <p class="text-3xl font-bold text-green-400">+{{ number_format($stats['new_users']) }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Active (7 days)</p>
        <p class="text-3xl font-bold text-indigo-400">{{ number_format($stats['active_users']) }}</p>
    </div>
</div>

{{-- Chart --}}
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-white">User Growth</h3>
        <form method="GET" class="flex items-center space-x-2">
            <select name="days" onchange="this.form.submit()"
                    class="bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="7"   {{ $days == 7   ? 'selected' : '' }}>7 days</option>
                <option value="30"  {{ $days == 30  ? 'selected' : '' }}>30 days</option>
                <option value="90"  {{ $days == 90  ? 'selected' : '' }}>90 days</option>
                <option value="365" {{ $days == 365 ? 'selected' : '' }}>1 year</option>
            </select>
        </form>
    </div>
    <canvas id="growthChart" height="70"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: @json($userGrowth->pluck('date')),
        datasets: [{
            label: 'New Users',
            data: @json($userGrowth->pluck('count')),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.08)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#6366f1',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: '#6b7280', font: { size: 11 } }, grid: { color: '#1f2937' } },
            y: { ticks: { color: '#6b7280', font: { size: 11 } }, grid: { color: '#1f2937' }, beginAtZero: true }
        }
    }
});
</script>

@endsection
BLADE;
    }

    private function reportRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Report Routes ────────────────────────────────────────────────────────────
// Paste into routes/web.php inside auth middleware group
use App\Http\Controllers\ReportController;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
});
PHP;
    }

    // ─── MEDIA MODULE ────────────────────────────────────────────────────────

    private function mediaFiles(): array
    {
        return [
            ['path' => 'app/Models/Media.php',                              'content' => $this->mediaModel()],
            ['path' => 'app/Http/Controllers/MediaController.php',          'content' => $this->mediaController()],
            ['path' => 'database/migrations/2024_01_01_400001_create_media_table.php', 'content' => $this->mediaMigration()],
            ['path' => 'resources/views/media/manager.blade.php',           'content' => $this->mediaManagerView(), 'language' => 'blade'],
        ];
    }

    private function mediaModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = ['user_id', 'disk', 'path', 'filename', 'mime_type', 'size', 'alt', 'title'];
    protected $appends  = ['url'];

    public function user() { return $this->belongsTo(User::class); }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->size < 1024)    return $this->size . ' B';
        if ($this->size < 1048576) return round($this->size / 1024, 1) . ' KB';
        return round($this->size / 1048576, 1) . ' MB';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
PHP;
    }

    private function mediaController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        $media = auth()->user()->media()->latest()->paginate(24);
        return view('media.manager', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);

        $file = $request->file('file');
        $disk = config('filesystems.default', 'public');
        $path = $file->store('media/' . auth()->id(), $disk);

        $media = Media::create([
            'user_id'   => auth()->id(),
            'disk'      => $disk,
            'path'      => $path,
            'filename'  => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
        ]);

        return response()->json(['success' => true, 'media' => $media->append('url')]);
    }

    public function destroy(Media $media)
    {
        $this->authorize('delete', $media);
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
        return response()->json(['success' => true]);
    }
}
PHP;
    }

    private function mediaMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('alt')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('media'); }
};
PHP;
    }

    private function mediaManagerView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Media Manager')
@section('content')

<div x-data="mediaManager()" class="space-y-4">
    {{-- Upload Zone --}}
    <div @dragover.prevent="dragging = true"
         @dragleave.prevent="dragging = false"
         @drop.prevent="handleDrop($event)"
         :class="dragging ? 'border-indigo-500 bg-indigo-500/5' : 'border-gray-700 hover:border-gray-600'"
         class="border-2 border-dashed rounded-2xl p-10 text-center transition-all cursor-pointer"
         @click="$refs.fileInput.click()">
        <input type="file" x-ref="fileInput"
               @change="handleFiles($event.target.files)"
               multiple accept="image/*,.pdf,.doc,.docx,.zip"
               class="hidden">
        <div class="text-4xl mb-3">🖼️</div>
        <p class="text-sm text-gray-400 mb-1">
            Drag & drop files here, or <span class="text-indigo-400">click to browse</span>
        </p>
        <p class="text-xs text-gray-600">Images, PDF, DOC, ZIP — max 10 MB each</p>
        <div x-show="uploading" class="mt-3 text-xs text-indigo-400">Uploading...</div>
    </div>

    {{-- Media Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @forelse($media as $item)
        <div class="group relative bg-gray-900 border border-gray-800 rounded-xl overflow-hidden aspect-square">
            @if($item->isImage())
                <img src="{{ $item->url }}" alt="{{ $item->alt ?? $item->filename }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-2xl bg-gray-800">
                    📄
                    <span class="text-xs text-gray-500 mt-1">{{ pathinfo($item->filename, PATHINFO_EXTENSION) }}</span>
                </div>
            @endif
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center space-y-1.5 p-2">
                <button @click="copy('{{ $item->url }}')"
                        class="w-full py-1 bg-white/10 hover:bg-white/20 rounded-lg text-white text-xs transition-colors">
                    Copy URL
                </button>
                <button class="w-full py-1 bg-red-500/20 hover:bg-red-500/40 rounded-lg text-red-400 text-xs transition-colors">
                    Delete
                </button>
            </div>
            <div class="absolute bottom-0 left-0 right-0 px-2 py-1 bg-gradient-to-t from-black/80">
                <p class="text-xs text-gray-300 truncate">{{ $item->filename }}</p>
            </div>
        </div>
        @empty
        <div class="col-span-6 py-12 text-center text-gray-500 text-sm">No media uploaded yet</div>
        @endforelse
    </div>

    <div>{{ $media->links() }}</div>
</div>

<script>
function mediaManager() {
    return {
        dragging: false,
        uploading: false,
        async handleFiles(files) {
            this.uploading = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            for (const file of Array.from(files)) {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('_token', token);
                await fetch('/media', { method: 'POST', body: fd });
            }
            this.uploading = false;
            window.location.reload();
        },
        handleDrop(e) {
            this.dragging = false;
            this.handleFiles(e.dataTransfer.files);
        },
        copy(url) {
            navigator.clipboard.writeText(url).then(() => alert('URL copied!'));
        },
    };
}
</script>

@endsection
BLADE;
    }

    // ─── INVENTORY MODULE ────────────────────────────────────────────────────

    private function inventoryFiles(): array
    {
        return [
            ['path' => 'app/Models/InventoryProduct.php',                                              'content' => $this->inventoryProductModel()],
            ['path' => 'app/Models/StockMovement.php',                                                 'content' => $this->stockMovementModel()],
            ['path' => 'database/migrations/2024_01_10_000001_create_inventory_products_table.php',    'content' => $this->inventoryProductsMigration()],
            ['path' => 'database/migrations/2024_01_10_000002_create_stock_movements_table.php',       'content' => $this->stockMovementsMigration()],
            ['path' => 'app/Http/Controllers/InventoryController.php',                                 'content' => $this->inventoryController()],
            ['path' => 'resources/views/inventory/index.blade.php',                                    'content' => $this->inventoryIndexView(),  'language' => 'blade'],
            ['path' => 'resources/views/inventory/create.blade.php',                                   'content' => $this->inventoryCreateView(), 'language' => 'blade'],
            ['path' => 'resources/views/inventory/edit.blade.php',                                     'content' => $this->inventoryEditView(),   'language' => 'blade'],
            ['path' => 'resources/views/inventory/alerts.blade.php',                                   'content' => $this->inventoryAlertsView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/inventory_routes.php',                                            'content' => $this->inventoryRoutes()],
        ];
    }

    private function inventoryProductModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category', 'unit', 'cost_price', 'sale_price',
        'quantity', 'min_stock', 'location', 'description', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity'   => 'integer',
        'min_stock'  => 'integer',
        'is_active'  => 'boolean',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock');
    }
}
PHP;
    }

    private function stockMovementModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'type', 'quantity', 'reference', 'date', 'notes', 'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date'     => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function inventoryProductsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category')->nullable();
            $table->string('unit')->default('pcs');
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('min_stock')->default(5);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
PHP;
    }

    private function stockMovementsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'return'])->default('in');
            $table->integer('quantity');
            $table->string('reference')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
PHP;
    }

    private function inventoryController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\InventoryProduct;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryProduct::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")->orWhere('category', 'like', "%{$s}%"));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = InventoryProduct::distinct()->pluck('category')->filter()->sort()->values();
        $lowCount   = InventoryProduct::lowStock()->count();

        return view('inventory.index', compact('products', 'categories', 'lowCount'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'required|string|max:100|unique:inventory_products',
            'category'    => 'nullable|string|max:100',
            'unit'        => 'required|string|max:50',
            'cost_price'  => 'nullable|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'min_stock'   => 'required|integer|min:0',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $product = InventoryProduct::create($data);

        if ($data['quantity'] > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type'       => 'in',
                'quantity'   => $data['quantity'],
                'reference'  => 'Initial stock',
                'date'       => now()->toDateString(),
                'user_id'    => Auth::id(),
            ]);
        }

        return redirect()->route('inventory.index')->with('success', "Product \"{$product->name}\" added to inventory.");
    }

    public function edit(InventoryProduct $inventoryProduct)
    {
        $movements = $inventoryProduct->stockMovements()->latest()->limit(10)->get();
        return view('inventory.edit', compact('inventoryProduct', 'movements'));
    }

    public function update(Request $request, InventoryProduct $inventoryProduct)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'required|string|max:100|unique:inventory_products,sku,' . $inventoryProduct->id,
            'category'    => 'nullable|string|max:100',
            'unit'        => 'required|string|max:50',
            'cost_price'  => 'nullable|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'min_stock'   => 'required|integer|min:0',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $inventoryProduct->update($data);

        return redirect()->route('inventory.index')->with('success', "Product updated.");
    }

    public function destroy(InventoryProduct $inventoryProduct)
    {
        $inventoryProduct->delete();
        return redirect()->route('inventory.index')->with('success', "Product removed from inventory.");
    }

    public function adjustStock(Request $request, InventoryProduct $inventoryProduct)
    {
        $data = $request->validate([
            'type'      => 'required|in:in,out,adjustment,return',
            'quantity'  => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
        ]);

        StockMovement::create([
            'product_id' => $inventoryProduct->id,
            'type'       => $data['type'],
            'quantity'   => $data['quantity'],
            'reference'  => $data['reference'] ?? null,
            'date'       => now()->toDateString(),
            'notes'      => $data['notes'] ?? null,
            'user_id'    => Auth::id(),
        ]);

        $delta = in_array($data['type'], ['out']) ? -$data['quantity'] : $data['quantity'];
        $inventoryProduct->increment('quantity', $delta);

        return redirect()->route('inventory.edit', $inventoryProduct)->with('success', "Stock adjusted.");
    }

    public function alerts()
    {
        $products = InventoryProduct::lowStock()->active()->orderBy('quantity')->get();
        return view('inventory.alerts', compact('products'));
    }
}
PHP;
    }

    private function inventoryIndexView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Inventory')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Inventory</h1>
        <p class="text-sm text-gray-400 mt-0.5">Manage your stock and products</p>
    </div>
    <div class="flex items-center gap-3">
        @if($lowCount > 0)
            <a href="{{ route('inventory.alerts') }}"
               class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium bg-red-900/40 border border-red-700 text-red-400 hover:bg-red-900/60 transition-colors">
                ⚠️ {{ $lowCount }} Low Stock
            </a>
        @endif
        <a href="{{ route('inventory.create') }}"
           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            + Add Product
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU…"
           class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <select name="category" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors">Filter</button>
    @if(request()->hasAny(['search','category']))
        <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-gray-400 hover:text-white text-sm transition-colors">Clear</a>
    @endif
</form>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-800">
            <tr class="text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 text-left">Product / SKU</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-right">Cost</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-right">Stock</th>
                <th class="px-4 py-3 text-left">Location</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($products as $product)
            <tr class="hover:bg-gray-800/50 transition-colors">
                <td class="px-4 py-3">
                    <div class="font-medium text-white">{{ $product->name }}</div>
                    <div class="text-xs text-gray-500 font-mono">{{ $product->sku }}</div>
                </td>
                <td class="px-4 py-3 text-gray-400">{{ $product->category ?: '—' }}</td>
                <td class="px-4 py-3 text-right text-gray-400">${{ number_format($product->cost_price, 2) }}</td>
                <td class="px-4 py-3 text-right text-white">${{ number_format($product->sale_price, 2) }}</td>
                <td class="px-4 py-3 text-right">
                    <span class="font-semibold {{ $product->isLowStock() ? 'text-red-400' : 'text-green-400' }}">
                        {{ $product->quantity }}
                    </span>
                    <span class="text-xs text-gray-500"> / min {{ $product->min_stock }}</span>
                </td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $product->location ?: '—' }}</td>
                <td class="px-4 py-3">
                    @if($product->is_active)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-900/50 text-green-400 border border-green-800">Active</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-800 text-gray-500 border border-gray-700">Inactive</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('inventory.edit', $product) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-12 text-center text-gray-500">No products found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
BLADE;
    }

    private function inventoryCreateView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Add Product')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-6 text-sm text-gray-400">
        <a href="{{ route('inventory.index') }}" class="hover:text-white transition-colors">Inventory</a>
        <span>/</span>
        <span class="text-white">Add Product</span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h1 class="text-lg font-bold text-white mb-6">Add New Product</h1>

        <form method="POST" action="{{ route('inventory.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">SKU / Barcode *</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sku') border-red-500 @enderror">
                    @error('sku')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Unit *</label>
                    <select name="unit" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['pcs','kg','g','litre','ml','box','pack','set','pair','roll','m','cm','ft'] as $u)
                            <option value="{{ $u }}" {{ old('unit','pcs') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Cost Price</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Sale Price</label>
                    <input type="number" name="sale_price" value="{{ old('sale_price') }}" step="0.01" min="0"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Initial Quantity *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Minimum Stock Alert *</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', 5) }}" min="0" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Warehouse Location</label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Shelf A3, Bin 12"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div class="sm:col-span-2 flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                           class="rounded border-gray-700 bg-gray-800 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-gray-300">Active product</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Save Product</button>
                <a href="{{ route('inventory.index') }}" class="px-5 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE;
    }

    private function inventoryEditView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Edit Product')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-6 text-sm text-gray-400">
        <a href="{{ route('inventory.index') }}" class="hover:text-white transition-colors">Inventory</a>
        <span>/</span>
        <span class="text-white">{{ $inventoryProduct->name }}</span>
    </div>

    {{-- Edit Form --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-5">
        <h1 class="text-lg font-bold text-white mb-6">Edit Product</h1>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('inventory.update', $inventoryProduct) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $inventoryProduct->name) }}" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku', $inventoryProduct->sku) }}" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Category</label>
                    <input type="text" name="category" value="{{ old('category', $inventoryProduct->category) }}"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Cost Price</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', $inventoryProduct->cost_price) }}" step="0.01" min="0"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Sale Price</label>
                    <input type="number" name="sale_price" value="{{ old('sale_price', $inventoryProduct->sale_price) }}" step="0.01" min="0"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Min Stock Alert</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', $inventoryProduct->min_stock) }}" min="0" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location', $inventoryProduct->location) }}"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $inventoryProduct->description) }}</textarea>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $inventoryProduct->is_active ? 'checked' : '' }}
                           class="rounded border-gray-700 bg-gray-800 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-gray-300">Active</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Update</button>
                <a href="{{ route('inventory.index') }}" class="px-5 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Stock Adjustment --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-5">
        <h2 class="text-sm font-semibold text-white mb-4">Adjust Stock
            <span class="ml-2 text-{{ $inventoryProduct->isLowStock() ? 'red' : 'green' }}-400 font-bold">
                (Current: {{ $inventoryProduct->quantity }} {{ $inventoryProduct->unit }})
            </span>
        </h2>
        <form method="POST" action="{{ route('inventory.adjust', $inventoryProduct) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @csrf
            <select name="type" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
                <option value="adjustment">Adjustment</option>
                <option value="return">Return</option>
            </select>
            <input type="number" name="quantity" min="1" placeholder="Quantity" required
                   class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="text" name="reference" placeholder="Reference #"
                   class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Apply</button>
        </form>
    </div>

    {{-- Movement History --}}
    @if($movements->isNotEmpty())
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-800">
            <h2 class="text-sm font-semibold text-white">Recent Stock Movements</h2>
        </div>
        <table class="w-full text-xs">
            <thead class="border-b border-gray-800">
                <tr class="text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-right">Qty</th>
                    <th class="px-4 py-2 text-left">Reference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($movements as $m)
                <tr class="text-gray-400">
                    <td class="px-4 py-2">{{ $m->date->format('d M Y') }}</td>
                    <td class="px-4 py-2 capitalize">{{ $m->type }}</td>
                    <td class="px-4 py-2 text-right {{ in_array($m->type,['in','return']) ? 'text-green-400' : 'text-red-400' }}">
                        {{ in_array($m->type,['in','return']) ? '+' : '-' }}{{ $m->quantity }}
                    </td>
                    <td class="px-4 py-2">{{ $m->reference ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
BLADE;
    }

    private function inventoryAlertsView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Low Stock Alerts')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Low Stock Alerts</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ $products->count() }} product(s) at or below minimum stock level</p>
    </div>
    <a href="{{ route('inventory.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">← Back to Inventory</a>
</div>

@if($products->isEmpty())
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
        <div class="text-4xl mb-3">✅</div>
        <p class="text-white font-semibold">All products are well stocked!</p>
        <p class="text-gray-400 text-sm mt-1">No items below their minimum stock threshold.</p>
    </div>
@else
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-800">
                <tr class="text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3 text-left">Product</th>
                    <th class="px-4 py-3 text-left">SKU</th>
                    <th class="px-4 py-3 text-right">Current Stock</th>
                    <th class="px-4 py-3 text-right">Min Stock</th>
                    <th class="px-4 py-3 text-right">Shortage</th>
                    <th class="px-4 py-3 text-left">Location</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($products as $product)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3 font-medium text-white">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-bold {{ $product->quantity === 0 ? 'text-red-500' : 'text-orange-400' }}">
                            {{ $product->quantity }}
                        </span>
                        <span class="text-gray-500 text-xs"> {{ $product->unit }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-400">{{ $product->min_stock }}</td>
                    <td class="px-4 py-3 text-right text-red-400 font-semibold">
                        {{ max(0, $product->min_stock - $product->quantity) }}
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $product->location ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('inventory.edit', $product) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Restock</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
BLADE;
    }

    private function inventoryRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Inventory Routes ─────────────────────────────────────────────────────────
// Paste into routes/web.php inside the auth middleware group
use App\Http\Controllers\InventoryController;

Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/',                           [InventoryController::class, 'index'])->name('index');
    Route::get('/create',                     [InventoryController::class, 'create'])->name('create');
    Route::post('/',                          [InventoryController::class, 'store'])->name('store');
    Route::get('/{inventoryProduct}/edit',    [InventoryController::class, 'edit'])->name('edit');
    Route::put('/{inventoryProduct}',         [InventoryController::class, 'update'])->name('update');
    Route::delete('/{inventoryProduct}',      [InventoryController::class, 'destroy'])->name('destroy');
    Route::post('/{inventoryProduct}/adjust', [InventoryController::class, 'adjustStock'])->name('adjust');
    Route::get('/alerts',                     [InventoryController::class, 'alerts'])->name('alerts');
});
PHP;
    }

    // ─── ORDERS MODULE ────────────────────────────────────────────────────────

    private function ordersFiles(): array
    {
        return [
            ['path' => 'app/Models/Order.php',                                                  'content' => $this->orderModel()],
            ['path' => 'app/Models/OrderItem.php',                                              'content' => $this->orderItemModel()],
            ['path' => 'database/migrations/2024_01_11_000001_create_orders_table.php',         'content' => $this->ordersMigration()],
            ['path' => 'database/migrations/2024_01_11_000002_create_order_items_table.php',    'content' => $this->orderItemsMigration()],
            ['path' => 'app/Http/Controllers/OrderController.php',                              'content' => $this->orderController()],
            ['path' => 'resources/views/orders/index.blade.php',                                'content' => $this->ordersIndexView(),   'language' => 'blade'],
            ['path' => 'resources/views/orders/show.blade.php',                                 'content' => $this->ordersShowView(),    'language' => 'blade'],
            ['path' => 'resources/views/orders/create.blade.php',                               'content' => $this->ordersCreateView(),  'language' => 'blade'],
            ['path' => 'resources/views/orders/returns.blade.php',                              'content' => $this->ordersReturnsView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/orders_routes.php',                                        'content' => $this->ordersRoutes()],
        ];
    }

    private function orderModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'status', 'subtotal', 'tax', 'discount', 'total',
        'payment_status', 'payment_method', 'notes', 'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax'      => 'decimal:2',
        'discount' => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeReturns($query)
    {
        return $query->where('status', 'returned');
    }

    public static function generateNumber(): string
    {
        return 'ORD-' . strtoupper(substr(uniqid(), -6)) . '-' . now()->format('ymd');
    }

    public function canReturn(): bool
    {
        return in_array($this->status, ['delivered']);
    }
}
PHP;
    }

    private function orderItemModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_name', 'product_sku', 'quantity', 'unit_price', 'subtotal',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
PHP;
    }

    private function ordersMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->enum('status', ['pending','confirmed','processing','shipped','delivered','cancelled','returned'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
PHP;
    }

    private function orderItemsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
PHP;
    }

    private function orderController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('order_number', 'like', "%{$s}%")->orWhere('customer_name', 'like', "%{$s}%")->orWhere('customer_email', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items');
        return view('orders.show', compact('order'));
    }

    public function create()
    {
        return view('orders.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'nullable|string|max:30',
            'payment_method'   => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.name'     => 'required|string|max:255',
            'items.*.sku'      => 'nullable|string|max:100',
            'items.*.qty'      => 'required|integer|min:1',
            'items.*.price'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $subtotal = collect($data['items'])->sum(fn($i) => $i['qty'] * $i['price']);
            $tax      = round($subtotal * 0.05, 2);

            $order = Order::create([
                'order_number'   => Order::generateNumber(),
                'customer_name'  => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'discount'       => 0,
                'total'          => $subtotal + $tax,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
                'user_id'        => Auth::id(),
            ]);

            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_name' => $item['name'],
                    'product_sku'  => $item['sku'] ?? null,
                    'quantity'     => $item['qty'],
                    'unit_price'   => $item['price'],
                    'subtotal'     => $item['qty'] * $item['price'],
                ]);
            }

            session()->flash('success', "Order {$order->order_number} created.");
        });

        return redirect()->route('orders.index');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => 'required|in:' . implode(',', Order::STATUSES)]);
        $order->update($data);
        return back()->with('success', "Order status updated to {$data['status']}.");
    }

    public function returns(Request $request)
    {
        $orders = Order::returns()->latest()->paginate(20)->withQueryString();
        return view('orders.returns', compact('orders'));
    }

    public function processReturn(Order $order)
    {
        abort_unless($order->canReturn(), 422, 'Only delivered orders can be returned.');
        $order->update(['status' => 'returned']);
        return back()->with('success', "Order {$order->order_number} marked as returned.");
    }
}
PHP;
    }

    private function ordersIndexView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Orders')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Orders</h1>
        <p class="text-sm text-gray-400 mt-0.5">Manage and track customer orders</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('orders.returns') }}" class="px-3 py-2 text-sm text-gray-400 hover:text-white bg-gray-800 border border-gray-700 rounded-lg transition-colors">↩️ Returns</a>
        <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">+ New Order</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
@endif

<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, customer name…"
           class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 w-60 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <select name="status" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">All Statuses</option>
        @foreach(\App\Models\Order::STATUSES as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors">Filter</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('orders.index') }}" class="px-4 py-2 text-gray-400 hover:text-white text-sm transition-colors">Clear</a>
    @endif
</form>

@php
    $statusColors = [
        'pending'    => 'bg-yellow-900/40 text-yellow-400 border-yellow-800',
        'confirmed'  => 'bg-blue-900/40 text-blue-400 border-blue-800',
        'processing' => 'bg-indigo-900/40 text-indigo-400 border-indigo-800',
        'shipped'    => 'bg-purple-900/40 text-purple-400 border-purple-800',
        'delivered'  => 'bg-green-900/40 text-green-400 border-green-800',
        'cancelled'  => 'bg-red-900/40 text-red-400 border-red-800',
        'returned'   => 'bg-gray-800 text-gray-400 border-gray-700',
    ];
@endphp

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-800">
            <tr class="text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 text-left">Order #</th>
                <th class="px-4 py-3 text-left">Customer</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Payment</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($orders as $order)
            <tr class="hover:bg-gray-800/50 transition-colors">
                <td class="px-4 py-3 font-mono text-xs text-indigo-400">{{ $order->order_number }}</td>
                <td class="px-4 py-3">
                    <div class="font-medium text-white">{{ $order->customer_name }}</div>
                    @if($order->customer_email)
                        <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs border {{ $statusColors[$order->status] ?? 'bg-gray-800 text-gray-400 border-gray-700' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs {{ $order->payment_status === 'paid' ? 'text-green-400' : 'text-yellow-400' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-white font-semibold">${{ number_format($order->total, 2) }}</td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $order->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('orders.show', $order) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
BLADE;
    }

    private function ordersShowView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 mb-6 text-sm text-gray-400">
        <a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">Orders</a>
        <span>/</span>
        <span class="text-white font-mono">{{ $order->order_number }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
        {{-- Order Info --}}
        <div class="md:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Order Details</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-gray-500">Customer</dt><dd class="text-white font-medium mt-0.5">{{ $order->customer_name }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd class="text-white mt-0.5">{{ $order->customer_email ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd class="text-white mt-0.5">{{ $order->customer_phone ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Payment</dt><dd class="text-white mt-0.5">{{ ucfirst($order->payment_status) }} {{ $order->payment_method ? "({$order->payment_method})" : '' }}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="text-white mt-0.5">{{ $order->notes ?: '—' }}</dd></div>
            </dl>
        </div>

        {{-- Status & Actions --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Status</h2>
            <p class="text-2xl font-bold text-white mb-4">${{ number_format($order->total, 2) }}</p>
            <form method="POST" action="{{ route('orders.status', $order) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\Order::STATUSES as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Update Status</button>
            </form>

            @if($order->canReturn())
                <form method="POST" action="{{ route('orders.return', $order) }}" class="mt-2">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full px-4 py-2 bg-red-900/40 hover:bg-red-900/60 text-red-400 border border-red-800 text-sm font-medium rounded-lg transition-colors">
                        ↩️ Mark Returned
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Order Items --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-800">
            <h2 class="text-sm font-semibold text-white">Items</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b border-gray-800">
                <tr class="text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-right">Qty</th>
                    <th class="px-4 py-2 text-right">Price</th>
                    <th class="px-4 py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($order->items as $item)
                <tr class="text-gray-300">
                    <td class="px-4 py-2 font-medium text-white">{{ $item->product_name }}</td>
                    <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $item->product_sku ?: '—' }}</td>
                    <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                    <td class="px-4 py-2 text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-2 text-right text-white">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-800 text-sm">
                <tr><td colspan="4" class="px-4 py-2 text-right text-gray-400">Subtotal</td><td class="px-4 py-2 text-right text-white">${{ number_format($order->subtotal, 2) }}</td></tr>
                <tr><td colspan="4" class="px-4 py-2 text-right text-gray-400">Tax</td><td class="px-4 py-2 text-right text-white">${{ number_format($order->tax, 2) }}</td></tr>
                <tr><td colspan="4" class="px-4 py-2 text-right text-gray-400">Discount</td><td class="px-4 py-2 text-right text-red-400">-${{ number_format($order->discount, 2) }}</td></tr>
                <tr class="font-bold"><td colspan="4" class="px-4 py-2 text-right text-white">Total</td><td class="px-4 py-2 text-right text-indigo-400 text-base">${{ number_format($order->total, 2) }}</td></tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
BLADE;
    }

    private function ordersCreateView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'New Order')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-6 text-sm text-gray-400">
        <a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">Orders</a>
        <span>/</span>
        <span class="text-white">New Order</span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h1 class="text-lg font-bold text-white mb-6">Create Order</h1>

        <form method="POST" action="{{ route('orders.store') }}" class="space-y-5" x-data="orderForm()">
            @csrf

            {{-- Customer --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Customer Name *</label>
                    <input type="text" name="customer_name" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" name="customer_email"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Phone</label>
                    <input type="text" name="customer_phone"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select method</option>
                        @foreach(['cash','card','bank_transfer','online'] as $m)
                            <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Order Items --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-300">Order Items *</label>
                    <button type="button" @click="addItem()" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">+ Add Item</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <input type="text" :name="'items['+i+'][name]'" x-model="item.name" placeholder="Product name" required
                                   class="col-span-5 bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <input type="number" :name="'items['+i+'][qty]'" x-model.number="item.qty" min="1" placeholder="Qty" required
                                   class="col-span-2 bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <input type="number" :name="'items['+i+'][price]'" x-model.number="item.price" min="0" step="0.01" placeholder="Price" required
                                   class="col-span-3 bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="col-span-1 text-xs text-gray-400 text-right" x-text="'$'+(item.qty*item.price).toFixed(2)"></span>
                            <button type="button" @click="removeItem(i)" x-show="items.length > 1" class="col-span-1 text-red-400 hover:text-red-300 text-lg leading-none">×</button>
                        </div>
                    </template>
                </div>
                <div class="mt-3 text-right text-sm text-gray-400">
                    Total: <span class="text-white font-bold" x-text="'$'+total.toFixed(2)"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Create Order</button>
                <a href="{{ route('orders.index') }}" class="px-5 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        items: [{ name: '', qty: 1, price: 0 }],
        get total() {
            return this.items.reduce((s, i) => s + (i.qty * i.price), 0);
        },
        addItem() { this.items.push({ name: '', qty: 1, price: 0 }); },
        removeItem(i) { this.items.splice(i, 1); },
    };
}
</script>
@endpush
@endsection
BLADE;
    }

    private function ordersReturnsView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Returns')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Returns</h1>
        <p class="text-sm text-gray-400 mt-0.5">Orders marked as returned</p>
    </div>
    <a href="{{ route('orders.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">← All Orders</a>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-800">
            <tr class="text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 text-left">Order #</th>
                <th class="px-4 py-3 text-left">Customer</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-left">Returned On</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($orders as $order)
            <tr class="hover:bg-gray-800/50 transition-colors">
                <td class="px-4 py-3 font-mono text-xs text-indigo-400">{{ $order->order_number }}</td>
                <td class="px-4 py-3 text-white">{{ $order->customer_name }}</td>
                <td class="px-4 py-3 text-right text-white">${{ number_format($order->total, 2) }}</td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $order->updated_at->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('orders.show', $order) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No returns yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
BLADE;
    }

    private function ordersRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Orders Routes ─────────────────────────────────────────────────────────────
// Paste into routes/web.php inside the auth middleware group
use App\Http\Controllers\OrderController;

Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/',                     [OrderController::class, 'index'])->name('index');
    Route::get('/returns',              [OrderController::class, 'returns'])->name('returns');
    Route::get('/create',               [OrderController::class, 'create'])->name('create');
    Route::post('/',                    [OrderController::class, 'store'])->name('store');
    Route::get('/{order}',              [OrderController::class, 'show'])->name('show');
    Route::patch('/{order}/status',     [OrderController::class, 'updateStatus'])->name('status');
    Route::patch('/{order}/return',     [OrderController::class, 'processReturn'])->name('return');
});
PHP;
    }

    // ─── SUBSCRIPTIONS MODULE ─────────────────────────────────────────────────

    private function subscriptionsFiles(): array
    {
        return [
            ['path' => 'app/Models/Plan.php',                                                          'content' => $this->planModel()],
            ['path' => 'app/Models/UserSubscription.php',                                              'content' => $this->userSubscriptionModel()],
            ['path' => 'database/migrations/2024_01_12_000001_create_plans_table.php',                 'content' => $this->plansMigration()],
            ['path' => 'database/migrations/2024_01_12_000002_create_user_subscriptions_table.php',    'content' => $this->userSubscriptionsMigration()],
            ['path' => 'app/Http/Controllers/SubscriptionController.php',                              'content' => $this->subscriptionController()],
            ['path' => 'resources/views/subscriptions/plans.blade.php',                                'content' => $this->subscriptionPlansView(),   'language' => 'blade'],
            ['path' => 'resources/views/subscriptions/current.blade.php',                              'content' => $this->subscriptionCurrentView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/subscriptions_routes.php',                                        'content' => $this->subscriptionsRoutes()],
        ];
    }

    private function planModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_yearly',
        'max_users', 'max_projects', 'features', 'is_popular', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly'  => 'decimal:2',
        'max_users'     => 'integer',
        'max_projects'  => 'integer',
        'features'      => 'array',
        'is_popular'    => 'boolean',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('price_monthly');
    }

    public function getPrice(string $cycle = 'monthly'): float
    {
        return $cycle === 'yearly' ? (float) $this->price_yearly : (float) $this->price_monthly;
    }

    public function yearlyDiscount(): int
    {
        if (!$this->price_monthly || !$this->price_yearly) return 0;
        $annual = $this->price_monthly * 12;
        return (int) round(($annual - $this->price_yearly) / $annual * 100);
    }
}
PHP;
    }

    private function userSubscriptionModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan_id', 'billing_cycle', 'status',
        'amount', 'started_at', 'expires_at', 'trial_ends_at',
        'cancelled_at', 'payment_reference',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'started_at'   => 'date',
        'expires_at'   => 'date',
        'trial_ends_at'=> 'date',
        'cancelled_at' => 'datetime',
    ];

    const STATUSES = ['trial', 'active', 'past_due', 'cancelled', 'expired'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial'])
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function daysRemaining(): int
    {
        $end = $this->trial_ends_at ?? $this->expires_at;
        return $end ? max(0, now()->diffInDays($end, false)) : 0;
    }
}
PHP;
    }

    private function plansMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly',  10, 2)->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_projects')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed starter plans
        \App\Models\Plan::insert([
            ['name'=>'Starter','slug'=>'starter','description'=>'Perfect for individuals','price_monthly'=>0,'price_yearly'=>0,'max_users'=>1,'max_projects'=>3,'features'=>json_encode(['3 projects','Basic support']),'is_popular'=>false,'is_active'=>true,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Pro',    'slug'=>'pro',    'description'=>'For growing teams',      'price_monthly'=>19,'price_yearly'=>190,'max_users'=>10,'max_projects'=>25,'features'=>json_encode(['25 projects','Priority support','API access']),'is_popular'=>true,'is_active'=>true,'sort_order'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Business','slug'=>'business','description'=>'For large teams',     'price_monthly'=>49,'price_yearly'=>490,'max_users'=>null,'max_projects'=>null,'features'=>json_encode(['Unlimited projects','Dedicated support','Custom domain','SLA']),'is_popular'=>false,'is_active'=>true,'sort_order'=>3,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
PHP;
    }

    private function userSubscriptionsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('trial');
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('started_at');
            $table->date('expires_at')->nullable();
            $table->date('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
PHP;
    }

    private function subscriptionController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans        = Plan::active()->get();
        $current      = Auth::user()->subscriptions()->with('plan')->latest()->first();
        return view('subscriptions.plans', compact('plans', 'current'));
    }

    public function current()
    {
        $subscription = Auth::user()->subscriptions()->with('plan')->latest()->first();
        return view('subscriptions.current', compact('subscription'));
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'plan_id'       => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $plan    = Plan::findOrFail($data['plan_id']);
        $user    = Auth::user();
        $cycle   = $data['billing_cycle'];
        $amount  = $plan->getPrice($cycle);
        $expires = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $existing = $user->subscriptions()->whereIn('status', ['active','trial'])->first();
        if ($existing) {
            $existing->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        $sub = UserSubscription::create([
            'user_id'       => $user->id,
            'plan_id'       => $plan->id,
            'billing_cycle' => $cycle,
            'status'        => 'active',
            'amount'        => $amount,
            'started_at'    => now()->toDateString(),
            'expires_at'    => $expires->toDateString(),
        ]);

        return redirect()->route('subscriptions.current')
            ->with('success', "Subscribed to {$plan->name} ({$cycle}) plan successfully.");
    }

    public function cancel()
    {
        $sub = Auth::user()->subscriptions()->whereIn('status', ['active','trial'])->latest()->first();

        if (!$sub) {
            return back()->with('error', 'No active subscription to cancel.');
        }

        $sub->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return redirect()->route('subscriptions.current')
            ->with('success', 'Your subscription has been cancelled.');
    }
}
PHP;
    }

    private function subscriptionPlansView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="text-center mb-10">
        <h1 class="text-2xl font-bold text-white">Choose Your Plan</h1>
        <p class="text-gray-400 mt-2">Upgrade or downgrade at any time. All plans include a 14-day free trial.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-900/40 border border-green-700 rounded-xl text-green-400 text-sm text-center">{{ session('success') }}</div>
    @endif

    @if($current && $current->isActive())
        <div class="mb-6 p-4 bg-indigo-900/30 border border-indigo-700 rounded-xl text-indigo-300 text-sm text-center">
            You are on the <strong>{{ $current->plan->name }}</strong> plan
            ({{ ucfirst($current->billing_cycle) }}) — expires {{ $current->expires_at?->format('d M Y') ?? 'never' }}.
            <a href="{{ route('subscriptions.current') }}" class="ml-2 underline">Manage →</a>
        </div>
    @endif

    <div x-data="{ cycle: 'monthly' }" class="space-y-6">
        {{-- Billing Toggle --}}
        <div class="flex items-center justify-center gap-4">
            <span class="text-sm" :class="cycle==='monthly' ? 'text-white' : 'text-gray-500'">Monthly</span>
            <button @click="cycle = cycle === 'monthly' ? 'yearly' : 'monthly'"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                    :class="cycle==='yearly' ? 'bg-indigo-600' : 'bg-gray-700'">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                      :class="cycle==='yearly' ? 'translate-x-6' : 'translate-x-1'"></span>
            </button>
            <span class="text-sm" :class="cycle==='yearly' ? 'text-white' : 'text-gray-500'">
                Yearly <span class="ml-1 px-1.5 py-0.5 text-xs bg-green-900/50 text-green-400 rounded">Save up to 20%</span>
            </span>
        </div>

        {{-- Plans Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($plans as $plan)
            <div class="relative bg-gray-900 border rounded-2xl p-6 flex flex-col
                        {{ $plan->is_popular ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-800' }}">

                @if($plan->is_popular)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-semibold rounded-full">Most Popular</span>
                    </div>
                @endif

                <h2 class="text-lg font-bold text-white mb-1">{{ $plan->name }}</h2>
                <p class="text-sm text-gray-400 mb-5">{{ $plan->description }}</p>

                <div class="mb-5">
                    <template x-if="cycle==='monthly'">
                        <div>
                            <span class="text-3xl font-bold text-white">${{ number_format($plan->price_monthly, 0) }}</span>
                            <span class="text-gray-500 text-sm">/mo</span>
                        </div>
                    </template>
                    <template x-if="cycle==='yearly'">
                        <div>
                            <span class="text-3xl font-bold text-white">${{ number_format($plan->price_yearly ?? $plan->price_monthly * 12, 0) }}</span>
                            <span class="text-gray-500 text-sm">/yr</span>
                            @if($plan->yearlyDiscount() > 0)
                                <span class="ml-2 text-xs text-green-400">{{ $plan->yearlyDiscount() }}% off</span>
                            @endif
                        </div>
                    </template>
                </div>

                @if($plan->features)
                <ul class="space-y-2 mb-6 flex-1">
                    @foreach($plan->features as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <span class="text-green-400 text-xs">✓</span> {{ $feature }}
                    </li>
                    @endforeach
                    @if($plan->max_users)
                        <li class="flex items-center gap-2 text-sm text-gray-300"><span class="text-green-400 text-xs">✓</span> Up to {{ $plan->max_users }} users</li>
                    @else
                        <li class="flex items-center gap-2 text-sm text-gray-300"><span class="text-green-400 text-xs">✓</span> Unlimited users</li>
                    @endif
                </ul>
                @endif

                <form method="POST" action="{{ route('subscriptions.subscribe') }}" x-data>
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="hidden" name="billing_cycle" :value="cycle">

                    @if($current && $current->plan_id === $plan->id && $current->isActive())
                        <button type="button" disabled
                                class="w-full py-2.5 rounded-xl text-sm font-medium bg-gray-800 text-gray-400 cursor-not-allowed">
                            Current Plan
                        </button>
                    @else
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl text-sm font-medium transition-colors
                                       {{ $plan->is_popular ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-800 hover:bg-gray-700 text-white border border-gray-700' }}">
                            {{ $plan->price_monthly == 0 ? 'Get Started Free' : 'Subscribe' }}
                        </button>
                    @endif
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
BLADE;
    }

    private function subscriptionCurrentView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'My Subscription')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">My Subscription</h1>
        <a href="{{ route('subscriptions.plans') }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">View Plans →</a>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-900/40 border border-green-700 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 p-4 bg-red-900/40 border border-red-700 rounded-xl text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    @if($subscription && $subscription->isActive())
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-5">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-white">{{ $subscription->plan->name }} Plan</h2>
                    <p class="text-sm text-gray-400 mt-0.5">{{ ucfirst($subscription->billing_cycle) }} billing</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $subscription->status === 'trial' ? 'bg-yellow-900/40 text-yellow-400 border border-yellow-800' : 'bg-green-900/40 text-green-400 border border-green-800' }}">
                    {{ ucfirst($subscription->status) }}
                </span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm mb-6">
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Amount</dt>
                    <dd class="text-white font-semibold text-lg">${{ number_format($subscription->amount, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Days Remaining</dt>
                    <dd class="text-white font-semibold text-lg">{{ $subscription->daysRemaining() }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Started</dt>
                    <dd class="text-gray-300">{{ $subscription->started_at->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Renews / Expires</dt>
                    <dd class="text-gray-300">{{ $subscription->expires_at?->format('d M Y') ?? 'Never' }}</dd>
                </div>
                @if($subscription->isOnTrial())
                <div class="col-span-2">
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Trial Ends</dt>
                    <dd class="text-yellow-400 font-medium">{{ $subscription->trial_ends_at->format('d M Y') }}</dd>
                </div>
                @endif
            </dl>

            {{-- Plan Features --}}
            @if($subscription->plan->features)
            <div class="mb-6">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Included Features</p>
                <ul class="space-y-1.5">
                    @foreach($subscription->plan->features as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <span class="text-green-400 text-xs">✓</span> {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <a href="{{ route('subscriptions.plans') }}"
                   class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl text-center transition-colors">
                    Change Plan
                </a>
                <form method="POST" action="{{ route('subscriptions.cancel') }}" onsubmit="return confirm('Cancel your subscription?')">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 bg-red-900/30 hover:bg-red-900/50 text-red-400 border border-red-800 text-sm font-medium rounded-xl transition-colors">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

    @else
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
            <div class="text-4xl mb-4">🔄</div>
            <h2 class="text-lg font-bold text-white mb-2">No Active Subscription</h2>
            <p class="text-gray-400 text-sm mb-6">Choose a plan to unlock all features. Start with a 14-day free trial.</p>
            <a href="{{ route('subscriptions.plans') }}"
               class="inline-flex px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                View Plans
            </a>
        </div>
    @endif
</div>
@endsection
BLADE;
    }

    private function subscriptionsRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Subscriptions Routes ──────────────────────────────────────────────────────
// Paste into routes/web.php inside the auth middleware group
// Also add to User model: public function subscriptions() { return $this->hasMany(\App\Models\UserSubscription::class); }
use App\Http\Controllers\SubscriptionController;

Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/plans',   [SubscriptionController::class, 'plans'])->name('plans');
    Route::get('/current', [SubscriptionController::class, 'current'])->name('current');
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::post('/cancel',    [SubscriptionController::class, 'cancel'])->name('cancel');
});
PHP;
    }

    // ─── MULTI-TENANT MODULE ─────────────────────────────────────────────────

    private function multiTenantFiles(): array
    {
        return [
            ['path' => 'app/Models/Tenant.php',                                                     'content' => $this->tenantModel()],
            ['path' => 'app/Models/TenantUser.php',                                                  'content' => $this->tenantUserModel()],
            ['path' => 'database/migrations/2024_01_13_000001_create_tenants_table.php',             'content' => $this->tenantsMigration()],
            ['path' => 'database/migrations/2024_01_13_000002_create_tenant_users_table.php',        'content' => $this->tenantUsersMigration()],
            ['path' => 'app/Http/Middleware/IdentifyTenant.php',                                     'content' => $this->identifyTenantMiddleware()],
            ['path' => 'app/Http/Middleware/EnsureTenantAccess.php',                                 'content' => $this->ensureTenantAccessMiddleware()],
            ['path' => 'app/Traits/BelongsToTenant.php',                                            'content' => $this->belongsToTenantTrait()],
            ['path' => 'app/Http/Controllers/TenantController.php',                                  'content' => $this->tenantController()],
            ['path' => 'resources/views/tenants/index.blade.php',                                    'content' => $this->tenantsIndexView(),    'language' => 'blade'],
            ['path' => 'resources/views/tenants/show.blade.php',                                     'content' => $this->tenantsShowView(),     'language' => 'blade'],
            ['path' => 'resources/views/tenants/settings.blade.php',                                 'content' => $this->tenantsSettingsView(), 'language' => 'blade'],
            ['path' => 'routes/stubs/tenant_routes.php',                                             'content' => $this->tenantRoutes()],
        ];
    }

    private function tenantModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'domain', 'custom_domain', 'owner_id',
        'plan', 'status', 'settings', 'trial_ends_at',
    ];

    protected $casts = [
        'settings'     => 'array',
        'trial_ends_at'=> 'datetime',
    ];

    const STATUSES = ['active', 'suspended', 'trial', 'cancelled'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function tenantUsers()
    {
        return $this->hasMany(TenantUser::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->isOnTrial();
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    public function subdomain(): string
    {
        return $this->slug . '.' . config('app.base_domain', parse_url(config('app.url'), PHP_URL_HOST));
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name) . '-' . Str::random(4);
            }
        });
    }
}
PHP;
    }

    private function tenantUserModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TenantUser extends Pivot
{
    protected $table = 'tenant_users';

    protected $fillable = ['tenant_id', 'user_id', 'role'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }
}
PHP;
    }

    private function tenantsMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->unique()->nullable();        // auto-generated subdomain
            $table->string('custom_domain')->unique()->nullable(); // e.g. app.client.com
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan')->default('starter');
            $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
            $table->json('settings')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
PHP;
    }

    private function tenantUsersMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'member', 'viewer'])->default('member');
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};
PHP;
    }

    private function identifyTenantMiddleware(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant from:
 *   1. Custom domain (custom_domain column)
 *   2. Subdomain prefix  (slug.basedomain.com)
 *   3. X-Tenant-Slug header (API / testing)
 *
 * Stores the resolved tenant in app('tenant') and request()->tenant().
 * Routes that require a resolved tenant should also apply EnsureTenantAccess.
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolve($request);

        if ($tenant) {
            app()->instance('tenant', $tenant);
            $request->macro('tenant', fn() => $tenant);
        }

        return $next($request);
    }

    private function resolve(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $base = config('app.base_domain', parse_url(config('app.url'), PHP_URL_HOST));

        // Custom domain
        $tenant = Tenant::where('custom_domain', $host)->first();
        if ($tenant) return $tenant;

        // Subdomain (slug.base.com)
        if (str_ends_with($host, '.' . $base)) {
            $slug = str_replace('.' . $base, '', $host);
            $tenant = Tenant::where('slug', $slug)->first();
            if ($tenant) return $tenant;
        }

        // Header fallback for API / local dev
        if ($slug = $request->header('X-Tenant-Slug')) {
            return Tenant::where('slug', $slug)->first();
        }

        return null;
    }
}
PHP;
    }

    private function ensureTenantAccessMiddleware(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures:
 *   1. A tenant has been resolved by IdentifyTenant
 *   2. The authenticated user belongs to that tenant
 *   3. The tenant is active (not suspended / cancelled)
 */
class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next, string $minRole = 'member'): Response
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        if (!$tenant->isActive()) {
            abort(403, 'This workspace is suspended or cancelled.');
        }

        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $tenantUser = $tenant->tenantUsers()->where('user_id', $user->id)->first();

        if (!$tenantUser) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Role hierarchy: owner > admin > member > viewer
        $hierarchy = ['viewer' => 0, 'member' => 1, 'admin' => 2, 'owner' => 3];
        $required  = $hierarchy[$minRole] ?? 1;
        $actual    = $hierarchy[$tenantUser->role] ?? 0;

        if ($actual < $required) {
            abort(403, "You need {$minRole} access for this action.");
        }

        return $next($request);
    }
}
PHP;
    }

    private function belongsToTenantTrait(): string
    {
        return <<<'PHP'
<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * Automatically scopes Eloquent queries to the current tenant.
 *
 * Usage: add `use BelongsToTenant;` to any model that has a `tenant_id` column.
 * The global scope fires on every query. When no tenant is resolved (e.g. in
 * seeder/artisan context), the scope is a no-op so admin queries still work.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Auto-fill tenant_id on create
        static::creating(function ($model) {
            if (app()->bound('tenant') && empty($model->tenant_id)) {
                $model->tenant_id = app('tenant')->id;
            }
        });

        // Global query scope
        static::addGlobalScope('tenant', function (Builder $query) {
            if (app()->bound('tenant')) {
                $query->where($query->getModel()->getTable() . '.tenant_id', app('tenant')->id);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Bypass tenant scope for cross-tenant admin queries */
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
PHP;
    }

    private function tenantController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    // ── Admin: list all tenants ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Tenant::with('owner')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tenants = $query->paginate(20)->withQueryString();
        return view('tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('owner', 'users');
        return view('tenants.show', compact('tenant'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'plan'  => 'nullable|string|max:50',
        ]);

        $tenant = Tenant::create([
            'name'          => $data['name'],
            'owner_id'      => Auth::id(),
            'plan'          => $data['plan'] ?? 'starter',
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Add creator as owner member
        TenantUser::create([
            'tenant_id' => $tenant->id,
            'user_id'   => Auth::id(),
            'role'      => 'owner',
        ]);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', "Workspace \"{$tenant->name}\" created.");
    }

    public function updateStatus(Request $request, Tenant $tenant)
    {
        $data = $request->validate(['status' => 'required|in:' . implode(',', Tenant::STATUSES)]);
        $tenant->update($data);
        return back()->with('success', "Tenant status updated to {$data['status']}.");
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Workspace deleted.');
    }

    // ── Settings (tenant owner) ───────────────────────────────────────────────

    public function settings()
    {
        $tenant = app('tenant');
        abort_unless(
            $tenant->tenantUsers()->where('user_id', Auth::id())->whereIn('role', ['owner', 'admin'])->exists(),
            403
        );
        return view('tenants.settings', compact('tenant'));
    }

    public function saveSettings(Request $request)
    {
        $tenant = app('tenant');
        abort_unless(
            $tenant->tenantUsers()->where('user_id', Auth::id())->whereIn('role', ['owner', 'admin'])->exists(),
            403
        );

        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'custom_domain' => 'nullable|string|max:253',
            'settings.timezone'  => 'nullable|string|max:60',
            'settings.locale'    => 'nullable|string|max:10',
            'settings.brand_color' => 'nullable|string|max:20',
        ]);

        $tenant->update([
            'name'          => $data['name'],
            'custom_domain' => $data['custom_domain'] ?? null,
            'settings'      => array_merge($tenant->settings ?? [], $data['settings'] ?? []),
        ]);

        return back()->with('success', 'Workspace settings saved.');
    }

    // ── Member management ─────────────────────────────────────────────────────

    public function inviteMember(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => 'required|in:admin,member,viewer',
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        TenantUser::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $user->id],
            ['role' => $data['role']]
        );

        return back()->with('success', "{$user->name} added as {$data['role']}.");
    }

    public function removeMember(Tenant $tenant, User $user)
    {
        abort_if($tenant->owner_id === $user->id, 422, 'Cannot remove the workspace owner.');
        TenantUser::where('tenant_id', $tenant->id)->where('user_id', $user->id)->delete();
        return back()->with('success', "{$user->name} removed from workspace.");
    }
}
PHP;
    }

    private function tenantsIndexView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Tenants')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Workspaces</h1>
        <p class="text-sm text-gray-400 mt-0.5">All tenants across the platform</p>
    </div>
    <button onclick="document.getElementById('createModal').classList.remove('hidden')"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
        + New Workspace
    </button>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
@endif

<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, slug…"
           class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <select name="status" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">All Statuses</option>
        @foreach(\App\Models\Tenant::STATUSES as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors">Filter</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('tenants.index') }}" class="px-4 py-2 text-gray-400 hover:text-white text-sm transition-colors">Clear</a>
    @endif
</form>

@php
$statusColors = [
    'active'    => 'bg-green-900/40 text-green-400 border-green-800',
    'trial'     => 'bg-yellow-900/40 text-yellow-400 border-yellow-800',
    'suspended' => 'bg-red-900/40 text-red-400 border-red-800',
    'cancelled' => 'bg-gray-800 text-gray-500 border-gray-700',
];
@endphp

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-800">
            <tr class="text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 text-left">Workspace</th>
                <th class="px-4 py-3 text-left">Owner</th>
                <th class="px-4 py-3 text-left">Plan</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Domain</th>
                <th class="px-4 py-3 text-left">Created</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($tenants as $tenant)
            <tr class="hover:bg-gray-800/50 transition-colors">
                <td class="px-4 py-3">
                    <div class="font-medium text-white">{{ $tenant->name }}</div>
                    <div class="text-xs text-gray-500 font-mono">{{ $tenant->slug }}</div>
                </td>
                <td class="px-4 py-3 text-gray-300">{{ $tenant->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs bg-gray-800 text-gray-300 border border-gray-700 capitalize">{{ $tenant->plan }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs border {{ $statusColors[$tenant->status] ?? 'bg-gray-800 text-gray-400 border-gray-700' }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400 font-mono">
                    {{ $tenant->custom_domain ?: $tenant->subdomain() }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $tenant->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenants.show', $tenant) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Manage</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No workspaces found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tenants->links() }}</div>

{{-- Create Modal --}}
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.6)">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-bold">New Workspace</h2>
            <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-gray-500 hover:text-white text-xl">×</button>
        </div>
        <form method="POST" action="{{ route('tenants.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Workspace Name *</label>
                <input type="text" name="name" required
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Plan</label>
                <select name="plan" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(['starter','pro','business','enterprise'] as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Create</button>
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="flex-1 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE;
    }

    private function tenantsShowView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', $tenant->name . ' — Workspace')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-2 mb-6 text-sm text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-white transition-colors">Workspaces</a>
        <span>/</span>
        <span class="text-white">{{ $tenant->name }}</span>
    </div>

    @if(session('success'))
        <div class="mb-5 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

        {{-- Info card --}}
        <div class="md:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-white mb-4">Workspace Details</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Name</dt><dd class="text-white font-medium">{{ $tenant->name }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Slug</dt><dd class="text-white font-mono">{{ $tenant->slug }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Owner</dt><dd class="text-gray-300">{{ $tenant->owner?->name }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Plan</dt><dd class="text-gray-300 capitalize">{{ $tenant->plan }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Subdomain</dt><dd class="text-indigo-400 font-mono text-xs">{{ $tenant->subdomain() }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Custom Domain</dt><dd class="text-gray-300 font-mono text-xs">{{ $tenant->custom_domain ?: '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Created</dt><dd class="text-gray-300">{{ $tenant->created_at->format('d M Y') }}</dd></div>
                @if($tenant->trial_ends_at)
                <div><dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Trial Ends</dt><dd class="text-yellow-400">{{ $tenant->trial_ends_at->format('d M Y') }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Status & Actions --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Status</p>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $tenant->status === 'active' ? 'bg-green-900/40 text-green-400 border border-green-800' : ($tenant->status === 'trial' ? 'bg-yellow-900/40 text-yellow-400 border border-yellow-800' : 'bg-red-900/40 text-red-400 border border-red-800') }}">
                    {{ ucfirst($tenant->status) }}
                </span>
            </div>

            <form method="POST" action="{{ route('tenants.status', $tenant) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\Tenant::STATUSES as $s)
                        <option value="{{ $s }}" {{ $tenant->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Update Status</button>
            </form>

            <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                  onsubmit="return confirm('Delete this workspace and all its data?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full py-2 bg-red-900/30 hover:bg-red-900/50 text-red-400 border border-red-800 text-sm font-medium rounded-lg transition-colors">
                    Delete Workspace
                </button>
            </form>
        </div>
    </div>

    {{-- Members --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h2 class="text-sm font-semibold text-white">Members ({{ $tenant->users->count() }})</h2>
            <button onclick="document.getElementById('inviteModal').classList.remove('hidden')"
                    class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">+ Invite</button>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b border-gray-800">
                <tr class="text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Role</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($tenant->users as $member)
                <tr class="text-gray-300">
                    <td class="px-4 py-2 font-medium text-white">{{ $member->name }}</td>
                    <td class="px-4 py-2 text-gray-400 text-xs">{{ $member->email }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-0.5 rounded text-xs capitalize
                            {{ $member->pivot->role === 'owner' ? 'bg-indigo-900/40 text-indigo-400 border border-indigo-800' : 'bg-gray-800 text-gray-400 border border-gray-700' }}">
                            {{ $member->pivot->role }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        @if($member->id !== $tenant->owner_id)
                        <form method="POST" action="{{ route('tenants.members.remove', [$tenant, $member]) }}"
                              onsubmit="return confirm('Remove this member?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Remove</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Invite Modal --}}
<div id="inviteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.6)">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-sm">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-bold">Invite Member</h2>
            <button onclick="document.getElementById('inviteModal').classList.add('hidden')" class="text-gray-500 hover:text-white text-xl">×</button>
        </div>
        <form method="POST" action="{{ route('tenants.members.invite', $tenant) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Email *</label>
                <input type="email" name="email" required placeholder="user@example.com"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Role</label>
                <select name="role" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                    <option value="viewer">Viewer (read-only)</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Invite</button>
                <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')"
                        class="flex-1 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE;
    }

    private function tenantsSettingsView(): string
    {
        return <<<'BLADE'
@extends('layouts.app')
@section('title', 'Workspace Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-bold text-white mb-6">Workspace Settings</h1>

    @if(session('success'))
        <div class="mb-5 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('tenants.settings.save') }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- General --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-white mb-4">General</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Workspace Name *</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Subdomain</label>
                    <div class="flex items-center bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm">
                        <span class="text-indigo-400 font-mono font-medium">{{ $tenant->slug }}</span>
                        <span class="text-gray-500 ml-1">.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Subdomain is fixed after creation.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Custom Domain</label>
                    <input type="text" name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain) }}"
                           placeholder="app.yourcompany.com"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('custom_domain') border-red-500 @enderror">
                    @error('custom_domain')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">Point a CNAME record to <span class="font-mono text-indigo-400">{{ $tenant->subdomain() }}</span> first.</p>
                </div>
            </div>
        </div>

        {{-- Preferences --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-white mb-4">Preferences</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Timezone</label>
                    <select name="settings[timezone]" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ $tenant->getSetting('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Locale</label>
                    <select name="settings[locale]" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['en'=>'English','fr'=>'French','de'=>'German','es'=>'Spanish','ar'=>'Arabic','bn'=>'Bangla'] as $code => $label)
                            <option value="{{ $code }}" {{ $tenant->getSetting('locale','en') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Brand Color</label>
                    <input type="color" name="settings[brand_color]" value="{{ $tenant->getSetting('brand_color', '#6366f1') }}"
                           class="w-full h-9 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Save Settings</button>
        </div>
    </form>

    {{-- Danger Zone --}}
    <div class="bg-gray-900 border border-red-900/50 rounded-2xl p-6 mt-6">
        <h2 class="text-sm font-semibold text-red-400 mb-3">Danger Zone</h2>
        <p class="text-sm text-gray-400 mb-4">Permanently delete this workspace and all associated data. This cannot be undone.</p>
        <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
              onsubmit="return confirm('Are you sure? This will permanently delete the workspace and all its data.')">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-900/30 hover:bg-red-900/60 text-red-400 border border-red-800 text-sm font-medium rounded-lg transition-colors">
                Delete Workspace
            </button>
        </form>
    </div>
</div>
@endsection
BLADE;
    }

    private function tenantRoutes(): string
    {
        return <<<'PHP'
<?php
// ── Multi-Tenant Routes ───────────────────────────────────────────────────────
// Paste into routes/web.php inside the auth middleware group.
//
// Register middleware in bootstrap/app.php (Laravel 11):
//   ->withMiddleware(function (Middleware $middleware) {
//       $middleware->alias([
//           'tenant'        => \App\Http\Middleware\IdentifyTenant::class,
//           'tenant.access' => \App\Http\Middleware\EnsureTenantAccess::class,
//       ]);
//   })
//
// Add to User model:
//   public function tenants() {
//       return $this->belongsToMany(Tenant::class, 'tenant_users')->withPivot('role');
//   }
use App\Http\Controllers\TenantController;

// Admin: manage all tenants
Route::prefix('tenants')->name('tenants.')->group(function () {
    Route::get('/',                              [TenantController::class, 'index'])->name('index');
    Route::post('/',                             [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}',                      [TenantController::class, 'show'])->name('show');
    Route::patch('/{tenant}/status',             [TenantController::class, 'updateStatus'])->name('status');
    Route::delete('/{tenant}',                   [TenantController::class, 'destroy'])->name('destroy');
    Route::post('/{tenant}/members',             [TenantController::class, 'inviteMember'])->name('members.invite');
    Route::delete('/{tenant}/members/{user}',    [TenantController::class, 'removeMember'])->name('members.remove');
});

// Tenant-scoped settings (resolved via IdentifyTenant middleware)
Route::middleware(['tenant', 'tenant.access:admin'])->group(function () {
    Route::get('/workspace/settings',  [TenantController::class, 'settings'])->name('tenants.settings');
    Route::put('/workspace/settings',  [TenantController::class, 'saveSettings'])->name('tenants.settings.save');
});
PHP;
    }
}
