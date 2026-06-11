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
Route::post('/contact', [LandingController::class, 'contact'])->name('contact');
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
        $initial = strtoupper(mb_substr($appName, 0, 1));

        $navLinks = '';
        $mobileNavLinks = '';
        foreach ($entities as $idx => $e) {
            $name      = \Illuminate\Support\Str::studly($e['name']);
            $plural    = \Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($name));
            $plurTitle = \Illuminate\Support\Str::title(str_replace('_', ' ', $plural));
            $icon      = $this->entityIcon($e['name']);
            $navLinks .= <<<BLADE2

                    <a href="{{ route('{$plural}.index') }}"
                       class="nav-link {{ request()->routeIs('{$plural}.*') ? 'active' : '' }}">
                        <span class="nav-icon">{$icon}</span>
                        {$plurTitle}
                    </a>
BLADE2;
            if ($idx < 3) {
                $mobileNavLinks .= <<<BLADE3

    <a href="{{ route('{$plural}.index') }}" class="mob-nb {{ request()->routeIs('{$plural}.*') ? 'active' : '' }}">
        <span>{$icon}</span><span class="mob-nbl">{$plurTitle}</span>
    </a>
BLADE3;
            }
        }

        return <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '{$appName}')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{--brand:{$brandColor};--bg:#f1f5f9;--bdr:#e2e8f0;--text:#0f172a;--text2:#475569;--text3:#94a3b8;--sbw:240px}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;font-family:'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased;background:var(--bg);color:var(--text)}
        a{text-decoration:none;color:inherit}
        .app-wrap{display:flex;height:100vh;overflow:hidden}
        .sidebar{width:var(--sbw);background:var(--brand);color:#fff;display:flex;flex-direction:column;flex-shrink:0;height:100vh;overflow-y:auto;overflow-x:hidden;z-index:50;transition:left .25s}
        .sidebar::-webkit-scrollbar{width:3px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:4px}
        .sb-head{padding:18px 14px 13px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.12)}
        .sb-icon{width:33px;height:33px;background:rgba(255,255,255,.2);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;flex-shrink:0}
        .sb-name{font-size:13.5px;font-weight:800;color:#fff}
        .sb-sub{font-size:10px;color:rgba(255,255,255,.6);margin-top:1px}
        .sb-section{padding:7px 14px 3px;font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5)}
        .nav-link{display:flex;align-items:center;gap:9px;padding:9px 13px;margin:2px 7px;border-radius:9px;color:rgba(255,255,255,.8);font-size:13px;font-weight:500;text-decoration:none;width:calc(100% - 14px);transition:.15s}
        .nav-link:hover,.nav-link.active{background:rgba(255,255,255,.18);color:#fff}
        .nav-link.active{font-weight:700}
        .nav-icon{font-size:15px;flex-shrink:0;width:19px;text-align:center}
        .sb-footer{margin-top:auto;padding:12px 14px;border-top:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:9px}
        .sb-av{width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0}
        .main-content{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0}
        .topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 22px;display:flex;align-items:center;height:56px;gap:14px;flex-shrink:0}
        .topbar-menu{display:none;background:none;border:none;padding:5px;border-radius:7px;color:var(--text2);font-size:18px;cursor:pointer}
        .topbar-title{font-size:14px;font-weight:700;color:var(--text)}
        .topbar-date{font-size:12px;color:var(--text3);margin-left:auto}
        .topbar-av{width:32px;height:32px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;margin-left:8px;flex-shrink:0}
        .page-body{flex:1;overflow-y:auto;padding:22px;background:var(--bg)}
        .page-body::-webkit-scrollbar{width:5px}
        .page-body::-webkit-scrollbar-thumb{background:var(--bdr);border-radius:3px}
        .alert-ok{background:#dcfce7;border:1px solid #86efac;color:#166534;padding:10px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
        .alert-err{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
        .mob-bnav{display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--bdr);z-index:200;padding:5px 0}
        .mob-nb{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;background:none;border:none;padding:5px 3px;color:var(--text3);min-width:0;cursor:pointer;text-decoration:none;font-family:inherit}
        .mob-nb.active{color:var(--brand)}
        .mob-nb span:first-child{font-size:18px}
        .mob-nbl{font-size:9.5px;font-weight:600;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        @media(max-width:768px){
            .sidebar{position:fixed;left:calc(-1 * var(--sbw));top:0;bottom:0}
            .sidebar.open{left:0;box-shadow:0 0 0 100vw rgba(0,0,0,.35)}
            .topbar-menu{display:flex}
            .mob-bnav{display:flex}
            .page-body{padding:14px;padding-bottom:68px}
        }
        @yield('styles')
    </style>
    @yield('head')
</head>
<body>
<div class="app-wrap" x-data="{ sbOpen: false }">
    <nav class="sidebar" :class="{ 'open': sbOpen }">
        <div class="sb-head">
            <div class="sb-icon">{$initial}</div>
            <div><div class="sb-name">{$appName}</div><div class="sb-sub">Management System</div></div>
        </div>
        <div style="padding:10px 8px 6px">
            <div class="sb-section">Main</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <div class="sb-section" style="margin-top:8px">Modules</div>
{$navLinks}
            <div style="margin:8px 8px 4px;padding-top:8px;border-top:1px solid rgba(255,255,255,.12);font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);padding-left:6px">System</div>
            <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span> Settings
            </a>
        </div>
        <div class="sb-footer">
            <div class="sb-av">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
            <div><div style="font-size:12.5px;font-weight:600;color:#fff">{{ auth()->user()->name ?? 'Admin' }}</div><div style="font-size:10px;color:rgba(255,255,255,.6)">Administrator</div></div>
        </div>
    </nav>
    <div class="main-content">
        <div class="topbar">
            <button class="topbar-menu" @click="sbOpen = !sbOpen">☰</button>
            <div class="topbar-title">@yield('title', 'Dashboard')</div>
            <div class="topbar-date">{{ now()->format('D, d M Y') }}</div>
            <div class="topbar-av">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
        </div>
        <div class="page-body">
            @if(session('success'))
                <div class="alert-ok">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-err">✕ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-err">@foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach</div>
            @endif
            @yield('content')
        </div>
    </div>
    <div x-show="sbOpen" @click="sbOpen=false" style="display:none;position:fixed;inset:0;z-index:49" x-bind:style="sbOpen ? 'display:block' : 'display:none'"></div>
</div>
<nav class="mob-bnav">
    <a href="{{ route('dashboard') }}" class="mob-nb {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span>🏠</span><span class="mob-nbl">Home</span>
    </a>
{$mobileNavLinks}
    <a href="{{ route('settings') }}" class="mob-nb {{ request()->routeIs('settings*') ? 'active' : '' }}">
        <span>⚙️</span><span class="mob-nbl">Settings</span>
    </a>
</nav>
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
        $profile      = $this->domainProfile($appName, $entities);
        $extras       = $this->domainExtras($profile['domain'], $appName);
        $brand        = $profile['brand'];
        $brandDk      = $profile['brandDk'];
        $brandLight   = $profile['brandLight'];
        $heroTag      = $profile['heroTag'];
        $heroSub      = $profile['heroSub'];
        $userRole     = $profile['userRole'];
        $userName     = $profile['userName'];
        $sbSection    = $profile['sbSection'];
        $featSfx      = $profile['featSuffix'];
        $appInitial   = strtoupper(mb_substr($appName, 0, 1));
        $userInitials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', str_replace(' ', '', $userName)), 0, 2));
        $year         = date('Y');

        // Palette for KPI / feature card accent colors (light bg tint + dark text/icon)
        $palette  = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#06b6d4','#84cc16','#a855f7'];
        $trends   = ['+12.4%','+8.7%','+5.1%','+18.3%','+3.8%','+9.6%','+6.4%','+11.2%'];
        $counts   = [1847,84,18,342,2891,127,438,73,512,284,96,156,631,89,447,238];

        // ── Hero metrics strip ─────────────────────────────────────────────
        $metricsHtml = '';
        foreach ($profile['metrics'] as $m) {
            $metricsHtml .= '<div class="hero-stat"><div class="hero-stat-num">' . $m['num'] . '</div><div class="hero-stat-lbl">' . $m['lbl'] . '</div></div>';
        }

        // ── Sidebar nav links ───────────────────────────────────────────────
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

        // ── Mobile bottom nav (top 5 items) ────────────────────────────────
        $mobileNavItems = '';
        $mobileSlice = array_slice($entities, 0, 4);
        foreach ($mobileSlice as $i => $e) {
            $label = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $icon  = $this->entityIcon($e['name']);
            $mobileNavItems .= "<button class=\"mob-nav-btn\" onclick=\"navTo('{$e['name']}','{$label}',document.getElementById('nav-{$e['name']}'))\">"
                             . "<span>{$icon}</span><span class=\"mob-nav-lbl\">{$label}</span></button>\n";
        }
        $mobileNavItems .= "<button class=\"mob-nav-btn\" onclick=\"showSettings()\"><span>⚙️</span><span class=\"mob-nav-lbl\">Settings</span></button>\n";

        // ── KPI cards ───────────────────────────────────────────────────────
        $cards = '';
        foreach ($entities as $i => $e) {
            $label  = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $color  = $palette[$i % count($palette)];
            $icon   = $this->entityIcon($e['name']);
            $rc     = $this->realisticCount($e['name'], $profile['domain']);
            $count  = number_format($rc['count']);
            $trend  = $rc['trend'];
            $bgL    = $color . '15';
            $kpiLbl = $profile['kpiPfx'][$i % 8] . ' ' . $label;
            $trendColor = '#10b981';
            $cards .= <<<CARD
<div class="kpi-card" onclick="navTo('{$e['name']}','{$label}',document.getElementById('nav-{$e['name']}'))">
  <div class="kpi-head">
    <div class="kpi-icon" style="background:{$bgL};color:{$color}">{$icon}</div>
    <span class="kpi-badge" style="color:{$trendColor};background:{$trendColor}18">▲ {$trend}</span>
  </div>
  <div class="kpi-num" style="color:{$color}">{$count}</div>
  <div class="kpi-lbl">{$kpiLbl}</div>
</div>
CARD;
        }

        // ── Feature cards (landing) ─────────────────────────────────────────
        $featureCards = '';
        foreach (array_slice($entities, 0, 6) as $i => $e) {
            $label = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $icon  = $this->entityIcon($e['name']);
            $color = $palette[$i % count($palette)];
            $bgL   = $color . '18';
            $featureCards .= <<<FEAT
<div class="feat-card">
  <div class="feat-icon" style="background:{$bgL};color:{$color}">{$icon}</div>
  <div class="feat-name">{$label} Management</div>
  <div class="feat-desc">Complete {$label} records {$featSfx}</div>
</div>
FEAT;
        }

        // ── Testimonials (from extras) ──────────────────────────────────────
        $testiHtml = '';
        foreach (array_slice($extras['testimonials'], 0, 3) as $t) {
            $testiHtml .= <<<TESTI
<div class="testi-card">
  <div class="testi-stars">★★★★★</div>
  <p class="testi-quote">"{$t['quote']}"</p>
  <div class="testi-person">
    <div class="testi-avatar">{$t['init']}</div>
    <div><div class="testi-name">{$t['name']}</div><div class="testi-role">{$t['role']}, {$t['company']}</div></div>
  </div>
</div>
TESTI;
        }

        // ── Chart data ──────────────────────────────────────────────────────
        $chartSlice  = array_slice($entities, 0, 8);
        $chartLabels = implode(',', array_map(fn($e) => '"' . Str::title(str_replace('_',' ',Str::snake($e['name']))) . '"', $chartSlice));
        $chartData   = implode(',', array_map(fn($i) => $counts[$i % count($counts)], range(0, count($chartSlice) - 1)));
        $chartBg     = implode(',', array_map(fn($i) => '"' . $palette[$i % count($palette)] . '"', range(0, count($chartSlice) - 1)));

        // ── Entities JSON for CRUD renderer ────────────────────────────────
        $entitiesJson = json_encode(array_values(array_map(fn($e) => [
            'name'  => $e['name'],
            'label' => Str::title(str_replace('_', ' ', Str::snake($e['name']))),
        ], $entities)));

        // ── Settings HTML (built in PHP, injected as JS constant) ──────────
        $settingsHtmlJson = json_encode($this->buildSettingsHtml($appName, $userName, $userRole, $userInitials, $brand));
        $entityCount      = count($entities);

        // ── Trusted logos strip ─────────────────────────────────────────────
        $trustedHtml = '';
        foreach (array_slice($extras['trustedBy'], 0, 5) as $org) {
            $trustedHtml .= "<span class=\"trusted-logo\">{$org}</span>";
        }

        // ── Landing pricing (first 2 plans) ────────────────────────────────
        $pricingHtml = '';
        foreach (array_slice($extras['pricing'], 0, 3) as $p) {
            $hlClass = $p['highlight'] ? ' pricing-highlight' : '';
            $badge   = $p['highlight'] ? '<div class="pricing-badge">Most Popular</div>' : '';
            $feats   = '';
            foreach (array_slice($p['features'], 0, 4) as $f) {
                $feats .= "<div class=\"pricing-feat\">✓ {$f}</div>";
            }
            $pricingHtml .= <<<PRICING
<div class="pricing-card{$hlClass}">
  {$badge}
  <div class="pricing-plan">{$p['name']}</div>
  <div class="pricing-price">{$p['price']}<span class="pricing-period">{$p['period']}</span></div>
  <div class="pricing-desc">{$p['desc']}</div>
  <div class="pricing-feats">{$feats}</div>
  <button class="pricing-btn" onclick="showDash()">{$p['cta']}</button>
</div>
PRICING;
        }

        // ── FAQ (first 4 items) ─────────────────────────────────────────────
        $faqHtml = '';
        foreach (array_slice($extras['faq'], 0, 4) as $idx => $f) {
            $id = "faq{$idx}";
            $faqHtml .= <<<FAQ
<details class="faq-item">
  <summary class="faq-q">{$f['q']}</summary>
  <div class="faq-a">{$f['a']}</div>
</details>
FAQ;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$appName}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<!-- LIGHT COLORFUL THEME — REPLACED DARK -->
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --brand:{$brand};--brand-dk:{$brandDk};--brand-lt:{$brandLight};
  --bg:#f1f5f9;--surface:#fff;--bdr:#e2e8f0;--bdr2:#f1f5f9;
  --text:#0f172a;--text2:#475569;--text3:#94a3b8;
  --sh:0 1px 3px rgba(0,0,0,.06),0 4px 12px rgba(0,0,0,.04);
  --shm:0 4px 20px rgba(0,0,0,.1);--sbw:240px
}
html,body{height:100%;font-family:'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased;background:var(--bg);color:var(--text)}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:inherit}
/* ── Screens ─────────────────────────────────────────────────── */
.screen{display:none}
.screen.active{display:block}
/* ─────────── LANDING ────────────────────────────────────────── */
#screen-landing{background:#fff}
.ln-nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.96);backdrop-filter:blur(16px);border-bottom:1px solid var(--bdr);padding:0 5%}
.ln-nav-in{max-width:1200px;margin:0 auto;display:flex;align-items:center;height:62px;gap:28px}
.ln-logo{display:flex;align-items:center;gap:9px;font-size:17px;font-weight:800;color:var(--text);flex-shrink:0}
.ln-logo-i{width:32px;height:32px;background:var(--brand);border-radius:8px;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900}
.ln-links{display:flex;gap:22px;margin-left:auto}
.ln-link{font-size:13.5px;font-weight:500;color:var(--text2);background:none;border:none;padding:0;transition:color .15s}
.ln-link:hover{color:var(--brand)}
.ln-nav-cta{background:var(--brand);color:#fff;border:none;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;transition:.15s}
.ln-nav-cta:hover{background:var(--brand-dk)}
/* Hero */
.ln-hero{background:linear-gradient(135deg,var(--brand-lt) 0%,#fff 65%);padding:76px 5% 56px;text-align:center;position:relative;overflow:hidden}
.ln-hero::after{content:'';position:absolute;top:-30%;right:-8%;width:500px;height:500px;background:var(--brand);opacity:.06;border-radius:50%;pointer-events:none}
.ln-eyebrow{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--bdr);color:var(--text2);padding:5px 14px;border-radius:100px;font-size:11.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;margin-bottom:22px;box-shadow:var(--sh)}
.ln-h1{font-size:clamp(2rem,5vw,3.6rem);font-weight:900;line-height:1.08;letter-spacing:-.03em;color:var(--text);margin-bottom:16px}
.ln-h1 em{font-style:normal;color:var(--brand)}
.ln-sub{font-size:clamp(.9rem,2vw,1.07rem);color:var(--text2);max-width:580px;margin:0 auto 34px;line-height:1.72}
.ln-ctas{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:44px}
.btn-lp{background:var(--brand);color:#fff;border:none;padding:13px 34px;border-radius:11px;font-size:14.5px;font-weight:700;box-shadow:0 4px 18px rgba(0,0,0,.12);transition:.2s}
.btn-lp:hover{background:var(--brand-dk);transform:translateY(-1px)}
.btn-lg{background:#fff;color:var(--text);border:1.5px solid var(--bdr);padding:13px 34px;border-radius:11px;font-size:14.5px;font-weight:600;transition:.2s}
.btn-lg:hover{border-color:var(--brand);color:var(--brand)}
.hero-stats{display:flex;justify-content:center;gap:36px;flex-wrap:wrap}
.hero-stat-num{font-size:1.75rem;font-weight:900;color:var(--brand)}
.hero-stat-lbl{font-size:12px;color:var(--text3);margin-top:3px;font-weight:500}
/* Trusted */
.ln-trusted{border-top:1px solid var(--bdr);border-bottom:1px solid var(--bdr);background:#fafbfc;padding:26px 5%;text-align:center}
.trusted-lbl{font-size:11.5px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--text3);margin-bottom:14px}
.trusted-logos{display:flex;justify-content:center;flex-wrap:wrap;gap:9px}
.trusted-logo{border:1px solid var(--bdr);border-radius:8px;padding:7px 16px;font-size:11.5px;font-weight:700;color:var(--text3);background:#fff}
/* Sections */
.ln-sec{padding:68px 5%}
.ln-sec-alt{background:#fafbfc}
.ln-sh{text-align:center;max-width:620px;margin:0 auto 48px}
.eyebrow-sm{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--brand);margin-bottom:10px}
.sec-title{font-size:clamp(1.6rem,3.5vw,2.3rem);font-weight:800;color:var(--text);line-height:1.15;margin-bottom:11px}
.sec-sub{font-size:14.5px;color:var(--text2);line-height:1.7}
.feat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;max-width:1200px;margin:0 auto}
.feat-card{background:#fff;border:1px solid var(--bdr);border-radius:13px;padding:22px;transition:.2s}
.feat-card:hover{border-color:var(--brand);box-shadow:var(--shm);transform:translateY(-3px)}
.feat-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:19px;margin-bottom:12px}
.feat-name{font-size:13.5px;font-weight:700;color:var(--text);margin-bottom:5px}
.feat-desc{font-size:12.5px;color:var(--text2);line-height:1.55}
/* Testimonials */
.testi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:18px;max-width:1200px;margin:0 auto}
.testi-card{background:#fff;border:1px solid var(--bdr);border-radius:13px;padding:26px;display:flex;flex-direction:column;gap:14px}
.testi-stars{color:#f59e0b;font-size:13px;letter-spacing:2px}
.testi-quote{font-size:13.5px;color:var(--text2);line-height:1.7;font-style:italic;flex:1}
.testi-person{display:flex;align-items:center;gap:11px;padding-top:14px;border-top:1px solid var(--bdr2)}
.testi-avatar{width:40px;height:40px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0}
.testi-name{font-size:13px;font-weight:700;color:var(--text)}
.testi-role{font-size:11.5px;color:var(--text3)}
/* Pricing */
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:18px;max-width:960px;margin:0 auto}
.pricing-card{background:#fff;border:1.5px solid var(--bdr);border-radius:15px;padding:26px;display:flex;flex-direction:column;position:relative}
.pricing-card.pricing-highlight{border-color:var(--brand);box-shadow:0 0 0 4px {$brandLight}}
.pricing-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--brand);color:#fff;font-size:10.5px;font-weight:800;padding:3px 13px;border-radius:20px;white-space:nowrap}
.pricing-plan{font-size:14.5px;font-weight:800;color:var(--text);margin-bottom:4px}
.pricing-price{font-size:2.1rem;font-weight:900;color:var(--brand);line-height:1;margin:7px 0 3px}
.pricing-period{font-size:12.5px;font-weight:500;color:var(--text3)}
.pricing-desc{font-size:12.5px;color:var(--text3);margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--bdr)}
.pricing-feats{flex:1;margin-bottom:18px}
.pricing-feat{font-size:12.5px;color:var(--text2);padding:4px 0;display:flex;gap:7px;align-items:flex-start}
.pricing-feat::before{content:'✓';color:var(--brand);font-weight:700;flex-shrink:0}
.pricing-btn{padding:10px 18px;border-radius:9px;font-size:13.5px;font-weight:700;border:1.5px solid var(--brand);color:var(--brand);background:#fff;transition:.2s;width:100%}
.pricing-highlight .pricing-btn,.pricing-btn:hover{background:var(--brand);color:#fff}
/* FAQ */
.faq-wrap{max-width:740px;margin:0 auto}
.faq-item{background:#fff;border:1px solid var(--bdr);border-radius:11px;margin-bottom:9px;overflow:hidden}
.faq-q{padding:17px 19px;font-size:14px;font-weight:600;color:var(--text);cursor:pointer;display:flex;justify-content:space-between;align-items:center;list-style:none}
.faq-q::-webkit-details-marker{display:none}
.faq-q::after{content:'+';font-size:19px;font-weight:300;color:var(--text3);flex-shrink:0}
details[open] .faq-q::after{content:'−';color:var(--brand)}
details[open] .faq-q{color:var(--brand)}
.faq-a{padding:0 19px 16px;font-size:13.5px;color:var(--text2);line-height:1.75;border-top:1px solid var(--bdr2)}
/* CTA banner */
.ln-cta-wrap{background:linear-gradient(135deg,var(--brand) 0%,var(--brand-dk) 100%);padding:68px 5%;text-align:center;color:#fff}
.ln-cta-wrap h2{font-size:clamp(1.6rem,3.5vw,2.3rem);font-weight:900;margin-bottom:12px}
.ln-cta-wrap p{font-size:15.5px;opacity:.85;margin-bottom:34px}
/* Footer */
.ln-foot{background:#0f172a;padding:52px 5% 0;color:#94a3b8}
.ln-foot-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:36px;margin-bottom:36px}
.ln-foot-brand p{font-size:12.5px;line-height:1.7;margin-top:10px;max-width:200px}
.ln-foot-col h4{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#f1f5f9;margin-bottom:13px}
.ln-foot-col a{display:block;font-size:12.5px;color:#64748b;margin-bottom:7px;transition:color .15s}
.ln-foot-col a:hover{color:#f1f5f9}
.ln-foot-bottom{border-top:1px solid #1e293b;padding:16px 0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:9px;max-width:1200px;margin:0 auto}
.ln-foot-bottom span,.ln-foot-bottom a{font-size:12px;color:#475569}
/* ─────────── LOGIN ───────────────────────────────────────────── */
#screen-login{min-height:100vh;background:var(--bg);display:none}
#screen-login.active{display:flex;align-items:center;justify-content:center}
.login-wrap{width:100%;max-width:390px;padding:20px}
.login-logo{text-align:center;margin-bottom:26px}
.login-logo-i{width:50px;height:50px;background:var(--brand);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:21px;font-weight:900;color:#fff;margin:0 auto 11px}
.login-logo-name{font-size:19px;font-weight:800;color:var(--text)}
.login-logo-sub{font-size:12.5px;color:var(--text3);margin-top:2px}
.login-card{background:#fff;border:1px solid var(--bdr);border-radius:17px;padding:34px;box-shadow:var(--shm)}
.login-h{font-size:19px;font-weight:700;color:var(--text);margin-bottom:3px}
.login-hint{font-size:12.5px;color:var(--text3);margin-bottom:26px}
.ln-f{margin-bottom:14px}
.ln-lbl{display:block;font-size:11.5px;font-weight:600;color:var(--text2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em}
.ln-inp{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:9px;font-size:13.5px;color:var(--text);background:#fff;outline:none;transition:border-color .15s;font-family:inherit}
.ln-inp:focus{border-color:var(--brand)}
.btn-login{width:100%;padding:12px;background:var(--brand);color:#fff;border:none;border-radius:9px;font-size:14.5px;font-weight:700;margin-top:4px;transition:.15s}
.btn-login:hover{background:var(--brand-dk)}
.login-foot{text-align:center;margin-top:18px;font-size:12.5px;color:var(--text3)}
/* ─────────── APP (DASHBOARD) ─────────────────────────────────── */
#screen-app{display:none}
#screen-app.active{display:flex;height:100vh;overflow:hidden}
/* Sidebar — uses brand color */
.sb{width:var(--sbw);background:var(--brand);color:#fff;display:flex;flex-direction:column;flex-shrink:0;height:100vh;overflow-y:auto;overflow-x:hidden;position:relative;z-index:50}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:4px}
.sb-head{padding:18px 15px 13px;display:flex;align-items:center;gap:9px;border-bottom:1px solid rgba(255,255,255,.12)}
.sb-logo-i{width:33px;height:33px;background:rgba(255,255,255,.2);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;flex-shrink:0}
.sb-app-name{font-size:13.5px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-app-role{font-size:10.5px;color:rgba(255,255,255,.6);margin-top:1px}
.sb-srch{padding:9px 11px}
.sb-srch input{width:100%;background:rgba(255,255,255,.12);border:none;border-radius:8px;padding:7px 11px;font-size:12.5px;color:#fff;outline:none;font-family:inherit}
.sb-srch input::placeholder{color:rgba(255,255,255,.5)}
.sb-sec-lbl{padding:8px 15px 3px;font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5)}
.nav-item{display:flex;align-items:center;gap:9px;padding:8px 13px;margin:2px 7px;border-radius:9px;background:none;border:none;color:rgba(255,255,255,.8);font-size:12.5px;font-weight:500;text-align:left;width:calc(100% - 14px);transition:.15s;cursor:pointer}
.nav-item:hover{background:rgba(255,255,255,.13);color:#fff}
.nav-item.active{background:rgba(255,255,255,.22);color:#fff;font-weight:700}
.nav-icon{font-size:15px;flex-shrink:0;width:19px;text-align:center}
.nav-label{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.nav-badge{background:rgba(255,255,255,.18);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;flex-shrink:0}
.sb-user{margin-top:auto;padding:12px 14px;border-top:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:9px}
.sb-av{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
.sb-uname{font-size:12.5px;font-weight:600;color:#fff}
.sb-urole{font-size:10.5px;color:rgba(255,255,255,.6)}
/* Content area */
.app-content{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0}
.topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 22px;display:flex;align-items:center;height:56px;gap:14px;flex-shrink:0;box-shadow:0 1px 0 var(--bdr)}
.topbar-menu{display:none;background:none;border:none;padding:5px;border-radius:7px;color:var(--text2);font-size:18px}
.topbar-bc{font-size:13.5px;color:var(--text2)}
.topbar-bc span{color:var(--text);font-weight:600}
.topbar-r{display:flex;align-items:center;gap:9px;margin-left:auto}
.tb-btn{width:34px;height:34px;background:var(--bg);border:1px solid var(--bdr);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;position:relative;flex-shrink:0}
.tb-notif::after{content:'3';position:absolute;top:-3px;right:-3px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;width:15px;height:15px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.tb-av{width:32px;height:32px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11.5px;font-weight:800;cursor:pointer;flex-shrink:0}
.dash-area{flex:1;overflow-y:auto;padding:22px;background:var(--bg)}
.dash-area::-webkit-scrollbar{width:5px}
.dash-area::-webkit-scrollbar-thumb{background:var(--bdr);border-radius:3px}
/* KPI cards */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:13px;margin-bottom:22px}
.kpi-card{background:#fff;border:1px solid var(--bdr);border-radius:13px;padding:18px;cursor:pointer;transition:.2s;position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px}
.kpi-card:hover{box-shadow:var(--shm);transform:translateY(-2px)}
.kpi-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px}
.kpi-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.kpi-badge{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:6px}
.kpi-num{font-size:1.7rem;font-weight:900;line-height:1;margin-bottom:4px}
.kpi-lbl{font-size:11.5px;color:var(--text2);font-weight:500}
/* Charts + tables */
.dg2{display:grid;grid-template-columns:1.4fr 1fr;gap:14px;margin-bottom:22px}
.dc{background:#fff;border:1px solid var(--bdr);border-radius:13px;padding:19px}
.dc-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.dc-title{font-size:13.5px;font-weight:700;color:var(--text)}
.dc-sub{font-size:11.5px;color:var(--text3)}
.dc-badge{background:var(--bg);border:1px solid var(--bdr);border-radius:6px;padding:3px 9px;font-size:11.5px;color:var(--text2)}
.tbl-wrap{overflow-x:auto}
table.tbl{width:100%;border-collapse:collapse;font-size:12.5px}
.tbl th{text-align:left;padding:9px 13px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);border-bottom:1px solid var(--bdr);background:#fafbfc;white-space:nowrap}
.tbl td{padding:11px 13px;border-bottom:1px solid var(--bdr2);color:var(--text2);vertical-align:middle}
.tbl tr:hover td{background:#fafbfc}
.tbl-badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:10.5px;font-weight:600}
.bg{background:#dcfce7;color:#16a34a}.bb{background:#dbeafe;color:#1d4ed8}
.by{background:#fef9c3;color:#a16207}.br{background:#fee2e2;color:#dc2626}
/* Module */
.mod-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:11px}
.mod-title{font-size:17px;font-weight:800;color:var(--text)}
.mod-acts{display:flex;gap:9px;align-items:center}
.btn-add{background:var(--brand);color:#fff;border:none;padding:9px 18px;border-radius:9px;font-size:12.5px;font-weight:700;display:flex;align-items:center;gap:6px;transition:.15s}
.btn-add:hover{background:var(--brand-dk)}
.btn-exp{background:#fff;color:var(--text2);border:1px solid var(--bdr);padding:9px 14px;border-radius:9px;font-size:12.5px;font-weight:600}
.search-bar{display:flex;align-items:center;gap:7px;background:#fff;border:1.5px solid var(--bdr);border-radius:9px;padding:0 13px;transition:.15s;margin-bottom:14px}
.search-bar:focus-within{border-color:var(--brand)}
.search-bar input{border:none;outline:none;padding:9px 0;font-size:12.5px;color:var(--text);background:transparent;width:100%;font-family:inherit}
.tbl-ab{background:none;border:1px solid var(--bdr);border-radius:6px;padding:4px 11px;font-size:11.5px;font-weight:600;color:var(--text2);transition:.15s;margin-right:3px;cursor:pointer}
.tbl-ab:hover{border-color:var(--brand);color:var(--brand)}
.tbl-ab.del:hover{border-color:#ef4444;color:#ef4444}
.pgn{display:flex;align-items:center;gap:5px;margin-top:14px;justify-content:flex-end}
.pg{width:30px;height:30px;border:1px solid var(--bdr);border-radius:7px;background:#fff;font-size:12.5px;font-weight:500;color:var(--text2);transition:.15s;cursor:pointer}
.pg:hover,.pg.on{background:var(--brand);color:#fff;border-color:var(--brand)}
/* Mobile bottom nav */
.mob-bnav{display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--bdr);z-index:200;padding:5px 0}
.mob-nb{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;background:none;border:none;padding:5px 3px;color:var(--text3);min-width:0}
.mob-nb.on{color:var(--brand)}
.mob-nb span:first-child{font-size:17px}
.mob-nbl{font-size:9.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
/* ─── Responsive ─────────────────────────────────────────────── */
@media(max-width:768px){
  .sb{position:fixed;left:calc(-1 * var(--sbw));top:0;bottom:0;transition:left .25s;z-index:300}
  .sb.open{left:0;box-shadow:0 0 0 100vw rgba(0,0,0,.35)}
  .app-content{width:100%}
  .topbar-menu{display:flex}
  .mob-bnav{display:flex}
  .dash-area{padding:14px;padding-bottom:68px}
  .kpi-grid{grid-template-columns:repeat(2,1fr);gap:10px}
  .kpi-num{font-size:1.35rem}
  .dg2{grid-template-columns:1fr}
  .ln-foot-grid{grid-template-columns:1fr 1fr}
  .ln-links{display:none}
  .mod-hdr{flex-direction:column;align-items:flex-start}
}
@media(max-width:480px){
  .kpi-grid{grid-template-columns:1fr 1fr}
  .testi-grid{grid-template-columns:1fr}
  .pricing-grid{grid-template-columns:1fr}
  .feat-grid{grid-template-columns:1fr 1fr}
  .ln-h1{font-size:1.9rem}
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<!-- ══════════ LANDING ═════════════════════════════════════════ -->
<div id="screen-landing" class="screen active">
  <nav class="ln-nav">
    <div class="ln-nav-in">
      <div class="ln-logo"><div class="ln-logo-i">{$appInitial}</div>{$appName}</div>
      <div class="ln-links">
        <button class="ln-link">Features</button>
        <button class="ln-link">Pricing</button>
        <button class="ln-link">About</button>
        <button class="ln-link">Contact</button>
      </div>
      <button class="ln-nav-cta" onclick="showLogin()" style="margin-left:auto">Sign In</button>
    </div>
  </nav>
  <div class="ln-hero">
    <div class="ln-eyebrow">{$heroTag}</div>
    <h1 class="ln-h1">{$appName} <em>— Built to Perform</em></h1>
    <p class="ln-sub">{$heroSub}</p>
    <div class="ln-ctas">
      <button class="btn-lp" onclick="showDash()">Start Free Trial</button>
      <button class="btn-lg" onclick="showDash()">See Live Demo →</button>
    </div>
    <div class="hero-stats">{$metricsHtml}</div>
  </div>
  <div class="ln-trusted">
    <div class="trusted-lbl">Trusted by leading organizations worldwide</div>
    <div class="trusted-logos">{$trustedHtml}</div>
  </div>
  <section class="ln-sec">
    <div class="ln-sh">
      <div class="eyebrow-sm">Features</div>
      <h2 class="sec-title">Everything your team needs</h2>
      <p class="sec-sub">A purpose-built platform for {$userRole}s — complete workflows, not just tools.</p>
    </div>
    <div class="feat-grid">{$featureCards}</div>
  </section>
  <section class="ln-sec ln-sec-alt">
    <div class="ln-sh">
      <div class="eyebrow-sm">Customer Stories</div>
      <h2 class="sec-title">Loved by industry leaders</h2>
      <p class="sec-sub">See what real teams say after switching to {$appName}.</p>
    </div>
    <div class="testi-grid">{$testiHtml}</div>
  </section>
  <section class="ln-sec">
    <div class="ln-sh">
      <div class="eyebrow-sm">Pricing</div>
      <h2 class="sec-title">Simple, transparent pricing</h2>
      <p class="sec-sub">All plans include a 14-day free trial. No credit card required.</p>
    </div>
    <div class="pricing-grid">{$pricingHtml}</div>
  </section>
  <section class="ln-sec ln-sec-alt">
    <div class="ln-sh">
      <div class="eyebrow-sm">FAQ</div>
      <h2 class="sec-title">Frequently Asked Questions</h2>
    </div>
    <div class="faq-wrap">{$faqHtml}</div>
  </section>
  <div class="ln-cta-wrap">
    <h2>Ready to transform your operations?</h2>
    <p>Join thousands of {$userRole}s already using {$appName}.</p>
    <button class="btn-lp" onclick="showDash()">Get Started Free →</button>
  </div>
  <footer class="ln-foot">
    <div class="ln-foot-grid">
      <div class="ln-foot-brand">
        <div style="display:flex;align-items:center;gap:8px;font-size:15px;font-weight:800;color:#f1f5f9">
          <div style="width:28px;height:28px;background:var(--brand);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;color:#fff">{$appInitial}</div>
          {$appName}
        </div>
        <p>The smarter way to manage {$appName} operations. Built for teams who demand excellence.</p>
      </div>
      <div class="ln-foot-col"><h4>Product</h4><a href="#">Features</a><a href="#">Pricing</a><a href="#">Integrations</a><a href="#">Changelog</a></div>
      <div class="ln-foot-col"><h4>Company</h4><a href="#">About</a><a href="#">Blog</a><a href="#">Careers</a><a href="#">Press</a></div>
      <div class="ln-foot-col"><h4>Support</h4><a href="#">Documentation</a><a href="#">Help Centre</a><a href="#">Status</a><a href="#">Contact</a></div>
      <div class="ln-foot-col"><h4>Legal</h4><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a><a href="#">Security</a></div>
    </div>
    <div class="ln-foot-bottom">
      <span>© {$year} {$appName}. All rights reserved.</span>
      <div style="display:flex;gap:14px"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a></div>
    </div>
  </footer>
</div>
<!-- ══════════ LOGIN ════════════════════════════════════════════ -->
<div id="screen-login" class="screen">
  <div class="login-wrap">
    <div class="login-logo">
      <div class="login-logo-i">{$appInitial}</div>
      <div class="login-logo-name">{$appName}</div>
      <div class="login-logo-sub">Sign in to your account</div>
    </div>
    <div class="login-card">
      <div class="login-h">Welcome back 👋</div>
      <div class="login-hint">Enter your credentials to continue</div>
      <div class="ln-f"><label class="ln-lbl">Email Address</label><input type="email" class="ln-inp" placeholder="admin@example.com" value="admin@example.com"></div>
      <div class="ln-f"><label class="ln-lbl">Password</label><input type="password" class="ln-inp" placeholder="••••••••" value="password"></div>
      <button class="btn-login" onclick="showDash()">Sign In →</button>
      <div class="login-foot">No account? <a href="#" style="color:var(--brand);font-weight:600">Start free trial</a></div>
    </div>
  </div>
</div>
<!-- ══════════ APP (DASHBOARD + MODULES) ══════════════════════ -->
<div id="screen-app" class="screen">
  <aside class="sb" id="sidebar">
    <div class="sb-head">
      <div class="sb-logo-i">{$appInitial}</div>
      <div><div class="sb-app-name">{$appName}</div><div class="sb-app-role">{$userRole}</div></div>
    </div>
    <div class="sb-srch"><input type="text" placeholder="Search..." id="sb-si" oninput="filterNav(this.value)"></div>
    <div class="sb-sec-lbl">Dashboard</div>
    <button class="nav-item active" onclick="showDash()" id="nav-dashboard"><span class="nav-icon">🏠</span><span class="nav-label">Dashboard</span></button>
    <div class="sb-sec-lbl">{$sbSection}</div>
    {$sidebarLinks}
    <div style="margin:7px 8px 3px;padding-top:7px;border-top:1px solid rgba(255,255,255,.12);font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);padding-left:7px">System</div>
    <button class="nav-item" onclick="showSettings()" id="nav-settings"><span class="nav-icon">⚙️</span><span class="nav-label">Settings</span></button>
    <button class="nav-item" onclick="showLogin()"><span class="nav-icon">🚪</span><span class="nav-label">Sign Out</span></button>
    <div class="sb-user"><div class="sb-av">{$userInitials}</div><div><div class="sb-uname">{$userName}</div><div class="sb-urole">{$userRole}</div></div></div>
  </aside>
  <div class="app-content">
    <div class="topbar">
      <button class="topbar-menu" onclick="toggleSidebar()">☰</button>
      <div class="topbar-bc">{$appName} / <span id="breadcrumb-cur">Dashboard</span></div>
      <div class="topbar-r">
        <div class="tb-btn tb-notif">🔔</div>
        <div class="tb-btn">❓</div>
        <div class="tb-av" onclick="showSettings()">{$userInitials}</div>
      </div>
    </div>
    <div class="dash-area" id="dash-content"></div>
  </div>
  <nav class="mob-bnav" id="mob-bnav" style="display:flex">
    <button class="mob-nb on" onclick="showDash()"><span>🏠</span><span class="mob-nbl">Home</span></button>
    {$mobileNavItems}
  </nav>
</div>
<button id="fs-btn" onclick="toggleFS()" style="position:fixed;bottom:20px;right:20px;z-index:9999;background:#fff;border:1.5px solid var(--bdr);color:var(--text2);padding:7px 15px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:var(--shm)">⛶ Full Screen</button>
<script>
var ENTITIES={$entitiesJson};
var SETTINGS_HTML={$settingsHtmlJson};
var chartInst=null;
var badges=[['bg','Active'],['bb','Verified'],['by','Pending'],['br','Inactive']];

/* Dashboard HTML */
function buildDashHtml(){
  var kpis='{$cards}';
  var activity=ENTITIES.slice(0,5).map(function(e,i){
    var t=['2m ago','5m ago','12m ago','1h ago','2h ago'][i]||'3h ago';
    return '<div style="display:flex;align-items:center;gap:10px;padding:10px;background:var(--bg);border-radius:8px"><div style="width:30px;height:30px;border-radius:8px;background:var(--brand);opacity:.15;flex-shrink:0"></div><div><div style="font-size:12.5px;font-weight:600;color:var(--text)">New '+e.label+' record added</div><div style="font-size:11px;color:var(--text3)">'+t+'</div></div></div>';
  }).join('');
  return '<div class="kpi-grid">'+kpis+'</div>'+
    '<div class="dg2">'+
      '<div class="dc"><div class="dc-head"><div><div class="dc-title">Overview</div><div class="dc-sub">All modules</div></div><span class="dc-badge">Last 30d</span></div><canvas id="dash-chart" height="180"></canvas></div>'+
      '<div class="dc"><div class="dc-head"><div class="dc-title">Recent Activity</div></div><div style="display:flex;flex-direction:column;gap:10px">'+activity+'</div></div>'+
    '</div>';
}

/* CRUD module HTML */
function moduleCrudHtml(entity){
  var rows='';
  for(var i=1;i<=8;i++){
    var b=badges[(i-1)%4];
    var d=new Date(Date.now()-i*86400000).toLocaleDateString();
    rows+='<tr><td>#'+String(i).padStart(3,'0')+'</td><td style="font-weight:600;color:var(--text)">'+entity.label+' '+i+'</td><td><span class="tbl-badge '+b[0]+'">'+b[1]+'</span></td><td>'+d+'</td>'+
      '<td><button class="tbl-ab" onclick="openEdit(\''+entity.label+'\','+i+')">✎ Edit</button>'+
      '<button class="tbl-ab del" onclick="deleteRow(this,\''+entity.label+'\','+i+')">✕ Delete</button></td></tr>';
  }
  return '<div class="mod-hdr"><div class="mod-title">'+entity.label+'</div>'+
    '<div class="mod-acts"><button class="btn-exp">⬇ Export</button>'+
    '<button class="btn-add" onclick="openAdd(\''+entity.label+'\')">＋ Add '+entity.label+'</button></div></div>'+
    '<div class="search-bar"><span style="color:var(--text3)">🔍</span><input type="text" placeholder="Search..." oninput="filterTbl(this,\'mod-tbl\')"></div>'+
    '<div class="dc"><div class="tbl-wrap"><table class="tbl" id="mod-tbl"><thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>'+rows+'</tbody></table></div>'+
    '<div class="pgn"><button class="pg">‹</button><button class="pg on">1</button><button class="pg">2</button><button class="pg">3</button><button class="pg">›</button></div></div>';
}

/* CRUD helpers */
function openAdd(label){
  var val=prompt('Add new '+label+' — enter name:');
  if(val&&val.trim()){var t=document.querySelector('#mod-tbl tbody');if(t){var tr=document.createElement('tr');tr.innerHTML='<td>#NEW</td><td style="font-weight:600;color:var(--text)">'+val.trim()+'</td><td><span class="tbl-badge bg">Active</span></td><td>Today</td><td><button class="tbl-ab" onclick="openEdit(\''+label+'\',0)">✎ Edit</button><button class="tbl-ab del" onclick="deleteRow(this,\''+label+'\',0)">✕ Delete</button></td>';t.insertBefore(tr,t.firstChild);}}
}
function openEdit(label,id){
  var val=prompt('Edit '+label+' #'+id+' — new name:');
  if(val&&val.trim()) alert(label+' updated successfully!');
}
function deleteRow(btn,label,id){
  if(confirm('Delete '+label+' #'+id+'?')) btn.closest('tr').remove();
}
function filterTbl(inp,id){
  var q=inp.value.toLowerCase();
  document.querySelectorAll('#'+id+' tbody tr').forEach(function(r){r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});
}

/* Navigation */
function setActive(el){
  document.querySelectorAll('.nav-item').forEach(function(n){n.classList.remove('active');});
  document.querySelectorAll('.mob-nb').forEach(function(n){n.classList.remove('on');});
  if(el) el.classList.add('active');
}
function showLanding(){document.querySelectorAll('.screen').forEach(function(s){s.classList.remove('active');});document.getElementById('screen-landing').classList.add('active');}
function showLogin(){document.querySelectorAll('.screen').forEach(function(s){s.classList.remove('active');});document.getElementById('screen-login').classList.add('active');}
function showDash(){
  document.querySelectorAll('.screen').forEach(function(s){s.classList.remove('active');});
  document.getElementById('screen-app').classList.add('active');
  document.getElementById('breadcrumb-cur').textContent='Dashboard';
  document.getElementById('dash-content').innerHTML=buildDashHtml();
  setActive(document.getElementById('nav-dashboard'));
  setTimeout(buildChart,80);closeSidebar();
}
function navTo(name,label,el){
  var entity=ENTITIES.find(function(e){return e.name===name;})||{name:name,label:label};
  document.querySelectorAll('.screen').forEach(function(s){s.classList.remove('active');});
  document.getElementById('screen-app').classList.add('active');
  document.getElementById('breadcrumb-cur').textContent=label;
  document.getElementById('dash-content').innerHTML=moduleCrudHtml(entity);
  setActive(el);closeSidebar();
}
function showSettings(){
  document.querySelectorAll('.screen').forEach(function(s){s.classList.remove('active');});
  document.getElementById('screen-app').classList.add('active');
  document.getElementById('breadcrumb-cur').textContent='Settings';
  document.getElementById('dash-content').innerHTML=SETTINGS_HTML;
  setActive(document.getElementById('nav-settings'));closeSidebar();
}

/* Sidebar */
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');}
document.addEventListener('click',function(e){
  var sb=document.getElementById('sidebar');
  if(sb&&sb.classList.contains('open')&&!sb.contains(e.target)&&!e.target.closest('.topbar-menu')) closeSidebar();
});

/* Search */
function filterNav(q){
  document.querySelectorAll('.nav-item').forEach(function(n){
    if(n.id==='nav-dashboard'||n.id==='nav-settings') return;
    var l=n.querySelector('.nav-label');
    if(l) n.style.display=l.textContent.toLowerCase().includes(q.toLowerCase())?'':'none';
  });
}

/* Chart */
function buildChart(){
  var c=document.getElementById('dash-chart');if(!c)return;
  if(chartInst){chartInst.destroy();chartInst=null;}
  chartInst=new Chart(c.getContext('2d'),{type:'bar',data:{labels:[{$chartLabels}],datasets:[{label:'Records',data:[{$chartData}],backgroundColor:[{$chartBg}],borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:10.5},maxRotation:30}},y:{grid:{color:'#f1f5f9'},ticks:{font:{size:10.5}}}}}});
}

/* Fullscreen */
function toggleFS(){
  var b=document.getElementById('fs-btn');
  if(!document.fullscreenElement){document.documentElement.requestFullscreen().catch(function(){});if(b)b.innerHTML='⊠ Exit';}
  else{document.exitFullscreen();if(b)b.innerHTML='⛶ Full Screen';}
}
document.addEventListener('fullscreenchange',function(){var b=document.getElementById('fs-btn');if(b)b.innerHTML=document.fullscreenElement?'⊠ Exit':'⛶ Full Screen';});

showLanding();
</script>
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
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:120',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:2000',
        ]);
        // TODO: wire up mail in config/mail.php and send via Mail::to(...)
        return back()->with('contact_success', 'Thank you! We\'ll be in touch within 24 hours.');
    }
}
PHP;
    }

    /** Domain-specific extra content: trustedBy, steps, testimonials, pricing, faq */
    private function domainExtras(string $domain, string $appName): array
    {
        $map = [

        'hospital' => [
            'trustedBy'    => ['Apollo Hospitals','Fortis Healthcare','Max Healthcare','Narayana Health','AIIMS Network'],
            'steps'        => [
                ['num'=>'01','icon'=>'📋','title'=>'Register Patient',     'desc'=>'Quick digital registration capturing demographics, insurance and emergency contacts in under 2 minutes.'],
                ['num'=>'02','icon'=>'👨‍⚕️','title'=>'Assign & Triage',     'desc'=>'Smart assignment to the right doctor or department based on specialty, availability and urgency.'],
                ['num'=>'03','icon'=>'💊','title'=>'Treatment & Records',   'desc'=>'Prescriptions, lab orders, imaging, vitals — all in one longitudinal clinical record accessible in real time.'],
                ['num'=>'04','icon'=>'🏠','title'=>'Discharge & Follow-up','desc'=>'Automated discharge summaries, billing finalization and follow-up scheduling in a single step.'],
            ],
            'testimonials' => [
                ['init'=>'RK','name'=>'Dr. Rajesh Kumar',   'role'=>'Chief Medical Officer',  'company'=>'Metro General Hospital', 'quote'=>'Patient wait times dropped 40% in the first month. The clinical dashboard gives our team real-time visibility across every ward.'],
                ['init'=>'PS','name'=>'Priya Sharma',        'role'=>'Head of Nursing',         'company'=>'Sunrise Medical Centre', 'quote'=>'Staff scheduling and ward management used to take 2 hours every morning. Now it\'s automated and done before I finish my coffee.'],
                ['init'=>'AM','name'=>'Anita Mehta',         'role'=>'Hospital Administrator',  'company'=>'City Healthcare Group',  'quote'=>'The billing and insurance claim module alone recovered its cost in the first quarter. Our collection rate went from 74% to 96%.'],
            ],
            'pricing' => [
                ['name'=>'Clinic',     'price'=>'$49', 'period'=>'/mo','desc'=>'Small clinics & diagnostic centres','highlight'=>false,'cta'=>'Start Free Trial','features'=>['Up to 50 patients/day','3 doctor accounts','OPD management','Basic reports','Email support']],
                ['name'=>'Hospital',   'price'=>'$149','period'=>'/mo','desc'=>'Mid-size hospitals & nursing homes', 'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['Unlimited patients','25 staff accounts','OPD + IPD + Pharmacy','Advanced analytics','HL7 integrations','Priority support']],
                ['name'=>'Enterprise', 'price'=>'Custom','period'=>'', 'desc'=>'Hospital chains & health systems',  'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Multi-branch management','Unlimited accounts','HIPAA compliance kit','Full API access','Dedicated CSM']],
            ],
            'faq' => [
                ['q'=>'Is the platform HIPAA compliant?',              'a'=>'Yes. All patient data is encrypted at rest (AES-256) and in transit (TLS 1.3). Role-based access and a full audit trail are included on every plan.'],
                ['q'=>'Can we migrate from our existing HIS?',         'a'=>'We provide a full data migration service with zero downtime. We support HL7, FHIR, and CSV/Excel import from all major HIS vendors.'],
                ['q'=>'Does it work across multiple hospital branches?','a'=>'Yes. The Enterprise plan supports unlimited branches with centralized reporting and independent branch-level user management.'],
                ['q'=>'What integrations are available?',              'a'=>'We connect with lab equipment, pharmacy systems, billing gateways, and EMRs via HL7 FHIR R4 APIs. Custom integrations on Enterprise.'],
                ['q'=>'How is patient data backed up?',                'a'=>'Encrypted backups every 6 hours, 30-day retention, geo-redundant storage, and point-in-time recovery within a 5-minute window.'],
                ['q'=>'Can we customize document templates?',          'a'=>'Yes — prescriptions, discharge summaries, lab reports, and consent forms are fully customizable per department with your branding.'],
                ['q'=>'Is there a mobile app for doctors and nurses?', 'a'=>'Yes. iOS and Android apps for clinical staff allow patient chart access, order entry, and real-time ward alerts from anywhere.'],
                ['q'=>'What is the uptime SLA?',                       'a'=>'99.9% uptime SLA on Hospital and Enterprise plans. Status page available 24/7 and dedicated on-call support for critical incidents.'],
            ],
        ],

        'ecommerce' => [
            'trustedBy'    => ['StyleHouse Fashion','TechGadgets Pro','Home & Living Co','Sports Arena','Beauty World'],
            'steps'        => [
                ['num'=>'01','icon'=>'📦','title'=>'Add Your Products','desc'=>'Bulk upload products with variants, images, pricing and inventory using CSV or our visual product builder.'],
                ['num'=>'02','icon'=>'🎨','title'=>'Customize Store',  'desc'=>'Pick a theme, connect your domain, set up payment gateways and shipping zones — no code required.'],
                ['num'=>'03','icon'=>'🚀','title'=>'Launch & Market',  'desc'=>'Go live instantly. Built-in SEO, discount engine, and email marketing drive traffic from day one.'],
                ['num'=>'04','icon'=>'📊','title'=>'Track & Scale',    'desc'=>'Real-time sales analytics, customer LTV insights, and inventory alerts keep you ahead of demand.'],
            ],
            'testimonials' => [
                ['init'=>'SJ','name'=>'Sarah Johnson','role'=>'Founder & CEO',       'company'=>'StyleHouse Fashion',  'quote'=>'We migrated from Shopify and our conversion rate went up 18%. The checkout flow is silky smooth and the analytics are far deeper.'],
                ['init'=>'MK','name'=>'Mike Karimi',  'role'=>'E-commerce Director', 'company'=>'TechGadgets Pro',     'quote'=>'Managing 12,000 SKUs across 3 warehouses used to be a nightmare. Now inventory sync is fully automatic and I sleep better at night.'],
                ['init'=>'LT','name'=>'Lisa Tang',    'role'=>'Marketing Manager',   'company'=>'Beauty World Online', 'quote'=>'The discount and loyalty engine drove a 34% increase in repeat purchases in 60 days. Best ROI we\'ve seen from any platform.'],
            ],
            'pricing' => [
                ['name'=>'Starter',    'price'=>'$29', 'period'=>'/mo','desc'=>'New stores getting started',        'highlight'=>false,'cta'=>'Start Free Trial','features'=>['Up to 500 products','2% transaction fee','Basic analytics','Email support']],
                ['name'=>'Growth',     'price'=>'$89', 'period'=>'/mo','desc'=>'Growing stores scaling up',         'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['Unlimited products','0% transaction fee','Advanced analytics','Abandoned cart recovery','Multi-currency','Priority support']],
                ['name'=>'Enterprise', 'price'=>'Custom','period'=>'', 'desc'=>'High-volume & multi-store ops',     'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Multi-store management','Custom integrations','Dedicated account manager','SLA guarantee','White-label option']],
            ],
            'faq' => [
                ['q'=>'Which payment gateways are supported?',    'a'=>'Stripe, PayPal, Razorpay, Paytm, SSLCommerz, and 40+ local gateways worldwide. Setup takes under 5 minutes.'],
                ['q'=>'Can I use my own domain?',                 'a'=>'Yes. Connect any domain you own or purchase a new one through us. Free SSL certificate included on all plans.'],
                ['q'=>'How does multi-currency and tax work?',    'a'=>'Automatic currency conversion based on customer location, and geo-specific tax rules (GST, VAT, sales tax) are fully automated.'],
                ['q'=>'How does inventory sync across warehouses?','a'=>'Real-time inventory sync across unlimited warehouses with low-stock alerts, auto-reorder rules, and backorder management.'],
                ['q'=>'Can I migrate my existing store?',         'a'=>'Yes. Free migration from Shopify, WooCommerce, Magento, and BigCommerce — products, orders, customers, and reviews included.'],
                ['q'=>'Is there a mobile storefront for customers?','a'=>'Yes — a Progressive Web App (PWA) is generated automatically, giving customers a native app-like experience on any device.'],
                ['q'=>'What shipping carriers are integrated?',   'a'=>'FedEx, UPS, DHL, USPS, Shiprocket, Delhivery, and 30+ carriers with live rate calculation and label printing.'],
                ['q'=>'How are product reviews managed?',         'a'=>'Built-in review system with moderation, verified-purchase badges, photo reviews, and auto-request emails after delivery.'],
            ],
        ],

        'education' => [
            'trustedBy'    => ['Oxford Academy','Cambridge Institute','MIT OpenLearning','Harvard EdTech','Stanford GSE'],
            'steps'        => [
                ['num'=>'01','icon'=>'📚','title'=>'Build Curriculum', 'desc'=>'Create courses, classes and lesson plans with a drag-and-drop builder. Upload videos, PDFs, and quizzes.'],
                ['num'=>'02','icon'=>'🎓','title'=>'Enroll Students',  'desc'=>'Bulk enrollment via CSV, self-registration portal, or direct teacher assignment. Parent accounts linked automatically.'],
                ['num'=>'03','icon'=>'📡','title'=>'Deliver & Engage', 'desc'=>'Live classes, recorded lectures, assignments, and discussion forums keep students active and on track.'],
                ['num'=>'04','icon'=>'📊','title'=>'Track & Report',   'desc'=>'Real-time attendance, gradebooks, learning analytics, and automated progress reports for students and parents.'],
            ],
            'testimonials' => [
                ['init'=>'DV','name'=>'Dr. Vidya Nair',  'role'=>'Principal',          'company'=>'Greenfield International School','quote'=>'The parent communication module transformed our school. Parents get real-time updates and we\'ve seen a 60% rise in engagement.'],
                ['init'=>'RP','name'=>'Rahul Patel',     'role'=>'Head of Department', 'company'=>'Sunrise College',                'quote'=>'Online exam management used to be chaos. Now invigilation, auto-grading, and result publication happen in one seamless workflow.'],
                ['init'=>'SM','name'=>'Sarah Mitchell',  'role'=>'E-learning Director','company'=>'Global Ed Network',              'quote'=>'We scaled from 200 to 15,000 students across 3 countries without a single performance issue. The platform just handles it.'],
            ],
            'pricing' => [
                ['name'=>'School',      'price'=>'$79', 'period'=>'/mo','desc'=>'Up to 500 students',              'highlight'=>false,'cta'=>'Start Free Trial','features'=>['500 student accounts','20 teacher accounts','Attendance & gradebook','Parent portal','Basic reports']],
                ['name'=>'College',     'price'=>'$199','period'=>'/mo','desc'=>'Up to 5,000 students',            'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['5,000 student accounts','Unlimited staff','Full LMS + online exams','Proctoring & analytics','API access','Priority support']],
                ['name'=>'University',  'price'=>'Custom','period'=>'','desc'=>'Unlimited campuses & departments', 'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Multi-campus management','Custom integrations','SCORM/xAPI compliance','White-label','Dedicated support']],
            ],
            'faq' => [
                ['q'=>'Does the platform support online exams with proctoring?','a'=>'Yes. Built-in exam module with auto-shuffled questions, tab-switch detection, webcam proctoring, and instant auto-grading.'],
                ['q'=>'Can parents track their child\'s progress?',            'a'=>'Yes. A dedicated parent portal shows attendance, grades, assignments, fee dues, and teacher communications in real time.'],
                ['q'=>'Is the platform SCORM/xAPI compliant?',                 'a'=>'Yes — import and deliver SCORM 1.2, SCORM 2004, and xAPI (Tin Can) content from any authoring tool.'],
                ['q'=>'Does it support live online classes?',                  'a'=>'Yes. Integrated video conferencing with Zoom/Google Meet/built-in classroom, recording, and auto attendance capture.'],
                ['q'=>'Can we manage fee collection and receipts?',            'a'=>'Yes. Online fee portal with payment gateway integration, automated receipts, overdue reminders, and scholarship management.'],
                ['q'=>'How do we migrate from our existing SIS?',              'a'=>'Free SIS migration from Fedena, Classter, Campus365, and any system that exports CSV/Excel. Typically done in 48 hours.'],
                ['q'=>'Can students access content on mobile?',                'a'=>'Yes. A fully responsive student portal and dedicated iOS/Android app for offline content access and assignment submissions.'],
                ['q'=>'What assessment types are supported?',                  'a'=>'MCQ, true/false, short answer, essay, file upload, coding problems, and AI-assisted auto-grading on applicable types.'],
            ],
        ],

        'restaurant' => [
            'trustedBy'    => ['Spice Garden Chain','Pizza Palace','The Grill House','Ocean Bistro','Saffron Fine Dining'],
            'steps'        => [
                ['num'=>'01','icon'=>'🍽️','title'=>'Set Up Menu',     'desc'=>'Digital menu builder with categories, modifiers, allergen tags, images, and dynamic pricing in under 30 minutes.'],
                ['num'=>'02','icon'=>'🪑','title'=>'Configure Tables', 'desc'=>'Map your floor plan, set capacities, configure reservation slots and walk-in queues for each shift.'],
                ['num'=>'03','icon'=>'📲','title'=>'Take Orders',      'desc'=>'QR code table ordering, waiter handheld POS, or kitchen display system — all in perfect sync in real time.'],
                ['num'=>'04','icon'=>'💳','title'=>'Bill & Analyse',   'desc'=>'Split bills, apply discounts, collect payment, and get end-of-day analytics across all your locations.'],
            ],
            'testimonials' => [
                ['init'=>'AC','name'=>'Amit Choudhary','role'=>'Owner (12 outlets)',  'company'=>'Spice Garden Chain', 'quote'=>'Table turnover increased 28% after we went digital. The kitchen display system eliminated 90% of order errors overnight.'],
                ['init'=>'RB','name'=>'Riya Bose',     'role'=>'Operations Manager', 'company'=>'The Grill House',     'quote'=>'Managing staff across 3 branches used to need 3 different systems. Now rosters, sales, and inventory are all in one place.'],
                ['init'=>'TN','name'=>'Thomas Nguyen', 'role'=>'Executive Chef',     'company'=>'Ocean Bistro',        'quote'=>'Recipe costing and ingredient consumption reports have cut our food cost by 12%. I see real-time stock levels from the kitchen.'],
            ],
            'pricing' => [
                ['name'=>'Single Branch', 'price'=>'$39', 'period'=>'/mo','desc'=>'One location, full features',   'highlight'=>false,'cta'=>'Start Free Trial','features'=>['1 location','POS + KDS','Table management','Basic analytics','Chat support']],
                ['name'=>'Multi-Branch',  'price'=>'$99', 'period'=>'/mo','desc'=>'2–10 locations',               'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['Up to 10 locations','Centralized menu','Cross-branch analytics','Delivery integration','Staff management','Priority support']],
                ['name'=>'Enterprise',    'price'=>'Custom','period'=>'', 'desc'=>'11+ locations & franchises',   'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Unlimited locations','Franchise management','Custom integrations','Dedicated team','White-label POS']],
            ],
            'faq' => [
                ['q'=>'Does it integrate with Zomato, Swiggy, or Uber Eats?','a'=>'Yes. Direct integration with Zomato, Swiggy, Uber Eats, and DoorDash. Orders flow into your KDS automatically — no manual entry.'],
                ['q'=>'Can we use it without an internet connection?',        'a'=>'Yes. The POS works in offline mode and syncs automatically when connectivity is restored. No orders are ever lost.'],
                ['q'=>'Does it support table reservations and waitlists?',    'a'=>'Yes — online booking widget, SMS/WhatsApp confirmations, automated reminders, and a real-time waitlist manager.'],
                ['q'=>'What payment methods are supported?',                  'a'=>'Cash, card, UPI, digital wallets (Paytm, PhonePe, GPay), and split billing across multiple payment methods per table.'],
                ['q'=>'Can we customize the KDS for different stations?',     'a'=>'Yes. Configure separate displays for grill, cold, bar, and dessert stations with custom routing rules per menu item.'],
                ['q'=>'How do we handle recipe costing and inventory?',       'a'=>'Link each menu item to ingredients. Stock deducts in real time and you get alerts when quantities drop below reorder point.'],
                ['q'=>'Is there a customer loyalty and rewards program?',     'a'=>'Yes. Built-in loyalty points, stamp cards, birthday offers, and push notifications via QR-based customer registration.'],
                ['q'=>'Can we manage staff schedules and tips?',              'a'=>'Yes — shift scheduling, clock-in/out, tip pooling rules, and individual staff sales performance reports included.'],
            ],
        ],

        'hotel' => [
            'trustedBy'    => ['Grand Meridian Hotels','Oceanic Resorts','The Heritage Inn','City Suites Group','Mountain Retreat'],
            'steps'        => [
                ['num'=>'01','icon'=>'🏨','title'=>'List Rooms & Rates','desc'=>'Add room types, rates, amenities, photos, and availability rules. Sync with OTAs automatically in one click.'],
                ['num'=>'02','icon'=>'📅','title'=>'Accept Bookings',   'desc'=>'Direct bookings via your website widget, phone reservations, and channel manager sync — all in one calendar.'],
                ['num'=>'03','icon'=>'🔑','title'=>'Smooth Check-in',   'desc'=>'Express check-in, room assignment, housekeeping requests, and in-stay services from a single front-desk view.'],
                ['num'=>'04','icon'=>'💰','title'=>'Maximize Revenue',  'desc'=>'Dynamic pricing, upsell triggers, revenue reports, and OTA analytics to grow your RevPAR month over month.'],
            ],
            'testimonials' => [
                ['init'=>'NK','name'=>'Nikhil Kapoor','role'=>'General Manager',    'company'=>'Grand Meridian Hotels','quote'=>'RevPAR grew 22% in 6 months after implementing dynamic pricing. OTA sync alone saves 3 hours of manual work every day.'],
                ['init'=>'SP','name'=>'Sofia Petrova','role'=>'Revenue Manager',    'company'=>'Oceanic Resorts',      'quote'=>'Forecasting is incredibly accurate. We\'ve reduced overbooking by 95% while maintaining 92% average occupancy year-round.'],
                ['init'=>'JL','name'=>'James Liu',    'role'=>'Front Office Manager','company'=>'City Suites Group',   'quote'=>'Check-in time dropped from 8 minutes to under 90 seconds. Guests love the express process and our Booking.com scores jumped.'],
            ],
            'pricing' => [
                ['name'=>'Boutique',    'price'=>'$59', 'period'=>'/mo','desc'=>'Small hotels up to 30 rooms',    'highlight'=>false,'cta'=>'Start Free Trial','features'=>['30 rooms','Online booking widget','Front desk POS','Basic reports','Email support']],
                ['name'=>'Business',    'price'=>'$149','period'=>'/mo','desc'=>'Hotels with 31–200 rooms',       'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['200 rooms','Channel manager (OTA sync)','Revenue management','Housekeeping app','F&B module','Priority support']],
                ['name'=>'Enterprise',  'price'=>'Custom','period'=>'','desc'=>'Hotel chains & large resorts',   'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Unlimited properties','Central reservation system','Custom integrations','Dedicated CSM','White-label app']],
            ],
            'faq' => [
                ['q'=>'Which OTAs does the channel manager support?',   'a'=>'Booking.com, Expedia, Airbnb, MakeMyTrip, Hotels.com, Agoda, and 80+ more via two-way API connection.'],
                ['q'=>'Can guests book directly from our website?',     'a'=>'Yes. A commission-free booking widget embeds on your site. Mobile-optimized and completes a booking in under 60 seconds.'],
                ['q'=>'How does dynamic pricing work?',                  'a'=>'Rules adjust rates based on occupancy, lead time, competitor rates, and local demand signals — maximizing revenue automatically.'],
                ['q'=>'Does it manage housekeeping and maintenance?',   'a'=>'Yes — mobile housekeeping app for room status, maintenance ticket tracking, and automatic room assignment on check-in.'],
                ['q'=>'Can we manage multiple properties?',             'a'=>'Yes. Enterprise includes a central dashboard with consolidated reporting and a unified guest database across all properties.'],
                ['q'=>'Does it integrate with accounting software?',    'a'=>'Direct integration with QuickBooks, Xero, Tally, and Zoho Books for automated revenue posting and reconciliation.'],
                ['q'=>'Is there a guest app or digital concierge?',     'a'=>'Yes — a branded guest app for check-in/out, room service orders, local recommendations, and direct messaging with staff.'],
                ['q'=>'How do we handle group and corporate bookings?', 'a'=>'Dedicated group booking flow with room block management, negotiated rate contracts, and corporate account billing.'],
            ],
        ],

        'finance' => [
            'trustedBy'    => ['Summit Capital','Meridian Finance','BlueStar Insurance','Apex Lending','Golden Trust Bank'],
            'steps'        => [
                ['num'=>'01','icon'=>'🏦','title'=>'Chart of Accounts',   'desc'=>'Import or build your chart of accounts with multi-currency, multi-entity, and segment-level reporting from day one.'],
                ['num'=>'02','icon'=>'💳','title'=>'Record Transactions', 'desc'=>'Automated bank feeds, supplier invoices, customer receipts, and journal entries with duplicate detection and approval.'],
                ['num'=>'03','icon'=>'📊','title'=>'Close the Books',     'desc'=>'Period-end close checklists, automated reconciliation, and one-click P&L, Balance Sheet, and Cash Flow statements.'],
                ['num'=>'04','icon'=>'🔍','title'=>'Audit & Comply',      'desc'=>'Full audit trail, user access logs, SOX-ready controls, and one-click regulatory report generation.'],
            ],
            'testimonials' => [
                ['init'=>'VR','name'=>'Vikram Rajan', 'role'=>'CFO',             'company'=>'Summit Capital Group','quote'=>'Month-end close went from 12 days to 3 days. The automated reconciliation catches discrepancies our manual process missed for years.'],
                ['init'=>'MT','name'=>'Monica Thakur','role'=>'Finance Controller','company'=>'BlueStar Insurance', 'quote'=>'SOX audit prep used to take 3 weeks. Now we generate the required reports in under an hour with a complete immutable audit trail.'],
                ['init'=>'CR','name'=>'Carlos Rodriguez','role'=>'Head of Treasury','company'=>'Apex Lending Corp','quote'=>'Multi-currency loan book management across 8 countries, all in one view. The FX exposure reports are exactly what our board needed.'],
            ],
            'pricing' => [
                ['name'=>'Startup',     'price'=>'$49', 'period'=>'/mo','desc'=>'Startups & small businesses',   'highlight'=>false,'cta'=>'Start Free Trial','features'=>['2 entities','Single currency','Basic P&L & Balance Sheet','5 users','Email support']],
                ['name'=>'Business',    'price'=>'$149','period'=>'/mo','desc'=>'Growing businesses & SMEs',     'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['10 entities','Multi-currency','Full financial statements','Automated bank feeds','20 users','Priority support']],
                ['name'=>'Enterprise',  'price'=>'Custom','period'=>'','desc'=>'Financial institutions & groups','highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Unlimited entities','SOX/IFRS compliance kit','Full audit trail','Custom integrations','Dedicated CSM']],
            ],
            'faq' => [
                ['q'=>'Is the platform SOX or IFRS compliant?',        'a'=>'Yes. Enterprise includes SOX-ready internal controls, IFRS/GAAP reporting standards, and a full immutable audit trail.'],
                ['q'=>'How does multi-currency accounting work?',       'a'=>'Real-time exchange rate feeds, functional currency reporting, and unrealized/realized FX gain-loss calculation per IFRS 21.'],
                ['q'=>'Can we automate bank reconciliation?',           'a'=>'Yes. Bank statement import (OFX, CSV, direct bank feed) with smart matching achieving 97%+ auto-reconciliation rates.'],
                ['q'=>'Does it handle inter-company transactions?',     'a'=>'Yes — automated inter-company eliminations, loan tracking, and consolidated group financials across unlimited entities.'],
                ['q'=>'What level of audit trail is maintained?',       'a'=>'Every transaction, edit, approval, and login is logged with user, timestamp, and IP. The log is immutable and exportable.'],
                ['q'=>'Can we integrate with our existing ERP?',        'a'=>'Pre-built connectors for SAP, Oracle NetSuite, Microsoft Dynamics, and Tally. Custom API integration on Enterprise.'],
                ['q'=>'Does it support accounts payable automation?',   'a'=>'Yes — invoice scanning (OCR), 3-way PO matching, approval workflows, and automated payment runs with bank integration.'],
                ['q'=>'How do we handle payroll and expense management?','a'=>'Full payroll module with statutory compliance, and integrated expense management with receipt capture and approvals.'],
            ],
        ],

        'hr' => [
            'trustedBy'    => ['TechCorp Global','BuildRight Group','RetailPro Chain','MediStaff Solutions','EduTalent Network'],
            'steps'        => [
                ['num'=>'01','icon'=>'📝','title'=>'Onboard Employees', 'desc'=>'Digital onboarding portal — offer letters, document collection, equipment requests, and induction scheduling automated.'],
                ['num'=>'02','icon'=>'⏱️','title'=>'Track Attendance',  'desc'=>'Biometric integration, mobile punch, geo-fencing, and shift scheduling with real-time dashboards for managers.'],
                ['num'=>'03','icon'=>'💸','title'=>'Run Payroll',        'desc'=>'One-click payroll processing with statutory deductions, bank transfers, and digital payslip distribution.'],
                ['num'=>'04','icon'=>'🌟','title'=>'Manage Performance', 'desc'=>'OKR/KPI setting, 360-degree reviews, continuous feedback, and calibration — all linked to compensation.'],
            ],
            'testimonials' => [
                ['init'=>'NK','name'=>'Neha Kapoor',   'role'=>'HR Director',          'company'=>'TechCorp Global (1,200 staff)', 'quote'=>'Payroll errors dropped to zero. What took 3 people 4 days now takes one person 2 hours. Our HR team finally focuses on people, not paperwork.'],
                ['init'=>'AS','name'=>'Arjun Singh',   'role'=>'Head of Talent',       'company'=>'BuildRight Group',             'quote'=>'Time-to-hire dropped from 47 days to 18 days. The ATS integrated seamlessly and recruiter productivity doubled overnight.'],
                ['init'=>'PW','name'=>'Patricia Wong', 'role'=>'VP People & Culture',  'company'=>'RetailPro Chain (3,500 staff)','quote'=>'Managing 3,500 employees across 42 stores seemed impossible. Now performance reviews, shifts, and payroll are effortless.'],
            ],
            'pricing' => [
                ['name'=>'Startup',     'price'=>'$4',  'period'=>'/emp/mo','desc'=>'Up to 50 employees',     'highlight'=>false,'cta'=>'Start Free Trial','features'=>['Core HR database','Leave management','Basic payroll','Employee self-service','Email support']],
                ['name'=>'Growth',      'price'=>'$8',  'period'=>'/emp/mo','desc'=>'51–500 employees',       'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['Full HRMS suite','Attendance + biometric','Full payroll + compliance','Performance management','Analytics','Priority support']],
                ['name'=>'Enterprise',  'price'=>'Custom','period'=>'',     'desc'=>'500+ employees',         'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Unlimited employees','Multi-country payroll','HRIS integrations','Dedicated CHRO advisor','Custom workflows']],
            ],
            'faq' => [
                ['q'=>'Which payroll compliances are supported?',         'a'=>'India (PF, ESI, TDS, PT, LWF), UAE (WPS, gratuity), US (federal/state taxes, 401k), UK (PAYE, NI), and 40+ countries.'],
                ['q'=>'Does it integrate with biometric devices?',        'a'=>'Yes — ZKTeco, Essl, Suprema, and Hikvision. Mobile punch with GPS and face recognition also supported.'],
                ['q'=>'Can employees access their own information?',      'a'=>'Yes. The Employee Self-Service portal lets staff view payslips, apply for leave, and track approvals on mobile.'],
                ['q'=>'How does the performance review cycle work?',      'a'=>'Fully configurable — objectives, mid-cycle check-ins, annual reviews, 360-degree feedback, and calibration sessions.'],
                ['q'=>'Is employee data GDPR compliant?',                 'a'=>'Yes — role-based data access, field-level encryption, right-to-erasure support, and GDPR/PDPA compliance included.'],
                ['q'=>'Can we run payroll for multiple countries?',       'a'=>'Yes. Multi-country payroll with country-specific statutory rules, currency support, and local compliance on Enterprise.'],
                ['q'=>'How does leave management work?',                  'a'=>'Configurable leave types, accrual rules, encashment policies, approval chains, and calendar integration for managers.'],
                ['q'=>'Is there an applicant tracking system (ATS)?',     'a'=>'Yes — full ATS with job posting, career page, applicant pipeline, interview scheduling, and offer letter generation.'],
            ],
        ],

        'crm' => [
            'trustedBy'    => ['Velocity Sales','Peak Growth Partners','Nexus Corp','Horizon Consulting','Delta Revenue'],
            'steps'        => [
                ['num'=>'01','icon'=>'📥','title'=>'Capture Leads',    'desc'=>'Website forms, LinkedIn import, email parsing, and business card scan feed leads automatically into your pipeline.'],
                ['num'=>'02','icon'=>'🎯','title'=>'Qualify & Assign', 'desc'=>'Lead scoring ranks prospects by conversion likelihood. Auto-assign to the right rep based on territory and capacity.'],
                ['num'=>'03','icon'=>'🔄','title'=>'Work the Pipeline','desc'=>'Visual kanban pipeline, activity tracking, email sequences, and call logging keep every deal moving forward.'],
                ['num'=>'04','icon'=>'🏆','title'=>'Close & Retain',   'desc'=>'E-signature, proposal builder, and post-sale onboarding flows turn closed deals into long-term loyal customers.'],
            ],
            'testimonials' => [
                ['init'=>'DK','name'=>'Daniel Kim',       'role'=>'VP Sales',      'company'=>'Velocity Sales Inc',    'quote'=>'Pipeline visibility is night and day. Our win rate improved 31% in the first quarter after switching. Best decision we made all year.'],
                ['init'=>'RA','name'=>'Rebecca Ashworth', 'role'=>'Sales Director', 'company'=>'Peak Growth Partners', 'quote'=>'The lead scoring model is scarily accurate. Our reps now spend 80% of their time on deals that actually close, not cold leads.'],
                ['init'=>'OC','name'=>'Oluwaseun Coker',  'role'=>'CEO',            'company'=>'Nexus Consulting',     'quote'=>'We went from $1.2M to $3.8M ARR in 18 months. The CRM made sure nothing fell through the cracks while we scaled fast.'],
            ],
            'pricing' => [
                ['name'=>'Starter',      'price'=>'$25', 'period'=>'/user/mo','desc'=>'Small sales teams',         'highlight'=>false,'cta'=>'Start Free Trial','features'=>['Contact & deal management','Email integration','Basic pipeline','Activity tracking','5 users max']],
                ['name'=>'Professional', 'price'=>'$65', 'period'=>'/user/mo','desc'=>'Growing revenue teams',     'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['Advanced pipeline','Lead scoring','Email sequences','Revenue forecasting','Reporting suite','API access']],
                ['name'=>'Enterprise',   'price'=>'Custom','period'=>'',      'desc'=>'Large sales organizations', 'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Custom CRM workflows','Territory management','Advanced AI scoring','Dedicated CSM','Custom integrations']],
            ],
            'faq' => [
                ['q'=>'Does it integrate with Gmail / Outlook?',          'a'=>'Yes — two-way sync with Gmail and Outlook. Emails auto-log against contacts and deals. Calendar sync included.'],
                ['q'=>'How does lead scoring work?',                      'a'=>'Scores 0–100 using engagement signals (email opens, page visits, form fills), demographic fit, and deal history. Fully customizable.'],
                ['q'=>'Can we build custom sales pipelines?',             'a'=>'Yes — unlimited pipelines with custom stages, win probability, and required fields per stage for different products/markets.'],
                ['q'=>'Does it have revenue forecasting?',                'a'=>'Deal-level and pipeline-level forecasting using weighted probability, historical win rates, and rep performance benchmarks.'],
                ['q'=>'What happens to our data if we cancel?',           'a'=>'Export all data in CSV/Excel at any time. On cancellation, you have 90 days to download everything before secure deletion.'],
                ['q'=>'Does it integrate with marketing automation?',     'a'=>'Yes — native integrations with HubSpot, Mailchimp, ActiveCampaign, and Marketo for lead nurture-to-sales handoff.'],
                ['q'=>'Is there a mobile app for sales reps?',            'a'=>'Yes. iOS and Android app with offline access, business card scan, voice note logging, and GPS check-in for field teams.'],
                ['q'=>'Can we manage customer support tickets in the CRM?','a'=>'Yes — integrated helpdesk module links support tickets to customer records, giving sales full customer health context.'],
            ],
        ],

        'inventory' => [
            'trustedBy'    => ['LogiPro Warehousing','FastTrack Retail','BuildMate Supplies','AgroStore Network','TechParts Direct'],
            'steps'        => [
                ['num'=>'01','icon'=>'📦','title'=>'Set Up Catalogue',    'desc'=>'Add SKUs with barcodes, variants, units of measure, images, and supplier details. Bulk import from Excel in minutes.'],
                ['num'=>'02','icon'=>'🏭','title'=>'Configure Warehouses','desc'=>'Map rack locations, set bin capacities, define putaway and pick strategies for each warehouse or zone.'],
                ['num'=>'03','icon'=>'🔄','title'=>'Manage Stock Moves',  'desc'=>'Receive, transfer, pick, and dispatch with barcode scanning. Real-time stock levels update across locations instantly.'],
                ['num'=>'04','icon'=>'📈','title'=>'Optimize & Automate', 'desc'=>'Reorder alerts, auto-purchase orders, demand forecasting, and ABC analysis eliminate stockouts and overstock.'],
            ],
            'testimonials' => [
                ['init'=>'KP','name'=>'Karan Patel',  'role'=>'Supply Chain Director','company'=>'LogiPro Warehousing', 'quote'=>'Inventory accuracy jumped from 87% to 99.6% after implementing the WMS. Pick errors nearly disappeared and throughput doubled.'],
                ['init'=>'YS','name'=>'Yuki Sato',    'role'=>'Operations Manager',   'company'=>'TechParts Direct',    'quote'=>'Stockout incidents dropped 94%. The demand forecasting is remarkably accurate — we cut safety stock requirements by 30%.'],
                ['init'=>'FM','name'=>'Fatima Malik', 'role'=>'Procurement Head',     'company'=>'BuildMate Supplies',  'quote'=>'Automatic PO generation based on reorder points saves my team 20 hours a week. Supplier lead time tracking is a game changer.'],
            ],
            'pricing' => [
                ['name'=>'Small Biz',  'price'=>'$49', 'period'=>'/mo','desc'=>'Up to 1,000 SKUs',         'highlight'=>false,'cta'=>'Start Free Trial','features'=>['1 warehouse','1,000 SKUs','Barcode scanning','Basic reports','Email support']],
                ['name'=>'Mid-Market', 'price'=>'$149','period'=>'/mo','desc'=>'Multi-warehouse operations','highlight'=>true, 'cta'=>'Start Free Trial','features'=>['25 warehouses','Unlimited SKUs','Demand forecasting','Auto-reorder','3PL integration','Priority support']],
                ['name'=>'Enterprise', 'price'=>'Custom','period'=>'', 'desc'=>'Complex supply chains',    'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Unlimited warehouses','Multi-company','ERP integration','Custom WMS logic','Dedicated support']],
            ],
            'faq' => [
                ['q'=>'Which barcode formats and scanners work?',          'a'=>'EAN-13, EAN-8, UPC-A, Code-128, QR Code, DataMatrix. Works with any USB, Bluetooth, or mobile camera scanner.'],
                ['q'=>'Can we do cycle counts without stopping operations?','a'=>'Yes — partial cycle counting by zone or category during normal operations with automatic variance reporting.'],
                ['q'=>'How does demand forecasting work?',                  'a'=>'Statistical forecasting using historical velocity, seasonality, and trend analysis. Accuracy improves each month.'],
                ['q'=>'Can it integrate with our ERP (SAP/Oracle)?',       'a'=>'Yes. Pre-built connectors for SAP, Oracle, Microsoft Dynamics, and Tally. Custom REST API for other systems.'],
                ['q'=>'Does it handle FIFO, FEFO, and LIFO costing?',      'a'=>'Yes — full support for FIFO, FEFO (perishables/pharma), LIFO, and weighted average costing with lot tracking.'],
                ['q'=>'What happens during a network outage?',             'a'=>'Mobile scanners cache transactions locally and sync automatically when connectivity resumes. No data loss.'],
                ['q'=>'Does it support drop-shipping and 3PL?',            'a'=>'Yes — 3PL warehouse integration, drop-ship order routing, and consignment stock tracking on all paid plans.'],
                ['q'=>'How do we manage supplier contracts and pricing?',  'a'=>'Supplier portal with contract management, tiered pricing, preferred vendor rules, and performance scorecards.'],
            ],
        ],

        ]; // end $map

        $default = [
            'trustedBy'    => ['TechCorp Inc','GlobalSystems','InnovateCo','DataPro Solutions','NexGen Enterprises'],
            'steps'        => [
                ['num'=>'01','icon'=>'⚙️','title'=>'Quick Setup',      'desc'=>'Get up and running in minutes. Import your data, configure settings, and invite your team.'],
                ['num'=>'02','icon'=>'🎯','title'=>'Start Managing',   'desc'=>'All your operations in one intuitive place. Workflows your team will actually adopt from day one.'],
                ['num'=>'03','icon'=>'📊','title'=>'Track & Analyse',  'desc'=>'Real-time dashboards and reports give you the visibility to make confident decisions fast.'],
                ['num'=>'04','icon'=>'🚀','title'=>'Scale & Grow',     'desc'=>'As your business grows the platform scales with you — add users, modules, and integrations any time.'],
            ],
            'testimonials' => [
                ['init'=>'JD','name'=>'James Davis',  'role'=>'CEO',               'company'=>'TechCorp Inc',      'quote'=>'This platform transformed how our team operates. Processes that took days now happen automatically. ROI was visible within the first month.'],
                ['init'=>'AL','name'=>'Amara Lawson', 'role'=>'Operations Director','company'=>'GlobalSystems Ltd', 'quote'=>'We evaluated 8 platforms and this was the only one that handled our specific workflows without expensive customization. Worth every dollar.'],
                ['init'=>'MN','name'=>'Michael Nguyen','role'=>'Head of Technology','company'=>'InnovateCo',       'quote'=>'The API is solid, the documentation is excellent, and the support team actually knows the product. Rare to find all three together.'],
            ],
            'pricing' => [
                ['name'=>'Starter',     'price'=>'$29', 'period'=>'/mo','desc'=>'Small teams getting started','highlight'=>false,'cta'=>'Start Free Trial','features'=>['5 user accounts','Core modules','Basic reports','Email support']],
                ['name'=>'Professional','price'=>'$99', 'period'=>'/mo','desc'=>'Growing businesses',         'highlight'=>true, 'cta'=>'Start Free Trial','features'=>['25 user accounts','All modules','Advanced analytics','API access','Priority support']],
                ['name'=>'Enterprise',  'price'=>'Custom','period'=>'','desc'=>'Large organizations',         'highlight'=>false,'cta'=>'Contact Sales',   'features'=>['Unlimited users','Custom modules','SLA guarantee','Dedicated CSM','On-premise option']],
            ],
            'faq' => [
                ['q'=>'How long does it take to get started?',            'a'=>'Most teams are fully operational within 1–2 business days. Our onboarding team guides you through setup and training.'],
                ['q'=>'Can we import data from our existing system?',     'a'=>'Yes — CSV/Excel import for all major data types. For complex migrations our team provides full migration assistance free.'],
                ['q'=>'Is there a free trial?',                           'a'=>'Yes. Every plan starts with a 14-day free trial — no credit card required. Full access to all features during the trial.'],
                ['q'=>'How is data security handled?',                    'a'=>'Data is encrypted at rest (AES-256) and in transit (TLS 1.3). SOC 2 Type II certified with daily encrypted backups.'],
                ['q'=>'Can we add or remove users at any time?',          'a'=>'Yes. Add or remove users instantly. Billing adjusts automatically — you only pay for active users each month.'],
                ['q'=>'What support options are available?',              'a'=>'Email on all plans, live chat on Professional, and a dedicated customer success manager on Enterprise.'],
                ['q'=>'Is there an API for custom integrations?',         'a'=>'Yes — full REST API with comprehensive documentation, sandbox environment, and webhook support on Professional and above.'],
                ['q'=>'Can we white-label the platform for our clients?', 'a'=>'Yes — custom domain, logo, colors, and email templates for white-label deployment available on the Enterprise plan.'],
            ],
        ];

        return $map[$domain] ?? $default;
    }

    private function shellLandingView(string $appName, array $entities): string
    {
        $profile = $this->domainProfile($appName, $entities);
        $extras  = $this->domainExtras($profile['domain'], $appName);

        $brand      = $profile['brand'];
        $brandDk    = $profile['brandDk'];
        $gradient   = $profile['gradient'];
        $heroTag    = $profile['heroTag'];
        $heroSub    = $profile['heroSub'];
        $userRole   = $profile['userRole'];
        $featSuffix = $profile['featSuffix'];
        $appInitial = strtoupper(substr($appName, 0, 1));
        $year       = date('Y');

        // ── PHP array literal builders ──────────────────────────────────────────

        $metricsPhp = "[\n";
        foreach ($profile['metrics'] as $m) {
            $metricsPhp .= "        ['num'=>'" . addslashes($m['num']) . "','lbl'=>'" . addslashes($m['lbl']) . "'],\n";
        }
        $metricsPhp .= "    ]";

        $featuresPhp = "[\n";
        foreach ($entities as $e) {
            $icon  = $this->entityIcon($e['name']);
            $title = addslashes(ucwords(str_replace('_', ' ', $e['name'])) . ' Management');
            $desc  = addslashes("Manage all {$e['name']} records {$featSuffix}");
            $featuresPhp .= "        ['icon'=>'{$icon}','title'=>'{$title}','desc'=>'{$desc}'],\n";
        }
        if (empty($entities)) {
            $featuresPhp .= "        ['icon'=>'⚡','title'=>'Fast & Reliable','desc'=>'Built for performance and scale {$featSuffix}'],\n";
        }
        $featuresPhp .= "    ]";

        $trustedPhp = "['" . implode("','", array_map('addslashes', $extras['trustedBy'])) . "']";

        $stepsPhp = "[\n";
        foreach ($extras['steps'] as $s) {
            $stepsPhp .= "        ['num'=>'" . addslashes($s['num']) . "','icon'=>'" . addslashes($s['icon']) . "','title'=>'" . addslashes($s['title']) . "','desc'=>'" . addslashes($s['desc']) . "'],\n";
        }
        $stepsPhp .= "    ]";

        $testimonialsPhp = "[\n";
        foreach ($extras['testimonials'] as $t) {
            $testimonialsPhp .= "        ['init'=>'" . addslashes($t['init']) . "','name'=>'" . addslashes($t['name']) . "','role'=>'" . addslashes($t['role']) . "','company'=>'" . addslashes($t['company']) . "','quote'=>'" . addslashes($t['quote']) . "'],\n";
        }
        $testimonialsPhp .= "    ]";

        $pricingPhp = "[\n";
        foreach ($extras['pricing'] as $p) {
            $hl       = $p['highlight'] ? 'true' : 'false';
            $featsStr = "['" . implode("','", array_map('addslashes', $p['features'])) . "']";
            $pricingPhp .= "        ['name'=>'" . addslashes($p['name']) . "','price'=>'" . addslashes($p['price']) . "','period'=>'" . addslashes($p['period']) . "','desc'=>'" . addslashes($p['desc']) . "','highlight'=>{$hl},'cta'=>'" . addslashes($p['cta']) . "','features'=>{$featsStr}],\n";
        }
        $pricingPhp .= "    ]";

        $faqPhp = "[\n";
        foreach ($extras['faq'] as $f) {
            $faqPhp .= "        ['q'=>'" . addslashes($f['q']) . "','a'=>'" . addslashes($f['a']) . "'],\n";
        }
        $faqPhp .= "    ]";

        return <<<BLADE
{{--
╔══════════════════════════════════════════════════════════════════╗
║  Landing Page — {$appName}                                       ║
║  ──────────────────────────────────────────────────────────────  ║
║  ALL editable content lives in the @php block below.            ║
║  Change text, metrics, steps, testimonials, pricing, FAQ,        ║
║  contact info, footer — no AI needed, just edit and refresh.    ║
╚══════════════════════════════════════════════════════════════════╝
--}}
@php
// ┌──────────────────────────────────────────────────────────────┐
// │  EDITABLE CONTENT  —  no AI, no rebuild, just edit + refresh │
// └──────────────────────────────────────────────────────────────┘
\$page = [

  // ── Brand & Colors ─────────────────────────────────────────────
  'appName'    => '{$appName}',
  'appInitial' => '{$appInitial}',
  'tagline'    => 'Smarter. Faster. Built for your team.',
  'brand'      => '{$brand}',
  'brandDk'    => '{$brandDk}',
  'gradient'   => 'linear-gradient(160deg,{$gradient})',

  // ── Nav ────────────────────────────────────────────────────────
  'navLinks' => [
    ['label'=>'Features',     'href'=>'#features'],
    ['label'=>'How It Works', 'href'=>'#how-it-works'],
    ['label'=>'Pricing',      'href'=>'#pricing'],
    ['label'=>'FAQ',          'href'=>'#faq'],
    ['label'=>'Contact',      'href'=>'#contact'],
  ],
  'navCta'     => 'Sign In',
  'navCtaHref' => '/login',

  // ── Hero ───────────────────────────────────────────────────────
  'heroEyebrow'    => '{$heroTag}',
  'heroTitle'      => '{$appName}',
  'heroSub'        => '{$heroSub}',
  'heroCta'        => 'Start Free Trial',
  'heroCtaHref'    => '/register',
  'heroCtaAlt'     => 'Watch Demo',
  'heroCtaAltHref' => '#how-it-works',

  // ── Metrics strip ──────────────────────────────────────────────
  'metrics' => {$metricsPhp},

  // ── Trusted By ─────────────────────────────────────────────────
  'trustedBy'     => {$trustedPhp},
  'trustedByLabel'=> 'Trusted by leading organizations worldwide',

  // ── Features ───────────────────────────────────────────────────
  'featuresTitle' => 'Everything you need, nothing you don\'t',
  'featuresSub'   => 'A complete platform built for {$userRole}s and their teams.',
  'features'      => {$featuresPhp},

  // ── How It Works ───────────────────────────────────────────────
  'stepsTitle' => 'Up and running in four simple steps',
  'stepsSub'   => 'From setup to fully operational in less time than you think.',
  'steps'      => {$stepsPhp},

  // ── Testimonials ───────────────────────────────────────────────
  'testimonialsTitle' => 'Trusted by industry leaders',
  'testimonialsSub'   => 'See what real customers say about {$appName}.',
  'testimonials'      => {$testimonialsPhp},

  // ── Pricing ────────────────────────────────────────────────────
  'pricingTitle'   => 'Simple, transparent pricing',
  'pricingSub'     => 'All plans include a 14-day free trial — no credit card required.',
  'pricingBadge'   => 'Most Popular',
  'pricing'        => {$pricingPhp},

  // ── FAQ ────────────────────────────────────────────────────────
  'faqTitle' => 'Frequently Asked Questions',
  'faqSub'   => 'Still have questions? Contact our team — we reply within 24 hours.',
  'faq'      => {$faqPhp},

  // ── Contact ────────────────────────────────────────────────────
  'contactTitle'   => 'Get in touch',
  'contactSub'     => 'Have a question or want to schedule a demo? We\'d love to hear from you.',
  'contactEmail'   => 'hello@{$appName}.com',
  'contactPhone'   => '+1 (555) 000-0000',
  'contactAddress' => '123 Business Ave, Suite 100, New York, NY 10001',
  'contactHours'   => 'Mon–Fri, 9 AM – 6 PM EST',

  // ── CTA Banner ─────────────────────────────────────────────────
  'ctaTitle'   => 'Ready to transform how your team works?',
  'ctaSub'     => 'Join thousands of {$userRole}s already using {$appName}.',
  'ctaBtn'     => 'Start Your Free Trial',
  'ctaBtnHref' => '/register',
  'ctaBtnAlt'  => 'Schedule a Demo',
  'ctaBtnAltHref' => '#contact',

  // ── Footer ─────────────────────────────────────────────────────
  'footerTagline' => 'The smartest way to manage your {$appName} operations.',
  'footerCols' => [
    ['title'=>'Product', 'links'=>[
      ['label'=>'Features',    'href'=>'#features'],
      ['label'=>'Pricing',     'href'=>'#pricing'],
      ['label'=>'Integrations','href'=>'/integrations'],
      ['label'=>'Changelog',   'href'=>'/changelog'],
      ['label'=>'Roadmap',     'href'=>'/roadmap'],
    ]],
    ['title'=>'Company', 'links'=>[
      ['label'=>'About Us',  'href'=>'/about'],
      ['label'=>'Blog',      'href'=>'/blog'],
      ['label'=>'Careers',   'href'=>'/careers'],
      ['label'=>'Press Kit', 'href'=>'/press'],
      ['label'=>'Partners',  'href'=>'/partners'],
    ]],
    ['title'=>'Support', 'links'=>[
      ['label'=>'Documentation', 'href'=>'/docs'],
      ['label'=>'Help Centre',   'href'=>'/help'],
      ['label'=>'Status Page',   'href'=>'/status'],
      ['label'=>'Contact Us',    'href'=>'#contact'],
      ['label'=>'Community',     'href'=>'/community'],
    ]],
    ['title'=>'Legal', 'links'=>[
      ['label'=>'Privacy Policy',   'href'=>'/privacy'],
      ['label'=>'Terms of Service', 'href'=>'/terms'],
      ['label'=>'Cookie Policy',    'href'=>'/cookies'],
      ['label'=>'Security',         'href'=>'/security'],
      ['label'=>'GDPR',             'href'=>'/gdpr'],
    ]],
  ],
  'footerSocial' => [
    ['icon'=>'𝕏',  'href'=>'https://twitter.com'],
    ['icon'=>'in', 'href'=>'https://linkedin.com'],
    ['icon'=>'gh', 'href'=>'https://github.com'],
    ['icon'=>'yt', 'href'=>'https://youtube.com'],
  ],
  'footerCopy' => '© {$year} {$appName}. All rights reserved.',
];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="{{ \$page['heroSub'] }}">
<title>{{ \$page['appName'] }} — {{ \$page['tagline'] }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--brand:{{ \$page['brand'] }};--brand-dk:{{ \$page['brandDk'] }};--bg:#09090f;--surface:rgba(255,255,255,.04);--border:rgba(255,255,255,.08);--text:#e2e8f0;--muted:#64748b;--subtle:#94a3b8}
html{scroll-behavior:smooth}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit;transition:.15s}
img{max-width:100%;display:block}
.container{max-width:1200px;margin:0 auto;padding:0 5%}
.section{padding:96px 5%}
.section-center{text-align:center;max-width:680px;margin:0 auto 64px}
.eyebrow{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--brand);margin-bottom:14px}
h2.sec-title{font-size:clamp(1.9rem,4vw,2.8rem);font-weight:800;color:#f1f5f9;line-height:1.15;margin-bottom:14px}
p.sec-sub{font-size:16px;color:var(--muted);line-height:1.7}
.btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand-dk));color:#fff;padding:14px 36px;border-radius:12px;font-weight:700;font-size:15px;display:inline-block;transition:.2s;box-shadow:0 4px 24px rgba(0,0,0,.4);cursor:pointer;border:none}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(0,0,0,.5);color:#fff}
.btn-ghost{border:1px solid rgba(255,255,255,.18);color:var(--text);padding:14px 36px;border-radius:12px;font-weight:600;font-size:15px;display:inline-block;transition:.2s;backdrop-filter:blur(6px)}
.btn-ghost:hover{background:rgba(255,255,255,.07);color:#fff}
.btn-sm{padding:10px 24px;font-size:13px;border-radius:10px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:28px;transition:.25s}
.card:hover{background:rgba(255,255,255,.07);border-color:var(--brand);transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.3)}
.metric-val{font-size:2.4rem;font-weight:900;background:linear-gradient(135deg,var(--brand),#f8fafc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
/* Nav */
nav a{color:var(--subtle);font-size:14px;font-weight:500}
nav a:hover{color:var(--text)}
/* Trusted logos */
.trusted-logo{border:1px solid var(--border);border-radius:10px;padding:10px 22px;font-size:12px;font-weight:700;color:var(--muted);letter-spacing:.04em;white-space:nowrap}
/* Steps */
.step-num{font-size:3rem;font-weight:900;color:var(--brand);opacity:.25;line-height:1;margin-bottom:6px}
.step-connector{height:2px;background:linear-gradient(90deg,var(--brand),transparent);flex:1;margin:0 12px;opacity:.3;margin-top:-30px}
/* Testimonials */
.testi-avatar{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--brand-dk));display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#fff;flex-shrink:0}
.stars{color:#f59e0b;font-size:14px;letter-spacing:1px;margin-bottom:14px}
/* Pricing */
.pricing-card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:32px;display:flex;flex-direction:column;transition:.25s}
.pricing-card.highlighted{border-color:var(--brand);background:rgba(255,255,255,.07);position:relative}
.pricing-card:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.35)}
.pricing-badge{position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--brand);color:#fff;font-size:11px;font-weight:800;padding:4px 16px;border-radius:20px;white-space:nowrap}
.price-amount{font-size:3rem;font-weight:900;color:#f1f5f9;line-height:1}
.price-period{font-size:14px;color:var(--muted);margin-left:4px}
.price-feat{display:flex;align-items:flex-start;gap:10px;font-size:13.5px;color:var(--subtle);margin-bottom:10px}
.price-feat::before{content:'✓';color:var(--brand);font-weight:900;flex-shrink:0;margin-top:1px}
/* FAQ */
details{background:var(--surface);border:1px solid var(--border);border-radius:14px;margin-bottom:10px;overflow:hidden;transition:.2s}
details:hover{border-color:rgba(255,255,255,.14)}
details[open]{border-color:var(--brand)}
summary{padding:20px 24px;cursor:pointer;font-size:15px;font-weight:600;color:#f1f5f9;display:flex;justify-content:space-between;align-items:center;list-style:none;user-select:none}
summary::-webkit-details-marker{display:none}
summary::after{content:'+';font-size:20px;font-weight:400;color:var(--subtle);transition:.2s;flex-shrink:0}
details[open] summary::after{content:'−';color:var(--brand)}
details[open] summary{color:var(--brand)}
.faq-body{padding:0 24px 20px;font-size:14.5px;color:var(--muted);line-height:1.75;border-top:1px solid var(--border)}
/* Contact form */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.form-label{font-size:13px;font-weight:600;color:var(--subtle)}
.form-input{background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:10px;padding:12px 16px;color:var(--text);font-size:14px;width:100%;outline:none;transition:.2s;font-family:inherit}
.form-input:focus{border-color:var(--brand);background:rgba(255,255,255,.09)}
textarea.form-input{resize:vertical;min-height:130px}
.alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#34d399;padding:14px 18px;border-radius:10px;font-size:14px;margin-bottom:20px}
/* Footer */
.footer-col h4{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#f1f5f9;margin-bottom:18px}
.footer-col a{font-size:13px;color:var(--muted);display:block;margin-bottom:10px}
.footer-col a:hover{color:var(--text)}
.social-btn{width:36px;height:36px;border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:var(--subtle);transition:.2s}
.social-btn:hover{border-color:var(--brand);color:var(--brand)}
/* CTA gradient */
.cta-section{background:linear-gradient(135deg,rgba(0,0,0,0) 0%,rgba(255,255,255,.02) 100%);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
/* Dividers */
.section-divider{border:none;border-top:1px solid var(--border);margin:0}
/* Responsive */
@media(max-width:768px){
  .form-row{grid-template-columns:1fr}
  .hero-btns{flex-direction:column;align-items:center}
  nav .nav-links{display:none}
}
</style>
</head>
<body>

{{-- ══ 1. STICKY NAV ══════════════════════════════════════════════ --}}
<nav style="position:sticky;top:0;z-index:200;background:rgba(9,9,15,.9);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-bottom:1px solid var(--border);padding:0 5%">
  <div class="container" style="display:flex;align-items:center;height:66px;gap:36px;max-width:1200px;margin:0 auto;padding:0">
    <a href="/" style="display:flex;align-items:center;gap:10px;font-weight:900;font-size:17px;color:#f8fafc;flex-shrink:0">
      <div style="width:34px;height:34px;background:var(--brand);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff">{{ \$page['appInitial'] }}</div>
      {{ \$page['appName'] }}
    </a>
    <div class="nav-links" style="display:flex;gap:28px;margin-left:auto;align-items:center">
      @foreach(\$page['navLinks'] as \$link)
        <a href="{{ \$link['href'] }}">{{ \$link['label'] }}</a>
      @endforeach
      <a href="{{ \$page['navCtaHref'] }}" class="btn-primary btn-sm" style="margin-left:8px">{{ \$page['navCta'] }}</a>
    </div>
  </div>
</nav>

{{-- ══ 2. HERO ════════════════════════════════════════════════════ --}}
<section style="background:{{ \$page['gradient'] }};padding:110px 5% 90px;text-align:center;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 80% 55% at 50% -10%,rgba(14,165,233,.1),transparent);pointer-events:none"></div>
  <div style="position:absolute;top:15%;left:5%;width:320px;height:320px;background:var(--brand);opacity:.04;border-radius:50%;filter:blur(80px);pointer-events:none"></div>
  <div style="position:absolute;bottom:10%;right:5%;width:400px;height:400px;background:var(--brand-dk);opacity:.05;border-radius:50%;filter:blur(100px);pointer-events:none"></div>
  <div style="max-width:860px;margin:0 auto;position:relative">
    <div class="eyebrow">{{ \$page['heroEyebrow'] }}</div>
    <h1 style="font-size:clamp(2.6rem,7vw,4.4rem);font-weight:900;line-height:1.06;color:#f8fafc;margin-bottom:22px;letter-spacing:-.02em">
      {{ \$page['heroTitle'] }}
    </h1>
    <p style="font-size:clamp(1.05rem,2.5vw,1.22rem);color:var(--subtle);line-height:1.72;max-width:680px;margin:0 auto 44px">
      {{ \$page['heroSub'] }}
    </p>
    <div class="hero-btns" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a href="{{ \$page['heroCtaHref'] }}" class="btn-primary">{{ \$page['heroCta'] }}</a>
      <a href="{{ \$page['heroCtaAltHref'] }}" class="btn-ghost">{{ \$page['heroCtaAlt'] }}</a>
    </div>
    <p style="margin-top:18px;font-size:12.5px;color:var(--muted)">No credit card required · 14-day free trial · Cancel anytime</p>
  </div>
</section>

{{-- ══ 3. METRICS STRIP ══════════════════════════════════════════ --}}
<div style="border-top:1px solid var(--border);border-bottom:1px solid var(--border);background:rgba(255,255,255,.025);padding:44px 5%">
  <div class="container" style="display:flex;justify-content:center;gap:clamp(32px,6vw,80px);flex-wrap:wrap;text-align:center">
    @foreach(\$page['metrics'] as \$m)
      <div>
        <div class="metric-val">{{ \$m['num'] }}</div>
        <div style="font-size:13px;color:var(--muted);margin-top:5px">{{ \$m['lbl'] }}</div>
      </div>
    @endforeach
  </div>
</div>

{{-- ══ 4. TRUSTED BY ═════════════════════════════════════════════ --}}
<div style="padding:40px 5%;text-align:center;border-bottom:1px solid var(--border)">
  <p style="font-size:12px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:22px">{{ \$page['trustedByLabel'] }}</p>
  <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:10px">
    @foreach(\$page['trustedBy'] as \$org)
      <span class="trusted-logo">{{ \$org }}</span>
    @endforeach
  </div>
</div>

{{-- ══ 5. FEATURES ═══════════════════════════════════════════════ --}}
<section id="features" class="section">
  <div class="section-center">
    <div class="eyebrow">Features</div>
    <h2 class="sec-title">{{ \$page['featuresTitle'] }}</h2>
    <p class="sec-sub">{{ \$page['featuresSub'] }}</p>
  </div>
  <div class="container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:20px">
    @foreach(\$page['features'] as \$feat)
      <div class="card">
        <div style="font-size:2.1rem;margin-bottom:14px">{{ \$feat['icon'] }}</div>
        <h3 style="font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:8px">{{ \$feat['title'] }}</h3>
        <p style="font-size:13.5px;color:var(--muted);line-height:1.65">{{ \$feat['desc'] }}</p>
      </div>
    @endforeach
  </div>
</section>

<hr class="section-divider">

{{-- ══ 6. HOW IT WORKS ══════════════════════════════════════════ --}}
<section id="how-it-works" class="section">
  <div class="section-center">
    <div class="eyebrow">How It Works</div>
    <h2 class="sec-title">{{ \$page['stepsTitle'] }}</h2>
    <p class="sec-sub">{{ \$page['stepsSub'] }}</p>
  </div>
  <div class="container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px">
    @foreach(\$page['steps'] as \$step)
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:28px 26px;position:relative">
        <div class="step-num">{{ \$step['num'] }}</div>
        <div style="font-size:2rem;margin-bottom:12px">{{ \$step['icon'] }}</div>
        <h3 style="font-size:16px;font-weight:700;color:#f1f5f9;margin-bottom:9px">{{ \$step['title'] }}</h3>
        <p style="font-size:13.5px;color:var(--muted);line-height:1.65">{{ \$step['desc'] }}</p>
      </div>
    @endforeach
  </div>
</section>

<hr class="section-divider">

{{-- ══ 7. TESTIMONIALS ══════════════════════════════════════════ --}}
<section style="padding:96px 5%">
  <div class="section-center">
    <div class="eyebrow">Customer Stories</div>
    <h2 class="sec-title">{{ \$page['testimonialsTitle'] }}</h2>
    <p class="sec-sub">{{ \$page['testimonialsSub'] }}</p>
  </div>
  <div class="container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:22px">
    @foreach(\$page['testimonials'] as \$t)
      <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;gap:20px">
        <div>
          <div class="stars">★★★★★</div>
          <p style="font-size:14.5px;color:var(--subtle);line-height:1.75;font-style:italic">"{{ \$t['quote'] }}"</p>
        </div>
        <div style="display:flex;align-items:center;gap:14px;padding-top:16px;border-top:1px solid var(--border)">
          <div class="testi-avatar">{{ \$t['init'] }}</div>
          <div>
            <div style="font-size:14px;font-weight:700;color:#f1f5f9">{{ \$t['name'] }}</div>
            <div style="font-size:12.5px;color:var(--muted)">{{ \$t['role'] }}, {{ \$t['company'] }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</section>

<hr class="section-divider">

{{-- ══ 8. PRICING ════════════════════════════════════════════════ --}}
<section id="pricing" class="section">
  <div class="section-center">
    <div class="eyebrow">Pricing</div>
    <h2 class="sec-title">{{ \$page['pricingTitle'] }}</h2>
    <p class="sec-sub">{{ \$page['pricingSub'] }}</p>
  </div>
  <div class="container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:22px;align-items:start">
    @foreach(\$page['pricing'] as \$plan)
      <div class="pricing-card {{ \$plan['highlight'] ? 'highlighted' : '' }}">
        @if(\$plan['highlight'])
          <div class="pricing-badge">{{ \$page['pricingBadge'] }}</div>
        @endif
        <div style="margin-bottom:20px">
          <div style="font-size:16px;font-weight:800;color:#f1f5f9;margin-bottom:6px">{{ \$plan['name'] }}</div>
          <div style="font-size:13px;color:var(--muted);margin-bottom:18px">{{ \$plan['desc'] }}</div>
          <div style="display:flex;align-items:baseline;gap:2px">
            <span class="price-amount">{{ \$plan['price'] }}</span>
            <span class="price-period">{{ \$plan['period'] }}</span>
          </div>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:20px;margin-bottom:24px;flex:1">
          @foreach(\$plan['features'] as \$feat)
            <div class="price-feat">{{ \$feat }}</div>
          @endforeach
        </div>
        <a href="{{ \$plan['highlight'] ? '/register' : (\$plan['cta'] === 'Contact Sales' ? '#contact' : '/register') }}"
           class="{{ \$plan['highlight'] ? 'btn-primary' : 'btn-ghost' }}" style="text-align:center;display:block;padding:13px 24px;font-size:14px">
          {{ \$plan['cta'] }}
        </a>
      </div>
    @endforeach
  </div>
</section>

<hr class="section-divider">

{{-- ══ 9. FAQ ════════════════════════════════════════════════════ --}}
<section id="faq" class="section">
  <div class="section-center">
    <div class="eyebrow">FAQ</div>
    <h2 class="sec-title">{{ \$page['faqTitle'] }}</h2>
    <p class="sec-sub">{{ \$page['faqSub'] }}</p>
  </div>
  <div class="container" style="max-width:780px;margin:0 auto">
    @foreach(\$page['faq'] as \$item)
      <details>
        <summary>{{ \$item['q'] }}</summary>
        <div class="faq-body">{{ \$item['a'] }}</div>
      </details>
    @endforeach
  </div>
</section>

<hr class="section-divider">

{{-- ══ 10. CONTACT ══════════════════════════════════════════════ --}}
<section id="contact" class="section">
  <div class="section-center">
    <div class="eyebrow">Contact</div>
    <h2 class="sec-title">{{ \$page['contactTitle'] }}</h2>
    <p class="sec-sub">{{ \$page['contactSub'] }}</p>
  </div>
  <div class="container" style="display:grid;grid-template-columns:1fr 1.4fr;gap:48px;align-items:start;max-width:1000px;margin:0 auto">
    {{-- Contact info column --}}
    <div>
      <div class="card" style="padding:32px">
        <h3 style="font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:24px">Contact Information</h3>
        <div style="display:flex;flex-direction:column;gap:20px">
          <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:38px;height:38px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">✉️</div>
            <div><div style="font-size:12px;color:var(--muted);margin-bottom:2px">Email</div><div style="font-size:14px;color:#f1f5f9">{{ \$page['contactEmail'] }}</div></div>
          </div>
          <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:38px;height:38px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">📞</div>
            <div><div style="font-size:12px;color:var(--muted);margin-bottom:2px">Phone</div><div style="font-size:14px;color:#f1f5f9">{{ \$page['contactPhone'] }}</div></div>
          </div>
          <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:38px;height:38px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">📍</div>
            <div><div style="font-size:12px;color:var(--muted);margin-bottom:2px">Address</div><div style="font-size:14px;color:#f1f5f9">{{ \$page['contactAddress'] }}</div></div>
          </div>
          <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:38px;height:38px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">🕐</div>
            <div><div style="font-size:12px;color:var(--muted);margin-bottom:2px">Hours</div><div style="font-size:14px;color:#f1f5f9">{{ \$page['contactHours'] }}</div></div>
          </div>
        </div>
      </div>
    </div>
    {{-- Contact form column --}}
    <div class="card" style="padding:32px">
      <h3 style="font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:24px">Send us a message</h3>
      @if(session('contact_success'))
        <div class="alert-success">✓ {{ session('contact_success') }}</div>
      @endif
      <form action="{{ route('contact') }}" method="POST">
        @csrf
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-input" placeholder="John Smith" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-input" placeholder="john@company.com" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-input" placeholder="How can we help you?">
        </div>
        <div class="form-group">
          <label class="form-label">Message *</label>
          <textarea name="message" class="form-input" placeholder="Tell us about your project or question..." required></textarea>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;text-align:center;font-size:15px;padding:14px">Send Message →</button>
      </form>
    </div>
  </div>
</section>

{{-- ══ 11. CTA BANNER ════════════════════════════════════════════ --}}
<section class="cta-section" style="padding:96px 5%;text-align:center">
  <div style="max-width:680px;margin:0 auto">
    <div class="eyebrow" style="margin-bottom:14px">Get Started Today</div>
    <h2 style="font-size:clamp(1.8rem,4vw,2.8rem);font-weight:900;color:#f1f5f9;line-height:1.15;margin-bottom:16px">{{ \$page['ctaTitle'] }}</h2>
    <p style="font-size:16px;color:var(--subtle);margin-bottom:40px;line-height:1.7">{{ \$page['ctaSub'] }}</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a href="{{ \$page['ctaBtnHref'] }}" class="btn-primary">{{ \$page['ctaBtn'] }}</a>
      <a href="{{ \$page['ctaBtnAltHref'] }}" class="btn-ghost">{{ \$page['ctaBtnAlt'] }}</a>
    </div>
    <p style="margin-top:18px;font-size:12.5px;color:var(--muted)">No credit card required · 14-day free trial · Cancel anytime</p>
  </div>
</section>

{{-- ══ 12. FOOTER (4-COLUMN) ═════════════════════════════════════ --}}
<footer style="background:rgba(255,255,255,.015);border-top:1px solid var(--border);padding:72px 5% 0">
  <div class="container" style="display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr 1fr;gap:40px;margin-bottom:56px">
    {{-- Brand column --}}
    <div>
      <div style="display:flex;align-items:center;gap:10px;font-weight:900;font-size:17px;color:#f1f5f9;margin-bottom:14px">
        <div style="width:34px;height:34px;background:var(--brand);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff">{{ \$page['appInitial'] }}</div>
        {{ \$page['appName'] }}
      </div>
      <p style="font-size:13.5px;color:var(--muted);line-height:1.7;margin-bottom:22px;max-width:220px">{{ \$page['footerTagline'] }}</p>
      <div style="display:flex;gap:8px">
        @foreach(\$page['footerSocial'] as \$s)
          <a href="{{ \$s['href'] }}" class="social-btn" target="_blank" rel="noopener">{{ \$s['icon'] }}</a>
        @endforeach
      </div>
    </div>
    {{-- Link columns --}}
    @foreach(\$page['footerCols'] as \$col)
      <div class="footer-col">
        <h4>{{ \$col['title'] }}</h4>
        @foreach(\$col['links'] as \$lnk)
          <a href="{{ \$lnk['href'] }}">{{ \$lnk['label'] }}</a>
        @endforeach
      </div>
    @endforeach
  </div>

  {{-- ── COPYRIGHT BAR ──────────────────────────────────────────── --}}
  <div style="border-top:1px solid var(--border);padding:22px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <span style="font-size:13px;color:var(--muted)">{{ \$page['footerCopy'] }}</span>
    <div style="display:flex;gap:18px">
      <a href="/privacy" style="font-size:13px;color:var(--muted)">Privacy</a>
      <a href="/terms" style="font-size:13px;color:var(--muted)">Terms</a>
      <a href="/cookies" style="font-size:13px;color:var(--muted)">Cookies</a>
    </div>
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
