@extends('layouts.app')
@section('title', 'Projects')
@section('header', 'My Projects')


@php
$typeMap = [
    'laravel'    => ['emoji'=>'⚡','from'=>'#f97316','to'=>'#ef4444','bg'=>'#fff7ed','bd'=>'#fed7aa','txt'=>'#c2410c','label'=>'Laravel'],
    'react'      => ['emoji'=>'⚛️','from'=>'#06b6d4','to'=>'#3b82f6','bg'=>'#ecfeff','bd'=>'#a5f3fc','txt'=>'#0e7490','label'=>'React'],
    'nextjs'     => ['emoji'=>'▲', 'from'=>'#475569','to'=>'#1e293b','bg'=>'#f8fafc','bd'=>'#e2e8f0','txt'=>'#475569','label'=>'Next.js'],
    'ecommerce'  => ['emoji'=>'🛒','from'=>'#10b981','to'=>'#059669','bg'=>'#ecfdf5','bd'=>'#a7f3d0','txt'=>'#065f46','label'=>'eCommerce'],
    'crm'        => ['emoji'=>'👥','from'=>'#8b5cf6','to'=>'#7c3aed','bg'=>'#fdf4ff','bd'=>'#e9d5ff','txt'=>'#6d28d9','label'=>'CRM'],
    'hrm'        => ['emoji'=>'🧑','from'=>'#0ea5e9','to'=>'#6366f1','bg'=>'#f0f9ff','bd'=>'#bae6fd','txt'=>'#0369a1','label'=>'HRM'],
    'erp'        => ['emoji'=>'🏭','from'=>'#d97706','to'=>'#b45309','bg'=>'#fffbeb','bd'=>'#fde68a','txt'=>'#92400e','label'=>'ERP'],
    'saas'       => ['emoji'=>'🚀','from'=>'#6366f1','to'=>'#8b5cf6','bg'=>'#eef2ff','bd'=>'#c7d2fe','txt'=>'#4338ca','label'=>'SaaS'],
    'hospital'   => ['emoji'=>'🏥','from'=>'#ef4444','to'=>'#dc2626','bg'=>'#fef2f2','bd'=>'#fecaca','txt'=>'#991b1b','label'=>'Hospital'],
    'school'     => ['emoji'=>'🎓','from'=>'#06b6d4','to'=>'#0891b2','bg'=>'#ecfeff','bd'=>'#a5f3fc','txt'=>'#0e7490','label'=>'School'],
    'restaurant' => ['emoji'=>'🍽️','from'=>'#f97316','to'=>'#ea580c','bg'=>'#fff7ed','bd'=>'#fed7aa','txt'=>'#c2410c','label'=>'Restaurant'],
    'pos'        => ['emoji'=>'🏪','from'=>'#10b981','to'=>'#059669','bg'=>'#ecfdf5','bd'=>'#a7f3d0','txt'=>'#065f46','label'=>'POS'],
    'inventory'  => ['emoji'=>'📦','from'=>'#6366f1','to'=>'#4f46e5','bg'=>'#eef2ff','bd'=>'#c7d2fe','txt'=>'#3730a3','label'=>'Inventory'],
    'api'        => ['emoji'=>'🔌','from'=>'#475569','to'=>'#334155','bg'=>'#f8fafc','bd'=>'#e2e8f0','txt'=>'#475569','label'=>'API'],
    'default'    => ['emoji'=>'🗂️','from'=>'#6366f1','to'=>'#8b5cf6','bg'=>'#eef2ff','bd'=>'#c7d2fe','txt'=>'#4338ca','label'=>'App'],
];
// All unique types from current user's projects for filter
$allTypes = $projects->pluck('type')->unique()->filter()->values()->toArray();
@endphp

@push('head')
<style>
/* ─── Toolbar ─── */
.proj-toolbar {
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    padding:14px 0 2px;
}
.proj-search {
    display:flex;align-items:center;gap:8px;
    background:var(--surface-base);border:1.5px solid var(--border);
    border-radius:11px;padding:0 12px;flex:1;min-width:200px;max-width:340px;
    transition:border-color .13s,box-shadow .13s;
}
.proj-search:focus-within { border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-ring); }
.proj-search svg { color:var(--text-3);flex-shrink:0;width:15px;height:15px; }
.proj-search input { border:none;outline:none;background:transparent;font-size:13px;color:var(--text-1);padding:9px 0;width:100%;font-family:inherit; }
.proj-search input::placeholder { color:var(--text-3); }

.proj-sel {
    height:36px;border:1.5px solid var(--border);border-radius:10px;
    padding:0 10px;font-size:12.5px;color:var(--text-2);
    background:var(--surface-base);outline:none;cursor:pointer;
    transition:border-color .13s;font-family:inherit;
}
.proj-sel:focus { border-color:var(--brand); }

.proj-toggle-btn {
    width:36px;height:36px;border-radius:10px;
    border:1.5px solid var(--border);background:var(--surface-base);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;color:var(--text-3);transition:all .13s;flex-shrink:0;
}
.proj-toggle-btn:hover, .proj-toggle-btn.active {
    border-color:var(--brand);background:color-mix(in srgb,var(--brand) 8%,transparent);color:var(--brand);
}
.proj-toggle-btn svg { width:15px;height:15px; }

/* Results info */
.proj-info { font-size:12px;color:var(--text-3);padding:2px 0 10px; }

/* ─── Grid view ─── */
.proj-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:14px;
}
.proj-card {
    border-radius:16px;overflow:hidden;
    background:var(--surface-base);
    border:1px solid var(--border);
    border-left:3px solid transparent;
    box-shadow:var(--shadow);
    transition:box-shadow .22s ease,transform .22s ease,border-color .18s ease,background .18s ease;
    cursor:pointer;
}
.proj-card:hover {
    border-left-color: var(--c, var(--brand));
    box-shadow: 0 10px 36px color-mix(in srgb, var(--c, var(--brand)) 22%, transparent),
                0 2px 10px  color-mix(in srgb, var(--c, var(--brand)) 10%, transparent);
    transform: translateY(-3px);
}
.proj-card-header {
    height:76px;display:flex;align-items:center;justify-content:center;
    position:relative;border-bottom:1px solid var(--border);
    background:var(--surface-raised);
}
.proj-card-body { padding:16px 18px;display:flex;flex-direction:column;gap:10px; }
.proj-card-name { font-size:14px;font-weight:700;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.proj-card-desc { font-size:11.5px;color:var(--text-3);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.6; }
.proj-card-meta { display:flex;align-items:center;justify-content:space-between;font-size:10.5px;color:var(--text-3); }
.proj-card-actions { display:flex;gap:6px; }

/* ── Gap between toolbar and cards ── */
.proj-grid, .proj-list { margin-top:12px; }

/* ── Light colorful action button (inherits --c from card) ── */
.btn-card-action {
    display:inline-flex;align-items:center;justify-content:center;gap:6px;
    padding:8px 14px;border-radius:10px;
    font-size:12px;font-weight:700;text-decoration:none;
    background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff);
    color: var(--c, var(--brand));
    border: 1.5px solid color-mix(in srgb, var(--c, var(--brand)) 25%, transparent);
    transition: all .18s ease;
    flex: 1;
}
.btn-card-action:hover {
    background: var(--c, var(--brand));
    color: #fff;
    border-color: var(--c, var(--brand));
    box-shadow: 0 3px 10px color-mix(in srgb, var(--c, var(--brand)) 30%, transparent);
    transform: translateY(-1px);
}
.btn-card-action svg { width:12px;height:12px;stroke:currentColor;flex-shrink:0; }

/* ── Light ext link button ── */
.btn-ext-link {
    width:36px;height:36px;display:flex;align-items:center;justify-content:center;
    border:1.5px solid var(--border);border-radius:10px;
    color:var(--text-3);text-decoration:none;transition:all .15s;
}
.btn-ext-link:hover {
    background:var(--surface-raised);color:var(--text-1);border-color:var(--border-strong);
}

/* ─── List view ─── */
.proj-list { display:flex;flex-direction:column;gap:1px;border-radius:14px;overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow); }
.proj-list-row {
    display:flex;align-items:center;gap:14px;
    padding:13px 18px;background:var(--surface-base);
    box-shadow: inset 3px 0 0 transparent;
    transition:background .18s ease, box-shadow .2s ease;
    text-decoration:none;color:inherit;
}
.proj-list-row:hover {
    background: color-mix(in srgb, var(--c, var(--brand)) 4%, var(--surface-base));
    box-shadow: inset 3px 0 0 var(--c, var(--brand));
}
.proj-list-ico { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
.proj-list-name { font-size:13.5px;font-weight:700;color:var(--text-1);flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.proj-list-badge { font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;white-space:nowrap;flex-shrink:0; }
.proj-list-meta { font-size:11px;color:var(--text-3);flex-shrink:0;min-width:80px;text-align:right; }

/* ─── Empty / no-results ─── */
.proj-empty { padding:56px 24px;text-align:center;display:flex;flex-direction:column;align-items:center; }
.proj-empty-ico { width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:16px; }
.proj-empty h3 { font-size:16px;font-weight:800;color:var(--text-1);margin-bottom:6px; }
.proj-empty p  { font-size:13px;color:var(--text-3);margin-bottom:22px;max-width:360px;line-height:1.7; }
.proj-empty-btn {
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 24px;border-radius:12px;font-size:13.5px;font-weight:700;
    text-decoration:none;
    background: color-mix(in srgb, var(--brand) 10%, #fff);
    color: var(--brand);
    border: 1.5px solid color-mix(in srgb, var(--brand) 28%, transparent);
    transition:all .18s ease;
}
.proj-empty-btn:hover {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
    box-shadow: 0 4px 16px var(--brand-ring);
    transform:translateY(-1px);
}

/* Template chips in empty state */
.tpl-grid { display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:20px; }
.tpl-chip {
    display:inline-flex;align-items:center;gap:6px;
    padding:7px 14px;border-radius:99px;font-size:12px;font-weight:600;
    border:1.5px solid var(--border);background:var(--surface-base);
    color:var(--text-2);text-decoration:none;
    transition:all .13s;
}
.tpl-chip:hover { border-color:var(--brand);color:var(--brand);background:color-mix(in srgb,var(--brand) 6%,transparent); }

@media(max-width:600px) {
    .proj-toolbar { gap:8px; }
    .proj-search { max-width:100%;min-width:0;flex-basis:100%; }
}
</style>
@endpush

@section('content')
<div x-data="projectsPage()" x-init="init()">

    @if($projects->isEmpty())
    {{-- ═══ TRUE EMPTY STATE ══════════════════════════════════════════ --}}
    <div class="proj-empty">
        <div class="proj-empty-ico" style="background:linear-gradient(135deg,#eef2ff,#ede9fe);border:1px solid #c7d2fe;">
            <svg style="width:28px;height:28px;stroke:#6366f1" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <h3>Start building your first app</h3>
        <p>RyaanCMS uses AI to generate complete, production-ready applications from a single prompt. From eCommerce stores to hospital management systems.</p>
        <div class="tpl-grid">
            @foreach([
                ['e'=>'🛒','l'=>'eCommerce','t'=>'ecommerce'],
                ['e'=>'👥','l'=>'CRM','t'=>'crm'],
                ['e'=>'🏥','l'=>'Hospital','t'=>'hospital'],
                ['e'=>'🚀','l'=>'SaaS','t'=>'saas'],
                ['e'=>'🎓','l'=>'School ERP','t'=>'school'],
                ['e'=>'🍽️','l'=>'Restaurant','t'=>'restaurant'],
                ['e'=>'🏭','l'=>'ERP','t'=>'erp'],
                ['e'=>'📦','l'=>'Inventory','t'=>'inventory'],
            ] as $t)
            <a href="{{ route('projects.create') }}?template={{ $t['t'] }}" class="tpl-chip">{{ $t['e'] }} {{ $t['l'] }}</a>
            @endforeach
        </div>
        <a href="{{ route('projects.create') }}" class="proj-empty-btn">
            <svg style="width:15px;height:15px;stroke:currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Create First Project
        </a>
    </div>

    @else
    {{-- ═══ TOOLBAR ════════════════════════════════════════════════════ --}}
    <div class="proj-toolbar">
        {{-- Search --}}
        <div class="proj-search">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="Search projects…"
                   @input.debounce.150="filter()" aria-label="Search projects">
            <button x-show="search" @click="search='';filter()"
                    style="background:none;border:none;cursor:pointer;padding:0;color:var(--text-3);display:flex;" aria-label="Clear search">
                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Type filter --}}
        @if(count($allTypes) > 1)
        <select x-model="typeFilter" @change="filter()" class="proj-sel" aria-label="Filter by type">
            <option value="">All types</option>
            @foreach($allTypes as $t)
            @php $tInfo = $typeMap[$t] ?? $typeMap['default']; @endphp
            <option value="{{ $t }}">{{ $tInfo['emoji'] }} {{ $tInfo['label'] }}</option>
            @endforeach
        </select>
        @endif

        {{-- Status filter --}}
        <select x-model="statusFilter" @change="filter()" class="proj-sel" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>

        {{-- Sort --}}
        <select x-model="sortBy" @change="filter()" class="proj-sel" aria-label="Sort projects">
            <option value="recent">Recent</option>
            <option value="name">A → Z</option>
            <option value="name_desc">Z → A</option>
            <option value="oldest">Oldest first</option>
        </select>

        <div style="flex:1;"></div>

        {{-- View toggle --}}
        <div style="display:flex;gap:4px;">
            <button class="proj-toggle-btn" :class="{ active: view==='grid' }" @click="setView('grid')" title="Grid view" aria-label="Grid view">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </button>
            <button class="proj-toggle-btn" :class="{ active: view==='list' }" @click="setView('list')" title="List view" aria-label="List view">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Results info --}}
    <div class="proj-info" x-show="search || typeFilter || statusFilter">
        <span x-text="visibleCount + ' of {{ $projects->total() }} projects'"></span>
        <button x-show="search || typeFilter || statusFilter"
                @click="search='';typeFilter='';statusFilter='';filter()"
                style="margin-left:8px;font-size:11px;font-weight:600;color:var(--brand);background:none;border:none;cursor:pointer;padding:0;">
            Clear filters
        </button>
    </div>

    {{-- No search results --}}
    <div x-show="visibleCount === 0 && (search || typeFilter || statusFilter)"
         style="padding:48px 24px;text-align:center;">
        <div style="font-size:32px;margin-bottom:12px;">🔍</div>
        <div style="font-size:14px;font-weight:700;color:var(--text-1);margin-bottom:6px;">No projects match</div>
        <div style="font-size:12.5px;color:var(--text-3);margin-bottom:16px;">Try a different search term or remove filters.</div>
        <button @click="search='';typeFilter='';statusFilter='';filter()"
                style="padding:8px 20px;border-radius:10px;font-size:13px;font-weight:600;background:var(--brand);color:#fff;border:none;cursor:pointer;box-shadow:0 2px 8px var(--brand-ring);">
            Clear filters
        </button>
    </div>

    {{-- ═══ GRID VIEW ════════════════════════════════════════════════ --}}
    <div class="proj-grid" x-show="view==='grid' && visibleCount > 0">
        @foreach($projects as $project)
        @php $tc = $typeMap[$project->type] ?? $typeMap['default']; @endphp
        <div class="proj-card"
             data-name="{{ strtolower($project->name) }}"
             data-type="{{ $project->type }}"
             data-status="{{ $project->status }}"
             data-updated="{{ $project->updated_at->timestamp }}"
             data-created="{{ $project->created_at->timestamp }}"
             style="--c:{{ $tc['from'] }};">

            {{-- Header strip --}}
            <div class="proj-card-header">
                <span style="font-size:36px;select:none;" role="img" aria-label="{{ $tc['label'] }}">{{ $tc['emoji'] }}</span>
                {{-- Status badge --}}
                <div style="position:absolute;top:10px;right:10px;">
                    @if($project->status === 'active')
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:700;padding:2px 8px;border-radius:99px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">
                        <span style="width:5px;height:5px;border-radius:50%;background:#22c55e;display:inline-block;"></span>Active
                    </span>
                    @else
                    <span style="font-size:9.5px;font-weight:600;padding:2px 8px;border-radius:99px;background:var(--surface-overlay);color:var(--text-3);border:1px solid var(--border);">{{ ucfirst($project->status ?? 'Draft') }}</span>
                    @endif
                </div>
                {{-- Type badge --}}
                <div style="position:absolute;top:10px;left:10px;">
                    <span style="font-size:9.5px;font-weight:700;padding:2px 8px;border-radius:99px;background:{{ $tc['bg'] }};color:{{ $tc['txt'] }};border:1px solid {{ $tc['bd'] }};">{{ $tc['label'] }}</span>
                </div>
            </div>

            <div class="proj-card-body">
                <div style="display:flex;align-items:start;justify-content:space-between;gap:8px;">
                    <div class="proj-card-name">{{ $project->name }}</div>
                    {{-- Kebab menu --}}
                    <div class="relative flex-shrink-0" x-data="{ open: false }" @click.outside="open = false">
                        <button @click.stop="open=!open"
                                style="width:28px;height:28px;border-radius:8px;border:none;background:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center;justify-content:center;transition:background .12s;"
                                onmouseover="this.style.background='var(--surface-overlay)'" onmouseout="this.style.background=''"
                                aria-label="Project options" :aria-expanded="open">
                            <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             style="position:absolute;right:0;top:calc(100% + 4px);z-index:50;width:160px;border-radius:12px;padding:4px 0;background:var(--surface-base);border:1px solid var(--border);box-shadow:var(--shadow-lg);overflow:hidden;">
                            <a href="{{ route('projects.show', $project) }}"
                               style="display:flex;align-items:center;gap:8px;padding:9px 13px;font-size:12.5px;color:var(--text-2);text-decoration:none;transition:background .12s;"
                               onmouseover="this.style.background='var(--surface-raised)'" onmouseout="this.style.background=''">
                                <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View Details
                            </a>
                            <a href="{{ route('builder.show', $project) }}"
                               style="display:flex;align-items:center;gap:8px;padding:9px 13px;font-size:12.5px;color:var(--text-2);text-decoration:none;transition:background .12s;"
                               onmouseover="this.style.background='var(--surface-raised)'" onmouseout="this.style.background=''">
                                <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                Open Builder
                            </a>
                            <div style="height:1px;background:var(--border);margin:3px 0;"></div>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                  onsubmit="return confirm('Delete {{ addslashes($project->name) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="width:100%;display:flex;align-items:center;gap:8px;padding:9px 13px;font-size:12.5px;color:#ef4444;border:none;background:transparent;cursor:pointer;text-align:left;transition:background .12s;"
                                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                                    <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <p class="proj-card-desc">{{ $project->description ?: 'No description added.' }}</p>

                @if($project->tech_stack)
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    @foreach(array_slice($project->tech_stack, 0, 3) as $tech)
                    <span style="font-size:9.5px;font-weight:500;padding:2px 7px;border-radius:5px;background:var(--surface-overlay);color:var(--text-2);border:1px solid var(--border);">{{ $tech }}</span>
                    @endforeach
                    @if(count($project->tech_stack) > 3)
                    <span style="font-size:9.5px;padding:2px 7px;border-radius:5px;background:var(--surface-overlay);color:var(--text-3);">+{{ count($project->tech_stack) - 3 }}</span>
                    @endif
                </div>
                @endif

                <div class="proj-card-meta">
                    <span>{{ $project->files_count ?? 0 }} files</span>
                    <span>{{ $project->storage_used_formatted }}</span>
                    <span>{{ $project->updated_at->diffForHumans() }}</span>
                </div>

                <div class="proj-card-actions">
                    <a href="{{ route('builder.show', $project) }}" class="btn-card-action">
                        <svg fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        Open Builder
                    </a>
                    @if($project->deployment_url)
                    <a href="{{ $project->deployment_url }}" target="_blank" rel="noopener" class="btn-ext-link" title="Open live site">
                        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ LIST VIEW ════════════════════════════════════════════════ --}}
    <div class="proj-list" x-show="view==='list' && visibleCount > 0">
        @foreach($projects as $project)
        @php $tc = $typeMap[$project->type] ?? $typeMap['default']; @endphp
        <div class="proj-list-row"
             data-name="{{ strtolower($project->name) }}"
             data-type="{{ $project->type }}"
             data-status="{{ $project->status }}"
             data-updated="{{ $project->updated_at->timestamp }}"
             data-created="{{ $project->created_at->timestamp }}"
             style="--c:{{ $tc['from'] }};">
            <div class="proj-list-ico" style="background:{{ $tc['bg'] }};border:1px solid {{ $tc['bd'] }};">{{ $tc['emoji'] }}</div>
            <div style="flex:1;min-width:0;">
                <div class="proj-list-name">{{ $project->name }}</div>
                <div style="font-size:11px;color:var(--text-3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $project->description ?: 'No description' }}
                </div>
            </div>
            <span class="proj-list-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['txt'] }};border:1px solid {{ $tc['bd'] }};">{{ $tc['label'] }}</span>
            @if($project->status === 'active')
            <span style="font-size:9px;font-weight:700;padding:2px 8px;border-radius:99px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;flex-shrink:0;">Active</span>
            @else
            <span style="font-size:9px;font-weight:700;padding:2px 8px;border-radius:99px;background:var(--surface-overlay);color:var(--text-3);border:1px solid var(--border);flex-shrink:0;">{{ ucfirst($project->status ?? 'Draft') }}</span>
            @endif
            <div class="proj-list-meta">{{ $project->updated_at->diffForHumans() }}</div>
            <div style="display:flex;gap:5px;flex-shrink:0;">
                <a href="{{ route('builder.show', $project) }}" class="btn-card-action" style="flex:none;padding:5px 14px;">
                    Builder
                </a>
                <a href="{{ route('projects.show', $project) }}" class="btn-ext-link" style="width:32px;height:32px;">
                    <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div style="margin-top:16px;" x-show="visibleCount > 0">{{ $projects->links() }}</div>

    @endif

</div>
@endsection

@push('scripts')
<script>
function projectsPage() {
    return {
        search:       '',
        typeFilter:   '',
        statusFilter: '',
        sortBy:       'recent',
        view:         localStorage.getItem('proj_view') || 'grid',
        visibleCount: 0,

        init() {
            this.visibleCount = this.$el.querySelectorAll('[data-name]').length;
        },

        setView(v) {
            this.view = v;
            localStorage.setItem('proj_view', v);
            this.$nextTick(() => this.filter());
        },

        filter() {
            const q    = this.search.toLowerCase().trim();
            const type = this.typeFilter;
            const stat = this.statusFilter;
            const sort = this.sortBy;

            const cards = Array.from(this.$el.querySelectorAll('[data-name]'));
            let visible = [];

            cards.forEach(c => {
                const nameOk   = !q    || c.dataset.name.includes(q);
                const typeOk   = !type || c.dataset.type === type;
                const statusOk = !stat || c.dataset.status === stat;
                const show     = nameOk && typeOk && statusOk;
                c.style.display = show ? '' : 'none';
                if (show) visible.push(c);
            });

            this.visibleCount = visible.length;

            // Sort
            const containers = this.$el.querySelectorAll('.proj-grid, .proj-list');
            containers.forEach(container => {
                const rows = Array.from(container.querySelectorAll('[data-name]'));
                rows.sort((a, b) => {
                    if (sort === 'name')      return a.dataset.name.localeCompare(b.dataset.name);
                    if (sort === 'name_desc') return b.dataset.name.localeCompare(a.dataset.name);
                    if (sort === 'oldest')    return parseInt(a.dataset.created) - parseInt(b.dataset.created);
                    return parseInt(b.dataset.updated) - parseInt(a.dataset.updated); // recent
                });
                rows.forEach(r => container.appendChild(r));
            });
        },
    };
}
</script>
@endpush
