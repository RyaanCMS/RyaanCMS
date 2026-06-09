@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Overview')

@php
$hour     = now()->hour;
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$storageMB = round($stats['storage'] / 1048576, 1);
$userName  = explode(' ', auth()->user()->name)[0];

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

$feedItems = collect();
foreach($recentActivity as $c) {
    $feedItems->push(['kind'=>'ai','title'=>$c->title ?? 'AI Conversation','proj'=>$c->project->name ?? '—','sub'=>$c->message_count.' msgs','time'=>$c->updated_at]);
}
foreach($recentDeployments as $d) {
    $feedItems->push(['kind'=>'deploy','status'=>$d->status,'title'=>$d->project->name ?? 'Project','proj'=>null,'sub'=>ucfirst($d->status).' deployment','time'=>$d->created_at]);
}
$feedItems = $feedItems->sortByDesc('time')->take(8)->values();
@endphp

@section('header-actions')
<a href="{{ route('projects.create') }}"
   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
   style="background:var(--brand);box-shadow:0 2px 10px var(--brand-ring);">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
    </svg>
    New Project
</a>
@endsection

@push('head')
<style>
/* ═══════════════════════════════════════════
   Dashboard — Professional SaaS design
   All colors use CSS custom properties
═══════════════════════════════════════════ */

/* ── Greeting bar ── */
.db-greet {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px;
    background: linear-gradient(135deg, var(--card-bg) 0%, color-mix(in srgb, var(--brand) 4%, var(--card-bg)) 100%);
    border: 1px solid var(--border);
    border-left: 3px solid var(--brand);
    border-radius: 16px;
    box-shadow: var(--shadow);
}
.db-greet-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    border: 2px solid var(--brand);
    flex-shrink: 0;
    box-shadow: 0 0 0 3px var(--brand-ring);
}
.db-greet-name {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-1);
    letter-spacing: -.025em;
    line-height: 1.15;
}
.db-greet-sub {
    font-size: 12px;
    color: var(--text-3);
    margin-top: 2px;
}
.db-greet-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 600;
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

/* ── KPI row ── */
.db-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.db-kpi {
    background: #fff;
    border: 1px solid var(--border);
    border-top: 3px solid transparent;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: var(--shadow);
    transition: box-shadow .2s, transform .2s;
    cursor: default;
}
.db-kpi:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}
.db-kpi-ico {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 12px;
}
.db-kpi-val {
    font-size: 30px;
    font-weight: 800;
    color: var(--text-1);
    letter-spacing: -.04em;
    line-height: 1;
}
.db-kpi-unit {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-3);
    margin-left: 2px;
}
.db-kpi-label {
    font-size: 11.5px;
    color: var(--text-2);
    font-weight: 500;
    margin-top: 6px;
}
.db-kpi-bar {
    height: 3px;
    border-radius: 99px;
    margin-top: 12px;
    background: var(--hover-bg);
    overflow: hidden;
}
.db-kpi-bar-fill {
    height: 100%;
    border-radius: 99px;
}

/* ── Main layout ── */
.db-layout {
    display: grid;
    grid-template-columns: 1fr 256px;
    gap: 14px;
    align-items: start;
}

/* ── Section card ── */
.db-section {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
    overflow: hidden;
}
.db-section-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    background: var(--hover-bg);
}
.db-section-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-1);
}
.db-section-link {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--brand);
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 7px;
    background: #eef2ff;
    transition: background .13s;
}
.db-section-link:hover { background: var(--brand-light); }
.db-section-count {
    font-size: 10.5px;
    font-weight: 600;
    color: var(--text-3);
    background: var(--hover-bg);
    border: 1px solid var(--border);
    padding: 2px 8px;
    border-radius: 99px;
    margin-left: 6px;
}

/* ── Project grid — 2 columns ── */
.db-proj-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: var(--border);
}

/* ── Project card ── */
.db-proj-card {
    background: var(--card-bg);
    padding: 16px 18px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: background .15s;
    position: relative;
}
.db-proj-card::after {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 0;
    opacity: 0;
    transition: opacity .15s;
}
.db-proj-card:hover { background: var(--hover-bg); }
.db-proj-card:hover::after { opacity: 1; }

.db-proj-row1 { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.db-proj-ico  {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.db-proj-name {
    font-size: 13.5px; font-weight: 700; color: var(--text-1);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    flex: 1; margin: 0 8px;
}
.db-proj-status {
    font-size: 9px; font-weight: 700;
    padding: 2px 7px; border-radius: 99px;
    white-space: nowrap; flex-shrink: 0;
}
.db-proj-row2 { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.db-proj-type {
    font-size: 10px; font-weight: 700;
    padding: 2px 7px; border-radius: 5px;
}
.db-proj-dot { color: var(--border); }
.db-proj-age { font-size: 10.5px; color: var(--text-3); }
.db-proj-row3 { display: flex; align-items: center; justify-content: space-between; }
.db-proj-files { font-size: 10px; color: var(--text-3); }
.db-proj-cta {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700; color: #fff;
    padding: 5px 12px; border-radius: 7px; text-decoration: none;
    transition: opacity .13s, transform .13s;
}
.db-proj-cta:hover { opacity: .88; transform: translateY(-1px); }

/* ── New project ghost ── */
.db-new-card {
    background: var(--card-bg);
    padding: 28px 18px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background .15s;
    min-height: 140px;
}
.db-new-card:hover { background: var(--brand-light); }
.db-new-ico {
    width: 36px; height: 36px; border-radius: 10px;
    background: var(--hover-bg);
    border: 1.5px dashed var(--border);
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.db-new-card:hover .db-new-ico {
    background: var(--brand-light);
    border-color: #a5b4fc;
}
.db-new-lbl { font-size: 12px; font-weight: 600; color: #94a3b8; transition: color .15s; }
.db-new-card:hover .db-new-lbl { color: #6366f1; }

/* ── Empty state ── */
.db-empty {
    padding: 52px 24px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center;
}
.db-empty-ico {
    width: 56px; height: 56px; border-radius: 14px;
    background: var(--brand-light);
    border: 1px solid var(--brand-ring);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
}
.db-empty h3 { font-size: 14px; font-weight: 700; color: var(--text-1); margin-bottom: 6px; }
.db-empty p  { font-size: 12px; color: var(--text-3); margin-bottom: 20px; max-width: 240px; line-height: 1.7; }
.db-empty-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 22px; border-radius: 11px; font-size: 13px;
    font-weight: 700; color: #fff; text-decoration: none;
    background: var(--brand); box-shadow: 0 3px 12px var(--brand-ring);
    transition: all .15s;
}
.db-empty-btn:hover { filter: brightness(1.06); transform: translateY(-1px); }

/* ── Activity feed ── */
.db-feed-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
    transition: background .12s;
}
.db-feed-row:last-child { border-bottom: none; }
.db-feed-row:hover { background: var(--hover-bg); }
.db-feed-dot {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.db-feed-title { font-size: 12.5px; font-weight: 600; color: var(--text-1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.db-feed-meta  { font-size: 10.5px; color: var(--text-3); margin-top: 2px; }

/* ── Sidebar ── */
.db-sidebar { display: flex; flex-direction: column; gap: 12px; }

.db-widget {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: var(--shadow);
    overflow: hidden;
}
.db-widget-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 15px;
    border-bottom: 1px solid var(--border);
    background: var(--hover-bg);
    font-size: 12px; font-weight: 700; color: var(--text-1);
}

.db-action {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px;
    text-decoration: none;
    border-bottom: 1px solid var(--border);
    transition: background .12s;
}
.db-action:last-child { border-bottom: none; }
.db-action:hover { background: var(--hover-bg); }
.db-action-ico {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.db-action-label { font-size: 12.5px; font-weight: 600; color: var(--text-1); flex: 1; }
.db-action-hint  { font-size: 10.5px; color: var(--text-3); margin-top: 1px; }

.db-tpl {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 12px; border-radius: 8px;
    text-decoration: none;
    margin: 2px 6px;
    transition: background .12s;
}
.db-tpl:hover { background: var(--brand-light); }
.db-tpl-ico {
    width: 24px; height: 24px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; flex-shrink: 0;
}
.db-tpl-lbl { font-size: 12px; font-weight: 500; color: var(--text-2); flex: 1; }

.db-sys-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 14px;
    border-bottom: 1px solid var(--border);
}
.db-sys-row:last-child { border-bottom: none; }

/* ── Responsive ── */
@media (max-width: 1180px) {
    .db-kpi-row { grid-template-columns: repeat(2, 1fr); }
    .db-layout  { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .db-kpi-row   { grid-template-columns: repeat(2, 1fr); }
    .db-proj-grid { grid-template-columns: 1fr; }
    .db-greet {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
    }
    .db-greet-badge { align-self: flex-start; }
    .db-kpi-val { font-size: 22px; }
    .db-section-hd { padding: 12px 14px; }
    .db-proj-card { padding: 12px 14px; }
    .db-feed-row  { padding: 10px 14px; }
}
@media (max-width: 400px) {
    .db-kpi-row { grid-template-columns: 1fr 1fr; }
    .db-kpi { padding: 14px 12px; }
    .db-kpi-val { font-size: 20px; }
}
</style>
@endpush

@section('content')
<div style="display:flex;flex-direction:column;gap:16px;">

{{-- ══ GREETING ══════════════════════════════════════════════════ --}}
<div class="db-greet">
    <div style="display:flex;align-items:center;gap:13px;">
        <img src="{{ auth()->user()->avatar_url }}"
             class="db-greet-avatar" alt="{{ $userName }}">
        <div>
            <div class="db-greet-name">{{ $greeting }}, {{ $userName }}</div>
            <div class="db-greet-sub">{{ now()->format('l, F j, Y') }}</div>
        </div>
    </div>
    <div class="db-greet-badge">
        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        AI Builder Active
    </div>
</div>

{{-- ══ KPI ROW ═══════════════════════════════════════════════════ --}}
<div class="db-kpi-row">
    {{-- Projects --}}
    <div class="db-kpi" style="border-top-color:#6366f1;">
        <div class="db-kpi-ico" style="background:#eef2ff;">
            <svg style="width:16px;height:16px;stroke:#6366f1" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
        </div>
        <div class="db-kpi-val">{{ $stats['projects'] }}</div>
        <div class="db-kpi-label">Total Projects</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, $stats['projects'] * 10) }}%;background:#6366f1;"></div></div>
    </div>

    {{-- AI Messages --}}
    <div class="db-kpi" style="border-top-color:#8b5cf6;">
        <div class="db-kpi-ico" style="background:#fdf4ff;">
            <svg style="width:16px;height:16px;stroke:#8b5cf6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
        </div>
        <div class="db-kpi-val">{{ number_format($stats['ai_messages']) }}</div>
        <div class="db-kpi-label">AI Messages</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, ($stats['ai_messages'] / max(1, 500)) * 100) }}%;background:#8b5cf6;"></div></div>
    </div>

    {{-- Deployments --}}
    <div class="db-kpi" style="border-top-color:#10b981;">
        <div class="db-kpi-ico" style="background:#ecfdf5;">
            <svg style="width:16px;height:16px;stroke:#10b981" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="db-kpi-val">{{ $stats['deployments'] }}</div>
        <div class="db-kpi-label">Deployments</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, $stats['deployments'] * 20) }}%;background:#10b981;"></div></div>
    </div>

    {{-- Storage --}}
    <div class="db-kpi" style="border-top-color:#f97316;">
        <div class="db-kpi-ico" style="background:#fff7ed;">
            <svg style="width:16px;height:16px;stroke:#f97316" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
            </svg>
        </div>
        <div>
            <span class="db-kpi-val">{{ $storageMB }}</span>
            <span class="db-kpi-unit">MB</span>
        </div>
        <div class="db-kpi-label">Storage Used</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, ($storageMB / 500) * 100) }}%;background:#f97316;"></div></div>
    </div>
</div>

{{-- ══ MAIN LAYOUT ═══════════════════════════════════════════════ --}}
<div class="db-layout">

    {{-- LEFT ──────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Projects ── --}}
        <div class="db-section">
            <div class="db-section-hd">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="db-section-title">My Projects</span>
                    @if(!$recentProjects->isEmpty())
                    <span class="db-section-count">{{ $stats['projects'] }} total</span>
                    @endif
                </div>
                <a href="{{ route('projects.index') }}" class="db-section-link">View all →</a>
            </div>

            @if($recentProjects->isEmpty())
            <div class="db-empty">
                <div class="db-empty-ico">
                    <svg style="width:26px;height:26px;stroke:#6366f1" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3>No projects yet</h3>
                <p>Describe any application and the AI will architect, code, and deliver it — front-end to database.</p>
                <a href="{{ route('projects.create') }}" class="db-empty-btn">
                    <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create First Project
                </a>
            </div>
            @else
            <div class="db-proj-grid">
                @foreach($recentProjects as $proj)
                @php $tc = $typeMap[$proj->type] ?? $typeMap['default']; @endphp
                <a class="db-proj-card" href="{{ route('projects.show', $proj) }}"
                   style="--stripe:{{ $tc['from'] }};"
                   onmouseover="this.querySelector('.db-proj-card-line').style.opacity='1'"
                   onmouseout="this.querySelector('.db-proj-card-line').style.opacity='0'">
                    <div class="db-proj-card-line"
                         style="position:absolute;left:0;top:0;bottom:0;width:3px;background:{{ $tc['from'] }};opacity:0;border-radius:2px 0 0 2px;transition:opacity .15s;"></div>
                    <div class="db-proj-row1">
                        <div class="db-proj-ico"
                             style="background:{{ $tc['bg'] }};border:1px solid {{ $tc['bd'] }};">
                            {{ $tc['emoji'] }}
                        </div>
                        <span class="db-proj-name">{{ $proj->name }}</span>
                        @if($proj->status === 'active')
                        <span class="db-proj-status"
                              style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">Active</span>
                        @else
                        <span class="db-proj-status"
                              style="background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0;">{{ ucfirst($proj->status ?? 'Draft') }}</span>
                        @endif
                    </div>
                    <div class="db-proj-row2">
                        <span class="db-proj-type"
                              style="background:{{ $tc['bg'] }};color:{{ $tc['txt'] }};border:1px solid {{ $tc['bd'] }};">
                            {{ $tc['label'] }}
                        </span>
                        <span class="db-proj-dot">·</span>
                        <span class="db-proj-age">{{ $proj->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="db-proj-row3">
                        <span class="db-proj-files">{{ $proj->files_count ?? 0 }} files</span>
                        <a class="db-proj-cta"
                           href="{{ route('builder.show', $proj) }}"
                           style="background:linear-gradient(135deg,{{ $tc['from'] }},{{ $tc['to'] }});"
                           onclick="event.stopPropagation()">
                            <svg style="width:9px;height:9px;stroke:currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            Builder
                        </a>
                    </div>
                </a>
                @endforeach

                <a href="{{ route('projects.create') }}" class="db-new-card">
                    <div class="db-new-ico">
                        <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="db-new-lbl">New Project</span>
                </a>
            </div>
            @endif
        </div>

        {{-- Activity feed ── --}}
        <div class="db-section">
            <div class="db-section-hd">
                <span class="db-section-title">Recent Activity</span>
                <span style="font-size:10.5px;color:#94a3b8;">AI sessions &amp; deployments</span>
            </div>
            @if($feedItems->isEmpty())
            <div style="padding:28px;text-align:center;">
                <p style="font-size:12px;color:#94a3b8;">No activity yet — start building to see your history here.</p>
            </div>
            @else
            @foreach($feedItems as $item)
            <div class="db-feed-row">
                @if($item['kind'] === 'ai')
                <div class="db-feed-dot" style="background:#fdf4ff;border:1px solid #ede9fe;">
                    <svg style="width:11px;height:11px;stroke:#8b5cf6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                @elseif(($item['status'] ?? '') === 'success')
                <div class="db-feed-dot" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <svg style="width:11px;height:11px;stroke:#16a34a" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                @elseif(($item['status'] ?? '') === 'failed')
                <div class="db-feed-dot" style="background:#fef2f2;border:1px solid #fecaca;">
                    <svg style="width:11px;height:11px;stroke:#dc2626" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                @else
                <div class="db-feed-dot" style="background:#fffbeb;border:1px solid #fde68a;">
                    <svg style="width:11px;height:11px;stroke:#d97706" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118h4z"/>
                    </svg>
                </div>
                @endif
                <div style="flex:1;min-width:0;">
                    <div class="db-feed-title">{{ Str::limit($item['title'], 50) }}</div>
                    <div class="db-feed-meta">
                        @if($item['proj'])<span style="color:#6366f1;font-weight:600;">{{ $item['proj'] }}</span> &middot; @endif
                        {{ $item['sub'] }} &middot; {{ $item['time']->diffForHumans() }}
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

    </div>{{-- /left --}}

    {{-- SIDEBAR ──────────────────────────────────────── --}}
    <div class="db-sidebar">

        {{-- Quick Actions ── --}}
        <div class="db-widget">
            <div class="db-widget-hd">Quick Actions</div>
            @foreach([
                ['href'=>route('projects.create'),           'label'=>'New Project',    'hint'=>'Build with AI',         'bg'=>'#eef2ff','ic'=>'#6366f1','path'=>'M12 4v16m8-8H4'],
                ['href'=>route('marketplace.index'),         'label'=>'Module Store',   'hint'=>'30+ free modules',      'bg'=>'#ecfdf5','ic'=>'#10b981','path'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['href'=>route('marketplace.upload-install'),'label'=>'Upload Package', 'hint'=>'Install a .zip module', 'bg'=>'#fdf4ff','ic'=>'#8b5cf6','path'=>'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                ['href'=>route('settings.index'),            'label'=>'Settings',       'hint'=>'AI keys · branding',    'bg'=>'#f0f9ff','ic'=>'#0ea5e9','path'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ] as $a)
            <a href="{{ $a['href'] }}" class="db-action">
                <div class="db-action-ico" style="background:{{ $a['bg'] }};">
                    <svg style="width:13px;height:13px;stroke:{{ $a['ic'] }}" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $a['path'] }}"/>
                    </svg>
                </div>
                <div>
                    <div class="db-action-label">{{ $a['label'] }}</div>
                    <div class="db-action-hint">{{ $a['hint'] }}</div>
                </div>
                <svg style="width:10px;height:10px;stroke:#d1d5db;flex-shrink:0;" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>

        {{-- Templates ── --}}
        <div class="db-widget">
            <div class="db-widget-hd">
                Start a Template
                <span style="font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:99px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">0 tokens</span>
            </div>
            <div style="padding:6px 0 8px;">
                @foreach([
                    ['e'=>'🛒','l'=>'eCommerce', 't'=>'ecommerce', 'c'=>'#10b981'],
                    ['e'=>'👥','l'=>'CRM System', 't'=>'crm',       'c'=>'#8b5cf6'],
                    ['e'=>'🏥','l'=>'Hospital',   't'=>'hospital',  'c'=>'#ef4444'],
                    ['e'=>'🚀','l'=>'SaaS App',   't'=>'saas',      'c'=>'#6366f1'],
                    ['e'=>'🎓','l'=>'School ERP', 't'=>'school',    'c'=>'#06b6d4'],
                    ['e'=>'🍽️','l'=>'Restaurant', 't'=>'restaurant','c'=>'#f97316'],
                ] as $t)
                <a href="{{ route('projects.create') }}?template={{ $t['t'] }}" class="db-tpl">
                    <span class="db-tpl-ico" style="background:{{ $t['c'] }}15;border:1px solid {{ $t['c'] }}25;">{{ $t['e'] }}</span>
                    <span class="db-tpl-lbl">{{ $t['l'] }}</span>
                    <svg style="width:9px;height:9px;stroke:#d1d5db;flex-shrink:0;" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>

        {{-- System ── --}}
        <div class="db-widget">
            <div class="db-widget-hd">
                System
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:99px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
                    <span style="width:5px;height:5px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                    Healthy
                </span>
            </div>
            @php
            $sys = [
                ['k'=>'Laravel', 'v'=>'v'.app()->version(),                              'ok'=>true],
                ['k'=>'PHP',     'v'=>phpversion(),                                      'ok'=>PHP_MAJOR_VERSION>=8],
                ['k'=>'Cache',   'v'=>ucfirst(config('cache.default','file')),           'ok'=>true],
                ['k'=>'Session', 'v'=>ucfirst(config('session.driver','file')),          'ok'=>true],
                ['k'=>'Storage', 'v'=>is_writable(storage_path()) ? 'Writable':'Error', 'ok'=>is_writable(storage_path())],
            ];
            @endphp
            @foreach($sys as $s)
            <div class="db-sys-row">
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="width:5px;height:5px;border-radius:50%;background:{{ $s['ok']?'#22c55e':'#f59e0b' }};display:inline-block;flex-shrink:0;"></span>
                    <span style="font-size:11.5px;color:#475569;">{{ $s['k'] }}</span>
                </div>
                <span style="font-size:10.5px;color:#94a3b8;font-family:monospace;">{{ $s['v'] }}</span>
            </div>
            @endforeach
        </div>

    </div>{{-- /sidebar --}}

</div>{{-- /layout --}}
</div>{{-- /page --}}
@endsection
