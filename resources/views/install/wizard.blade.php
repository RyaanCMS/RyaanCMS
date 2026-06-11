@php if (!isset($errors)) { $errors = new \Illuminate\Support\ViewErrorBag(); } @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install RyaanCMS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'inter', sans-serif; }
        .step-active { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
        .step-done   { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
        .step-todo   { background: #f8fafc; color: #94a3b8; border-color: #e2e8f0; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50 flex items-center justify-center p-4">

<!-- Background blobs -->
<div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full blur-3xl" style="background:rgba(99,102,241,.08)"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full blur-3xl" style="background:rgba(139,92,246,.07)"></div>
</div>

<div class="relative w-full max-w-2xl">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 shadow-lg"
             style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-800">Install RyaanCMS</h1>
        <p class="text-slate-500 text-sm mt-1">AI-Powered Application Operating System</p>
    </div>

    <!-- Step Indicator -->
    <div class="flex items-center justify-center mb-8 gap-0">
        @foreach([
            ['num'=>1,'label'=>'Requirements'],
            ['num'=>2,'label'=>'Database'],
            ['num'=>3,'label'=>'Migrate'],
            ['num'=>4,'label'=>'Admin'],
            ['num'=>5,'label'=>'Done'],
        ] as $s)
        <div class="flex items-center">
            <div class="flex flex-col items-center">
                <div class="w-9 h-9 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-all
                    {{ $step == $s['num'] ? 'step-active border-indigo-500 shadow-lg shadow-indigo-200' : ($step > $s['num'] ? 'step-done border-emerald-300' : 'step-todo border-slate-200') }}">
                    @if($step > $s['num'])
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    {{ $s['num'] }}
                    @endif
                </div>
                <span class="text-[10px] font-medium mt-1 {{ $step == $s['num'] ? 'text-indigo-600' : 'text-slate-400' }}">
                    {{ $s['label'] }}
                </span>
            </div>
            @if(!$loop->last)
            <div class="w-12 h-0.5 mb-4 mx-1 {{ $step > $s['num'] ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Card -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/80 border border-slate-100 overflow-hidden">

        {{-- ══ STEP 1: Requirements ══ --}}
        @if($step === 1)
        <div class="px-8 pt-8 pb-6 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#eef2ff,#ede9fe);">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">System Requirements</h2>
                    <p class="text-sm text-slate-500">Checking your server environment</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            @php $allOk = collect($checks)->every(fn($c) => $c['ok']); @endphp

            @if(!$allOk)
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700 font-medium">
                Some requirements are not met. Please fix the issues below before proceeding.
            </div>
            @endif

            <div class="space-y-2 mb-8">
                @foreach($checks as $check)
                <div class="flex items-center justify-between px-4 py-3 rounded-xl {{ $check['ok'] ? 'bg-emerald-50' : 'bg-red-50' }}">
                    <div class="flex items-center space-x-3">
                        @if($check['ok'])
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        @endif
                        <span class="text-sm font-medium {{ $check['ok'] ? 'text-emerald-800' : 'text-red-800' }}">{{ $check['name'] }}</span>
                    </div>
                    <span class="text-xs font-mono {{ $check['ok'] ? 'text-emerald-600' : 'text-red-600' }}">{{ $check['value'] }}</span>
                </div>
                @endforeach
            </div>

            @if($allOk)
            <a href="/install/database"
               class="flex items-center justify-center space-x-2 w-full py-3.5 rounded-2xl text-white font-bold text-sm transition-all hover:-translate-y-0.5"
               style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 6px 20px rgba(99,102,241,.3);">
                <span>Continue to Database Setup</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            @else
            <a href="/install"
               class="flex items-center justify-center space-x-2 w-full py-3.5 rounded-2xl font-bold text-sm transition-all"
               style="background:#f1f5f9; color:#475569;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Re-check Requirements</span>
            </a>
            @endif
        </div>
        @endif

        {{-- ══ STEP 2: Database ══ --}}
        @if($step === 2)
        <div class="px-8 pt-8 pb-6 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Database Configuration</h2>
                    <p class="text-sm text-slate-500">Enter your MySQL database credentials</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
            @endif

            <form action="/install/database" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Database Host</label>
                        <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Port</label>
                        <input type="number" name="db_port" value="{{ old('db_port', 3306) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Database Name</label>
                    <input type="text" name="db_name" value="{{ old('db_name') }}" required placeholder="ryaancms"
                           class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Username</label>
                        <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
                        <input type="password" name="db_password" value="{{ old('db_password') }}" placeholder="Leave blank if none"
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                </div>

                <div class="pt-2 flex items-center space-x-3">
                    <a href="/install" class="px-5 py-3 rounded-2xl text-sm font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">Back</a>
                    <button type="submit"
                            class="flex-1 flex items-center justify-center space-x-2 py-3.5 rounded-2xl text-white font-bold text-sm transition-all hover:-translate-y-0.5"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 6px 20px rgba(99,102,241,.3);">
                        <span>Test & Save Connection</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- ══ STEP 3: Migrate ══ --}}
        @if($step === 3)
        <div class="px-8 pt-8 pb-6 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#fff7ed,#fef3c7);">
                    <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Run Database Migrations</h2>
                    <p class="text-sm text-slate-500">Create all required tables in your database</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
            @endif

            <div class="space-y-3 mb-8">
                @foreach(['users', 'projects', 'project_files', 'ai_conversations', 'ai_messages', 'ai_providers', 'marketplace_items', 'marketplace_installations', 'settings', 'deployments'] as $tbl)
                <div class="flex items-center space-x-3 px-4 py-2.5 rounded-xl bg-slate-50">
                    <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                    <span class="text-sm font-mono text-slate-600">{{ $tbl }}</span>
                </div>
                @endforeach
            </div>

            <form action="/install/migrate" method="POST" class="flex items-center space-x-3">
                @csrf
                <a href="/install/database" class="px-5 py-3 rounded-2xl text-sm font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">Back</a>
                <button type="submit"
                        class="flex-1 flex items-center justify-center space-x-2 py-3.5 rounded-2xl text-white font-bold text-sm transition-all hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,#f59e0b,#d97706); box-shadow:0 6px 20px rgba(245,158,11,.3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Run Migrations Now</span>
                </button>
            </form>
        </div>
        @endif

        {{-- ══ STEP 4: Admin Account ══ --}}
        @if($step === 4)
        <div class="px-8 pt-8 pb-6 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#fdf4ff,#fce7f3);">
                    <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Create Administrator Account</h2>
                    <p class="text-sm text-slate-500">This will be your main admin login</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
            @endif

            <form action="/install/admin" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Application Name</label>
                        <input type="text" name="app_name" value="{{ old('app_name', 'RyaanCMS') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Application URL</label>
                        <input type="url" name="app_url" value="{{ old('app_url', request()->root()) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Admin Credentials</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Administrator"
                           class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@yourdomain.com"
                           class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
                        <input type="password" name="password" required minlength="8" placeholder="Min 8 characters"
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Repeat password"
                               class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50 focus:bg-white transition-all text-slate-800">
                    </div>
                </div>

                <div class="pt-2 flex items-center space-x-3">
                    <a href="/install/migrate" class="px-5 py-3 rounded-2xl text-sm font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">Back</a>
                    <button type="submit"
                            class="flex-1 flex items-center justify-center space-x-2 py-3.5 rounded-2xl text-white font-bold text-sm transition-all hover:-translate-y-0.5"
                            style="background:linear-gradient(135deg,#a855f7,#8b5cf6); box-shadow:0 6px 20px rgba(168,85,247,.3);">
                        <span>Create Account & Finish</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- ══ STEP 5: Done ══ --}}
        @if($step === 5)
        <div class="p-12 text-center">
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-6"
                 style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);">
                <svg class="w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Installation Complete!</h2>
            <p class="text-slate-500 mb-8">RyaanCMS has been installed successfully. You can now log in with your admin account.</p>

            <div class="space-y-3 max-w-xs mx-auto">
                <a href="{{ route('login') }}"
                   class="flex items-center justify-center space-x-2 w-full py-3.5 rounded-2xl text-white font-bold text-sm transition-all hover:-translate-y-0.5"
                   style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 6px 20px rgba(99,102,241,.3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Go to Login</span>
                </a>

                <div class="px-4 py-3 rounded-xl text-left" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <p class="text-xs font-semibold text-slate-500 mb-2">Quick Start</p>
                    <ul class="text-xs text-slate-500 space-y-1.5">
                        <li class="flex items-start gap-1.5"><span class="text-indigo-400 font-bold flex-shrink-0">1.</span> Log in and create your first project</li>
                        <li class="flex items-start gap-1.5"><span class="text-indigo-400 font-bold flex-shrink-0">2.</span> When ready to use AI, go to <strong class="text-slate-600">Settings → AI Providers</strong> and add an API key</li>
                        <li class="flex items-start gap-1.5"><span class="text-indigo-400 font-bold flex-shrink-0">3.</span> Start building — the AI pipeline, blueprint tools, and marketplace are all ready</li>
                    </ul>
                    <p class="text-xs text-emerald-600 font-medium mt-2.5">✓ No API key required to get started</p>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- ryaancms-powered-attribution -->
    <p class="text-center mt-6 text-slate-400 text-xs">
        RyaanCMS &copy; {{ date('Y') }} — AI-Powered Application Operating System
    </p>
    <div class="text-center mt-1">
        <a href="https://ryaancms.com" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#94a3b8;text-decoration:none;">
            ⚡ Powered by <span style="color:#6366f1;font-weight:700;">RyaanCMS</span>
        </a>
    </div>
</div>

</body>
</html>
