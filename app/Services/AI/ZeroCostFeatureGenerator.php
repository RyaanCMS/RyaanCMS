<?php

namespace App\Services\AI;

use App\Models\Project;
use Illuminate\Support\Str;

/**
 * ZeroCostFeatureGenerator
 *
 * Generates complete feature pages (analytics, reports, calendar, kanban)
 * using Chart.js and standard Laravel DB queries — ZERO AI tokens.
 *
 * Intent detection → deterministic code generation.
 * Called from CodeGeneratorService before any AI provider is needed.
 */
class ZeroCostFeatureGenerator
{
    // ── Intent detection ────────────────────────────────────────────────────

    private array $intents = [
        'analytics' => [
            'chart', 'charts', 'analytics', 'statistics', 'stats', 'overview',
            'dashboard', 'graph', 'graphs', 'visualization', 'insight', 'insights',
            'booking analytics', 'sales analytics', 'revenue chart',
        ],
        'reports'   => [
            'report', 'reports', 'export', 'summary report', 'monthly report',
            'weekly report', 'daily report', 'print', 'pdf report',
        ],
        'calendar'  => [
            'calendar', 'schedule', 'timeline', 'planner', 'booking calendar',
            'event calendar', 'appointment calendar',
        ],
        'kanban'    => [
            'kanban', 'board', 'pipeline', 'status board', 'task board',
            'workflow board', 'drag and drop',
        ],
    ];

    public function detectIntent(string $prompt): ?string
    {
        $lower = strtolower($prompt);
        foreach ($this->intents as $intent => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $intent;
                }
            }
        }
        return null;
    }

    /**
     * Generate files for the detected intent. Returns null if no match.
     */
    public function generate(string $prompt, Project $project): ?array
    {
        $intent = $this->detectIntent($prompt);
        if (!$intent) return null;

        $blueprint = $project->blueprint ?? [];
        $entities  = $blueprint['suggested_entities'] ?? $blueprint['entities'] ?? [];
        $appName   = $project->name ?: 'App';

        return match ($intent) {
            'analytics' => $this->buildAnalytics($entities, $appName),
            'reports'   => $this->buildReports($entities, $appName),
            'calendar'  => $this->buildCalendar($entities, $appName),
            'kanban'    => $this->buildKanban($entities, $appName),
            default     => null,
        };
    }

    public function intentLabel(string $intent): string
    {
        return match ($intent) {
            'analytics' => 'Analytics Dashboard',
            'reports'   => 'Reports Page',
            'calendar'  => 'Calendar View',
            'kanban'    => 'Kanban Board',
            default     => 'Feature',
        };
    }

    // ── Analytics Dashboard ─────────────────────────────────────────────────

    private function buildAnalytics(array $entities, string $appName): array
    {
        return [
            [
                'path'    => 'app/Http/Controllers/AnalyticsController.php',
                'content' => $this->analyticsController($entities),
            ],
            [
                'path'    => 'resources/views/analytics/index.blade.php',
                'content' => $this->analyticsView($entities, $appName),
            ],
            [
                'path'    => 'routes/_analytics.txt',
                'content' => "// Add to routes/web.php:\nuse App\\Http\\Controllers\\AnalyticsController;\nRoute::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');",
            ],
        ];
    }

    private function analyticsController(array $entities): string
    {
        $counts    = '';
        $trends    = '';
        $passCount = '';
        $passTrend = '';

        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $name  = Str::studly($e['name']);
            $var   = Str::camel($name);
            $table = Str::snake(Str::plural($name));

            $counts    .= "        \${$var}Count = \\App\\Models\\{$name}::count();\n";
            $trends    .= "        \${$var}Trend = \\DB::table('{$table}')\n            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')\n            ->where('created_at', '>=', now()->subDays(30))\n            ->groupBy('date')->orderBy('date')->get();\n";
            $passCount .= "            '{$var}Count' => \${$var}Count,\n";
            $passTrend .= "            '{$var}Trend' => \${$var}Trend,\n";
        }

        // Find first entity with a status/enum field for distribution chart
        $statusChartCode = '';
        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $fields = $e['fields'] ?? [];
            $statusField = null;
            foreach ($fields as $f) {
                if (in_array($f['type'] ?? '', ['enum', 'select', 'status']) || str_contains(strtolower($f['name'] ?? ''), 'status')) {
                    $statusField = $f['name'];
                    break;
                }
            }
            if ($statusField) {
                $name  = Str::studly($e['name']);
                $var   = Str::camel($name);
                $table = Str::snake(Str::plural($name));
                $statusChartCode = "        \$statusDist = \\DB::table('{$table}')\n            ->selectRaw('{$statusField}, COUNT(*) as total')\n            ->groupBy('{$statusField}')->get();\n";
                $passCount .= "            'statusDist' => \$statusDist,\n            'statusLabel' => '" . Str::title(str_replace('_', ' ', $statusField)) . "',\n";
                break;
            }
        }

        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
{$counts}
{$trends}
{$statusChartCode}
        return view('analytics.index', [
{$passCount}
{$passTrend}
        ]);
    }
}
PHP;
    }

    private function analyticsView(array $entities, string $appName): string
    {
        $colors = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];

        // KPI cards
        $kpiCards = '';
        $i = 0;
        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $name      = Str::studly($e['name']);
            $var       = Str::camel($name);
            $singTitle = Str::title(str_replace('_', ' ', Str::snake($name)));
            $plural    = Str::plural(Str::snake($name));
            $plurTitle = Str::title(str_replace('_', ' ', $plural));
            $color     = $colors[$i % count($colors)];
            $i++;
            $kpiCards .= <<<BLADE

        <div class="kpi-card" style="--c:{$color};">
            <div class="kpi-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="kpi-num">{{ \${$var}Count }}</div>
            <div class="kpi-label">Total {$plurTitle}</div>
        </div>
BLADE;
        }

        // Line chart datasets (last 30 days trend per entity)
        $chartDatasets = '';
        $i = 0;
        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $var   = Str::camel(Str::studly($e['name']));
            $label = Str::title(str_replace('_', ' ', Str::snake($e['name'])));
            $color = $colors[$i % count($colors)];
            $i++;
            $chartDatasets .= <<<JS

        {
            label: '{$label}',
            data: {!! json_encode(\${$var}Trend->pluck('total')->toArray()) !!},
            borderColor: '{$color}',
            backgroundColor: '{$color}22',
            tension: 0.4, fill: true, pointRadius: 3,
        },
JS;
        }

        // Labels from first entity's trend dates
        $firstVar = '';
        foreach ($entities as $e) {
            if (!empty($e['name'])) { $firstVar = Str::camel(Str::studly($e['name'])); break; }
        }
        $labelsCode = $firstVar
            ? "{!! json_encode(\${$firstVar}Trend->pluck('date')->toArray()) !!}"
            : "[]";

        return <<<BLADE
@extends('layouts.app')
@section('title', 'Analytics')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endsection

@section('styles')
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:14px; margin-bottom:24px; }
.kpi-card { background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:18px 16px;
            display:flex; flex-direction:column; align-items:center; text-align:center; transition:.2s; }
.kpi-card:hover { border-color:var(--c); box-shadow:0 6px 20px color-mix(in srgb,var(--c) 15%,transparent); }
.kpi-icon { width:40px;height:40px;border-radius:10px;background:color-mix(in srgb,var(--c) 12%,#fff);
            display:flex;align-items:center;justify-content:center;margin-bottom:10px; }
.kpi-icon svg { width:18px;height:18px;color:var(--c); }
.kpi-num { font-size:26px;font-weight:800;color:#1e293b;line-height:1; }
.kpi-label { font-size:11px;font-weight:600;color:#64748b;margin-top:3px; }
.chart-card { background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:20px; }
.chart-title { font-size:14px;font-weight:700;color:#1e293b;margin-bottom:16px; }
.charts-grid { display:grid; grid-template-columns:2fr 1fr; gap:16px; }
@media(max-width:768px){ .charts-grid{ grid-template-columns:1fr; } }
@endsection

@section('content')
<div style="margin-bottom:20px;">
    <h1 style="font-size:20px;font-weight:700;color:#1e293b;">{$appName} — Analytics</h1>
    <p style="font-size:13px;color:#64748b;margin-top:2px;">Last 30 days overview</p>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid">
{$kpiCards}
</div>

{{-- Charts --}}
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-title">Activity Trend — Last 30 Days</div>
        <canvas id="trendChart" height="100"></canvas>
    </div>
    @isset(\$statusDist)
    <div class="chart-card">
        <div class="chart-title">{{ \$statusLabel ?? 'Status' }} Distribution</div>
        <canvas id="statusChart" height="200"></canvas>
    </div>
    @endisset
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Trend line chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {$labelsCode},
            datasets: [{$chartDatasets}
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });

    @isset(\$statusDist)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(\$statusDist->pluck(array_key_first(\$statusDist->first() ? (array)\$statusDist->first() : ['status' => 'status']))->toArray()) !!},
            datasets: [{ data: {!! json_encode(\$statusDist->pluck('total')->toArray()) !!},
                backgroundColor: ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6'] }],
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
    });
    @endisset
});
</script>
@endsection
BLADE;
    }

    // ── Reports Page ────────────────────────────────────────────────────────

    private function buildReports(array $entities, string $appName): array
    {
        $files = [];
        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $name   = Str::studly($e['name']);
            $plural = Str::plural(Str::snake($name));
            $files[] = [
                'path'    => "resources/views/reports/{$plural}.blade.php",
                'content' => $this->reportView($e, $appName),
            ];
        }
        $files[] = [
            'path'    => 'app/Http/Controllers/ReportController.php',
            'content' => $this->reportController($entities),
        ];
        $files[] = [
            'path'    => 'routes/_reports.txt',
            'content' => $this->reportRoutes($entities),
        ];
        return $files;
    }

    private function reportController(array $entities): string
    {
        $methods = '';
        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $name   = Str::studly($e['name']);
            $var    = Str::camel($name);
            $plural = Str::plural(Str::snake($name));
            $methods .= <<<PHP

    public function {$var}Report(Request \$request)
    {
        \$query = \\App\\Models\\{$name}::query();
        if (\$request->from) \$query->whereDate('created_at', '>=', \$request->from);
        if (\$request->to)   \$query->whereDate('created_at', '<=', \$request->to);
        \$records = \$query->latest()->paginate(50);
        return view('reports.{$plural}', compact('records'));
    }

PHP;
        }

        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
{$methods}
}
PHP;
    }

    private function reportRoutes(array $entities): string
    {
        $lines = "// Add to routes/web.php:\nuse App\\Http\\Controllers\\ReportController;\n";
        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            $var    = Str::camel(Str::studly($e['name']));
            $plural = Str::plural(Str::snake(Str::studly($e['name'])));
            $lines .= "Route::get('/reports/{$plural}', [ReportController::class, '{$var}Report'])->name('reports.{$plural}');\n";
        }
        return $lines;
    }

    private function reportView(array $entity, string $appName): string
    {
        $name      = Str::studly($entity['name']);
        $var       = Str::camel($name);
        $plural    = Str::plural(Str::snake($name));
        $singTitle = Str::title(str_replace('_', ' ', Str::snake($name)));
        $plurTitle = Str::title(str_replace('_', ' ', $plural));
        $fields    = array_slice($entity['fields'] ?? [], 0, 6);

        $headers = '<th>ID</th>' . "\n";
        $cells   = '<td>{{ $item->id }}</td>' . "\n";
        foreach ($fields as $f) {
            $col   = $f['name'] ?? '';
            $label = Str::title(str_replace('_', ' ', $col));
            $headers .= "                <th>{$label}</th>\n";
            $cells   .= "                <td>{{ \$item->{$col} }}</td>\n";
        }
        $headers .= '                <th>Created</th>';
        $cells   .= '                <td>{{ $item->created_at?->format(\'d M Y\') }}</td>';

        return <<<BLADE
@extends('layouts.app')
@section('title', '{$plurTitle} Report')

@section('styles')
.report-table th { background:#f8fafc; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; padding:10px 12px; border-bottom:1px solid #e2e8f0; }
.report-table td { font-size:13px; color:#374151; padding:10px 12px; border-bottom:1px solid #f1f5f9; }
.report-table tr:hover td { background:#fafbff; }
@media print { .no-print{display:none!important} body{background:#fff} .sidebar{display:none!important} .main-content{width:100%} }
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#1e293b;">{$plurTitle} Report</h1>
        <p style="font-size:12px;color:#64748b;">{{ \$records->total() }} records found</p>
    </div>
    <button onclick="window.print()" class="no-print"
        style="background:#6366f1;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
        Print / Export
    </button>
</div>

{{-- Date filter --}}
<form class="no-print" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;background:#fff;padding:12px 16px;border-radius:12px;border:1px solid #e2e8f0;">
    <label style="font-size:12px;font-weight:600;color:#64748b;">From</label>
    <input type="date" name="from" value="{{ request('from') }}" style="border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:13px;">
    <label style="font-size:12px;font-weight:600;color:#64748b;">To</label>
    <input type="date" name="to" value="{{ request('to') }}" style="border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:13px;">
    <button type="submit" style="background:#6366f1;color:#fff;border:none;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Filter</button>
    @if(request('from') || request('to'))
        <a href="{{ url()->current() }}" style="font-size:12px;color:#64748b;text-decoration:none;">Clear</a>
    @endif
</form>

<div style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">
    <table class="report-table" style="width:100%;border-collapse:collapse;">
        <thead><tr>
            {$headers}
        </tr></thead>
        <tbody>
            @forelse(\$records as \$item)
            <tr>
                {$cells}
            </tr>
            @empty
            <tr><td colspan="99" style="text-align:center;padding:32px;color:#94a3b8;font-size:13px;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;" class="no-print">{{ \$records->withQueryString()->links() }}</div>
@endsection
BLADE;
    }

    // ── Calendar View ───────────────────────────────────────────────────────

    private function buildCalendar(array $entities, string $appName): array
    {
        // Use first entity with a date field
        $targetEntity = null;
        $dateField    = 'created_at';
        $titleField   = 'name';

        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            foreach ($e['fields'] ?? [] as $f) {
                $type = $f['type'] ?? '';
                $fname = strtolower($f['name'] ?? '');
                if (in_array($type, ['date', 'datetime', 'timestamp']) || str_contains($fname, 'date') || str_contains($fname, 'at')) {
                    $targetEntity = $e;
                    $dateField    = $f['name'];
                    break;
                }
            }
            // Find title field
            if ($targetEntity) {
                foreach ($e['fields'] ?? [] as $f) {
                    $fname = strtolower($f['name'] ?? '');
                    if (in_array($fname, ['name','title','subject','summary','label'])) {
                        $titleField = $f['name'];
                        break;
                    }
                }
                break;
            }
        }

        if (!$targetEntity) $targetEntity = $entities[0] ?? ['name' => 'Event', 'fields' => []];

        $name   = Str::studly($targetEntity['name']);
        $var    = Str::camel($name);
        $plural = Str::plural(Str::snake($name));

        return [
            [
                'path'    => 'resources/views/calendar/index.blade.php',
                'content' => $this->calendarView($name, $var, $plural, $dateField, $titleField, $appName),
            ],
            [
                'path'    => 'app/Http/Controllers/CalendarController.php',
                'content' => $this->calendarController($name, $var, $dateField, $titleField),
            ],
            [
                'path'    => 'routes/_calendar.txt',
                'content' => "// Add to routes/web.php:\nuse App\\Http\\Controllers\\CalendarController;\nRoute::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');\nRoute::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');",
            ],
        ];
    }

    private function calendarController(string $name, string $var, string $dateField, string $titleField): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function events(Request \$request)
    {
        \$start = \$request->get('start');
        \$end   = \$request->get('end');

        \$records = \\App\\Models\\{$name}::query()
            ->when(\$start, fn(\$q) => \$q->whereDate('{$dateField}', '>=', \$start))
            ->when(\$end,   fn(\$q) => \$q->whereDate('{$dateField}', '<=', \$end))
            ->get()
            ->map(fn(\$r) => [
                'id'    => \$r->id,
                'title' => \$r->{$titleField} ?? 'Item #' . \$r->id,
                'start' => \$r->{$dateField},
                'url'   => route('{$var}s.show', \$r->id),
            ]);

        return response()->json(\$records);
    }
}
PHP;
    }

    private function calendarView(string $name, string $var, string $plural, string $dateField, string $titleField, string $appName): string
    {
        $plurTitle = Str::title(str_replace('_', ' ', $plural));
        return <<<BLADE
@extends('layouts.app')
@section('title', '{$plurTitle} Calendar')

@section('head')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.js"></script>
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#1e293b;">{$plurTitle} Calendar</h1>
        <p style="font-size:12px;color:#64748b;">View and manage by date</p>
    </div>
    <a href="{{ route('{$plural}.create') }}"
       style="background:#6366f1;color:#fff;text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">
        + New {$name}
    </a>
</div>

<div style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;padding:20px;">
    <div id="calendar"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        events: '{{ route("calendar.events") }}',
        eventColor: '#6366f1',
        eventClick: function (info) { if (info.event.url) { window.location.href = info.event.url; info.jsEvent.preventDefault(); } },
    }).render();
});
</script>
@endsection
BLADE;
    }

    // ── Kanban Board ────────────────────────────────────────────────────────

    private function buildKanban(array $entities, string $appName): array
    {
        // Find entity with a status field
        $targetEntity = null;
        $statusField  = 'status';
        $statusValues = ['pending', 'in_progress', 'done'];
        $titleField   = 'name';

        foreach ($entities as $e) {
            if (empty($e['name'])) continue;
            foreach ($e['fields'] ?? [] as $f) {
                $fname = strtolower($f['name'] ?? '');
                if ($fname === 'status' || str_contains($fname, 'status') || ($f['type'] ?? '') === 'enum') {
                    $targetEntity = $e;
                    $statusField  = $f['name'];
                    $statusValues = $f['options'] ?? $f['values'] ?? ['pending', 'in_progress', 'done'];
                    break;
                }
            }
            if ($targetEntity) {
                foreach ($e['fields'] ?? [] as $f) {
                    if (in_array(strtolower($f['name'] ?? ''), ['name','title','subject','label'])) {
                        $titleField = $f['name'];
                        break;
                    }
                }
                break;
            }
        }

        if (!$targetEntity) $targetEntity = $entities[0] ?? ['name' => 'Task', 'fields' => []];

        $name   = Str::studly($targetEntity['name']);
        $var    = Str::camel($name);
        $plural = Str::plural(Str::snake($name));

        return [
            [
                'path'    => 'resources/views/kanban/index.blade.php',
                'content' => $this->kanbanView($name, $var, $plural, $statusField, $statusValues, $titleField, $appName),
            ],
            [
                'path'    => 'app/Http/Controllers/KanbanController.php',
                'content' => $this->kanbanController($name, $var, $plural, $statusField, $statusValues, $titleField),
            ],
            [
                'path'    => 'routes/_kanban.txt',
                'content' => "// Add to routes/web.php:\nuse App\\Http\\Controllers\\KanbanController;\nRoute::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');\nRoute::patch('/kanban/{id}/status', [KanbanController::class, 'updateStatus'])->name('kanban.status');",
            ],
        ];
    }

    private function kanbanController(string $name, string $var, string $plural, string $statusField, array $statuses, string $titleField): string
    {
        $statusList = implode("', '", $statuses);
        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KanbanController extends Controller
{
    private array \$statuses = ['{$statusList}'];

    public function index()
    {
        \$columns = collect(\$this->statuses)->mapWithKeys(fn(\$s) =>
            [\$s => \\App\\Models\\{$name}::where('{$statusField}', \$s)->latest()->get()]
        );
        return view('kanban.index', compact('columns'));
    }

    public function updateStatus(Request \$request, int \$id)
    {
        \$request->validate(['{$statusField}' => ['required', 'in:' . implode(',', \$this->statuses)]]);
        \\App\\Models\\{$name}::findOrFail(\$id)->update(['{$statusField}' => \$request->{$statusField}]);
        return response()->json(['success' => true]);
    }
}
PHP;
    }

    private function kanbanView(string $name, string $var, string $plural, string $statusField, array $statuses, string $titleField, string $appName): string
    {
        $colors  = ['#6366f1','#f59e0b','#10b981','#ef4444','#0ea5e9','#8b5cf6'];
        $columns = '';
        $i       = 0;
        foreach ($statuses as $status) {
            $label  = Str::title(str_replace('_', ' ', $status));
            $color  = $colors[$i % count($colors)];
            $i++;
            $columns .= <<<BLADE

        <div class="kanban-col" data-status="{$status}"
             x-on:dragover.prevent x-on:drop="drop(\$event, '{$status}')">
            <div class="col-header" style="--c:{$color};">
                <span>{$label}</span>
                <span class="col-count" x-text="columns['{$status}']?.length ?? 0"></span>
            </div>
            <div class="col-body" :id="'col-{$status}'">
                @foreach(\$columns['{$status}'] ?? [] as \$item)
                <div class="kanban-card" draggable="true"
                     x-on:dragstart="drag(\$event, {{ \$item->id }}, '{$status}')"
                     data-id="{{ \$item->id }}">
                    <div class="card-title">{{ \$item->{$titleField} ?? 'Item #' . \$item->id }}</div>
                    <div class="card-meta">{{ \$item->created_at?->diffForHumans() }}</div>
                </div>
                @endforeach
            </div>
        </div>
BLADE;
        }

        $statusJson = json_encode(array_values($statuses));
        return <<<BLADE
@extends('layouts.app')
@section('title', 'Kanban Board')

@section('styles')
.kanban-board { display:flex; gap:16px; overflow-x:auto; padding-bottom:16px; }
.kanban-col { min-width:240px; flex-shrink:0; background:#f8fafc; border-radius:14px; overflow:hidden; border:1px solid #e2e8f0; }
.col-header { display:flex; align-items:center; justify-content:space-between; padding:12px 14px;
              background:color-mix(in srgb,var(--c) 10%,#fff); border-bottom:2px solid var(--c); }
.col-header span { font-size:12px; font-weight:700; color:#1e293b; }
.col-count { background:var(--c); color:#fff; border-radius:99px; padding:1px 8px; font-size:11px; }
.col-body { padding:10px; min-height:200px; display:flex; flex-direction:column; gap:8px; }
.kanban-card { background:#fff; border-radius:10px; padding:12px; border:1px solid #e2e8f0;
               cursor:grab; transition:.15s; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.kanban-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); transform:translateY(-1px); }
.kanban-card.dragging { opacity:.4; }
.card-title { font-size:13px; font-weight:600; color:#1e293b; }
.card-meta { font-size:11px; color:#94a3b8; margin-top:4px; }
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#1e293b;">Kanban Board</h1>
        <p style="font-size:12px;color:#64748b;">{$appName} — drag cards to update status</p>
    </div>
    <a href="{{ route('{$plural}.create') }}"
       style="background:#6366f1;color:#fff;text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">
        + New {$name}
    </a>
</div>

<div class="kanban-board" x-data="kanban()">
{$columns}
</div>

<script>
function kanban() {
    return {
        dragging: null,
        columns: @json(\$columns->map(fn(\$c) => \$c->toArray())),
        statuses: {$statusJson},
        drag(e, id, fromStatus) {
            this.dragging = { id, fromStatus };
            e.target.classList.add('dragging');
            e.dataTransfer.setData('text/plain', id);
        },
        drop(e, toStatus) {
            if (!this.dragging || this.dragging.fromStatus === toStatus) return;
            fetch('{{ route("kanban.status", ["id" => "__ID__"]) }}'.replace('__ID__', this.dragging.id), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                body: JSON.stringify({ {$statusField}: toStatus }),
            }).then(r => r.ok && location.reload());
        },
    };
}
</script>
@endsection
BLADE;
    }
}
