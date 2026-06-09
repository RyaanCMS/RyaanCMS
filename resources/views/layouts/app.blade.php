<!DOCTYPE html>
@php
    $userId      = auth()->id();
    $brandColor  = \App\Models\Setting::get('branding.primary_color', '#6366f1', $userId);
    $fontFamily  = \App\Models\Setting::get('branding.font_family',   'Poppins', $userId);
    $logoPath    = \App\Models\Setting::get('branding.logo_path',     null,      $userId);
    $faviconPath = \App\Models\Setting::get('branding.favicon_path',  null,      $userId);
    $fontSlug    = strtolower(str_replace(' ', '+', $fontFamily));
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="appLayout()">
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
        /* ─── Theme (always light / white) ──────────────────── */
        :root {
            --page-bg:    #ffffff;
            --card-bg:    #ffffff;
            --card-sub:   #f8fafc;
            --border:     #e5e7eb;
            --sidebar-bg: #ffffff;
            --header-bg:  #ffffff;
            --text-1:     #111827;
            --text-2:     #374151;
            --text-3:     #9ca3af;
            --hover-bg:   #f9fafb;
            --input-bg:   #ffffff;
            --shadow-sm:  0 1px 2px rgba(0,0,0,.04);
            --shadow:     0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.03);
            --shadow-md:  0 4px 12px rgba(0,0,0,.06);
            --shadow-lg:  0 8px 24px rgba(0,0,0,.08);

            --brand:       {{ $brandColor }};
            --brand-dark:  color-mix(in srgb, {{ $brandColor }} 80%, #000);
            --brand-light: color-mix(in srgb, {{ $brandColor }} 10%, transparent);
            --brand-ring:  color-mix(in srgb, {{ $brandColor }} 25%, transparent);
        }

        /* ─── Base Styles ────────────────────────────────────── */
        body {
            font-family: '{{ $fontFamily }}', 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-1);
        }

        /* ─── Form Elements ──────────────────────────────────── */
        input, select, textarea {
            background: var(--input-bg) !important;
            border-color: var(--border) !important;
            color: var(--text-1) !important;
        }
        input::placeholder, textarea::placeholder { color: var(--text-3) !important; }

        /* ─── Scrollbar ──────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

        /* ─── Smooth Transitions ─────────────────────────────── */
        *, *::before, *::after {
            transition: background-color 0.2s ease, border-color 0.15s ease, color 0.1s ease;
        }

        /* ═══════════════════════════════════════════════════════
           ALWAYS LIGHT — Override dark Tailwind utility classes
        ═══════════════════════════════════════════════════════ */

        /* ── Backgrounds ──────────────────────────────────────── */
        .bg-gray-950, .bg-gray-900 { background-color: #ffffff !important; }
        .bg-gray-800               { background-color: #f9fafb !important; }
        .bg-gray-700               { background-color: #f3f4f6 !important; }
        .bg-gray-600               { background-color: #e5e7eb !important; }

        /* opacity variants */
        .bg-gray-950\/50,.bg-gray-950\/90,.bg-gray-950\/95 { background-color: rgba(255,255,255,.95) !important; }
        .bg-gray-900\/50 { background-color: rgba(255,255,255,.5)  !important; }
        .bg-gray-900\/70 { background-color: rgba(255,255,255,.7)  !important; }
        .bg-gray-900\/90,.bg-gray-900\/95 { background-color: rgba(255,255,255,.95) !important; }
        .bg-gray-800\/20 { background-color: rgba(249,250,251,.2)  !important; }
        .bg-gray-800\/40 { background-color: rgba(249,250,251,.4)  !important; }
        .bg-gray-800\/60 { background-color: rgba(249,250,251,.6)  !important; }
        .bg-gray-800\/80 { background-color: rgba(249,250,251,.8)  !important; }
        .bg-gray-700\/60 { background-color: rgba(243,244,246,.6)  !important; }
        .bg-gray-950\/10,.bg-gray-950\/20,.bg-gray-950\/30,.bg-gray-950\/40,
        .bg-gray-950\/60,.bg-gray-950\/70,.bg-gray-950\/80 { background-color: rgba(249,250,251,.9) !important; }
        .bg-gray-900\/10,.bg-gray-900\/20,.bg-gray-900\/30,.bg-gray-900\/40,
        .bg-gray-900\/60,.bg-gray-900\/80 { background-color: rgba(249,250,251,.7) !important; }
        .bg-gray-800\/10,.bg-gray-800\/30,.bg-gray-800\/50,.bg-gray-800\/70,
        .bg-gray-800\/90 { background-color: rgba(249,250,251,.8) !important; }
        .bg-gray-700\/20,.bg-gray-700\/30,.bg-gray-700\/40,.bg-gray-700\/50,
        .bg-gray-700\/80 { background-color: rgba(243,244,246,.6) !important; }

        /* ── Hover Backgrounds ────────────────────────────────── */
        .hover\:bg-gray-950:hover,.hover\:bg-gray-900:hover,
        .hover\:bg-gray-800:hover,.hover\:bg-gray-750:hover { background-color: #f3f4f6 !important; }
        .hover\:bg-gray-700:hover { background-color: #e5e7eb !important; }

        /* ── Text Colors ──────────────────────────────────────── */
        .text-white    { color: #111827 !important; }
        .text-gray-100 { color: #1f2937 !important; }
        .text-gray-200 { color: #374151 !important; }
        .text-gray-300 { color: #4b5563 !important; }
        .text-gray-400 { color: #6b7280 !important; }
        .text-gray-500 { color: #6b7280 !important; }
        .text-gray-600 { color: #4b5563 !important; }

        .hover\:text-white:hover    { color: #111827 !important; }
        .hover\:text-gray-100:hover { color: #1f2937 !important; }
        .hover\:text-gray-200:hover { color: #374151 !important; }
        .hover\:text-gray-300:hover { color: #4b5563 !important; }

        /* ── Borders ──────────────────────────────────────────── */
        .border-gray-800    { border-color: #e5e7eb !important; }
        .border-gray-700    { border-color: #e5e7eb !important; }
        .border-gray-600    { border-color: #d1d5db !important; }
        .border-gray-800\/60,.border-gray-700\/60 { border-color: rgba(229,231,235,.6) !important; }
        .divide-gray-800 > * + * { border-color: #e5e7eb !important; }
        .divide-gray-700 > * + * { border-color: #e5e7eb !important; }

        /* ── Placeholders ─────────────────────────────────────── */
        .placeholder-gray-600::placeholder,
        .placeholder-gray-500::placeholder { color: #9ca3af !important; }

        /* ── Shadows ──────────────────────────────────────────── */
        .shadow-xl        { box-shadow: 0 8px 24px rgba(0,0,0,.06) !important; }
        .shadow-black\/50 { box-shadow: 0 8px 24px rgba(0,0,0,.05) !important; }
        .shadow-black\/20 { box-shadow: 0 4px 12px rgba(0,0,0,.04) !important; }

        /* ── Focus ────────────────────────────────────────────── */
        .focus\:ring-indigo-500:focus         { box-shadow: 0 0 0 2px var(--brand-ring) !important; }
        .focus\:border-indigo-500:focus       { border-color: var(--brand) !important; }
        .focus-within\:border-indigo-500:focus-within { border-color: var(--brand) !important; }
        .focus-within\:ring-1:focus-within    { box-shadow: 0 0 0 1px var(--brand-ring) !important; }

        /* ── Misc ─────────────────────────────────────────────── */
        .font-mono        { color: #374151 !important; }
        .text-indigo-300  { color: #4338ca !important; }
        .text-green-400   { color: #15803d !important; }
        .text-green-300   { color: #15803d !important; }
        .backdrop-blur-xl { background-color: rgba(255,255,255,.92) !important; }

        /* ── Builder code editor ──────────────────────────────── */
        #codeEditor {
            background-color: #f8fafc !important;
            color: #1e293b !important;
        }

        /* ════════════════════════════════════════════════════════
           POWERED BY RyaanCMS — Required attribution
           Copyright © RyaanCMS. Do not remove.
        ════════════════════════════════════════════════════════ */
        /* ryaancms-attribution-v1 */
        .ryaan-powered {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.02em;
            text-decoration: none;
        }
        .ryaan-powered:hover { color: #6366f1; }
        .ryaan-powered span  { color: #6366f1; font-weight: 700; }
    </style>

    @stack('head')
</head>
<body>

<div class="flex h-screen overflow-hidden">

    <!-- ───────────── SIDEBAR ───────────── -->
    <aside :class="(sidebarOpen || sidebarHovered) ? 'w-64' : 'w-[68px]'"
           @mouseenter="sidebarHovered = true"
           @mouseleave="sidebarHovered = false"
           class="flex-shrink-0 flex flex-col transition-all duration-200 ease-in-out z-30 overflow-hidden"
           style="background:var(--sidebar-bg); border-right:1px solid var(--border); box-shadow:var(--shadow-md);">

        <!-- Logo row -->
        <div class="flex items-center h-16 px-4 flex-shrink-0" style="border-bottom:1px solid var(--border);">
            <div class="flex items-center space-x-3 min-w-0">
                @if($logoPath)
                <img src="{{ Storage::url($logoPath) }}" alt="Logo" class="w-8 h-8 rounded-xl object-cover flex-shrink-0">
                @else
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,var(--brand),var(--brand-dark));">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                @endif
                <span x-show="sidebarOpen || sidebarHovered" x-transition:enter="transition-opacity duration-150"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      class="font-extrabold text-base truncate" style="color:var(--text-1);">
                    {{ config('app.name') }}
                </span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen"
                    class="ml-auto w-7 h-7 rounded-lg flex items-center justify-center transition-colors flex-shrink-0"
                    style="color:var(--text-3);"
                    onmouseover="this.style.background='var(--hover-bg)';this.style.color='var(--text-1)'"
                    onmouseout="this.style.background='';this.style.color='var(--text-3)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="sidebarOpen"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-1.5 space-y-0.5 overflow-y-auto overflow-x-hidden">
            @php
                // Both 'sidebar' (legacy) and 'admin_sidebar' (new) render in the sidebar
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
            @php
                $navItems = [
                    [
                        'route' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                        'from'  => '#38bdf8', 'to' => '#6366f1', 'txt' => '#0ea5e9',
                    ],
                    [
                        'route' => 'projects.index',
                        'label' => 'Projects',
                        'icon'  => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
                        'from'  => '#a78bfa', 'to' => '#f472b6', 'txt' => '#8b5cf6',
                    ],
                    [
                        'route' => 'marketplace.index',
                        'label' => 'Marketplace',
                        'icon'  => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                        'from'  => '#34d399', 'to' => '#06b6d4', 'txt' => '#10b981',
                    ],
                    [
                        'route' => 'wisdom.index',
                        'label' => 'Wisdom',
                        'icon'  => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                        'from'  => '#f59e0b', 'to' => '#fbbf24', 'txt' => '#f59e0b',
                    ],
                    [
                        'route' => 'menus.index',
                        'label' => 'Menus',
                        'icon'  => 'M4 6h16M4 12h16M4 18h7',
                        'from'  => '#f472b6', 'to' => '#fb7185', 'txt' => '#ec4899',
                    ],
                    [
                        'route' => 'settings.index',
                        'label' => 'Settings',
                        'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        'from'  => '#fb923c', 'to' => '#f59e0b', 'txt' => '#f97316',
                    ],
                ];
            @endphp

            @foreach($navItems as $item)
            @php $active = request()->routeIs(explode('.', $item['route'])[0].'*'); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center px-2 py-1 rounded-lg text-sm font-normal transition-all duration-150 group/item"
               style="{{ $active
                   ? 'background:color-mix(in srgb,'.$item['from'].' 12%,transparent);'
                   : '' }}"
               onmouseover="if(!{{ $active ? 'true' : 'false' }}) this.style.background='var(--hover-bg)';"
               onmouseout="if(!{{ $active ? 'true' : 'false' }}) this.style.background='';">
                <div class="w-7 h-7 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" style="color:{{ $item['txt'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
                    </svg>
                </div>
                <span x-show="sidebarOpen || sidebarHovered"
                      x-transition:enter="transition-opacity duration-150"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      class="ml-3 truncate whitespace-nowrap font-normal"
                      style="color:{{ $active ? $item['txt'] : 'var(--text-2)' }};">
                    {{ $item['label'] }}
                </span>
                @if($active)
                <span x-show="sidebarOpen || sidebarHovered"
                      class="ml-auto w-1.5 h-1.5 rounded-full flex-shrink-0"
                      style="background:{{ $item['txt'] }};"></span>
                @endif
            </a>
            @endforeach

            {{-- Admin Panel link (visible only to admins) --}}
            @if(Auth::user()->isAdmin())
            @php $adminActive = request()->routeIs('marketplace.admin*'); @endphp
            <div class="pt-1">
                <a href="{{ route('marketplace.admin.panel') }}"
                   class="flex items-center px-2 py-1 rounded-lg text-sm font-normal transition-all duration-150"
                   style="{{ $adminActive ? 'background:rgba(239,68,68,.1);' : '' }}"
                   onmouseover="if(!{{ $adminActive ? 'true' : 'false' }}) this.style.background='var(--hover-bg)';"
                   onmouseout="if(!{{ $adminActive ? 'true' : 'false' }}) this.style.background='';">
                    <div class="w-7 h-7 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" style="color:#ef4444" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen || sidebarHovered"
                          x-transition:enter="transition-opacity duration-150"
                          x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                          class="ml-3 truncate whitespace-nowrap font-normal"
                          style="color:{{ $adminActive ? '#ef4444' : 'var(--text-2)' }};">
                        Marketplace Admin
                    </span>
                </a>
            </div>
            @endif

            {{-- ── Admin Menu (sidebar bottom) ── --}}
            @foreach($dynamicSidebarMenus as $dynMenu)
            <div x-show="sidebarOpen || sidebarHovered" x-transition class="pt-2">
                <p class="px-2 text-[9px] font-semibold uppercase tracking-widest mb-1" style="color:var(--text-3);">
                    {{ $dynMenu->name }}
                </p>
                @foreach($dynMenu->items as $mItem)
                @php
                    // Auto-fix: if URL points to the builder editor (/builder/xxx without /preview),
                    // redirect to the preview page so the app opens full-screen instead of the AI IDE.
                    $rawUrl  = $mItem->url ?? '';
                    $urlPath = parse_url($rawUrl, PHP_URL_PATH) ?? '';
                    if ($rawUrl && preg_match('#^/builder/[^/]+$#', $urlPath)) {
                        $rawUrl = rtrim($rawUrl, '/') . '/preview';
                    }
                    $mPath   = ltrim($urlPath !== '' ? $urlPath : '', '/');
                    $mActive = $rawUrl && ($mPath ? request()->is($mPath, $mPath.'/*') : false);
                @endphp
                <a href="{{ $rawUrl ?: '#' }}" target="{{ $mItem->target ?: '_blank' }}"
                   class="flex items-center px-2 py-1 rounded-lg text-sm font-normal transition-all duration-150"
                   style="{{ $mActive ? 'background:var(--brand-light);' : '' }}"
                   onmouseover="if(!{{ $mActive ? 'true' : 'false' }}) this.style.background='var(--hover-bg)';"
                   onmouseout="if(!{{ $mActive ? 'true' : 'false' }}) this.style.background='';">
                    <div class="w-7 h-7 flex items-center justify-center flex-shrink-0">
                        @if($mItem->icon)
                        <svg class="w-4 h-4" style="color:var(--brand)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $mItem->icon }}"/>
                        </svg>
                        @else
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--brand)"></span>
                        @endif
                    </div>
                    <span x-show="sidebarOpen || sidebarHovered"
                          x-transition:enter="transition-opacity duration-150"
                          x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                          class="ml-0 truncate whitespace-nowrap font-normal"
                          style="color:{{ $mActive ? 'var(--brand)' : 'var(--text-2)' }};">
                        {{ $mItem->label }}
                    </span>
                    @if($mActive)
                    <span x-show="sidebarOpen || sidebarHovered"
                          class="ml-auto w-1.5 h-1.5 rounded-full flex-shrink-0"
                          style="background:var(--brand);"></span>
                    @endif
                </a>
                @foreach($mItem->children as $child)
                @php
                    $cRaw  = $child->url ?? '';
                    $cPath = parse_url($cRaw, PHP_URL_PATH) ?? '';
                    if ($cRaw && preg_match('#^/builder/[^/]+$#', $cPath)) {
                        $cRaw = rtrim($cRaw, '/') . '/preview';
                    }
                    $cPathTrimmed = ltrim($cPath, '/');
                    $cActive = $cRaw && ($cPathTrimmed ? request()->is($cPathTrimmed, $cPathTrimmed.'/*') : false);
                @endphp
                <a href="{{ $cRaw ?: '#' }}" target="{{ $child->target ?: '_blank' }}"
                   class="flex items-center pl-8 pr-2 py-1 rounded-lg text-xs font-normal transition-all"
                   style="{{ $cActive ? 'color:var(--brand);' : 'color:var(--text-3);' }}"
                   onmouseover="this.style.background='var(--hover-bg)'; this.style.color='var(--text-2)';"
                   onmouseout="this.style.background=''; this.style.color='{{ $cActive ? 'var(--brand)' : 'var(--text-3)' }}';">
                    <span class="w-1 h-1 rounded-full mr-2 flex-shrink-0" style="background:var(--border)"></span>
                    <span x-show="sidebarOpen || sidebarHovered" class="truncate">{{ $child->label }}</span>
                </a>
                @endforeach
                @endforeach
            </div>
            @endforeach

        </nav>

        <!-- User Profile -->
        <div class="p-2.5 flex-shrink-0" style="border-top:1px solid var(--border);" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center px-2 py-2 rounded-xl transition-colors"
                    onmouseover="this.style.background='var(--hover-bg)'"
                    onmouseout="this.style.background=''">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                     class="w-8 h-8 rounded-full flex-shrink-0 ring-2"
                     style="ring-color:var(--border);">
                <div x-show="sidebarOpen || sidebarHovered" x-transition class="ml-3 text-left min-w-0 flex-1">
                    <p class="text-sm font-semibold truncate" style="color:var(--text-1);">{{ auth()->user()->name }}</p>
                    <p class="text-xs truncate" style="color:var(--text-3);">{{ auth()->user()->email }}</p>
                </div>
                <svg x-show="sidebarOpen || sidebarHovered" class="w-3.5 h-3.5 ml-1 flex-shrink-0"
                     style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition @click.away="open = false"
                 class="absolute bottom-16 left-3 right-3 rounded-xl z-50 py-1 overflow-hidden"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow-lg);">
                <a href="{{ route('settings.index') }}"
                   class="flex items-center px-4 py-2.5 text-sm transition-colors"
                   style="color:var(--text-2);"
                   onmouseover="this.style.background='var(--hover-bg)';this.style.color='var(--text-1)';"
                   onmouseout="this.style.background='';this.style.color='var(--text-2)';">
                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile Settings
                </a>
                <div style="border-top:1px solid var(--border); margin:4px 0;"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center px-4 py-2.5 text-sm transition-colors"
                            style="color:#ef4444;"
                            onmouseover="this.style.background='#fef2f2';"
                            onmouseout="this.style.background='';">
                        <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ───────────── MAIN ───────────── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Top Bar -->
        @php
            $userTopbarMenus = auth()->check()
                ? \App\Models\Menu::where('user_id', auth()->id())
                      ->where('category', 'user_topbar')
                      ->where('is_active', true)
                      ->with(['items' => fn($q) => $q->where('is_active', true)->orderBy('order')])
                      ->get()
                : collect();
        @endphp
        <header class="h-16 flex items-center px-6 flex-shrink-0"
                style="background:var(--header-bg); border-bottom:1px solid var(--border); box-shadow:var(--shadow-sm);">
            <div class="flex items-center flex-1 min-w-0 gap-6">
                <h1 class="font-bold text-base flex-shrink-0" style="color:var(--text-1);">@yield('header', 'Dashboard')</h1>

                {{-- ── User Menu (topbar) ── --}}
                @if($userTopbarMenus->isNotEmpty())
                <nav class="flex items-center gap-1 min-w-0 overflow-x-auto" style="scrollbar-width:none;">
                    @foreach($userTopbarMenus as $topMenu)
                        @foreach($topMenu->items as $topItem)
                        @php
                            $topPath   = ltrim(parse_url($topItem->url ?? '', PHP_URL_PATH) ?? '', '/');
                            $topActive = $topItem->url && ($topPath ? request()->is($topPath, $topPath.'/*') : false);
                        @endphp
                        @if($topItem->children->isNotEmpty())
                        {{-- Dropdown item --}}
                        <div x-data="{ open: false }" class="relative flex-shrink-0">
                            <button @click="open = !open" @click.away="open = false"
                                    class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                    style="{{ $topActive ? 'background:var(--brand-light);color:var(--brand);' : 'color:var(--text-2);' }}"
                                    onmouseover="this.style.background='var(--hover-bg)'"
                                    onmouseout="this.style.background='{{ $topActive ? 'var(--brand-light)' : '' }}'">
                                @if($topItem->icon)
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $topItem->icon }}"/>
                                </svg>
                                @endif
                                <span class="whitespace-nowrap">{{ $topItem->label }}</span>
                                <svg class="w-3 h-3 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition
                                 class="absolute top-full left-0 mt-1 py-1 rounded-xl z-50 min-w-[160px]"
                                 style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-md);">
                                @foreach($topItem->children as $child)
                                <a href="{{ $child->url ?: '#' }}" target="{{ $child->target ?: '_self' }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm transition-colors"
                                   style="color:var(--text-2);"
                                   onmouseover="this.style.background='var(--hover-bg)';this.style.color='var(--text-1)';"
                                   onmouseout="this.style.background='';this.style.color='var(--text-2)';">
                                    {{ $child->label }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @else
                        {{-- Simple link --}}
                        <a href="{{ $topItem->url ?: '#' }}" target="{{ $topItem->target ?: '_self' }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all flex-shrink-0 whitespace-nowrap"
                           style="{{ $topActive ? 'background:var(--brand-light);color:var(--brand);font-weight:600;' : 'color:var(--text-2);' }}"
                           onmouseover="this.style.background='var(--hover-bg)';if(!{{ $topActive ? 'true':'false' }})this.style.color='var(--text-1)';"
                           onmouseout="this.style.background='{{ $topActive ? 'var(--brand-light)':'' }}';this.style.color='{{ $topActive ? 'var(--brand)':'var(--text-2)' }}';">
                            @if($topItem->icon)
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $topItem->icon }}"/>
                            </svg>
                            @endif
                            {{ $topItem->label }}
                            @if($topActive)
                            <span class="w-1 h-1 rounded-full flex-shrink-0" style="background:var(--brand);"></span>
                            @endif
                        </a>
                        @endif
                        @endforeach
                    @endforeach
                </nav>
                @endif
            </div>
            <div class="flex items-center space-x-1.5 flex-shrink-0">
                @yield('header-actions')

                <!-- Notifications -->
                <button class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                        style="color:var(--text-2);"
                        onmouseover="this.style.background='var(--hover-bg)';this.style.color='var(--text-1)'"
                        onmouseout="this.style.background='';this.style.color='var(--text-2)'">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>

                <!-- New Project -->
                <a href="{{ route('projects.create') }}"
                   class="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                   style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Project</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mx-6 mt-4 flex items-center p-3.5 rounded-xl text-sm"
             style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;">
            <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="mx-6 mt-4 flex items-center p-3.5 rounded-xl text-sm"
             style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b;">
            <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto @yield('main-class', 'p-6')">
            @yield('content')
        </main>
    </div>
</div>

@stack('modals')
@stack('scripts')

<script>
function appLayout() {
    return {
        sidebarOpen:    false,
        sidebarHovered: false,
        init() {
            // Always light mode — remove any saved dark preference
            localStorage.removeItem('theme');
            document.documentElement.classList.remove('dark');
        },
    }
}
</script>
</body>
</html>
