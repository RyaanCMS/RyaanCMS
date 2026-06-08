@extends('layouts.app')
@section('title', 'Marketplace')
@section('header', 'Marketplace')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('marketplace.my-items') }}"
       class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all"
       style="border:1px solid var(--border); color:var(--text-2); background:var(--card-bg);"
       onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-bg)'">
        My Items
    </a>
    <a href="{{ route('marketplace.upload-install') }}"
       class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
       style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Upload Package
    </a>
</div>
@endsection

@section('content')
<div x-data="marketplace()" x-init="init()">

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="mb-6 rounded-2xl overflow-hidden"
     style="background:linear-gradient(135deg,#eef2ff 0%,#fdf4ff 50%,#ecfdf5 100%); border:1px solid var(--border); box-shadow:var(--shadow);">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold mb-1" style="color:var(--text-1);">RyaanCMS Module Store</h2>
            <p class="text-sm mb-2" style="color:var(--text-2);">Install production-ready modules in one click. Zero AI tokens for built-in modules.</p>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                      style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;">
                    🧩 {{ count($modules) }} modules
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                      style="background:#fdf4ff; color:#6d28d9; border:1px solid #e9d5ff;">
                    🤖 {{ count($agents) }} agents
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                      style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">
                    ⚡ 0 tokens for CRUD
                </span>
            </div>
        </div>
        <form method="GET" action="{{ route('marketplace.index') }}" class="flex gap-2 w-full md:w-80">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="flex-1 rounded-xl px-3.5 py-2 text-sm outline-none transition-all"
                   style="background:var(--card-bg); border:1.5px solid var(--border); color:var(--text-1);"
                   placeholder="Search marketplace..."
                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
            <button type="submit"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                    style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                Search
            </button>
        </form>
    </div>
</div>

{{-- ── Tab Navigation ──────────────────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-6 p-1 rounded-xl w-fit" style="background:var(--card-sub); border:1px solid var(--border);">
    @foreach([
        ['key'=>'modules',     'label'=>'🧩 Modules'],
        ['key'=>'agents',      'label'=>'🤖 AI Agents'],
        ['key'=>'marketplace', 'label'=>'🔌 Plugins & Templates'],
    ] as $tab)
    <button @click="tab = '{{ $tab['key'] }}'"
            :style="tab === '{{ $tab['key'] }}'
                ? 'background:var(--brand); color:#fff; box-shadow:0 2px 8px var(--brand-ring);'
                : 'background:none; color:var(--text-2);'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
            onmouseover="if(this.style.background === 'none' || !this.style.background) this.style.background='var(--hover-bg)'"
            onmouseout="">
        {{ $tab['label'] }}
    </button>
    @endforeach
</div>

{{-- ════════════════════════════════════════════
     TAB: BUILT-IN MODULES
════════════════════════════════════════════ --}}
<div x-show="tab === 'modules'" x-cloak>

    @php
        $popular = collect($modules)->filter(fn($m) => $m['popular'] ?? false)->values();
    @endphp

    @if($popular->count())
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold" style="color:var(--text-1);">Popular Modules</h3>
            <span class="text-xs" style="color:var(--text-3);">All 0 AI tokens — pure PHP generation</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($modules as $key => $module)
            @if($module['popular'] ?? false)
            <div class="rounded-2xl p-5 transition-all hover:-translate-y-0.5 group"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);"
                 onmouseover="this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='#c7d2fe';"
                 onmouseout="this.style.boxShadow='var(--shadow)'; this.style.borderColor='var(--border)';">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
                         style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                        {{ $module['icon'] }}
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full"
                              style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">Free</span>
                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full"
                              style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;">0 tokens</span>
                    </div>
                </div>
                <h4 class="font-semibold text-sm mb-1 transition-colors group-hover:text-indigo-600"
                    style="color:var(--text-1);">{{ $module['name'] }}</h4>
                <p class="text-xs leading-relaxed mb-3" style="color:var(--text-3);">{{ $module['description'] }}</p>
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach(array_slice($module['features'], 0, 3) as $feature)
                    <span class="px-1.5 py-0.5 text-[10px] rounded-md"
                          style="background:var(--card-sub); color:var(--text-3); border:1px solid var(--border);">{{ str_replace('_',' ',$feature) }}</span>
                    @endforeach
                </div>
                @if($installedKeys->contains($key))
                <div class="w-full py-2 rounded-xl text-xs font-semibold text-center"
                     style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">✓ Installed</div>
                @else
                <button @click="openInstallModal('{{ $key }}', '{{ addslashes($module['name']) }}')"
                        class="w-full py-2 rounded-xl text-xs font-semibold text-white transition-all hover:-translate-y-px"
                        style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                    Install Module
                </button>
                @endif
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    @php
        $grouped = [];
        foreach ($modules as $key => $module) {
            $grouped[$module['category']][] = array_merge($module, ['key' => $key]);
        }
        ksort($grouped);
        $categoryLabels = [
            'core'=>'⚙️ Core','billing'=>'💰 Billing','communication'=>'💬 Communication',
            'analytics'=>'📊 Analytics','business'=>'🏪 Business','ecommerce'=>'🛒 eCommerce',
            'compliance'=>'📋 Compliance','integration'=>'🔌 Integration','utility'=>'🛠️ Utility','saas'=>'🏢 SaaS',
        ];
    @endphp

    @foreach($grouped as $category => $mods)
    <div class="mb-7">
        <p class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:var(--text-3);">
            {{ $categoryLabels[$category] ?? ucfirst($category) }}
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($mods as $mod)
            <div class="rounded-xl p-4 transition-all flex items-start gap-3 group cursor-default"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow-sm);"
                 onmouseover="this.style.boxShadow='var(--shadow)'; this.style.borderColor='#e0e7ff';"
                 onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.borderColor='var(--border)';">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                     style="background:var(--card-sub); border:1px solid var(--border);">
                    {{ $mod['icon'] }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-0.5">
                        <h4 class="font-medium text-sm transition-colors group-hover:text-indigo-600"
                            style="color:var(--text-1);">{{ $mod['name'] }}</h4>
                        @if(!($mod['free'] ?? true))
                        <span class="px-1.5 py-0.5 text-[10px] rounded-full flex-shrink-0"
                              style="background:#fffbeb; color:#92400e; border:1px solid #fde68a;">Pro</span>
                        @endif
                    </div>
                    <p class="text-xs leading-relaxed mb-2" style="color:var(--text-3);">{{ $mod['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <div class="flex gap-1">
                            @foreach(array_slice($mod['generates'], 0, 3) as $gen)
                            <span class="px-1.5 py-0.5 text-[10px] rounded"
                                  style="background:var(--card-sub); color:var(--text-3);">{{ $gen }}</span>
                            @endforeach
                        </div>
                        @if($installedKeys->contains($mod['key']))
                        <span class="text-xs font-semibold" style="color:#16a34a;">✓ Installed</span>
                        @else
                        <button @click="openInstallModal('{{ $mod['key'] }}', '{{ addslashes($mod['name']) }}')"
                                class="text-xs font-semibold transition-colors"
                                style="color:var(--brand);"
                                onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                            Install →
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- ════════════════════════════════════════════
     TAB: AI AGENTS
════════════════════════════════════════════ --}}
<div x-show="tab === 'agents'" x-cloak>
    <div class="mb-5 rounded-2xl p-5"
         style="background:linear-gradient(135deg,#fdf4ff,#ede9fe); border:1px solid #e9d5ff; box-shadow:var(--shadow);">
        <h3 class="text-sm font-bold mb-1" style="color:#3b0764;">AI Agents</h3>
        <p class="text-sm" style="color:#6d28d9;">Specialized AI agents that enhance your builder — each focused on one job, using the cheapest model that can do it.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($agents as $key => $agent)
        @php
            $tierStyle = match($agent['model_tier'] ?? 'free') {
                'free'    => ['bg'=>'#ecfdf5','txt'=>'#065f46','bd'=>'#a7f3d0'],
                'cheap'   => ['bg'=>'#eff6ff','txt'=>'#1d4ed8','bd'=>'#bfdbfe'],
                'premium' => ['bg'=>'#fdf4ff','txt'=>'#6d28d9','bd'=>'#e9d5ff'],
                default   => ['bg'=>'#f9fafb','txt'=>'#374151','bd'=>'#e5e7eb'],
            };
        @endphp
        <div class="rounded-2xl p-5 transition-all hover:-translate-y-0.5 group"
             style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);"
             onmouseover="this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='#e9d5ff';"
             onmouseout="this.style.boxShadow='var(--shadow)'; this.style.borderColor='var(--border)';">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
                     style="background:linear-gradient(135deg,#fdf4ff,#ede9fe); border:1px solid #e9d5ff;">
                    {{ $agent['icon'] }}
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full capitalize"
                          style="background:{{ $tierStyle['bg'] }}; color:{{ $tierStyle['txt'] }}; border:1px solid {{ $tierStyle['bd'] }};">
                        {{ $agent['model_tier'] }} model
                    </span>
                    @if($agent['free'])
                    <span class="px-2 py-0.5 text-[10px] rounded-full" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">Free</span>
                    @endif
                </div>
            </div>
            <h4 class="font-semibold text-sm mb-1 transition-colors group-hover:text-purple-600"
                style="color:var(--text-1);">{{ $agent['name'] }}</h4>
            <p class="text-xs leading-relaxed mb-4" style="color:var(--text-3);">{{ $agent['description'] }}</p>
            <button @click="openInstallModal('agent:{{ $key }}', '{{ addslashes($agent['name']) }}')"
                    class="w-full py-2 rounded-xl text-xs font-semibold text-white transition-all hover:-translate-y-px"
                    style="background:linear-gradient(135deg,#8b5cf6,#a855f7); box-shadow:0 2px 8px rgba(139,92,246,.3);">
                Activate Agent
            </button>
        </div>
        @endforeach
    </div>
</div>

{{-- ════════════════════════════════════════════
     TAB: PLUGINS & TEMPLATES
════════════════════════════════════════════ --}}
<div x-show="tab === 'marketplace'" x-cloak>

    @if($featured->isNotEmpty() && !request('search'))
    <div class="mb-7">
        <h3 class="text-sm font-bold mb-4" style="color:var(--text-1);">Featured</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($featured as $item)
            <a href="{{ route('marketplace.show', $item) }}"
               class="block rounded-2xl p-5 transition-all hover:-translate-y-0.5 group"
               style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);"
               onmouseover="this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='#c7d2fe';"
               onmouseout="this.style.boxShadow='var(--shadow)'; this.style.borderColor='var(--border)';">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                         style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                        @switch($item->type)
                            @case('module') 📦 @break @case('theme') 🎨 @break
                            @case('agent')  🤖 @break @case('template') 📄 @break @default 🔌
                        @endswitch
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full"
                          style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;">{{ $item->category }}</span>
                </div>
                <h4 class="font-semibold text-sm mb-1 transition-colors group-hover:text-indigo-600"
                    style="color:var(--text-1);">{{ $item->name }}</h4>
                <p class="text-xs line-clamp-2 mb-3" style="color:var(--text-3);">{{ $item->description }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold" style="color:{{ $item->is_free ? '#16a34a' : 'var(--text-1)' }};">{{ $item->price_formatted }}</span>
                    <span class="text-xs" style="color:var(--text-3);">↓ {{ number_format($item->downloads) }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Category pills --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('marketplace.index', request()->except('category')) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors"
           style="{{ !request('category') ? 'background:var(--brand); color:#fff; box-shadow:0 2px 8px var(--brand-ring);' : 'background:var(--card-sub); color:var(--text-2); border:1px solid var(--border);' }}">
            All
        </a>
        @foreach($categories as $key => $label)
        <a href="{{ route('marketplace.index', array_merge(request()->all(), ['category' => $key])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors"
           style="{{ request('category') === $key ? 'background:var(--brand); color:#fff; box-shadow:0 2px 8px var(--brand-ring);' : 'background:var(--card-sub); color:var(--text-2); border:1px solid var(--border);' }}"
           onmouseover="if('{{ request('category') }}' !== '{{ $key }}') this.style.background='var(--hover-bg)'"
           onmouseout="if('{{ request('category') }}' !== '{{ $key }}') this.style.background='var(--card-sub)'">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($items as $item)
        <a href="{{ route('marketplace.show', $item) }}"
           class="block rounded-2xl p-5 transition-all hover:-translate-y-0.5 group"
           style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);"
           onmouseover="this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='#c7d2fe';"
           onmouseout="this.style.boxShadow='var(--shadow)'; this.style.borderColor='var(--border)';">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                     style="background:var(--card-sub); border:1px solid var(--border);">
                    {{ $item->icon ?? match($item->type) { 'module'=>'📦','theme'=>'🎨','agent'=>'🤖','template'=>'📄', default=>'🔌' } }}
                </div>
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full"
                      style="{{ $item->is_free ? 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;' : 'background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;' }}">
                    {{ $item->price_formatted }}
                </span>
            </div>
            <h4 class="font-semibold text-sm mb-1 transition-colors group-hover:text-indigo-600"
                style="color:var(--text-1);">{{ $item->name }}</h4>
            <p class="text-xs line-clamp-2 mb-3" style="color:var(--text-3);">{{ $item->description }}</p>
            <div class="flex items-center justify-between text-[10px]" style="color:var(--text-3);">
                <span>{{ $item->category }}</span>
                <span>v{{ $item->version }}</span>
            </div>
        </a>
        @empty
        <div class="col-span-4 flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3"
                 style="background:var(--card-sub); border:1px solid var(--border);">
                <svg class="w-7 h-7" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p class="text-sm font-medium mb-1" style="color:var(--text-2);">No items found</p>
            <p class="text-xs" style="color:var(--text-3);">Try a different search or category</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->withQueryString()->links() }}</div>
</div>

{{-- ════════════════════════════════════════════
     INSTALL MODAL
════════════════════════════════════════════ --}}
<div x-show="modalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="closeModal()">
    <div class="absolute inset-0 backdrop-blur-sm" style="background:rgba(0,0,0,.4);" @click="closeModal()"></div>
    <div class="relative w-full max-w-md rounded-2xl p-6 overflow-hidden"
         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow-lg);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <button @click="closeModal()"
                class="absolute top-4 right-4 w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                style="color:var(--text-3);"
                onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h3 class="text-base font-bold mb-0.5" style="color:var(--text-1);" x-text="'Install ' + modalModuleName"></h3>
        <p class="text-sm mb-5" style="color:var(--text-3);">Select a project to install this module into</p>

        {{-- Success --}}
        <div x-show="installSuccess" class="space-y-4">
            <div class="rounded-xl p-4 text-center" style="background:#ecfdf5; border:1px solid #a7f3d0;">
                <p class="text-2xl mb-2">✅</p>
                <p class="text-sm font-semibold" style="color:#065f46;" x-text="successMessage"></p>
                <p class="text-xs mt-1" style="color:#16a34a;">0 AI tokens used — pure PHP generation</p>
            </div>
            <div x-show="menuItems.length > 0" class="rounded-xl p-4" style="background:var(--card-sub); border:1px solid var(--border);">
                <p class="text-xs font-semibold mb-2" style="color:var(--text-2);">📌 Add these to your navigation menu:</p>
                <div class="space-y-2">
                    <template x-for="item in menuItems" :key="item.route">
                        <div class="flex items-center justify-between rounded-lg px-3 py-2" style="background:var(--card-bg); border:1px solid var(--border);">
                            <div class="flex items-center gap-2">
                                <span x-text="item.icon" class="text-base"></span>
                                <div>
                                    <span class="text-sm font-medium" style="color:var(--text-1);" x-text="item.label"></span>
                                    <span class="text-xs ml-2" style="color:var(--text-3);" x-text="'Group: ' + item.group"></span>
                                </div>
                            </div>
                            <code class="text-xs px-2 py-0.5 rounded font-mono" style="background:var(--card-sub); color:var(--brand);" x-text="item.route"></code>
                        </div>
                    </template>
                </div>
                <p class="text-xs mt-3" style="color:var(--text-3);">
                    Go to <a href="{{ route('menus.index') }}" class="font-semibold underline" style="color:var(--brand);">Menu Management</a> to add these routes.
                </p>
            </div>
            <button @click="closeModal()"
                    class="w-full py-2 rounded-xl text-sm font-medium transition-colors"
                    style="background:var(--card-sub); border:1px solid var(--border); color:var(--text-2);"
                    onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-sub)'">
                Close
            </button>
        </div>

        {{-- Error --}}
        <div x-show="installError"
             class="mb-4 p-3 rounded-xl text-sm" style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b;"
             x-text="errorMessage"></div>

        {{-- Install form --}}
        <div x-show="!installSuccess">
            @if($projects->count())
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--text-3);">Select Project</label>
                    <select x-model="selectedProject"
                            class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                            style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1); appearance:none; cursor:pointer;"
                            onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        <option value="">— Choose a project —</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button @click="doInstall()"
                        :disabled="!selectedProject || installing"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-all flex items-center justify-center gap-2"
                        :style="(!selectedProject || installing) ? 'opacity:.5; cursor:not-allowed; background:var(--brand);' : 'background:var(--brand); box-shadow:0 2px 8px var(--brand-ring); cursor:pointer;'">
                    <span x-show="!installing">Install Module — 0 AI Tokens</span>
                    <span x-show="installing" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Installing...
                    </span>
                </button>
            </div>
            @else
            <div class="text-center py-6">
                <p class="text-sm mb-3" style="color:var(--text-2);">You don't have any projects yet.</p>
                <a href="{{ route('projects.create') }}"
                   class="inline-block px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                   style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                    Create a project
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

<script>
function marketplace() {
    return {
        tab: 'modules',
        modalOpen: false,
        modalModuleKey: '',
        modalModuleName: '',
        selectedProject: '',
        installing: false,
        installSuccess: false,
        installError: false,
        successMessage: '',
        errorMessage: '',
        menuItems: [],

        init() {
            if (new URLSearchParams(window.location.search).has('search') ||
                new URLSearchParams(window.location.search).has('category')) {
                this.tab = 'marketplace';
            }
        },

        openInstallModal(key, name) {
            this.modalModuleKey  = key;
            this.modalModuleName = name;
            this.selectedProject = '';
            this.installing      = false;
            this.installSuccess  = false;
            this.installError    = false;
            this.successMessage  = '';
            this.errorMessage    = '';
            this.modalOpen       = true;
        },

        closeModal() { this.modalOpen = false; },

        async doInstall() {
            if (!this.selectedProject || this.installing) return;
            this.installing   = true;
            this.installError = false;
            try {
                const res = await fetch(`/marketplace/modules/${this.modalModuleKey}/install`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ project_id: this.selectedProject }),
                });
                const data = await res.json();
                if (data.success) {
                    this.installSuccess = true;
                    this.successMessage = data.message || 'Module installed successfully!';
                    this.menuItems      = data.menu_items || [];
                } else {
                    this.installError = true;
                    this.errorMessage = data.message || 'Installation failed.';
                }
            } catch (err) {
                this.installError = true;
                this.errorMessage = 'Network error — please try again.';
            } finally {
                this.installing = false;
            }
        },
    };
}
</script>

@endsection
