@extends('layouts.app')
@section('title', 'Marketplace')
@section('header', 'Marketplace')

@section('header-actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('marketplace.my-items') }}"
       class="flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm border border-gray-700 transition-colors">
        My Items
    </a>
    <a href="{{ route('marketplace.upload-install') }}"
       class="flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm border border-gray-700 transition-colors">
        Upload Package
    </a>
</div>
@endsection

@section('content')

<div x-data="marketplace()" x-init="init()">

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="mb-6 bg-gradient-to-r from-indigo-900/30 via-purple-900/20 to-pink-900/10 border border-indigo-500/20 rounded-2xl p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white mb-1">RyaanCMS Module Store</h2>
            <p class="text-gray-400 text-sm">Install production-ready modules in one click. Zero AI tokens for built-in modules.</p>
            <div class="flex items-center space-x-4 mt-2">
                <span class="text-xs text-gray-500">🧩 {{ count($modules) }} built-in modules</span>
                <span class="text-xs text-gray-500">🤖 {{ count($agents) }} AI agents</span>
                <span class="text-xs text-green-500">⚡ 0 AI tokens for CRUD</span>
            </div>
        </div>
        <form method="GET" action="{{ route('marketplace.index') }}" class="flex space-x-2 w-full md:w-96">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="flex-1 bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-500"
                   placeholder="Search marketplace...">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm transition-colors">
                Search
            </button>
        </form>
    </div>
</div>

{{-- ── Tab Navigation ────────────────────────────────────────────────────── --}}
<div class="flex items-center space-x-1 mb-6 bg-gray-900/60 border border-gray-800 rounded-xl p-1 w-fit">
    <button @click="tab = 'modules'"
            :class="tab === 'modules' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        🧩 Modules
    </button>
    <button @click="tab = 'agents'"
            :class="tab === 'agents' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        🤖 AI Agents
    </button>
    <button @click="tab = 'marketplace'"
            :class="tab === 'marketplace' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        🔌 Plugins & Templates
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: BUILT-IN MODULES                                                   --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'modules'" x-cloak>

    {{-- Popular modules row --}}
    @php
        $popular = collect($modules)->filter(fn($m) => $m['popular'] ?? false)->values();
    @endphp
    @if($popular->count())
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-white">Popular Modules</h3>
            <span class="text-xs text-gray-500">All 0 AI tokens — pure PHP generation</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($modules as $key => $module)
            @if($module['popular'] ?? false)
            <div class="bg-gray-900 border border-gray-800 hover:border-indigo-500/40 rounded-2xl p-5 transition-all hover:-translate-y-0.5 group">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/20 flex items-center justify-center text-2xl">
                        {{ $module['icon'] }}
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="px-2 py-0.5 text-xs bg-green-500/10 text-green-400 border border-green-500/20 rounded-full">Free</span>
                        <span class="px-2 py-0.5 text-xs bg-indigo-500/10 text-indigo-400 border border-indigo-500/10 rounded-full">0 tokens</span>
                    </div>
                </div>
                <h4 class="font-semibold text-white text-sm mb-1 group-hover:text-indigo-300 transition-colors">{{ $module['name'] }}</h4>
                <p class="text-xs text-gray-500 leading-relaxed mb-3">{{ $module['description'] }}</p>
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach(array_slice($module['features'], 0, 3) as $feature)
                    <span class="px-1.5 py-0.5 text-[10px] bg-gray-800 text-gray-500 rounded-md">{{ str_replace('_', ' ', $feature) }}</span>
                    @endforeach
                </div>
                @if($installedKeys->contains($key))
                <button disabled class="w-full py-2 bg-green-500/10 text-green-400 border border-green-500/20 rounded-xl text-xs font-medium cursor-default">
                    ✓ Installed
                </button>
                @else
                <button @click="openInstallModal('{{ $key }}', '{{ addslashes($module['name']) }}')"
                        class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-medium transition-colors">
                    Install Module
                </button>
                @endif
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- All modules by category --}}
    @php
        $grouped = [];
        foreach ($modules as $key => $module) {
            $grouped[$module['category']][] = array_merge($module, ['key' => $key]);
        }
        ksort($grouped);
        $categoryLabels = [
            'core' => '⚙️ Core', 'billing' => '💰 Billing', 'communication' => '💬 Communication',
            'analytics' => '📊 Analytics', 'business' => '🏪 Business', 'ecommerce' => '🛒 eCommerce',
            'compliance' => '📋 Compliance', 'integration' => '🔌 Integration',
            'utility' => '🛠️ Utility', 'saas' => '🏢 SaaS',
        ];
    @endphp

    @foreach($grouped as $category => $mods)
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">
            {{ $categoryLabels[$category] ?? ucfirst($category) }}
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($mods as $mod)
            <div class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl p-4 transition-all flex items-start gap-4 group">
                <div class="w-10 h-10 rounded-xl bg-gray-800 border border-gray-700 flex items-center justify-center text-xl flex-shrink-0">
                    {{ $mod['icon'] }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h4 class="font-medium text-white text-sm group-hover:text-indigo-300 transition-colors">{{ $mod['name'] }}</h4>
                        @if(!($mod['free'] ?? true))
                        <span class="px-1.5 py-0.5 text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full flex-shrink-0">Pro</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed mb-2">{{ $mod['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            @foreach(array_slice($mod['generates'], 0, 3) as $gen)
                            <span class="px-1.5 py-0.5 text-[10px] bg-gray-800 text-gray-500 rounded">{{ $gen }}</span>
                            @endforeach
                        </div>
                        @if($installedKeys->contains($mod['key']))
                        <span class="text-xs text-green-400 font-medium">✓ Installed</span>
                        @else
                        <button @click="openInstallModal('{{ $mod['key'] }}', '{{ addslashes($mod['name']) }}')"
                                class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
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

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: AI AGENTS                                                           --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'agents'" x-cloak>
    <div class="mb-6 bg-gradient-to-r from-purple-900/20 to-pink-900/10 border border-purple-500/20 rounded-2xl p-5">
        <h3 class="text-base font-semibold text-white mb-1">AI Agents</h3>
        <p class="text-sm text-gray-400">Specialized AI agents that enhance your builder — each focused on one job, using the cheapest model that can do it.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($agents as $key => $agent)
        <div class="bg-gray-900 border border-gray-800 hover:border-purple-500/30 rounded-2xl p-5 transition-all hover:-translate-y-0.5 group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/20 flex items-center justify-center text-2xl">
                    {{ $agent['icon'] }}
                </div>
                <div class="flex flex-col items-end gap-1">
                    @php
                        $tierColors = ['free' => 'green', 'cheap' => 'blue', 'premium' => 'purple'];
                        $tc = $tierColors[$agent['model_tier']] ?? 'gray';
                    @endphp
                    <span class="px-2 py-0.5 text-xs bg-{{ $tc }}-500/10 text-{{ $tc }}-400 border border-{{ $tc }}-500/20 rounded-full capitalize">
                        {{ $agent['model_tier'] }} model
                    </span>
                    @if($agent['free'])
                    <span class="px-2 py-0.5 text-xs bg-green-500/10 text-green-400 rounded-full">Free</span>
                    @endif
                </div>
            </div>
            <h4 class="font-semibold text-white text-sm mb-1 group-hover:text-purple-300 transition-colors">{{ $agent['name'] }}</h4>
            <p class="text-xs text-gray-500 leading-relaxed mb-4">{{ $agent['description'] }}</p>
            <button @click="openInstallModal('agent:{{ $key }}', '{{ addslashes($agent['name']) }}')"
                    class="w-full py-2 bg-purple-600/80 hover:bg-purple-600 text-white rounded-xl text-xs font-medium transition-colors">
                Activate Agent
            </button>
        </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: PLUGINS & TEMPLATES (Existing marketplace)                          --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'marketplace'" x-cloak>

    {{-- Featured --}}
    @if($featured->isNotEmpty() && !request('search'))
    <div class="mb-8">
        <h3 class="text-base font-semibold text-white mb-4">Featured</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($featured as $item)
            <a href="{{ route('marketplace.show', $item) }}"
               class="bg-gray-900 border border-gray-800 hover:border-indigo-500/50 rounded-2xl p-5 transition-all hover:-translate-y-0.5 group block">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/20 flex items-center justify-center text-xl">
                        @switch($item->type)
                            @case('module') 📦 @break
                            @case('theme') 🎨 @break
                            @case('agent') 🤖 @break
                            @case('template') 📄 @break
                            @default 🔌
                        @endswitch
                    </div>
                    <span class="px-2 py-0.5 text-xs bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full">{{ $item->category }}</span>
                </div>
                <h4 class="font-semibold text-white text-sm mb-1 group-hover:text-indigo-300 transition-colors">{{ $item->name }}</h4>
                <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $item->description }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold {{ $item->is_free ? 'text-green-400' : 'text-white' }}">{{ $item->price_formatted }}</span>
                    <span class="text-xs text-gray-600">↓ {{ number_format($item->downloads) }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Category pills --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('marketplace.index', request()->except('category')) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ !request('category') ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white border border-gray-700' }}">
            All
        </a>
        @foreach($categories as $key => $label)
        <a href="{{ route('marketplace.index', array_merge(request()->all(), ['category' => $key])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ request('category') === $key ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white border border-gray-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Items grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($items as $item)
        <a href="{{ route('marketplace.show', $item) }}"
           class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-2xl p-5 transition-all hover:-translate-y-0.5 block group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gray-800 border border-gray-700 flex items-center justify-center text-xl">
                    {{ $item->icon ?? match($item->type) { 'module'=>'📦','theme'=>'🎨','agent'=>'🤖','template'=>'📄', default=>'🔌' } }}
                </div>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $item->is_free ? 'bg-green-500/10 text-green-400' : 'bg-indigo-500/10 text-indigo-400' }}">
                    {{ $item->price_formatted }}
                </span>
            </div>
            <h4 class="font-semibold text-white text-sm mb-1 group-hover:text-indigo-300 transition-colors">{{ $item->name }}</h4>
            <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $item->description }}</p>
            <div class="flex items-center justify-between text-xs text-gray-600">
                <span>{{ $item->category }}</span>
                <span>v{{ $item->version }}</span>
            </div>
        </a>
        @empty
        <div class="col-span-4 text-center py-16 text-gray-500">
            <p class="text-lg mb-2">No items found</p>
            <p class="text-sm">Try a different search or category</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->withQueryString()->links() }}</div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- INSTALL MODAL                                                            --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div x-show="modalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="closeModal()">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeModal()"></div>

    {{-- Modal --}}
    <div class="relative w-full max-w-md bg-gray-900 border border-gray-700 rounded-2xl p-6 shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <button @click="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="mb-5">
            <h3 class="text-lg font-bold text-white mb-1" x-text="'Install ' + modalModuleName"></h3>
            <p class="text-sm text-gray-400">Select a project to install this module into</p>
        </div>

        {{-- Success state --}}
        <div x-show="installSuccess" class="space-y-4">
            <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 text-center">
                <p class="text-2xl mb-2">✅</p>
                <p class="text-green-400 font-semibold text-sm" x-text="successMessage"></p>
                <p class="text-xs text-gray-500 mt-1">0 AI tokens used — pure PHP generation</p>
            </div>

            {{-- Menu items guide --}}
            <div x-show="menuItems.length > 0" class="bg-gray-800/60 border border-gray-700 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-300 mb-2">📌 Add these to your navigation menu:</p>
                <div class="space-y-2">
                    <template x-for="item in menuItems" :key="item.route">
                        <div class="flex items-center justify-between bg-gray-900/60 rounded-lg px-3 py-2">
                            <div class="flex items-center space-x-2">
                                <span x-text="item.icon" class="text-base"></span>
                                <div>
                                    <span class="text-sm text-white font-medium" x-text="item.label"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="'Group: ' + item.group"></span>
                                </div>
                            </div>
                            <code class="text-xs text-indigo-400 bg-gray-900 px-2 py-0.5 rounded font-mono" x-text="item.route"></code>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    Go to
                    <a href="{{ route('menus.index') }}" class="text-indigo-400 hover:text-indigo-300 underline">Menu Management</a>
                    → Add these routes to your sidebar or navbar.
                </p>
            </div>

            <button @click="closeModal()" class="w-full py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm transition-colors">
                Close
            </button>
        </div>

        {{-- Error state --}}
        <div x-show="installError" class="mb-4 bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm" x-text="errorMessage"></div>

        {{-- Install form --}}
        <div x-show="!installSuccess">
            @if($projects->count())
            <div class="space-y-3">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Select project</label>
                    <select x-model="selectedProject"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Choose a project —</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button @click="doInstall()"
                        :disabled="!selectedProject || installing"
                        :class="(!selectedProject || installing) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-700 cursor-pointer'"
                        class="w-full py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center space-x-2">
                    <span x-show="!installing">Install Module — 0 AI Tokens</span>
                    <span x-show="installing" class="flex items-center space-x-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span>Installing...</span>
                    </span>
                </button>
            </div>
            @else
            <div class="text-center py-6">
                <p class="text-gray-400 text-sm mb-3">You don't have any projects yet.</p>
                <a href="{{ route('projects.create') }}"
                   class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm transition-colors">
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
            // Switch to marketplace tab if search/category param present
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

        closeModal() {
            this.modalOpen = false;
        },

        async doInstall() {
            if (!this.selectedProject || this.installing) return;

            this.installing   = true;
            this.installError = false;

            try {
                const res = await fetch(
                    `/marketplace/modules/${this.modalModuleKey}/install`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ project_id: this.selectedProject }),
                    }
                );

                const data = await res.json();

                if (data.success) {
                    this.installSuccess = true;
                    this.successMessage = data.message || 'Module installed successfully!';
                    this.menuItems      = data.menu_items || [];
                } else {
                    this.installError   = true;
                    this.errorMessage   = data.message || 'Installation failed.';
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
