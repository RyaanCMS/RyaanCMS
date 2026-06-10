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

@push('head')
<style>
.sfield{width:100%;font-size:13.5px;font-family:inherit;padding:9px 13px;border-radius:10px;outline:none;transition:border-color .15s,box-shadow .15s;}
.sfield:focus{border-color:var(--brand)!important;box-shadow:0 0 0 3px var(--brand-ring);}
.slabel{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px;}
.sbtn-primary{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:600;color:#fff;border:none;cursor:pointer;transition:all .15s;}
.sbtn-primary:hover{filter:brightness(1.08);transform:translateY(-1px);}
.sbtn-secondary{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;}

/* ─── Settings layout ─── */
.st-wrap{
    display:grid;
    grid-template-columns:210px 1fr;
    gap:24px;
    align-items:start;
    max-width:880px;
}
/* Left nav */
.st-nav{
    background:var(--card-bg);
    border:1px solid var(--border);
    border-radius:14px;
    overflow:hidden;
    box-shadow:var(--shadow);
    position:sticky;
    top:24px;
}
.st-nav-user{
    padding:16px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    gap:10px;
    background:var(--hover-bg);
}
.st-nav-item{
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 14px;
    font-size:13px;
    font-weight:500;
    color:var(--text-2);
    cursor:pointer;
    border:none;
    background:transparent;
    width:100%;
    text-align:left;
    transition:all .13s;
    border-radius:0;
    border-left:3px solid transparent;
}
.st-nav-item:hover:not(.active){background:var(--hover-bg);color:var(--text-1);}
.st-nav-item.active{
    color:var(--brand);
    background:var(--brand-light,#eef2ff);
    border-left-color:var(--brand);
    font-weight:700;
}
.st-nav-ico{
    width:28px;height:28px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    background:var(--hover-bg);
    flex-shrink:0;
}
.st-nav-item.active .st-nav-ico{background:var(--brand);box-shadow:0 2px 6px var(--brand-ring);}
.st-nav-item.active .st-nav-ico svg{stroke:#fff!important;}

/* Content panels */
.st-panel{display:flex;flex-direction:column;gap:16px;}

/* Section card */
.scard{border-radius:14px;overflow:hidden;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow);}
.scard-hd{padding:16px 20px;border-bottom:1px solid var(--border);background:var(--hover-bg);display:flex;align-items:center;gap:10px;}
.scard-hd-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.scard-body{padding:20px;}

/* Section nav separator */
.st-nav-sep{height:1px;background:var(--border);margin:4px 0;}
.st-nav-label{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--text-3);padding:8px 14px 4px;}
</style>
@endpush

@section('content')
<div class="st-wrap"
     x-data="{
        tab: '{{ session('_tab', 'profile') }}',
        brandColor: '{{ $savedColor }}',
        fontFamily: '{{ $savedFont }}'
     }">

    {{-- ══ LEFT NAV ══ --}}
    <nav class="st-nav">
        <div class="st-nav-user">
            <img src="{{ auth()->user()->avatar_url }}"
                 style="width:36px;height:36px;border-radius:10px;border:2px solid var(--brand);box-shadow:0 0 0 3px var(--brand-ring);flex-shrink:0;" alt="">
            <div style="min-width:0;">
                <div style="font-size:12.5px;font-weight:700;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                <div style="font-size:11px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <div style="padding:6px 0;">
            <div class="st-nav-label">Account</div>

            <button @click="tab='profile'" :class="tab==='profile' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico" :style="tab==='profile' ? '' : ''">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                Profile
            </button>
            <button @click="tab='security'" :class="tab==='security' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico" :style="tab==='security' ? '' : ''">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                Security
            </button>

            <div class="st-nav-sep"></div>
            <div class="st-nav-label">Workspace</div>

            <button @click="tab='ai'" :class="tab==='ai' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico" :style="tab==='ai' ? '' : ''">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                AI Providers
            </button>
            <button @click="tab='branding'" :class="tab==='branding' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico" :style="tab==='branding' ? '' : ''">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                Branding
            </button>

            <div class="st-nav-sep"></div>
            <div class="st-nav-label">System</div>

            <a href="{{ route('settings.updates') }}" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                Updates
            </a>

            <div class="st-nav-sep"></div>
            <div class="st-nav-label">More</div>

            <button @click="tab='notifications'" :class="tab==='notifications' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                Notifications
            </button>
            <button @click="tab='integrations'" :class="tab==='integrations' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                Integrations
            </button>
            <button @click="tab='team'" :class="tab==='team' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                Team
            </button>
            <button @click="tab='danger'" :class="tab==='danger' ? 'active' : ''" class="st-nav-item"
                    :style="tab==='danger' ? 'color:#ef4444;background:#fef2f2;border-left-color:#ef4444;' : ''">
                <div class="st-nav-ico" :style="tab==='danger' ? 'background:#fecaca;' : ''">
                    <svg style="width:14px;height:14px;" :style="tab==='danger' ? 'stroke:#ef4444' : 'stroke:#94a3b8'" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                Danger Zone
            </button>
        </div>
    </nav>

    {{-- ══ CONTENT ══ --}}
    <div>

        {{-- ──── PROFILE ──── --}}
        <div x-show="tab === 'profile'" class="st-panel">
            @if(session('status') === 'profile-updated')
            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;font-size:13px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;">
                <svg style="width:16px;height:16px;flex-shrink:0;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Profile updated successfully.
            </div>
            @endif

            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#eef2ff;">
                        <svg style="width:15px;height:15px;stroke:#6366f1" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">Profile Information</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Update your name, email, and username</div>
                    </div>
                </div>
                <div class="scard-body">
                    {{-- Avatar row --}}
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid #f1f5f9;">
                        <img src="{{ auth()->user()->avatar_url }}"
                             style="width:56px;height:56px;border-radius:14px;border:2px solid #e2e8f0;" alt="Avatar">
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ auth()->user()->name }}</div>
                            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ auth()->user()->email }}</div>
                            <span style="display:inline-block;margin-top:5px;padding:2px 10px;border-radius:99px;font-size:10px;font-weight:700;background:var(--brand-light,#eef2ff);color:var(--brand);">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('settings.profile') }}" style="display:flex;flex-direction:column;gap:16px;">
                        @csrf @method('PUT')
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div>
                                <label class="slabel">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                       class="sfield" style="background:#fafbff;border:1.5px solid #e2e8f0;color:#0f172a;">
                                @error('name')<p style="margin-top:4px;font-size:11.5px;color:#dc2626;">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="slabel">Username</label>
                                <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}"
                                       placeholder="@username"
                                       class="sfield" style="background:#fafbff;border:1.5px solid #e2e8f0;color:#0f172a;">
                            </div>
                        </div>
                        <div>
                            <label class="slabel">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                   class="sfield" style="background:#fafbff;border:1.5px solid #e2e8f0;color:#0f172a;">
                            @error('email')<p style="margin-top:4px;font-size:11.5px;color:#dc2626;">{{ $message }}</p>@enderror
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="sbtn-primary" style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ──── SECURITY ──── --}}
        <div x-show="tab === 'security'" class="st-panel">
            @if(session('status') === 'password-updated')
            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;font-size:13px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;">
                <svg style="width:16px;height:16px;flex-shrink:0;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Password updated successfully.
            </div>
            @endif
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#fdf4ff;">
                        <svg style="width:15px;height:15px;stroke:#8b5cf6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">Change Password</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Use a strong password with at least 8 characters</div>
                    </div>
                </div>
                <div class="scard-body">
                    <form method="POST" action="{{ route('settings.password') }}" style="display:flex;flex-direction:column;gap:16px;">
                        @csrf @method('PUT')
                        <div>
                            <label class="slabel">Current Password</label>
                            <input type="password" name="current_password"
                                   class="sfield" style="background:#fafbff;border:1.5px solid #e2e8f0;color:#0f172a;">
                            @error('current_password')<p style="margin-top:4px;font-size:11.5px;color:#dc2626;">{{ $message }}</p>@enderror
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div>
                                <label class="slabel">New Password</label>
                                <input type="password" name="password" minlength="8"
                                       class="sfield" style="background:#fafbff;border:1.5px solid #e2e8f0;color:#0f172a;">
                                @error('password')<p style="margin-top:4px;font-size:11.5px;color:#dc2626;">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="slabel">Confirm Password</label>
                                <input type="password" name="password_confirmation"
                                       class="sfield" style="background:#fafbff;border:1.5px solid #e2e8f0;color:#0f172a;">
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="sbtn-primary" style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ──── AI PROVIDERS ──── --}}
        <div x-show="tab === 'ai'" class="st-panel">
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#fffbeb;">
                        <svg style="width:15px;height:15px;stroke:#d97706" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">AI Provider Keys</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Connect AI accounts to enable code generation</div>
                    </div>
                </div>
                <div class="scard-body" style="display:flex;flex-direction:column;gap:10px;">
                    @php
                        $grouped = collect($allProviders)->groupBy(fn($p) => $p['category'] ?? 'text', preserveKeys: true);
                        $categoryTitles = ['text' => 'Text Generation', 'voice' => 'Voice & Audio', 'local' => 'Local / Self-Hosted'];
                        $categoryIcons  = ['text' => '🧠', 'voice' => '🎙️', 'local' => '💻'];
                    @endphp
                    @foreach($grouped as $catKey => $catProviders)
                    <div>
                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#cbd5e1;padding:0 0 8px;">
                            {{ $categoryIcons[$catKey] ?? '' }} {{ $categoryTitles[$catKey] ?? ucfirst($catKey) }}
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
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
                        <div style="border-radius:12px;padding:14px;transition:all .13s;"
                             x-data="providerCard({{ $isConfigured ? 'true' : 'false' }}, '{{ $savedProvider?->id ?? '' }}', '{{ $key }}')"
                             :style="connected ? 'border:1.5px solid #a7f3d0;background:#f0fdf4;' : 'border:1.5px solid #e8ecf0;background:#fafbff;'">

                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                                    <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;transition:all .13s;"
                                         :style="connected ? 'background:#dcfce7;border:1px solid #a7f3d0;' : 'background:#f1f5f9;border:1px solid #e2e8f0;'">
                                        {{ $providerEmojis[$key] ?? '🔧' }}
                                    </div>
                                    <div style="min-width:0;">
                                        <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ $provider['name'] }}</div>
                                        <div style="font-size:11px;margin-top:1px;transition:color .13s;"
                                             :style="connected ? 'color:#15803d;' : 'color:#94a3b8;'"
                                             x-text="connected ? '✓ Connected' : 'Not configured'"></div>
                                        @if($link)
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                           style="display:inline-flex;align-items:center;gap:4px;font-size:10px;margin-top:1px;color:var(--brand);">
                                            <svg style="width:9px;height:9px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            {{ $link['label'] }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                    <button x-show="connected" x-cloak @click="runTest()" :disabled="testing"
                                            style="padding:5px 12px;border-radius:8px;font-size:11.5px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .13s;disabled:opacity:.5;"
                                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                                        <span x-text="testing ? 'Testing…' : 'Test'"></span>
                                    </button>
                                    <button @click="open = !open"
                                            style="padding:5px 12px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .13s;border:1px solid;"
                                            :style="connected
                                                ? 'background:#dcfce7;border-color:#a7f3d0;color:#15803d;'
                                                : 'background:var(--brand-light,#eef2ff);border-color:var(--brand-ring,#c7d2fe);color:var(--brand);'">
                                        <span x-text="connected ? '✓ Update' : '+ Connect'"></span>
                                    </button>
                                </div>
                            </div>

                            <div x-show="testResult || saveResult" style="margin-top:10px;padding:10px 12px;border-radius:9px;font-size:12px;"
                                 :style="(testResult?.success || saveResult?.success)
                                    ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;'
                                    : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;'">
                                <span x-text="testResult?.message || saveResult?.message"></span>
                            </div>

                            <div x-show="open" x-transition style="margin-top:12px;padding-top:12px;border-top:1px solid #e8ecf0;display:flex;flex-direction:column;gap:10px;">
                                <input type="hidden" id="provider_key_{{ $key }}" value="{{ $key }}">
                                <input type="hidden" id="csrf_{{ $key }}" value="{{ csrf_token() }}">

                                @if($key !== 'ollama')
                                <div>
                                    <label class="slabel">API Key</label>
                                    <input type="password" id="api_key_{{ $key }}"
                                           :placeholder="connected ? '•••••••• (leave blank to keep current)' : 'Enter {{ $provider['name'] }} API key'"
                                           class="sfield" style="background:#fff;border:1.5px solid #e2e8f0;color:#0f172a;">
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
                                           class="sfield" style="background:#fff;border:1.5px solid #e2e8f0;color:#0f172a;">
                                </div>
                                @endif

                                <div>
                                    <label class="slabel">Default Model</label>
                                    <select id="model_{{ $key }}"
                                            class="sfield" style="background:#fff;border:1.5px solid #e2e8f0;color:#0f172a;appearance:none;cursor:pointer;">
                                        @foreach($provider['models'] as $mKey => $mName)
                                        <option value="{{ $mKey }}" {{ ($savedProvider?->default_model === $mKey) ? 'selected' : '' }}>{{ $mName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" id="default_{{ $key }}" style="width:14px;height:14px;border-radius:4px;accent-color:var(--brand);">
                                    <label for="default_{{ $key }}" style="font-size:12.5px;color:#475569;cursor:pointer;">Set as default AI provider</label>
                                </div>

                                <div style="display:flex;align-items:center;gap:8px;padding-top:2px;">
                                    <button @click="doSave('{{ $key }}')" :disabled="saving"
                                            class="sbtn-primary"
                                            style="font-size:12px;padding:7px 16px;"
                                            :style="saveResult?.success
                                                ? 'background:#16a34a;box-shadow:none;'
                                                : 'background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);'">
                                        <svg x-show="saving" style="width:12px;height:12px;flex-shrink:0;" class="animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span x-text="saving ? 'Saving…' : (saveResult?.success ? '✓ Saved!' : 'Connect')"></span>
                                    </button>
                                    <button type="button" @click="open=false; saveResult=null"
                                            style="padding:7px 14px;border-radius:9px;font-size:12px;font-weight:500;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;cursor:pointer;"
                                            onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                                        Cancel
                                    </button>
                                    <button type="button" x-show="connected" x-cloak
                                            @click="doRemove('{{ route('settings.ai-provider.delete', ['aiProvider' => $savedProvider?->id ?? 0]) }}')"
                                            style="margin-left:auto;padding:7px 14px;border-radius:9px;font-size:12px;font-weight:500;color:#ef4444;border:none;background:transparent;cursor:pointer;"
                                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
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

        {{-- ──── BRANDING ──── --}}
        <div x-show="tab === 'branding'" class="st-panel">
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#ecfdf5;">
                        <svg style="width:15px;height:15px;stroke:#10b981" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">Brand Colors & Typography</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Customize your RyaanCMS dashboard appearance</div>
                    </div>
                </div>
                <div class="scard-body">
                    <form method="POST" action="{{ route('settings.branding') }}" style="display:flex;flex-direction:column;gap:20px;">
                        @csrf

                        <div>
                            <label class="slabel">Primary Brand Color</label>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="color" name="primary_color" x-model="brandColor"
                                       style="width:48px;height:48px;border-radius:12px;cursor:pointer;border:2px solid #e2e8f0;padding:3px;background:none;">
                                <div style="flex:1;display:flex;align-items:center;gap:10px;border-radius:11px;padding:10px 14px;background:#f8fafc;border:1.5px solid #e2e8f0;">
                                    <div style="width:20px;height:20px;border-radius:6px;flex-shrink:0;transition:background .2s;" :style="'background:' + brandColor"></div>
                                    <input type="text" x-model="brandColor"
                                           @input="if(/^#[0-9A-Fa-f]{6}$/.test($event.target.value)) brandColor=$event.target.value"
                                           style="flex:1;background:transparent;border:none;outline:none;font-size:13px;font-weight:700;font-family:monospace;color:#0f172a;text-transform:uppercase;"
                                           maxlength="7" placeholder="#6366f1">
                                </div>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                                @foreach([
                                    ['color'=>'#6366f1','name'=>'Indigo'],['color'=>'#8b5cf6','name'=>'Violet'],
                                    ['color'=>'#ec4899','name'=>'Pink'],  ['color'=>'#f97316','name'=>'Orange'],
                                    ['color'=>'#10b981','name'=>'Emerald'],['color'=>'#3b82f6','name'=>'Blue'],
                                    ['color'=>'#ef4444','name'=>'Red'],   ['color'=>'#eab308','name'=>'Yellow'],
                                    ['color'=>'#14b8a6','name'=>'Teal'],  ['color'=>'#a855f7','name'=>'Purple'],
                                ] as $preset)
                                <button type="button" @click="brandColor = '{{ $preset['color'] }}'"
                                        style="width:30px;height:30px;border-radius:9px;cursor:pointer;transition:all .15s;background:{{ $preset['color'] }};"
                                        :style="brandColor === '{{ $preset['color'] }}' ? 'transform:scale(1.15);outline:2.5px solid {{ $preset['color'] }};outline-offset:2px;' : 'opacity:.8;border:2px solid transparent;'"
                                        title="{{ $preset['name'] }}"></button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="slabel">Font Family</label>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                                @foreach([
                                    ['name'=>'Poppins','default'=>true],['name'=>'Inter'],['name'=>'DM Sans'],
                                    ['name'=>'Nunito'],['name'=>'Roboto'],['name'=>'Open Sans'],
                                ] as $font)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="font_family" value="{{ $font['name'] }}" x-model="fontFamily" style="display:none;">
                                    <div style="border-radius:12px;padding:14px;text-align:center;transition:all .15s;"
                                         :style="fontFamily === '{{ $font['name'] }}'
                                            ? 'border:2px solid var(--brand);background:var(--brand-light,#eef2ff);box-shadow:0 0 0 3px var(--brand-ring,#c7d2fe40);'
                                            : 'border:2px solid #e8ecf0;background:#fafbff;'">
                                        <div style="font-size:20px;font-weight:700;margin-bottom:3px;"
                                             :style="'font-family:{{ $font['name'] }},sans-serif;color:' + (fontFamily==='{{ $font['name'] }}' ? 'var(--brand)' : '#334155')">
                                            Aa Bb
                                        </div>
                                        <div style="font-size:10.5px;font-weight:600;"
                                             :style="fontFamily === '{{ $font['name'] }}' ? 'color:var(--brand);' : 'color:#94a3b8;'">
                                            {{ $font['name'] }}{{ ($font['default'] ?? false) ? ' ✓' : '' }}
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div style="border-radius:12px;padding:16px;background:#f8fafc;border:1px solid #e8ecf0;">
                            <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#cbd5e1;margin-bottom:12px;">Live Preview</div>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                                <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:800;flex-shrink:0;"
                                     :style="'background:' + brandColor">A</div>
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#0f172a;" :style="'font-family:' + fontFamily + ',sans-serif;'">Dashboard Overview</div>
                                    <div style="font-size:11.5px;color:#94a3b8;" :style="'font-family:' + fontFamily + ',sans-serif;'">Your workspace at a glance</div>
                                </div>
                            </div>
                            <div style="display:flex;gap:8px;">
                                <div style="padding:7px 16px;border-radius:9px;font-size:12px;font-weight:700;color:#fff;"
                                     :style="'background:' + brandColor + ';font-family:' + fontFamily + ',sans-serif;'">Primary</div>
                                <div style="padding:7px 16px;border-radius:9px;font-size:12px;font-weight:700;border:1.5px solid;"
                                     :style="'color:' + brandColor + ';border-color:' + brandColor + ';font-family:' + fontFamily + ',sans-serif;'">Outline</div>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="sbtn-primary" style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                                Save Brand Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#f0f9ff;">
                        <svg style="width:15px;height:15px;stroke:#0ea5e9" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">Logo & Favicon</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Appears in the sidebar and browser tab</div>
                    </div>
                </div>
                <div class="scard-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                              x-data="{ preview: '{{ $savedLogo ? Storage::url($savedLogo) : '' }}' }">
                            @csrf
                            <input type="hidden" name="type" value="logo">
                            <label class="slabel">Site Logo</label>
                            <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:border-color .15s;"
                                 @click="$refs.logoInput.click()"
                                 onmouseover="this.style.borderColor='var(--brand)'" onmouseout="this.style.borderColor='#e2e8f0'">
                                <template x-if="preview">
                                    <img :src="preview" style="width:64px;height:64px;object-fit:contain;margin:0 auto 10px;border-radius:10px;display:block;">
                                </template>
                                <template x-if="!preview">
                                    <div style="width:48px;height:48px;border-radius:12px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                        <svg style="width:22px;height:22px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </template>
                                <div style="font-size:12.5px;font-weight:600;color:#475569;">Click to upload</div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">PNG, SVG, JPG · Max 2MB</div>
                            </div>
                            <input type="file" name="file" x-ref="logoInput" accept="image/*" style="display:none;"
                                   @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            @if($savedLogo)<div style="margin-top:6px;font-size:11px;color:#16a34a;">✓ Logo uploaded</div>@endif
                        </form>

                        <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                              x-data="{ preview: '{{ $savedFav ? Storage::url($savedFav) : '' }}' }">
                            @csrf
                            <input type="hidden" name="type" value="favicon">
                            <label class="slabel">Favicon</label>
                            <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:border-color .15s;"
                                 @click="$refs.favInput.click()"
                                 onmouseover="this.style.borderColor='var(--brand)'" onmouseout="this.style.borderColor='#e2e8f0'">
                                <template x-if="preview">
                                    <img :src="preview" style="width:48px;height:48px;object-fit:contain;margin:0 auto 10px;border-radius:10px;display:block;">
                                </template>
                                <template x-if="!preview">
                                    <div style="width:48px;height:48px;border-radius:12px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                        <svg style="width:22px;height:22px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                </template>
                                <div style="font-size:12.5px;font-weight:600;color:#475569;">Click to upload</div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">ICO, PNG, SVG · 32×32px</div>
                            </div>
                            <input type="file" name="file" x-ref="favInput" accept="image/*,.ico" style="display:none;"
                                   @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            @if($savedFav)<div style="margin-top:6px;font-size:11px;color:#16a34a;">✓ Favicon uploaded</div>@endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /content --}}
</div>{{-- /st-wrap --}}
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
