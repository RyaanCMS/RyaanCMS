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
        $validEntities = array_filter($entities, fn($e) => !empty($e['name']) && !empty($e['fields']));

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

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
            $navLinks .= <<<BLADE

                    <a href="{{ route('{$plural}.index') }}"
                       class="nav-link {{ request()->routeIs('{$plural}.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
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
                    color:#c7d2fe; text-decoration:none; font-size:13.5px; font-weight:500; transition:.15s; }
        .nav-link:hover, .nav-link.active { background:rgba(99,102,241,.25); color:#fff; }
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
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
{$navLinks}
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
            $i++;
            $cards .= <<<BLADE

        <a href="{{ route('{$plural}.index') }}" class="stat-card" style="--c:{$color};">
            <div class="stat-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:22px;height:22px;color:{$color};"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16"/></svg>
            </div>
            <div class="stat-num">{{ \${$var}Count }}</div>
            <div class="stat-label">Total {$plurTitle}</div>
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
}
