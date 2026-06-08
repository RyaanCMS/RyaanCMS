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
