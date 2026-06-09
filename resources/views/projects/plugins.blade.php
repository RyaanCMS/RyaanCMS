@extends('layouts.app')
@section('title', 'Plugins — ' . $project->name)
@section('header', $project->name . ' — Plugins')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('projects.show', $project) }}"
       class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-all"
       style="border:1px solid var(--border);color:var(--text-2);background:var(--card-bg);"
       onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-bg)'">
        ← Project
    </a>
    <a href="{{ route('marketplace.index') }}"
       class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
       style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
        + Add New Plugin
    </a>
</div>
@endsection

@push('head')
<style>
:root {
    --card-bg:#fff;--border:#e8ecf0;--text-1:#0f172a;--text-2:#64748b;
    --text-3:#94a3b8;--brand:#6366f1;--brand-ring:rgba(99,102,241,.25);
    --hover-bg:#f8fafc;--shadow:0 1px 4px rgba(0,0,0,.06);
}

/* ── Filter tabs ── */
.plug-tabs { display:flex; gap:0; border:1px solid var(--border); border-radius:10px; overflow:hidden; background:var(--card-bg); width:fit-content; margin-bottom:20px; }
.plug-tab  { padding:7px 18px; font-size:12.5px; font-weight:600; color:var(--text-2); cursor:pointer; border:none; background:transparent; transition:all .13s; }
.plug-tab:hover { background:var(--hover-bg); color:var(--text-1); }
.plug-tab.active { background:var(--brand); color:#fff; }

/* ── Plugin row ── */
.plug-table { width:100%; border-collapse:collapse; }
.plug-table thead tr { background:var(--hover-bg); }
.plug-table thead th {
    text-align:left; font-size:10.5px; font-weight:700; color:var(--text-3);
    text-transform:uppercase; letter-spacing:.07em;
    padding:10px 16px; border-bottom:1px solid var(--border);
}
.plug-table tbody tr {
    border-bottom:1px solid var(--border);
    transition:background .1s;
}
.plug-table tbody tr:hover { background:var(--hover-bg); }
.plug-table tbody tr.is-active { background:#f0fdf4; }
.plug-table tbody tr.is-active:hover { background:#dcfce7; }
.plug-table td { padding:14px 16px; vertical-align:middle; }

/* ── Plugin info cell ── */
.plug-ico { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
.plug-name { font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:2px; }
.plug-desc { font-size:11.5px;color:var(--text-3);line-height:1.45; }

/* ── Status badge ── */
.status-active   { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
.status-inactive { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;background:#f8fafc;color:var(--text-3);border:1px solid var(--border); }

/* ── Action links (WordPress style) ── */
.plug-actions { display:flex;gap:0;flex-wrap:wrap; }
.plug-act-link {
    font-size:11.5px; font-weight:600; padding:0; border:none; background:none;
    cursor:pointer; transition:color .1s; text-decoration:none;
}
.plug-act-link + .plug-act-link::before { content:'|'; margin:0 6px; color:var(--border); }
.plug-act-activate   { color:var(--brand); }
.plug-act-activate:hover { color:#4f46e5; }
.plug-act-deactivate { color:#d97706; }
.plug-act-deactivate:hover { color:#b45309; }
.plug-act-delete     { color:#ef4444; }
.plug-act-delete:hover { color:#dc2626; }
.plug-act-view       { color:var(--text-2); }
.plug-act-view:hover { color:var(--text-1); }

/* ── Stats strip ── */
.plug-stats { display:flex;align-items:center;gap:20px;padding:14px 18px;background:var(--card-bg);border:1px solid var(--border);border-radius:12px;margin-bottom:20px; }
.plug-stat  { text-align:center; }
.plug-stat-val { font-size:18px;font-weight:800;color:var(--text-1); }
.plug-stat-lbl { font-size:10.5px;color:var(--text-3); }
.plug-stat-sep { width:1px;height:28px;background:var(--border); }

/* ── Empty state ── */
.plug-empty { text-align:center;padding:48px 24px;color:var(--text-3); }

/* ── Section heading ── */
.plug-section-hd { font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.08em;padding:10px 16px;background:var(--hover-bg);border-bottom:1px solid var(--border); }

/* ── Toast ── */
.toast {
    position:fixed;bottom:24px;right:24px;z-index:9999;
    padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;
    box-shadow:0 8px 32px rgba(0,0,0,.15);transform:translateY(80px);
    transition:transform .3s cubic-bezier(.16,1,.3,1);
    display:flex;align-items:center;gap:10px;
}
.toast.show { transform:translateY(0); }
.toast-ok  { background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
.toast-err { background:#fef2f2;color:#991b1b;border:1px solid #fecaca; }

/* ── Search ── */
.plug-search {
    display:flex;align-items:center;gap:8px;padding:8px 14px;
    border:1px solid var(--border);border-radius:10px;background:var(--card-bg);
    box-shadow:var(--shadow);max-width:280px;
}
.plug-search input { flex:1;border:none;outline:none;font-size:13px;color:var(--text-1);background:transparent;font-family:inherit; }
</style>
@endpush

@section('content')
<div x-data="pluginsPage()" x-init="init()">

<div id="toast" class="toast"></div>

@php
    $categoryColors = [
        'core'=>'#6366f1','billing'=>'#10b981','communication'=>'#0ea5e9',
        'analytics'=>'#8b5cf6','business'=>'#f97316','ecommerce'=>'#059669',
        'compliance'=>'#64748b','integration'=>'#ec4899','utility'=>'#d97706',
        'saas'=>'#6366f1','auth'=>'#6366f1','rbac'=>'#8b5cf6',
    ];

    // Merge built-in modules + marketplace packages into one list
    $builtinInstalled = $projectModules->map(function($pm) use ($allModules, $categoryColors) {
        $mod = $allModules[$pm->module_key] ?? null;
        return [
            'key'    => $pm->module_key,
            'name'   => $mod['name'] ?? $pm->module_key,
            'desc'   => $mod['description'] ?? '—',
            'icon'   => $mod['icon'] ?? '🧩',
            'color'  => $categoryColors[$mod['category'] ?? ''] ?? '#6366f1',
            'type'   => 'builtin',
            'status' => $pm->status,
            'pm_id'  => $pm->id,
            'cat'    => $mod['category'] ?? 'module',
        ];
    })->values();

    $pkgInstalled = $installations->map(fn($inst) => [
        'key'     => 'pkg:' . $inst->id,
        'name'    => $inst->item->name ?? '—',
        'desc'    => $inst->item->description ?? '',
        'icon'    => $inst->item->icon ?? '📦',
        'color'   => '#6366f1',
        'type'    => 'package',
        'status'  => $inst->status,
        'inst_id' => $inst->id,
        'version' => $inst->version,
        'cat'     => 'package',
    ])->values();

    $allInstalled = $builtinInstalled->concat($pkgInstalled);
    $activeCount  = $allInstalled->where('status', 'active')->count()
                  + $allInstalled->where('status', 'installed')->count();
    $inactiveCount = $allInstalled->where('status', 'inactive')->count();
    $totalCount    = $allInstalled->count();
@endphp

{{-- ── Stats strip ─────────────────────────────────────────────────────── --}}
<div class="plug-stats">
    <div class="plug-stat">
        <div class="plug-stat-val">{{ $totalCount }}</div>
        <div class="plug-stat-lbl">Total</div>
    </div>
    <div class="plug-stat-sep"></div>
    <div class="plug-stat" style="cursor:pointer;" @click="filter='all'">
        <div class="plug-stat-val" style="color:var(--brand);">{{ $activeCount }}</div>
        <div class="plug-stat-lbl">Active</div>
    </div>
    <div class="plug-stat-sep"></div>
    <div class="plug-stat">
        <div class="plug-stat-val" style="color:var(--text-3);">{{ $inactiveCount }}</div>
        <div class="plug-stat-lbl">Inactive</div>
    </div>
    <div style="flex:1;"></div>
    <div class="plug-search">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" x-model="search" placeholder="Search plugins…">
    </div>
</div>

{{-- ── Filter tabs ──────────────────────────────────────────────────────── --}}
<div class="plug-tabs">
    <button class="plug-tab" :class="filter==='all'      ? 'active' : ''" @click="filter='all'">
        All <span x-text="'(' + counts.all + ')'"></span>
    </button>
    <button class="plug-tab" :class="filter==='active'   ? 'active' : ''" @click="filter='active'">
        Active <span x-text="'(' + counts.active + ')'"></span>
    </button>
    <button class="plug-tab" :class="filter==='inactive' ? 'active' : ''" @click="filter='inactive'">
        Inactive <span x-text="'(' + counts.inactive + ')'"></span>
    </button>
    <button class="plug-tab" :class="filter==='package'  ? 'active' : ''" @click="filter='package'">
        Packages <span x-text="'(' + counts.package + ')'"></span>
    </button>
</div>

{{-- ── Plugin table ──────────────────────────────────────────────────────── --}}
<div style="background:var(--card-bg);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow);">

    @if($allInstalled->isEmpty())
    <div class="plug-empty">
        <div style="font-size:32px;margin-bottom:12px;">🧩</div>
        <p style="font-size:14px;font-weight:700;color:var(--text-1);margin-bottom:6px;">No plugins installed</p>
        <p style="font-size:13px;margin-bottom:20px;">Go to the marketplace to install modules.</p>
        <a href="{{ route('marketplace.index') }}"
           style="display:inline-flex;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;text-decoration:none;background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
            Browse Marketplace
        </a>
    </div>
    @else

    {{-- Table header --}}
    <table class="plug-table">
        <thead>
            <tr>
                <th style="width:44%;">Plugin</th>
                <th>Status</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Built-in modules --}}
            @foreach($builtinInstalled as $plug)
            @php $isActive = in_array($plug['status'], ['active','installed']); @endphp
            <tr class="{{ $isActive ? 'is-active' : '' }}"
                x-show="matchesFilter({{ json_encode($plug) }})"
                x-cloak>
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="plug-ico" style="background:{{ $plug['color'] }}12;border:1px solid {{ $plug['color'] }}25;">
                            {{ $plug['icon'] }}
                        </div>
                        <div>
                            <div class="plug-name">{{ $plug['name'] }}</div>
                            <div class="plug-desc">{{ Str::limit($plug['desc'], 75) }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($isActive)
                    <span class="status-active">● Active</span>
                    @else
                    <span class="status-inactive">○ Inactive</span>
                    @endif
                </td>
                <td>
                    <span style="font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:5px;background:var(--hover-bg);color:var(--text-2);border:1px solid var(--border);">
                        Built-in
                    </span>
                </td>
                <td>
                    <div class="plug-actions">
                        @if($isActive)
                        <button class="plug-act-link plug-act-deactivate"
                                onclick="doToggle('{{ $plug['key'] }}', '{{ $plug['status'] }}', this)">
                            Deactivate
                        </button>
                        @else
                        <button class="plug-act-link plug-act-activate"
                                onclick="doToggle('{{ $plug['key'] }}', '{{ $plug['status'] }}', this)">
                            Activate
                        </button>
                        @endif
                        <button class="plug-act-link plug-act-delete"
                                onclick="doDelete('{{ $plug['key'] }}', 'builtin', this)">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach

            {{-- Marketplace packages --}}
            @foreach($pkgInstalled as $pkg)
            @php $isActive = $pkg['status'] === 'active'; @endphp
            <tr class="{{ $isActive ? 'is-active' : '' }}"
                x-show="matchesFilter({{ json_encode($pkg) }})"
                x-cloak>
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="plug-ico" style="background:#eef2ff;border:1px solid #c7d2fe;">
                            {{ $pkg['icon'] }}
                        </div>
                        <div>
                            <div class="plug-name">{{ $pkg['name'] }}</div>
                            <div class="plug-desc">
                                {{ Str::limit($pkg['desc'], 60) }}
                                <span style="color:var(--text-3);"> · v{{ $pkg['version'] }}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($isActive)
                    <span class="status-active">● Active</span>
                    @else
                    <span class="status-inactive">○ Inactive</span>
                    @endif
                </td>
                <td>
                    <span style="font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:5px;background:#fdf4ff;color:#6d28d9;border:1px solid #e9d5ff;">
                        Package
                    </span>
                </td>
                <td>
                    <div class="plug-actions">
                        @if($isActive)
                        <button class="plug-act-link plug-act-deactivate"
                                onclick="doPkgToggle({{ $pkg['inst_id'] }}, this)">
                            Deactivate
                        </button>
                        @else
                        <button class="plug-act-link plug-act-activate"
                                onclick="doPkgToggle({{ $pkg['inst_id'] }}, this)">
                            Activate
                        </button>
                        @endif
                        <button class="plug-act-link plug-act-delete"
                                onclick="doDelete('{{ $pkg['inst_id'] }}', 'package', this)">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

</div>{{-- /x-data --}}

<script>
const PROJECT_ID = {{ $project->id }};
const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function toast(msg, ok = true) {
    const el = document.getElementById('toast');
    el.textContent = (ok ? '✅ ' : '❌ ') + msg;
    el.className   = 'toast show ' + (ok ? 'toast-ok' : 'toast-err');
    setTimeout(() => el.classList.remove('show'), 3200);
}

// Built-in module toggle
async function doToggle(key, currentStatus, btn) {
    const label = btn.textContent.trim();
    btn.textContent = '…'; btn.disabled = true;
    try {
        const res  = await fetch(`/marketplace/modules/${key}/toggle`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ project_id: PROJECT_ID }),
        });
        const data = await res.json();
        if (data.success) { toast(data.message); setTimeout(() => location.reload(), 600); }
        else { toast(data.message || 'Failed', false); btn.textContent = label; btn.disabled = false; }
    } catch { toast('Network error', false); btn.textContent = label; btn.disabled = false; }
}

// Package toggle
async function doPkgToggle(instId, btn) {
    const label = btn.textContent.trim();
    btn.textContent = '…'; btn.disabled = true;
    try {
        const res  = await fetch(`/marketplace/installations/${instId}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) { toast(data.message); setTimeout(() => location.reload(), 600); }
        else { toast(data.message || 'Failed', false); btn.textContent = label; btn.disabled = false; }
    } catch { toast('Network error', false); btn.textContent = label; btn.disabled = false; }
}

// Delete
async function doDelete(id, type, btn) {
    if (!confirm('Remove this plugin from the project?')) return;
    btn.disabled = true;
    try {
        let url, method, body;
        if (type === 'builtin') {
            url    = `/marketplace/modules/${id}/uninstall`;
            method = 'DELETE';
            body   = JSON.stringify({ project_id: PROJECT_ID });
        } else {
            url    = `/marketplace/installations/${id}/uninstall`;
            method = 'DELETE';
            body   = null;
        }
        const res  = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body,
        });
        const data = await res.json();
        if (data.success) { toast('Plugin removed.'); setTimeout(() => location.reload(), 600); }
        else { toast(data.message || 'Failed', false); btn.disabled = false; }
    } catch { toast('Network error', false); btn.disabled = false; }
}

function pluginsPage() {
    return {
        filter: 'all',
        search: '',
        counts: {
            all:      {{ $totalCount }},
            active:   {{ $activeCount }},
            inactive: {{ $inactiveCount }},
            package:  {{ $pkgInstalled->count() }},
        },
        init() {
            const p = new URLSearchParams(window.location.search);
            if (p.get('filter')) this.filter = p.get('filter');
        },
        matchesFilter(plug) {
            const s = this.search.toLowerCase();
            if (s && !plug.name.toLowerCase().includes(s) && !plug.desc.toLowerCase().includes(s)) return false;
            if (this.filter === 'all')      return true;
            if (this.filter === 'package')  return plug.type === 'package';
            if (this.filter === 'active')   return ['active','installed'].includes(plug.status);
            if (this.filter === 'inactive') return plug.status === 'inactive';
            return true;
        },
    };
}
</script>
@endsection
