@extends('layouts.app')
@section('title', 'Settings')
@section('header', 'Settings')

@php
    $userId      = auth()->id();
    $savedColor  = \App\Models\Setting::get('branding.primary_color', '#6366f1', $userId);
    $savedFont   = \App\Models\Setting::get('branding.font_family',   'Poppins', $userId);
    $savedLogo   = \App\Models\Setting::get('branding.logo_path',     null,      $userId);
    $savedFav    = \App\Models\Setting::get('branding.favicon_path',  null,      $userId);
@endphp

@section('content')
<div class="max-w-4xl mx-auto"
     x-data="{
        tab: '{{ session('_tab', 'profile') }}',
        brandColor: '{{ $savedColor }}',
        fontFamily: '{{ $savedFont }}'
     }">

    <!-- Tab Navigation -->
    <div class="flex flex-wrap gap-1 mb-8 bg-gray-900 border border-gray-800 rounded-2xl p-1 w-fit">
        @foreach([
            ['key' => 'profile',    'label' => 'Profile',      'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['key' => 'ai',         'label' => 'AI Providers', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['key' => 'branding',   'label' => 'Branding',     'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
            ['key' => 'appearance', 'label' => 'Appearance',   'icon' => 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'],
            ['key' => 'security',   'label' => 'Security',     'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ] as $t)
        <button @click="tab = '{{ $t['key'] }}'"
                :class="tab === '{{ $t['key'] }}' ? 'bg-gray-800 text-white shadow' : 'text-gray-500 hover:text-gray-300'"
                class="flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $t['icon'] }}"/></svg>
            <span>{{ $t['label'] }}</span>
        </button>
        @endforeach
    </div>

    <!-- ══════════════════════════════════════════
         PROFILE TAB
    ══════════════════════════════════════════ -->
    <div x-show="tab === 'profile'" class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-base font-semibold text-white mb-6">Profile Information</h3>
            <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="flex items-center space-x-6 mb-6">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-20 h-20 rounded-2xl" alt="Avatar">
                    <div>
                        <h4 class="font-semibold text-white">{{ auth()->user()->name }}</h4>
                        <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                        <p class="text-xs text-indigo-400 mt-1">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Username</label>
                        <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="@username">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-all hover:-translate-y-0.5">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         AI PROVIDERS TAB
    ══════════════════════════════════════════ -->
    <div x-show="tab === 'ai'" class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-semibold text-white">AI Provider Keys</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Connect your AI accounts to enable code generation</p>
                </div>
            </div>
            @php
                $grouped = collect($allProviders)->groupBy(fn($p) => $p['category'] ?? 'text', preserveKeys: true);
                $categoryTitles = ['text' => '🧠 Text Generation', 'voice' => '🎙️ Voice & Audio', 'local' => '💻 Local / Self-Hosted'];
            @endphp
            @foreach($grouped as $catKey => $catProviders)
            <div class="mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">{{ $categoryTitles[$catKey] ?? ucfirst($catKey) }}</p>
                <div class="space-y-4">
                @foreach($catProviders as $key => $provider)
                @php
                    $savedProvider = $aiProviders->where('provider', $key)->first();
                    $isConfigured  = $savedProvider && $savedProvider->is_active;
                @endphp
                @php
                    $providerLinks = [
                        'claude'       => ['url' => 'https://console.anthropic.com/settings/keys',       'label' => 'console.anthropic.com'],
                        'openai'       => ['url' => 'https://platform.openai.com/api-keys',              'label' => 'platform.openai.com'],
                        'gemini'       => ['url' => 'https://aistudio.google.com/app/apikey',            'label' => 'aistudio.google.com'],
                        'mistral'      => ['url' => 'https://console.mistral.ai/api-keys',               'label' => 'console.mistral.ai'],
                        'grok'         => ['url' => 'https://console.x.ai/',                             'label' => 'console.x.ai'],
                        'deepseek'     => ['url' => 'https://platform.deepseek.com/api-keys',            'label' => 'platform.deepseek.com'],
                        'groq'         => ['url' => 'https://console.groq.com/keys',                     'label' => 'console.groq.com'],
                        'cohere'       => ['url' => 'https://dashboard.cohere.com/api-keys',             'label' => 'dashboard.cohere.com'],
                        'perplexity'   => ['url' => 'https://www.perplexity.ai/settings/api',            'label' => 'perplexity.ai/settings/api'],
                        'openrouter'   => ['url' => 'https://openrouter.ai/keys',                        'label' => 'openrouter.ai/keys'],
                        'together'     => ['url' => 'https://api.together.ai/settings/api-keys',         'label' => 'api.together.ai'],
                        'huggingface'  => ['url' => 'https://huggingface.co/settings/tokens',            'label' => 'huggingface.co/tokens'],
                        'azure'        => ['url' => 'https://portal.azure.com/#view/Microsoft_Azure_ProjectOxford/CognitiveServicesHub', 'label' => 'portal.azure.com'],
                        'bedrock'      => ['url' => 'https://aws.amazon.com/bedrock/',                   'label' => 'aws.amazon.com/bedrock'],
                        'replicate'    => ['url' => 'https://replicate.com/account/api-tokens',          'label' => 'replicate.com/account'],
                        'fireworks'    => ['url' => 'https://fireworks.ai/account/api-keys',             'label' => 'fireworks.ai/account'],
                        'cerebras'     => ['url' => 'https://cloud.cerebras.ai/',                        'label' => 'cloud.cerebras.ai'],
                        'ai21'         => ['url' => 'https://studio.ai21.com/account/api-key',           'label' => 'studio.ai21.com'],
                        'sambanova'    => ['url' => 'https://cloud.sambanova.ai/apis',                   'label' => 'cloud.sambanova.ai'],
                        'elevenlabs'   => ['url' => 'https://elevenlabs.io/app/settings/api-keys',       'label' => 'elevenlabs.io/api-keys'],
                        'ollama'       => ['url' => 'https://ollama.com/download',                       'label' => 'ollama.com/download'],
                    ];
                    $link = $providerLinks[$key] ?? null;
                    $categoryLabels = ['text' => 'Text Generation', 'voice' => 'Voice & Audio', 'local' => 'Local / Self-Hosted'];
                    $providerCategory = $provider['category'] ?? 'text';
                @endphp
                <div class="border rounded-2xl p-5 transition-all"
                     x-data="providerCard({{ $isConfigured ? 'true' : 'false' }}, '{{ $savedProvider?->id ?? '' }}', '{{ $key }}')"
                     :class="connected ? 'border-green-500/30 bg-green-500/[0.03]' : 'border-gray-700'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl border flex-shrink-0 transition-all"
                                 :class="connected ? 'bg-green-500/10 border-green-500/30' : 'bg-gray-800 border-gray-700'">
                                @switch($key)
                                    @case('claude')      🤖 @break
                                    @case('openai')      🔮 @break
                                    @case('gemini')      ✨ @break
                                    @case('mistral')     🌀 @break
                                    @case('grok')        ⚡ @break
                                    @case('deepseek')    🔵 @break
                                    @case('groq')        🚀 @break
                                    @case('cohere')      🧠 @break
                                    @case('perplexity')  🔍 @break
                                    @case('openrouter')  🔀 @break
                                    @case('together')    🤝 @break
                                    @case('huggingface') 🤗 @break
                                    @case('azure')       ☁️ @break
                                    @case('bedrock')     🟠 @break
                                    @case('replicate')   ♻️ @break
                                    @case('fireworks')   🎆 @break
                                    @case('cerebras')    ⚙️ @break
                                    @case('ai21')        🔬 @break
                                    @case('sambanova')   🏎️ @break
                                    @case('elevenlabs')  🎙️ @break
                                    @case('ollama')      🦙 @break
                                    @default             🔧
                                @endswitch
                            </div>
                            <div>
                                <h4 class="font-medium text-white text-sm">{{ $provider['name'] }}</h4>
                                <p class="text-xs transition-colors"
                                   :class="connected ? 'text-green-400' : 'text-gray-500'"
                                   x-text="connected ? '✓ Connected' : 'Not configured'"></p>
                                @if($link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center space-x-1 text-xs text-indigo-400 hover:text-indigo-300 mt-0.5 transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Test button — only when connected -->
                            <button x-show="connected"
                                    x-cloak
                                    @click="runTest()"
                                    :disabled="testing"
                                    class="text-xs bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50">
                                <span x-text="testing ? 'Testing…' : 'Test'"></span>
                            </button>
                            <!-- Connect / Update button -->
                            <button @click="open = !open"
                                    class="text-xs px-3 py-1.5 rounded-lg border transition-all font-medium"
                                    :class="connected
                                        ? 'bg-green-500/10 border-green-500/30 text-green-400 hover:bg-green-500/20'
                                        : 'bg-indigo-600/20 border-indigo-500/30 text-indigo-400 hover:bg-indigo-600/30'">
                                <span x-text="connected ? '✓ Connected — Update' : '+ Connect'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Test / save result feedback -->
                    <div x-show="testResult || saveResult" class="mt-3 p-3 rounded-xl text-xs"
                         :class="(testResult?.success || saveResult?.success) ? 'bg-green-900/30 border border-green-500/20 text-green-300' : 'bg-red-900/30 border border-red-500/20 text-red-300'">
                        <span x-text="testResult?.message || saveResult?.message"></span>
                    </div>

                    <!-- Expand form -->
                    <div x-show="open" x-transition class="mt-4 pt-4 border-t border-gray-700 space-y-3">
                        <input type="hidden" id="provider_key_{{ $key }}" value="{{ $key }}">
                        <input type="hidden" id="csrf_{{ $key }}" value="{{ csrf_token() }}">

                        @if($key !== 'ollama')
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">API Key</label>
                            <input type="password" id="api_key_{{ $key }}"
                                   :placeholder="connected ? '••••••••• (leave blank to keep current)' : 'Enter {{ $provider['name'] }} API key'"
                                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-600">
                        </div>
                        @endif

                        @if(!empty($provider['requires_endpoint']) || $key === 'ollama')
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">
                                @if($key === 'ollama') Ollama Host URL
                                @elseif($key === 'bedrock') AWS Region (e.g. us-east-1)
                                @else Endpoint URL
                                @endif
                            </label>
                            <input type="text" id="api_url_{{ $key }}"
                                   value="{{ $savedProvider?->api_url ?? $provider['api_url'] }}"
                                   placeholder="{{ $provider['api_url'] }}"
                                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-600">
                            @if($key === 'azure')
                            <p class="text-xs text-gray-600 mt-1">Format: https://{resource-name}.openai.azure.com/</p>
                            @endif
                        </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Default Model</label>
                            <select id="model_{{ $key }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach($provider['models'] as $mKey => $mName)
                                <option value="{{ $mKey }}" {{ ($savedProvider?->default_model === $mKey) ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="default_{{ $key }}" class="w-4 h-4 rounded text-indigo-600">
                            <label for="default_{{ $key }}" class="text-xs text-gray-400">Set as default AI provider</label>
                        </div>

                        <div class="flex items-center space-x-2 pt-1">
                            <!-- Connect / Save button -->
                            <button @click="doSave('{{ $key }}')"
                                    :disabled="saving"
                                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition-all disabled:opacity-50"
                                    :class="saveResult?.success
                                        ? 'bg-green-600 text-white'
                                        : 'bg-indigo-600 hover:bg-indigo-500 text-white'">
                                <svg x-show="saving" class="w-3.5 h-3.5 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="saving ? 'Connecting…' : (saveResult?.success ? '✓ Connected!' : 'Connect')"></span>
                            </button>

                            <button type="button" @click="open=false; saveResult=null"
                                    class="bg-gray-700 hover:bg-gray-600 text-gray-300 px-4 py-2 rounded-xl text-xs transition-colors">
                                Cancel
                            </button>

                            <!-- Remove button — only when connected -->
                            <button type="button" x-show="connected" x-cloak
                                    @click="doRemove('{{ route('settings.ai-provider.delete', ['aiProvider' => $savedProvider?->id ?? 0]) }}')"
                                    class="text-red-400 hover:text-red-300 px-4 py-2 text-xs transition-colors ml-auto">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>{{-- /.space-y-4 --}}
            </div>{{-- /.mb-6 category group --}}
            @endforeach
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         BRANDING TAB
    ══════════════════════════════════════════ -->
    <div x-show="tab === 'branding'" class="space-y-6">

        {{-- Brand Color & Font --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-base font-semibold text-white mb-1">Brand Colors & Typography</h3>
            <p class="text-sm text-gray-500 mb-6">Customize the look of your RyaanCMS dashboard</p>

            <form method="POST" action="{{ route('settings.branding') }}" class="space-y-6">
                @csrf

                {{-- Color Picker --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Primary Brand Color</label>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="color" name="primary_color" x-model="brandColor"
                                   class="w-14 h-14 rounded-2xl border-2 border-gray-700 cursor-pointer bg-transparent p-1"
                                   style="padding: 3px;">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5">
                                <div class="w-5 h-5 rounded-lg flex-shrink-0 transition-colors" :style="'background-color: ' + brandColor"></div>
                                <input type="text" x-model="brandColor" @input="if(/^#[0-9A-Fa-f]{6}$/.test($event.target.value)) brandColor=$event.target.value"
                                       class="flex-1 bg-transparent text-white text-sm font-mono focus:outline-none uppercase"
                                       maxlength="7" placeholder="#6366f1">
                            </div>
                            <p class="text-xs text-gray-600 mt-1.5">Applied to buttons, sidebar active items, and accent elements.</p>
                        </div>
                    </div>

                    {{-- Color Presets --}}
                    <div class="mt-3">
                        <p class="text-xs text-gray-500 mb-2">Quick Presets</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                ['color' => '#6366f1', 'name' => 'Indigo'],
                                ['color' => '#8b5cf6', 'name' => 'Violet'],
                                ['color' => '#ec4899', 'name' => 'Pink'],
                                ['color' => '#f97316', 'name' => 'Orange'],
                                ['color' => '#10b981', 'name' => 'Emerald'],
                                ['color' => '#3b82f6', 'name' => 'Blue'],
                                ['color' => '#ef4444', 'name' => 'Red'],
                                ['color' => '#eab308', 'name' => 'Yellow'],
                                ['color' => '#14b8a6', 'name' => 'Teal'],
                                ['color' => '#a855f7', 'name' => 'Purple'],
                            ] as $preset)
                            <button type="button" @click="brandColor = '{{ $preset['color'] }}'"
                                    class="w-8 h-8 rounded-xl border-2 transition-all hover:scale-110"
                                    :class="brandColor === '{{ $preset['color'] }}' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"
                                    style="background-color: {{ $preset['color'] }}"
                                    title="{{ $preset['name'] }}">
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Font Selector --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Font Family</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            ['name' => 'Poppins',      'preview' => 'Aa Bb Cc'],
                            ['name' => 'Inter',        'preview' => 'Aa Bb Cc'],
                            ['name' => 'DM Sans',      'preview' => 'Aa Bb Cc'],
                            ['name' => 'Nunito',       'preview' => 'Aa Bb Cc'],
                            ['name' => 'Roboto',       'preview' => 'Aa Bb Cc'],
                            ['name' => 'Open Sans',    'preview' => 'Aa Bb Cc'],
                        ] as $font)
                        <label class="cursor-pointer">
                            <input type="radio" name="font_family" value="{{ $font['name'] }}"
                                   x-model="fontFamily" class="sr-only">
                            <div :class="fontFamily === '{{ $font['name'] }}' ? 'border-indigo-500 bg-indigo-500/10 ring-2 ring-indigo-500/30' : 'border-gray-700 hover:border-gray-600'"
                                 class="border-2 rounded-2xl p-4 text-center transition-all">
                                <p class="text-2xl font-semibold text-white mb-1" style="font-family: '{{ $font['name'] }}', sans-serif;">{{ $font['preview'] }}</p>
                                <p class="text-xs font-medium" :class="fontFamily === '{{ $font['name'] }}' ? 'text-indigo-400' : 'text-gray-500'">{{ $font['name'] }}</p>
                                @if($font['name'] === 'Poppins')
                                <span class="text-xs text-green-400">Default</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-5">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-3">Live Preview</p>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold" :style="'background-color: ' + brandColor">A</div>
                            <div>
                                <p class="text-sm font-semibold text-white" :style="'font-family: ' + fontFamily + ', sans-serif'">Dashboard Overview</p>
                                <p class="text-xs text-gray-500" :style="'font-family: ' + fontFamily + ', sans-serif'">Your workspace at a glance</p>
                            </div>
                        </div>
                        <div class="flex space-x-2 pt-1">
                            <div class="px-4 py-2 rounded-xl text-white text-xs font-semibold" :style="'background-color: ' + brandColor + '; font-family: ' + fontFamily + ', sans-serif'">Primary Button</div>
                            <div class="px-4 py-2 rounded-xl text-xs font-semibold border-2" :style="'color: ' + brandColor + '; border-color: ' + brandColor + '; font-family: ' + fontFamily + ', sans-serif'">Outline Button</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-all hover:-translate-y-0.5 shadow-lg">
                        Save Brand Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Logo Upload --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-base font-semibold text-white mb-1">Logo & Favicon</h3>
            <p class="text-sm text-gray-500 mb-6">Appears in the sidebar and browser tab</p>

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Logo --}}
                <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                      x-data="{ preview: '{{ $savedLogo ? Storage::url($savedLogo) : '' }}' }">
                    @csrf
                    <input type="hidden" name="type" value="logo">
                    <label class="block text-sm font-medium text-gray-300 mb-3">Site Logo</label>
                    <div class="border-2 border-dashed border-gray-700 hover:border-indigo-500/50 rounded-2xl p-6 text-center transition-colors cursor-pointer"
                         @click="$refs.logoInput.click()">
                        <template x-if="preview">
                            <img :src="preview" class="w-24 h-24 object-contain mx-auto mb-3 rounded-xl">
                        </template>
                        <template x-if="!preview">
                            <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </template>
                        <p class="text-sm text-gray-400">Click to upload logo</p>
                        <p class="text-xs text-gray-600 mt-1">PNG, SVG, JPG · Max 2MB · Recommended: 200×200px</p>
                    </div>
                    <input type="file" name="file" x-ref="logoInput" accept="image/*" class="hidden"
                           @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                    @if($savedLogo)
                    <p class="mt-2 text-xs text-green-400">✓ Logo uploaded</p>
                    @endif
                </form>

                {{-- Favicon --}}
                <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                      x-data="{ preview: '{{ $savedFav ? Storage::url($savedFav) : '' }}' }">
                    @csrf
                    <input type="hidden" name="type" value="favicon">
                    <label class="block text-sm font-medium text-gray-300 mb-3">Favicon</label>
                    <div class="border-2 border-dashed border-gray-700 hover:border-indigo-500/50 rounded-2xl p-6 text-center transition-colors cursor-pointer"
                         @click="$refs.favInput.click()">
                        <template x-if="preview">
                            <img :src="preview" class="w-16 h-16 object-contain mx-auto mb-3 rounded-xl">
                        </template>
                        <template x-if="!preview">
                            <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                        </template>
                        <p class="text-sm text-gray-400">Click to upload favicon</p>
                        <p class="text-xs text-gray-600 mt-1">ICO, PNG, SVG · Max 2MB · Recommended: 32×32px</p>
                    </div>
                    <input type="file" name="file" x-ref="favInput" accept="image/*,.ico" class="hidden"
                           @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                    @if($savedFav)
                    <p class="mt-2 text-xs text-green-400">✓ Favicon uploaded</p>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         APPEARANCE TAB (Theme Toggle)
    ══════════════════════════════════════════ -->
    <div x-show="tab === 'appearance'" class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-base font-semibold text-white mb-1">Theme</h3>
            <p class="text-sm text-gray-500 mb-6">Choose how RyaanCMS looks for you</p>

            <div class="grid grid-cols-2 gap-4 mb-6" x-data>
                {{-- Light Mode --}}
                <button type="button" @click="
                    $dispatch('set-theme', 'light');
                    localStorage.setItem('theme', 'light');
                    document.documentElement.classList.remove('dark');
                    $store && $store.darkMode !== undefined ? ($store.darkMode = false) : null;
                "
                :class="!$root.closest('[x-data]').__x.$data.darkMode ? 'border-indigo-500 ring-2 ring-indigo-500/30' : 'border-gray-700 hover:border-gray-600'"
                class="border-2 rounded-2xl p-4 text-center cursor-pointer transition-all">
                    <div class="w-full h-24 rounded-xl mb-3 overflow-hidden border border-gray-300/30">
                        <div class="h-6 bg-white border-b border-gray-200 flex items-center px-3 space-x-1.5">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex h-full bg-white">
                            <div class="w-10 bg-slate-100 border-r border-gray-200 p-1 space-y-1">
                                <div class="w-full h-2 rounded bg-indigo-500"></div>
                                <div class="w-full h-2 rounded bg-slate-200"></div>
                                <div class="w-full h-2 rounded bg-slate-200"></div>
                            </div>
                            <div class="flex-1 bg-slate-50 p-2 space-y-1.5">
                                <div class="w-3/4 h-2 rounded bg-slate-200"></div>
                                <div class="w-1/2 h-2 rounded bg-slate-200"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="text-sm font-semibold text-white">Light</span>
                        <span class="text-xs text-green-400 bg-green-500/10 border border-green-500/20 px-1.5 py-0.5 rounded-full">Default</span>
                    </div>
                </button>

                {{-- Dark Mode --}}
                <button type="button" @click="
                    localStorage.setItem('theme', 'dark');
                    document.documentElement.classList.add('dark');
                "
                :class="$root.closest('[x-data]').__x.$data.darkMode ? 'border-indigo-500 ring-2 ring-indigo-500/30' : 'border-gray-700 hover:border-gray-600'"
                class="border-2 rounded-2xl p-4 text-center cursor-pointer transition-all">
                    <div class="w-full h-24 rounded-xl mb-3 overflow-hidden border border-gray-700/50">
                        <div class="h-6 bg-gray-950 border-b border-gray-800 flex items-center px-3 space-x-1.5">
                            <div class="w-2 h-2 rounded-full bg-red-500/60"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-500/60"></div>
                            <div class="w-2 h-2 rounded-full bg-green-500/60"></div>
                        </div>
                        <div class="flex h-full bg-gray-950">
                            <div class="w-10 bg-gray-900 border-r border-gray-800 p-1 space-y-1">
                                <div class="w-full h-2 rounded bg-indigo-600"></div>
                                <div class="w-full h-2 rounded bg-gray-700"></div>
                                <div class="w-full h-2 rounded bg-gray-700"></div>
                            </div>
                            <div class="flex-1 bg-gray-950 p-2 space-y-1.5">
                                <div class="w-3/4 h-2 rounded bg-gray-700"></div>
                                <div class="w-1/2 h-2 rounded bg-gray-700"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="text-sm font-semibold text-white">Dark</span>
                    </div>
                </button>
            </div>

            {{-- Toggle Switch --}}
            <div class="flex items-center justify-between bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                <div>
                    <p class="text-sm font-semibold text-white" x-text="$root.closest('[x-data]').__x?.$data?.darkMode ? 'Dark Mode Active' : 'Light Mode Active'">Light Mode Active</p>
                    <p class="text-xs text-gray-500">Toggle with the sun/moon icon in the top bar or sidebar</p>
                </div>
                <button type="button"
                        @click="
                            let isDark = document.documentElement.classList.contains('dark');
                            if(isDark) {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('theme','light');
                            } else {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('theme','dark');
                            }
                        "
                        class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors focus:outline-none"
                        :class="document.documentElement.classList.contains('dark') ? 'bg-indigo-600' : 'bg-gray-600'">
                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform"
                          :class="document.documentElement.classList.contains('dark') ? 'translate-x-8' : 'translate-x-1'">
                    </span>
                </button>
            </div>

            <p class="text-xs text-gray-600 mt-4">
                💡 Your theme preference is saved locally in your browser. It persists across sessions.
            </p>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         SECURITY TAB
    ══════════════════════════════════════════ -->
    <div x-show="tab === 'security'" class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-base font-semibold text-white mb-6">Change Password</h3>
            <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Current Password</label>
                    <input type="password" name="current_password"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('current_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">New Password</label>
                    <input type="password" name="password" minlength="8"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-all hover:-translate-y-0.5">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function providerCard(isConnected, savedId, providerKey) {
    return {
        connected:   isConnected,
        providerId:  savedId,
        open:        false,
        saving:      false,
        testing:     false,
        saveResult:  null,
        testResult:  null,

        doSave(key) {
            this.saveResult = null;
            this.testResult = null;

            const apiKeyEl  = document.getElementById('api_key_'  + key);
            const apiUrlEl  = document.getElementById('api_url_'  + key);
            const modelEl   = document.getElementById('model_'    + key);
            const defaultEl = document.getElementById('default_'  + key);
            const csrfEl    = document.getElementById('csrf_'     + key);

            const keyValue = apiKeyEl ? apiKeyEl.value.trim() : '';

            // Block submit when no key entered and this is a fresh connection
            if (!this.connected && !keyValue) {
                this.saveResult = { success: false, message: 'API Key is required. Please paste it in the field above.' };
                return;
            }

            this.saving = true;

            const body = {
                provider:      key,
                api_key:       keyValue,
                api_url:       apiUrlEl  ? apiUrlEl.value  : '',
                default_model: modelEl   ? modelEl.value   : '',
                set_default:   defaultEl ? (defaultEl.checked ? '1' : '0') : '0',
            };

            fetch('{{ route('settings.ai-provider.save') }}', {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  csrfEl ? csrfEl.value : document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(body),
            })
            .then(r => r.json())
            .then(data => {
                this.saving     = false;
                this.saveResult = data;
                if (data.success) {
                    this.connected  = true;
                    this.providerId = data.provider_id ?? this.providerId;
                    if (apiKeyEl) apiKeyEl.value = '';
                    // auto-close after 2.5s
                    setTimeout(() => {
                        this.open       = false;
                        this.saveResult = null;
                    }, 2500);
                }
            })
            .catch(() => {
                this.saving     = false;
                this.saveResult = { success: false, message: 'Network error — please try again.' };
            });
        },

        runTest() {
            this.testing    = true;
            this.testResult = null;
            this.saveResult = null;

            fetch('{{ route('settings.ai-provider.test') }}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ provider: this.providerKey ?? providerKey }),
            })
            .then(r => r.json())
            .then(data => { this.testing = false; this.testResult = data; })
            .catch(() => { this.testing = false; this.testResult = { success: false, message: 'Connection failed.' }; });
        },

        doRemove(deleteUrl) {
            if (!confirm('Remove this provider and its API key?')) return;

            fetch(deleteUrl, {
                method:  'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.connected  = false;
                    this.providerId = '';
                    this.open       = false;
                    this.saveResult = null;
                    this.testResult = null;
                }
            })
            .catch(() => {});
        },
    };
}
</script>
@endpush
