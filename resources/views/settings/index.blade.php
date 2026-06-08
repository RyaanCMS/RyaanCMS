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

<style>
    .sfield { width:100%; font-size:13.5px; font-family:inherit; padding:9px 13px; border-radius:10px; outline:none; transition:border-color .15s, box-shadow .15s; }
    .sfield:focus { border-color:var(--brand) !important; box-shadow:0 0 0 3px var(--brand-ring); }
    .stab-btn { display:flex; align-items:center; gap:7px; padding:7px 14px; border-radius:10px; font-size:13px; font-weight:500; cursor:pointer; border:none; background:none; transition:all .15s; white-space:nowrap; }
    .stab-btn.active { background:var(--brand); color:#fff; box-shadow:0 2px 8px var(--brand-ring); }
    .stab-btn:not(.active) { color:var(--text-2); }
    .stab-btn:not(.active):hover { background:var(--hover-bg); color:var(--text-1); }
    .scard { border-radius:16px; overflow:hidden; background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow); }
    .scard-header { padding:18px 22px; border-bottom:1px solid var(--border); }
    .scard-body { padding:22px; }
    .slabel { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-3); margin-bottom:6px; }
    .sbtn-primary { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; border-radius:10px; font-size:13px; font-weight:600; color:#fff; border:none; cursor:pointer; transition:all .15s; }
    .sbtn-primary:hover { filter:brightness(1.08); transform:translateY(-1px); }
    .sbtn-secondary { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; font-size:13px; font-weight:500; cursor:pointer; transition:all .15s; }
</style>

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
        tab: '{{ session('_tab', 'profile') }}',
        brandColor: '{{ $savedColor }}',
        fontFamily: '{{ $savedFont }}'
     }">

    {{-- ── Tab Navigation ─────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-1.5 p-1.5 rounded-2xl"
         style="background:var(--card-sub); border:1px solid var(--border);">
        @foreach([
            ['key'=>'profile',  'label'=>'Profile',      'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['key'=>'ai',       'label'=>'AI Providers', 'icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
            ['key'=>'branding', 'label'=>'Branding',     'icon'=>'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
            ['key'=>'security', 'label'=>'Security',     'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ] as $t)
        <button @click="tab = '{{ $t['key'] }}'"
                :class="tab === '{{ $t['key'] }}' ? 'active' : ''"
                class="stab-btn">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $t['icon'] }}"/>
            </svg>
            <span>{{ $t['label'] }}</span>
        </button>
        @endforeach
    </div>

    {{-- ══════════ PROFILE ══════════ --}}
    <div x-show="tab === 'profile'" class="space-y-5">

        @if(session('status') === 'profile-updated')
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Profile updated successfully.
        </div>
        @endif

        <div class="scard">
            <div class="scard-header">
                <p class="text-sm font-semibold" style="color:var(--text-1);">Profile Information</p>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">Update your name, email, and username</p>
            </div>
            <div class="scard-body">
                {{-- Avatar row --}}
                <div class="flex items-center gap-4 mb-6 pb-5" style="border-bottom:1px solid var(--border);">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-2xl ring-2" style="ring-color:var(--border);" alt="Avatar">
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--text-1);">{{ auth()->user()->name }}</p>
                        <p class="text-xs mt-0.5" style="color:var(--text-3);">{{ auth()->user()->email }}</p>
                        <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                              style="background:var(--brand-light); color:var(--brand);">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="slabel">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                   class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                            @error('name')<p class="mt-1 text-xs" style="color:#dc2626;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="slabel">Username</label>
                            <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}"
                                   placeholder="@username"
                                   class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                        </div>
                    </div>
                    <div>
                        <label class="slabel">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                               class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                        @error('email')<p class="mt-1 text-xs" style="color:#dc2626;">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="sbtn-primary" style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════ AI PROVIDERS ══════════ --}}
    <div x-show="tab === 'ai'" class="space-y-5">

        <div class="scard">
            <div class="scard-header">
                <p class="text-sm font-semibold" style="color:var(--text-1);">AI Provider Keys</p>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">Connect your AI accounts to enable code generation. Optional — you can add these later.</p>
            </div>
            <div class="scard-body space-y-3">
                @php
                    $grouped = collect($allProviders)->groupBy(fn($p) => $p['category'] ?? 'text', preserveKeys: true);
                    $categoryTitles = ['text' => 'Text Generation', 'voice' => 'Voice & Audio', 'local' => 'Local / Self-Hosted'];
                    $categoryIcons  = ['text' => '🧠', 'voice' => '🎙️', 'local' => '💻'];
                @endphp
                @foreach($grouped as $catKey => $catProviders)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-2 px-1" style="color:var(--text-3);">
                        {{ $categoryIcons[$catKey] ?? '' }} {{ $categoryTitles[$catKey] ?? ucfirst($catKey) }}
                    </p>
                    <div class="space-y-2">
                    @foreach($catProviders as $key => $provider)
                    @php
                        $savedProvider = $aiProviders->where('provider', $key)->first();
                        $isConfigured  = $savedProvider && $savedProvider->is_active;
                        $providerLinks = [
                            'claude'      =>['url'=>'https://console.anthropic.com/settings/keys',   'label'=>'console.anthropic.com'],
                            'openai'      =>['url'=>'https://platform.openai.com/api-keys',          'label'=>'platform.openai.com'],
                            'gemini'      =>['url'=>'https://aistudio.google.com/app/apikey',        'label'=>'aistudio.google.com'],
                            'mistral'     =>['url'=>'https://console.mistral.ai/api-keys',           'label'=>'console.mistral.ai'],
                            'grok'        =>['url'=>'https://console.x.ai/',                         'label'=>'console.x.ai'],
                            'deepseek'    =>['url'=>'https://platform.deepseek.com/api-keys',        'label'=>'platform.deepseek.com'],
                            'groq'        =>['url'=>'https://console.groq.com/keys',                 'label'=>'console.groq.com'],
                            'cohere'      =>['url'=>'https://dashboard.cohere.com/api-keys',         'label'=>'dashboard.cohere.com'],
                            'perplexity'  =>['url'=>'https://www.perplexity.ai/settings/api',        'label'=>'perplexity.ai'],
                            'openrouter'  =>['url'=>'https://openrouter.ai/keys',                    'label'=>'openrouter.ai'],
                            'together'    =>['url'=>'https://api.together.ai/settings/api-keys',     'label'=>'api.together.ai'],
                            'huggingface' =>['url'=>'https://huggingface.co/settings/tokens',        'label'=>'huggingface.co'],
                            'azure'       =>['url'=>'https://portal.azure.com/',                     'label'=>'portal.azure.com'],
                            'bedrock'     =>['url'=>'https://aws.amazon.com/bedrock/',               'label'=>'aws.amazon.com/bedrock'],
                            'replicate'   =>['url'=>'https://replicate.com/account/api-tokens',      'label'=>'replicate.com'],
                            'fireworks'   =>['url'=>'https://fireworks.ai/account/api-keys',         'label'=>'fireworks.ai'],
                            'cerebras'    =>['url'=>'https://cloud.cerebras.ai/',                    'label'=>'cloud.cerebras.ai'],
                            'ai21'        =>['url'=>'https://studio.ai21.com/account/api-key',       'label'=>'studio.ai21.com'],
                            'sambanova'   =>['url'=>'https://cloud.sambanova.ai/apis',               'label'=>'cloud.sambanova.ai'],
                            'elevenlabs'  =>['url'=>'https://elevenlabs.io/app/settings/api-keys',   'label'=>'elevenlabs.io'],
                            'ollama'      =>['url'=>'https://ollama.com/download',                   'label'=>'ollama.com'],
                        ];
                        $link = $providerLinks[$key] ?? null;
                        $providerEmojis = ['claude'=>'🤖','openai'=>'🔮','gemini'=>'✨','mistral'=>'🌀','grok'=>'⚡','deepseek'=>'🔵','groq'=>'🚀','cohere'=>'🧠','perplexity'=>'🔍','openrouter'=>'🔀','together'=>'🤝','huggingface'=>'🤗','azure'=>'☁️','bedrock'=>'🟠','replicate'=>'♻️','fireworks'=>'🎆','cerebras'=>'⚙️','ai21'=>'🔬','sambanova'=>'🏎️','elevenlabs'=>'🎙️','ollama'=>'🦙'];
                    @endphp
                    <div class="rounded-xl p-4 transition-all"
                         x-data="providerCard({{ $isConfigured ? 'true' : 'false' }}, '{{ $savedProvider?->id ?? '' }}', '{{ $key }}')"
                         :style="connected ? 'border:1.5px solid #a7f3d0; background:#f0fdf4;' : 'border:1.5px solid var(--border); background:var(--card-bg);'">

                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0 transition-all"
                                     :style="connected ? 'background:#dcfce7; border:1px solid #a7f3d0;' : 'background:var(--card-sub); border:1px solid var(--border);'">
                                    {{ $providerEmojis[$key] ?? '🔧' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold" style="color:var(--text-1);">{{ $provider['name'] }}</p>
                                    <p class="text-xs transition-colors"
                                       :style="connected ? 'color:#15803d;' : 'color:var(--text-3);'"
                                       x-text="connected ? '✓ Connected' : 'Not configured'"></p>
                                    @if($link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 text-[10px] mt-0.5 transition-colors"
                                       style="color:var(--brand);"
                                       onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        {{ $link['label'] }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button x-show="connected" x-cloak @click="runTest()" :disabled="testing"
                                        class="sbtn-secondary text-xs disabled:opacity-50"
                                        style="background:var(--card-sub); border:1px solid var(--border); color:var(--text-2);"
                                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-sub)'">
                                    <span x-text="testing ? 'Testing…' : 'Test'"></span>
                                </button>
                                <button @click="open = !open"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium transition-all"
                                        :style="connected
                                            ? 'background:#dcfce7; border:1px solid #a7f3d0; color:#15803d;'
                                            : 'background:var(--brand-light); border:1px solid var(--brand-ring); color:var(--brand);'">
                                    <span x-text="connected ? '✓ Update' : '+ Connect'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Feedback --}}
                        <div x-show="testResult || saveResult"
                             class="mt-3 p-3 rounded-lg text-xs"
                             :style="(testResult?.success || saveResult?.success)
                                ? 'background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;'
                                : 'background:#fef2f2; border:1px solid #fecaca; color:#991b1b;'">
                            <span x-text="testResult?.message || saveResult?.message"></span>
                        </div>

                        {{-- Expand form --}}
                        <div x-show="open" x-transition class="mt-4 pt-4 space-y-3" style="border-top:1px solid var(--border);">
                            <input type="hidden" id="provider_key_{{ $key }}" value="{{ $key }}">
                            <input type="hidden" id="csrf_{{ $key }}" value="{{ csrf_token() }}">

                            @if($key !== 'ollama')
                            <div>
                                <label class="slabel">API Key</label>
                                <input type="password" id="api_key_{{ $key }}"
                                       :placeholder="connected ? '•••••••• (leave blank to keep current)' : 'Enter {{ $provider['name'] }} API key'"
                                       class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                            </div>
                            @endif

                            @if(!empty($provider['requires_endpoint']) || $key === 'ollama')
                            <div>
                                <label class="slabel">
                                    @if($key === 'ollama') Ollama Host URL
                                    @elseif($key === 'bedrock') AWS Region
                                    @else Endpoint URL
                                    @endif
                                </label>
                                <input type="text" id="api_url_{{ $key }}"
                                       value="{{ $savedProvider?->api_url ?? $provider['api_url'] }}"
                                       placeholder="{{ $provider['api_url'] }}"
                                       class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                            </div>
                            @endif

                            <div>
                                <label class="slabel">Default Model</label>
                                <select id="model_{{ $key }}"
                                        class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1); appearance:none; cursor:pointer;">
                                    @foreach($provider['models'] as $mKey => $mName)
                                    <option value="{{ $mKey }}" {{ ($savedProvider?->default_model === $mKey) ? 'selected' : '' }}>{{ $mName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="default_{{ $key }}"
                                       class="w-3.5 h-3.5 rounded" style="accent-color:var(--brand);">
                                <label for="default_{{ $key }}" class="text-xs" style="color:var(--text-2);">Set as default AI provider</label>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button @click="doSave('{{ $key }}')" :disabled="saving"
                                        class="sbtn-primary text-xs disabled:opacity-50"
                                        :style="saveResult?.success
                                            ? 'background:#16a34a; box-shadow:none;'
                                            : 'background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);'">
                                    <svg x-show="saving" class="w-3.5 h-3.5 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span x-text="saving ? 'Saving…' : (saveResult?.success ? '✓ Saved!' : 'Connect')"></span>
                                </button>
                                <button type="button" @click="open=false; saveResult=null"
                                        class="sbtn-secondary text-xs"
                                        style="background:var(--card-sub); border:1px solid var(--border); color:var(--text-2);"
                                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-sub)'">
                                    Cancel
                                </button>
                                <button type="button" x-show="connected" x-cloak
                                        @click="doRemove('{{ route('settings.ai-provider.delete', ['aiProvider' => $savedProvider?->id ?? 0]) }}')"
                                        class="ml-auto text-xs px-3 py-1.5 rounded-lg transition-colors"
                                        style="color:#ef4444;"
                                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════ BRANDING ══════════ --}}
    <div x-show="tab === 'branding'" class="space-y-5">

        <div class="scard">
            <div class="scard-header">
                <p class="text-sm font-semibold" style="color:var(--text-1);">Brand Colors & Typography</p>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">Customize the look of your RyaanCMS dashboard</p>
            </div>
            <div class="scard-body">
                <form method="POST" action="{{ route('settings.branding') }}" class="space-y-6">
                    @csrf

                    {{-- Color Picker --}}
                    <div>
                        <label class="slabel">Primary Brand Color</label>
                        <div class="flex items-center gap-4">
                            <input type="color" name="primary_color" x-model="brandColor"
                                   class="w-12 h-12 rounded-xl cursor-pointer"
                                   style="border:2px solid var(--border); padding:3px; background:none;">
                            <div class="flex-1 flex items-center gap-3 rounded-xl px-4 py-2.5"
                                 style="background:var(--card-sub); border:1.5px solid var(--border);">
                                <div class="w-5 h-5 rounded-lg flex-shrink-0 transition-colors" :style="'background:' + brandColor"></div>
                                <input type="text" x-model="brandColor"
                                       @input="if(/^#[0-9A-Fa-f]{6}$/.test($event.target.value)) brandColor=$event.target.value"
                                       class="flex-1 bg-transparent text-sm font-mono outline-none uppercase" style="color:var(--text-1);"
                                       maxlength="7" placeholder="#6366f1">
                            </div>
                        </div>
                        {{-- Presets --}}
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach([
                                ['color'=>'#6366f1','name'=>'Indigo'],['color'=>'#8b5cf6','name'=>'Violet'],
                                ['color'=>'#ec4899','name'=>'Pink'],  ['color'=>'#f97316','name'=>'Orange'],
                                ['color'=>'#10b981','name'=>'Emerald'],['color'=>'#3b82f6','name'=>'Blue'],
                                ['color'=>'#ef4444','name'=>'Red'],   ['color'=>'#eab308','name'=>'Yellow'],
                                ['color'=>'#14b8a6','name'=>'Teal'],  ['color'=>'#a855f7','name'=>'Purple'],
                            ] as $preset)
                            <button type="button" @click="brandColor = '{{ $preset['color'] }}'"
                                    class="w-8 h-8 rounded-xl border-2 transition-all hover:scale-110"
                                    :class="brandColor === '{{ $preset['color'] }}' ? 'scale-110 shadow-md' : 'border-transparent'"
                                    :style="'background:{{ $preset['color'] }}; ' + (brandColor === '{{ $preset['color'] }}' ? 'border-color:{{ $preset['color'] }}; outline:2px solid {{ $preset['color'] }}33;' : '')"
                                    title="{{ $preset['name'] }}"></button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Font Selector --}}
                    <div>
                        <label class="slabel">Font Family</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach([
                                ['name'=>'Poppins','default'=>true],['name'=>'Inter'],['name'=>'DM Sans'],
                                ['name'=>'Nunito'],['name'=>'Roboto'],['name'=>'Open Sans'],
                            ] as $font)
                            <label class="cursor-pointer">
                                <input type="radio" name="font_family" value="{{ $font['name'] }}" x-model="fontFamily" class="sr-only">
                                <div class="rounded-xl p-4 text-center transition-all"
                                     :style="fontFamily === '{{ $font['name'] }}'
                                        ? 'border:2px solid var(--brand); background:var(--brand-light); box-shadow:0 0 0 3px var(--brand-ring);'
                                        : 'border:2px solid var(--border); background:var(--card-bg);'"
                                     onmouseover="if(!this.parentElement.previousElementSibling.checked) this.style.borderColor='var(--border)'"
                                     onmouseout="">
                                    <p class="text-xl font-semibold mb-1" :style="'font-family: ' + '{{ $font['name'] }}' + ', sans-serif; color:var(--text-1);'">Aa Bb</p>
                                    <p class="text-[11px] font-medium"
                                       :style="fontFamily === '{{ $font['name'] }}' ? 'color:var(--brand);' : 'color:var(--text-3);'">
                                        {{ $font['name'] }}
                                    </p>
                                    @if($font['default'] ?? false)
                                    <span class="text-[10px]" style="color:#10b981;">Default</span>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Live Preview --}}
                    <div class="rounded-xl p-4" style="background:var(--card-sub); border:1px solid var(--border);">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:var(--text-3);">Live Preview</p>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                 :style="'background:' + brandColor">A</div>
                            <div>
                                <p class="text-sm font-semibold" :style="'font-family:' + fontFamily + ', sans-serif; color:var(--text-1);'">Dashboard Overview</p>
                                <p class="text-xs" :style="'font-family:' + fontFamily + ', sans-serif; color:var(--text-3);'">Your workspace at a glance</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="px-4 py-1.5 rounded-lg text-white text-xs font-semibold"
                                 :style="'background:' + brandColor + '; font-family:' + fontFamily + ', sans-serif;'">Primary Button</div>
                            <div class="px-4 py-1.5 rounded-lg text-xs font-semibold"
                                 :style="'color:' + brandColor + '; border:1.5px solid ' + brandColor + '; font-family:' + fontFamily + ', sans-serif;'">Outline</div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="sbtn-primary"
                                style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                            Save Brand Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Logo & Favicon --}}
        <div class="scard">
            <div class="scard-header">
                <p class="text-sm font-semibold" style="color:var(--text-1);">Logo & Favicon</p>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">Appears in the sidebar and browser tab</p>
            </div>
            <div class="scard-body">
                <div class="grid md:grid-cols-2 gap-6">
                    {{-- Logo --}}
                    <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                          x-data="{ preview: '{{ $savedLogo ? Storage::url($savedLogo) : '' }}' }">
                        @csrf
                        <input type="hidden" name="type" value="logo">
                        <label class="slabel">Site Logo</label>
                        <div class="rounded-xl p-5 text-center transition-all cursor-pointer"
                             style="border:2px dashed var(--border);"
                             @click="$refs.logoInput.click()"
                             onmouseover="this.style.borderColor='var(--brand)'"
                             onmouseout="this.style.borderColor='var(--border)'">
                            <template x-if="preview">
                                <img :src="preview" class="w-20 h-20 object-contain mx-auto mb-3 rounded-xl">
                            </template>
                            <template x-if="!preview">
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-3"
                                     style="background:var(--card-sub); border:1px solid var(--border);">
                                    <svg class="w-7 h-7" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </template>
                            <p class="text-sm font-medium" style="color:var(--text-2);">Click to upload logo</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-3);">PNG, SVG, JPG · Max 2MB</p>
                        </div>
                        <input type="file" name="file" x-ref="logoInput" accept="image/*" class="hidden"
                               @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        @if($savedLogo)
                        <p class="mt-2 text-xs" style="color:#16a34a;">✓ Logo uploaded</p>
                        @endif
                    </form>

                    {{-- Favicon --}}
                    <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                          x-data="{ preview: '{{ $savedFav ? Storage::url($savedFav) : '' }}' }">
                        @csrf
                        <input type="hidden" name="type" value="favicon">
                        <label class="slabel">Favicon</label>
                        <div class="rounded-xl p-5 text-center transition-all cursor-pointer"
                             style="border:2px dashed var(--border);"
                             @click="$refs.favInput.click()"
                             onmouseover="this.style.borderColor='var(--brand)'"
                             onmouseout="this.style.borderColor='var(--border)'">
                            <template x-if="preview">
                                <img :src="preview" class="w-14 h-14 object-contain mx-auto mb-3 rounded-xl">
                            </template>
                            <template x-if="!preview">
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-3"
                                     style="background:var(--card-sub); border:1px solid var(--border);">
                                    <svg class="w-7 h-7" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </template>
                            <p class="text-sm font-medium" style="color:var(--text-2);">Click to upload favicon</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-3);">ICO, PNG, SVG · Max 2MB · 32×32px</p>
                        </div>
                        <input type="file" name="file" x-ref="favInput" accept="image/*,.ico" class="hidden"
                               @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        @if($savedFav)
                        <p class="mt-2 text-xs" style="color:#16a34a;">✓ Favicon uploaded</p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ SECURITY ══════════ --}}
    <div x-show="tab === 'security'" class="space-y-5">

        @if(session('status') === 'password-updated')
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Password updated successfully.
        </div>
        @endif

        <div class="scard">
            <div class="scard-header">
                <p class="text-sm font-semibold" style="color:var(--text-1);">Change Password</p>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">Use a strong password with at least 8 characters</p>
            </div>
            <div class="scard-body">
                <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="slabel">Current Password</label>
                        <input type="password" name="current_password"
                               class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                        @error('current_password')<p class="mt-1 text-xs" style="color:#dc2626;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="slabel">New Password</label>
                        <input type="password" name="password" minlength="8"
                               class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                        @error('password')<p class="mt-1 text-xs" style="color:#dc2626;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="slabel">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="sfield" style="background:var(--input-bg); border:1.5px solid var(--border); color:var(--text-1);">
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="sbtn-primary" style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
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
                    setTimeout(() => { this.open = false; this.saveResult = null; }, 2500);
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
                body: JSON.stringify({ provider: providerKey }),
            })
            .then(r => r.json())
            .then(data => { this.testing = false; this.testResult = data; })
            .catch(() => { this.testing = false; this.testResult = { success: false, message: 'Connection failed.' }; });
        },

        doRemove(deleteUrl) {
            if (!confirm('Remove this provider and its API key?')) return;
            fetch(deleteUrl, {
                method:  'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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
