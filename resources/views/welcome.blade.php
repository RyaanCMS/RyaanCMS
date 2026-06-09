<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RyaanCMS — The World's First AI Business Operating System Builder. 21 Knowledge Bases, 10 AI Agents, Blueprint Assembly. Build smarter. Ship faster.">
    <title>RyaanCMS — AI Business Operating System Builder</title>
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
    </style>
</head>
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
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2.5">
            <a href="https://github.com/ryaancms" target="_blank" rel="noopener" class="btn-outline hidden sm:inline-flex">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                </svg>
                <span class="hidden lg:inline">GitHub</span>
            </a>
            <a href="{{ route('login') }}" class="btn-secondary hidden sm:inline-flex" style="padding:9px 18px;font-size:14px;">Sign In</a>
            <a href="{{ route('register') }}" class="btn-primary" style="padding:9px 20px;font-size:14px;">Get Started Free →</a>
        </div>
    </nav>
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
            Not just code generation — an AI that accumulates wisdom, remembers every decision,
            and gets smarter with every project it builds.
        </p>

        <!-- Stat Pills -->
        <div class="flex flex-wrap justify-center gap-2.5 mb-10">
            <span class="stat-pill">⚡ 21 Knowledge Bases</span>
            <span class="stat-pill">🤖 10 AI Agents</span>
            <span class="stat-pill">📐 Unlimited Domains</span>
            <span class="stat-pill">💰 70%+ AI Cost Reduction</span>
            <span class="stat-pill">🛡️ Senior Dev Intelligence</span>
        </div>

        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
            <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto" style="padding:15px 32px;font-size:16px;">
                Start Building Free →
            </a>
            <a href="#how-it-works" class="btn-secondary w-full sm:w-auto" style="padding:15px 32px;font-size:16px;">
                See How It Works ↓
            </a>
        </div>

        <!-- Terminal Preview -->
        <div class="terminal max-w-3xl mx-auto text-left">
            <div class="terminal-header">
                <div class="t-dot" style="background:#ff5f57;"></div>
                <div class="t-dot" style="background:#febc2e;"></div>
                <div class="t-dot" style="background:#28c840;"></div>
                <span class="ml-3 text-xs font-mono" style="color:#64748b;">ryaancms — ai builder</span>
                <span class="ml-auto text-xs font-mono px-2 py-0.5 rounded"
                      style="background:rgba(255,255,255,0.05); color:#475569;">v2.0</span>
            </div>
            <div class="terminal-body">
                <p style="color:#64748b;">$ ryaan build <span style="color:#a78bfa;">"hospital management system for Bangladesh"</span></p>
                <p class="mt-2" style="color:#475569;">  Analyzing prompt...</p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Domain detected:</span> <span style="color:#f1f5f9; font-weight:600;">hospital</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Country pack:</span> <span style="color:#f1f5f9; font-weight:600;">Bangladesh (bKash · Nagad · SSLCommerz)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Blueprint:</span> <span style="color:#f1f5f9; font-weight:600;">hospital_v2</span> <span style="color:#7c3aed; font-weight:700;">(0 AI tokens used)</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Business rules:</span> <span style="color:#f1f5f9; font-weight:600;">patient_privacy · prescription_by_doctor_only</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">Security:</span> <span style="color:#f1f5f9; font-weight:600;">HIPAA-equivalent + OWASP Top 10 enforced</span></p>
                <p style="color:#4ade80;">✓ <span style="color:#475569;">28 files generated</span> <span style="color:#f1f5f9; font-weight:600;">in 47 seconds</span></p>
                <div class="mt-4 pt-4 flex items-center gap-3 flex-wrap"
                     style="border-top:1px solid rgba(255,255,255,0.06);">
                    <span style="color:#475569;">AI cost:</span>
                    <span style="color:#475569; text-decoration:line-through; opacity:.6;">$4.20</span>
                    <span style="color:#475569;">→</span>
                    <span style="color:#4ade80; font-weight:800; font-size:15px;">$0.31</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold"
                          style="background:rgba(34,211,238,0.12); color:#22d3ee;">93% savings</span>
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
                        'Blueprint Assembly — reuses proven structures instantly',
                        'Any business domain — blueprints with full module/table/role knowledge',
                        'Decision Cache — reuses prior decisions automatically',
                        'Business Rules Engine — immutable accounting & payroll rules',
                        '6-layer Senior Dev Knowledge — security, anti-patterns, performance',
                        '70%+ AI cost reduction through intelligent routing',
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

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
            $steps = [
                ['01','Describe Your Idea','Tell RyaanCMS what you want to build in plain language. Bengali, English, or any language.','#6d28d9','#faf5ff'],
                ['02','Intelligence Applied','Domain detected → Blueprint selected → 21 KB files loaded → Senior Dev Brief injected. All in milliseconds.','#0891b2','#ecfeff'],
                ['03','Assembled, Not Generated','CRUD generated by rule engine (0 AI tokens). Business logic by knowledge base. Only novel parts use AI.','#059669','#f0fdf4'],
                ['04','Your Code, Your Server','Full Laravel source code. Deploy anywhere. No subscription, no lock-in. Own everything forever.','#d97706','#fffbeb'],
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
                ['🏗️','Blueprint Assembly','#6d28d9','#faf5ff','Proven app structures assembled in seconds — CRM, Hospital, School, SaaS — pre-researched tables, roles, and modules ready to go.'],
                ['🧠','Organizational Memory','#4f46e5','#eef2ff','Decisions, patterns, and lessons accumulate across projects. The platform gets smarter every time you build.'],
                ['🛡️','Senior Dev Security','#dc2626','#fef2f2','OWASP Top 10 auto-applied. Authorization enforced. SQL injection, XSS, mass assignment — blocked automatically.'],
                ['⚡','Zero-AI CRUD','#059669','#f0fdf4','Standard CRUD generated by rule engine — 0 AI tokens. 70%+ of typical generation cost eliminated through intelligent routing.'],
                ['📊','Business KPI Dashboards','#d97706','#fffbeb','Domain-specific KPIs pre-configured. eCommerce AOV, SaaS MRR/churn, CRM win rate — not generic "total records" pages.'],
                ['🏠','Self-Hosted & Open Source','#0891b2','#ecfeff','Full Laravel source code. Your server, your cloud, your country. No vendor lock-in. No monthly tax. Own it forever.'],
                ['💡','Smart Question Engine','#1d4ed8','#eff6ff','AI asks the right questions before building — multi-tenant? Payment gateway? Country? Prevents 80% of rework.'],
                ['⚠️','Risk Detection','#ea580c','#fff7ed','15 critical technical risks auto-flagged — missing transactions, no pagination, no authorization — caught before damage.'],
                ['🔄','Upgrade Path Intelligence','#0d9488','#f0fdfa','Monolith → Modular → Microservices. MySQL → PostgreSQL. Manual → CI/CD. Clear evolution paths built in.'],
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
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach([
                ['21',   'Knowledge Base Files','#6d28d9'],
                ['10',   'Autonomous AI Agents','#4f46e5'],
                ['12',   'Domain Blueprints',   '#0891b2'],
                ['70%+', 'AI Cost Reduction',   '#059669'],
                ['330+', 'Wisdom Patterns',     '#d97706'],
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
            <a href="{{ route('login') }}" class="btn-secondary w-full sm:w-auto" style="padding:17px 36px;font-size:16px;">
                Sign In
            </a>
        </div>

        <div class="flex flex-wrap justify-center gap-6 text-sm" style="color:#6b7280;">
            @foreach(['No credit card required','Deploy on your own server','Keep all source code','No vendor lock-in'] as $item)
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
        <div style="display:grid;grid-template-columns:280px repeat(5,1fr);gap:40px;margin-bottom:40px;align-items:start;">

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
                    <li><a href="https://github.com/ryaancms/releases" target="_blank" rel="noopener" class="hover:text-violet-700 transition-colors">Changelog</a></li>
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
