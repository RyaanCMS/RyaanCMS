@extends('layouts.app')

@section('title', 'Intelligence Ledger')

@section('content')
<div class="p-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-1)">Intelligence Ledger</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-3)">Accumulated wisdom from every project build</p>
        </div>
        <a href="{{ route('wisdom.stats') }}" class="text-xs px-3 py-1.5 rounded-lg border"
           style="border-color:var(--border);color:var(--text-2)" target="_blank">
            Raw Stats
        </a>
    </div>

    {{-- Migration Notice --}}
    @if(!empty($needsMigration))
    <div class="mb-6 rounded-2xl p-5 flex items-start gap-4"
         style="background:#fffbeb; border:1px solid #fde68a;">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
             style="background:#fef3c7; border:1px solid #fcd34d;">
            <svg class="w-4 h-4" style="color:#d97706" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold mb-0.5" style="color:#92400e;">Database migration required</p>
            <p class="text-sm" style="color:#b45309;">The <code class="px-1 py-0.5 rounded text-xs font-mono" style="background:#fde68a;">project_wisdom</code> table has not been created yet. Run <code class="px-1.5 py-0.5 rounded text-xs font-mono" style="background:#fde68a;">php artisan migrate</code> in your project directory to activate the Intelligence Ledger.</p>
        </div>
    </div>
    @endif

    {{-- Stats Row --}}
    @php
        $statCards = [
            ['label' => 'Total Lessons',     'value' => ($stats['db_lessons'] ?? 0) + ($stats['static_lessons'] ?? 0), 'color' => '#7c3aed'],
            ['label' => 'Rule Candidates',   'value' => $stats['rule_candidates'] ?? 0,           'color' => '#06b6d4'],
            ['label' => 'Mental Models',     'value' => $stats['mental_models'] ?? 0,             'color' => '#10b981'],
            ['label' => 'AI Rules (no-call)','value' => $stats['ai_replacement_rules'] ?? 0,      'color' => '#f59e0b'],
            ['label' => 'AI Cost Saved',     'value' => '$' . number_format($stats['total_ai_saved'] ?? 0, 4), 'color' => '#ec4899'],
        ];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach($statCards as $card)
        <div class="kpi-card" style="--c:{{ $card['color'] }};background:var(--card-bg);">
            <div class="kpi-val">{{ $card['value'] }}</div>
            <div class="kpi-label">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('wisdom.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search lessons, recommendationsâ€¦"
               class="flex-1 min-w-[200px] rounded-lg px-3 py-2 text-sm border"
               style="background:var(--input-bg);border-color:var(--border);color:var(--text-1)">

        <select name="domain"
                class="rounded-lg px-3 py-2 text-sm border"
                style="background:var(--input-bg);border-color:var(--border);color:var(--text-1)">
            <option value="all">All Domains</option>
            @foreach($domains as $d)
            <option value="{{ $d }}" @selected(request('domain') === $d)>{{ ucfirst($d) }}</option>
            @endforeach
        </select>

        <select name="entry_type"
                class="rounded-lg px-3 py-2 text-sm border"
                style="background:var(--input-bg);border-color:var(--border);color:var(--text-1)">
            <option value="all">All Types</option>
            @foreach(['lesson','decision','outcome','autopsy'] as $t)
            <option value="{{ $t }}" @selected(request('entry_type') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium transition-all sys-btn">Filter</button>
        @if(request()->hasAny(['search','domain','entry_type']))
        <a href="{{ route('wisdom.index') }}" class="px-4 py-2 rounded-lg text-sm border"
           style="border-color:var(--border);color:var(--text-2)">Clear</a>
        @endif
    </form>

    {{-- Entries Table --}}
    @if($entries->isEmpty())
    <div class="rounded-xl border p-12 text-center" style="background:var(--card-bg);border-color:var(--border)">
        <div class="text-5xl mb-3">ðŸ§ </div>
        <div class="font-medium" style="color:var(--text-1)">No entries yet</div>
        <div class="text-sm mt-1" style="color:var(--text-3)">
            Entries accumulate automatically as you build projects with AI.
        </div>
    </div>
    @else
    <div class="sys-card" style="overflow:hidden;border-radius:14px;">
        <div style="padding:16px 18px 14px;border-bottom:1px solid var(--border);background:var(--card-bg);">
            <h2 style="margin:0;font-size:15px;line-height:1.35;font-weight:800;color:var(--text-1);">Wisdom Entries Table</h2>
            <p style="margin:3px 0 0;font-size:12px;line-height:1.5;color:var(--text-3);">Review lessons, decisions, and rule candidates captured from project builds.</p>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--card-sub);border-bottom:1px solid var(--border)">
                    <th class="text-left px-4 py-3 font-medium" style="color:var(--text-2)">Lesson</th>
                    <th class="text-left px-4 py-3 font-medium w-24 hidden md:table-cell" style="color:var(--text-2)">Domain</th>
                    <th class="text-left px-4 py-3 font-medium w-24 hidden md:table-cell" style="color:var(--text-2)">Type</th>
                    <th class="text-left px-4 py-3 font-medium w-20 hidden lg:table-cell" style="color:var(--text-2)">Confidence</th>
                    <th class="text-left px-4 py-3 font-medium w-16 hidden lg:table-cell" style="color:var(--text-2)">Reused</th>
                    <th class="text-right px-4 py-3 font-medium w-24" style="color:var(--text-2)">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                @php
                    $typeColor = match($entry->entry_type) {
                        'lesson'   => '#10b981',
                        'decision' => '#7c3aed',
                        'autopsy'  => '#ef4444',
                        'outcome'  => '#06b6d4',
                        default    => '#6b7280',
                    };
                    $confColor = $entry->confidence >= 0.85 ? '#10b981' : ($entry->confidence >= 0.7 ? '#f59e0b' : '#ef4444');
                @endphp
                <tr style="border-bottom:1px solid var(--border);background:var(--card-bg)"
                    onmouseover="this.style.background='var(--hover-bg)'"
                    onmouseout="this.style.background='var(--card-bg)'">
                    <td class="px-4 py-3">
                        <div class="font-medium truncate max-w-xs" style="color:var(--text-1)">
                            {{ Str::limit($entry->lesson ?? $entry->recommendation ?? 'â€”', 80) }}
                        </div>
                        @if($entry->recommendation)
                        <div class="text-xs mt-0.5 truncate max-w-xs" style="color:var(--text-3)">
                            â†’ {{ Str::limit($entry->recommendation, 70) }}
                        </div>
                        @endif
                        @if($entry->is_rule_candidate)
                        <span class="inline-block text-xs px-1.5 py-0.5 rounded mt-1"
                              style="background:rgba(245,158,11,0.15);color:#f59e0b">â­ Rule candidate</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="text-xs px-2 py-0.5 rounded-full"
                              style="background:rgba(124,58,237,0.12);color:#7c3aed">
                            {{ $entry->domain }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              style="background:{{ $typeColor }}20;color:{{ $typeColor }}">
                            {{ ucfirst($entry->entry_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="font-mono text-xs" style="color:{{ $confColor }}">
                            {{ number_format($entry->confidence * 100, 0) }}%
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="text-xs" style="color:var(--text-2)">{{ $entry->reuse_count }}Ã—</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('wisdom.show', $entry) }}"
                               class="text-xs px-2 py-1 rounded border"
                               style="border-color:var(--border);color:var(--text-2)">View</a>
                            @unless($entry->is_rule_candidate)
                            <form method="POST" action="{{ route('wisdom.promote', $entry) }}">
                                @csrf
                                <button type="submit" class="text-xs px-2 py-1 rounded border"
                                        style="border-color:#f59e0b;color:#f59e0b"
                                        title="Promote to rule candidate">â­</button>
                            </form>
                            @endunless
                            <form method="POST" action="{{ route('wisdom.destroy', $entry) }}"
                                  onsubmit="return confirm('Delete this entry?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2 py-1 rounded border"
                                        style="border-color:#ef4444;color:#ef4444">Ã—</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $entries->links() }}
    </div>
    @endif

</div>
@endsection

