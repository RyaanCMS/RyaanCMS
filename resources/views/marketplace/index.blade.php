@extends('layouts.app')
@section('title', 'RyaanCMS Module Store')
@section('header', 'RyaanCMS Module Store')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('marketplace.index', ['tab' => 'plugins']) }}"
       class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium transition-all"
       style="border:1px solid var(--border); color:var(--text-2); background:var(--card-bg);"
       onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-bg)'">
        🎨 Templates
    </a>
    <a href="{{ route('marketplace.my-items') }}"
       class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium transition-all"
       style="border:1px solid var(--border); color:var(--text-2); background:var(--card-bg);"
       onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-bg)'">
        My Items
    </a>
    <a href="{{ route('marketplace.upload-install') }}"
       class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
       style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        Upload Package
    </a>
</div>
@endsection

@push('head')
<style>
/* ═══════════════════════════════════════════════
   Module Store — unified card design system
═══════════════════════════════════════════════ */

/* Search bar */
.ms-search {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 16px;
    box-shadow: var(--shadow);
    max-width: 440px;
}
.ms-search input {
    flex: 1; border: none; outline: none; font-size: 13.5px;
    color: var(--text-1); background: transparent; font-family: inherit;
}
.ms-search svg { color: #94a3b8; flex-shrink: 0; }

/* Tabs */
.ms-tabs {
    display: flex;
    gap: 2px;
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 4px;
    width: fit-content;
}
.ms-tab {
    padding: 7px 16px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    background: transparent;
    color: var(--text-2);
}
.ms-tab:hover:not(.ms-tab-active) { background: #fff; color: var(--text-1); }
.ms-tab-active {
    background: #fff !important;
    color: var(--text-1) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    font-weight: 600;
}

/* Section heading */
.ms-section-hd {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.ms-section-title {
    font-size: 13px; font-weight: 700; color: var(--text-1);
}
.ms-section-hint {
    font-size: 11.5px; color: var(--text-3);
}
.ms-cat-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: #94a3b8;
    padding: 0 2px; margin-bottom: 10px;
}

/* ══ THE UNIFIED CARD ══
   Same structure everywhere.
   .ms-card      = standard size (category lists, agents)
   .ms-card-lg   = featured/popular size (larger, more prominent)
*/
.ms-card, .ms-card-lg {
    background: color-mix(in srgb, var(--surface-base) 96%, #f8fafc);
    border: 1px solid var(--border);
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    transition: box-shadow .18s, border-color .18s, transform .18s, background .18s;
    overflow: hidden;
    aspect-ratio: 1;
}
.ms-card:hover    { background:#fff; box-shadow: 0 6px 22px rgba(15,23,42,.055); border-color: #dbe2ea; transform: translateY(-2px); }
.ms-card-lg:hover { background:#fff; box-shadow: 0 8px 28px rgba(15,23,42,.065); border-color: #dbe2ea; transform: translateY(-2px); }

/* Card top accent stripe */
.ms-card-stripe { height: 3px; width: 100%; flex-shrink: 0; }

/* Card body */
.ms-card .ms-body   { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 7px; min-height: 0; }
.ms-card-lg .ms-body{ padding: 16px 18px; flex: 1; display: flex; flex-direction: column; gap: 9px; min-height: 0; }

/* Icon sizes */
.ms-card    .ms-ico { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.ms-card-lg .ms-ico { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }

/* Name */
.ms-card    .ms-name { font-size: 13px;   font-weight: 650; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ms-card-lg .ms-name { font-size: 14px;   font-weight: 680; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Description — 2-line clamp to fit square */
.ms-card    .ms-desc { font-size: 11.5px; color: #94a3b8; line-height: 1.55; font-weight:400; flex: 1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.ms-card-lg .ms-desc { font-size: 12.5px; color: #94a3b8; line-height: 1.55; font-weight:400; flex: 1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }

/* Tag row */
.ms-tags { display: flex; flex-wrap: wrap; gap: 4px; }
.ms-tag  {
    font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 5px;
    background: #f8fafc; color: #94a3b8; border: 1px solid #edf2f7;
}

/* Badges */
.ms-badge-free    { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.ms-badge-pro     { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; background:#fdf4ff; color:#6d28d9; border:1px solid #e9d5ff; }
.ms-badge-tokens  { font-size:10px; font-weight:600; padding:2px 8px; border-radius:99px; background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; }
.ms-badge-agent   { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; }

/* Card footer */
.ms-card    .ms-foot { padding: 0 16px 14px; flex-shrink: 0; }
.ms-card-lg .ms-foot { padding: 0 18px 16px; flex-shrink: 0; }

/* Action buttons */
.ms-btn-install {
    width: 100%; padding: 7px 0; border-radius: 9px; font-size: 12px; font-weight: 700;
    color: #fff; border: none; cursor: pointer; transition: all .15s;
    background: var(--brand); box-shadow: 0 2px 8px var(--brand-ring);
}
.ms-btn-install:hover { filter: brightness(1.08); transform: translateY(-1px); }
.ms-btn-install-lg {
    width: 100%; padding: 9px 0; border-radius: 10px; font-size: 13px; font-weight: 700;
    color: #fff; border: none; cursor: pointer; transition: all .15s;
    background: var(--brand); box-shadow: 0 2px 8px var(--brand-ring);
}
.ms-btn-install-lg:hover { filter: brightness(1.08); transform: translateY(-1px); }
.ms-btn-installed {
    width: 100%; padding: 7px 0; border-radius: 9px; font-size: 12px; font-weight: 700;
    color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; cursor: pointer;
    transition: all .13s;
}
.ms-btn-installed:hover { background: #dcfce7; }
.ms-btn-installed-lg {
    width: 100%; padding: 9px 0; border-radius: 10px; font-size: 13px; font-weight: 700;
    color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; cursor: pointer;
    transition: all .13s;
}
.ms-btn-installed-lg:hover { background: #dcfce7; }

/* Grids — auto-fill keeps each card ~project-card size */
.ms-grid-lg { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; }
.ms-grid    { display: grid; grid-template-columns: repeat(auto-fill, minmax(225px, 1fr)); gap: 14px; }

/* Agent tier badge colors */
.tier-free    { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.tier-cheap   { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.tier-premium { background:#fdf4ff; color:#6d28d9; border:1px solid #e9d5ff; }

/* Category pills */
.ms-cat-pills { display: flex; flex-wrap: wrap; gap: 6px; }
.ms-cat-pill {
    padding: 5px 14px; border-radius: 99px; font-size: 12px; font-weight: 500;
    text-decoration: none; transition: all .13s; border: 1px solid var(--border);
    color: var(--text-2); background: #f8fafc;
}
.ms-cat-pill:hover      { background: #eef2ff; color: #4338ca; border-color: #c7d2fe; }
.ms-cat-pill-active     { background: var(--brand); color: #fff; border-color: var(--brand); box-shadow: 0 2px 8px var(--brand-ring); }

/* Stats strip */
.ms-stats {
    display: flex; align-items: center; gap: 20px;
    padding: 12px 18px; background: #f8fafc;
    border: 1px solid var(--border); border-radius: 12px;
    margin-bottom: 22px;
}
.ms-stat-item { display: flex; align-items: center; gap: 8px; }
.ms-stat-ico  { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 13px; }
.ms-stat-val  { font-size: 16px; font-weight: 800; color: var(--text-1); letter-spacing: -.02em; }
.ms-stat-lbl  { font-size: 11px; color: var(--text-3); }
.ms-stat-sep  { width: 1px; height: 28px; background: var(--border); }

@media (max-width: 600px) {
    .ms-grid-lg, .ms-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 360px) {
    .ms-grid-lg, .ms-grid { grid-template-columns: 1fr; }
}

/* ─── Override ms-card/ms-card-lg with sys-card pattern ─── */
.ms-card, .ms-card-lg {
    border-left: 3px solid transparent !important;
    transition: box-shadow .22s ease, transform .22s ease, border-color .18s ease !important;
}
.ms-card:hover, .ms-card-lg:hover {
    border-left-color: var(--c, var(--brand)) !important;
    box-shadow: 0 10px 28px color-mix(in srgb, var(--c, var(--brand)) 12%, transparent),
                0 2px 8px  color-mix(in srgb, var(--c, var(--brand)) 7%, transparent) !important;
    transform: translateY(-3px) !important;
    border-color: var(--border) !important;
}
/* Card icon: use brand-light bg */
.ms-ico {
    background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff) !important;
    border: 1px solid color-mix(in srgb, var(--c, var(--brand)) 20%, transparent) !important;
}
/* Install buttons → light colorful */
.ms-btn-install, .ms-btn-install-lg {
    background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff) !important;
    color: var(--c, var(--brand)) !important;
    border: 1.5px solid color-mix(in srgb, var(--c, var(--brand)) 25%, transparent) !important;
    box-shadow: none !important;
}
.ms-btn-install:hover, .ms-btn-install-lg:hover {
    background: var(--c, var(--brand)) !important;
    color: #fff !important;
    border-color: var(--c, var(--brand)) !important;
    box-shadow: 0 3px 10px color-mix(in srgb, var(--c, var(--brand)) 30%, transparent) !important;
    filter: none !important;
}
/* Tab active state */
.ms-tab-active { border-left: 2px solid var(--brand) !important; color: var(--brand) !important; }
/* Category pills active */
.ms-cat-pill-active { background: var(--brand) !important; color: #fff !important; border-color: var(--brand) !important; }
/* Stats strip */
.ms-stats { background: var(--surface-raised) !important; border-radius: 14px !important; }

/* Final card tone pass: lighter, cleaner text hierarchy */
.ms-card .ms-name,
.ms-card-lg .ms-name {
    color:#64748b !important;
    font-weight:650 !important;
    letter-spacing:0 !important;
}
.ms-card .ms-desc,
.ms-card-lg .ms-desc {
    color:#a0aec0 !important;
    font-weight:400 !important;
}
.ms-card .ms-tag,
.ms-card-lg .ms-tag {
    background:#fbfdff !important;
    color:#9aa8b8 !important;
    border-color:#edf2f7 !important;
}
.ms-card:hover .ms-name,
.ms-card-lg:hover .ms-name {
    color:#475569 !important;
}
</style>
@endpush

@section('content')
<div x-data="marketplace()" x-init="init()">

{{-- ── Top bar: search + stats ──────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('marketplace.index') }}" class="ms-search">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search modules, agents, plugins…">
        <button type="submit"
                style="padding:5px 14px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:var(--brand);border:none;cursor:pointer;">
            Search
        </button>
    </form>
    <div class="ms-stats" style="margin-bottom:0;">
        <div class="ms-stat-item">
            <div class="ms-stat-ico" style="background:#eef2ff;">🧩</div>
            <div>
                <div class="ms-stat-val">{{ count($modules) }}</div>
                <div class="ms-stat-lbl">Modules</div>
            </div>
        </div>
        <div class="ms-stat-sep"></div>
        <div class="ms-stat-item">
            <div class="ms-stat-ico" style="background:#fdf4ff;">🤖</div>
            <div>
                <div class="ms-stat-val">{{ count($agents) }}</div>
                <div class="ms-stat-lbl">AI Agents</div>
            </div>
        </div>
        <div class="ms-stat-sep"></div>
        <div class="ms-stat-item">
            <div class="ms-stat-ico" style="background:#fdf4ff;">🎨</div>
            <div>
                <div class="ms-stat-val">{{ count($builtinTemplates) }}</div>
                <div class="ms-stat-lbl">Templates</div>
            </div>
        </div>
        <div class="ms-stat-sep"></div>
        <div class="ms-stat-item">
            <div class="ms-stat-ico" style="background:#ecfdf5;">⚡</div>
            <div>
                <div class="ms-stat-val">0</div>
                <div class="ms-stat-lbl">Tokens for CRUD</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Tab Navigation ───────────────────────────────────────────────── --}}
<div style="margin-bottom:24px;">
    <div class="ms-tabs">
        <button @click="tab='modules'"
                :class="tab==='modules' ? 'ms-tab ms-tab-active' : 'ms-tab'">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Modules
        </button>
        <button @click="tab='agents'"
                :class="tab==='agents' ? 'ms-tab ms-tab-active' : 'ms-tab'">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            AI Agents
        </button>
        <button @click="tab='marketplace'"
                :class="tab==='marketplace' ? 'ms-tab ms-tab-active' : 'ms-tab'">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Plugins & Templates
        </button>
    </div>
</div>

{{-- ════════════════════════════════════════
     TAB: MODULES
════════════════════════════════════════ --}}
<div x-show="tab === 'modules'" x-cloak>

    @php
        $popular  = collect($modules)->filter(fn($m) => $m['popular'] ?? false)->values();
        $grouped  = [];
        foreach ($modules as $key => $module) {
            $grouped[$module['category']][] = array_merge($module, ['key' => $key]);
        }
        ksort($grouped);
        $categoryLabels = [
            'core'=>'Core','billing'=>'Billing','communication'=>'Communication',
            'analytics'=>'Analytics','business'=>'Business','ecommerce'=>'eCommerce',
            'compliance'=>'Compliance','integration'=>'Integration','utility'=>'Utility','saas'=>'SaaS',
        ];
        $categoryColors = [
            'core'=>'#6366f1','billing'=>'#10b981','communication'=>'#0ea5e9',
            'analytics'=>'#8b5cf6','business'=>'#f97316','ecommerce'=>'#059669',
            'compliance'=>'#64748b','integration'=>'#ec4899','utility'=>'#d97706','saas'=>'#6366f1',
        ];
    @endphp

    {{-- Popular / Featured — large cards --}}
    @if($popular->count())
    <div style="margin-bottom:32px;">
        <div class="ms-section-hd">
            <span class="ms-section-title">Popular Modules</span>
            <span class="ms-section-hint">0 AI tokens — pure PHP generation</span>
        </div>
        <div class="ms-grid-lg">
            @foreach($modules as $key => $module)
            @if($module['popular'] ?? false)
            @php $color = $categoryColors[$module['category']] ?? '#6366f1'; @endphp
            <div class="ms-card-lg" style="--c:{{ $color }}">
                <div class="ms-card-stripe" style="background:linear-gradient(90deg,{{ $color }},{{ $color }}88);"></div>
                <div class="ms-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                        <div class="ms-ico" style="background:{{ $color }}12;border:1px solid {{ $color }}25;">
                            {{ $module['icon'] }}
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                            <span class="ms-badge-free">Free</span>
                            <span class="ms-badge-tokens">0 tokens</span>
                        </div>
                    </div>
                    <div>
                        <div class="ms-name">{{ $module['name'] }}</div>
                        <div class="ms-desc" style="margin-top:4px;">{{ $module['description'] }}</div>
                    </div>
                    <div class="ms-tags">
                        @foreach(array_slice($module['features'], 0, 3) as $f)
                        <span class="ms-tag">{{ str_replace('_',' ',$f) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="ms-foot">
                    @if($installedKeys->contains($key))
                    @php $mgInstalls = $installedModules->get($key, collect())->toArray(); @endphp
                    <button class="ms-btn-installed-lg"
                            @click="openManageModal('{{ $key }}', '{{ addslashes($module['name']) }}', '{{ addslashes(json_encode($mgInstalls)) }}')">
                        ✓ Installed — Manage
                    </button>
                    @else
                    <button class="ms-btn-install-lg"
                            @click="openInstallModal('{{ $key }}', '{{ addslashes($module['name']) }}')">
                        Install Module
                    </button>
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- All modules by category — standard cards --}}
    @foreach($grouped as $category => $mods)
    @php $catColor = $categoryColors[$category] ?? '#6366f1'; @endphp
    <div style="margin-bottom:28px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <span style="width:3px;height:14px;border-radius:99px;background:{{ $catColor }};display:inline-block;"></span>
            <span class="ms-cat-label" style="margin-bottom:0;">{{ $categoryLabels[$category] ?? ucfirst($category) }}</span>
        </div>
        <div class="ms-grid">
            @foreach($mods as $mod)
            @php $c = $categoryColors[$category] ?? '#6366f1'; @endphp
            <div class="ms-card" style="--c:{{ $c }}">
                <div class="ms-card-stripe" style="background:{{ $c }};opacity:.5;"></div>
                <div class="ms-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;">
                        <div class="ms-ico" style="background:{{ $c }}10;border:1px solid {{ $c }}20;">
                            {{ $mod['icon'] }}
                        </div>
                        <span class="ms-badge-free" style="flex-shrink:0;">Free</span>
                    </div>
                    <div>
                        <div class="ms-name">{{ $mod['name'] }}</div>
                        <div class="ms-desc" style="margin-top:3px;">{{ Str::limit($mod['description'], 70) }}</div>
                    </div>
                    <div class="ms-tags">
                        @foreach(array_slice($mod['generates'] ?? [], 0, 2) as $g)
                        <span class="ms-tag">{{ $g }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="ms-foot">
                    @if($installedKeys->contains($mod['key']))
                    @php $mgInstalls2 = $installedModules->get($mod['key'], collect())->toArray(); @endphp
                    <button class="ms-btn-installed"
                            @click="openManageModal('{{ $mod['key'] }}', '{{ addslashes($mod['name']) }}', '{{ addslashes(json_encode($mgInstalls2)) }}')">
                        ✓ Manage
                    </button>
                    @else
                    <button class="ms-btn-install"
                            @click="openInstallModal('{{ $mod['key'] }}', '{{ addslashes($mod['name']) }}')">
                        Install →
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- ════════════════════════════════════════
     TAB: AI AGENTS
════════════════════════════════════════ --}}
<div x-show="tab === 'agents'" x-cloak>
    <div style="margin-bottom:24px;">
        <div class="ms-section-hd">
            <span class="ms-section-title">AI Agents</span>
            <span class="ms-section-hint">Specialised agents using the cheapest capable model</span>
        </div>
    </div>
    <div class="ms-grid-lg">
        @foreach($agents as $key => $agent)
        @php
            $tierClass = match($agent['model_tier'] ?? 'free') {
                'free'    => 'tier-free',
                'cheap'   => 'tier-cheap',
                'premium' => 'tier-premium',
                default   => '',
            };
            $agentColor = match($agent['model_tier'] ?? 'free') {
                'premium' => '#8b5cf6',
                'cheap'   => '#3b82f6',
                default   => '#10b981',
            };
        @endphp
        <div class="ms-card-lg" style="--c:{{ $agentColor }}">
            <div class="ms-card-stripe" style="background:linear-gradient(90deg,{{ $agentColor }},{{ $agentColor }}66);"></div>
            <div class="ms-body">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                    <div class="ms-ico" style="background:{{ $agentColor }}12;border:1px solid {{ $agentColor }}25;">
                        {{ $agent['icon'] }}
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                        <span class="ms-badge-agent {{ $tierClass }}" style="text-transform:capitalize;">
                            {{ $agent['model_tier'] ?? 'free' }} model
                        </span>
                        @if($agent['free'])
                        <span class="ms-badge-free">Free</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="ms-name">{{ $agent['name'] }}</div>
                    <div class="ms-desc" style="margin-top:4px;">{{ $agent['description'] }}</div>
                </div>
            </div>
            <div class="ms-foot">
                <button class="ms-btn-install-lg"
                        @click="openInstallModal('agent:{{ $key }}', '{{ addslashes($agent['name']) }}')">
                    Activate Agent
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ════════════════════════════════════════
     TAB: PLUGINS & TEMPLATES
════════════════════════════════════════ --}}
<div x-show="tab === 'marketplace'" x-cloak>

    {{-- ── Built-in Templates ──────────────────────────────────────────────── --}}
    @if(count($builtinTemplates))
    <div style="margin-bottom:32px;">
        <div class="ms-section-hd">
            <span class="ms-section-title">Built-in Templates</span>
            <span class="ms-section-hint">Ready-to-use — apply to any project instantly</span>
        </div>
        <div class="ms-grid-lg">
            @foreach($builtinTemplates as $tKey => $tpl)
            @php $tColor = $tpl['color'] ?? '#6366f1'; @endphp
            <div class="ms-card-lg" style="--c:{{ $tColor }}">
                <div class="ms-card-stripe" style="background:linear-gradient(90deg,{{ $tColor }},{{ $tColor }}88);"></div>
                <div class="ms-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                        <div class="ms-ico" style="background:{{ $tColor }}12;border:1px solid {{ $tColor }}25;font-size:22px;">
                            {{ $tpl['icon'] }}
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                            <span class="ms-badge-free">Built-in</span>
                            <span class="ms-badge-tokens">Free</span>
                        </div>
                    </div>
                    <div>
                        <div class="ms-name">{{ $tpl['name'] }}</div>
                        <div class="ms-desc" style="margin-top:4px;">{{ $tpl['description'] }}</div>
                    </div>
                    <div class="ms-tags">
                        @foreach(array_slice($tpl['tags'] ?? [], 0, 4) as $tag)
                        <span class="ms-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="ms-foot" style="display:flex;gap:8px;">
                    <button class="ms-btn-install-lg"
                            style="flex:1;"
                            @click="openInstallModal('{{ $tKey }}', '{{ addslashes($tpl['name']) }}')">
                        Apply to Project
                    </button>
                    <a href="{{ route('marketplace.template.download', $tKey) }}"
                       title="Download ZIP"
                       style="display:flex;align-items:center;justify-content:center;width:40px;border-radius:10px;font-size:14px;border:1px solid var(--border);background:var(--hover-bg);color:var(--text-2);text-decoration:none;flex-shrink:0;transition:all .13s;"
                       onmouseover="this.style.borderColor='var(--brand)';this.style.color='var(--brand)';"
                       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-2)';">
                        ⬇
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Featured — large cards --}}
    @if($featured->isNotEmpty() && !request('search'))
    <div style="margin-bottom:32px;">
        <div class="ms-section-hd">
            <span class="ms-section-title">Featured</span>
        </div>
        <div class="ms-grid-lg">
            @foreach($featured as $item)
            @php
                $ico = match($item->type) { 'module'=>'📦','theme'=>'🎨','agent'=>'🤖','template'=>'📄', default=>'🔌' };
            @endphp
            <a href="{{ route('marketplace.show', $item) }}" class="ms-card-lg" style="--c:#6366f1;text-decoration:none;color:inherit;">
                <div class="ms-card-stripe" style="background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
                <div class="ms-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                        <div class="ms-ico" style="background:#eef2ff;border:1px solid #c7d2fe;">{{ $ico }}</div>
                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;flex-shrink:0;">{{ $item->category }}</span>
                    </div>
                    <div>
                        <div class="ms-name">{{ $item->name }}</div>
                        <div class="ms-desc" style="margin-top:4px;">{{ Str::limit($item->description, 90) }}</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
                        <span style="font-size:14px;font-weight:800;color:{{ $item->is_free ? '#16a34a' : '#0f172a' }};">{{ $item->price_formatted }}</span>
                        <span style="font-size:11px;color:#94a3b8;">↓ {{ number_format($item->downloads) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Category pills --}}
    <div style="margin-bottom:20px;">
        <div class="ms-cat-pills">
            <a href="{{ route('marketplace.index', request()->except('category')) }}"
               class="ms-cat-pill {{ !request('category') ? 'ms-cat-pill-active' : '' }}">All</a>
            @foreach($categories as $key => $label)
            <a href="{{ route('marketplace.index', array_merge(request()->all(), ['category' => $key])) }}"
               class="ms-cat-pill {{ request('category') === $key ? 'ms-cat-pill-active' : '' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- All items — standard cards --}}
    <div class="ms-grid">
        @forelse($items as $item)
        @php $ico = match($item->type) { 'module'=>'📦','theme'=>'🎨','agent'=>'🤖','template'=>'📄', default=>'🔌' }; @endphp
        <a href="{{ route('marketplace.show', $item) }}" class="ms-card" style="--c:#6366f1;text-decoration:none;color:inherit;">
            <div class="ms-card-stripe" style="background:#6366f1;opacity:.4;"></div>
            <div class="ms-body">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;">
                    <div class="ms-ico" style="background:#f1f5f9;border:1px solid #e2e8f0;">
                        {{ $item->icon ?? $ico }}
                    </div>
                    <span class="{{ $item->is_free ? 'ms-badge-free' : 'ms-badge-tokens' }}" style="flex-shrink:0;">
                        {{ $item->price_formatted }}
                    </span>
                </div>
                <div>
                    <div class="ms-name">{{ $item->name }}</div>
                    <div class="ms-desc" style="margin-top:3px;">{{ Str::limit($item->description, 70) }}</div>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:10.5px;color:#94a3b8;">{{ $item->category }}</span>
                    <span style="font-size:10px;color:#94a3b8;">v{{ $item->version }}</span>
                </div>
            </div>
        </a>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:56px 24px;">
            <div style="width:52px;height:52px;border-radius:14px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <svg style="width:24px;height:24px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p style="font-size:13.5px;font-weight:700;color:#1e293b;margin-bottom:4px;">No items found</p>
            <p style="font-size:12px;color:#94a3b8;">Try a different search or category filter</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $items->withQueryString()->links() }}</div>
</div>

{{-- ════════════════════════════════════════
     INSTALL MODAL (unchanged logic)
════════════════════════════════════════ --}}
<div x-show="modalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="closeModal()">
    <div class="absolute inset-0 backdrop-blur-sm" style="background:rgba(0,0,0,.35);" @click="closeModal()"></div>
    <div class="relative w-full max-w-md rounded-2xl p-6"
         style="background:#fff; border:1px solid #e2e8f0; box-shadow:0 20px 60px rgba(0,0,0,.15);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <button @click="closeModal()"
                style="position:absolute;top:16px;right:16px;width:28px;height:28px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#94a3b8;"
                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:2px;" x-text="'Install ' + modalModuleName"></h3>
        <p style="font-size:12.5px;color:#94a3b8;margin-bottom:20px;">Select a project to install this module into</p>

        <div x-show="installSuccess" class="space-y-4">
            <div style="border-radius:12px;padding:16px;text-align:center;background:#f0fdf4;border:1px solid #bbf7d0;">
                <p style="font-size:24px;margin-bottom:8px;">✅</p>
                <p style="font-size:13px;font-weight:600;color:#15803d;" x-text="successMessage"></p>
                <p style="font-size:11.5px;color:#16a34a;margin-top:4px;">0 AI tokens used — pure PHP generation</p>
            </div>
            <div x-show="menuItems.length > 0" style="border-radius:12px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;">
                <p style="font-size:11px;font-weight:700;color:#475569;margin-bottom:8px;">📌 Add to your navigation:</p>
                <div class="space-y-2">
                    <template x-for="item in menuItems" :key="item.route">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span x-text="item.icon"></span>
                                <span style="font-size:12.5px;font-weight:500;color:#1e293b;" x-text="item.label"></span>
                            </div>
                            <code style="font-size:10.5px;padding:2px 7px;border-radius:5px;background:color-mix(in srgb,var(--brand) 8%,#fff);color:var(--brand);font-family:monospace;" x-text="item.route"></code>
                        </div>
                    </template>
                </div>
                <p style="font-size:11px;color:#94a3b8;margin-top:10px;">
                    Go to <a href="{{ route('menus.index') }}" style="font-weight:600;color:var(--brand);text-decoration:underline;">Menu Management</a> to add these routes.
                </p>
            </div>
            <button @click="closeModal()"
                    style="width:100%;padding:9px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;cursor:pointer;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                Close
            </button>
        </div>

        <div x-show="installError" style="margin-bottom:14px;padding:12px 14px;border-radius:10px;font-size:12.5px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;" x-text="errorMessage"></div>

        <div x-show="!installSuccess">
            @if($projects->count())
            <div class="space-y-3">
                <div>
                    <label style="display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px;">Select Project</label>
                    <select x-model="selectedProject"
                            style="width:100%;padding:9px 13px;border-radius:10px;font-size:13.5px;border:1.5px solid #e2e8f0;color:#0f172a;background:#fff;outline:none;appearance:none;cursor:pointer;font-family:inherit;"
                            onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">— Choose a project —</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button @click="doInstall()" :disabled="!selectedProject || installing"
                        style="width:100%;padding:10px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .15s;"
                        :style="(!selectedProject || installing) ? 'opacity:.5;cursor:not-allowed;background:var(--brand);' : 'background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);cursor:pointer;'">
                    <span x-show="!installing">Install Module — 0 AI Tokens</span>
                    <span x-show="installing" style="display:flex;align-items:center;gap:8px;">
                        <svg class="animate-spin" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Installing…
                    </span>
                </button>
            </div>
            @else
            <div style="text-align:center;padding:24px 0;">
                <p style="font-size:13px;color:#64748b;margin-bottom:14px;">You don't have any projects yet.</p>
                <a href="{{ route('projects.create') }}"
                   style="display:inline-block;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;text-decoration:none;background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                    Create a project
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     MANAGE MODAL (toggle / uninstall per project)
════════════════════════════════════════ --}}
<div x-show="manageOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="closeManage()">
    <div class="absolute inset-0 backdrop-blur-sm" style="background:rgba(0,0,0,.35);" @click="closeManage()"></div>
    <div class="relative w-full max-w-lg rounded-2xl"
         style="background:#fff; border:1px solid #e2e8f0; box-shadow:0 20px 60px rgba(0,0,0,.15); max-height:80vh; overflow:hidden; display:flex; flex-direction:column;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div style="padding:20px 24px 16px; border-bottom:1px solid #f1f5f9; flex-shrink:0;">
            <button @click="closeManage()"
                    style="position:absolute;top:16px;right:16px;width:28px;height:28px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#94a3b8;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:2px;" x-text="'Manage: ' + manageName"></h3>
            <p style="font-size:12px;color:#94a3b8;">Toggle or uninstall per project below</p>
        </div>

        {{-- Feedback bar --}}
        <div x-show="manageFeedback"
             :style="manageFeedbackOk ? 'background:#f0fdf4;border-color:#bbf7d0;color:#15803d;' : 'background:#fef2f2;border-color:#fecaca;color:#991b1b;'"
             style="margin:12px 24px 0;padding:10px 14px;border-radius:10px;font-size:12.5px;border:1px solid;"
             x-text="manageFeedback"></div>

        {{-- Project rows --}}
        <div style="overflow-y:auto;flex:1;padding:16px 24px;">
            <template x-if="manageInstalls.length === 0">
                <p style="text-align:center;padding:32px 0;font-size:13px;color:#94a3b8;">No installations found.</p>
            </template>
            <template x-for="(inst, idx) in manageInstalls" :key="inst.pm_id">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="min-width:0;flex:1;">
                        <p style="font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="inst.project_name"></p>
                        <p style="font-size:11px;margin-top:2px;"
                           :style="inst.active ? 'color:#15803d;' : 'color:#ef4444;'"
                           x-text="inst.active ? '● Active' : '○ Inactive'"></p>
                    </div>
                    <div style="display:flex;gap:8px;flex-shrink:0;">
                        <button @click="doToggle(inst.project_id, inst.status, idx)"
                                :disabled="manageWorking"
                                :style="inst.active
                                    ? 'background:#fef9c3;color:#a16207;border:1px solid #fde68a;'
                                    : 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;'"
                                style="padding:5px 14px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .13s;"
                                x-text="inst.active ? 'Deactivate' : 'Activate'">
                        </button>
                        <button @click="doUninstall(inst.project_id, idx)"
                                :disabled="manageWorking"
                                style="padding:5px 12px;border-radius:8px;font-size:11.5px;font-weight:700;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;cursor:pointer;transition:all .13s;"
                                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                            Uninstall
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div style="padding:14px 24px;border-top:1px solid #f1f5f9;flex-shrink:0;">
            <button @click="closeManage()"
                    style="width:100%;padding:9px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;cursor:pointer;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                Close
            </button>
        </div>
    </div>
</div>

</div>{{-- /x-data --}}

<script>
function marketplace() {
    return {
        tab: 'modules',
        modalOpen: false, modalModuleKey: '', modalModuleName: '',
        selectedProject: '', installing: false,
        installSuccess: false, installError: false,
        successMessage: '', errorMessage: '', menuItems: [],

        manageOpen: false, manageKey: '', manageName: '', manageInstalls: [],
        manageWorking: false, manageFeedback: '', manageFeedbackOk: true,

        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('search') || params.has('category') || params.get('tab') === 'plugins') {
                this.tab = 'marketplace';
            }
        },

        openInstallModal(key, name) {
            this.modalModuleKey  = key;   this.modalModuleName = name;
            this.selectedProject = '';    this.installing      = false;
            this.installSuccess  = false; this.installError    = false;
            this.successMessage  = '';    this.errorMessage    = '';
            this.modalOpen       = true;
        },

        closeModal() { this.modalOpen = false; },

        openManageModal(key, name, installsJson) {
            this.manageKey      = key;
            this.manageName     = name;
            this.manageInstalls = JSON.parse(installsJson);
            this.manageFeedback = '';
            this.manageOpen     = true;
        },

        closeManage() { this.manageOpen = false; },

        async doInstall() {
            if (!this.selectedProject || this.installing) return;
            this.installing = true; this.installError = false;
            try {
                const res  = await fetch(`/marketplace/modules/${this.modalModuleKey}/install`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', 'Accept':'application/json' },
                    body: JSON.stringify({ project_id: this.selectedProject }),
                });
                const data = await res.json();
                if (data.success) { this.installSuccess = true; this.successMessage = data.message || 'Installed!'; this.menuItems = data.menu_items || []; }
                else              { this.installError = true; this.errorMessage = data.message || 'Installation failed.'; }
            } catch { this.installError = true; this.errorMessage = 'Network error — please try again.'; }
            finally   { this.installing = false; }
        },

        async doToggle(projectId, currentStatus, idx) {
            this.manageWorking = true;
            try {
                const res = await fetch(`/marketplace/modules/${this.manageKey}/toggle`, {
                    method: 'PATCH',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', 'Accept':'application/json' },
                    body: JSON.stringify({ project_id: projectId }),
                });
                const data = await res.json();
                if (data.success) {
                    this.manageInstalls[idx].status = data.status;
                    this.manageInstalls[idx].active = data.active;
                    this.manageFeedback    = data.message;
                    this.manageFeedbackOk  = true;
                } else {
                    this.manageFeedback   = data.message || 'Toggle failed.';
                    this.manageFeedbackOk = false;
                }
            } catch {
                this.manageFeedback   = 'Network error — please try again.';
                this.manageFeedbackOk = false;
            }
            this.manageWorking = false;
        },

        async doUninstall(projectId, idx) {
            if (!confirm('Uninstall this module from the project? This cannot be undone.')) return;
            this.manageWorking = true;
            try {
                const res = await fetch(`/marketplace/modules/${this.manageKey}/uninstall`, {
                    method: 'DELETE',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', 'Accept':'application/json' },
                    body: JSON.stringify({ project_id: projectId }),
                });
                const data = await res.json();
                if (data.success) {
                    this.manageInstalls.splice(idx, 1);
                    this.manageFeedback   = data.message;
                    this.manageFeedbackOk = true;
                    if (this.manageInstalls.length === 0) { setTimeout(() => { this.manageOpen = false; }, 1200); }
                } else {
                    this.manageFeedback   = data.message || 'Uninstall failed.';
                    this.manageFeedbackOk = false;
                }
            } catch {
                this.manageFeedback   = 'Network error — please try again.';
                this.manageFeedbackOk = false;
            }
            this.manageWorking = false;
        },
    };
}
</script>
@endsection
