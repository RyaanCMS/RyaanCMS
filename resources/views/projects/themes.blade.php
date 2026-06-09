@extends('layouts.app')
@section('title', 'Themes — ' . $project->name)
@section('header', $project->name . ' — Themes')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('projects.show', $project) }}"
       class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-all"
       style="border:1px solid var(--border);color:var(--text-2);background:var(--card-bg);"
       onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-bg)'">
        ← Project
    </a>
    <a href="{{ route('marketplace.index', ['tab' => 'plugins']) }}"
       class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
       style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
        + Add New Template
    </a>
</div>
@endsection

@push('head')
<style>
:root {
    --card-bg: #fff; --border: #e8ecf0; --text-1: #0f172a; --text-2: #64748b;
    --text-3: #94a3b8; --brand: #6366f1; --brand-ring: rgba(99,102,241,.25);
    --hover-bg: #f8fafc; --shadow: 0 1px 4px rgba(0,0,0,.06);
}

/* ── Theme card grid ── */
.theme-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media(max-width:1100px){ .theme-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width:640px) { .theme-grid { grid-template-columns: 1fr; } }

/* ── Individual theme card ── */
.theme-card {
    border-radius: 14px;
    border: 2px solid var(--border);
    overflow: hidden;
    background: var(--card-bg);
    transition: border-color .18s, box-shadow .18s, transform .18s;
    position: relative;
    cursor: default;
}
.theme-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.1); transform: translateY(-2px); }
.theme-card.is-active { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-ring); }

/* ── Preview area ── */
.theme-preview {
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    position: relative;
    overflow: hidden;
}
.theme-preview-grid {
    position: absolute; inset: 0;
    display: grid;
    grid-template-columns: 48px 1fr;
    grid-template-rows: 28px 1fr 24px;
    gap: 0;
    opacity: .35;
}
.tpg-sidebar { grid-row: 1/-1; background: rgba(0,0,0,.15); }
.tpg-header  { background: rgba(0,0,0,.1); }
.tpg-content { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; padding: 6px; }
.tpg-block   { border-radius: 3px; background: rgba(255,255,255,.3); }
.tpg-footer  { background: rgba(0,0,0,.08); }
.theme-ico   { position: relative; z-index: 1; text-shadow: 0 2px 8px rgba(0,0,0,.15); }

/* ── Active badge ── */
.active-badge {
    position: absolute; top: 10px; left: 10px; z-index: 2;
    font-size: 10px; font-weight: 700; padding: 3px 9px;
    border-radius: 99px; background: var(--brand); color: #fff;
    letter-spacing: .04em;
}

/* ── Card info ── */
.theme-info { padding: 14px 16px; }
.theme-name { font-size: 13.5px; font-weight: 700; color: var(--text-1); margin-bottom: 2px; }
.theme-cat  { font-size: 11.5px; color: var(--text-3); margin-bottom: 12px; }
.theme-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 12px; }
.theme-tag  { font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 5px;
              background: var(--hover-bg); color: var(--text-2); border: 1px solid var(--border); }

/* ── Action buttons ── */
.theme-actions { display: flex; gap: 8px; }
.btn-activate {
    flex: 1; padding: 8px 0; border-radius: 9px; font-size: 12px; font-weight: 700;
    color: #fff; border: none; cursor: pointer; transition: all .15s;
    background: var(--brand); box-shadow: 0 2px 8px var(--brand-ring);
}
.btn-activate:hover { filter: brightness(1.08); transform: translateY(-1px); }
.btn-activate:disabled { opacity:.6; cursor:not-allowed; transform:none; filter:none; }
.btn-secondary {
    padding: 8px 14px; border-radius: 9px; font-size: 12px; font-weight: 600;
    color: var(--text-2); border: 1px solid var(--border); background: var(--hover-bg);
    cursor: pointer; transition: all .13s; text-decoration: none; display: flex;
    align-items: center;
}
.btn-secondary:hover { background: #f1f5f9; color: var(--text-1); }
.btn-danger {
    padding: 8px 12px; border-radius: 9px; font-size: 12px; font-weight: 600;
    color: #ef4444; border: 1px solid #fecaca; background: #fef2f2;
    cursor: pointer; transition: all .13s;
}
.btn-danger:hover { background: #fee2e2; }

/* ── Section header ── */
.section-hd {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.section-title { font-size: 14px; font-weight: 700; color: var(--text-1); }
.section-hint  { font-size: 12px; color: var(--text-3); }

/* ── Available (uninstalled) cards ── */
.theme-card-sm {
    border-radius: 14px; border: 2px dashed var(--border);
    overflow: hidden; background: var(--card-bg);
    transition: border-color .18s, box-shadow .18s, transform .18s;
}
.theme-card-sm:hover { border-color: var(--brand); box-shadow: 0 4px 16px rgba(0,0,0,.07); transform: translateY(-1px); }

/* ── Toast ── */
.toast {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600;
    box-shadow: 0 8px 32px rgba(0,0,0,.15); transform: translateY(80px);
    transition: transform .3s cubic-bezier(.16,1,.3,1);
    display: flex; align-items: center; gap: 10px;
}
.toast.show { transform: translateY(0); }
.toast-ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.toast-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endpush

@section('content')
<div x-data="themesPage()" x-init="init()">

{{-- ── Toast ──────────────────────────────────────────────────────────── --}}
<div id="toast" class="toast"></div>

{{-- ── Active Theme ───────────────────────────────────────────────────── --}}
@php
    $active = $installedModules->first(fn($m) => $m->status === 'active');
    $activeKey = $active ? $active->module_key : null;
    $activeData = $activeKey ? ($allTemplates[$activeKey] ?? null) : null;
@endphp

@if($activeData)
<div style="margin-bottom:32px;">
    <div class="section-hd">
        <span class="section-title">Active Theme</span>
        @if($project->domain ?? false)
        <a href="{{ route('site.serve', $project) }}" target="_blank" class="btn-secondary" style="font-size:11px;">
            🌐 View Live Site
        </a>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">
        {{-- Big preview --}}
        <div class="theme-preview" style="height:220px;border-radius:14px;background:{{ $activeData['color'] ?? '#6366f1' }};border:2px solid var(--border);">
            <div class="theme-preview-grid">
                <div class="tpg-sidebar"></div>
                <div class="tpg-header"></div>
                <div class="tpg-content">
                    <div class="tpg-block"></div><div class="tpg-block"></div>
                    <div class="tpg-block"></div><div class="tpg-block"></div>
                </div>
                <div class="tpg-footer"></div>
            </div>
            <span class="theme-ico" style="font-size:56px;">{{ $activeData['icon'] }}</span>
        </div>

        {{-- Info --}}
        <div style="padding:8px 0;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:18px;font-weight:800;color:var(--text-1);">{{ $activeData['name'] }}</span>
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;background:var(--brand);color:#fff;">● ACTIVE</span>
            </div>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:14px;line-height:1.6;">{{ $activeData['description'] }}</p>
            <div class="theme-tags" style="margin-bottom:16px;">
                @foreach($activeData['tags'] ?? [] as $tag)
                <span class="theme-tag">{{ $tag }}</span>
                @endforeach
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('site.serve', $project) }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;text-decoration:none;background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                    🌐 View Live
                </a>
                <button onclick="doDeactivate('{{ $activeKey }}')"
                        style="padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid var(--border);background:var(--hover-bg);color:var(--text-2);cursor:pointer;">
                    Deactivate
                </button>
                <a href="{{ route('marketplace.template.download', $activeKey) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid var(--border);background:var(--hover-bg);color:var(--text-2);text-decoration:none;">
                    ⬇ Download ZIP
                </a>
            </div>
        </div>
    </div>
</div>
@else
{{-- No active theme notice --}}
<div style="margin-bottom:28px;padding:18px 20px;border-radius:14px;background:linear-gradient(135deg,#fffbeb,#fef9c3);border:1px solid #fde68a;display:flex;align-items:center;gap:14px;">
    <span style="font-size:24px;">🎨</span>
    <div>
        <p style="font-size:13.5px;font-weight:700;color:#92400e;margin-bottom:2px;">No active theme</p>
        <p style="font-size:12px;color:#a16207;">Install and activate a template below to give your project a look.</p>
    </div>
</div>
@endif

{{-- ── Installed Themes (not active) ─────────────────────────────────── --}}
@php
    $inactiveInstalled = $installedModules->filter(fn($m) => $m->status !== 'active');
@endphp

@if($inactiveInstalled->isNotEmpty())
<div style="margin-bottom:32px;">
    <div class="section-hd">
        <span class="section-title">Installed Themes</span>
        <span class="section-hint">{{ $inactiveInstalled->count() }} installed, not active</span>
    </div>
    <div class="theme-grid">
        @foreach($inactiveInstalled as $key => $pm)
        @php $tpl = $allTemplates[$key] ?? null; @endphp
        @if($tpl)
        <div class="theme-card" id="card-{{ Str::slug($key) }}">
            <div class="theme-preview" style="height:140px;background:{{ $tpl['color'] ?? '#6366f1' }};">
                <div class="theme-preview-grid">
                    <div class="tpg-sidebar"></div>
                    <div class="tpg-header"></div>
                    <div class="tpg-content">
                        <div class="tpg-block"></div><div class="tpg-block"></div>
                    </div>
                    <div class="tpg-footer"></div>
                </div>
                <span class="theme-ico">{{ $tpl['icon'] }}</span>
            </div>
            <div class="theme-info">
                <div class="theme-name">{{ $tpl['name'] }}</div>
                <div class="theme-cat">{{ $tpl['category'] }}</div>
                <div class="theme-actions">
                    <button class="btn-activate" onclick="doActivate('{{ $key }}', this)">
                        Activate
                    </button>
                    <a href="{{ route('marketplace.template.download', $key) }}"
                       class="btn-secondary" title="Download ZIP">⬇</a>
                    <button class="btn-danger" onclick="doRemove('{{ $key }}', this)" title="Remove">✕</button>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif

{{-- ── Available Templates (not installed) ───────────────────────────── --}}
@if($availableTemplates->isNotEmpty())
<div style="margin-bottom:32px;">
    <div class="section-hd">
        <span class="section-title">Available Templates</span>
        <span class="section-hint">Click Install to add to this project</span>
    </div>
    <div class="theme-grid">
        @foreach($availableTemplates as $key => $tpl)
        <div class="theme-card-sm" id="card-{{ Str::slug($key) }}">
            <div class="theme-preview" style="height:130px;background:{{ $tpl['color'] ?? '#6366f1' }};opacity:.85;">
                <div class="theme-preview-grid">
                    <div class="tpg-sidebar"></div>
                    <div class="tpg-header"></div>
                    <div class="tpg-content">
                        <div class="tpg-block"></div><div class="tpg-block"></div>
                    </div>
                    <div class="tpg-footer"></div>
                </div>
                <span class="theme-ico" style="font-size:40px;">{{ $tpl['icon'] }}</span>
            </div>
            <div class="theme-info">
                <div class="theme-name">{{ $tpl['name'] }}</div>
                <div class="theme-cat">{{ $tpl['category'] }}</div>
                <div class="theme-tags">
                    @foreach(array_slice($tpl['tags'] ?? [], 0, 3) as $tag)
                    <span class="theme-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                <div class="theme-actions">
                    <button class="btn-activate" onclick="doInstallActivate('{{ $key }}', '{{ addslashes($tpl['name']) }}', this)">
                        Install &amp; Activate
                    </button>
                    <a href="{{ route('marketplace.template.download', $key) }}"
                       class="btn-secondary" title="Download ZIP">⬇</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Empty state ─────────────────────────────────────────────────────── --}}
@if($installedModules->isEmpty() && $availableTemplates->isEmpty())
<div style="text-align:center;padding:64px 24px;">
    <div style="width:64px;height:64px;border-radius:18px;background:var(--hover-bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;">🎨</div>
    <p style="font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:6px;">No templates yet</p>
    <p style="font-size:13px;color:var(--text-3);margin-bottom:20px;">Browse the marketplace to add templates to your project.</p>
    <a href="{{ route('marketplace.index', ['tab' => 'plugins']) }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;text-decoration:none;background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
        Browse Templates
    </a>
</div>
@endif

</div>{{-- /x-data --}}

<script>
const PROJECT_ID  = {{ $project->id }};
const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function toast(msg, ok = true) {
    const el = document.getElementById('toast');
    el.textContent = (ok ? '✅ ' : '❌ ') + msg;
    el.className   = 'toast show ' + (ok ? 'toast-ok' : 'toast-err');
    setTimeout(() => el.classList.remove('show'), 3200);
}

async function doActivate(key, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Activating…'; }
    try {
        const res  = await fetch(`/projects/${PROJECT_ID}/templates/${key}/activate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) { toast(data.message); setTimeout(() => location.reload(), 700); }
        else              { toast(data.message || 'Failed', false); if(btn){btn.disabled=false;btn.textContent='Activate';} }
    } catch { toast('Network error', false); if(btn){btn.disabled=false;btn.textContent='Activate';} }
}

async function doDeactivate(key) {
    try {
        const res  = await fetch(`/projects/${PROJECT_ID}/templates/${key}/deactivate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) { toast('Template deactivated.'); setTimeout(() => location.reload(), 700); }
        else              { toast(data.message || 'Failed', false); }
    } catch { toast('Network error', false); }
}

async function doInstallActivate(key, name, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Installing…'; }
    try {
        // Install first
        const installRes = await fetch(`/marketplace/modules/${key}/install`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ project_id: PROJECT_ID }),
        });
        const installData = await installRes.json();
        if (!installData.success && !installData.message?.includes('already')) {
            toast(installData.message || 'Install failed', false);
            if(btn){btn.disabled=false;btn.textContent='Install & Activate';}
            return;
        }
        // Then activate
        if(btn) btn.textContent = 'Activating…';
        const actRes  = await fetch(`/projects/${PROJECT_ID}/templates/${key}/activate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const actData = await actRes.json();
        if (actData.success) { toast(name + ' is now live! 🎉'); setTimeout(() => location.reload(), 800); }
        else                 { toast(actData.message || 'Activate failed', false); if(btn){btn.disabled=false;btn.textContent='Install & Activate';} }
    } catch { toast('Network error', false); if(btn){btn.disabled=false;btn.textContent='Install & Activate';} }
}

async function doRemove(key, btn) {
    if (!confirm('Remove this template from the project?')) return;
    if (btn) { btn.disabled = true; }
    try {
        const res  = await fetch(`/marketplace/modules/${key}/uninstall`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ project_id: PROJECT_ID }),
        });
        const data = await res.json();
        if (data.success) { toast('Template removed.'); setTimeout(() => location.reload(), 600); }
        else              { toast(data.message || 'Failed', false); if(btn) btn.disabled=false; }
    } catch { toast('Network error', false); if(btn) btn.disabled=false; }
}

function themesPage() { return { init() {} }; }
</script>
@endsection
