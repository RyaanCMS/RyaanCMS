@php
    $brandColor = \App\Models\Setting::get('branding.primary_color', '#6366f1');
    $fontFamily = \App\Models\Setting::get('branding.font_family',   'Inter');
    $fontSlug   = strtolower(str_replace(' ', '+', $fontFamily));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ $fontSlug }}:300,400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand:       {{ $brandColor }};
            --brand-dark:  color-mix(in srgb, {{ $brandColor }} 80%, #000);
            --brand-light: color-mix(in srgb, {{ $brandColor }} 10%, #fff);
            --brand-ring:  color-mix(in srgb, {{ $brandColor }} 25%, transparent);
        }
        body { font-family: '{{ $fontFamily }}', 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body class="antialiased min-h-screen flex" style="background:#f8fafc;">

    <!-- ── Left Panel ─────────────────────────────────────── -->
    <div class="hidden lg:flex lg:w-[52%] xl:w-[55%] relative flex-col justify-between p-12 overflow-hidden"
         style="background:linear-gradient(145deg,#1e1b4b 0%,#312e81 30%,#4338ca 65%,#6d28d9 100%);">

        <!-- Grid overlay -->
        <div class="absolute inset-0 opacity-[0.04]"
             style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);
                    background-size:48px 48px;"></div>

        <!-- Glow orbs -->
        <div class="absolute top-0 left-0 w-96 h-96 rounded-full blur-3xl pointer-events-none"
             style="background:rgba(139,92,246,.25);transform:translate(-30%,-30%)"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 rounded-full blur-3xl pointer-events-none"
             style="background:rgba(99,102,241,.2);transform:translate(25%,25%)"></div>

        <!-- Top: Logo -->
        <div class="relative">
            <a href="/" class="inline-flex items-center space-x-3">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-lg"
                     style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.2); backdrop-filter:blur(8px);">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-xl font-extrabold text-white tracking-tight">{{ config('app.name', 'RyaanCMS') }}</span>
            </a>
        </div>

        <!-- Center: Main copy -->
        <div class="relative space-y-8">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full mb-6"
                     style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15);">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-semibold text-indigo-200">AI-Powered Application Builder</span>
                </div>
                <h2 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-4">
                    Build Complete Apps<br>
                    <span style="background:linear-gradient(135deg,#a5b4fc,#e879f9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                        With One Prompt
                    </span>
                </h2>
                <p class="text-indigo-200 text-base leading-relaxed max-w-sm">
                    Describe your idea. RyaanCMS AI generates the full application — backend, frontend, database, APIs. Ready to deploy.
                </p>
            </div>

            <!-- Feature bullets -->
            <div class="space-y-3">
                @foreach([
                    ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z',             'text'=>'Generate complete apps from plain text'],
                    ['icon'=>'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'text'=>'Claude, GPT-4o, Gemini & more AI models'],
                    ['icon'=>'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'text'=>'Self-hosted — own your code and data forever'],
                    ['icon'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'text'=>'Plugin marketplace with 50+ ready-made modules'],
                ] as $f)
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.15);">
                        <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-sm text-indigo-100">{{ $f['text'] }}</span>
                </div>
                @endforeach
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-3 gap-4 pt-2">
                @foreach([['5+','AI Models'],['∞','Projects'],['Free','Forever']] as $s)
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-white">{{ $s[0] }}</div>
                    <div class="text-xs text-indigo-300 font-medium">{{ $s[1] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Bottom: Testimonial -->
        <div class="relative">
            <div class="p-5 rounded-2xl" style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); backdrop-filter:blur(8px);">
                <div class="flex items-center space-x-1 mb-3">
                    @for($i=0;$i<5;$i++)
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="text-indigo-100 text-sm leading-relaxed italic mb-4">
                    "I built a complete pharmacy management system for my client in 3 hours. This would have taken 2 weeks before."
                </p>
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white"
                         style="background:linear-gradient(135deg,#a855f7,#6366f1);">R</div>
                    <div>
                        <p class="text-sm font-semibold text-white">Rahim Uddin</p>
                        <p class="text-xs text-indigo-300">Freelance Developer, Bangladesh</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Panel ─────────────────────────────────────── -->
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 relative min-h-screen">

        <!-- Mobile logo -->
        <div class="lg:hidden mb-8 text-center">
            <a href="/" class="inline-flex items-center space-x-2.5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background:var(--brand);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-xl font-extrabold text-slate-800">{{ config('app.name', 'RyaanCMS') }}</span>
            </a>
        </div>

        <!-- Back to home -->
        <a href="{{ route('home') }}"
           class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-indigo-600 transition-colors mb-6 self-start"
           style="margin-left: calc(50% - 11rem);">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to home
        </a>

        <!-- Form card -->
        <div class="w-full max-w-sm">
            @yield('content')
        </div>

        <p class="mt-8 text-center text-slate-400 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name', 'RyaanCMS') }}. All rights reserved.
        </p>
        <!-- ryaancms-powered-attribution -->
        <div class="mt-3 text-center">
            <a href="https://ryaancms.com" target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#94a3b8;text-decoration:none;letter-spacing:.02em;">
                ⚡ Powered by <span style="color:var(--brand);font-weight:700;">RyaanCMS</span> <span style="opacity:.5;font-size:.65rem;">v{{ config('ryaan.version', '1.0.0') }}</span>
            </a>
        </div>
    </div>

</body>
</html>
