<?php

namespace App\Services\AI;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * MetadataCrudGenerator — Phase 7 of Blueprint-Driven Development
 *
 * Given an entity definition (name + fields), generates a complete Laravel CRUD:
 *   - Migration
 *   - Model
 *   - Controller (full CRUD with validation)
 *   - Views: index (data table), create, edit
 *   - Route stub
 *
 * ZERO AI cost. 100% deterministic. Same quality every time.
 * Eliminates ~80% of AI calls for standard CRUD operations.
 */
class MetadataCrudGenerator
{
    public function generate(array $entity): array
    {
        $name       = Str::studly($entity['name']);
        $table      = Str::snake(Str::plural($name));
        $variable   = Str::camel($name);
        $plural     = Str::plural(Str::snake($name));
        $singTitle  = Str::title(str_replace('_', ' ', Str::snake($name)));
        $plurTitle  = Str::title(str_replace('_', ' ', $plural));
        $fields     = $entity['fields'] ?? [];
        $timestamp  = Carbon::now()->format('Y_m_d_His');

        return [
            [
                'path'    => "database/migrations/{$timestamp}_create_{$table}_table.php",
                'content' => $this->migration($name, $table, $fields),
            ],
            [
                'path'    => "app/Models/{$name}.php",
                'content' => $this->model($name, $table, $fields),
            ],
            [
                'path'    => "app/Http/Controllers/{$name}Controller.php",
                'content' => $this->controller($name, $variable, $plural, $singTitle, $plurTitle, $fields),
            ],
            [
                'path'    => "resources/views/{$plural}/index.blade.php",
                'content' => $this->viewIndex($name, $variable, $plural, $singTitle, $plurTitle, $fields),
            ],
            [
                'path'    => "resources/views/{$plural}/create.blade.php",
                'content' => $this->viewForm('create', $name, $variable, $plural, $singTitle, $plurTitle, $fields),
            ],
            [
                'path'    => "resources/views/{$plural}/edit.blade.php",
                'content' => $this->viewForm('edit', $name, $variable, $plural, $singTitle, $plurTitle, $fields),
            ],
            [
                'path'    => "routes/_crud_{$plural}.txt",
                'content' => "// Add this to routes/web.php:\nRoute::resource('{$plural}', \\App\\Http\\Controllers\\{$name}Controller::class);",
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Migration
    // ─────────────────────────────────────────────────────────────────────────

    private function migration(string $name, string $table, array $fields): string
    {
        $cols = '';
        foreach ($fields as $f) {
            $cols .= $this->migrationCol($f);
        }

        return <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
{$cols}            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }

    private function migrationCol(array $f): string
    {
        $n    = $f['name'];
        $type = $f['type'] ?? 'string';
        $null = ($f['required'] ?? false) ? '' : '->nullable()';

        return match ($type) {
            'text'      => "            \$table->text('{$n}'){$null};\n",
            'integer'   => "            \$table->integer('{$n}')->default(0){$null};\n",
            'decimal'   => "            \$table->decimal('{$n}', 15, 2)->default(0){$null};\n",
            'boolean'   => "            \$table->boolean('{$n}')->default(false);\n",
            'date'      => "            \$table->date('{$n}'){$null};\n",
            'datetime'  => "            \$table->dateTime('{$n}'){$null};\n",
            'json'      => "            \$table->json('{$n}'){$null};\n",
            'enum'      => $this->enumCol($n, $f['options'] ?? ['active', 'inactive'], $null),
            'foreignId' => "            \$table->foreignId('{$n}')->constrained()->cascadeOnDelete();\n",
            default     => "            \$table->string('{$n}'){$null};\n",
        };
    }

    private function enumCol(string $name, array $opts, string $null): string
    {
        $list    = "'" . implode("', '", $opts) . "'";
        $default = "->default('{$opts[0]}')";
        return "            \$table->enum('{$name}', [{$list}]){$null}{$default};\n";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Model
    // ─────────────────────────────────────────────────────────────────────────

    private function model(string $name, string $table, array $fields): string
    {
        $fillable = implode(",\n        ", array_map(fn($f) => "'{$f['name']}'", $fields));

        $casts = '';
        foreach ($fields as $f) {
            $cast = match ($f['type'] ?? 'string') {
                'boolean'  => "'boolean'",
                'integer'  => "'integer'",
                'decimal'  => "'decimal:2'",
                'json'     => "'array'",
                'date'     => "'date'",
                'datetime' => "'datetime'",
                default    => null,
            };
            if ($cast) $casts .= "\n        '{$f['name']}' => {$cast},";
        }
        $castsBlock = $casts ? "\n\n    protected \$casts = [{$casts}\n    ];" : '';

        return <<<PHP
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class {$name} extends Model
{
    use SoftDeletes;

    protected \$fillable = [
        {$fillable},
    ];{$castsBlock}
}
PHP;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Controller
    // ─────────────────────────────────────────────────────────────────────────

    private function controller(string $name, string $var, string $plural, string $singTitle, string $plurTitle, array $fields): string
    {
        $rules = '';
        foreach ($fields as $f) {
            $r = ($f['required'] ?? false) ? "'required'" : "'nullable'";
            $t = $f['type'] ?? 'string';
            if (in_array($t, ['string']))   $r .= ", 'string', 'max:255'";
            if ($t === 'integer')           $r .= ", 'integer'";
            if (in_array($t, ['decimal']))  $r .= ", 'numeric'";
            if ($t === 'boolean')           $r  = "'boolean'";
            if ($t === 'date')              $r .= ", 'date'";
            if ($t === 'datetime')          $r .= ", 'date'";
            if ($t === 'email' || $f['name'] === 'email') $r .= ", 'email'";
            if ($t === 'enum') {
                $in = implode(',', $f['options'] ?? []);
                $r .= ", 'in:{$in}'";
            }
            $rules .= "\n            '{$f['name']}' => [{$r}],";
        }

        // Build searchable fields (first 3 string/text fields)
        $searchable = array_values(array_filter($fields, fn($f) => in_array($f['type'] ?? 'string', ['string', 'text'])));
        $searchClauses = '';
        foreach (array_slice($searchable, 0, 3) as $sf) {
            $searchClauses .= "\n                ->orWhere('{$sf['name']}', 'like', \"%{\$search}%\")";
        }
        if (!$searchClauses) $searchClauses = "\n                ->orWhere('id', 'like', \"%{\$search}%\")";

        return <<<PHP
<?php

namespace App\\Http\\Controllers;

use App\\Models\\{$name};
use Illuminate\\Http\\Request;

class {$name}Controller extends Controller
{
    public function index(Request \$request)
    {
        \$query = {$name}::query();
        if (\$search = \$request->get('search')) {
            \$query->where(function (\$q) use (\$search) {
                \$q->where('id', 0){$searchClauses};
            });
        }
        \${$plural} = \$query->latest()->paginate(20)->withQueryString();
        return view('{$plural}.index', compact('{$plural}'));
    }

    public function create()
    {
        return view('{$plural}.create');
    }

    public function store(Request \$request)
    {
        \$data = \$request->validate([{$rules}
        ]);
        {$name}::create(\$data);
        return redirect()->route('{$plural}.index')->with('success', '{$singTitle} created successfully.');
    }

    public function edit({$name} \${$var})
    {
        return view('{$plural}.edit', compact('{$var}'));
    }

    public function update(Request \$request, {$name} \${$var})
    {
        \$data = \$request->validate([{$rules}
        ]);
        \${$var}->update(\$data);
        return redirect()->route('{$plural}.index')->with('success', '{$singTitle} updated successfully.');
    }

    public function destroy({$name} \${$var})
    {
        \${$var}->delete();
        return redirect()->route('{$plural}.index')->with('success', '{$singTitle} deleted.');
    }
}
PHP;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Views
    // ─────────────────────────────────────────────────────────────────────────

    private function viewIndex(string $name, string $var, string $plural, string $singTitle, string $plurTitle, array $fields): string
    {
        $ths = '';
        $tds = '';
        foreach ($fields as $f) {
            $label = $f['label'] ?? Str::title(str_replace('_', ' ', $f['name']));
            $ths .= "                        <th class=\"px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide\">{$label}</th>\n";
            if (($f['type'] ?? '') === 'boolean') {
                $tds .= "                        <td class=\"px-4 py-3\">" .
                    "<span class=\"text-xs px-2 py-0.5 rounded-full \">{{ \${$var}->{$f['name']} ? 'Yes' : 'No' }}</span>" .
                    "</td>\n";
            } else {
                $tds .= "                        <td class=\"px-4 py-3 text-sm text-gray-700\">{{ \${$var}->{$f['name']} ?? '—' }}</td>\n";
            }
        }

        $colCount = count($fields) + 2;

        return <<<BLADE
@extends('layouts.app')
@section('title', '{$plurTitle}')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{$plurTitle}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage all {$plurTitle}</p>
        </div>
        <a href="{{ route('{$plural}.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New {$singTitle}
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium bg-green-50 text-green-700 border border-green-200">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">{$plurTitle} Table</h2>
            <p class="text-xs text-gray-500 mt-0.5">Search, review, and manage {$plurTitle} records.</p>
        </div>

        {{-- Search bar --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3">
            <form method="GET" class="flex-1 flex items-center gap-2">
                <div class="relative flex-1 max-w-sm">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search {$plurTitle}..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Search</button>
                @if(request('search'))
                <a href="{{ route('{$plural}.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
                @endif
            </form>
            <span class="text-sm text-gray-400">{{ \${$plural}->total() }} records</span>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">#</th>
{$ths}                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse(\${$plural} as \${$var})
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400">{{ \${$var}->id }}</td>
{$tds}                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('{$plural}.edit', \${$var}) }}"
                                   class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 font-medium transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('{$plural}.destroy', \${$var}) }}"
                                      onsubmit="return confirm('Delete this {$singTitle}? This action cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{$colCount}" class="px-4 py-12 text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm text-gray-400">No {$plurTitle} found.</p>
                            <a href="{{ route('{$plural}.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">Create your first {$singTitle}</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(\${$plural}->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ \${$plural}->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
BLADE;
    }

    private function viewForm(string $mode, string $name, string $var, string $plural, string $singTitle, string $plurTitle, array $fields): string
    {
        $isEdit   = $mode === 'edit';
        $title    = $isEdit ? "Edit {$singTitle}" : "New {$singTitle}";
        $action   = $isEdit ? "route('{$plural}.update', \${$var})" : "route('{$plural}.store')";
        $method   = $isEdit ? '@csrf @method(\'PUT\')' : '@csrf';
        $formFields = $this->buildFormFields($fields, $isEdit ? $var : null);

        return <<<BLADE
@extends('layouts.app')
@section('title', '{$title}')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 mb-6 text-sm">
        <a href="{{ route('{$plural}.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{$plurTitle}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">{$title}</span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-6">{$title}</h1>

        <form method="POST" action="{{ {$action} }}">
            {$method}
            <div class="space-y-5">
{$formFields}
            </div>

            <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                    {$title}
                </button>
                <a href="{{ route('{$plural}.index') }}"
                   class="px-5 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE;
    }

    private function buildFormFields(array $fields, ?string $var): string
    {
        $html = '';
        foreach ($fields as $f) {
            $name     = $f['name'];
            $label    = $f['label'] ?? Str::title(str_replace('_', ' ', $name));
            $type     = $f['type'] ?? 'string';
            $required = ($f['required'] ?? false) ? 'required' : '';
            $req_star = ($f['required'] ?? false) ? '<span class="text-red-500 ml-0.5">*</span>' : '';
            $oldVal   = $var
                ? "{{ old('{$name}', \${$var}->{$name}) }}"
                : "{{ old('{$name}') }}";

            $inputHtml = match (true) {
                $type === 'boolean' => $this->boolInput($name, $var),
                $type === 'text'    => "<textarea name=\"{$name}\" id=\"{$name}\" rows=\"4\" {$required} class=\"w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('{$name}') border-red-400 @enderror\">{$oldVal}</textarea>",
                $type === 'enum'    => $this->enumInput($name, $f['options'] ?? [], $var, $required),
                $type === 'date'    => "<input type=\"date\" name=\"{$name}\" id=\"{$name}\" value=\"{$oldVal}\" {$required} class=\"w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('{$name}') border-red-400 @enderror\">",
                $type === 'integer' => "<input type=\"number\" step=\"1\" name=\"{$name}\" id=\"{$name}\" value=\"{$oldVal}\" {$required} class=\"w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('{$name}') border-red-400 @enderror\">",
                $type === 'decimal' => "<input type=\"number\" step=\"0.01\" name=\"{$name}\" id=\"{$name}\" value=\"{$oldVal}\" {$required} class=\"w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('{$name}') border-red-400 @enderror\">",
                default             => "<input type=\"" . ($name === 'email' || $type === 'email' ? 'email' : 'text') . "\" name=\"{$name}\" id=\"{$name}\" value=\"{$oldVal}\" {$required} class=\"w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('{$name}') border-red-400 @enderror\">",
            };

            if ($type === 'boolean') {
                $html .= "                <div class=\"flex items-center gap-2\">{$inputHtml}<label for=\"{$name}\" class=\"text-sm font-medium text-gray-700 cursor-pointer\">{$label}</label></div>\n";
            } else {
                $html .= <<<FLD
                <div>
                    <label for="{$name}" class="block text-sm font-semibold text-gray-700 mb-1.5">{$label}{$req_star}</label>
                    {$inputHtml}
                    @error('{$name}')<p class="mt-1 text-xs text-red-600">{{ \$message }}</p>@enderror
                </div>
FLD;
            }
        }
        return $html;
    }

    private function boolInput(string $name, ?string $var): string
    {
        $checked = $var
            ? "{{ old('{$name}', \${$var}->{$name}) ? 'checked' : '' }}"
            : "{{ old('{$name}') ? 'checked' : '' }}";
        return "<input type=\"checkbox\" name=\"{$name}\" id=\"{$name}\" value=\"1\" {$checked} class=\"w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500\">";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // App Shell — routes + layout + dashboard (zero AI, runs after all CRUDs)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate the app shell: consolidated routes, layout, sidebar nav, dashboard.
     * Call once after all entity CRUDs are generated.
     *
     * @param  array  $entities  Same entity array passed to generate()
     * @param  string $appName   Human-readable project name
     * @param  string $brandColor CSS hex e.g. "#6366f1"
     */
    public function generateAppShell(array $entities, string $appName = 'My App', string $brandColor = '#6366f1'): array
    {
        $validEntities = array_filter($entities, fn($e) => !empty($e['name']));

        return [
            [
                'path'    => 'routes/web.php',
                'content' => $this->shellRoutes($validEntities),
            ],
            [
                'path'    => 'app/Http/Controllers/DashboardController.php',
                'content' => $this->shellDashboardController($validEntities),
            ],
            [
                'path'    => 'resources/views/layouts/app.blade.php',
                'content' => $this->shellLayout($appName, $validEntities, $brandColor),
            ],
            [
                'path'    => 'resources/views/dashboard.blade.php',
                'content' => $this->shellDashboardView($appName, $validEntities),
            ],
            [
                'path'    => 'preview.html',
                'content' => $this->shellPreviewHtml($appName, array_values($validEntities)),
            ],
            [
                'path'    => 'app/Http/Controllers/SettingsController.php',
                'content' => $this->shellSettingsController(),
            ],
            [
                'path'    => 'resources/views/settings/index.blade.php',
                'content' => $this->shellSettingsView($appName),
            ],
            [
                'path'    => 'app/Http/Controllers/LandingController.php',
                'content' => $this->shellLandingController($appName),
            ],
            [
                'path'    => 'resources/views/landing.blade.php',
                'content' => $this->shellLandingView($appName, array_values($validEntities)),
            ],
        ];
    }

    private function shellRoutes(array $entities): string
    {
        $lines = '';
        foreach ($entities as $e) {
            $name   = Str::studly($e['name']);
            $plural = Str::plural(Str::snake($name));
            $lines .= "Route::resource('{$plural}', \\App\\Http\\Controllers\\{$name}Controller::class);\n";
        }

        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LandingController;

Route::get('/landing', [LandingController::class, 'index'])->name('landing');
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
Route::post('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding');
Route::post('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email');
Route::post('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system');

{$lines}
PHP;
    }

    private function shellDashboardController(array $entities): string
    {
        $counts = '';
        $pass   = '';
        foreach ($entities as $e) {
            $name    = Str::studly($e['name']);
            $var     = Str::camel($name);
            $counts .= "        \${$var}Count = \\App\\Models\\{$name}::count();\n";
            $pass   .= "            '{$var}Count' => \${$var}Count,\n";
        }

        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
{$counts}
        return view('dashboard', [
{$pass}        ]);
    }
}
PHP;
    }

    private function shellLayout(string $appName, array $entities, string $brandColor): string
    {
        $navLinks = '';
        foreach ($entities as $e) {
            $name      = Str::studly($e['name']);
            $plural    = Str::plural(Str::snake($name));
            $plurTitle = Str::title(str_replace('_', ' ', $plural));
            $icon      = $this->entityIcon($e['name']);
            $navLinks .= <<<BLADE

                    <a href="{{ route('{$plural}.index') }}"
                       class="nav-link {{ request()->routeIs('{$plural}.*') ? 'active' : '' }}">
                        <span style="font-size:15px;line-height:1;width:18px;flex-shrink:0;text-align:center">{$icon}</span>
                        {$plurTitle}
                    </a>
BLADE;
        }

        return <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '{$appName}')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root { --brand: {$brandColor}; }
        body { background:#f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .sidebar { width:240px; min-height:100vh; background:#1e1b4b; flex-shrink:0; }
        .nav-link { display:flex; align-items:center; gap:10px; padding:10px 16px; border-radius:10px;
                    color:#94a3b8; text-decoration:none; font-size:13.5px; font-weight:500; transition:.15s; }
        .nav-link:hover, .nav-link.active { background:rgba(99,102,241,.25); color:#e2e8f0; }
        .nav-icon { width:16px; height:16px; flex-shrink:0; }
        .main-content { flex:1; overflow-auto; }
        .topbar { background:#fff; border-bottom:1px solid #e2e8f0; padding:0 24px; height:56px;
                  display:flex; align-items:center; justify-content:space-between; }
        .page-body { padding:24px; }
        @yield('styles')
    </style>
    @yield('head')
</head>
<body>
<div style="display:flex;">

    {{-- Sidebar --}}
    <nav class="sidebar" x-data="{ open: true }">
        <div style="padding:20px 16px; border-bottom:1px solid rgba(255,255,255,.08);">
            <div style="font-size:16px; font-weight:700; color:#fff; letter-spacing:-.3px;">{$appName}</div>
            <div style="font-size:11px; color:#818cf8; margin-top:2px;">Management System</div>
        </div>
        <div style="padding:12px 8px;">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span style="font-size:15px;line-height:1;width:18px;flex-shrink:0;text-align:center">📊</span>
                Dashboard
            </a>
{$navLinks}
            <div style="margin:8px 8px 4px;padding-top:8px;border-top:1px solid rgba(255,255,255,.07);font-size:10px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.08em">System</div>
            <a href="{{ route('settings') }}"
               class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
                <span style="font-size:15px;line-height:1;width:18px;flex-shrink:0;text-align:center">⚙️</span>
                Settings
            </a>
        </div>
    </nav>

    {{-- Main --}}
    <div class="main-content">
        <div class="topbar">
            <div style="font-size:14px; font-weight:600; color:#1e293b;">@yield('title', 'Dashboard')</div>
            <div style="font-size:12px; color:#64748b;">{{ now()->format('D, d M Y') }}</div>
        </div>
        <div class="page-body">
            @if(session('success'))
                <div style="background:#dcfce7; border:1px solid #86efac; color:#166534; padding:10px 16px; border-radius:10px; margin-bottom:16px; font-size:13px;">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>

</div>
</body>
</html>
BLADE;
    }

    private function shellDashboardView(string $appName, array $entities): string
    {
        $profile = $this->domainProfile($appName, $entities);
        $cards = '';
        $colors = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];
        $i = 0;
        foreach ($entities as $e) {
            $name      = Str::studly($e['name']);
            $var       = Str::camel($name);
            $singTitle = Str::title(str_replace('_', ' ', Str::snake($name)));
            $plural    = Str::plural(Str::snake($name));
            $plurTitle = Str::title(str_replace('_', ' ', $plural));
            $color     = $colors[$i % count($colors)];
            $icon      = $this->entityIcon($e['name']);
            $kpiPfx    = $profile['kpiPfx'][$i % 8];
            $i++;
            $cards .= <<<BLADE

        <a href="{{ route('{$plural}.index') }}" class="stat-card" style="--c:{$color};">
            <div class="stat-icon" style="font-size:22px;line-height:1">{$icon}</div>
            <div class="stat-num">{{ \${$var}Count }}</div>
            <div class="stat-label">{$kpiPfx} {$plurTitle}</div>
            <div class="stat-hint">View all →</div>
        </a>
BLADE;
        }

        return <<<BLADE
@extends('layouts.app')
@section('title', 'Dashboard')

@section('styles')
.stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; margin-bottom:32px; }
.stat-card { background:#fff; border-radius:16px; padding:20px; text-decoration:none; display:flex;
             flex-direction:column; align-items:center; text-align:center;
             border:1px solid #e2e8f0; transition:.2s; aspect-ratio:1; justify-content:center; }
.stat-card:hover { box-shadow:0 8px 24px color-mix(in srgb,var(--c) 20%,transparent); border-color:var(--c); transform:translateY(-2px); }
.stat-icon { width:44px;height:44px;border-radius:12px;background:color-mix(in srgb,var(--c) 12%,#fff);
             display:flex;align-items:center;justify-content:center;margin-bottom:12px; }
.stat-num { font-size:28px; font-weight:800; color:#1e293b; line-height:1; }
.stat-label { font-size:12px; font-weight:600; color:#64748b; margin-top:4px; }
.stat-hint { font-size:11px; color:var(--c); margin-top:6px; }
@endsection

@section('content')
<div style="margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;color:#1e293b;">{$appName}</h1>
    <p style="font-size:13px;color:#64748b;margin-top:4px;">Overview of all modules</p>
</div>

<div class="stats-grid">
{$cards}
</div>

<div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:24px;">
    <h2 style="font-size:15px;font-weight:700;color:#1e293b;margin-bottom:4px;">Getting Started</h2>
    <p style="font-size:13px;color:#64748b;line-height:1.6;">
        Your core modules are ready. Click any stat card above to manage records,
        or use the sidebar navigation. Add more features by describing what you need in the AI builder.
    </p>
</div>
@endsection
BLADE;
    }

    private function shellPreviewHtml(string $appName, array $entities): string
    {
        $profile     = $this->domainProfile($appName, $entities);
        $brand       = $profile['brand'];
        $brandDk     = $profile['brandDk'];
        $brandLight  = $profile['brandLight'];
        $gradient    = $profile['gradient'];
        $heroTag     = $profile['heroTag'];
        $heroSub     = $profile['heroSub'];
        $userRole    = $profile['userRole'];
        $userName    = $profile['userName'];
        $sbSection   = $profile['sbSection'];
        $featSfx     = $profile['featSuffix'];
        $appInitial  = strtoupper(mb_substr($appName, 0, 1));
        $userInitials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', str_replace(' ', '', $userName)), 0, 2));

        $palette = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#06b6d4','#84cc16','#a855f7'];
        $samples = [1203,847,342,2891,127,438,73,512,284,96,156,631,89,447,238,72];
        $trends  = ['+14.2%','+8.7%','+5.1%','+22.3%','+3.8%','+11.6%','+6.4%','+9.2%'];
        $counts  = [1203,847,342,2891,127,438,73,512,284,96,156,631,89,447,238,72,319,184];

        // Hero metrics HTML
        $metricsHtml = '';
        foreach ($profile['metrics'] as $idx => $m) {
            if ($idx > 0) {
                $metricsHtml .= '<div style="width:1px;background:rgba(255,255,255,.08)"></div>';
            }
            $metricsHtml .= '<div style="text-align:center"><div class="metric-num">' . $m['num'] . '</div><div class="metric-lbl">' . $m['lbl'] . '</div></div>';
        }

        // Sidebar nav links with domain-specific entity icons
        $sidebarLinks = '';
        foreach ($entities as $i => $e) {
            $label = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $icon  = $this->entityIcon($e['name']);
            $cnt   = $counts[$i % count($counts)];
            $sidebarLinks .= "<button class=\"nav-item\" onclick=\"navTo('{$e['name']}','{$label}',this)\" id=\"nav-{$e['name']}\">"
                           . "<span class=\"nav-icon\">{$icon}</span>"
                           . "<span class=\"nav-label\">{$label}</span>"
                           . "<span class=\"nav-badge\">{$cnt}</span>"
                           . "</button>\n";
        }

        // KPI stat cards with domain-prefixed labels
        $cards = '';
        foreach ($entities as $i => $e) {
            $label   = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $color   = $palette[$i % count($palette)];
            $icon    = $this->entityIcon($e['name']);
            $rc      = $this->realisticCount($e['name'], $profile['domain']);
            $count   = number_format($rc['count']);
            $trend   = $rc['trend'];
            $bgLight = $color . '18';
            $kpiPfx  = $profile['kpiPfx'][$i % 8];
            $kpiLbl  = $kpiPfx . ' ' . $label;
            $cards  .= <<<HTML
<div class="kpi-card" onclick="navTo('{$e['name']}','{$label}',document.getElementById('nav-{$e['name']}'))">
  <div class="kpi-top">
    <div class="kpi-icon" style="background:{$bgLight};color:{$color}">{$icon}</div>
    <span class="kpi-trend">▲ {$trend}</span>
  </div>
  <div class="kpi-value">{$count}</div>
  <div class="kpi-label">{$kpiLbl}</div>
</div>
HTML;
        }

        // Feature cards for landing (first 6 entities) with domain copy
        $featureCards = '';
        foreach (array_slice($entities, 0, 6) as $i => $e) {
            $label = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $icon  = $this->entityIcon($e['name']);
            $color = $palette[$i % count($palette)];
            $bgL   = $color . '22';
            $featureCards .= <<<HTML
<div class="feat-card">
  <div class="feat-icon" style="background:{$bgL};color:{$color}">{$icon}</div>
  <div class="feat-title">{$label} Management</div>
  <div class="feat-desc">Complete {$label} records {$featSfx}</div>
</div>
HTML;
        }

        // Chart data (first 8 entities)
        $chartSlice  = array_slice($entities, 0, 8);
        $chartLabels = implode(',', array_map(fn($e) => '"' . Str::title(str_replace('_',' ',Str::snake($e['name']))) . '"', $chartSlice));
        $chartData   = implode(',', array_map(fn($i) => $samples[$i % count($samples)], range(0, count($chartSlice) - 1)));
        $chartBg     = implode(',', array_map(fn($i) => '"' . $palette[$i % count($palette)] . '"', range(0, count($chartSlice) - 1)));

        // Entities JSON for JS module renderer
        $entitiesJson = json_encode(array_values(array_map(fn($e) => [
            'name'  => $e['name'],
            'label' => Str::title(str_replace('_', ' ', Str::snake($e['name']))),
        ], $entities)));

        // Settings screen HTML (PHP-built, JSON-passed to JS)
        $settingsHtmlJson = json_encode($this->buildSettingsHtml($appName, $userName, $userRole, $userInitials, $brand));

        $entityCount = count($entities);
        $year        = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$appName}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{--brand:{$brand};--brand-dark:{$brandDk};--brand-light:{$brandLight};--success:#10b981;--warning:#f59e0b;--danger:#ef4444;--sidebar:#0f172a;--surface:#ffffff;--bg:#f8fafc;--border:#e2e8f0;--text:#0f172a;--muted:#64748b;--subtle:#f1f5f9}
*{box-sizing:border-box;margin:0;padding:0;font-family:'Inter',system-ui,sans-serif}
/* ── screens ── */
.screen{display:none;min-height:100vh}
.screen.active{display:flex}
/* ═══════════════ LANDING ═══════════════ */
#screen-landing{flex-direction:column;background:linear-gradient(160deg,{$gradient})}
.land-nav{display:flex;justify-content:space-between;align-items:center;padding:18px 64px;border-bottom:1px solid rgba(255,255,255,.06);position:sticky;top:0;backdrop-filter:blur(12px);background:rgba(10,15,30,.7);z-index:10}
.land-logo{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:800;color:#fff;letter-spacing:-.3px}
.land-logo-dot{width:8px;height:8px;background:var(--brand);border-radius:50%;display:inline-block}
.land-pills{display:flex;gap:28px}
.land-pill{color:#94a3b8;font-size:14px;font-weight:500;cursor:pointer;background:none;border:none;transition:color .15s}
.land-pill:hover{color:#fff}
.land-actions{display:flex;gap:10px}
.btn-outline{background:transparent;border:1px solid rgba(255,255,255,.18);color:#e2e8f0;padding:9px 22px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;transition:all .15s}
.btn-outline:hover{background:rgba(255,255,255,.08)}
.btn-solid{background:var(--brand);border:none;color:#fff;padding:9px 22px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;transition:all .15s;box-shadow:0 0 20px rgba(99,102,241,.4)}
.btn-solid:hover{background:var(--brand-dark)}
.hero{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:80px 40px 60px;flex:1}
.hero-eyebrow{display:inline-flex;align-items:center;gap:6px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;padding:6px 16px;border-radius:100px;font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:28px}
.hero-h1{font-size:60px;font-weight:900;line-height:1.05;letter-spacing:-1.5px;margin-bottom:24px;background:linear-gradient(130deg,#fff 20%,#c7d2fe 80%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{font-size:17px;color:#94a3b8;max-width:580px;line-height:1.7;margin-bottom:44px}
.hero-ctas{display:flex;gap:14px;margin-bottom:64px}
.btn-cta-primary{background:var(--brand);border:none;color:#fff;padding:15px 36px;border-radius:12px;cursor:pointer;font-size:15px;font-weight:700;box-shadow:0 4px 24px rgba(99,102,241,.5);transition:all .2s}
.btn-cta-primary:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(99,102,241,.6)}
.btn-cta-ghost{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);color:#e2e8f0;padding:15px 36px;border-radius:12px;cursor:pointer;font-size:15px;font-weight:600;transition:all .2s}
.btn-cta-ghost:hover{background:rgba(255,255,255,.1)}
.hero-metrics{display:flex;gap:56px;margin-bottom:72px}
.metric-num{font-size:36px;font-weight:900;color:#fff;letter-spacing:-1px}
.metric-lbl{font-size:12px;color:#475569;margin-top:4px;font-weight:500;letter-spacing:.04em;text-transform:uppercase}
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:960px;width:100%}
.feat-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px 24px;transition:all .2s;cursor:default}
.feat-card:hover{background:rgba(255,255,255,.07);border-color:rgba(99,102,241,.3);transform:translateY(-3px)}
.feat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:14px}
.feat-title{font-size:14px;font-weight:700;color:#e2e8f0;margin-bottom:6px}
.feat-desc{font-size:13px;color:#64748b;line-height:1.5}
/* ═══════════════ LOGIN ═══════════════ */
#screen-login{align-items:center;justify-content:center;background:linear-gradient(160deg,{$gradient})}
.login-wrap{width:420px}
.login-brand{text-align:center;margin-bottom:32px}
.login-brand-name{font-size:22px;font-weight:800;color:#fff;letter-spacing:-.3px}
.login-brand-sub{font-size:13px;color:#475569;margin-top:4px}
.login-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:36px;backdrop-filter:blur(20px)}
.login-title{font-size:20px;font-weight:700;color:#fff;margin-bottom:4px}
.login-hint{font-size:13px;color:#475569;margin-bottom:28px}
.field{margin-bottom:16px}
.field-label{display:block;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px}
.field-input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f8fafc;padding:12px 14px;border-radius:10px;font-size:14px;outline:none;transition:border .15s}
.field-input:focus{border-color:var(--brand);background:rgba(99,102,241,.08)}
.btn-signin{width:100%;background:var(--brand);border:none;color:#fff;padding:13px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;margin-top:6px;box-shadow:0 2px 12px rgba(99,102,241,.4);transition:all .15s}
.btn-signin:hover{background:var(--brand-dark)}
.login-back{display:block;text-align:center;margin-top:20px;color:#475569;font-size:13px;cursor:pointer;transition:color .15s}
.login-back:hover{color:#a5b4fc}
/* ═══════════════ DASHBOARD ═══════════════ */
#screen-dashboard{background:var(--bg);color:var(--text);overflow:hidden}
/* Sidebar */
.sidebar{width:256px;background:var(--sidebar);flex-shrink:0;display:flex;flex-direction:column;overflow-y:auto}
.sb-logo{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.06)}
.sb-logo-name{font-size:15px;font-weight:800;color:#fff;letter-spacing:-.2px}
.sb-logo-tag{font-size:11px;color:#334155;margin-top:2px;font-weight:500}
.sb-search{padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.06)}
.sb-search input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#94a3b8;padding:8px 12px 8px 32px;border-radius:8px;font-size:12px;outline:none}
.sb-search{position:relative}
.sb-search::before{content:'🔍';position:absolute;left:22px;top:50%;transform:translateY(-50%);font-size:11px;pointer-events:none}
.sb-section{padding:16px 16px 6px;font-size:10px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.1em}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 16px;color:#64748b;font-size:13px;font-weight:500;cursor:pointer;border:none;background:none;width:100%;text-align:left;transition:all .15s;border-radius:0;position:relative}
.nav-item:hover{background:rgba(255,255,255,.04);color:#cbd5e1}
.nav-item.active{background:rgba(99,102,241,.12);color:#fff}
.nav-item.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--brand);border-radius:0 2px 2px 0}
.nav-icon{width:20px;text-align:center;font-size:14px;flex-shrink:0}
.nav-label{flex:1}
.nav-badge{background:rgba(255,255,255,.08);color:#64748b;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px}
.nav-item.active .nav-badge{background:rgba(99,102,241,.3);color:#a5b4fc}
.sb-user{padding:16px;border-top:1px solid rgba(255,255,255,.06);margin-top:auto;display:flex;align-items:center;gap:10px}
.sb-user-avatar{width:34px;height:34px;background:linear-gradient(135deg,var(--brand),#8b5cf6);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;flex-shrink:0}
.sb-user-name{font-size:13px;font-weight:600;color:#e2e8f0}
.sb-user-role{font-size:11px;color:#475569}
.sb-logout{background:none;border:none;color:#334155;cursor:pointer;font-size:11px;margin-left:auto;padding:4px 8px;border-radius:6px;transition:all .15s}
.sb-logout:hover{color:#ef4444;background:rgba(239,68,68,.1)}
/* Main */
.dash-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 28px;height:60px;display:flex;align-items:center;gap:16px;flex-shrink:0}
.breadcrumb{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);flex:1}
.breadcrumb-sep{color:#d1d5db}
.breadcrumb-cur{color:var(--text);font-weight:600}
.topbar-search{display:flex;align-items:center;gap:8px;background:var(--subtle);border:1px solid var(--border);border-radius:8px;padding:7px 14px;width:240px}
.topbar-search input{background:none;border:none;outline:none;font-size:13px;color:var(--text);width:100%}
.topbar-search input::placeholder{color:#94a3b8}
.topbar-actions{display:flex;align-items:center;gap:12px}
.notif-btn{background:none;border:none;cursor:pointer;font-size:18px;position:relative;padding:4px}
.notif-dot{position:absolute;top:4px;right:4px;width:7px;height:7px;background:#ef4444;border-radius:50%;border:1.5px solid #fff}
.user-chip{display:flex;align-items:center;gap:8px;padding:6px 12px;background:var(--subtle);border:1px solid var(--border);border-radius:8px;cursor:pointer}
.user-chip-avatar{width:24px;height:24px;background:linear-gradient(135deg,var(--brand),#8b5cf6);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:800}
.user-chip-name{font-size:12px;font-weight:600;color:var(--text)}
/* Content */
.content-area{flex:1;overflow-y:auto;padding:24px 28px}
/* KPI cards */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:24px}
.kpi-card{background:#fff;border-radius:14px;padding:20px;border:1px solid var(--border);cursor:pointer;transition:all .2s;box-shadow:0 1px 4px rgba(0,0,0,.04)}
.kpi-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.09);transform:translateY(-2px)}
.kpi-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.kpi-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
.kpi-trend{font-size:11px;font-weight:700;color:var(--success);background:#ecfdf5;padding:3px 8px;border-radius:20px}
.kpi-value{font-size:28px;font-weight:800;letter-spacing:-1px;color:var(--text);margin-bottom:4px}
.kpi-label{font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
/* Charts */
.chart-row{display:grid;grid-template-columns:1.6fr 1fr;gap:20px;margin-bottom:24px}
.chart-card{background:#fff;border-radius:14px;border:1px solid var(--border);padding:22px;box-shadow:0 1px 4px rgba(0,0,0,.04)}
.chart-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.chart-title{font-size:14px;font-weight:700;color:var(--text)}
.chart-period{font-size:11px;color:var(--muted);background:var(--subtle);padding:4px 10px;border-radius:6px;font-weight:500}
/* Table card */
.table-card{background:#fff;border-radius:14px;border:1px solid var(--border);overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04)}
.table-header{display:flex;justify-content:space-between;align-items:center;padding:18px 20px;border-bottom:1px solid var(--border)}
.table-title{font-size:14px;font-weight:700;color:var(--text)}
.table-actions{display:flex;gap:8px}
.btn-sm{padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:none;transition:all .15s}
.btn-sm-outline{background:#fff;border:1px solid var(--border)!important;color:var(--muted)}
.btn-sm-outline:hover{border-color:var(--brand)!important;color:var(--brand)}
.btn-sm-primary{background:var(--brand);color:#fff}
.btn-sm-primary:hover{background:var(--brand-dark)}
.data-table{width:100%;border-collapse:collapse}
.data-table th{text-align:left;padding:11px 20px;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;background:var(--subtle);border-bottom:1px solid var(--border)}
.data-table td{padding:13px 20px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:#fafbfc}
.status-pill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700}
.pill-green{background:#ecfdf5;color:#059669}
.pill-blue{background:#eff6ff;color:#2563eb}
.pill-amber{background:#fffbeb;color:#d97706}
.pill-gray{background:var(--subtle);color:var(--muted)}
.pill-red{background:#fef2f2;color:#dc2626}
.action-btn{background:none;border:1px solid var(--border);color:var(--muted);padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;transition:all .15s}
.action-btn:hover{border-color:var(--brand);color:var(--brand)}
.tbl-pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid var(--border)}
.pg-info{font-size:12px;color:var(--muted)}
.pg-btns{display:flex;gap:4px}
.pg-btn{background:#fff;border:1px solid var(--border);color:var(--muted);width:30px;height:30px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center}
.pg-btn:hover{border-color:var(--brand);color:var(--brand)}
.pg-btn.cur{background:var(--brand);border-color:var(--brand);color:#fff}
/* Module list search bar */
.list-toolbar{display:flex;gap:10px;margin-bottom:20px}
.search-box{flex:1;display:flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--border);border-radius:10px;padding:10px 14px}
.search-box input{background:none;border:none;outline:none;font-size:13px;color:var(--text);width:100%}
.filter-btn{background:#fff;border:1px solid var(--border);color:var(--muted);padding:10px 16px;border-radius:10px;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px}
.filter-btn:hover{border-color:var(--brand);color:var(--brand)}
</style>
</head>
<body>

<!-- ════════════════════════════════════
     SCREEN 1 — LANDING
════════════════════════════════════ -->
<div id="screen-landing" class="screen active">
  <nav class="land-nav">
    <div class="land-logo">
      <span style="background:var(--brand);color:#fff;width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:16px;font-weight:900">{$appInitial}</span>
      {$appName}
    </div>
    <div class="land-pills">
      <button class="land-pill">Features</button>
      <button class="land-pill">Modules</button>
      <button class="land-pill">About</button>
    </div>
    <div class="land-actions">
      <button class="btn-outline" onclick="go('login')">Sign In</button>
      <button class="btn-solid" onclick="go('login')">Get Started →</button>
    </div>
  </nav>
  <div class="hero">
    <div class="hero-eyebrow">{$heroTag} &nbsp;·&nbsp; {$entityCount} Modules</div>
    <h1 class="hero-h1">{$appName}</h1>
    <p class="hero-sub">{$heroSub}</p>
    <div class="hero-ctas">
      <button class="btn-cta-primary" onclick="go('login')">🚀 Get Started Free</button>
      <button class="btn-cta-ghost" onclick="go('login')">View Dashboard →</button>
    </div>
    <div class="hero-metrics">{$metricsHtml}</div>
    <div class="feat-grid">{$featureCards}</div>
  </div>
</div>

<!-- ════════════════════════════════════
     SCREEN 2 — LOGIN
════════════════════════════════════ -->
<div id="screen-login" class="screen">
  <div class="login-wrap">
    <div class="login-brand">
      <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--brand),#8b5cf6);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#fff;margin:0 auto 14px">{$appInitial}</div>
      <div class="login-brand-name">{$appName}</div>
      <div class="login-brand-sub">Sign in to your workspace</div>
    </div>
    <div class="login-card">
      <div class="login-title">Welcome back 👋</div>
      <div class="login-hint">Enter your credentials to continue</div>
      <div class="field">
        <label class="field-label">Email Address</label>
        <input class="field-input" type="email" value="admin@example.com">
      </div>
      <div class="field">
        <label class="field-label">Password</label>
        <input class="field-input" type="password" value="password">
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;cursor:pointer">
          <input type="checkbox" checked style="accent-color:var(--brand)"> Remember me
        </label>
        <a style="font-size:12px;color:var(--brand);cursor:pointer">Forgot password?</a>
      </div>
      <button class="btn-signin" onclick="go('dashboard')">Sign In →</button>
    </div>
    <div class="login-back" onclick="go('landing')">← Back to home</div>
  </div>
</div>

<!-- ════════════════════════════════════
     SCREEN 3 — DASHBOARD
════════════════════════════════════ -->
<div id="screen-dashboard" class="screen">

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sb-logo">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--brand),#8b5cf6);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;flex-shrink:0">{$appInitial}</div>
        <div>
          <div class="sb-logo-name">{$appName}</div>
          <div class="sb-logo-tag">Admin Panel</div>
        </div>
      </div>
    </div>
    <div class="sb-search">
      <input placeholder="Search modules..." id="sb-search-input">
    </div>
    <div class="sb-section">Overview</div>
    <button class="nav-item active" id="nav-dashboard" onclick="showDashboard(this)">
      <span class="nav-icon">📊</span>
      <span class="nav-label">Dashboard</span>
    </button>
    <div class="sb-section">{$sbSection}</div>
    {$sidebarLinks}
    <div class="sb-section" style="padding-top:12px;margin-top:4px;border-top:1px solid rgba(255,255,255,.04)">System</div>
    <button class="nav-item" onclick="showSettings(this)" id="nav-settings">
      <span class="nav-icon">⚙️</span><span class="nav-label">Settings</span>
    </button>
    <div class="sb-user">
      <div class="sb-user-avatar">{$userInitials}</div>
      <div>
        <div class="sb-user-name">{$userName}</div>
        <div class="sb-user-role">{$userRole}</div>
      </div>
      <button class="sb-logout" onclick="go('landing')" title="Logout">⏻</button>
    </div>
  </div>

  <!-- Main area -->
  <div class="dash-main">
    <!-- Top bar -->
    <div class="topbar">
      <div class="breadcrumb">
        <span style="color:var(--muted)">{$appName}</span>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-cur" id="breadcrumb-cur">Dashboard</span>
      </div>
      <div class="topbar-search">
        <span style="color:#94a3b8;font-size:13px">🔍</span>
        <input placeholder="Search anything...">
      </div>
      <div class="topbar-actions">
        <button class="notif-btn">🔔<span class="notif-dot"></span></button>
        <div class="user-chip">
          <div class="user-chip-avatar">{$userInitials}</div>
          <span class="user-chip-name">{$userName}</span>
          <span style="color:#94a3b8;font-size:10px">▾</span>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="content-area" id="dash-content">
      <!-- KPI Grid -->
      <div class="kpi-grid">{$cards}</div>
      <!-- Charts -->
      <div class="chart-row">
        <div class="chart-card">
          <div class="chart-header">
            <span class="chart-title">📈 Records Overview</span>
            <span class="chart-period">Last 30 days</span>
          </div>
          <canvas id="barChart" height="160"></canvas>
        </div>
        <div class="chart-card">
          <div class="chart-header">
            <span class="chart-title">🥧 Distribution</span>
            <span class="chart-period">All time</span>
          </div>
          <canvas id="doughChart" height="160"></canvas>
        </div>
      </div>
      <!-- Recent records table -->
      <div class="table-card" id="recent-table"></div>
    </div>
  </div>
</div>

<script>
const ENTITIES      = {$entitiesJson};
const PALETTE       = [{$chartBg}];
const SETTINGS_HTML = {$settingsHtmlJson};
const SAMPLES  = [1203,847,342,2891,127,438,73,512,284,96,156,631,89,447,238,72];
const STATUSES = [
  {label:'Active',    cls:'pill-green'},
  {label:'Pending',   cls:'pill-amber'},
  {label:'Completed', cls:'pill-blue'},
  {label:'Inactive',  cls:'pill-gray'},
  {label:'Cancelled', cls:'pill-red'}
];

/* ── Screen switching ── */
function go(s){
  document.querySelectorAll('.screen').forEach(el=>el.classList.remove('active'));
  document.getElementById('screen-'+s).classList.add('active');
  if(s==='dashboard'){ initCharts(); renderRecentTable(ENTITIES[0]); }
}

/* ── Charts ── */
let chartsInited=false;
function initCharts(){
  if(chartsInited)return; chartsInited=true;
  const labels=[{$chartLabels}];
  const data=[{$chartData}];
  new Chart(document.getElementById('barChart'),{
    type:'bar',
    data:{labels,datasets:[{data,backgroundColor:PALETTE,borderRadius:8,borderSkipped:false}]},
    options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f8fafc'},ticks:{font:{size:11},color:'#94a3b8'}},x:{grid:{display:false},ticks:{font:{size:11},color:'#94a3b8'}}},maintainAspectRatio:false}
  });
  new Chart(document.getElementById('doughChart'),{
    type:'doughnut',
    data:{labels,datasets:[{data,backgroundColor:PALETTE,borderWidth:3,borderColor:'#fff'}]},
    options:{plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:12,color:'#64748b'}}},cutout:'68%',maintainAspectRatio:false}
  });
}

/* ── Recent table ── */
function renderRecentTable(entity){
  if(!entity)return;
  var rows='';
  var fnames=['James Wilson','Maria Garcia','Chen Wei','Aisha Patel','Lucas Costa','Sophie Martin','Omar Hassan','Yuki Tanaka','Carlos Silva','Emma Brown'];
  var dates=['2026-06-11','2026-06-10','2026-06-09','2026-06-08','2026-06-07','2026-06-06'];
  for(var r=0;r<8;r++){
    var s=STATUSES[r%STATUSES.length];
    rows+='<tr>'
      +'<td><div style="display:flex;align-items:center;gap:10px"><div style="width:32px;height:32px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#64748b;flex-shrink:0">'+(r+1)+'</div><div><div style="font-weight:600;color:#0f172a">'+entity.label+' #'+(1001+r)+'</div><div style="font-size:11px;color:#94a3b8">ID-'+(10000+r*7)+'</div></div></div></td>'
      +'<td style="color:#64748b">'+fnames[r%fnames.length]+'</td>'
      +'<td><span class="status-pill '+s.cls+'">'+s.label+'</span></td>'
      +'<td style="color:#94a3b8;font-size:12px">'+dates[r%dates.length]+'</td>'
      +'<td><button class="action-btn" onclick="showForm(\''+entity.name+'\',\''+entity.label+'\')">View</button> <button class="action-btn">Edit</button></td>'
    +'</tr>';
  }
  document.getElementById('recent-table').innerHTML=
    '<div class="table-header">'
    +'<div class="table-title">📋 Recent '+entity.label+' Records</div>'
    +'<div class="table-actions"><button class="btn-sm btn-sm-outline" style="border:1px solid var(--border)">Export</button><button class="btn-sm btn-sm-primary" onclick="showForm(\''+entity.name+'\',\''+entity.label+'\')">+ Add New</button></div>'
    +'</div>'
    +'<table class="data-table"><thead><tr><th>Record</th><th>Name</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>'+rows+'</tbody></table>'
    +'<div class="tbl-pagination"><span class="pg-info">Showing 1–8 of 247 records</span><div class="pg-btns"><button class="pg-btn">‹</button><button class="pg-btn cur">1</button><button class="pg-btn">2</button><button class="pg-btn">3</button><button class="pg-btn">›</button></div></div>';
}

/* ── Dashboard home ── */
function showDashboard(el){
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  if(el)el.classList.add('active');
  document.getElementById('breadcrumb-cur').textContent='Dashboard';
  document.getElementById('dash-content').innerHTML=
    '<div class="kpi-grid">'+document.querySelector('.kpi-grid').innerHTML+'</div>'
    +'<div class="chart-row"><div class="chart-card"><div class="chart-header"><span class="chart-title">📈 Records Overview</span><span class="chart-period">Last 30 days</span></div><canvas id="barChart" height="160"></canvas></div><div class="chart-card"><div class="chart-header"><span class="chart-title">🥧 Distribution</span><span class="chart-period">All time</span></div><canvas id="doughChart" height="160"></canvas></div></div>'
    +'<div class="table-card" id="recent-table"></div>';
  chartsInited=false; initCharts(); renderRecentTable(ENTITIES[0]);
}

/* ── Module list view ── */
function navTo(name,label,el){
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  if(el)el.classList.add('active');
  document.getElementById('breadcrumb-cur').textContent=label;
  var rows='';
  var fnames=['James Wilson','Maria Garcia','Chen Wei','Aisha Patel','Lucas Costa','Sophie Martin','Omar Hassan','Yuki Tanaka','Carlos Silva','Emma Brown','David Kim','Sara Johnson'];
  var emails=['james@example.com','maria@example.com','chen@example.com','aisha@example.com','lucas@example.com','sophie@example.com','omar@example.com','yuki@example.com','carlos@example.com','emma@example.com'];
  for(var r=0;r<10;r++){
    var s=STATUSES[r%STATUSES.length];
    var val=Math.floor(Math.random()*9000+1000);
    rows+='<tr>'
      +'<td><input type="checkbox" style="accent-color:var(--brand)"></td>'
      +'<td><div style="display:flex;align-items:center;gap:10px"><div style="width:34px;height:34px;background:linear-gradient(135deg,'+PALETTE[r%PALETTE.length]+','+PALETTE[(r+2)%PALETTE.length]+');border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0">'+(r+1)+'</div><div><div style="font-weight:600;color:#0f172a">'+label+' #'+(1001+r)+'</div><div style="font-size:11px;color:#94a3b8">'+emails[r%emails.length]+'</div></div></div></td>'
      +'<td style="font-weight:500">'+fnames[r%fnames.length]+'</td>'
      +'<td><span class="status-pill '+s.cls+'">'+s.label+'</span></td>'
      +'<td style="font-weight:600;color:#0f172a">$'+val.toLocaleString()+'</td>'
      +'<td style="color:#94a3b8;font-size:12px">2026-06-'+(String(11-r).padStart(2,'0'))+'</td>'
      +'<td><button class="action-btn" onclick="showForm(\''+name+'\',\''+label+'\')">View</button> <button class="action-btn">Edit</button> <button class="action-btn" style="color:#ef4444" onclick="if(confirm(\'Delete this record?\'))this.closest(\'tr\').remove()">Del</button></td>'
    +'</tr>';
  }
  document.getElementById('dash-content').innerHTML=
    '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px">'
    +'<div><h2 style="font-size:20px;font-weight:800;letter-spacing:-.3px">'+label+'</h2><p style="font-size:13px;color:var(--muted);margin-top:3px">Manage all '+label.toLowerCase()+' records</p></div>'
    +'<div style="display:flex;gap:10px"><button class="btn-sm btn-sm-outline" style="border:1px solid var(--border)">📥 Import</button><button class="btn-sm btn-sm-outline" style="border:1px solid var(--border)">📤 Export</button><button class="btn-sm btn-sm-primary" onclick="showForm(\''+name+'\',\''+label+'\')">+ Add New '+label+'</button></div>'
    +'</div>'
    +'<div class="list-toolbar">'
    +'<div class="search-box"><span style="color:#94a3b8">🔍</span><input placeholder="Search '+label.toLowerCase()+'..."></div>'
    +'<button class="filter-btn">⚡ Filter</button>'
    +'<button class="filter-btn">📅 Date Range</button>'
    +'<button class="filter-btn">↕ Sort</button>'
    +'</div>'
    +'<div class="table-card">'
    +'<table class="data-table"><thead><tr><th style="width:36px"><input type="checkbox" style="accent-color:var(--brand)"></th><th>Record</th><th>Name</th><th>Status</th><th>Value</th><th>Date</th><th>Actions</th></tr></thead><tbody>'+rows+'</tbody></table>'
    +'<div class="tbl-pagination"><span class="pg-info">Showing 1–10 of 247 records</span><div class="pg-btns"><button class="pg-btn">‹</button><button class="pg-btn cur">1</button><button class="pg-btn">2</button><button class="pg-btn">3</button><button class="pg-btn">⋯</button><button class="pg-btn">25</button><button class="pg-btn">›</button></div></div>'
    +'</div>';
}

/* ── Add / View form ── */
function showForm(name,label){
  var overlay=document.createElement('div');
  overlay.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:1000;display:flex;align-items:center;justify-content:center';
  overlay.innerHTML='<div style="background:#fff;border-radius:20px;width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25)">'
    +'<div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">'
    +'<div><div style="font-size:17px;font-weight:800;color:#0f172a">Add New '+label+'</div><div style="font-size:12px;color:#64748b;margin-top:2px">Fill in the details below</div></div>'
    +'<button onclick="this.closest(\'div[style*=fixed]\').remove()" style="background:#f1f5f9;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:16px;color:#64748b">✕</button>'
    +'</div>'
    +'<div style="padding:24px 28px">'
    +'<div style="margin-bottom:16px"><label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Name</label><input style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;outline:none;color:#0f172a" placeholder="Enter name"></div>'
    +'<div style="margin-bottom:16px"><label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Status</label><select style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;outline:none;color:#0f172a;background:#fff"><option>Active</option><option>Pending</option><option>Inactive</option></select></div>'
    +'<div style="margin-bottom:16px"><label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Email</label><input type="email" style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;outline:none;color:#0f172a" placeholder="email@example.com"></div>'
    +'<div style="margin-bottom:24px"><label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Notes</label><textarea style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;outline:none;color:#0f172a;resize:vertical;min-height:80px" placeholder="Optional notes..."></textarea></div>'
    +'<div style="display:flex;gap:10px"><button onclick="this.closest(\'div[style*=fixed]\').remove()" style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;color:#64748b">Cancel</button><button style="flex:1;background:var(--brand);border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;color:#fff" onclick="alert(\'Record saved!\');this.closest(\'div[style*=fixed]\').remove()">Save Record</button></div>'
    +'</div></div>';
  document.body.appendChild(overlay);
  overlay.addEventListener('click',function(e){if(e.target===overlay)overlay.remove();});
}

/* Sidebar search filter */
document.getElementById('sb-search-input').addEventListener('input',function(){
  var q=this.value.toLowerCase();
  document.querySelectorAll('.nav-item').forEach(function(el){
    if(!el.id||el.id==='nav-dashboard'||el.id==='nav-settings')return;
    el.style.display=el.textContent.toLowerCase().includes(q)?'':'none';
  });
});

/* ── Settings tab switcher (global — called from injected HTML) ── */
function switchTab(btn,id){
  document.querySelectorAll('.stab').forEach(function(b){
    b.style.background='transparent';b.style.color='#64748b';
  });
  btn.style.background='var(--brand)';btn.style.color='#fff';
  ['t-profile','t-brand','t-email','t-system'].forEach(function(t){
    var el=document.getElementById(t);
    if(el)el.style.display=(t===id)?'block':'none';
  });
}

/* ── Settings screen ── */
function showSettings(el){
  document.querySelectorAll('.nav-item').forEach(function(n){n.classList.remove('active');});
  if(el)el.classList.add('active');
  document.getElementById('breadcrumb-cur').textContent='Settings';
  document.getElementById('dash-content').innerHTML=SETTINGS_HTML;
}

/* ── Full Screen ── */
function toggleFS(){
  var btn=document.getElementById('fs-btn');
  if(!document.fullscreenElement){
    document.documentElement.requestFullscreen().catch(function(){});
    if(btn)btn.innerHTML='<span>⊠</span> Exit Full Screen';
  }else{
    document.exitFullscreen();
    if(btn)btn.innerHTML='<span>⛶</span> Full Screen';
  }
}
document.addEventListener('fullscreenchange',function(){
  var btn=document.getElementById('fs-btn');
  if(btn)btn.innerHTML=document.fullscreenElement?'<span>⊠</span> Exit Full Screen':'<span>⛶</span> Full Screen';
});
</script>

<button id="fs-btn" onclick="toggleFS()" title="Toggle full screen" style="position:fixed;bottom:20px;right:20px;z-index:9999;background:rgba(15,23,42,.82);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.14);color:#e2e8f0;padding:8px 18px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;letter-spacing:.02em;transition:background .15s" onmouseover="this.style.background='rgba(99,102,241,.85)'" onmouseout="this.style.background='rgba(15,23,42,.82)'"><span>⛶</span> Full Screen</button>
</body>
</html>
HTML;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Realistic dummy data — deterministic counts per entity type + domain
    // ─────────────────────────────────────────────────────────────────────────

    private function realisticCount(string $entityName, string $domain): array
    {
        $n = strtolower($entityName);

        $ranges = [
            'patient'      => [1200, 3800, 'admitted today'],
            'doctor'       => [48,   180,  'on duty'],
            'nurse'        => [120,  420,  'on shift'],
            'appointment'  => [280,  840,  'today'],
            'ward'         => [12,   32,   'in use'],
            'bed'          => [80,   280,  'occupied'],
            'medicine'     => [820,  3200, 'in stock'],
            'prescription' => [320,  980,  'issued today'],
            'surgery'      => [4,    18,   'scheduled today'],
            'lab'          => [28,   120,  'pending'],
            'diagnosis'    => [45,   280,  'this week'],
            'product'      => [1200, 8400, 'active'],
            'order'        => [340,  2400, 'today'],
            'customer'     => [2800, 18000,'new this week'],
            'category'     => [12,   64,   'active'],
            'inventory'    => [840,  4200, 'SKUs'],
            'supplier'     => [18,   120,  'active'],
            'vendor'       => [18,   120,  'active'],
            'student'      => [1200, 4800, 'enrolled'],
            'teacher'      => [42,   240,  'active'],
            'course'       => [38,   180,  'offered'],
            'class'        => [32,   140,  'in session'],
            'grade'        => [840,  3200, 'submitted'],
            'exam'         => [18,   84,   'scheduled'],
            'table'        => [18,   80,   'occupied'],
            'reservation'  => [38,   280,  'tonight'],
            'menu'         => [28,   120,  'items'],
            'room'         => [24,   280,  'occupied'],
            'booking'      => [48,   420,  'this week'],
            'guest'        => [120,  840,  'checked in'],
            'account'      => [1200, 12000,'active'],
            'transaction'  => [2400, 24000,'today'],
            'loan'         => [240,  2800, 'active'],
            'invoice'      => [480,  4800, 'issued'],
            'payment'      => [840,  8400, 'processed'],
            'payroll'      => [120,  840,  'processed'],
            'employee'     => [120,  1200, 'active'],
            'staff'        => [80,   800,  'active'],
            'leave'        => [18,   84,   'requests'],
            'recruitment'  => [12,   64,   'open positions'],
            'lead'         => [240,  1800, 'in pipeline'],
            'deal'         => [48,   480,  'active'],
            'campaign'     => [8,    48,   'running'],
            'shipment'     => [48,   840,  'in transit'],
            'vehicle'      => [12,   84,   'available'],
            'driver'       => [18,   120,  'on route'],
            'ticket'       => [48,   840,  'open'],
            'task'         => [84,   840,  'pending'],
            'project'      => [12,   84,   'active'],
            'department'   => [6,    24,   'teams'],
            'role'         => [4,    18,   'configured'],
            'user'         => [24,   480,  'active'],
            'report'       => [24,   280,  'generated'],
            'property'     => [18,   240,  'listed'],
            'tenant'       => [80,   840,  'active'],
        ];

        foreach ($ranges as $keyword => [$min, $max, $label]) {
            if (str_contains($n, $keyword)) {
                $seed  = (crc32($entityName) & 0x7FFFFFFF) % ($max - $min);
                $count = $min + $seed;
                $delta = max(1, (int)($count * 0.014));
                return ['count' => $count, 'trend' => "+{$delta} {$label}"];
            }
        }

        $fallback = match ($domain) {
            'hospital'   => [200,  1200],
            'ecommerce'  => [400,  2400],
            'education'  => [100,  800],
            'restaurant' => [20,   200],
            'hotel'      => [20,   200],
            'finance'    => [400,  3200],
            'hr'         => [50,   400],
            'crm'        => [100,  1200],
            'inventory'  => [200,  2400],
            default      => [80,   800],
        };
        $seed  = (crc32($entityName) & 0x7FFFFFFF) % ($fallback[1] - $fallback[0]);
        $count = $fallback[0] + $seed;
        return ['count' => $count, 'trend' => '+' . max(1, (int)($count * 0.02)) . ' this week'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings HTML builder — for preview.html (PHP-built, JS-injected)
    // ─────────────────────────────────────────────────────────────────────────

    private function buildSettingsHtml(string $appName, string $userName, string $userRole, string $userInitials, string $brand): string
    {
        $h  = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
        $js = fn($s) => addslashes((string)$s);

        $sField = function (string $lbl, string $type, string $val, string $ph = '') use ($h): string {
            return '<div style="margin-bottom:16px">'
                . '<label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">' . $h($lbl) . '</label>'
                . '<input type="' . $type . '" value="' . $h($val) . '"' . ($ph ? ' placeholder="' . $h($ph) . '"' : '')
                . ' style="width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:10px 13px;font-size:14px;color:#0f172a;outline:none" '
                . 'onfocus="this.style.borderColor=\'var(--brand)\'" onblur="this.style.borderColor=\'#e2e8f0\'">'
                . '</div>';
        };

        $sSelect = function (string $lbl, array $opts) use ($h): string {
            $options = implode('', array_map(fn($o) => '<option>' . $h($o) . '</option>', $opts));
            return '<div style="margin-bottom:16px">'
                . '<label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">' . $h($lbl) . '</label>'
                . '<select style="width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:10px 13px;font-size:14px;color:#0f172a;outline:none;background:#fff">' . $options . '</select>'
                . '</div>';
        };

        $card = fn(string $title, string $body): string =>
            '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;margin-bottom:20px">'
            . '<h3 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:20px">' . $h($title) . '</h3>'
            . $body . '</div>';

        $saveBtn = fn(string $lbl): string =>
            '<button onclick="alert(\'' . $js($lbl) . ' — saved! ✅\')" '
            . 'style="background:var(--brand);border:none;padding:11px 28px;border-radius:9px;color:#fff;font-size:14px;font-weight:700;cursor:pointer">'
            . $h($lbl) . '</button>';

        $tabs =
            '<div style="display:flex;gap:4px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:4px;width:fit-content;margin-bottom:24px">'
            . '<button class="stab" onclick="switchTab(this,\'t-profile\')" style="padding:8px 18px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;background:var(--brand);color:#fff">👤 Profile</button>'
            . '<button class="stab" onclick="switchTab(this,\'t-brand\')" style="padding:8px 18px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;background:transparent;color:#64748b">🎨 Branding</button>'
            . '<button class="stab" onclick="switchTab(this,\'t-email\')" style="padding:8px 18px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;background:transparent;color:#64748b">📧 Email / SMTP</button>'
            . '<button class="stab" onclick="switchTab(this,\'t-system\')" style="padding:8px 18px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;background:transparent;color:#64748b">⚙️ System</button>'
            . '</div>';

        $profileTab =
            '<div id="t-profile">'
            . '<div style="display:grid;grid-template-columns:240px 1fr;gap:20px">'
            . $card('Avatar & Identity',
                '<div style="display:flex;flex-direction:column;align-items:center;gap:16px;padding:8px 0">'
                . '<div style="width:88px;height:88px;background:linear-gradient(135deg,' . $h($brand) . ',#8b5cf6);border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:900;color:#fff;box-shadow:0 4px 20px rgba(0,0,0,.12)">' . $h($userInitials) . '</div>'
                . '<div style="text-align:center"><div style="font-size:15px;font-weight:700;color:#0f172a">' . $h($userName) . '</div>'
                . '<div style="font-size:12px;color:#64748b">' . $h($userRole) . '</div></div>'
                . '<button onclick="alert(\'Avatar upload available in production\')" style="width:100%;background:#f8fafc;border:1px solid #e2e8f0;padding:9px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;color:#64748b">📷 Upload Avatar</button>'
                . '</div>'
            )
            . $card('Personal Information',
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">'
                . $sField('Full Name', 'text', $userName)
                . $sField('Email Address', 'email', 'admin@example.com')
                . '</div>'
                . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">'
                . $sField('Job Title', 'text', $userRole)
                . $sField('Phone', 'tel', '', '+1 (555) 000-0000')
                . '</div>'
                . $saveBtn('Save Profile')
            )
            . '</div>'
            . $card('Change Password',
                '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">'
                . $sField('Current Password', 'password', '', '••••••••')
                . $sField('New Password', 'password', '', 'Min. 8 characters')
                . $sField('Confirm Password', 'password', '', 'Repeat new password')
                . '</div>'
                . $saveBtn('Update Password')
            )
            . '</div>';

        $brandingTab =
            '<div id="t-brand" style="display:none">'
            . $card('Application Identity',
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">'
                . $sField('Application Name', 'text', $appName)
                . $sField('Tagline', 'text', 'Enterprise Management Platform')
                . '</div>'
                . '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">'
                . '<div style="margin-bottom:16px"><label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Brand Color</label>'
                . '<div style="display:flex;gap:8px;align-items:center"><input type="color" value="' . $h($brand) . '" style="width:48px;height:40px;border:1px solid #e2e8f0;border-radius:9px;padding:2px;cursor:pointer">'
                . '<input type="text" value="' . $h($brand) . '" style="flex:1;border:1px solid #e2e8f0;border-radius:9px;padding:10px 13px;font-size:14px;color:#0f172a;outline:none"></div></div>'
                . $sField('Support Email', 'email', 'support@example.com')
                . $sField('Footer Text', 'text', '© 2026 ' . $appName . '. All rights reserved.')
                . '</div>'
                . $saveBtn('Save Branding')
            )
            . $card('Logo & Favicon',
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">'
                . '<div style="border:2px dashed #e2e8f0;border-radius:12px;padding:28px;text-align:center;cursor:pointer" onmouseover="this.style.borderColor=\'var(--brand)\'" onmouseout="this.style.borderColor=\'#e2e8f0\'">'
                . '<div style="font-size:36px;margin-bottom:8px">🖼️</div><div style="font-weight:700;color:#0f172a;margin-bottom:4px">App Logo</div>'
                . '<div style="font-size:12px;color:#64748b">PNG, SVG · max 2MB · 200×60px</div></div>'
                . '<div style="border:2px dashed #e2e8f0;border-radius:12px;padding:28px;text-align:center;cursor:pointer" onmouseover="this.style.borderColor=\'var(--brand)\'" onmouseout="this.style.borderColor=\'#e2e8f0\'">'
                . '<div style="font-size:36px;margin-bottom:8px">🔖</div><div style="font-weight:700;color:#0f172a;margin-bottom:4px">Favicon</div>'
                . '<div style="font-size:12px;color:#64748b">ICO, PNG · max 512KB · 32×32px</div></div>'
                . '</div>'
                . '<div style="margin-top:16px">' . $saveBtn('Upload Assets') . '</div>'
            )
            . '</div>';

        $emailTab =
            '<div id="t-email" style="display:none">'
            . $card('SMTP Configuration',
                '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">'
                . $sField('SMTP Host', 'text', 'smtp.mailtrap.io')
                . $sField('SMTP Port', 'number', '587')
                . $sSelect('Encryption', ['TLS', 'SSL', 'None'])
                . '</div>'
                . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">'
                . $sField('Username', 'text', 'your-smtp-username')
                . $sField('Password', 'password', '', '••••••••••••')
                . '</div>'
                . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">'
                . $sField('From Name', 'text', $appName)
                . $sField('From Email', 'email', 'noreply@example.com')
                . '</div>'
                . '<div style="display:flex;gap:10px">'
                . $saveBtn('Save Configuration')
                . '<button onclick="alert(\'Test email sent to admin@example.com ✅\')" style="background:#fff;border:1px solid #e2e8f0;padding:11px 24px;border-radius:9px;color:#64748b;font-size:14px;font-weight:700;cursor:pointer">📨 Send Test Email</button>'
                . '</div>'
            )
            . '</div>';

        $systemTab =
            '<div id="t-system" style="display:none">'
            . $card('Regional Settings',
                '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">'
                . $sSelect('Timezone', ['UTC', 'America/New_York', 'America/Los_Angeles', 'Europe/London', 'Asia/Dubai', 'Asia/Dhaka', 'Asia/Kolkata', 'Asia/Singapore', 'Asia/Tokyo'])
                . $sSelect('Language', ['English', 'বাংলা (Bangla)', 'हिंदी (Hindi)', 'العربية (Arabic)', 'Français', 'Español', 'Türkçe'])
                . $sSelect('Date Format', ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD', 'D MMMM YYYY'])
                . '</div>'
                . $sSelect('Currency', ['USD ($)', 'EUR (€)', 'GBP (£)', 'BDT (৳)', 'INR (₹)', 'AED (د.إ)', 'SAR (﷼)', 'JPY (¥)'])
                . $saveBtn('Save Regional')
            )
            . $card('System Maintenance',
                '<div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px">'
                . '<div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#f8fafc;border-radius:10px">'
                . '<div><div style="font-weight:600;color:#0f172a;font-size:14px">Maintenance Mode</div><div style="font-size:12px;color:#64748b;margin-top:2px">Take the application offline for maintenance</div></div>'
                . '<div onclick="this.style.background=this.getAttribute(\'data-on\')===\'1\'?(this.setAttribute(\'data-on\',\'0\'),\'#e2e8f0\'):(this.setAttribute(\'data-on\',\'1\'),\'var(--brand)\')" data-on="0" style="width:44px;height:24px;background:#e2e8f0;border-radius:34px;cursor:pointer;transition:background .25s"></div>'
                . '</div>'
                . '<div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#f8fafc;border-radius:10px">'
                . '<div><div style="font-weight:600;color:#0f172a;font-size:14px">Debug Mode</div><div style="font-size:12px;color:#64748b;margin-top:2px">Enable detailed error logging and stack traces</div></div>'
                . '<div onclick="this.style.background=this.getAttribute(\'data-on\')===\'1\'?(this.setAttribute(\'data-on\',\'0\'),\'#e2e8f0\'):(this.setAttribute(\'data-on\',\'1\'),\'var(--brand)\')" data-on="0" style="width:44px;height:24px;background:#e2e8f0;border-radius:34px;cursor:pointer;transition:background .25s"></div>'
                . '</div>'
                . '</div>'
                . '<div style="display:flex;flex-wrap:wrap;gap:10px">'
                . '<button onclick="alert(\'Cache cleared! ✅\')" style="background:#fff;border:1px solid #e2e8f0;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;color:#64748b">🗑️ Clear Cache</button>'
                . '<button onclick="alert(\'All systems operational ✅\')" style="background:#fff;border:1px solid #e2e8f0;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;color:#64748b">💚 System Health</button>'
                . '<button onclick="alert(\'Backup started! ✅\')" style="background:#fff;border:1px solid #e2e8f0;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;color:#64748b">💾 Backup Now</button>'
                . '<button onclick="alert(\'Logs exported! ✅\')" style="background:#fff;border:1px solid #e2e8f0;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;color:#64748b">📋 Export Logs</button>'
                . '</div>'
            )
            . '</div>';

        return '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">'
            . '<div><h2 style="font-size:20px;font-weight:800;letter-spacing:-.3px;color:#0f172a">Settings</h2>'
            . '<p style="font-size:13px;color:#64748b;margin-top:3px">Configure your application preferences</p></div>'
            . '</div>'
            . $tabs . $profileTab . $brandingTab . $emailTab . $systemTab;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Landing page — editable Blade with all content in a @php block at the top
    // ─────────────────────────────────────────────────────────────────────────

    private function shellLandingController(string $appName): string
    {
        $class = 'LandingController';
        return <<<PHP
<?php

namespace App\Http\Controllers;

class {$class} extends Controller
{
    public function index()
    {
        return view('landing');
    }
}
PHP;
    }

    private function shellLandingView(string $appName, array $entities): string
    {
        $profile   = $this->domainProfile($appName, $entities);
        $brand     = $profile['brand'];
        $brandDk   = $profile['brandDk'];
        $gradient  = $profile['gradient'];
        $heroTag   = $profile['heroTag'];
        $heroSub   = $profile['heroSub'];
        $userRole  = $profile['userRole'];

        // Build editable metrics list as PHP array literal
        $metricsPhp = '';
        foreach ($profile['metrics'] as $m) {
            $n = addslashes($m['num']);
            $l = addslashes($m['lbl']);
            $metricsPhp .= "    ['num' => '{$n}', 'lbl' => '{$l}'],\n";
        }

        // Build editable features list
        $featPhp = '';
        $featSuffix = $profile['featSuffix'];
        foreach ($entities as $i => $e) {
            $icon  = $this->entityIcon($e['name']);
            $title = ucwords(str_replace('_', ' ', $e['name'])) . ' Management';
            $desc  = "Manage all {$e['name']} records {$featSuffix}";
            $featPhp .= "    ['icon' => '{$icon}', 'title' => '" . addslashes($title) . "', 'desc' => '" . addslashes($desc) . "'],\n";
        }
        if (!$featPhp) {
            $featPhp = "    ['icon' => '⚡', 'title' => 'Fast & Reliable', 'desc' => 'Built for performance and scale {$featSuffix}'],\n";
        }

        $appInitial = strtoupper(substr($appName, 0, 1));
        $year       = date('Y');

        return <<<BLADE
{{--
  Landing Page — {$appName}
  ════════════════════════════════════════════════════════════════
  All editable content is in the @php block below.
  Change text, colors, nav links, metrics, features — no AI needed.
  ════════════════════════════════════════════════════════════════
--}}
@php
// ┌─────────────────────────────────────────────────────────────┐
// │  EDITABLE CONTENT — change anything here without touching   │
// │  the HTML below. No AI, no rebuild, just edit and refresh.  │
// └─────────────────────────────────────────────────────────────┘

\$page = [

    // ── Brand ────────────────────────────────────────────────────
    'appName'    => '{$appName}',
    'appInitial' => '{$appInitial}',
    'tagline'    => 'Smarter. Faster. Built for your team.',

    // ── Colors (CSS hex) ─────────────────────────────────────────
    'brand'      => '{$brand}',
    'brandDk'    => '{$brandDk}',
    'gradient'   => 'linear-gradient(160deg,{$gradient})',

    // ── Hero section ─────────────────────────────────────────────
    'heroEyebrow' => '{$heroTag}',
    'heroTitle'   => '{$appName}',
    'heroSub'     => '{$heroSub}',
    'heroCta'     => 'Get Started Free',
    'heroCtaHref' => '/register',
    'heroCtaAlt'  => 'Live Demo',
    'heroCtaAltHref' => '/demo',

    // ── Metrics strip ─────────────────────────────────────────────
    // Each item: ['num' => '...', 'lbl' => '...']
    'metrics' => [
{$metricsPhp}    ],

    // ── Nav links ─────────────────────────────────────────────────
    // Each item: ['label' => '...', 'href' => '...']
    'navLinks' => [
        ['label' => 'Features',  'href' => '#features'],
        ['label' => 'Pricing',   'href' => '#pricing'],
        ['label' => 'About',     'href' => '#about'],
        ['label' => 'Contact',   'href' => '#contact'],
    ],
    'navCta'     => 'Sign In',
    'navCtaHref' => '/login',

    // ── Features section ─────────────────────────────────────────
    'featuresTitle' => 'Everything you need, nothing you don\'t',
    'featuresSub'   => 'A complete platform purpose-built for {$userRole}s and their teams.',
    // Each item: ['icon' => '...', 'title' => '...', 'desc' => '...']
    'features' => [
{$featPhp}    ],

    // ── CTA banner ────────────────────────────────────────────────
    'ctaTitle'    => 'Ready to transform your operations?',
    'ctaSub'      => 'Join thousands of teams already using {$appName}.',
    'ctaBtn'      => 'Start for Free',
    'ctaBtnHref'  => '/register',

    // ── Footer ────────────────────────────────────────────────────
    'footerCopy'  => '© {$year} {$appName}. All rights reserved.',
    'footerLinks' => [
        ['label' => 'Privacy Policy', 'href' => '/privacy'],
        ['label' => 'Terms of Service', 'href' => '/terms'],
        ['label' => 'Support', 'href' => '/support'],
    ],
];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ \$page['appName'] }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  :root { --brand: {{ \$page['brand'] }}; --brand-dk: {{ \$page['brandDk'] }}; }
  body  { font-family:'Inter',system-ui,sans-serif; background:#0a0a0f; color:#e2e8f0; }
  .btn-primary {
    background: linear-gradient(135deg, var(--brand), var(--brand-dk));
    color:#fff; padding:14px 36px; border-radius:12px; font-weight:700;
    font-size:15px; text-decoration:none; display:inline-block; transition:.2s;
    box-shadow:0 4px 20px rgba(0,0,0,.4);
  }
  .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,0,0,.5); }
  .btn-ghost {
    border:1px solid rgba(255,255,255,.18); color:#e2e8f0; padding:14px 36px;
    border-radius:12px; font-weight:600; font-size:15px; text-decoration:none;
    display:inline-block; transition:.2s; backdrop-filter:blur(6px);
  }
  .btn-ghost:hover { background:rgba(255,255,255,.07); }
  .card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:28px; transition:.2s; }
  .card:hover { background:rgba(255,255,255,.07); border-color:var(--brand); transform:translateY(-3px); }
  .metric-val { font-size:2.2rem; font-weight:800; background:linear-gradient(135deg,var(--brand),#fff); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
  nav a { color:#94a3b8; text-decoration:none; font-size:14px; font-weight:500; transition:.15s; }
  nav a:hover { color:#e2e8f0; }
  .eyebrow { font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--brand); }
</style>
</head>
<body>

{{-- ── NAV ───────────────────────────────────────────────────────── --}}
<nav style="position:sticky;top:0;z-index:100;background:rgba(10,10,15,.88);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,.07);padding:0 5%;">
  <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;height:64px;gap:32px;">
    <div style="display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px;color:#f1f5f9;">
      <div style="width:32px;height:32px;background:var(--brand);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;">{{ \$page['appInitial'] }}</div>
      {{ \$page['appName'] }}
    </div>
    <div style="display:flex;gap:28px;margin-left:auto;align-items:center;">
      @foreach(\$page['navLinks'] as \$link)
        <a href="{{ \$link['href'] }}">{{ \$link['label'] }}</a>
      @endforeach
      <a href="{{ \$page['navCtaHref'] }}" class="btn-primary" style="padding:9px 22px;font-size:13px;">{{ \$page['navCta'] }}</a>
    </div>
  </div>
</nav>

{{-- ── HERO ─────────────────────────────────────────────────────── --}}
<section style="background:{{ \$page['gradient'] }};padding:100px 5% 80px;text-align:center;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(var(--brand),0.12),transparent);pointer-events:none;"></div>
  <div style="max-width:820px;margin:0 auto;position:relative;">
    <div class="eyebrow" style="margin-bottom:20px;">{{ \$page['heroEyebrow'] }}</div>
    <h1 style="font-size:clamp(2.4rem,6vw,4rem);font-weight:900;line-height:1.1;margin:0 0 20px;color:#f8fafc;">
      {{ \$page['heroTitle'] }}
    </h1>
    <p style="font-size:clamp(1rem,2.5vw,1.2rem);color:#94a3b8;line-height:1.7;margin:0 0 40px;">
      {{ \$page['heroSub'] }}
    </p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="{{ \$page['heroCta'] !== '' ? \$page['heroCtaHref'] : '#' }}" class="btn-primary">{{ \$page['heroCta'] }}</a>
      <a href="{{ \$page['heroCtaAltHref'] }}" class="btn-ghost">{{ \$page['heroCtaAlt'] }}</a>
    </div>
  </div>
</section>

{{-- ── METRICS STRIP ────────────────────────────────────────────── --}}
<section style="border-top:1px solid rgba(255,255,255,.07);border-bottom:1px solid rgba(255,255,255,.07);padding:40px 5%;background:rgba(255,255,255,.02);">
  <div style="max-width:1000px;margin:0 auto;display:flex;justify-content:center;gap:60px;flex-wrap:wrap;text-align:center;">
    @foreach(\$page['metrics'] as \$m)
      <div>
        <div class="metric-val">{{ \$m['num'] }}</div>
        <div style="font-size:13px;color:#64748b;margin-top:4px;">{{ \$m['lbl'] }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ── FEATURES ─────────────────────────────────────────────────── --}}
<section id="features" style="padding:90px 5%;">
  <div style="max-width:1200px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:60px;">
      <div class="eyebrow" style="margin-bottom:12px;">Features</div>
      <h2 style="font-size:clamp(1.8rem,4vw,2.6rem);font-weight:800;color:#f1f5f9;margin:0 0 14px;">{{ \$page['featuresTitle'] }}</h2>
      <p style="color:#64748b;font-size:16px;max-width:560px;margin:0 auto;">{{ \$page['featuresSub'] }}</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
      @foreach(\$page['features'] as \$feat)
        <div class="card">
          <div style="font-size:2rem;margin-bottom:12px;">{{ \$feat['icon'] }}</div>
          <h3 style="font-size:15px;font-weight:700;color:#f1f5f9;margin:0 0 8px;">{{ \$feat['title'] }}</h3>
          <p style="font-size:13.5px;color:#64748b;line-height:1.6;margin:0;">{{ \$feat['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── CTA BANNER ───────────────────────────────────────────────── --}}
<section style="padding:80px 5%;background:linear-gradient(135deg,rgba(var(--brand),.12),rgba(var(--brand-dk),.08));border-top:1px solid rgba(255,255,255,.07);">
  <div style="max-width:700px;margin:0 auto;text-align:center;">
    <h2 style="font-size:clamp(1.6rem,4vw,2.4rem);font-weight:800;color:#f1f5f9;margin:0 0 14px;">{{ \$page['ctaTitle'] }}</h2>
    <p style="font-size:16px;color:#94a3b8;margin:0 0 36px;">{{ \$page['ctaSub'] }}</p>
    <a href="{{ \$page['ctaBtnHref'] }}" class="btn-primary">{{ \$page['ctaBtn'] }}</a>
  </div>
</section>

{{-- ── FOOTER ───────────────────────────────────────────────────── --}}
<footer style="border-top:1px solid rgba(255,255,255,.07);padding:32px 5%;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
  <span style="font-size:13px;color:#475569;">{{ \$page['footerCopy'] }}</span>
  <div style="display:flex;gap:20px;">
    @foreach(\$page['footerLinks'] as \$fl)
      <a href="{{ \$fl['href'] }}" style="font-size:13px;color:#475569;text-decoration:none;">{{ \$fl['label'] }}</a>
    @endforeach
  </div>
</footer>

</body>
</html>
BLADE;
    }

    // Settings — real Laravel controller + Blade view
    // ─────────────────────────────────────────────────────────────────────────

    private function shellSettingsController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'app_name'    => 'required|string|max:255',
            'tagline'     => 'nullable|string|max:255',
            'brand_color' => 'nullable|string|max:20',
        ]);
        return back()->with('success', 'Branding settings saved.');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'from_email'=> 'required|email',
        ]);
        return back()->with('success', 'Email settings saved.');
    }

    public function updateSystem(Request $request)
    {
        $request->validate([
            'timezone'    => 'nullable|string|max:100',
            'date_format' => 'nullable|string|max:30',
        ]);
        return back()->with('success', 'System settings saved.');
    }
}
PHP;
    }

    private function shellSettingsView(string $appName): string
    {
        return <<<BLADE
@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Configure your application preferences</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium bg-green-50 text-green-700 border border-green-200">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Tab nav --}}
    <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 w-fit mb-6">
        <a href="#profile" onclick="showTab('profile')" id="tab-profile"
           class="px-5 py-2 text-sm font-semibold rounded-lg transition-colors bg-indigo-600 text-white">
            👤 Profile
        </a>
        <a href="#branding" onclick="showTab('branding')" id="tab-branding"
           class="px-5 py-2 text-sm font-semibold rounded-lg transition-colors text-gray-500 hover:text-gray-800">
            🎨 Branding
        </a>
        <a href="#email" onclick="showTab('email')" id="tab-email"
           class="px-5 py-2 text-sm font-semibold rounded-lg transition-colors text-gray-500 hover:text-gray-800">
            📧 Email / SMTP
        </a>
        <a href="#system" onclick="showTab('system')" id="tab-system"
           class="px-5 py-2 text-sm font-semibold rounded-lg transition-colors text-gray-500 hover:text-gray-800">
            ⚙️ System
        </a>
    </div>

    {{-- Profile --}}
    <div id="pane-profile">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col items-center gap-4">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl font-black text-white">AD</div>
                <div class="text-center">
                    <div class="font-bold text-gray-900">Admin User</div>
                    <div class="text-xs text-gray-500">Super Admin</div>
                </div>
                <button class="w-full py-2 text-sm font-semibold bg-gray-50 border border-gray-200 rounded-xl text-gray-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors">📷 Upload Avatar</button>
            </div>
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-base font-bold text-gray-900 mb-5">Personal Information</h2>
                <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Full Name</label>
                            <input type="text" name="name" value="Admin User" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email Address</label>
                            <input type="email" name="email" value="admin@example.com" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Save Profile</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Branding --}}
    <div id="pane-branding" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-900 mb-5">🎨 Application Branding</h2>
            <form method="POST" action="{{ route('settings.branding') }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Application Name</label>
                        <input type="text" name="app_name" value="{$appName}" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Tagline</label>
                        <input type="text" name="tagline" value="Enterprise Management Platform" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Brand Color</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="brand_color" value="#6366f1" class="w-12 h-10 border border-gray-300 rounded-xl p-1 cursor-pointer">
                            <input type="text" value="#6366f1" class="flex-1 px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">App Logo</label>
                        <label class="flex flex-col items-center justify-center h-10 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 transition-colors">
                            <span class="text-xs text-gray-500">📷 Upload PNG/SVG</span>
                            <input type="file" name="logo" accept=".png,.svg,.jpg" class="hidden">
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Favicon</label>
                        <label class="flex flex-col items-center justify-center h-10 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 transition-colors">
                            <span class="text-xs text-gray-500">🔖 Upload ICO/PNG</span>
                            <input type="file" name="favicon" accept=".ico,.png" class="hidden">
                        </label>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Save Branding</button>
            </form>
        </div>
    </div>

    {{-- Email / SMTP --}}
    <div id="pane-email" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-900 mb-5">📧 Email / SMTP Configuration</h2>
            <form method="POST" action="{{ route('settings.email') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">SMTP Host</label>
                        <input type="text" name="smtp_host" value="smtp.mailtrap.io" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">SMTP Port</label>
                        <input type="number" name="smtp_port" value="587" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Encryption</label>
                        <select name="encryption" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option selected>TLS</option><option>SSL</option><option>None</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">SMTP Username</label>
                        <input type="text" name="smtp_user" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">From Name</label>
                        <input type="text" name="from_name" value="{$appName}" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">From Email</label>
                        <input type="email" name="from_email" value="noreply@example.com" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>

    {{-- System --}}
    <div id="pane-system" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-900 mb-5">⚙️ System Configuration</h2>
            <form method="POST" action="{{ route('settings.system') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Timezone</label>
                        <select name="timezone" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option>UTC</option><option>America/New_York</option><option>Europe/London</option>
                            <option>Asia/Dubai</option><option selected>Asia/Dhaka</option><option>Asia/Kolkata</option><option>Asia/Singapore</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Language</label>
                        <select name="language" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option selected>English</option><option>বাংলা (Bangla)</option><option>हिंदी (Hindi)</option><option>العربية</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date Format</label>
                        <select name="date_format" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option>DD/MM/YYYY</option><option>MM/DD/YYYY</option><option>YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Save Settings</button>
            </form>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="text-base font-bold text-gray-900 mb-5">System Maintenance</h2>
            <div class="flex flex-wrap gap-3">
                <button onclick="if(confirm('Clear all application cache?')) location.reload()" class="px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:border-indigo-400 hover:text-indigo-600 transition-colors">🗑️ Clear Cache</button>
                <button onclick="alert('All systems operational ✅')" class="px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:border-green-400 hover:text-green-600 transition-colors">💚 System Health</button>
                <button onclick="alert('Backup started!')" class="px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:border-blue-400 hover:text-blue-600 transition-colors">💾 Backup Now</button>
            </div>
        </div>
    </div>

</div>

<script>
function showTab(name){
  ['profile','branding','email','system'].forEach(function(t){
    document.getElementById('pane-'+t).style.display=(t===name)?'block':'none';
    var tab=document.getElementById('tab-'+t);
    if(tab){
      if(t===name){tab.className=tab.className.replace('text-gray-500','');tab.style.cssText='background:#4f46e5;color:#fff;padding:8px 20px;font-size:14px;font-weight:600;border-radius:8px';}
      else{tab.style.cssText='padding:8px 20px;font-size:14px;font-weight:600;border-radius:8px;color:#6b7280;background:transparent';}
    }
  });
  return false;
}
</script>
@endsection
BLADE;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Domain intelligence — detects business domain and returns design tokens
    // ─────────────────────────────────────────────────────────────────────────

    private function domainProfile(string $appName, array $entities): array
    {
        $t = strtolower($appName . ' ' . implode(' ', array_column($entities, 'name')));

        // Variant 0-2: each app name deterministically picks a different color scheme
        // within its domain — same app name = same design, different names = different designs
        $v = (crc32($appName) & 0x7FFFFFFF) % 3;

        if (preg_match('/hospital|clinic|medical|health|patient|doctor|nurse|pharmacy|ward|surgery|diagnostic|icu|opd/', $t)) {
            $colors = [
                ['#0ea5e9','#0284c7','#e0f2fe','#020d18 0%,#0b1a2e 40%,#082330 100%'],
                ['#06b6d4','#0891b2','#cffafe','#020e12 0%,#051a20 40%,#062830 100%'],
                ['#10b981','#059669','#d1fae5','#020f08 0%,#051a10 40%,#071d12 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'hospital',   'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '🏥 Healthcare Platform · HIPAA Ready · Real-time',
                'heroSub'   => 'Streamline patient care, clinical workflows, staff management and medical records — all in one secure, compliant platform built for modern hospitals.',
                'metrics'   => [['num'=>'50K+','lbl'=>'Patients Managed'],['num'=>'99.9%','lbl'=>'System Uptime'],['num'=>'200+','lbl'=>'Clinical Staff']],
                'userRole'  => 'Chief Medical Officer', 'userName' => 'Dr. Admin',
                'sbSection' => 'Clinical Modules',
                'kpiPfx'    => ['Active','On Duty','Scheduled','Available','Pending','Completed','Registered','In Queue'],
                'featSuffix'=> 'with complete clinical history, audit trails, and HIPAA-compliant data storage.',
            ];
        }

        if (preg_match('/ecommerce|e-commerce|shop|store|retail|product|order|cart|checkout|marketplace|catalogue/', $t)) {
            $colors = [
                ['#7c3aed','#6d28d9','#ede9fe','#0d0618 0%,#130926 40%,#1a0d35 100%'],
                ['#6366f1','#4f46e5','#eef2ff','#06040f 0%,#0f0a24 40%,#130d2e 100%'],
                ['#ec4899','#db2777','#fce7f3','#140208 0%,#250410 40%,#2d0515 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'ecommerce',  'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '🛍️ eCommerce Platform · Multi-Store · Scalable',
                'heroSub'   => 'Manage your entire store — products, orders, customers, inventory, and analytics — from one powerful platform that scales with your business.',
                'metrics'   => [['num'=>'$2.4M','lbl'=>'Revenue Processed'],['num'=>'15K+','lbl'=>'Orders Fulfilled'],['num'=>'8K+','lbl'=>'Happy Customers']],
                'userRole'  => 'Store Manager', 'userName' => 'Admin User',
                'sbSection' => 'Store Modules',
                'kpiPfx'    => ['Total','New','Pending','Active','Completed','Featured','Published','Fulfilled'],
                'featSuffix'=> 'with advanced filtering, bulk actions, and real-time inventory sync.',
            ];
        }

        if (preg_match('/school|education|university|college|academy|student|teacher|course|class|grade|exam|curriculum|faculty/', $t)) {
            $colors = [
                ['#2563eb','#1d4ed8','#dbeafe','#020714 0%,#080f24 40%,#0a1030 100%'],
                ['#0ea5e9','#0284c7','#e0f2fe','#020a12 0%,#051525 40%,#072030 100%'],
                ['#8b5cf6','#7c3aed','#ede9fe','#0d0618 0%,#12082a 40%,#160a30 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'education',  'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '🎓 Education Platform · LMS Ready · Multi-Campus',
                'heroSub'   => 'Manage students, teachers, courses, exams, and academic records with a modern learning management system built for 21st century education.',
                'metrics'   => [['num'=>'5K+','lbl'=>'Students Enrolled'],['num'=>'98%','lbl'=>'Pass Rate'],['num'=>'150+','lbl'=>'Courses Offered']],
                'userRole'  => 'Principal', 'userName' => 'Admin User',
                'sbSection' => 'Academic Modules',
                'kpiPfx'    => ['Enrolled','Active','Scheduled','Graded','Passed','Published','Registered','Assigned'],
                'featSuffix'=> 'with academic tracking, progress reports, and parent portal integration.',
            ];
        }

        if (preg_match('/restaurant|food|cafe|menu|kitchen|recipe|dish|table|reservation|waiter|chef|dine/', $t)) {
            $colors = [
                ['#ea580c','#c2410c','#fff7ed','#180802 0%,#1f0f05 40%,#241206 100%'],
                ['#f59e0b','#d97706','#fef3c7','#100a02 0%,#1a1205 40%,#1e1608 100%'],
                ['#ef4444','#dc2626','#fee2e2','#180202 0%,#240505 40%,#2c0505 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'restaurant', 'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '🍽️ Restaurant Platform · POS Ready · Multi-Branch',
                'heroSub'   => 'Run your restaurant smarter — manage orders, tables, menu, kitchen workflow, staff and customer experience from a single integrated platform.',
                'metrics'   => [['num'=>'500+','lbl'=>'Orders Daily'],['num'=>'4.9★','lbl'=>'Customer Rating'],['num'=>'12+','lbl'=>'Locations']],
                'userRole'  => 'Restaurant Manager', 'userName' => 'Admin User',
                'sbSection' => 'Restaurant Modules',
                'kpiPfx'    => ["Today's",'Active','Pending','Completed','Available','Featured','Reserved','Open'],
                'featSuffix'=> 'with real-time kitchen display, POS integration, and customer feedback.',
            ];
        }

        if (preg_match('/hotel|resort|property|realestate|real.estate|room|booking|guest|tenant|lease|apartment|hostel|accommodation/', $t)) {
            $colors = [
                ['#d97706','#b45309','#fef3c7','#180e02 0%,#1a1005 40%,#1e1408 100%'],
                ['#f59e0b','#d97706','#fef9c3','#12100 0%,#1e1600 40%,#241c02 100%'],
                ['#0ea5e9','#0284c7','#e0f2fe','#020d18 0%,#0b1a2e 40%,#082330 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'hotel',      'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '🏨 Property Platform · Multi-Property · Revenue Mgmt',
                'heroSub'   => 'Manage reservations, guests, rooms, housekeeping, billing and revenue across all your properties from one elegant platform.',
                'metrics'   => [['num'=>'95%','lbl'=>'Occupancy Rate'],['num'=>'1.2K','lbl'=>'Happy Guests'],['num'=>'48h','lbl'=>'Avg Stay']],
                'userRole'  => 'General Manager', 'userName' => 'Admin User',
                'sbSection' => 'Property Modules',
                'kpiPfx'    => ['Available','Booked','Check-in','Check-out','Occupied','Reserved','Pending','Completed'],
                'featSuffix'=> 'with channel manager integration, revenue optimization, and guest CRM.',
            ];
        }

        if (preg_match('/finance|bank|accounting|loan|ledger|transaction|account|balance|payroll|tax|audit|budget|insurance|capital/', $t)) {
            $colors = [
                ['#1e40af','#1e3a8a','#dbeafe','#02060f 0%,#060d1f 40%,#080e25 100%'],
                ['#0f172a','#020617','#e2e8f0','#000000 0%,#030308 40%,#060612 100%'],
                ['#0ea5e9','#0284c7','#e0f2fe','#020d18 0%,#0b1a2e 40%,#082330 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'finance',    'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '🏦 Finance Platform · SOX Compliant · Real-time',
                'heroSub'   => 'Comprehensive financial management — accounts, transactions, loans, payroll, tax reporting and audit trails built for modern financial institutions.',
                'metrics'   => [['num'=>'$50M+','lbl'=>'Assets Managed'],['num'=>'100%','lbl'=>'Audit Compliant'],['num'=>'5K+','lbl'=>'Client Accounts']],
                'userRole'  => 'Finance Director', 'userName' => 'Admin User',
                'sbSection' => 'Finance Modules',
                'kpiPfx'    => ['Total','Active','Pending','Processed','Outstanding','Cleared','Approved','Reconciled'],
                'featSuffix'=> 'with double-entry accounting, real-time reconciliation, and regulatory reporting.',
            ];
        }

        if (preg_match('/\bhr\b|human.resource|employee|staff|recruitment|payroll|attendance|leave|performance|training|onboard/', $t)) {
            $colors = [
                ['#7c3aed','#6d28d9','#ede9fe','#0d0618 0%,#12082a 40%,#160a30 100%'],
                ['#8b5cf6','#7c3aed','#f3e8ff','#0f0620 0%,#160930 40%,#1a0b38 100%'],
                ['#ec4899','#db2777','#fce7f3','#140208 0%,#250410 40%,#2d0515 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'hr',         'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '👥 HR Platform · HRMS · Payroll Automation',
                'heroSub'   => 'Manage your entire workforce — recruitment, onboarding, attendance, payroll, performance reviews and training from one intelligent HR platform.',
                'metrics'   => [['num'=>'500+','lbl'=>'Employees Managed'],['num'=>'99%','lbl'=>'Payroll Accuracy'],['num'=>'40h','lbl'=>'Saved Monthly']],
                'userRole'  => 'HR Director', 'userName' => 'Admin User',
                'sbSection' => 'HR Modules',
                'kpiPfx'    => ['Total','Active','On Leave','New Hires','Pending','Completed','Approved','Scheduled'],
                'featSuffix'=> 'with automated payroll, leave approval workflows, and performance dashboards.',
            ];
        }

        if (preg_match('/\bcrm\b|sales|lead|deal|pipeline|prospect|opportunity|campaign|funnel/', $t)) {
            $colors = [
                ['#059669','#047857','#d1fae5','#020f0a 0%,#051a10 40%,#071d12 100%'],
                ['#10b981','#059669','#ecfdf5','#020d08 0%,#041a0a 40%,#051d0c 100%'],
                ['#06b6d4','#0891b2','#cffafe','#020e12 0%,#051a20 40%,#062830 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'crm',        'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '📈 CRM Platform · Sales Pipeline · Revenue Intelligence',
                'heroSub'   => 'Supercharge your sales team with intelligent lead tracking, deal pipeline management, customer insights, and automated follow-up workflows.',
                'metrics'   => [['num'=>'340%','lbl'=>'Pipeline Growth'],['num'=>'2.4x','lbl'=>'Faster Close Rate'],['num'=>'$1.8M','lbl'=>'Revenue Tracked']],
                'userRole'  => 'Sales Director', 'userName' => 'Admin User',
                'sbSection' => 'Sales Modules',
                'kpiPfx'    => ['Total','Hot','Qualified','Closed','Active','New','Won','In Progress'],
                'featSuffix'=> 'with pipeline visualization, activity tracking, and revenue forecasting.',
            ];
        }

        if (preg_match('/inventory|warehouse|stock|logistics|supply|shipment|purchase|vendor|supplier|freight|dispatch/', $t)) {
            $colors = [
                ['#f59e0b','#d97706','#fef3c7','#100a02 0%,#1a1205 40%,#1e1608 100%'],
                ['#ea580c','#c2410c','#fff7ed','#180802 0%,#1f0f05 40%,#241206 100%'],
                ['#84cc16','#65a30d','#ecfccb','#070e02 0%,#0f1a03 40%,#121e04 100%'],
            ];
            [$brand,$brandDk,$brandLight,$gradient] = $colors[$v];
            return [
                'domain'    => 'inventory',  'brand' => $brand, 'brandDk' => $brandDk,
                'brandLight'=> $brandLight,  'gradient' => $gradient,
                'heroTag'   => '📦 Inventory Platform · WMS · Real-time Tracking',
                'heroSub'   => 'Take full control of your supply chain — track stock levels, manage vendors, process purchase orders and optimize warehouse operations in real time.',
                'metrics'   => [['num'=>'99.8%','lbl'=>'Inventory Accuracy'],['num'=>'10K+','lbl'=>'SKUs Tracked'],['num'=>'48h','lbl'=>'Avg Fulfillment']],
                'userRole'  => 'Warehouse Manager', 'userName' => 'Admin User',
                'sbSection' => 'Inventory Modules',
                'kpiPfx'    => ['Total','In Stock','Low Stock','Pending','Shipped','Received','Returned','Reserved'],
                'featSuffix'=> 'with barcode scanning, reorder alerts, and multi-warehouse support.',
            ];
        }

        // Default — 3 color variants so different apps look distinct
        $defColors = [
            ['#6366f1','#4f46e5','#eef2ff','#06040f 0%,#0f0a24 40%,#130d2e 100%'],
            ['#8b5cf6','#7c3aed','#f3e8ff','#0f0620 0%,#160930 40%,#1a0b38 100%'],
            ['#0ea5e9','#0284c7','#e0f2fe','#020d18 0%,#0b1a2e 40%,#082330 100%'],
        ];
        [$brand,$brandDk,$brandLight,$gradient] = $defColors[$v];
        return [
            'domain'    => 'default',    'brand' => $brand, 'brandDk' => $brandDk,
            'brandLight'=> $brandLight,  'gradient' => $gradient,
            'heroTag'   => '✦ Enterprise Platform · Production Ready · AI Powered',
            'heroSub'   => 'The complete enterprise management platform. Manage all operations, workflows and teams from one intelligent, scalable system built for your business.',
            'metrics'   => [['num'=>(string)count($entities),'lbl'=>'Modules Ready'],['num'=>'∞','lbl'=>'Records Supported'],['num'=>'24/7','lbl'=>'Always Available']],
            'userRole'  => 'Super Admin', 'userName' => 'Admin User',
            'sbSection' => 'Modules',
            'kpiPfx'    => ['Total','Active','New','Pending','Completed','Published','Registered','Processed'],
            'featSuffix'=> 'with full CRUD operations, search, filters, export, and audit logging.',
        ];
    }

    private function entityIcon(string $name): string
    {
        $n   = strtolower($name);
        $map = [
            'patient'      => '🤒', 'doctor'       => '👨‍⚕️', 'nurse'        => '👩‍⚕️',
            'appointment'  => '📅', 'medicine'     => '💊', 'drug'         => '💊',
            'pharmacy'     => '💊', 'ward'         => '🏥', 'bed'          => '🛏️',
            'surgery'      => '🔬', 'lab'          => '🧪', 'diagnosis'    => '🩺',
            'prescription' => '📋', 'ambulance'    => '🚑', 'insurance'    => '🛡️',
            'vital'        => '❤️', 'treatment'    => '💉', 'equipment'    => '⚕️',
            'product'      => '📦', 'item'         => '📦', 'sku'          => '📦',
            'inventory'    => '📦', 'stock'        => '📦', 'warehouse'    => '🏭',
            'order'        => '🛒', 'cart'         => '🛒', 'purchase'     => '🛒',
            'sale'         => '💰', 'revenue'      => '📈', 'payment'      => '💳',
            'invoice'      => '🧾', 'billing'      => '💳', 'transaction'  => '💳',
            'customer'     => '👤', 'client'       => '👤', 'user'         => '👤',
            'member'       => '👤', 'contact'      => '📞', 'lead'         => '📊',
            'employee'     => '👥', 'staff'        => '👥', 'team'         => '👥',
            'department'   => '🏢', 'branch'       => '🏢', 'office'       => '🏢',
            'student'      => '🎓', 'teacher'      => '👨‍🏫', 'course'       => '📚',
            'class'        => '🏫', 'grade'        => '📝', 'exam'         => '✏️',
            'assignment'   => '📝', 'curriculum'   => '📚', 'attendance'   => '✅',
            'menu'         => '🍽️', 'dish'         => '🍜', 'recipe'       => '🍳',
            'table'        => '🍽️', 'reservation'  => '📅', 'chef'         => '👨‍🍳',
            'ingredient'   => '🥕', 'kitchen'      => '🍳', 'food'         => '🍔',
            'room'         => '🚪', 'booking'      => '📅', 'guest'        => '🏨',
            'property'     => '🏠', 'tenant'       => '👤', 'lease'        => '📄',
            'maintenance'  => '🔧', 'amenity'      => '⭐', 'hotel'        => '🏨',
            'account'      => '🏦', 'loan'         => '💰', 'ledger'       => '📒',
            'budget'       => '💰', 'expense'      => '📉', 'payroll'      => '💵',
            'tax'          => '🧮', 'audit'        => '🔍', 'asset'        => '🏦',
            'leave'        => '🌴', 'payslip'      => '💵', 'recruitment'  => '🎯',
            'candidate'    => '🎯', 'training'     => '📚', 'performance'  => '⭐',
            'deal'         => '🤝', 'opportunity'  => '🎯', 'pipeline'     => '📊',
            'campaign'     => '📢', 'report'       => '📊', 'analytics'    => '📈',
            'category'     => '🏷️', 'tag'          => '🏷️', 'role'         => '🔑',
            'permission'   => '🔑', 'setting'      => '⚙️', 'config'       => '⚙️',
            'supplier'     => '🚚', 'vendor'       => '🚚', 'shipment'     => '🚚',
            'delivery'     => '🚚', 'logistics'    => '🚚', 'vehicle'      => '🚗',
            'driver'       => '🚗', 'route'        => '🗺️', 'ticket'       => '🎫',
            'task'         => '✅', 'project'      => '🗂️', 'milestone'    => '🎯',
            'notification' => '🔔', 'message'      => '💬', 'email'        => '📧',
            'feedback'     => '⭐', 'review'       => '⭐', 'rating'       => '⭐',
            'blog'         => '✍️', 'post'         => '✍️', 'article'      => '✍️',
            'document'     => '📄', 'certificate'  => '🏆', 'award'        => '🏆',
            'location'     => '📍', 'address'      => '📍', 'map'          => '🗺️',
            'discount'     => '🏷️', 'coupon'       => '🎟️', 'promotion'    => '📢',
            'brand'        => '🏷️', 'banner'       => '🖼️', 'media'        => '🖼️',
            'subscription' => '🔄', 'plan'         => '📋', 'feature'      => '⚡',
        ];
        foreach ($map as $keyword => $icon) {
            if (str_contains($n, $keyword)) {
                return $icon;
            }
        }
        return '📋';
    }

    private function enumInput(string $name, array $opts, ?string $var, string $required): string
    {
        $options = "<option value=\"\">-- Select --</option>\n";
        foreach ($opts as $opt) {
            $selected = $var
                ? "{{ old('{$name}', \${$var}->{$name}) === '{$opt}' ? 'selected' : '' }}"
                : "{{ old('{$name}') === '{$opt}' ? 'selected' : '' }}";
            $optLabel = Str::title(str_replace('_', ' ', $opt));
            $options .= "                       <option value=\"{$opt}\" {$selected}>{$optLabel}</option>\n";
        }
        return "<select name=\"{$name}\" id=\"{$name}\" {$required} class=\"w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('{$name}') border-red-400 @enderror\">\n                       {$options}                   </select>";
    }
}
