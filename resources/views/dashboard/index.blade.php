@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Overview')

@php
$hour      = now()->hour;
$greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$storageMB = round($stats['storage'] / 1048576, 1);
$userName  = explode(' ', auth()->user()->name)[0];

// AI Provider status
$activeAiProvider = \App\Models\AIProvider::where('user_id', auth()->id())
    ->where('is_active', true)
    ->orderByDesc('is_default')
    ->latest()
    ->first();
$aiProviderReady = (bool) $activeAiProvider;
$providerLabel   = $activeAiProvider?->name ?? 'No provider';

$readinessChecks = [
    $aiProviderReady,
    $stats['projects'] > 0,
    $stats['deployments_total'] > 0,
    $stats['modules_installed'] > 0,
    $stats['ai_week'] > 0,
];
$readinessScore = round((collect($readinessChecks)->filter()->count() / count($readinessChecks)) * 100);
$smartSignals = [
    ['label' => 'AI Provider', 'value' => $aiProviderReady ? $providerLabel : 'Needs key', 'ok' => $aiProviderReady],
    ['label' => 'Projects', 'value' => $stats['projects'].' total', 'ok' => $stats['projects'] > 0],
    ['label' => 'Deployments', 'value' => $stats['deploy_rate'].'% success', 'ok' => $stats['deployments_total'] > 0 && $stats['deploy_rate'] >= 70],
    ['label' => 'Modules', 'value' => $stats['modules_installed'].' installed', 'ok' => $stats['modules_installed'] > 0],
];
$nextAction = !$aiProviderReady
    ? ['label' => 'Connect AI Provider', 'href' => route('settings.index') . '#ai', 'hint' => 'Unlock generation and project intelligence']
    : ($stats['projects'] === 0
        ? ['label' => 'Create First Project', 'href' => route('projects.create'), 'hint' => 'Start with a real app blueprint']
        : ($stats['modules_installed'] === 0
            ? ['label' => 'Install a Module', 'href' => route('marketplace.index'), 'hint' => 'Add CRUD-ready capability without AI tokens']
            : ['label' => 'Open Builder', 'href' => $recentProjects->first() ? route('builder.show', $recentProjects->first()) : route('projects.create'), 'hint' => 'Continue your most recent build']));
$smartTips = collect([
    !$aiProviderReady ? 'Add an AI provider before starting serious builds.' : null,
    $stats['projects'] > 0 && $stats['ai_week'] === 0 ? 'No AI sessions this week. Resume a project to keep momentum.' : null,
    $stats['deployments_total'] > 0 && $stats['deploy_rate'] < 70 ? 'Deployment success is low. Review the latest failed build before adding features.' : null,
    $stats['modules_installed'] === 0 ? 'Install one zero-token module to speed up CRUD delivery.' : null,
    $stats['projects_week'] > 0 ? $stats['projects_week'].' new project'.($stats['projects_week'] === 1 ? '' : 's').' started this week.' : null,
])->filter()->take(3)->values();

$focusLabel = !$aiProviderReady
    ? 'Setup AI foundation'
    : ($stats['projects'] === 0
        ? 'Start first product'
        : ($stats['deployments_total'] > 0 && $stats['deploy_rate'] < 70
            ? 'Stabilize delivery'
            : ($stats['modules_installed'] === 0 ? 'Add module coverage' : 'Ship next feature')));
$riskLabel = !$aiProviderReady
    ? 'Provider missing'
    : ($stats['deployments_total'] > 0 && $stats['deploy_rate'] < 70 ? 'Deploy risk' : 'Low risk');
$velocityLabel = $stats['projects_week'] > 0
    ? $stats['projects_week'].' started this week'
    : ($stats['ai_week'] > 0 ? $stats['ai_week'].' AI sessions this week' : 'Needs activity');
$tokenPlan = $stats['modules_installed'] > 0
    ? $stats['modules_installed'].' installed modules'
    : 'Use 0-token modules first';
$smartBrief = [
    ['label' => 'Focus', 'value' => $focusLabel, 'color' => '#6366f1'],
    ['label' => 'Risk', 'value' => $riskLabel, 'color' => $riskLabel === 'Low risk' ? '#10b981' : '#f59e0b'],
    ['label' => 'Velocity', 'value' => $velocityLabel, 'color' => '#0ea5e9'],
    ['label' => 'Token Plan', 'value' => $tokenPlan, 'color' => '#8b5cf6'],
];

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

$feedAll = collect();
foreach($recentActivity as $c) {
    $feedAll->push(['kind'=>'ai','title'=>$c->title ?? 'AI Conversation','proj'=>$c->project->name ?? null,'sub'=>$c->message_count.' messages','time'=>$c->updated_at,'status'=>null]);
}
foreach($recentDeployments as $d) {
    $feedAll->push(['kind'=>'deploy','title'=>$d->project->name ?? 'Project','proj'=>null,'sub'=>ucfirst($d->status).' deployment','time'=>$d->created_at,'status'=>$d->status]);
}
$feedAll  = $feedAll->sortByDesc('time')->take(10)->values();
$feedAI   = $feedAll->where('kind','ai')->values();
$feedDep  = $feedAll->where('kind','deploy')->values();
@endphp

@push('head')
<style>
.db-greet {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 20px 24px;
    background: linear-gradient(120deg, var(--surface-base) 0%, color-mix(in srgb, var(--brand) 5%, var(--surface-base)) 100%);
    border: 1px solid var(--border);
    border-left: 3px solid var(--brand);
    border-radius: 16px;
    box-shadow: var(--shadow);
}
.db-greet-avatar { width:44px;height:44px;border-radius:12px;border:2px solid var(--brand);flex-shrink:0;box-shadow:0 0 0 3px var(--brand-ring); }
.db-greet-name   { font-size:18px;font-weight:800;color:var(--text-1);letter-spacing:-.025em;line-height:1.15; }
.db-greet-sub    { font-size:12px;color:var(--text-3);margin-top:2px; }

/* Smart command center */
.db-smart {
    display:grid;
    grid-template-columns: 1.2fr .8fr;
    gap:14px;
}
.db-smart-card {
    background:linear-gradient(135deg, var(--surface-base), color-mix(in srgb,var(--brand) 3%,var(--surface-base)));
    border:1px solid var(--border);
    border-radius:16px;
    box-shadow:var(--shadow);
    overflow:hidden;
}
.db-smart-main { padding:20px 22px;display:flex;align-items:center;justify-content:space-between;gap:18px; }
.db-smart-kicker { font-size:10px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;color:var(--text-3); }
.db-smart-title { font-size:18px;font-weight:850;color:var(--text-1);margin-top:4px;letter-spacing:-.02em; }
.db-smart-hint { font-size:12px;color:var(--text-3);margin-top:4px;line-height:1.55; }
.db-score {
    width:86px;height:86px;border-radius:22px;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    background:conic-gradient(var(--brand) calc(var(--score) * 1%), var(--surface-overlay) 0);
    position:relative;flex-shrink:0;
}
.db-score::before { content:'';position:absolute;inset:7px;border-radius:17px;background:var(--surface-base); }
.db-score-val { position:relative;font-size:24px;font-weight:850;color:var(--text-1);line-height:1; }
.db-score-lbl { position:relative;font-size:9px;font-weight:700;color:var(--text-3);text-transform:uppercase;margin-top:3px; }
.db-signal-row { display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--border); }
.db-signal { padding:11px 14px;border-right:1px solid var(--border); }
.db-signal:last-child { border-right:none; }
.db-signal-label { font-size:10px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em; }
.db-signal-val { display:flex;align-items:center;gap:6px;font-size:12px;font-weight:650;color:var(--text-2);margin-top:4px;min-width:0; }
.db-signal-dot { width:6px;height:6px;border-radius:99px;flex-shrink:0; }
.db-next { padding:18px 20px;display:flex;flex-direction:column;justify-content:space-between;gap:14px;height:100%; }
.db-tip-list { display:flex;flex-direction:column;gap:8px;margin-top:14px; }
.db-tip { display:flex;align-items:flex-start;gap:8px;font-size:11.5px;color:var(--text-2);line-height:1.45; }
.db-tip-dot { width:6px;height:6px;border-radius:99px;background:var(--brand);margin-top:5px;flex-shrink:0;opacity:.75; }
.db-brief-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:16px; }
.db-brief-item {
    min-width:0;padding:10px 11px;border-radius:12px;
    background:color-mix(in srgb,var(--brief-c) 7%,var(--surface-base));
    border:1px solid color-mix(in srgb,var(--brief-c) 18%,var(--border));
}
.db-brief-label { font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3); }
.db-brief-value { margin-top:4px;font-size:11.5px;font-weight:750;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.db-priority-grid { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px; }
.db-priority-mini {
    padding:9px 10px;border-radius:11px;background:var(--surface-raised);
    border:1px solid var(--border);min-width:0;
}
.db-priority-mini span { display:block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3); }
.db-priority-mini strong { display:block;margin-top:4px;font-size:11.5px;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.db-next-link {
    display:inline-flex;align-items:center;justify-content:center;gap:7px;
    padding:9px 16px;border-radius:11px;text-decoration:none;
    background:color-mix(in srgb,var(--brand) 10%,#fff);
    color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 28%,transparent);
    font-size:12.5px;font-weight:750;transition:all .18s ease;width:fit-content;
}
.db-next-link:hover { background:var(--brand);color:#fff;border-color:var(--brand);box-shadow:0 4px 14px var(--brand-ring);transform:translateY(-1px); }

/* ── KPI grid — 6 cards, 3 columns each row ── */
.db-kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.db-kpi {
    background: var(--surface-base);
    border: 1px solid var(--border);
    border-top: 3px solid transparent;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: var(--shadow);
    transition: box-shadow .18s, transform .18s;
    cursor: default;
}
.db-kpi:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.db-kpi-top  { display:flex;align-items:flex-start;justify-content:space-between;gap:8px; }
.db-kpi-ico  { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.db-kpi-ico svg { width:16px;height:16px; }
.db-trend {
    display:inline-flex;align-items:center;gap:3px;
    font-size:10px;font-weight:700;padding:3px 7px;border-radius:99px;
}
.db-trend-up   { background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
.db-trend-down { background:#fff1f2;color:#b91c1c;border:1px solid #fecdd3; }
.db-trend-neu  { background:var(--surface-overlay);color:var(--text-3);border:1px solid var(--border); }
.db-kpi-val   { font-size:30px;font-weight:800;color:var(--text-1);letter-spacing:-.04em;line-height:1;margin-top:10px; }
.db-kpi-unit  { font-size:13px;font-weight:600;color:var(--text-3);margin-left:2px; }
.db-kpi-label { font-size:11.5px;color:var(--text-2);font-weight:500;margin-top:6px; }
.db-kpi-bar   { height:3px;border-radius:99px;margin-top:12px;background:var(--surface-overlay);overflow:hidden; }
.db-kpi-bar-fill { height:100%;border-radius:99px;transition:width .6s var(--ease-out); }

/* ── Main layout ── */
.db-layout { display:grid;grid-template-columns:1fr 260px;gap:14px;align-items:start; }

/* ── Section card ── */
.db-section { background:var(--surface-base);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);overflow:hidden; }
.db-section-hd { display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);background:var(--surface-raised); }
.db-section-title { font-size:13px;font-weight:700;color:var(--text-1); }
.db-section-count { font-size:10.5px;font-weight:600;color:var(--text-3);background:var(--surface-raised);border:1px solid var(--border);padding:2px 8px;border-radius:99px;margin-left:6px; }
.db-section-link  { font-size:11.5px;font-weight:600;color:var(--brand);text-decoration:none;padding:4px 10px;border-radius:7px;background:color-mix(in srgb,var(--brand) 8%,transparent);transition:background .12s; }
.db-section-link:hover { background:color-mix(in srgb,var(--brand) 15%,transparent); }

/* ── Tabs ── */
.db-tabs { display:flex;gap:2px;padding:10px 14px;background:var(--surface-raised);border-bottom:1px solid var(--border); }
.db-tab {
    padding:5px 14px;border-radius:8px;font-size:12px;font-weight:600;
    cursor:pointer;color:var(--text-3);border:none;background:none;
    transition:all .13s;
}
.db-tab.active { background:var(--surface-base);color:var(--text-1);box-shadow:var(--shadow-xs);border:1px solid var(--border); }
.db-tab:not(.active):hover { color:var(--text-1);background:var(--surface-overlay); }

/* ── Projects ── */
.db-proj-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:1px;background:var(--border); }
.db-proj-card {
    background:var(--surface-base);padding:16px 18px;
    text-decoration:none;color:inherit;
    display:flex;flex-direction:column;gap:12px;
    transition:background .2s ease, box-shadow .22s ease;
    position:relative;
}
.db-proj-card:hover {
    background: color-mix(in srgb, var(--c, var(--brand)) 5%, var(--surface-base));
    box-shadow: inset 0 -2px 0 color-mix(in srgb, var(--c, var(--brand)) 35%, transparent);
}
.db-proj-stripe {
    position:absolute;left:0;top:0;bottom:0;width:3px;
    border-radius:2px 0 0 2px;opacity:0;
    transition:opacity .2s ease;
}
.db-proj-card:hover .db-proj-stripe { opacity:1; }
.db-proj-row1 { display:flex;align-items:center;justify-content:space-between;gap:8px; }
.db-proj-ico  { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0; }
.db-proj-name { font-size:13.5px;font-weight:700;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;margin:0 8px; }
.db-proj-status { font-size:9px;font-weight:700;padding:2px 7px;border-radius:99px;white-space:nowrap;flex-shrink:0; }
.db-proj-row2 { display:flex;align-items:center;gap:6px;flex-wrap:wrap; }
.db-proj-type { font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px; }
.db-proj-dot  { color:var(--border-strong); }
.db-proj-age  { font-size:10.5px;color:var(--text-3); }
.db-proj-row3 { display:flex;align-items:center;justify-content:space-between; }
.db-proj-files { font-size:10px;color:var(--text-3); }
.db-proj-cta  {
    display:inline-flex;align-items:center;gap:4px;
    font-size:10.5px;font-weight:700;
    padding:5px 12px;border-radius:7px;text-decoration:none;
    background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff);
    color: var(--c, var(--brand));
    border: 1.5px solid color-mix(in srgb, var(--c, var(--brand)) 25%, transparent);
    transition: all .18s ease;
}
.db-proj-cta:hover {
    background: var(--c, var(--brand));
    color: #fff;
    border-color: var(--c, var(--brand));
    box-shadow: 0 3px 10px color-mix(in srgb, var(--c, var(--brand)) 28%, transparent);
    transform:translateY(-1px);
}
.db-new-card {
    background:var(--surface-base);padding:28px 18px;
    text-decoration:none;display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:8px;transition:background .15s;min-height:140px;
}
.db-new-card:hover { background:color-mix(in srgb,var(--brand) 6%,transparent); }
.db-new-ico {
    width:36px;height:36px;border-radius:10px;
    background:var(--surface-overlay);border:1.5px dashed var(--border);
    display:flex;align-items:center;justify-content:center;transition:all .15s;
}
.db-new-card:hover .db-new-ico { background:color-mix(in srgb,var(--brand) 12%,transparent);border-color:color-mix(in srgb,var(--brand) 40%,transparent); }
.db-new-lbl { font-size:12px;font-weight:600;color:var(--text-3);transition:color .15s; }
.db-new-card:hover .db-new-lbl { color:var(--brand); }

/* ── Empty state ── */
.db-empty { padding:52px 24px;text-align:center;display:flex;flex-direction:column;align-items:center; }
.db-empty-ico { width:56px;height:56px;border-radius:14px;background:color-mix(in srgb,var(--brand) 10%,transparent);border:1px solid color-mix(in srgb,var(--brand) 25%,transparent);display:flex;align-items:center;justify-content:center;margin-bottom:16px; }
.db-empty h3 { font-size:14px;font-weight:700;color:var(--text-1);margin-bottom:6px; }
.db-empty p  { font-size:12px;color:var(--text-3);margin-bottom:20px;max-width:240px;line-height:1.7; }
.db-empty-btn {
    display:inline-flex;align-items:center;gap:6px;padding:10px 22px;
    border-radius:11px;font-size:13px;font-weight:700;text-decoration:none;
    background: color-mix(in srgb, var(--brand) 10%, #fff);
    color: var(--brand);
    border: 1.5px solid color-mix(in srgb, var(--brand) 28%, transparent);
    transition:all .18s ease;
}
.db-empty-btn:hover {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
    box-shadow: 0 4px 14px var(--brand-ring);
    transform:translateY(-1px);
}

/* ── Activity feed ── */
.db-feed-row { display:flex;align-items:flex-start;gap:12px;padding:12px 18px;border-bottom:1px solid var(--border);transition:background .12s; }
.db-feed-row:last-child { border-bottom:none; }
.db-feed-row:hover { background:var(--surface-raised); }
.db-feed-dot { width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px; }
.db-feed-dot svg { width:11px;height:11px; }
.db-feed-title { font-size:12.5px;font-weight:600;color:var(--text-1);overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.db-feed-meta  { font-size:10.5px;color:var(--text-3);margin-top:2px; }

/* ── Sidebar widgets ── */
.db-sidebar { display:flex;flex-direction:column;gap:12px; }
.db-widget { background:var(--surface-base);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden; }
.db-widget-hd { display:flex;align-items:center;justify-content:space-between;padding:12px 15px;border-bottom:1px solid var(--border);background:var(--surface-raised);font-size:12px;font-weight:700;color:var(--text-1); }
.db-action { display:flex;align-items:center;gap:10px;padding:11px 14px;text-decoration:none;border-bottom:1px solid var(--border);transition:background .12s; }
.db-action:last-child { border-bottom:none; }
.db-action:hover { background:var(--surface-raised); }
.db-action-ico { width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.db-action-ico svg { width:13px;height:13px; }
.db-action-label { font-size:12.5px;font-weight:600;color:var(--text-1);flex:1; }
.db-action-hint  { font-size:10.5px;color:var(--text-3);margin-top:1px; }
.db-tpl { display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:8px;text-decoration:none;margin:2px 6px;transition:background .12s; }
.db-tpl:hover { background:color-mix(in srgb,var(--brand) 7%,transparent); }
.db-tpl-ico { width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0; }
.db-tpl-lbl { font-size:12px;font-weight:500;color:var(--text-2);flex:1; }
.db-sys-row { display:flex;align-items:center;justify-content:space-between;padding:7px 14px;border-bottom:1px solid var(--border); }
.db-sys-row:last-child { border-bottom:none; }

/* ── Responsive ── */
@media(max-width:1180px) {
    .db-kpi-row { grid-template-columns:repeat(3,1fr); }
    .db-layout  { grid-template-columns:1fr; }
    .db-smart { grid-template-columns:1fr; }
}
@media(max-width:768px) {
    .db-kpi-row { grid-template-columns:repeat(2,1fr); }
    .db-proj-grid { grid-template-columns:1fr; }
    .db-greet { flex-direction:column;align-items:flex-start;gap:12px;padding:16px; }
    .db-smart-main { align-items:flex-start;flex-direction:column; }
    .db-signal-row { grid-template-columns:1fr; }
    .db-brief-grid { grid-template-columns:repeat(2,1fr); }
    .db-signal { border-right:none;border-bottom:1px solid var(--border); }
    .db-signal:last-child { border-bottom:none; }
}
@media(max-width:480px) {
    .db-kpi-row { grid-template-columns:1fr 1fr; }
    .db-kpi { padding:14px 12px; }
    .db-kpi-val { font-size:22px; }
}
</style>
@endpush

@section('content')
<div style="display:flex;flex-direction:column;gap:16px;">

{{-- ══ GREETING ══════════════════════════════════════════════════ --}}
<div class="db-greet">
    <div style="display:flex;align-items:center;gap:13px;">
        <img src="{{ auth()->user()->avatar_url }}" class="db-greet-avatar" alt="{{ $userName }}">
        <div>
            <div class="db-greet-name">{{ $greeting }}, {{ $userName }}</div>
            <div class="db-greet-sub">{{ now()->format('l, F j, Y') }} — workspace ready</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        @if($aiProviderReady)
        <div style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:99px;font-size:12px;font-weight:600;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
            <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
            {{ $providerLabel }} Active
        </div>
        @else
        <a href="{{ route('settings.index') }}#ai" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:99px;font-size:12px;font-weight:600;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;text-decoration:none;">
            <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Add AI Key
        </a>
        @endif
        <a href="{{ route('projects.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:99px;font-size:12px;font-weight:700;text-decoration:none;background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 28%,transparent);transition:all .18s ease;"
           onmouseover="this.style.background='var(--brand)';this.style.color='#fff';this.style.boxShadow='0 4px 14px var(--brand-ring)'"
           onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)';this.style.boxShadow='none'">
            <svg style="width:11px;height:11px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            New Project
        </a>
    </div>
</div>

{{-- ══ KPI ROW — 6 cards ═══════════════════════════════════════ --}}
<div class="db-smart">
    <div class="db-smart-card">
        <div class="db-smart-main">
            <div>
                <div class="db-smart-kicker">AI Command Center</div>
                <div class="db-smart-title">
                    {{ $readinessScore >= 80 ? 'Production path is clear' : ($readinessScore >= 45 ? 'Almost ready for serious building' : 'Setup needs attention first') }}
                </div>
                <div class="db-smart-hint">
                    Live workspace readout based on AI keys, project momentum, module coverage, and deployment health.
                </div>
                @if($smartTips->isNotEmpty())
                <div class="db-tip-list">
                    @foreach($smartTips as $tip)
                    <div class="db-tip"><span class="db-tip-dot"></span><span>{{ $tip }}</span></div>
                    @endforeach
                </div>
                @endif
                <div class="db-brief-grid">
                    @foreach($smartBrief as $brief)
                    <div class="db-brief-item" style="--brief-c:{{ $brief['color'] }};">
                        <div class="db-brief-label">{{ $brief['label'] }}</div>
                        <div class="db-brief-value">{{ $brief['value'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="db-score" style="--score:{{ $readinessScore }};">
                <div class="db-score-val">{{ $readinessScore }}%</div>
                <div class="db-score-lbl">Ready</div>
            </div>
        </div>
        <div class="db-signal-row">
            @foreach($smartSignals as $signal)
            <div class="db-signal">
                <div class="db-signal-label">{{ $signal['label'] }}</div>
                <div class="db-signal-val">
                    <span class="db-signal-dot" style="background:{{ $signal['ok'] ? '#22c55e' : '#f59e0b' }};"></span>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $signal['value'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="db-smart-card">
        <div class="db-next">
            <div>
                <div class="db-smart-kicker">Priority</div>
                <div class="db-smart-title">{{ $nextAction['label'] }}</div>
                <div class="db-smart-hint">{{ $nextAction['hint'] }}</div>
                <div class="db-priority-grid">
                    <div class="db-priority-mini">
                        <span>Now</span>
                        <strong>{{ $focusLabel }}</strong>
                    </div>
                    <div class="db-priority-mini">
                        <span>Watch</span>
                        <strong>{{ $riskLabel }}</strong>
                    </div>
                </div>
            </div>
            <a href="{{ $nextAction['href'] }}" class="db-next-link">
                Continue
                <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<div class="db-kpi-row">

    {{-- Projects --}}
    <div class="db-kpi" style="border-top-color:#6366f1;">
        <div class="db-kpi-top">
            <div class="db-kpi-ico" style="background:#eef2ff;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#6366f1" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            @if($stats['projects_week'] > 0)
            <span class="db-trend db-trend-up">
                <svg style="width:9px;height:9px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/></svg>
                +{{ $stats['projects_week'] }} this week
            </span>
            @else
            <span class="db-trend db-trend-neu">No new this week</span>
            @endif
        </div>
        <div class="db-kpi-val">{{ $stats['projects'] }}</div>
        <div class="db-kpi-label">Total Projects</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, max(4, $stats['projects'] * 10)) }}%;background:var(--brand);"></div></div>
    </div>

    {{-- AI Messages --}}
    <div class="db-kpi" style="border-top-color:#8b5cf6;">
        <div class="db-kpi-top">
            <div class="db-kpi-ico" style="background:#fdf4ff;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#8b5cf6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            @if($stats['ai_week'] > 0)
            <span class="db-trend db-trend-up">
                <svg style="width:9px;height:9px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/></svg>
                +{{ $stats['ai_week'] }} this week
            </span>
            @else
            <span class="db-trend db-trend-neu">No sessions this week</span>
            @endif
        </div>
        <div class="db-kpi-val">{{ number_format($stats['ai_messages']) }}</div>
        <div class="db-kpi-label">AI Messages</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, max(4, ($stats['ai_messages'] / max(1,500))*100)) }}%;background:#8b5cf6;"></div></div>
    </div>

    {{-- Deployments success --}}
    <div class="db-kpi" style="border-top-color:#10b981;">
        <div class="db-kpi-top">
            <div class="db-kpi-ico" style="background:#ecfdf5;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            @if($stats['deployments_total'] > 0)
            <span class="db-trend {{ $stats['deploy_rate'] >= 80 ? 'db-trend-up' : ($stats['deploy_rate'] >= 50 ? 'db-trend-neu' : 'db-trend-down') }}">
                {{ $stats['deploy_rate'] }}% rate
            </span>
            @else
            <span class="db-trend db-trend-neu">No deploys yet</span>
            @endif
        </div>
        <div class="db-kpi-val">{{ $stats['deployments'] }}</div>
        <div class="db-kpi-label">Successful Deploys</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, max(4, $stats['deploy_rate'])) }}%;background:#10b981;"></div></div>
    </div>

    {{-- Modules --}}
    <div class="db-kpi" style="border-top-color:#0ea5e9;">
        <div class="db-kpi-top">
            <div class="db-kpi-ico" style="background:#f0f9ff;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#0ea5e9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            @if($stats['modules_installed'] > 0)
            <span class="db-trend db-trend-up">
                <svg style="width:9px;height:9px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/></svg>
                Installed
            </span>
            @else
            <span class="db-trend db-trend-neu">None yet</span>
            @endif
        </div>
        <div class="db-kpi-val">{{ $stats['modules_installed'] }}</div>
        <div class="db-kpi-label">Modules Installed</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, max(4, $stats['modules_installed'] * 12)) }}%;background:#0ea5e9;"></div></div>
    </div>

    {{-- AI Conversations --}}
    <div class="db-kpi" style="border-top-color:#f59e0b;">
        <div class="db-kpi-top">
            <div class="db-kpi-ico" style="background:#fffbeb;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            @if($stats['ai_conversations'] > 0)
            <span class="db-trend db-trend-up">Active</span>
            @else
            <span class="db-trend db-trend-neu">No sessions</span>
            @endif
        </div>
        <div class="db-kpi-val">{{ $stats['ai_conversations'] }}</div>
        <div class="db-kpi-label">AI Sessions</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ min(100, max(4, $stats['ai_conversations'] * 8)) }}%;background:#f59e0b;"></div></div>
    </div>

    {{-- Storage --}}
    <div class="db-kpi" style="border-top-color:#f97316;">
        <div class="db-kpi-top">
            <div class="db-kpi-ico" style="background:#fff7ed;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#f97316" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
            </div>
            @php $storPct = min(100, ($storageMB / 500)*100); @endphp
            <span class="db-trend {{ $storPct > 80 ? 'db-trend-down' : ($storPct > 50 ? 'db-trend-neu' : 'db-trend-up') }}">
                {{ round($storPct) }}% of 500MB
            </span>
        </div>
        <div style="margin-top:10px;">
            <span class="db-kpi-val">{{ $storageMB }}</span>
            <span class="db-kpi-unit">MB</span>
        </div>
        <div class="db-kpi-label">Storage Used</div>
        <div class="db-kpi-bar"><div class="db-kpi-bar-fill" style="width:{{ max(4, $storPct) }}%;background:#f97316;"></div></div>
    </div>

</div>

{{-- ══ MAIN LAYOUT ═══════════════════════════════════════════════ --}}
<div class="db-layout">

    {{-- LEFT ─────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Projects ── --}}
        <div class="db-section">
            <div class="db-section-hd">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="db-section-title">My Projects</span>
                    @if($stats['projects'] > 0)
                    <span class="db-section-count">{{ $stats['projects'] }} total</span>
                    @endif
                </div>
                <a href="{{ route('projects.index') }}" class="db-section-link">View all →</a>
            </div>

            @if($recentProjects->isEmpty())
            <div class="db-empty">
                <div class="db-empty-ico">
                    <svg style="width:26px;height:26px;" fill="none" viewBox="0 0 24 24" stroke="var(--brand)" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3>No projects yet</h3>
                <p>Describe any application and the AI will architect, code, and deliver it — front-end to database.</p>
                <a href="{{ route('projects.create') }}" class="db-empty-btn">
                    <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Create First Project
                </a>
            </div>
            @else
            <div class="db-proj-grid">
                @foreach($recentProjects as $proj)
                @php $tc = $typeMap[$proj->type] ?? $typeMap['default']; @endphp
                <a class="db-proj-card" href="{{ route('projects.show', $proj) }}" style="--c:{{ $tc['from'] }};">
                    <div class="db-proj-stripe" style="background:{{ $tc['from'] }};"></div>
                    <div class="db-proj-row1">
                        <div class="db-proj-ico" style="background:{{ $tc['bg'] }};border:1px solid {{ $tc['bd'] }};">{{ $tc['emoji'] }}</div>
                        <span class="db-proj-name">{{ $proj->name }}</span>
                        @if($proj->status === 'active')
                        <span class="db-proj-status" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">Active</span>
                        @else
                        <span class="db-proj-status" style="background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border);">{{ ucfirst($proj->status ?? 'Draft') }}</span>
                        @endif
                    </div>
                    <div class="db-proj-row2">
                        <span class="db-proj-type" style="background:{{ $tc['bg'] }};color:{{ $tc['txt'] }};border:1px solid {{ $tc['bd'] }};">{{ $tc['label'] }}</span>
                        <span class="db-proj-dot">·</span>
                        <span class="db-proj-age">{{ $proj->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="db-proj-row3">
                        <span class="db-proj-files">{{ $proj->files_count ?? 0 }} files</span>
                        <a class="db-proj-cta"
                           href="{{ route('builder.show', $proj) }}"
                           style="background:linear-gradient(135deg,{{ $tc['from'] }},{{ $tc['to'] }});"
                           onclick="event.stopPropagation()">
                            <svg style="width:9px;height:9px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            Builder
                        </a>
                    </div>
                </a>
                @endforeach
                <a href="{{ route('projects.create') }}" class="db-new-card">
                    <div class="db-new-ico">
                        <svg style="width:14px;height:14px;stroke:var(--text-3)" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span class="db-new-lbl">New Project</span>
                </a>
            </div>
            @endif
        </div>

        {{-- Tabbed Activity Feed ── --}}
        <div class="db-section" x-data="{ tab: 'all' }">
            <div class="db-section-hd" style="background:var(--surface-raised);">
                <span class="db-section-title">Recent Activity</span>
                <span style="font-size:10.5px;color:var(--text-3);">AI sessions &amp; deployments</span>
            </div>
            <div class="db-tabs">
                <button class="db-tab" :class="{ active: tab==='all' }"    @click="tab='all'">
                    All <span style="margin-left:4px;font-size:9px;background:var(--surface-overlay);border-radius:99px;padding:1px 5px;color:var(--text-3);">{{ $feedAll->count() }}</span>
                </button>
                <button class="db-tab" :class="{ active: tab==='ai' }"    @click="tab='ai'">
                    AI Sessions <span style="margin-left:4px;font-size:9px;background:var(--surface-overlay);border-radius:99px;padding:1px 5px;color:var(--text-3);">{{ $feedAI->count() }}</span>
                </button>
                <button class="db-tab" :class="{ active: tab==='deploy' }" @click="tab='deploy'">
                    Deployments <span style="margin-left:4px;font-size:9px;background:var(--surface-overlay);border-radius:99px;padding:1px 5px;color:var(--text-3);">{{ $feedDep->count() }}</span>
                </button>
            </div>

            {{-- All --}}
            <div x-show="tab==='all'" x-transition>
                @if($feedAll->isEmpty())
                <div style="padding:28px;text-align:center;"><p style="font-size:12px;color:var(--text-3);">No activity yet — start building to see your history here.</p></div>
                @else
                @foreach($feedAll as $item)
                @include('dashboard._feed_row', ['item' => $item])
                @endforeach
                @endif
            </div>

            {{-- AI --}}
            <div x-show="tab==='ai'" x-transition x-cloak>
                @if($feedAI->isEmpty())
                <div style="padding:28px;text-align:center;"><p style="font-size:12px;color:var(--text-3);">No AI sessions yet.</p></div>
                @else
                @foreach($feedAI as $item)
                @include('dashboard._feed_row', ['item' => $item])
                @endforeach
                @endif
            </div>

            {{-- Deployments --}}
            <div x-show="tab==='deploy'" x-transition x-cloak>
                @if($feedDep->isEmpty())
                <div style="padding:28px;text-align:center;"><p style="font-size:12px;color:var(--text-3);">No deployments yet.</p></div>
                @else
                @foreach($feedDep as $item)
                @include('dashboard._feed_row', ['item' => $item])
                @endforeach
                @endif
            </div>

        </div>

    </div>{{-- /left --}}

    {{-- SIDEBAR ─────────────────────────────────────────────── --}}
    <div class="db-sidebar">

        {{-- Quick Actions ── --}}
        <div class="db-widget">
            <div class="db-widget-hd">Quick Actions</div>
            @foreach([
                ['href'=>route('projects.create'),            'label'=>'New Project',    'hint'=>'Build with AI',         'bg'=>'#eef2ff','ic'=>'#6366f1','d'=>'M12 4v16m8-8H4'],
                ['href'=>route('marketplace.index'),          'label'=>'Module Store',   'hint'=>'30+ free modules',      'bg'=>'#ecfdf5','ic'=>'#10b981','d'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['href'=>route('marketplace.upload-install'), 'label'=>'Upload Package', 'hint'=>'Install a .zip module', 'bg'=>'#fdf4ff','ic'=>'#8b5cf6','d'=>'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                ['href'=>route('settings.index'),             'label'=>'Settings',       'hint'=>'AI keys · branding',    'bg'=>'#f0f9ff','ic'=>'#0ea5e9','d'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ] as $a)
            <a href="{{ $a['href'] }}" class="db-action">
                <div class="db-action-ico" style="background:{{ $a['bg'] }};">
                    <svg fill="none" viewBox="0 0 24 24" stroke="{{ $a['ic'] }}" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $a['d'] }}"/>
                    </svg>
                </div>
                <div>
                    <div class="db-action-label">{{ $a['label'] }}</div>
                    <div class="db-action-hint">{{ $a['hint'] }}</div>
                </div>
                <svg style="width:10px;height:10px;stroke:var(--border-strong);flex-shrink:0;" fill="none" viewBox="0 0 24 24" aria-hidden="true">
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
                    ['e'=>'🛒','l'=>'eCommerce', 't'=>'ecommerce','c'=>'#10b981'],
                    ['e'=>'👥','l'=>'CRM System', 't'=>'crm',      'c'=>'#8b5cf6'],
                    ['e'=>'🏥','l'=>'Hospital',   't'=>'hospital', 'c'=>'#ef4444'],
                    ['e'=>'🚀','l'=>'SaaS App',   't'=>'saas',     'c'=>'#6366f1'],
                    ['e'=>'🎓','l'=>'School ERP', 't'=>'school',   'c'=>'#06b6d4'],
                    ['e'=>'🍽️','l'=>'Restaurant', 't'=>'restaurant','c'=>'#f97316'],
                ] as $t)
                <a href="{{ route('projects.create') }}?template={{ $t['t'] }}" class="db-tpl">
                    <span class="db-tpl-ico" style="background:{{ $t['c'] }}18;border:1px solid {{ $t['c'] }}30;">{{ $t['e'] }}</span>
                    <span class="db-tpl-lbl">{{ $t['l'] }}</span>
                    <svg style="width:9px;height:9px;stroke:var(--border-strong);flex-shrink:0;" fill="none" viewBox="0 0 24 24" aria-hidden="true">
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
                ['k'=>'Laravel', 'v'=>'v'.app()->version(),                             'ok'=>true],
                ['k'=>'PHP',     'v'=>PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION,          'ok'=>PHP_MAJOR_VERSION>=8],
                ['k'=>'Cache',   'v'=>ucfirst(config('cache.default','file')),          'ok'=>true],
                ['k'=>'Session', 'v'=>ucfirst(config('session.driver','file')),         'ok'=>true],
                ['k'=>'Storage', 'v'=>is_writable(storage_path())?'Writable':'Error',  'ok'=>is_writable(storage_path())],
                ['k'=>'AI',      'v'=>$aiProviderReady ? $providerLabel : 'No key',    'ok'=>$aiProviderReady],
            ];
            @endphp
            @foreach($sys as $s)
            <div class="db-sys-row">
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="width:5px;height:5px;border-radius:50%;background:{{ $s['ok']?'#22c55e':'#f59e0b' }};display:inline-block;flex-shrink:0;"></span>
                    <span style="font-size:11.5px;color:var(--text-2);">{{ $s['k'] }}</span>
                </div>
                <span style="font-size:10.5px;color:var(--text-3);font-family:monospace;">{{ $s['v'] }}</span>
            </div>
            @endforeach
        </div>

    </div>{{-- /sidebar --}}

</div>{{-- /layout --}}
</div>{{-- /page --}}
@endsection
