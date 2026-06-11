<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RyaanCMS — The World's First AI Business Operating System Builder. 21 Knowledge Bases, 10 AI Agents, Blueprint Assembly. Build smarter. Ship faster.">
    <title>RyaanCMS — AI Business Operating System Builder</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background: #ffffff;
            color: #111827;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Page background ─────────────────────────── */
        .page-bg {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 80% 60% at 5% 0%,   rgba(109,40,217,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 55% 45% at 95% 15%,  rgba(79,70,229,0.05)  0%, transparent 60%),
                radial-gradient(ellipse 40% 35% at 50% 100%, rgba(8,145,178,0.04)  0%, transparent 60%);
        }
        .page-grid {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(109,40,217,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(109,40,217,0.035) 1px, transparent 1px);
            background-size: 64px 64px;
        }

        /* ── Typography ──────────────────────────────── */
        .display-1 {
            font-size: clamp(46px, 6.5vw, 84px);
            font-weight: 800;
            line-height: 1.02;
            letter-spacing: -0.04em;
            color: #0f172a;
        }
        .display-2 {
            font-size: clamp(30px, 4vw, 50px);
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.03em;
            color: #0f172a;
        }
        .g-text {
            background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 50%, #0891b2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Section label pill ──────────────────────── */
        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #6d28d9;
            background: rgba(109,40,217,0.07);
            border: 1px solid rgba(109,40,217,0.18);
            border-radius: 100px;
            padding: 5px 14px;
            margin-bottom: 20px;
        }
        .section-label-teal {
            color: #0891b2;
            background: rgba(8,145,178,0.07);
            border-color: rgba(8,145,178,0.2);
        }

        /* ── Cards ───────────────────────────────────── */
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: border-color .2s, box-shadow .2s, transform .2s;
        }
        .card:hover {
            border-color: rgba(109,40,217,0.28);
            box-shadow: 0 8px 32px rgba(109,40,217,0.09), 0 2px 8px rgba(0,0,0,0.04);
            transform: translateY(-2px);
        }

        /* ── Buttons ─────────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            background: linear-gradient(135deg, #6d28d9, #4f46e5);
            color: #ffffff;
            font-weight: 700; font-size: 15px;
            border-radius: 12px; border: none;
            padding: 14px 28px;
            text-decoration: none; cursor: pointer;
            box-shadow: 0 4px 16px rgba(109,40,217,0.35);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(109,40,217,0.45); }

        .btn-secondary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            background: #ffffff;
            color: #374151;
            font-weight: 600; font-size: 15px;
            border-radius: 12px; border: 1px solid #d1d5db;
            padding: 14px 28px;
            text-decoration: none; cursor: pointer;
            transition: all .2s;
        }
        .btn-secondary:hover { border-color: rgba(109,40,217,0.4); color: #6d28d9; background: rgba(109,40,217,0.03); }

        .btn-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            background: transparent;
            color: #6d28d9;
            font-weight: 600; font-size: 14px;
            border-radius: 10px; border: 1px solid rgba(109,40,217,0.22);
            padding: 9px 18px;
            text-decoration: none; cursor: pointer;
            transition: all .2s;
        }
        .btn-outline:hover { background: rgba(109,40,217,0.06); border-color: rgba(109,40,217,0.4); }

        /* ── Stat pill ───────────────────────────────── */
        .stat-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px;
            background: #f8f7ff; border: 1px solid #ddd6fe;
            border-radius: 100px;
            font-size: 13px; font-weight: 600; color: #5b21b6;
        }

        /* ── Terminal ────────────────────────────────── */
        .terminal {
            background: #0f172a;
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,0.16), 0 0 0 1px rgba(109,40,217,0.22);
        }
        .terminal-header {
            background: #1e293b;
            padding: 13px 18px;
            display: flex; align-items: center; gap: 7px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .t-dot { width: 12px; height: 12px; border-radius: 50%; }
        .terminal-body {
            padding: 28px;
            font-family: 'SF Mono','Monaco','Fira Code',monospace;
            font-size: 13.5px;
            line-height: 1.75;
        }

        /* ── Comparison cards ────────────────────────── */
        .compare-bad  { background: #fef2f2; border: 1px solid #fecaca; border-radius: 18px; }
        .compare-good { background: linear-gradient(135deg,#f0fdf4,#f5f3ff); border: 1px solid #bbf7d0; border-radius: 18px; }

        /* ── Pipeline step ───────────────────────────── */
        .p-step {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 13px; border-radius: 8px;
            font-size: 12.5px; font-weight: 700;
        }

        /* ── Feature icon ────────────────────────────── */
        .feat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 16px;
        }

        /* ── Nav link ────────────────────────────────── */
        .nav-link {
            padding: 7px 14px;
            font-size: 14px; font-weight: 500; color: #4b5563;
            border-radius: 8px; text-decoration: none;
            transition: color .15s, background .15s;
        }
        .nav-link:hover { color: #6d28d9; background: rgba(109,40,217,0.06); }

        /* ── Sections ────────────────────────────────── */
        .section-alt   { background: #f9fafb; }
        .section-tint  { background: linear-gradient(180deg,#faf5ff,#f8f4ff); }

        /* ── Animations ──────────────────────────────── */
        @keyframes pulse-dot  { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.5;transform:scale(.85);} }
        .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }

        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(109,40,217,0.4); }
            70%  { box-shadow: 0 0 0 8px rgba(109,40,217,0); }
            100% { box-shadow: 0 0 0 0 rgba(109,40,217,0); }
        }
        .pulse-ring { animation: pulse-ring 2.5s infinite; }

        /* ── Scrollbar ───────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f5f0ff; }
        ::-webkit-scrollbar-thumb { background: #c4b5fd; border-radius: 10px; }

        /* ── Domain card ─────────────────────────────── */
        .domain-card { transition: all .2s; cursor: pointer; }
        .domain-card:hover { transform: translateY(-2px); border-color: rgba(109,40,217,0.25) !important; box-shadow: 0 8px 24px rgba(109,40,217,0.08); }

        /* ── Agent card ──────────────────────────────── */
        .agent-card {
            background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px;
            padding: 16px 12px; text-align: center; transition: all .2s;
        }
        .agent-card:hover { border-color: rgba(109,40,217,0.25); box-shadow: 0 4px 16px rgba(109,40,217,0.07); }

        /* ── Footer ──────────────────────────────────── */
        footer { background: #f9fafb; border-top: 1px solid #e5e7eb; }

        /* ─── Mobile nav ──────────────────────────────────── */
        .mobile-nav-menu {
            display: none;
            position: absolute;
            top: 64px;
            left: 0; right: 0;
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px 16px;
            z-index: 49;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        .mobile-nav-menu.open { display: block; }
        .mobile-nav-link {
            display: block;
            padding: 10px 14px;
            font-size: 15px;
            font-weight: 500;
            color: #374151;
            border-radius: 10px;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .mobile-nav-link:hover { background: rgba(109,40,217,0.06); color: #6d28d9; }
        .btn-mobile-menu { display: none; }

        /* Footer responsive grid */
        .footer-cols {
            display: grid;
            grid-template-columns: 280px repeat(5, 1fr);
            gap: 40px;
            margin-bottom: 40px;
            align-items: start;
        }

        @media (max-width: 1279px) {
            .footer-cols { grid-template-columns: 1fr 1fr 1fr; gap: 32px; }
        }
        @media (max-width: 767px) {
            .footer-cols { grid-template-columns: 1fr 1fr; gap: 24px; }
            .btn-mobile-menu { display: flex !important; }
            .terminal-body { padding: 16px; font-size: 11.5px; }
        }
        @media (max-width: 480px) {
            .footer-cols { grid-template-columns: 1fr; gap: 20px; }
        }
    </style>
</head>
@php
    $vJson       = json_decode(file_get_contents(base_path('versions.json')), true);
    $latestVer   = $vJson['latest'] ?? '1.0.0';
    $latestEntry = collect($vJson['versions'] ?? [])->firstWhere('version', $latestVer);
    $downloadUrl = $latestEntry['download_url'] ?? "https://github.com/RyaanCMS/RyaanCMS/releases/latest";
    $releasesUrl = "https://github.com/RyaanCMS/RyaanCMS/releases/latest";
@endphp
<body x-data="landing()">
<div class="page-bg"></div>
<div class="page-grid"></div>

<!-- ══════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════ -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="scrolled ? 'shadow-sm' : ''"
        :style="scrolled
            ? 'background:rgba(255,255,255,0.97);backdrop-filter:blur(20px);border-bottom:1px solid #e5e7eb;'
            : 'background:rgba(255,255,255,0.82);backdrop-filter:blur(16px);'">
    <nav class="max-w-screen-xl mx-auto px-6 h-16 flex items-center justify-between">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#6d28d9,#4f46e5);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="font-bold text-base" style="color:#0f172a;">RyaanCMS</span>
        </a>

        <!-- Nav Links -->
        <div class="hidden md:flex items-center gap-1">
            <a href="#intelligence" class="nav-link">Intelligence</a>
            <a href="#how-it-works" class="nav-link">How It Works</a>
            <a href="#domains"      class="nav-link">Domains</a>
            <a href="#features"     class="nav-link">Features</a>
            <a href="#pricing"      class="nav-link" style="color:#7c3aed;font-weight:700;">Pricing</a>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2.5">
            <!-- Mobile menu button -->
            <button class="btn-mobile-menu w-9 h-9 rounded-lg items-center justify-center md:hidden"
                    style="display:none; color:#374151; border:1px solid #e5e7eb;"
                    @click="mobileMenuOpen = !mobileMenuOpen">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <a href="https://github.com/ryaancms" target="_blank" rel="noopener" class="btn-outline hidden sm:inline-flex">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                </svg>
                <span class="hidden lg:inline">GitHub</span>
            </a>
            <a href="{{ $downloadUrl }}" class="btn-outline hidden sm:inline-flex" style="color:#059669;border-color:rgba(5,150,105,0.25);" onmouseover="this.style.background='rgba(5,150,105,0.06)'" onmouseout="this.style.background='transparent'">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                v{{ $latestVer }}
            </a>
            <a href="{{ route('login') }}" class="btn-secondary hidden sm:inline-flex" style="padding:9px 18px;font-size:14px;">Sign In</a>
            <a href="#pricing" class="btn-primary" style="padding:9px 20px;font-size:14px;">Pro — 1 Month Free →</a>
        </div>
    </nav>

    <!-- Mobile Nav Menu -->
    <div class="mobile-nav-menu" :class="{ open: mobileMenuOpen }">
        <a href="#intelligence" class="mobile-nav-link" @click="mobileMenuOpen = false">Intelligence</a>
        <a href="#how-it-works" class="mobile-nav-link" @click="mobileMenuOpen = false">How It Works</a>
        <a href="#domains"      class="mobile-nav-link" @click="mobileMenuOpen = false">Domains</a>
        <a href="#features"     class="mobile-nav-link" @click="mobileMenuOpen = false">Features</a>
        <a href="#pricing"      class="mobile-nav-link" @click="mobileMenuOpen = false" style="color:#7c3aed;font-weight:700;">Pricing</a>
        <div style="border-top:1px solid #e5e7eb; margin:10px 0;"></div>
        <a href="{{ route('login') }}"    class="mobile-nav-link">Sign In</a>
        <a href="{{ route('register') }}" class="mobile-nav-link" style="color:#6d28d9; font-weight:600;">Get Started Free →</a>
    </div>
</header>

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="relative flex flex-col items-center justify-center text-center px-6"
         style="min-height:100svh; padding-top:128px; padding-bottom:80px;">
    <div class="relative z-10 max-w-5xl mx-auto w-full">

        <!-- Label -->
        <div class="section-label">
            <span class="w-1.5 h-1.5 rounded-full pulse-ring flex-shrink-0"
                  style="background:#6d28d9; display:inline-block;"></span>
            World's First AI Business Operating System Builder — Free &amp; Opensource
        </div>

        <!-- Headline -->
        <h1 class="display-1 mb-6">
            Build Smarter.<br>
            <span class="g-text">Ship Faster.</span> <span style="color:#6d28d9; opacity:0.6;">Learn Forever.</span>
        </h1>

        <!-- Subheadline -->
        <p class="text-lg sm:text-xl leading-relaxed mb-10 max-w-2xl mx-auto" style="color:#4b5563;">
            100+ ready blueprints generate 95% of any enterprise application at zero AI cost.
            No API key needed to start — add one only for the 5% that truly needs intelligence.
        </p>

        <!-- Stat Pills -->
        <div class="flex flex-wrap justify-center gap-2.5 mb-10">
            <span class="stat-pill">🚀 No API Key to Start</span>
            <span class="stat-pill">⚡ 95% Zero-Token Generation</span>
            <span class="stat-pill">🤖 10 Autonomous AI Agents</span>
            <span class="stat-pill">📐 100+ Ready Blueprints</span>
            <span class="stat-pill">💰 95%+ AI Cost Eliminated</span>
        </div>

        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
            <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto" style="padding:15px 32px;font-size:16px;">
                Start Building Free →
            </a>
            <a href="{{ $downloadUrl }}" class="btn-secondary w-full sm:w-auto" style="padding:15px 32px;font-size:16px;color:#059669;border-color:rgba(5,150,105,0.3);" onmouseover="this.style.background='rgba(5,150,105,0.05)';this.style.borderColor='rgba(5,150,105,0.5)'" onmouseout="this.style.background='#fff';this.style.borderColor='rgba(5,150,105,0.3)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download v{{ $latestVer }} for cPanel
            </a>
            <a href="#how-it-works" class="btn-outline w-full sm:w-auto" style="padding:15px 32px;font-size:16px;">
                See How It Works ↓
            </a>
        </div>

        <!-- Terminal Preview -->
        <div class="terminal max-w-3xl mx-auto text-left">
            <div class="terminal-header">
                <div class="t-dot" style="background:#ff5f57;"></div>
                <div class="t-dot" style="background:#febc2e;"></div>
                <div class="t-dot" style="background:#28c840;"></div>
                <span class="ml-3 text-xs font-mono" style="color:#64748b;">ryaancms — blueprint mode — no api key required</span>
                <span class="ml-auto text-xs font-mono px-2 py-0.5 rounded"
                      style="background:rgba(255,255,255,0.05); color:#475569;">v2.0</span>
            </div>
            <div class="terminal-body">
                <p style="color:#64748b;">$ ryaan build <span style="color:#a78bfa;">"hospital management system for Bangladesh"</span></p>
                <p class="mt-2" style="color:#475569;">  Detecting domain...</p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Blueprint matched:</span> <span style="color:#f1f5f9; font-weight:600;">Hospital Management</span> <span style="color:#7c3aed; font-weight:700;">(0 AI tokens)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Country pack:</span> <span style="color:#f1f5f9; font-weight:600;">Bangladesh (bKash · Nagad · SSLCommerz)</span> <span style="color:#7c3aed; font-weight:700;">(0 AI tokens)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Modules loaded:</span> <span style="color:#f1f5f9; font-weight:600;">auth · rbac · billing · appointments · pharmacy</span> <span style="color:#7c3aed; font-weight:700;">(0 AI tokens)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">CRUD generated:</span> <span style="color:#f1f5f9; font-weight:600;">patients · doctors · appointments · billing · EMR</span> <span style="color:#7c3aed; font-weight:700;">(0 AI tokens)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Business rules:</span> <span style="color:#f1f5f9; font-weight:600;">patient_privacy · prescription_by_doctor_only</span> <span style="color:#7c3aed; font-weight:700;">(0 AI tokens)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Security:</span> <span style="color:#f1f5f9; font-weight:600;">HIPAA-equivalent + OWASP Top 10 enforced</span></p>
                <p style="color:#4ade80; font-weight:700;">✓ <span style="color:#f1f5f9;">32 files generated in 41 seconds</span></p>
                <div class="mt-4 pt-4 flex items-center gap-3 flex-wrap"
                     style="border-top:1px solid rgba(255,255,255,0.06);">
                    <span style="color:#475569;">AI API cost:</span>
                    <span style="color:#475569; text-decoration:line-through; opacity:.6;">$4.20</span>
                    <span style="color:#475569;">→</span>
                    <span style="color:#4ade80; font-weight:800; font-size:16px;">$0.00</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold"
                          style="background:rgba(74,222,128,0.12); color:#4ade80;">Blueprint Mode — No API Key Used</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tech stack strip -->
<div class="relative z-10 py-6" style="background:#f9fafb; border-top:1px solid #f0ebff; border-bottom:1px solid #f0ebff;">
    <p class="text-center text-xs font-semibold uppercase tracking-widest mb-5" style="color:#9ca3af;">
        Built on battle-tested foundations
    </p>
    <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-3 px-6">
        @foreach(['Laravel 11','PHP 8.3','MySQL / PostgreSQL','Redis','Claude AI','OpenAI GPT','Google Gemini','Alpine.js','Tailwind CSS','Sanctum Auth'] as $t)
        <span class="text-sm font-semibold" style="color:#9ca3af;">{{ $t }}</span>
        @endforeach
    </div>
</div>

<!-- ══════════════════════════════════════════
     THE INTELLIGENCE GAP
══════════════════════════════════════════ -->
<section class="relative py-24 px-6" id="intelligence">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label">The Difference</div>
            <h2 class="display-2 mb-4">
                Other AI builders generate code.<br>
                <span class="g-text">RyaanCMS generates intelligence.</span>
            </h2>
            <p class="text-lg max-w-2xl mx-auto" style="color:#6b7280;">
                Every project makes the next one faster. Every decision becomes a reusable asset. Every domain is already known.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Traditional -->
            <div class="compare-bad p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base"
                         style="background:#fee2e2;">⚠️</div>
                    <h3 class="font-bold text-sm" style="color:#991b1b;">Traditional AI Builders</h3>
                </div>
                <ul class="space-y-3 text-sm">
                    @foreach([
                        'Starts from scratch every single time',
                        'No domain knowledge — "what is a hospital?"',
                        'Forgets every decision made in prior projects',
                        'No business rules — generates wrong accounting logic',
                        'No security awareness — skips authorization, N+1 queries',
                        '100% AI token cost every time',
                        'Getting smarter requires buying a newer model',
                    ] as $item)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 font-bold flex-shrink-0" style="color:#f87171;">✗</span>
                        <span style="color:#6b7280;">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- RyaanCMS -->
            <div class="compare-good p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base"
                         style="background:rgba(109,40,217,0.10);">✦</div>
                    <h3 class="font-bold text-sm" style="color:#4c1d95;">RyaanCMS Intelligence System</h3>
                </div>
                <ul class="space-y-3 text-sm">
                    @foreach([
                        'Start immediately — no API key required',
                        '100+ blueprints — 95% of any app generated at zero AI cost',
                        'Any business domain — blueprints with full module/table/role knowledge',
                        'Business Rules Engine — immutable accounting & payroll rules',
                        '6-layer Senior Dev Knowledge — security, anti-patterns, performance',
                        'Add AI only for the 5% that truly needs it — BYOK',
                        'Gets smarter with every project through Organizational Memory',
                    ] as $item)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 font-bold flex-shrink-0" style="color:#059669;">✓</span>
                        <span style="color:#374151;">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     TWO MODES
══════════════════════════════════════════ -->
<section class="relative py-24 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label">Zero Barrier to Entry</div>
            <h2 class="display-2 mb-4">
                Start Without an API Key.<br>
                <span class="g-text">Add One Only If You Need It.</span>
            </h2>
            <p class="text-lg max-w-2xl mx-auto" style="color:#6b7280;">
                95% of your application is generated from blueprints, knowledge bases, and rule engines — no AI API call needed.
                The remaining 5% is only where AI fills truly novel gaps.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-12">
            <!-- Blueprint Mode -->
            <div class="card p-8" style="background:linear-gradient(135deg,#f0fdf4,#f5fff7); border-color:#bbf7d0;">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                         style="background:rgba(5,150,105,0.12);">🚀</div>
                    <div>
                        <div class="font-bold text-sm" style="color:#065f46;">Blueprint Mode</div>
                        <div class="text-xs font-semibold mt-0.5 px-2 py-0.5 rounded-full inline-block"
                             style="background:rgba(5,150,105,0.1); color:#059669;">No API Key Required</div>
                    </div>
                </div>
                <p class="text-sm mb-5" style="color:#374151;">
                    RyaanCMS selects a pre-built blueprint, loads all modules, generates all CRUD operations,
                    and applies business rules — entirely without any AI API call.
                </p>
                <ul class="space-y-2.5 text-sm">
                    @foreach([
                        '100+ pre-built domain blueprints',
                        'All standard CRUD — generated by rule engine',
                        'Auth, RBAC, payments, notifications — installed automatically',
                        'Business rules applied (accounting, payroll, medical)',
                        'Country-specific payment gateways wired in',
                        'Dashboards, reports, and KPIs pre-configured',
                        '95% of your app — fully deployable, zero API cost',
                    ] as $item)
                    <li class="flex items-start gap-2.5">
                        <span class="flex-shrink-0 font-bold mt-0.5" style="color:#059669;">✓</span>
                        <span style="color:#374151;">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- AI Mode -->
            <div class="card p-8" style="background:linear-gradient(135deg,#faf5ff,#f0f4ff); border-color:#ddd6fe;">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                         style="background:rgba(109,40,217,0.10);">✦</div>
                    <div>
                        <div class="font-bold text-sm" style="color:#4c1d95;">AI Mode</div>
                        <div class="text-xs font-semibold mt-0.5 px-2 py-0.5 rounded-full inline-block"
                             style="background:rgba(109,40,217,0.08); color:#6d28d9;">API Key — Only for the 5%</div>
                    </div>
                </div>
                <p class="text-sm mb-5" style="color:#374151;">
                    Add your own API key only when you need truly custom logic, novel workflows,
                    or AI-powered features that no pre-built blueprint covers.
                </p>
                <ul class="space-y-2.5 text-sm">
                    @foreach([
                        'Custom business logic outside any blueprint',
                        'Novel workflows unique to your domain',
                        'AI features inside the built app (chatbot, recommendations)',
                        'Complex multi-system integrations',
                        'Autonomous 10-agent pipeline for full-stack generation',
                        'Bring your own key — Claude, OpenAI, Gemini, or Ollama',
                        'Pay only for what AI actually had to think about',
                    ] as $item)
                    <li class="flex items-start gap-2.5">
                        <span class="flex-shrink-0 font-bold mt-0.5" style="color:#7c3aed;">✦</span>
                        <span style="color:#374151;">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Flow diagram -->
        <div class="card p-6 text-center" style="background:#f9fafb;">
            <p class="text-xs font-bold uppercase tracking-widest mb-5" style="color:#9ca3af;">How the Decision Works</p>
            <div class="flex flex-wrap justify-center items-center gap-2">
                @foreach([
                    ['Your Prompt','#ede9fe','#6d28d9'],
                    ['Domain Detected','#dbeafe','#1d4ed8'],
                    ['Blueprint Found?','#fef3c7','#b45309'],
                    ['95% Generated — $0.00','#dcfce7','#15803d'],
                    ['Novel Gap?','#fef3c7','#b45309'],
                    ['AI Fills the 5%','#ede9fe','#6d28d9'],
                    ['✓ Full App Ready','#d1fae5','#065f46'],
                ] as $step)
                <span class="p-step" style="background:{{ $step[1] }}; color:{{ $step[2] }};">{{ $step[0] }}</span>
                @if(!$loop->last)
                <span style="color:#d1d5db; font-weight:bold;">›</span>
                @endif
                @endforeach
            </div>
            <p class="text-xs mt-4" style="color:#9ca3af;">
                For 95% of business applications, the entire build completes with zero AI API cost.
            </p>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     INTELLIGENCE PYRAMID
══════════════════════════════════════════ -->
<section class="relative py-24 px-6 section-alt">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label">6-Layer Knowledge Pyramid</div>
            <h2 class="display-2 mb-4">
                Senior Developer Knowledge<br><span class="g-text">Built Into Every Build</span>
            </h2>
            <p class="text-lg max-w-xl mx-auto" style="color:#6b7280;">
                Most AI builders operate at L1. RyaanCMS operates at all 6 levels simultaneously.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-start">
            <!-- Pyramid Visual -->
            <div class="flex flex-col items-center gap-2 pt-4">
                @php
                $levels = [
                    ['L6','Experience','Anti-patterns · Estimation · Risk · Outcomes','#f59e0b','#fffbeb','w-full'],
                    ['L5','Decision','Build vs Buy · Trade-offs · Architecture · Upgrade Paths','#7c3aed','#faf5ff','w-[90%]'],
                    ['L4','Product','KPIs · MVP Blueprints · UX Patterns · Smart Questions','#3b82f6','#eff6ff','w-[80%]'],
                    ['L3','Business','Domain Workflows · Business Rules · Country Knowledge','#0d9488','#f0fdfa','w-[70%]'],
                    ['L2','Architecture','Security · Performance · Caching · API Design','#10b981','#f0fdf4','w-[60%]'],
                    ['L1','Code','SOLID · DRY · KISS · Laravel Conventions · Eloquent','#64748b','#f8fafc','w-[50%]'],
                ];
                @endphp
                @foreach($levels as $lvl)
                <div class="{{ $lvl[5] }} mx-auto">
                    <div class="rounded-xl px-4 py-3 text-center"
                         style="background:{{ $lvl[4] }}; border:1px solid {{ $lvl[2] }}30;">
                        <span class="text-xs font-bold mr-2" style="color:{{ $lvl[2] }}; opacity:0.6;">{{ $lvl[0] }}</span>
                        <span class="text-sm font-bold" style="color:#0f172a;">{{ $lvl[1] }}</span>
                    </div>
                </div>
                @endforeach
                <p class="text-xs mt-2" style="color:#9ca3af;">Peak (L6) → Foundation (L1)</p>
            </div>

            <!-- Level Descriptions -->
            <div class="space-y-2">
                @foreach($levels as $lvl)
                <div class="card p-4">
                    <div class="flex items-start gap-3">
                        <span class="text-xs font-black mt-0.5 w-6 flex-shrink-0"
                              style="color:{{ $lvl[2] }};">{{ $lvl[0] }}</span>
                        <div>
                            <div class="text-sm font-semibold mb-0.5" style="color:#0f172a;">{{ $lvl[1] }}</div>
                            <div class="text-xs" style="color:#9ca3af;">{{ $lvl[3] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Stats -->
        <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([['21','Knowledge Base Files','#6d28d9'],['330+','Wisdom Patterns','#0891b2'],['70%+','AI Cost Eliminated','#059669'],['∞','Domain Blueprints','#d97706']] as $s)
            <div class="card p-5 text-center">
                <div class="text-3xl font-black mb-1.5" style="color:{{ $s[2] }};">{{ $s[0] }}</div>
                <div class="text-xs font-medium" style="color:#6b7280;">{{ $s[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════ -->
<section class="relative py-24 px-6" id="how-it-works">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label">The Process</div>
            <h2 class="display-2 mb-4">
                From Idea to<br><span class="g-text">Production-Ready App</span>
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @php
            $steps = [
                ['01','Describe Your Idea','Type what you want to build in any language — English, Bengali, Arabic, or Hindi. No technical knowledge required.','#6d28d9','#faf5ff'],
                ['02','Blueprint Matched Instantly','Domain detected → pre-built blueprint loaded → modules selected → business rules applied. Zero AI tokens. Zero cost.','#0891b2','#ecfeff'],
                ['03','95% Built Without AI','CRUD, dashboards, reports, auth, payments — all generated by the rule engine from the blueprint. No API key consumed.','#059669','#f0fdf4'],
                ['04','AI Fills Only the Gaps','Novel custom logic? Add your own API key (Claude, OpenAI, Gemini, or Ollama). AI works on the remaining 5% only.','#7c3aed','#faf5ff'],
                ['05','Your Code, Your Server','Full Laravel source code. Deploy on cPanel, VPS, or cloud. No subscription, no lock-in. Own everything forever.','#d97706','#fffbeb'],
            ];
            @endphp
            @foreach($steps as $step)
            <div class="card p-6">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black mb-5"
                     style="background:{{ $step[4] }}; color:{{ $step[3] }}; border:1px solid {{ $step[3] }}20;">
                    {{ $step[0] }}
                </div>
                <div class="font-semibold text-sm mb-2" style="color:#0f172a;">{{ $step[1] }}</div>
                <div class="text-sm leading-relaxed" style="color:#6b7280;">{{ $step[2] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     DOMAIN SHOWCASE
══════════════════════════════════════════ -->
<section class="relative py-24 px-6 section-alt" id="domains">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label">Unlimited Domain Blueprints</div>
            <h2 class="display-2 mb-4">
                Any Business Type.<br><span class="g-text">Zero Guesswork.</span>
            </h2>
            <p class="text-lg max-w-xl mx-auto" style="color:#6b7280;">
                Every domain ships with pre-researched tables, roles, modules, MVP scope, business rules, and KPIs — and the AI generates blueprints for any industry not yet in the library.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @php
            $domains = [
                ['🏬','eCommerce','Products · Orders · Inventory · Payments'],
                ['🤝','CRM','Leads · Pipeline · Deals · Reports'],
                ['👥','HRM','Employees · Payroll · Leave · Attendance'],
                ['🏥','Hospital','Patients · EMR · Billing · Pharmacy'],
                ['🎓','School','Students · Exams · Fees · Parent Portal'],
                ['☁️','SaaS','Multi-tenant · Subscriptions · Billing'],
                ['🍽️','Restaurant','Menu · Orders · Kitchen · Delivery'],
                ['📦','Inventory','Stock · Warehouses · GRN · FIFO'],
                ['🛒','Marketplace','Vendors · Commission · Multi-cart'],
                ['🖥️','POS','Cashier · Barcode · Cash/Card/MFS'],
                ['🏗️','ERP','Accounting → HR → Procurement → MFG'],
                ['💰','Finance','Double Entry · Ledger · Balance Sheet'],
            ];
            @endphp
            @foreach($domains as $d)
            <div class="domain-card card p-4">
                <div class="text-2xl mb-2">{{ $d[0] }}</div>
                <div class="font-semibold text-sm mb-1" style="color:#0f172a;">{{ $d[1] }}</div>
                <div class="text-xs leading-relaxed" style="color:#9ca3af;">{{ $d[2] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     10-AGENT PIPELINE
══════════════════════════════════════════ -->
<section class="relative py-24 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label section-label-teal">Autonomous Pipeline</div>
            <h2 class="display-2 mb-4">
                10 Specialized AI Agents<br><span class="g-text">Working as One Team</span>
            </h2>
            <p class="text-lg max-w-xl mx-auto" style="color:#6b7280;">
                PLAN → GENERATE → RUN → FIX → RETRY. The pipeline keeps going until quality passes.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-8">
            @php
            $agents = [
                ['📋','Requirements','Extracts scope & constraints'],
                ['🎯','Product','Defines features & flows'],
                ['🏗️','Architect','Designs system architecture'],
                ['🗄️','Database','Creates optimized schema'],
                ['🎨','UI/UX','Builds responsive interfaces'],
                ['⚙️','Backend','Generates business logic'],
                ['🧪','Testing','Writes feature + unit tests'],
                ['🔍','QA','Reviews code quality'],
                ['🛡️','Security','Audits for vulnerabilities'],
                ['🔧','Debug','Fixes and iterates'],
            ];
            @endphp
            @foreach($agents as $agent)
            <div class="agent-card">
                <div class="text-2xl mb-2">{{ $agent[0] }}</div>
                <div class="text-xs font-bold mb-0.5" style="color:#0f172a;">{{ $agent[1] }}</div>
                <div class="text-xs" style="color:#9ca3af;">{{ $agent[2] }}</div>
            </div>
            @endforeach
        </div>

        <!-- Pipeline Flow -->
        <div class="card p-6 text-center" style="background:#f9fafb;">
            <div class="flex flex-wrap justify-center items-center gap-2 mb-3">
                @foreach([
                    ['PLAN','#ede9fe','#6d28d9'],
                    ['GENERATE','#dbeafe','#1d4ed8'],
                    ['RUN','#dcfce7','#15803d'],
                    ['TEST','#fef3c7','#b45309'],
                    ['FIX','#fee2e2','#b91c1c'],
                    ['RETRY','#cffafe','#0e7490'],
                    ['✓ QUALITY PASS','#d1fae5','#065f46'],
                ] as $step)
                <span class="p-step" style="background:{{ $step[1] }}; color:{{ $step[2] }};">{{ $step[0] }}</span>
                @if(!$loop->last)
                <span style="color:#d1d5db; font-weight:bold;">›</span>
                @endif
                @endforeach
            </div>
            <p class="text-xs" style="color:#9ca3af;">
                Loop continues automatically until all quality checks pass — streamed live to your browser
            </p>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     COUNTRY-AWARE INTELLIGENCE
══════════════════════════════════════════ -->
<section class="relative py-24 px-6 section-alt">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label section-label-teal">Global Intelligence</div>
            <h2 class="display-2 mb-4">
                AI That Knows<br><span class="g-text">Your Country</span>
            </h2>
            <p class="text-lg max-w-xl mx-auto" style="color:#6b7280;">
                Auto-applies country-specific payments, legal requirements, UX patterns, and business rules.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
            $countries = [
                ['🇧🇩','Bangladesh','bKash · Nagad · SSLCommerz · COD mandatory · Bengali+English · Mobile-first'],
                ['🇮🇳','India','UPI dominant · Razorpay · GST 18% · TDS · Hindi/Regional · EMI display'],
                ['🇦🇪','UAE','Stripe · PayTabs · Tabby BNPL · 5% VAT · ZATCA e-invoice · Arabic RTL'],
                ['🇺🇸','USA','Stripe · TaxJar · CCPA · ADA accessibility · HIPAA for healthcare · MM/DD/YYYY'],
                ['🇸🇦','Saudi Arabia','Hyperpay · Mada cards · 15% VAT · ZATCA Phase 2 · Arabic RTL · Hijri calendar'],
                ['🇬🇧','United Kingdom','Stripe · GoCardless · 20% VAT · UK GDPR · ICO registration · DD/MM/YYYY'],
            ];
            @endphp
            @foreach($countries as $c)
            <div class="card p-5">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-2xl">{{ $c[0] }}</span>
                    <div>
                        <div class="font-semibold text-sm" style="color:#0f172a;">{{ $c[1] }}</div>
                        <div class="text-xs" style="color:#9ca3af;">Country Pack</div>
                    </div>
                </div>
                <p class="text-xs leading-relaxed" style="color:#6b7280;">{{ $c[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FEATURE CARDS
══════════════════════════════════════════ -->
<section class="relative py-24 px-6" id="features">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <div class="section-label">Core Capabilities</div>
            <h2 class="display-2 mb-4">
                Everything a Senior Developer<br><span class="g-text">Would Do Automatically</span>
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
            $features = [
                ['🚀','No API Key to Start','#059669','#f0fdf4','Start building immediately — no API key, no credit card, no setup. 95% of your application is generated from pre-built blueprints at zero cost.'],
                ['🏗️','100+ Ready Blueprints','#6d28d9','#faf5ff','eCommerce, Hospital, School, CRM, ERP, SaaS, Restaurant, POS — 100 pre-researched blueprints with tables, roles, modules, and business rules pre-loaded.'],
                ['⚡','Zero-AI CRUD Generation','#0891b2','#ecfeff','All standard CRUD generated by rule engine — 0 AI tokens. Models, controllers, views, routes, migrations — done in seconds without any API cost.'],
                ['🛡️','Senior Dev Security','#dc2626','#fef2f2','OWASP Top 10 auto-applied. Authorization enforced. SQL injection, XSS, mass assignment — blocked automatically on every file generated.'],
                ['📊','Business KPI Dashboards','#d97706','#fffbeb','Domain-specific KPIs pre-configured. eCommerce AOV, SaaS MRR/churn, CRM win rate — not generic "total records" pages.'],
                ['🧠','Organizational Memory','#4f46e5','#eef2ff','Decisions, patterns, and lessons accumulate across projects. The platform gets smarter every time you build — with or without AI.'],
                ['🔑','BYOK — Bring Your Own Key','#7c3aed','#faf5ff','Use Claude, OpenAI, Gemini, or local Ollama. Add your key only when you need the 5% — custom logic AI actually has to think about.'],
                ['⚠️','Risk Detection','#ea580c','#fff7ed','15 critical technical risks auto-flagged — missing transactions, no pagination, no authorization — caught before damage.'],
                ['🏠','Self-Hosted & Open Source','#0d9488','#f0fdfa','Full Laravel source code. Your server, your cloud, your country. No vendor lock-in. No monthly tax. Own it forever.'],
            ];
            @endphp
            @foreach($features as $f)
            <div class="card p-6">
                <div class="feat-icon" style="background:{{ $f[3] }}; border:1px solid {{ $f[2] }}18;">
                    {{ $f[0] }}
                </div>
                <div class="font-semibold mb-2" style="color:#0f172a; font-size:15px;">{{ $f[1] }}</div>
                <div class="text-sm leading-relaxed" style="color:#6b7280;">{{ $f[4] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     NUMBERS
══════════════════════════════════════════ -->
<section class="relative py-20 px-6 section-alt">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach([
                ['100+', 'Ready Blueprints',       '#6d28d9'],
                ['95%',  'Zero-Token Generation',  '#059669'],
                ['10',   'Autonomous AI Agents',   '#4f46e5'],
                ['21',   'Knowledge Base Files',   '#0891b2'],
                ['$0',   'Cost to Start Building', '#065f46'],
                ['330+', 'Wisdom Patterns',        '#d97706'],
            ] as $n)
            <div class="card p-6 text-center">
                <div class="text-4xl font-black mb-1.5 g-text">{{ $n[0] }}</div>
                <div class="text-xs font-semibold" style="color:#374151;">{{ $n[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     MARKETPLACE
══════════════════════════════════════════ -->
<section class="relative py-24 px-6">
    <div class="max-w-5xl mx-auto text-center">
        <div class="section-label">Marketplace</div>
        <h2 class="display-2 mb-5">
            Build Once.<br><span class="g-text">Sell to Thousands.</span>
        </h2>
        <p class="text-lg max-w-2xl mx-auto mb-12" style="color:#6b7280;">
            Package your app as a module and publish to the RyaanCMS Marketplace.
            Every developer and agency can install it with one click.
        </p>

        <div class="grid sm:grid-cols-3 gap-5 mb-10">
            @foreach([
                ['📦','Blueprint Marketplace','Publish complete domain blueprints. Developers install your proven app structure instantly.'],
                ['🔌','Module Marketplace','One-click module installation. ZIP packages with manifest, licensing, and domain activation.'],
                ['🏢','Agency Directory','Certified agencies who build with RyaanCMS. Quality scores, delivery history, and reviews.'],
            ] as $m)
            <div class="card p-6 text-center">
                <div class="text-3xl mb-3">{{ $m[0] }}</div>
                <div class="font-semibold mb-2" style="color:#0f172a;">{{ $m[1] }}</div>
                <div class="text-sm" style="color:#6b7280;">{{ $m[2] }}</div>
            </div>
            @endforeach
        </div>

        <a href="{{ route('register') }}" class="btn-primary" style="padding:15px 32px;font-size:15px;">
            Join the Marketplace →
        </a>
    </div>
</section>

<!-- ══════════════════════════════════════════
     PRICING
══════════════════════════════════════════ -->
<section class="relative py-24 px-6" id="pricing" style="background:#0f172a; overflow:hidden;">

    {{-- Background glow --}}
    <div style="position:absolute;inset:0;pointer-events:none;">
        <div style="position:absolute;top:-120px;left:50%;transform:translateX(-50%);width:900px;height:500px;background:radial-gradient(ellipse,rgba(109,40,217,0.18) 0%,transparent 70%);"></div>
        <div style="position:absolute;bottom:-80px;left:20%;width:400px;height:300px;background:radial-gradient(ellipse,rgba(5,150,105,0.12) 0%,transparent 70%);"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-6"
                 style="background:rgba(109,40,217,0.15);border:1px solid rgba(109,40,217,0.3);color:#a78bfa;">
                <span style="width:6px;height:6px;border-radius:50%;background:#a78bfa;display:inline-block;"></span>
                Simple Pricing
            </div>
            <h2 style="font-size:clamp(30px,4vw,52px);font-weight:800;line-height:1.1;letter-spacing:-0.03em;color:#fff;margin-bottom:16px;">
                95% of your AI costs — <span style="background:linear-gradient(135deg,#a78bfa,#34d399);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">already saved.</span>
            </h2>
            <p style="font-size:18px;color:#94a3b8;max-width:560px;margin:0 auto;line-height:1.6;">
                The 5% you do spend? We help cover that too — <strong style="color:#34d399;">first month free,</strong> then a small flat fee to keep the intelligence running.
            </p>
        </div>

        {{-- Cost comparison bar --}}
        <div style="max-width:680px;margin:0 auto 56px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:24px 28px;">
            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:14px;">What you actually pay in AI costs</div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <div style="font-size:12px;color:#94a3b8;width:90px;flex-shrink:0;">Traditional</div>
                <div style="flex:1;height:10px;border-radius:99px;background:rgba(239,68,68,0.2);overflow:hidden;">
                    <div style="height:100%;width:100%;background:linear-gradient(90deg,#ef4444,#f97316);border-radius:99px;"></div>
                </div>
                <div style="font-size:12px;font-weight:700;color:#f87171;width:50px;text-align:right;">100%</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="font-size:12px;color:#94a3b8;width:90px;flex-shrink:0;">RyaanCMS</div>
                <div style="flex:1;height:10px;border-radius:99px;background:rgba(52,211,153,0.12);overflow:hidden;">
                    <div style="height:100%;width:5%;background:linear-gradient(90deg,#34d399,#10b981);border-radius:99px;"></div>
                </div>
                <div style="font-size:12px;font-weight:800;color:#34d399;width:50px;text-align:right;">~5%</div>
            </div>
            <div style="margin-top:14px;font-size:12px;color:#475569;border-top:1px solid rgba(255,255,255,0.06);padding-top:12px;">
                95% of app generation is blueprints + CRUD + rules — <strong style="color:#64748b;">zero AI tokens.</strong> Only truly novel logic ever hits the AI.
            </div>
        </div>

        {{-- Plan cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:20px;max-width:1000px;margin:0 auto;">

            {{-- Free / Community --}}
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:32px;display:flex;flex-direction:column;gap:20px;">
                <div>
                    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(100,116,139,0.15);border:1px solid rgba(100,116,139,0.2);border-radius:99px;padding:4px 12px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.06em;margin-bottom:14px;">COMMUNITY</div>
                    <div style="font-size:36px;font-weight:800;color:#fff;letter-spacing:-0.03em;">$0<span style="font-size:16px;font-weight:500;color:#64748b;">/mo</span></div>
                    <div style="font-size:14px;color:#64748b;margin-top:4px;">Self-hosted forever. No expiry.</div>
                </div>
                <ul style="display:flex;flex-direction:column;gap:10px;list-style:none;padding:0;margin:0;flex:1;">
                    @foreach([
                        '100+ Blueprint Library',
                        '95% Zero-Token Generation',
                        'Full CRUD Generator',
                        'No API key to start',
                        '12 Domain Modules',
                        'Community Question Packs',
                        'Self-hosted (your server)',
                        'Full source code',
                    ] as $f)
                    <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:#94a3b8;">
                        <span style="width:16px;height:16px;border-radius:50%;background:rgba(100,116,139,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:9px;height:9px;stroke:#64748b" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" style="display:block;text-align:center;padding:13px;border-radius:12px;font-size:14px;font-weight:700;color:#94a3b8;border:1.5px solid rgba(255,255,255,0.1);text-decoration:none;transition:all .15s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.25)';this.style.color='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)';this.style.color='#94a3b8'">
                    Get Started Free →
                </a>
            </div>

            {{-- Pro — featured --}}
            <div style="background:linear-gradient(145deg,rgba(109,40,217,0.25),rgba(79,70,229,0.15));border:1.5px solid rgba(167,139,250,0.35);border-radius:20px;padding:32px;display:flex;flex-direction:column;gap:20px;position:relative;box-shadow:0 0 60px rgba(109,40,217,0.2);">

                {{-- Popular badge --}}
                <div style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#7c3aed,#6366f1);border-radius:99px;padding:4px 16px;font-size:11px;font-weight:800;color:#fff;letter-spacing:.06em;white-space:nowrap;box-shadow:0 4px 14px rgba(109,40,217,0.4);">
                    ★ MOST POPULAR
                </div>

                <div>
                    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(167,139,250,0.15);border:1px solid rgba(167,139,250,0.3);border-radius:99px;padding:4px 12px;font-size:11px;font-weight:700;color:#a78bfa;letter-spacing:.06em;margin-bottom:14px;">PRO</div>

                    {{-- Pricing with promo --}}
                    <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap;">
                        <div style="font-size:36px;font-weight:800;color:#fff;letter-spacing:-0.03em;">$9<span style="font-size:16px;font-weight:500;color:#a78bfa;">/mo</span></div>
                        <div style="background:rgba(52,211,153,0.15);border:1px solid rgba(52,211,153,0.3);border-radius:99px;padding:3px 10px;font-size:11px;font-weight:800;color:#34d399;">1st month FREE</div>
                    </div>
                    <div style="font-size:13px;color:#7c3aed;margin-top:6px;background:rgba(167,139,250,0.1);border-radius:8px;padding:6px 10px;display:inline-block;">
                        You save 95% in AI costs. We take $9. <strong style="color:#a78bfa;">Net savings: massive.</strong>
                    </div>
                </div>

                <ul style="display:flex;flex-direction:column;gap:10px;list-style:none;padding:0;margin:0;flex:1;">
                    @foreach([
                        ['Everything in Community', false],
                        ['Premium Question Packs', true],
                        ['Country-specific Packs (BD, IN, US…)', true],
                        ['Priority Registry Sync', true],
                        ['AI Mode — BYOK (your API key)', false],
                        ['Blueprint Pack Updates', true],
                        ['Industry Brain Packs', true],
                        ['Email support', false],
                    ] as [$f, $premium])
                    <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:{{ $premium ? '#e2e8f0' : '#94a3b8' }};">
                        <span style="width:16px;height:16px;border-radius:50%;background:{{ $premium ? 'rgba(167,139,250,0.25)' : 'rgba(100,116,139,0.15)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:9px;height:9px;stroke:{{ $premium ? '#a78bfa' : '#64748b' }}" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        {{ $f }}
                        @if($premium)<span style="font-size:10px;background:rgba(167,139,250,0.15);color:#a78bfa;border-radius:4px;padding:1px 5px;margin-left:2px;">PRO</span>@endif
                    </li>
                    @endforeach
                </ul>

                <div>
                    <a href="{{ route('register') }}" style="display:block;text-align:center;padding:14px;border-radius:12px;font-size:14px;font-weight:800;color:#fff;background:linear-gradient(135deg,#7c3aed,#6366f1);text-decoration:none;box-shadow:0 4px 20px rgba(109,40,217,0.4);letter-spacing:.01em;">
                        Claim 1 Month Free →
                    </a>
                    <div style="text-align:center;font-size:11.5px;color:#64748b;margin-top:8px;">No credit card needed to start. Cancel anytime.</div>
                </div>
            </div>

            {{-- Enterprise --}}
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:32px;display:flex;flex-direction:column;gap:20px;">
                <div>
                    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.2);border-radius:99px;padding:4px 12px;font-size:11px;font-weight:700;color:#fbbf24;letter-spacing:.06em;margin-bottom:14px;">ENTERPRISE</div>
                    <div style="font-size:36px;font-weight:800;color:#fff;letter-spacing:-0.03em;">Custom</div>
                    <div style="font-size:14px;color:#64748b;margin-top:4px;">Agencies, SaaS builders, white-label.</div>
                </div>
                <ul style="display:flex;flex-direction:column;gap:10px;list-style:none;padding:0;margin:0;flex:1;">
                    @foreach([
                        'Everything in Pro',
                        'White-label branding',
                        'Custom domain packs',
                        'Dedicated registry instance',
                        'Multi-team / multi-tenant',
                        'Custom module development',
                        'SLA + priority support',
                        'On-premise registry option',
                    ] as $f)
                    <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:#94a3b8;">
                        <span style="width:16px;height:16px;border-radius:50%;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:9px;height:9px;stroke:#fbbf24" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="mailto:hello@ryaancms.com" style="display:block;text-align:center;padding:13px;border-radius:12px;font-size:14px;font-weight:700;color:#fbbf24;border:1.5px solid rgba(245,158,11,0.25);text-decoration:none;transition:all .15s;" onmouseover="this.style.borderColor='rgba(245,158,11,0.5)';this.style.background='rgba(245,158,11,0.07)'" onmouseout="this.style.borderColor='rgba(245,158,11,0.25)';this.style.background='transparent'">
                    Contact Us →
                </a>
            </div>

        </div>

        {{-- Bottom trust note --}}
        <div style="text-align:center;margin-top:40px;display:flex;flex-wrap:wrap;justify-content:center;gap:24px;">
            @foreach([
                '✓ No credit card to start',
                '✓ Self-hosted — your server, your data',
                '✓ Cancel Pro anytime',
                '✓ Open source — always',
                '✓ 95% AI cost saved',
            ] as $note)
            <span style="font-size:13px;color:#475569;">{{ $note }}</span>
            @endforeach
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════
     CTA
══════════════════════════════════════════ -->
<section class="relative py-28 px-6 section-tint" style="border-top:1px solid #ede9fe;">
    <div class="max-w-3xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-8"
             style="background:rgba(5,150,105,0.08); border:1px solid rgba(5,150,105,0.2); color:#065f46;">
            <span>✓</span> Free &amp; Open Source — Always
        </div>

        <h2 class="mb-5" style="font-size:clamp(34px,5vw,62px); font-weight:800; line-height:1.05; letter-spacing:-0.03em; color:#0f172a;">
            Start Building the<br><span class="g-text">Smartest Way</span>
        </h2>

        <p class="text-xl leading-relaxed mb-10 max-w-xl mx-auto" style="color:#4b5563;">
            Free forever. Self-hosted. Full source code. Build anything from a CRM to a full hospital ERP.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-10">
            <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto" style="padding:17px 36px;font-size:16px;">
                Start Building Free →
            </a>
            <a href="{{ $downloadUrl }}" class="btn-secondary w-full sm:w-auto" style="padding:17px 36px;font-size:16px;color:#059669;border-color:rgba(5,150,105,0.3);" onmouseover="this.style.background='rgba(5,150,105,0.05)'" onmouseout="this.style.background='#fff'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download v{{ $latestVer }} for cPanel
            </a>
            <a href="{{ route('login') }}" class="btn-secondary w-full sm:w-auto" style="padding:17px 36px;font-size:16px;">
                Sign In
            </a>
        </div>

        <div class="flex flex-wrap justify-center gap-6 text-sm" style="color:#6b7280;">
            @foreach(['No API key to start','No credit card required','Deploy on your own server','Keep all source code','No vendor lock-in'] as $item)
            <span class="flex items-center gap-1.5">
                <span style="color:#059669; font-weight:700;">✓</span> {{ $item }}
            </span>
            @endforeach
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ -->
<footer class="px-6 py-14" style="background:#f9fafb;border-top:1px solid #e5e7eb;">
    <div class="max-w-7xl mx-auto">

        {{-- Brand + columns grid --}}
        <div class="footer-cols">

            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#6d28d9,#4f46e5);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-sm" style="color:#0f172a;">RyaanCMS</span>
                </div>
                <p class="text-sm leading-relaxed mb-5" style="color:#6b7280;max-width:220px;">
                    The world's first AI Business Operating System Builder. Free forever. Self-hosted. Open source.
                </p>
                <a href="https://github.com/ryaancms" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-sm font-medium transition-colors"
                   style="color:#6b7280;"
                   onmouseover="this.style.color='#6d28d9'" onmouseout="this.style.color='#6b7280'">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                    </svg>
                    GitHub
                </a>
            </div>

            {{-- Product --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color:#9ca3af;">Product</h4>
                <ul class="space-y-2.5 text-sm" style="color:#6b7280;">
                    <li><a href="#features"     class="hover:text-violet-700 transition-colors">Features</a></li>
                    <li><a href="#domains"      class="hover:text-violet-700 transition-colors">Domains</a></li>
                    <li><a href="#intelligence" class="hover:text-violet-700 transition-colors">Intelligence</a></li>
                    <li><a href="#how-it-works" class="hover:text-violet-700 transition-colors">How It Works</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-violet-700 transition-colors">Get Started Free</a></li>
                    <li><a href="{{ route('login') }}"    class="hover:text-violet-700 transition-colors">Sign In</a></li>
                </ul>
            </div>

            {{-- Domains --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color:#9ca3af;">Domains</h4>
                <ul class="space-y-2.5 text-sm" style="color:#6b7280;">
                    <li><a href="#domains" class="hover:text-violet-700 transition-colors">Laravel / React</a></li>
                    <li><a href="#domains" class="hover:text-violet-700 transition-colors">eCommerce</a></li>
                    <li><a href="#domains" class="hover:text-violet-700 transition-colors">CRM &amp; HRM</a></li>
                    <li><a href="#domains" class="hover:text-violet-700 transition-colors">Hospital &amp; School</a></li>
                    <li><a href="#domains" class="hover:text-violet-700 transition-colors">Restaurant &amp; POS</a></li>
                    <li><a href="#domains" class="hover:text-violet-700 transition-colors">SaaS &amp; ERP</a></li>
                </ul>
            </div>

            {{-- Modules --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color:#9ca3af;">Modules</h4>
                <ul class="space-y-2.5 text-sm" style="color:#6b7280;">
                    <li><a href="#features" class="hover:text-violet-700 transition-colors">RBAC &amp; Auth</a></li>
                    <li><a href="#features" class="hover:text-violet-700 transition-colors">Billing &amp; Invoices</a></li>
                    <li><a href="#features" class="hover:text-violet-700 transition-colors">Reports &amp; Analytics</a></li>
                    <li><a href="#features" class="hover:text-violet-700 transition-colors">Notifications</a></li>
                    <li><a href="#features" class="hover:text-violet-700 transition-colors">API Builder</a></li>
                    <li><a href="#features" class="hover:text-violet-700 transition-colors">Multi-tenant</a></li>
                </ul>
            </div>

            {{-- Resources --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color:#9ca3af;">Resources</h4>
                <ul class="space-y-2.5 text-sm" style="color:#6b7280;">
                    <li><a href="https://github.com/ryaancms" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">Documentation</a></li>
                    <li><a href="https://github.com/ryaancms" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">API Reference</a></li>
                    <li><a href="https://github.com/ryaancms" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">GitHub</a></li>
                    <li><a href="{{ $releasesUrl }}" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">Download v{{ $latestVer }}</a></li>
                    <li><a href="{{ $releasesUrl }}" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">Changelog</a></li>
                    <li><a href="https://github.com/ryaancms/issues" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">Report Issue</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color:#9ca3af;">Legal</h4>
                <ul class="space-y-2.5 text-sm" style="color:#6b7280;">
                    <li><a href="{{ route('terms') }}"   class="hover:text-violet-700 transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-violet-700 transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-violet-700 transition-colors">Cookie Policy</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-violet-700 transition-colors">Open Source License</a></li>
                </ul>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3"
             style="border-top:1px solid #e5e7eb;">
            <div class="flex items-center gap-6">
                <p class="text-xs" style="color:#9ca3af;">© {{ date('Y') }} RyaanCMS. Open Source. Free Forever.</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-bold" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">v1.0 — MIT License</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span>
                    <span class="text-xs" style="color:#9ca3af;">All systems operational</span>
                </div>
                <a href="https://github.com/ryaancms" target="_blank" rel="noopener"
                   class="text-xs transition-colors" style="color:#9ca3af;"
                   onmouseover="this.style.color='#6d28d9'" onmouseout="this.style.color='#9ca3af'">
                    Star on GitHub ★
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
function landing() {
    return {
        scrolled: false,
        mobileMenuOpen: false,
        init() {
            window.addEventListener('scroll', () => {
                this.scrolled = window.scrollY > 20;
            });
        }
    }
}
</script>
</body>
</html>
