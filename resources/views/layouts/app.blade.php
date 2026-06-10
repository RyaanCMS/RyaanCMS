<!DOCTYPE html>
@php
    $userId      = auth()->id();
    $brandColor  = \App\Models\Setting::get('branding.primary_color', '#6366f1', $userId);
    $fontFamily  = \App\Models\Setting::get('branding.font_family',   'Inter',   $userId);
    $logoPath    = \App\Models\Setting::get('branding.logo_path',     null,      $userId);
    $faviconPath = \App\Models\Setting::get('branding.favicon_path',  null,      $userId);
    $fontSlug    = strtolower(str_replace(' ', '+', $fontFamily));
    $showSidebar     = (bool) \App\Models\Setting::get('system.show_dashboard_sidebar', true,  $userId);
    $showTopbar      = (bool) \App\Models\Setting::get('system.show_dashboard_menu',    true,  $userId);
    $sidebarAutoHide = (bool) \App\Models\Setting::get('system.sidebar_auto_hide',     false, $userId);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="appLayout()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    @if($faviconPath)
    <link rel="icon" href="{{ Storage::url($faviconPath) }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ $fontSlug }}:300,400,500,600,700,800&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ═══════════════════════════════════════════════════════════
           DESIGN TOKEN SYSTEM — RyaanCMS v2
           Proper CSS custom properties — no nuclear overrides
        ═══════════════════════════════════════════════════════════ */
        :root {
            /* Brand (user-configurable) */
            --brand:        {{ $brandColor }};
            --brand-dark:   color-mix(in srgb, {{ $brandColor }} 80%, #000);
            --brand-light:  color-mix(in srgb, {{ $brandColor }} 10%, #fff);
            --brand-ring:   color-mix(in srgb, {{ $brandColor }} 25%, transparent);

            /* Surfaces */
            --surface-base:    #ffffff;
            --surface-raised:  #f8fafc;
            --surface-overlay: #f1f5f9;
            --surface-invert:  #0f172a;

            /* Semantic aliases (backward compat) */
            --page-bg:    var(--surface-base);
            --card-bg:    var(--surface-base);
            --card-sub:   var(--surface-raised);
            --sidebar-bg: var(--surface-base);
            --header-bg:  var(--surface-base);
            --hover-bg:   var(--surface-raised);
            --input-bg:   var(--surface-base);

            /* Borders */
            --border:        #e2e8f0;
            --border-strong: #cbd5e1;

            /* Text */
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;

            /* Shadows */
            --shadow-xs: 0 1px 2px rgba(0,0,0,.04);
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow:    0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.03);
            --shadow-md: 0 4px 12px rgba(0,0,0,.06), 0 2px 4px rgba(0,0,0,.04);
            --shadow-lg: 0 8px 24px rgba(0,0,0,.08), 0 4px 8px rgba(0,0,0,.04);
            --shadow-xl: 0 20px 40px rgba(0,0,0,.10), 0 8px 16px rgba(0,0,0,.06);

            /* Animation */
            --ease-out:    cubic-bezier(0.16, 1, 0.3, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --dur-fast:    100ms;
            --dur-base:    150ms;
            --dur-slow:    250ms;

            /* Sidebar */
            --sidebar-w-collapsed: 64px;
            --sidebar-w-expanded:  256px;
        }

        /* ─── Base ──────────────────────────────────────── */
        *, *::before, *::after {
            box-sizing: border-box;
            transition: background-color var(--dur-base) ease,
                        border-color var(--dur-fast) ease,
                        color var(--dur-fast) ease;
        }
        html { color-scheme: light; }
        body {
            font-family: '{{ $fontFamily }}', 'Inter', system-ui, sans-serif;
            background: var(--surface-base);
            color: var(--text-1);
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Form elements ─────────────────────────────── */
        input, select, textarea {
            background: var(--input-bg) !important;
            border-color: var(--border) !important;
            color: var(--text-1) !important;
        }
        input::placeholder, textarea::placeholder { color: var(--text-3) !important; }

        /* ─── Scrollbar ─────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--border-strong); }

        /* ─── Focus ring ────────────────────────────────── */
        :focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }

        /* ── Override ONLY dark backgrounds (force light mode) ── */
        /* Do NOT override text-white — it's needed on colored buttons */
        .bg-gray-950, .bg-gray-900 { background-color: #ffffff !important; }
        .bg-gray-800               { background-color: #f9fafb !important; }
        .bg-gray-700               { background-color: #f3f4f6 !important; }
        .bg-gray-600               { background-color: #e5e7eb !important; }
        .bg-gray-950\/50,.bg-gray-950\/90,.bg-gray-950\/95 { background-color: rgba(255,255,255,.95) !important; }
        .bg-gray-900\/50 { background-color: rgba(255,255,255,.5)  !important; }
        .bg-gray-900\/70 { background-color: rgba(255,255,255,.7)  !important; }
        .bg-gray-900\/90,.bg-gray-900\/95 { background-color: rgba(255,255,255,.95) !important; }
        .bg-gray-800\/20 { background-color: rgba(249,250,251,.2)  !important; }
        .bg-gray-800\/40 { background-color: rgba(249,250,251,.4)  !important; }
        .bg-gray-800\/60 { background-color: rgba(249,250,251,.6)  !important; }
        .bg-gray-800\/80 { background-color: rgba(249,250,251,.8)  !important; }

        /* Override near-white text colors to be readable on light bg */
        .text-gray-100 { color: #1e293b !important; }
        .text-gray-200 { color: #334155 !important; }
        .text-gray-300 { color: #475569 !important; }
        .hover\:text-gray-100:hover { color: #0f172a !important; }
        .hover\:text-gray-200:hover { color: #1e293b !important; }
        .hover\:text-gray-300:hover { color: #334155 !important; }

        /* Borders */
        .border-gray-800,.border-gray-700 { border-color: #e2e8f0 !important; }
        .border-gray-600 { border-color: #cbd5e1 !important; }
        .divide-gray-800 > * + *,.divide-gray-700 > * + * { border-color: #e2e8f0 !important; }

        /* Shadows */
        .shadow-xl         { box-shadow: 0 8px 24px rgba(0,0,0,.06) !important; }
        .shadow-black\/50  { box-shadow: 0 8px 24px rgba(0,0,0,.05) !important; }
        .backdrop-blur-xl  { background-color: rgba(255,255,255,.92) !important; }

        /* Builder code editor */
        #codeEditor { background-color: #f8fafc !important; color: #1e293b !important; }

        /* Attribution */
        .ryaan-powered { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.02em;text-decoration:none; }
        .ryaan-powered:hover { color:#6366f1; }
        .ryaan-powered span  { color:#6366f1;font-weight:700; }

        /* x-cloak */
        [x-cloak] { display: none !important; }

        /* ═══════════════════════════════════════════════════════════
           GLOBAL CARD SYSTEM — used by every page
           Set --c on the card element for per-card accent color.
           Default falls back to var(--brand).
        ═══════════════════════════════════════════════════════════ */

        /* ── Base card ── */
        .sys-card {
            border-radius: 16px;
            background: var(--surface-base);
            border: 1px solid var(--border);
            border-left: 3px solid transparent;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: box-shadow .22s ease, transform .22s ease,
                        border-color .18s ease, background .18s ease;
            position: relative;
        }
        .sys-card:hover {
            border-left-color: var(--c, var(--brand));
            box-shadow: 0 10px 36px color-mix(in srgb, var(--c, var(--brand)) 20%, transparent),
                        0 2px 10px  color-mix(in srgb, var(--c, var(--brand)) 10%, transparent);
            transform: translateY(-3px);
        }
        /* clickable card = pointer */
        a.sys-card, button.sys-card { cursor: pointer; text-decoration: none; color: inherit; }

        /* ── Card sections ── */
        .sys-card-hd {
            padding: 14px 18px;
            background: var(--surface-raised);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px;
        }
        .sys-card-body {
            padding: 16px 18px;
            display: flex; flex-direction: column; gap: 8px;
        }
        .sys-card-foot {
            padding: 0 18px 16px;
        }

        /* ── Icon badge ── */
        .sys-ico {
            width: 40px; height: 40px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
            background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff);
            border: 1px solid color-mix(in srgb, var(--c, var(--brand)) 20%, transparent);
        }
        .sys-ico-lg {
            width: 48px; height: 48px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
            background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff);
            border: 1px solid color-mix(in srgb, var(--c, var(--brand)) 20%, transparent);
        }

        /* ── Action button (light → solid on hover) ── */
        .sys-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            font-size: 12px; font-weight: 700;
            background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff);
            color: var(--c, var(--brand));
            border: 1.5px solid color-mix(in srgb, var(--c, var(--brand)) 25%, transparent);
            cursor: pointer; transition: all .18s ease;
            text-decoration: none; white-space: nowrap;
        }
        .sys-btn:hover {
            background: var(--c, var(--brand));
            color: #fff;
            border-color: var(--c, var(--brand));
            box-shadow: 0 3px 10px color-mix(in srgb, var(--c, var(--brand)) 30%, transparent);
            transform: translateY(-1px);
        }
        .sys-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
        .sys-btn-sm { padding: 5px 12px; font-size: 11.5px; border-radius: 8px; }
        .sys-btn-full { width: 100%; justify-content: center; padding: 9px; font-size: 13px; }

        /* Success variant */
        .sys-btn-success {
            background: #f0fdf4; color: #15803d;
            border: 1.5px solid #bbf7d0;
        }
        .sys-btn-success:hover { background: #15803d; color: #fff; border-color: #15803d; box-shadow: 0 3px 10px rgba(21,128,61,.25); }

        /* ── Status badge ── */
        .sys-badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 10px; font-weight: 700;
            background: color-mix(in srgb, var(--c, var(--brand)) 10%, #fff);
            color: var(--c, var(--brand));
            border: 1px solid color-mix(in srgb, var(--c, var(--brand)) 20%, transparent);
        }
        .sys-badge-free    { --c: #16a34a; }
        .sys-badge-pro     { --c: #7c3aed; }
        .sys-badge-gray    { background: var(--surface-raised); color: var(--text-2); border-color: var(--border); }

        /* ── KPI / stat card — always square ── */
        .kpi-card {
            border-radius: 14px;
            background: var(--surface-base);
            border: 1px solid var(--border);
            border-left: 3px solid var(--c, var(--brand));
            box-shadow: var(--shadow);
            padding: 18px;
            aspect-ratio: 1;
            display: flex; flex-direction: column; gap: 6px;
            justify-content: center; align-items: center; text-align: center;
        }
        .kpi-val   { font-size: 26px; font-weight: 800; color: var(--c, var(--brand)); letter-spacing: -.02em; line-height: 1; }
        .kpi-label { font-size: 11px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .05em; }

        /* ── Square card variant ── */
        .sys-card-sq {
            aspect-ratio: 1;
            overflow: hidden;
        }

        /* ── Grid layouts ── */
        .sys-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .sys-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .sys-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        @media (max-width: 1100px) { .sys-grid-4 { grid-template-columns: repeat(3, 1fr); } .sys-grid-3 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px)  { .sys-grid-4 { grid-template-columns: repeat(2, 1fr); } .sys-grid-3 { grid-template-columns: 1fr; } .sys-grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 480px)  { .sys-grid-4 { grid-template-columns: 1fr; } }

        /* ── Section header ── */
        .sys-section-hd {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }
        .sys-section-title { font-size: 13px; font-weight: 700; color: var(--text-1); }
        .sys-section-hint  { font-size: 11.5px; color: var(--text-3); }

        /* ── Category accent dot ── */
        .sys-cat-dot {
            width: 3px; height: 14px; border-radius: 99px;
            background: var(--c, var(--brand));
            display: inline-block; flex-shrink: 0;
        }

        /* ── Toolbar / search ── */
        .sys-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 4px 0 2px; }
        .sys-search {
            display: flex; align-items: center; gap: 8px;
            background: var(--surface-base); border: 1.5px solid var(--border);
            border-radius: 11px; padding: 0 12px;
            flex: 1; min-width: 200px; max-width: 360px;
            transition: border-color .13s, box-shadow .13s;
        }
        .sys-search:focus-within { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-ring); }
        .sys-search svg  { color: var(--text-3); flex-shrink: 0; width: 15px; height: 15px; }
        .sys-search input { border: none; outline: none; background: transparent; font-size: 13px; color: var(--text-1); padding: 9px 0; width: 100%; font-family: inherit; }

        /* ── Tab pills ── */
        .sys-tabs { display: flex; gap: 2px; background: var(--surface-raised); border: 1px solid var(--border); border-radius: 12px; padding: 4px; width: fit-content; }
        .sys-tab  { padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all .15s; display: flex; align-items: center; gap: 6px; white-space: nowrap; background: transparent; color: var(--text-2); }
        .sys-tab:hover:not(.sys-tab-on) { background: var(--surface-base); color: var(--text-1); }
        .sys-tab-on { background: var(--surface-base) !important; color: var(--text-1) !important; box-shadow: 0 1px 4px rgba(0,0,0,.08); font-weight: 600; }

        /* ── Cat pills (filter) ── */
        .sys-pill { padding: 5px 14px; border-radius: 99px; font-size: 12px; font-weight: 500; text-decoration: none; transition: all .13s; border: 1px solid var(--border); color: var(--text-2); background: var(--surface-raised); }
        .sys-pill:hover    { background: color-mix(in srgb, var(--brand) 10%, #fff); color: var(--brand); border-color: var(--brand-ring); }
        .sys-pill-on       { background: var(--brand) !important; color: #fff !important; border-color: var(--brand) !important; box-shadow: 0 2px 8px var(--brand-ring); }

        /* ── DataTable system ── */
        .dt-wrap { border-radius:16px; overflow:hidden; background:var(--surface-base); border:1px solid var(--border); box-shadow:var(--shadow); }
        .dt-toolbar { padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .dt-search { position:relative; flex:1; min-width:200px; }
        .dt-search-ico { position:absolute; left:11px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:var(--text-2); pointer-events:none; }
        .dt-search-input { width:100%; padding:8px 34px 8px 34px; border-radius:10px; border:1.5px solid var(--border); background:var(--surface-raised); font-size:13px; color:var(--text-1); outline:none; font-family:inherit; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; }
        .dt-search-input:focus { border-color:var(--brand); box-shadow:0 0 0 3px var(--brand-ring); }
        .dt-clear { position:absolute; right:9px; top:50%; transform:translateY(-50%); width:18px; height:18px; border-radius:50%; background:var(--border); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-3); transition:all .13s; line-height:1; }
        .dt-clear:hover { background:var(--text-3); color:#fff; }
        .dt-per-page { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-2); flex-shrink:0; }
        .dt-per-page select { padding:6px 10px; border-radius:8px; border:1.5px solid var(--border); background:var(--surface-raised); font-size:12px; color:var(--text-1); outline:none; cursor:pointer; transition:border-color .15s; }
        .dt-per-page select:focus { border-color:var(--brand); }
        .dt-count { font-size:12px; color:var(--text-3); flex-shrink:0; margin-left:auto; white-space:nowrap; }
        .dt-table { width:100%; border-collapse:collapse; }
        .dt-th { padding:10px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-3); background:var(--surface-raised); white-space:nowrap; }
        .dt-th:last-child { text-align:right; }
        .dt-td { padding:12px 16px; font-size:13px; color:var(--text-1); border-top:1px solid var(--border); vertical-align:middle; }
        .dt-tr { transition:background .1s; }
        .dt-tr:hover .dt-td { background:var(--hover-bg, var(--surface-raised)); }
        .dt-foot { padding:12px 18px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
        .dt-foot-info { font-size:12px; color:var(--text-3); }
        .dt-pages { display:flex; align-items:center; gap:3px; }
        .dt-page-btn { min-width:30px; height:30px; padding:0 6px; border-radius:8px; border:1.5px solid var(--border); background:none; cursor:pointer; font-size:12px; font-weight:600; color:var(--text-2); display:flex; align-items:center; justify-content:center; transition:all .13s; }
        .dt-page-btn:hover:not(:disabled):not(.dt-page-on) { background:var(--surface-raised); }
        .dt-page-btn:disabled { opacity:.35; cursor:default; }
        .dt-page-on { background:var(--brand) !important; color:#fff !important; border-color:var(--brand) !important; }
        .dt-page-dot { padding:0 4px; font-size:13px; color:var(--text-3); }
        .dt-empty { padding:52px 20px; text-align:center; color:var(--text-3); font-size:13px; }
        .dt-mark { background:color-mix(in srgb,var(--brand) 18%,#fff); color:var(--brand); border-radius:2px; padding:0 1px; }

        /* ═══════════════════════════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════════════════════════ */
        .app-sidebar {
            width: var(--sidebar-w-collapsed);
            background: var(--surface-base);
            border-right: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            transition: width var(--dur-slow) var(--ease-out);
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            z-index: 30;
        }
        .sidebar-expanded { width: var(--sidebar-w-expanded) !important; }

        .sb-section-label {
            font-size: 9.5px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .1em;
            color: var(--text-3);
            padding: 14px 18px 5px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity var(--dur-base) ease;
        }
        .sidebar-expanded .sb-section-label { opacity: 1; }

        .sb-item {
            display: flex; align-items: center;
            padding: 0 10px;
            margin: 1px 6px;
            height: 36px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: background var(--dur-fast) ease;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }
        .sb-item:hover { background: var(--surface-overlay); }
        .sb-item.active {
            background: var(--brand-light);
        }
        .sb-item.active::before {
            content: '';
            position: absolute;
            left: -6px; top: 6px; bottom: 6px;
            width: 3px;
            background: var(--brand);
            border-radius: 0 3px 3px 0;
        }
        .sb-ico {
            width: 22px; height: 22px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border-radius: 6px;
        }
        .sb-ico svg { width: 15px; height: 15px; }
        .sb-label {
            font-size: 13px; font-weight: 500;
            color: var(--text-2);
            margin-left: 10px;
            opacity: 0;
            transition: opacity var(--dur-base) ease;
            flex: 1;
        }
        .sidebar-expanded .sb-label { opacity: 1; }
        .sb-item.active .sb-label { color: var(--brand); font-weight: 600; }
        .sb-badge {
            font-size: 9px; font-weight: 800;
            background: var(--brand); color: #fff;
            padding: 1px 5px; border-radius: 99px;
            margin-left: 4px;
            opacity: 0;
            transition: opacity var(--dur-base) ease;
            flex-shrink: 0;
        }
        .sidebar-expanded .sb-badge { opacity: 1; }

        /* Sidebar tooltip on collapsed */
        .sb-item .sb-tooltip {
            position: absolute; left: calc(var(--sidebar-w-collapsed) + 4px);
            background: #1e293b; color: #fff;
            font-size: 12px; font-weight: 500;
            padding: 5px 10px; border-radius: 8px;
            pointer-events: none; opacity: 0;
            transition: opacity .1s ease;
            white-space: nowrap; z-index: 999;
            box-shadow: var(--shadow-lg);
        }
        .sb-item .sb-tooltip::before {
            content: '';
            position: absolute; right: 100%; top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #1e293b;
        }
        .app-sidebar:not(.sidebar-expanded) .sb-item:hover .sb-tooltip { opacity: 1; }

        /* Sidebar divider */
        .sb-divider {
            height: 1px; background: var(--border);
            margin: 6px 14px;
        }

        /* ── Auto-hide sidebar mode ── */
        /* When .sidebar-autohide and NOT expanded: collapse to zero width */
        .sidebar-autohide:not(.sidebar-expanded):not(.sidebar-mobile-open) {
            width: 0 !important;
            border-right: none !important;
            box-shadow: none !important;
            overflow: hidden !important;
        }
        /* Peek arrow tab — shows at left edge when sidebar is auto-hidden */
        .sb-peek {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 48px;
            background: var(--surface-base);
            border: 1px solid var(--border);
            border-left: 3px solid var(--brand);
            border-radius: 0 10px 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 35;
            cursor: pointer;
            box-shadow: 3px 0 14px rgba(0,0,0,.1);
            transition: width .18s ease, background .13s;
        }
        .sb-peek:hover {
            width: 24px;
            background: var(--brand-light);
        }
        .sb-peek svg {
            width: 10px; height: 10px;
            color: var(--text-3);
            flex-shrink: 0;
            transition: color .13s;
        }
        .sb-peek:hover svg { color: var(--brand); }

        /* ═══════════════════════════════════════════════════════════
           TOPBAR
        ═══════════════════════════════════════════════════════════ */
        .app-topbar {
            height: 56px;
            background: var(--surface-base);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-xs);
            display: flex; align-items: center;
            padding: 0 20px;
            gap: 12px;
            flex-shrink: 0;
        }
        .topbar-breadcrumb {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; color: var(--text-3);
            min-width: 0; flex: 1;
        }
        .topbar-breadcrumb a { color: var(--text-3); text-decoration: none; transition: color .1s; }
        .topbar-breadcrumb a:hover { color: var(--text-1); }
        .topbar-breadcrumb .bc-current { color: var(--text-1); font-weight: 600; }
        .topbar-breadcrumb .bc-sep { color: var(--border-strong); }

        .topbar-btn {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: none; background: none;
            color: var(--text-3);
            transition: background var(--dur-fast), color var(--dur-fast);
            flex-shrink: 0; position: relative;
        }
        .topbar-btn:hover { background: var(--surface-overlay); color: var(--text-1); }
        .topbar-btn svg { width: 17px; height: 17px; }

        .notif-badge {
            position: absolute; top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: #ef4444; border-radius: 50%;
            border: 2px solid var(--surface-base);
        }

        .btn-new-project {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            background: color-mix(in srgb, var(--brand) 10%, #fff);
            color: var(--brand) !important;
            border: 1.5px solid color-mix(in srgb, var(--brand) 28%, transparent);
            box-shadow: none;
            transition: all var(--dur-base) ease;
            text-decoration: none; cursor: pointer;
            flex-shrink: 0;
        }
        .btn-new-project:hover {
            background: var(--brand);
            color: #fff !important;
            border-color: var(--brand);
            box-shadow: 0 4px 14px var(--brand-ring);
            transform: translateY(-1px);
        }
        .btn-new-project svg { width: 14px; height: 14px; }

        /* ═══════════════════════════════════════════════════════════
           COMMAND PALETTE
        ═══════════════════════════════════════════════════════════ */
        .cmd-overlay {
            position: fixed; inset: 0;
            background: rgba(15,23,42,.5);
            z-index: 9000;
            backdrop-filter: blur(3px);
        }
        .cmd-box {
            position: fixed;
            top: 15vh; left: 50%;
            transform: translateX(-50%);
            width: min(560px, calc(100vw - 32px));
            background: var(--surface-base);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            z-index: 9001;
            overflow: hidden;
        }
        .cmd-input-wrap {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }
        .cmd-input-wrap svg { color: var(--text-3); flex-shrink: 0; width: 17px; height: 17px; }
        .cmd-input {
            flex: 1; border: none; outline: none;
            font-size: 15px; font-family: inherit;
            color: var(--text-1); background: transparent;
        }
        .cmd-input::placeholder { color: var(--text-3); }
        .cmd-kbd {
            font-size: 11px; color: var(--text-3);
            background: var(--surface-raised);
            border: 1px solid var(--border);
            padding: 2px 6px; border-radius: 5px;
            font-family: monospace; white-space: nowrap;
        }
        .cmd-section-label {
            font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--text-3);
            padding: 10px 16px 4px;
        }
        .cmd-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px;
            cursor: pointer;
            transition: background var(--dur-fast);
            text-decoration: none; color: var(--text-1);
        }
        .cmd-item:hover, .cmd-item.cmd-active { background: var(--surface-raised); }
        .cmd-item-ico {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            background: var(--surface-overlay); flex-shrink: 0;
        }
        .cmd-item-ico svg { width: 13px; height: 13px; color: var(--text-2); }
        .cmd-item-label { font-size: 13.5px; font-weight: 500; flex: 1; }
        .cmd-item-hint  { font-size: 11px; color: var(--text-3); }
        .cmd-footer {
            padding: 8px 16px;
            border-top: 1px solid var(--border);
            background: var(--surface-raised);
            display: flex; align-items: center; gap: 12px;
        }
        .cmd-footer-tip { font-size: 11px; color: var(--text-3); display: flex; align-items: center; gap: 4px; }

        /* ═══════════════════════════════════════════════════════════
           NOTIFICATIONS PANEL
        ═══════════════════════════════════════════════════════════ */
        .notif-panel {
            position: fixed; inset-y: 0; right: 0;
            width: 340px; max-width: 100vw;
            background: var(--surface-base);
            border-left: 1px solid var(--border);
            box-shadow: var(--shadow-xl);
            z-index: 8000;
            display: flex; flex-direction: column;
        }
        .notif-hd {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .notif-row {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 18px;
            border-bottom: 1px solid var(--border);
            transition: background var(--dur-fast);
        }
        .notif-row:hover { background: var(--surface-raised); }
        .notif-dot {
            width: 30px; height: 30px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
        }
        .notif-dot svg { width: 13px; height: 13px; }

        /* ═══════════════════════════════════════════════════════════
           TOAST SYSTEM
        ═══════════════════════════════════════════════════════════ */
        .toast-stack {
            position: fixed; top: 16px; right: 16px;
            z-index: 9999;
            display: flex; flex-direction: column; gap: 8px;
            pointer-events: none;
        }
        .toast {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px; font-weight: 500;
            box-shadow: var(--shadow-lg);
            max-width: 360px; pointer-events: all;
            border: 1px solid;
            animation: toastIn .22s var(--ease-out) both;
        }
        .toast-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .toast-error   { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .toast-info    { background: #f0f9ff; border-color: #bae6fd; color: #0369a1; }
        .toast svg { width: 15px; height: 15px; flex-shrink: 0; }
        .toast-close { margin-left: auto; opacity: .6; cursor: pointer; background: none; border: none; color: inherit; padding: 0; }
        .toast-close:hover { opacity: 1; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(16px) scale(.96); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }

        /* ═══════════════════════════════════════════════════════════
           MOBILE RESPONSIVE
        ═══════════════════════════════════════════════════════════ */
        .mobile-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 35;
        }
        .mobile-backdrop.active { display: block; }

        @media (max-width: 1023px) {
            .app-sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                height: 100dvh;
                width: 260px !important;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform .25s var(--ease-out), width .2s;
            }
            .app-sidebar.sidebar-expanded { transform: translateX(0) !important; width: 260px !important; }
            .app-sidebar.sidebar-mobile-open { transform: translateX(0); }
            .sidebar-expanded .sb-label,
            .sidebar-mobile-open .sb-label { opacity: 1 !important; }
            .sidebar-expanded .sb-section-label,
            .sidebar-mobile-open .sb-section-label { opacity: 1 !important; }
            .sidebar-expanded .sb-badge,
            .sidebar-mobile-open .sb-badge { opacity: 1 !important; }
            .btn-hamburger { display: flex !important; }
            .btn-new-project-label { display: none; }
            .main-content { padding: 12px !important; }
            .app-topbar { padding: 0 12px !important; }
        }
        @media (min-width: 1024px) {
            .btn-hamburger { display: none !important; }
        }

        /* Mobile bottom nav */
        .mobile-bottom-nav {
            display: none;
            position: fixed; bottom: 0; left: 0; right: 0;
            height: 60px; z-index: 50;
            background: var(--surface-base);
            border-top: 1px solid var(--border);
            box-shadow: 0 -4px 12px rgba(0,0,0,.06);
        }
        .mobile-bottom-nav-inner {
            display: flex; align-items: center; justify-content: space-around;
            height: 100%; padding: 0 8px;
        }
        .mbn-item {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            text-decoration: none; padding: 6px 12px; border-radius: 10px;
            transition: background .1s;
        }
        .mbn-item svg { width: 20px; height: 20px; }
        .mbn-item span { font-size: 10px; font-weight: 600; }
        .mbn-item:hover, .mbn-item.active { background: var(--brand-light); }
        .mbn-item.active svg, .mbn-item.active span { color: var(--brand); }
        .mbn-item:not(.active) svg, .mbn-item:not(.active) span { color: var(--text-3); }

        @media (max-width: 640px) {
            .mobile-bottom-nav { display: block; }
            .main-content { padding-bottom: 72px !important; }
            .notif-panel { width: 100%; }
        }

        /* Smooth main transitions */
        .app-main-area { transition: none; }
    </style>

    @stack('head')
</head>
<body>

{{-- ═══════════════ TOAST STACK ═══════════════ --}}
<div class="toast-stack" x-data="toastSystem()" @toast.window="addToast($event.detail)">
    <template x-for="t in toasts" :key="t.id">
        <div class="toast" :class="'toast-' + t.type" x-show="t.show"
             x-transition:leave="transition duration-200 ease-in"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4">
            <svg x-show="t.type === 'success'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg x-show="t.type === 'error'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg x-show="t.type === 'info'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="t.message"></span>
            <button class="toast-close" @click="dismiss(t.id)" aria-label="Close">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:13px;height:13px">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

{{-- ═══════════════ COMMAND PALETTE ═══════════════ --}}
<template x-teleport="body">
<div x-show="cmdOpen" x-cloak @keydown.escape.window="cmdOpen=false" x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="cmd-overlay" @click="cmdOpen=false"></div>
    <div class="cmd-box" @click.stop x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="cmd-input-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="cmd-input" x-model="cmdQuery" x-ref="cmdInput"
                   placeholder="Search pages, actions…"
                   @keydown.arrow-down.prevent="cmdMove(1)"
                   @keydown.arrow-up.prevent="cmdMove(-1)"
                   @keydown.enter.prevent="cmdGo()"
                   autocomplete="off">
            <span class="cmd-kbd">ESC</span>
        </div>

        <div style="max-height:360px; overflow-y:auto;" x-ref="cmdList">
            <template x-if="filteredCmdItems().length === 0">
                <div style="padding:28px;text-align:center;color:var(--text-3);font-size:13px;">No results for "<span x-text="cmdQuery"></span>"</div>
            </template>

            <template x-for="(group, gi) in groupedCmdItems()" :key="gi">
                <div>
                    <div class="cmd-section-label" x-text="group.label"></div>
                    <template x-for="(item, ii) in group.items" :key="item.href">
                        <a :href="item.href" class="cmd-item" :class="{'cmd-active': cmdIdx === item._idx}"
                           @mouseover="cmdIdx = item._idx" @click="cmdOpen=false">
                            <div class="cmd-item-ico">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon"/>
                                </svg>
                            </div>
                            <span class="cmd-item-label" x-text="item.label"></span>
                            <span class="cmd-item-hint" x-text="item.hint || ''"></span>
                        </a>
                    </template>
                </div>
            </template>
        </div>

        <div class="cmd-footer">
            <div class="cmd-footer-tip"><span class="cmd-kbd">↑↓</span> navigate</div>
            <div class="cmd-footer-tip"><span class="cmd-kbd">↵</span> open</div>
            <div class="cmd-footer-tip"><span class="cmd-kbd">ESC</span> close</div>
        </div>
    </div>
</div>
</template>

{{-- ═══════════════ NOTIFICATIONS PANEL ═══════════════ --}}
<template x-teleport="body">
<div x-show="notifOpen" x-cloak>
    <div style="position:fixed;inset:0;z-index:7999;" @click="notifOpen=false"
         x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         style="background:rgba(15,23,42,.2)"></div>
    <div class="notif-panel"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-8">
        <div class="notif-hd">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text-1);">Notifications</div>
                <div style="font-size:11px;color:var(--text-3);margin-top:1px;">Recent activity &amp; alerts</div>
            </div>
            <button class="topbar-btn" @click="notifOpen=false" aria-label="Close notifications">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div style="flex:1;overflow-y:auto;">
            @php
                $notifItems = collect();
                if(auth()->check()) {
                    $notifItems = \App\Models\AIConversation::where('user_id', auth()->id())
                        ->with('project:id,name')
                        ->latest()->limit(5)->get()
                        ->map(fn($c) => ['type'=>'ai','title'=>'AI build: '.($c->project->name ?? 'Project'),'sub'=>$c->message_count.' messages','time'=>$c->updated_at]);
                    $deploys = \App\Models\Deployment::whereHas('project', fn($q)=>$q->where('user_id',auth()->id()))
                        ->with('project:id,name')->latest()->limit(3)->get()
                        ->map(fn($d) => ['type'=>$d->status==='success'?'success':'warning','title'=>($d->project->name??'Project').' deployment','sub'=>ucfirst($d->status),'time'=>$d->created_at]);
                    $notifItems = $notifItems->concat($deploys)->sortByDesc('time')->take(8)->values();
                }
            @endphp
            @if($notifItems->isEmpty())
            <div style="padding:40px 20px;text-align:center;">
                <div style="width:44px;height:44px;border-radius:12px;background:var(--surface-raised);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <svg style="width:20px;height:20px;stroke:var(--text-3)" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div style="font-size:13px;font-weight:600;color:var(--text-1);margin-bottom:4px;">All caught up!</div>
                <div style="font-size:12px;color:var(--text-3);">No new notifications</div>
            </div>
            @else
            @foreach($notifItems as $n)
            <div class="notif-row">
                @if($n['type'] === 'ai')
                <div class="notif-dot" style="background:#fdf4ff;border:1px solid #ede9fe;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#8b5cf6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                @elseif($n['type'] === 'success')
                <div class="notif-dot" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                @else
                <div class="notif-dot" style="background:#fffbeb;border:1px solid #fde68a;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                @endif
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12.5px;font-weight:600;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $n['title'] }}</div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:2px;">{{ $n['sub'] }} · {{ $n['time']->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</div>
</template>

{{-- ═══════════════ APP SHELL ═══════════════ --}}
<div class="flex h-screen overflow-hidden">

    <!-- Mobile backdrop -->
    <div class="mobile-backdrop" :class="{ active: mobileSidebarOpen }" @click="mobileSidebarOpen=false"></div>

    <!-- ═══════ SIDEBAR ═══════ -->
    @if($showSidebar)

    {{-- Peek arrow: only renders in auto-hide mode, visible when sidebar is fully collapsed --}}
    @if($sidebarAutoHide)
    <div class="sb-peek"
         x-show="!(sidebarOpen || sidebarHovered || mobileSidebarOpen)"
         x-cloak
         @mouseenter="sidebarHovered = true"
         title="Open sidebar">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
    @endif

    <aside class="app-sidebar {{ $sidebarAutoHide ? 'sidebar-autohide' : '' }}"
           :class="{ 'sidebar-expanded': sidebarOpen || sidebarHovered, 'sidebar-mobile-open': mobileSidebarOpen }"
           @mouseenter="sidebarHovered=true"
           @mouseleave="sidebarHovered=false">

        {{-- Logo --}}
        <div style="height:56px;display:flex;align-items:center;padding:0 12px;border-bottom:1px solid var(--border);flex-shrink:0;gap:10px;">
            @if($logoPath)
            <img src="{{ Storage::url($logoPath) }}" alt="Logo"
                 style="width:32px;height:32px;border-radius:9px;object-cover;flex-shrink:0;">
            @else
            <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--brand-dark));display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px var(--brand-ring);">
                <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            @endif
            <div style="flex:1;min-width:0;opacity:0;transition:opacity var(--dur-base) ease;" :style="(sidebarOpen||sidebarHovered) ? 'opacity:1' : 'opacity:0'">
                <div style="font-size:14px;font-weight:800;color:var(--text-1);letter-spacing:-.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ config('app.name') }}</div>
            </div>
            <button @click="sidebarOpen=!sidebarOpen"
                    style="width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:none;background:none;cursor:pointer;color:var(--text-3);flex-shrink:0;opacity:0;transition:opacity var(--dur-base),background var(--dur-fast);"
                    :style="(sidebarOpen||sidebarHovered) ? 'opacity:1' : 'opacity:0'"
                    onmouseover="this.style.background='var(--surface-overlay)'" onmouseout="this.style.background='none'"
                    aria-label="Toggle sidebar">
                <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path x-show="sidebarOpen"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav style="flex:1;overflow-y:auto;overflow-x:hidden;padding:6px 0;" aria-label="Main navigation">
        @php
            $navGroups = [
                'WORKSPACE' => [
                    ['route'=>'dashboard',       'label'=>'Dashboard',    'color'=>'#6366f1', 'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ],
                'BUILD' => [
                    ['route'=>'projects.index',  'label'=>'Projects',     'color'=>'#8b5cf6', 'icon'=>'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'badge'=>'projects'],
                    ['route'=>'marketplace.index','label'=>'Marketplace', 'color'=>'#10b981', 'icon'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ],
                'AI TOOLS' => [
                    ['route'=>'wisdom.index',    'label'=>'AI Knowledge', 'color'=>'#f59e0b', 'icon'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                ],
                'CONFIGURE' => [
                    ['route'=>'menus.index',     'label'=>'Menus',        'color'=>'#ec4899', 'icon'=>'M4 6h16M4 12h16M4 18h7'],
                    ['route'=>'settings.index',  'label'=>'Settings',     'color'=>'#f97316', 'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ],
            ];
            $projectCount = auth()->check() ? \App\Models\Project::where('user_id', auth()->id())->count() : 0;
            $dynamicSidebarMenus = auth()->check()
                ? \App\Models\Menu::where('user_id', auth()->id())
                      ->whereIn('category', ['sidebar', 'admin_sidebar'])
                      ->where('is_active', true)
                      ->with(['items' => fn($q) => $q->where('is_active', true)->orderBy('order')->with([
                          'children' => fn($q2) => $q2->where('is_active', true)->orderBy('order'),
                      ])])
                      ->get()
                : collect();
        @endphp

        @foreach($navGroups as $groupLabel => $items)
            <div class="sb-section-label">{{ $groupLabel }}</div>
            @foreach($items as $item)
            @php $active = request()->routeIs(explode('.', $item['route'])[0].'*'); @endphp
            <a href="{{ route($item['route']) }}"
               class="sb-item{{ $active ? ' active' : '' }}"
               aria-current="{{ $active ? 'page' : 'false' }}">
                <div class="sb-ico" style="{{ $active ? 'background:color-mix(in srgb,'.$item['color'].' 15%,transparent);' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="{{ $active ? $item['color'] : 'var(--text-3)' }}" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
                    </svg>
                </div>
                <span class="sb-label" style="{{ $active ? 'color:'.$item['color'].';font-weight:600;' : '' }}">{{ $item['label'] }}</span>
                @if(isset($item['badge']) && $item['badge'] === 'projects' && $projectCount > 0)
                <span class="sb-badge" style="{{ $active ? '' : 'background:var(--surface-overlay);color:var(--text-2);' }}">{{ $projectCount }}</span>
                @endif
                <span class="sb-tooltip" aria-hidden="true">{{ $item['label'] }}</span>
            </a>
            @endforeach
        @endforeach

        {{-- Admin section --}}
        @if(Auth::user()->isAdmin())
        <div class="sb-divider"></div>
        <div class="sb-section-label">ADMIN</div>
        @php $adminActive = request()->routeIs('marketplace.admin*'); @endphp
        <a href="{{ route('marketplace.admin.panel') }}" class="sb-item{{ $adminActive ? ' active' : '' }}">
            <div class="sb-ico" style="{{ $adminActive ? 'background:#fef2f2;' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="{{ $adminActive ? '#ef4444' : 'var(--text-3)' }}" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <span class="sb-label" style="{{ $adminActive ? 'color:#ef4444;font-weight:600;' : '' }}">Marketplace Admin</span>
            <span class="sb-tooltip" aria-hidden="true">Marketplace Admin</span>
        </a>
        @endif

        {{-- Dynamic custom menus --}}
        @foreach($dynamicSidebarMenus as $dynMenu)
        <div class="sb-divider"></div>
        <div class="sb-section-label">{{ $dynMenu->name }}</div>
        @foreach($dynMenu->items as $mItem)
        @php
            $rawUrl  = $mItem->url ?? '';
            $urlPath = parse_url($rawUrl, PHP_URL_PATH) ?? '';
            if ($rawUrl && preg_match('#^/builder/[^/]+$#', $urlPath)) {
                $rawUrl = rtrim($rawUrl, '/') . '/preview';
            }
            $mPath   = ltrim($urlPath !== '' ? $urlPath : '', '/');
            $mActive = $rawUrl && ($mPath ? request()->is($mPath, $mPath.'/*') : false);
        @endphp
        <a href="{{ $rawUrl ?: '#' }}" target="{{ $mItem->target ?: '_blank' }}"
           class="sb-item{{ $mActive ? ' active' : '' }}">
            <div class="sb-ico" style="{{ $mActive ? 'background:var(--brand-light);' : '' }}">
                @if($mItem->icon)
                <svg fill="none" viewBox="0 0 24 24" stroke="{{ $mActive ? 'var(--brand)' : 'var(--text-3)' }}" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $mItem->icon }}"/>
                </svg>
                @else
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $mActive ? 'var(--brand)' : 'var(--border-strong)' }};display:block;"></span>
                @endif
            </div>
            <span class="sb-label">{{ $mItem->label }}</span>
            @foreach($mItem->children as $child)
            @php
                $cRaw  = $child->url ?? '';
                $cPath = parse_url($cRaw, PHP_URL_PATH) ?? '';
                if ($cRaw && preg_match('#^/builder/[^/]+$#', $cPath)) { $cRaw = rtrim($cRaw, '/') . '/preview'; }
                $cActive = $cRaw && (ltrim($cPath,'/') ? request()->is(ltrim($cPath,'/'), ltrim($cPath,'/').'/*') : false);
            @endphp
            @endforeach
        </a>
        @endforeach
        @endforeach

        </nav>

        {{-- User profile --}}
        <div style="padding:8px;border-top:1px solid var(--border);flex-shrink:0;" x-data="{ open: false }">
            <button @click="open=!open"
                    style="width:100%;display:flex;align-items:center;gap:10px;padding:8px;border-radius:10px;border:none;background:none;cursor:pointer;text-align:left;transition:background var(--dur-fast);"
                    onmouseover="this.style.background='var(--surface-raised)'" onmouseout="this.style.background='none'"
                    :aria-expanded="open">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                     style="width:30px;height:30px;border-radius:8px;flex-shrink:0;border:2px solid var(--border);">
                <div style="flex:1;min-width:0;opacity:0;transition:opacity var(--dur-base);" :style="(sidebarOpen||sidebarHovered) ? 'opacity:1' : 'opacity:0'">
                    <div style="font-size:12.5px;font-weight:600;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                    <div style="font-size:11px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
                </div>
                <svg style="width:12px;height:12px;color:var(--text-3);flex-shrink:0;opacity:0;transition:opacity var(--dur-base);" :style="(sidebarOpen||sidebarHovered) ? 'opacity:1' : 'opacity:0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition @click.away="open=false"
                 style="position:absolute;bottom:60px;left:8px;right:8px;background:var(--surface-base);border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow-lg);overflow:hidden;z-index:50;padding:4px 0;">
                <a href="{{ route('settings.index') }}"
                   style="display:flex;align-items:center;gap:10px;padding:10px 14px;font-size:13px;color:var(--text-2);text-decoration:none;transition:background var(--dur-fast);"
                   onmouseover="this.style.background='var(--surface-raised)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile &amp; Settings
                </a>
                <div style="height:1px;background:var(--border);margin:3px 0;"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 14px;font-size:13px;color:#ef4444;border:none;background:none;cursor:pointer;text-align:left;transition:background var(--dur-fast);"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                        <svg style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endif

    <!-- ═══════ MAIN AREA ═══════ -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden app-main-area">

        <!-- TOPBAR -->
        @if($showTopbar)
        @php
            $userTopbarMenus = auth()->check()
                ? \App\Models\Menu::where('user_id', auth()->id())
                      ->where('category', 'user_topbar')
                      ->where('is_active', true)
                      ->with(['items' => fn($q) => $q->where('is_active', true)->orderBy('order')
                          ->with(['children' => fn($q2) => $q2->where('is_active', true)->orderBy('order')])])
                      ->get()
                : collect();
        @endphp
        <header class="app-topbar">
            {{-- Mobile hamburger --}}
            <button class="topbar-btn btn-hamburger" style="display:none;"
                    @click="mobileSidebarOpen=!mobileSidebarOpen"
                    aria-label="Open navigation">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumbs / Page title --}}
            <div class="topbar-breadcrumb">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <span class="bc-current">@yield('header', 'Dashboard')</span>
                @endif
            </div>

            {{-- User topbar menus --}}
            @if($userTopbarMenus->isNotEmpty())
            <nav style="display:flex;align-items:center;gap:2px;overflow-x:auto;scrollbar-width:none;flex-shrink:0;" aria-label="Quick links">
                @foreach($userTopbarMenus as $topMenu)
                @foreach($topMenu->items as $topItem)
                @php
                    $topPath   = ltrim(parse_url($topItem->url ?? '', PHP_URL_PATH) ?? '', '/');
                    $topActive = $topItem->url && ($topPath ? request()->is($topPath, $topPath.'/*') : false);
                @endphp
                @if($topItem->children->isNotEmpty())
                <div x-data="{ open: false }" class="relative flex-shrink-0">
                    <button @click="open=!open" @click.away="open=false"
                            style="display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12.5px;font-weight:500;border:none;background:{{ $topActive ? 'var(--brand-light)' : 'none' }};color:{{ $topActive ? 'var(--brand)' : 'var(--text-2)' }};cursor:pointer;transition:background var(--dur-fast);"
                            onmouseover="this.style.background='var(--surface-overlay)'" onmouseout="this.style.background='{{ $topActive ? 'var(--brand-light)' : 'none' }}'">
                        {{ $topItem->label }}
                        <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-transition style="position:absolute;top:calc(100% + 4px);left:0;background:var(--surface-base);border:1px solid var(--border);border-radius:11px;box-shadow:var(--shadow-md);z-index:50;min-width:160px;padding:4px 0;overflow:hidden;">
                        @foreach($topItem->children as $child)
                        <a href="{{ $child->url ?: '#' }}" target="{{ $child->target ?: '_self' }}"
                           style="display:flex;align-items:center;padding:8px 14px;font-size:12.5px;color:var(--text-2);text-decoration:none;transition:background var(--dur-fast);"
                           onmouseover="this.style.background='var(--surface-raised)';this.style.color='var(--text-1)'"
                           onmouseout="this.style.background='';this.style.color='var(--text-2)'">{{ $child->label }}</a>
                        @endforeach
                    </div>
                </div>
                @else
                <a href="{{ $topItem->url ?: '#' }}" target="{{ $topItem->target ?: '_self' }}"
                   style="display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12.5px;font-weight:{{ $topActive ? '600' : '500' }};color:{{ $topActive ? 'var(--brand)' : 'var(--text-2)' }};background:{{ $topActive ? 'var(--brand-light)' : 'none' }};text-decoration:none;white-space:nowrap;transition:background var(--dur-fast);flex-shrink:0;"
                   onmouseover="this.style.background='var(--surface-overlay)'" onmouseout="this.style.background='{{ $topActive ? 'var(--brand-light)' : 'none' }}'">
                    {{ $topItem->label }}
                </a>
                @endif
                @endforeach
                @endforeach
            </nav>
            @endif

            {{-- Right actions --}}
            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                @yield('header-actions')

                {{-- ⌘K Search --}}
                <button class="topbar-btn" @click="cmdOpen=true; $nextTick(()=>$refs.cmdInput?.focus())"
                        aria-label="Open command palette (Ctrl+K)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                {{-- Notifications --}}
                <button class="topbar-btn" @click="notifOpen=!notifOpen"
                        aria-label="Notifications" :aria-expanded="notifOpen">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @php $notifCount = auth()->check() ? \App\Models\AIConversation::where('user_id',auth()->id())->where('created_at','>=',now()->subDay())->count() : 0; @endphp
                    @if($notifCount > 0)
                    <span class="notif-badge" aria-label="{{ $notifCount }} notifications"></span>
                    @endif
                </button>

                {{-- New Project --}}
                <a href="{{ route('projects.create') }}" class="btn-new-project">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="btn-new-project-label">New Project</span>
                </a>
            </div>
        </header>
        @endif

        {{-- Page Content --}}
        <main class="main-content flex-1 overflow-y-auto p-6 @yield('main-class', '')">
            @yield('content')
        </main>
    </div>
</div>

{{-- ═══ MOBILE BOTTOM NAV ═══ --}}
<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
    <div class="mobile-bottom-nav-inner">
        @php
            $mbnItems = [
                ['route'=>'dashboard',       'label'=>'Home',        'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route'=>'projects.index',  'label'=>'Projects',    'icon'=>'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ['route'=>'marketplace.index','label'=>'Store',      'icon'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ['route'=>'settings.index',  'label'=>'Settings',    'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ];
        @endphp
        @foreach($mbnItems as $mbn)
        @php $mbnActive = request()->routeIs(explode('.', $mbn['route'])[0].'*'); @endphp
        <a href="{{ route($mbn['route']) }}" class="mbn-item{{ $mbnActive ? ' active' : '' }}" aria-current="{{ $mbnActive ? 'page' : 'false' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $mbn['icon'] }}"/>
            </svg>
            <span>{{ $mbn['label'] }}</span>
        </a>
        @endforeach
    </div>
</nav>

@stack('modals')
@stack('scripts')

{{-- Session flash → toast --}}
@if(session('success') || session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: @json(session('success')) } }));
    @endif
    @if(session('error'))
    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: @json(session('error')) } }));
    @endif
});
</script>
@endif

<script>
function appLayout() {
    return {
        sidebarOpen:       localStorage.getItem('sb_open') === 'true',
        sidebarHovered:    false,
        mobileSidebarOpen: false,
        cmdOpen:           false,
        cmdQuery:          '',
        cmdIdx:            0,
        notifOpen:         false,

        init() {
            localStorage.removeItem('theme');
            document.documentElement.classList.remove('dark');
            // ⌘K / Ctrl+K
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.cmdOpen = !this.cmdOpen;
                    if (this.cmdOpen) this.$nextTick(() => this.$refs.cmdInput?.focus());
                }
            });
            this.$watch('sidebarOpen', v => localStorage.setItem('sb_open', v));
        },

        cmdItems() {
            return [
                // Navigation
                { group: 'Navigate', label: 'Dashboard',      hint: 'Overview',           href: '{{ route('dashboard') }}',        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
                { group: 'Navigate', label: 'Projects',        hint: 'All projects',       href: '{{ route('projects.index') }}',   icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' },
                { group: 'Navigate', label: 'Marketplace',     hint: 'Module store',       href: '{{ route('marketplace.index') }}',icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
                { group: 'Navigate', label: 'AI Knowledge',    hint: 'Intelligence ledger',href: '{{ route('wisdom.index') }}',     icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z' },
                { group: 'Navigate', label: 'Menus',           hint: 'Navigation manager', href: '{{ route('menus.index') }}',      icon: 'M4 6h16M4 12h16M4 18h7' },
                { group: 'Navigate', label: 'Settings',        hint: 'Account & workspace',href: '{{ route('settings.index') }}',   icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
                { group: 'Navigate', label: 'Updates',         hint: 'System updates',     href: '{{ route('settings.updates') }}', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' },
                // Actions
                { group: 'Actions',  label: 'New Project',     hint: 'Create with AI',     href: '{{ route('projects.create') }}',  icon: 'M12 4v16m8-8H4' },
                { group: 'Actions',  label: 'Upload Package',  hint: 'Install a .zip',     href: '{{ route('marketplace.upload-install') }}', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
                { group: 'Actions',  label: 'Installed Modules',hint: 'Manage modules',    href: '{{ route('marketplace.installed') }}', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
            ];
        },

        filteredCmdItems() {
            const q = this.cmdQuery.toLowerCase().trim();
            const items = this.cmdItems();
            const filtered = q ? items.filter(i => i.label.toLowerCase().includes(q) || (i.hint||'').toLowerCase().includes(q)) : items;
            return filtered.map((item, idx) => ({ ...item, _idx: idx }));
        },

        groupedCmdItems() {
            const items = this.filteredCmdItems();
            const groups = {};
            items.forEach(item => {
                if (!groups[item.group]) groups[item.group] = { label: item.group, items: [] };
                groups[item.group].items.push(item);
            });
            return Object.values(groups);
        },

        cmdMove(dir) {
            const items = this.filteredCmdItems();
            if (!items.length) return;
            this.cmdIdx = ((this.cmdIdx + dir) % items.length + items.length) % items.length;
        },

        cmdGo() {
            const items = this.filteredCmdItems();
            const item = items[this.cmdIdx];
            if (item) { window.location.href = item.href; this.cmdOpen = false; }
        },
    };
}

function toastSystem() {
    return {
        toasts: [],
        addToast({ type = 'info', message, duration = 4500 }) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message, show: true });
            setTimeout(() => this.dismiss(id), duration);
        },
        dismiss(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) { t.show = false; setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300); }
        },
    };
}

function dtMixin(opts) {
    opts = opts || {};
    return {
        search: '',
        page: 1,
        perPage: opts.perPage || 10,
        perPageOpts: [10, 20, 50, 100],

        dtSearch(list, keys) {
            var q = this.search.trim().toLowerCase();
            if (!q) return list;
            return list.filter(r =>
                keys.some(k => {
                    var v = k.split('.').reduce((o, p) => (o && o[p] != null ? o[p] : ''), r);
                    return String(v).toLowerCase().includes(q);
                })
            );
        },

        dtPageRange(total, cur) {
            var p = [];
            if (total <= 7) { for (var i = 1; i <= total; i++) p.push(i); return p; }
            p.push(1);
            if (cur > 3) p.push('…');
            for (var i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) p.push(i);
            if (cur < total - 2) p.push('…');
            p.push(total);
            return p;
        },

        dtInfo(total, page, pp) {
            if (!total) return 'No results';
            var f = (page - 1) * pp + 1, l = Math.min(page * pp, total);
            return 'Showing ' + f + '–' + l + ' of ' + total;
        },

        dtHighlight(text, q) {
            if (text == null) return '—';
            var s = String(text)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            if (!q || !q.trim()) return s;
            var esc = q.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return s.replace(new RegExp('(' + esc + ')', 'gi'), '<mark class="dt-mark">$1</mark>');
        },
    };
}
</script>
</body>
</html>
