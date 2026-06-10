@extends('layouts.app')
@section('title', 'Settings')
@section('header', 'Settings')

@php
    $userId      = auth()->id();
    $savedColor  = \App\Models\Setting::get('branding.primary_color', '#6366f1', $userId);
    // Update tab data (fast, no HTTP)
    $currentVersion = config('version.current', '1.0.0');
    $updateServerInfo = [
        'php'      => PHP_VERSION,
        'laravel'  => app()->version(),
        'zip'      => class_exists('ZipArchive'),
        'writable' => is_writable(storage_path()),
    ];
    $savedFont   = \App\Models\Setting::get('branding.font_family',   'Poppins', $userId);
    $savedLogo   = \App\Models\Setting::get('branding.logo_path',     null,      $userId);
    $savedFav    = \App\Models\Setting::get('branding.favicon_path',  null,      $userId);
    $showDashMenu      = \App\Models\Setting::get('system.show_dashboard_menu',    true,  $userId);
    $showDashSidebar   = \App\Models\Setting::get('system.show_dashboard_sidebar', true,  $userId);
    $sidebarAutoHide   = \App\Models\Setting::get('system.sidebar_auto_hide',      false, $userId);
    $aiBuilderDocs     = \App\Models\Setting::get('ai_builder.auto_docs',          false, $userId);
@endphp

@push('head')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;600;700&family=DM+Sans:wght@400;600;700&family=Nunito:wght@400;600;700&family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&display=swap');
.sfield{width:100%;font-size:13.5px;font-family:inherit;padding:9px 13px;border-radius:10px;outline:none;transition:border-color .15s,box-shadow .15s;}
.sfield:focus{border-color:var(--brand)!important;box-shadow:0 0 0 3px var(--brand-ring);}
.slabel{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px;}
.sbtn-primary{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:600;background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 28%,transparent);cursor:pointer;transition:all .15s;}
.sbtn-primary:hover{background:var(--brand);color:#fff;border-color:var(--brand);box-shadow:0 4px 14px var(--brand-ring);transform:translateY(-1px);}
.sbtn-secondary{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;}

/* Settings layout */
.st-wrap{
    display:grid;
    grid-template-columns:210px 1fr;
    gap:24px;
    align-items:start;
    max-width:880px;
    width:100%;
}
.st-wrap-wide{
    max-width:none;
    grid-template-columns:210px minmax(0,1fr);
}
.st-wrap > div{
    min-width:0;
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
@media (max-width:900px){
    .st-wrap,
    .st-wrap-wide{
        grid-template-columns:1fr;
        max-width:none;
    }
    .st-nav{
        position:static;
    }
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

/* System-config toggle switch */
.sys-toggle-btn{position:relative;display:inline-flex;height:24px;width:44px;border-radius:99px;border:none;cursor:pointer;transition:background .2s;flex-shrink:0;background:#d1d5db;}
.sys-toggle-on{background:var(--brand);}
.sys-toggle-disabled{opacity:.4;cursor:not-allowed;}
.sys-toggle-thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .2s;transform:translateX(0);}
.sys-toggle-thumb-on{transform:translateX(20px);}

/* --- Branding panel --- */
.bk-preview-wrap{border-radius:16px;overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow);}
.bk-preview-bar{padding:11px 16px;background:var(--hover-bg);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.bk-preview-dot{width:9px;height:9px;border-radius:50%;display:inline-block;}
.bk-sidebar-item{padding:6px 10px;display:flex;align-items:center;gap:6px;border-radius:7px;transition:background .1s;}
.bk-color-swatch{width:36px;height:36px;border-radius:10px;cursor:pointer;border:2px solid transparent;transition:all .15s;flex-shrink:0;}
.bk-color-swatch:hover{transform:scale(1.1);}
.bk-color-swatch.selected{outline:3px solid var(--brand);outline-offset:2px;transform:scale(1.12);}
.bk-shade{flex:1;height:22px;border-radius:6px;cursor:pointer;transition:transform .12s;}
.bk-shade:hover{transform:scaleY(1.2);}
.bk-font-card{border-radius:12px;padding:14px;text-align:center;cursor:pointer;transition:all .15s;border:2px solid #e8ecf0;background:#fafbff;}
.bk-font-card:hover{border-color:color-mix(in srgb,var(--brand) 40%,#e8ecf0);}
.bk-font-card.selected{border-color:var(--brand);background:var(--brand-light,#eef2ff);box-shadow:0 0 0 3px var(--brand-ring,#c7d2fe40);}
.bk-upload-zone{border:2px dashed #e2e8f0;border-radius:14px;padding:24px 16px;text-align:center;cursor:pointer;transition:all .15s;background:#fafbff;}
.bk-upload-zone:hover{border-color:var(--brand);background:var(--brand-light,#eef2ff);}
.bk-upload-drag{border-color:var(--brand)!important;background:var(--brand-light,#eef2ff)!important;box-shadow:0 0 0 3px var(--brand-ring);transform:scale(1.01);}
.bk-hex-input{flex:1;border:none;background:transparent;font-size:13px;font-family:monospace;font-weight:700;color:var(--text-1);outline:none;text-transform:uppercase;min-width:0;}
</style>
@endpush

@section('content')
<div class="st-wrap"
     :class="{ 'st-wrap-wide': tab === 'ai' || tab === 'team' }"
     x-data="{
        tab: '{{ session('_tab', 'profile') }}',
        brandColor: '{{ $savedColor }}',
        fontFamily: '{{ $savedFont }}',
        brandSaving: false,
        brandSaved: false,
        sysShowMenu: {{ $showDashMenu ? 'true' : 'false' }},
        sysShowSidebar: {{ $showDashSidebar ? 'true' : 'false' }},
        sysAutoHideSidebar: {{ $sidebarAutoHide ? 'true' : 'false' }},
        sysAiBuilderDocs: {{ $aiBuilderDocs ? 'true' : 'false' }},
        sysSaving: false,
        sysSaved: false,
        init() {
            const valid = ['profile','ai','branding','updates','system_config','notifications','integrations','team','danger'];
            const resolveTab = h => ({ 'ai-providers': 'ai', 'ai_provider': 'ai', 'ai-provider': 'ai' }[h] || h);
            const hash = resolveTab(window.location.hash.replace('#', ''));
            if (hash && valid.includes(hash)) this.tab = hash;
            this.$watch('tab', t => history.replaceState(null, '', '#' + t));
            window.addEventListener('hashchange', () => {
                const h = resolveTab(window.location.hash.replace('#', ''));
                if (h && valid.includes(h)) this.tab = h;
            });
            this.$watch('brandColor', v => { if(/^#[0-9a-fA-F]{6}$/.test(v)) this.hexInput=v.replace('#','').toUpperCase(); });
        },
        async saveSystemConfig() {
            this.sysSaving = true;
            await fetch('{{ route('settings.system-config') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ show_dashboard_menu: this.sysShowMenu, show_dashboard_sidebar: this.sysShowSidebar, sidebar_auto_hide: this.sysAutoHideSidebar, ai_builder_auto_docs: this.sysAiBuilderDocs })
            });
            this.sysSaving = false;
            this.sysSaved = true;
            setTimeout(() => this.sysSaved = false, 2500);
        },
        async saveBrandingAjax() {
            this.brandSaving = true;
            try {
                const fd = new FormData();
                fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                fd.append('primary_color', this.brandColor);
                fd.append('font_family', this.fontFamily);
                const res = await fetch('{{ route('settings.branding') }}', { method: 'POST', body: fd });
                if (res.ok || res.status === 302) {
                    this.brandSaved = true;
                    setTimeout(() => this.brandSaved = false, 2500);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Branding saved successfully.' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not save branding.' } }));
                }
            } catch(e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Network error saving branding.' } }));
            }
            this.brandSaving = false;
        },
        hexInput: '{{ ltrim($savedColor, '#') }}',
        brandCopied: false,
        hexToHsl(hex) {
            const r=parseInt(hex.slice(1,3),16)/255, g=parseInt(hex.slice(3,5),16)/255, b=parseInt(hex.slice(5,7),16)/255;
            const max=Math.max(r,g,b), min=Math.min(r,g,b); let h,s,l=(max+min)/2;
            if(max===min){h=s=0;} else {
                const d=max-min; s=l>.5?d/(2-max-min):d/(max+min);
                if(max===r) h=((g-b)/d+(g<b?6:0))/6;
                else if(max===g) h=((b-r)/d+2)/6;
                else h=((r-g)/d+4)/6;
            }
            return [Math.round(h*360), Math.round(s*100), Math.round(l*100)];
        },
        hslToHex(h,s,l) {
            s/=100; l/=100; const a=s*Math.min(l,1-l);
            const f=n=>{const k=(n+h/30)%12;const c=l-a*Math.max(Math.min(k-3,9-k,1),-1);return Math.round(255*c).toString(16).padStart(2,'0');};
            return '#'+f(0)+f(8)+f(4);
        },
        getShades(hex) {
            if(!hex||!/^#[0-9a-fA-F]{6}$/.test(hex)) return [];
            const [h,s]=this.hexToHsl(hex);
            return [93,84,74,63,52,41,32,23,14].map(l=>this.hslToHex(h,s,l));
        },
        pickColor(hex) { this.brandColor=hex; this.hexInput=(/^#[0-9a-fA-F]{6}$/.test(hex)?hex.replace('#',''):'').toUpperCase(); },
        onHexInput(v) { const c=v.replace(/[^0-9a-fA-F]/g,'').slice(0,6); this.hexInput=c.toUpperCase(); if(c.length===6) this.brandColor='#'+c; },
        copyHex() { navigator.clipboard.writeText(this.brandColor).then(()=>{this.brandCopied=true;setTimeout(()=>this.brandCopied=false,1800);}); }
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

            <button @click="tab='updates'" :class="tab==='updates' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico">
                    <svg style="width:14px;height:14px;" :style="tab==='updates' ? 'stroke:#fff' : 'stroke:#94a3b8'" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                Updates
            </button>
            <button @click="tab='system_config'" :class="tab==='system_config' ? 'active' : ''" class="st-nav-item">
                <div class="st-nav-ico">
                    <svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                System Config
            </button>

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

        {{-- PROFILE --}}
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
                    {{-- Avatar row with upload --}}
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid #f1f5f9;">
                        <form method="POST" action="{{ route('settings.profile.avatar') }}" enctype="multipart/form-data"
                              x-data="{ previewUrl: '{{ auth()->user()->avatar_url }}' }"
                              style="position:relative;flex-shrink:0;">
                            @csrf
                            <div style="position:relative;cursor:pointer;" @click="$refs.avatarInput.click()">
                                <img :src="previewUrl"
                                     style="width:64px;height:64px;border-radius:14px;border:2px solid #e2e8f0;display:block;" alt="Avatar">
                                <div style="position:absolute;inset:0;border-radius:14px;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s;"
                                     @mouseenter="$el.style.opacity='1'" @mouseleave="$el.style.opacity='0'">
                                    <svg style="width:18px;height:18px;stroke:#fff" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                            </div>
                            <input type="file" name="avatar" x-ref="avatarInput" accept="image/*" style="display:none;"
                                   @change="previewUrl = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        </form>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ auth()->user()->name }}</div>
                            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ auth()->user()->email }}</div>
                            <span style="display:inline-block;margin-top:5px;padding:2px 10px;border-radius:99px;font-size:10px;font-weight:700;background:var(--brand-light,#eef2ff);color:var(--brand);">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                            <div style="margin-top:4px;font-size:10.5px;color:#94a3b8;">Click avatar to change photo</div>
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
                            <button type="submit" class="sbtn-primary">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SECURITY --}}
        <div x-show="tab === 'profile'" class="st-panel">
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
                            <button type="submit" class="sbtn-primary">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- AI PROVIDERS --}}
        <div x-show="tab === 'ai'" class="st-panel">
            <script type="application/json" id="ai-provider-rows">@json($providerRows ?? [])</script>
            <div class="dt-wrap" x-data="aiProviderTable()">
                <div class="dt-head">
                    <div>
                        <h2 class="dt-title">AI Providers Table</h2>
                        <p class="dt-subtitle">Configure providers, manage multiple API keys, and review failover readiness.</p>
                    </div>
                </div>
                <div class="dt-toolbar" style="flex-direction:column;align-items:stretch;gap:10px;">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="dt-search">
                            <svg class="dt-search-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input x-ref="searchInput" x-model="search" @input="page = 1" @keydown.escape="search = ''; page = 1"
                                   type="text" placeholder="Search provider, category, model, status..." class="dt-search-input">
                            <button x-show="search" @click="search = ''; page = 1; $refs.searchInput.focus()" class="dt-clear" title="Clear (Esc)" x-cloak>
                                <svg style="width:9px;height:9px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="dt-per-page">
                            <span>Show</span>
                            <select x-model.number="perPage" @change="page = 1">
                                <template x-for="n in perPageOpts" :key="n">
                                    <option :value="n" x-text="n"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex rounded-xl overflow-hidden flex-shrink-0" style="border:1px solid var(--border);">
                            <button @click="statusFilter = 'all'; page = 1" class="px-3 py-1.5 text-xs transition-all"
                                    :style="statusFilter === 'all' ? 'background:var(--brand);color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">All</button>
                            <button @click="statusFilter = 'connected'; page = 1" class="px-3 py-1.5 text-xs transition-all border-l" style="border-color:var(--border);"
                                    :style="statusFilter === 'connected' ? 'background:#15803d;color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">Connected</button>
                            <button @click="statusFilter = 'missing'; page = 1" class="px-3 py-1.5 text-xs transition-all border-l" style="border-color:var(--border);"
                                    :style="statusFilter === 'missing' ? 'background:#64748b;color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">Missing</button>
                        </div>

                        <div class="dt-per-page">
                            <span>Sort</span>
                            <select x-model="sortBy" @change="page = 1">
                                <option value="active_first">Active & Connected First</option>
                                <option value="name_asc">Name A to Z</option>
                                <option value="name_desc">Name Z to A</option>
                                <option value="connected">Connected First</option>
                                <option value="category">Category</option>
                            </select>
                        </div>

                        <span class="dt-count">
                            <span x-text="filtered.length"></span>
                            <span x-text="filtered.length === 1 ? ' provider' : ' providers'"></span>
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="pill in categoryPills" :key="pill.val">
                            <button @click="categoryFilter = categoryFilter === pill.val ? '' : pill.val; page = 1"
                                    class="sys-pill" :class="categoryFilter === pill.val ? 'sys-pill-on' : ''">
                                <span x-text="pill.label"></span>
                                <span class="ml-1 opacity-70" x-text="'(' + categoryCount(pill.val) + ')'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="dt-table">
                        <thead>
                            <tr>
                                <th class="dt-th">#</th>
                                <th class="dt-th">Provider</th>
                                <th class="dt-th">Category</th>
                                <th class="dt-th">Model</th>
                                <th class="dt-th">Keys</th>
                                <th class="dt-th">Connection</th>
                                <th class="dt-th">Active</th>
                                <th class="dt-th">Updated</th>
                                <th class="dt-th" style="text-align:right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="paginated.length === 0">
                                <tr>
                                    <td colspan="8" class="dt-empty">
                                        <svg class="w-10 h-10 mb-3 mx-auto" style="color:var(--border)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm font-medium">No AI providers found</p>
                                        <p class="text-xs mt-1">Try a different search or filter</p>
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(provider, i) in paginated" :key="provider.provider">
                                <tr class="dt-tr">
                                    <td class="dt-td" style="color:var(--text-3)" x-text="(page - 1) * perPage + i + 1"></td>
                                    <td class="dt-td">
                                        <p class="font-semibold text-sm" style="color:var(--text-1)" x-html="highlight(provider.name)"></p>
                                        <p class="text-[11px] font-mono mt-0.5" style="color:var(--text-3)" x-html="highlight(provider.provider)"></p>
                                    </td>
                                    <td class="dt-td">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                              :style="categoryStyle(provider.category)" x-text="provider.category_label"></span>
                                    </td>
                                    <td class="dt-td">
                                        <p class="text-sm font-semibold" style="color:var(--text-2)" x-text="provider.default_model || 'Not selected'"></p>
                                        <p class="text-[11px] mt-0.5" style="color:var(--text-3)" x-text="provider.model_count + ' models'"></p>
                                    </td>
                                    <td class="dt-td">
                                        <div x-show="provider.has_key && provider.key_count > 0" class="flex items-center gap-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                                  style="background:color-mix(in srgb,var(--brand) 10%,transparent);color:var(--brand);border:1px solid color-mix(in srgb,var(--brand) 25%,transparent)">
                                                <svg style="width:9px;height:9px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                </svg>
                                                <span x-text="provider.key_count"></span>
                                            </span>
                                        </div>
                                        <span x-show="!provider.has_key || provider.key_count === 0" style="color:var(--text-3);font-size:12px;">-</span>
                                    </td>
                                    {{-- Connection status --}}
                                    <td class="dt-td">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                              :style="provider.has_key ? 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0' : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)'">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="provider.has_key ? 'bg-green-500' : 'bg-gray-400'"></span>
                                            <span x-text="provider.has_key ? (provider.is_default ? 'Default' : 'Connected') : 'Not configured'"></span>
                                        </span>
                                    </td>
                                    {{-- Active/Inactive toggle --}}
                                    <td class="dt-td">
                                        <template x-if="provider.has_key && provider.provider_id">
                                            <button @click="toggleProviderActive(provider)"
                                                    :title="provider.is_active ? 'Click to deactivate' : 'Click to activate'"
                                                    :disabled="togglingActive === provider.provider"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer border"
                                                    :style="provider.is_active
                                                        ? 'background:#f0fdf4;color:#15803d;border-color:#bbf7d0;'
                                                        : 'background:#fef2f2;color:#ef4444;border-color:#fecaca;'"
                                                    style="border:1px solid;">
                                                <span class="w-1.5 h-1.5 rounded-full"
                                                      :class="provider.is_active ? 'bg-green-500' : 'bg-red-400'"></span>
                                                <span x-text="togglingActive === provider.provider ? '...' : (provider.is_active ? 'Active' : 'Inactive')"></span>
                                            </button>
                                        </template>
                                        <span x-show="!provider.has_key || !provider.provider_id" style="color:var(--text-3);font-size:12px;">-</span>
                                    </td>
                                    <td class="dt-td" style="color:var(--text-3)" x-text="fmtDate(provider.updated_at)"></td>
                                    <td class="dt-td" style="text-align:right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button x-show="provider.configured" x-cloak @click="runTest(provider)" :disabled="testing === provider.provider"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                                    style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
                                                <span x-text="testing === provider.provider ? 'Testing...' : 'Test'"></span>
                                            </button>
                                            <button @click="openEdit(provider)"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                                    style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button x-show="provider.has_key" x-cloak @click="removeProvider(provider)"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                                    style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="dt-foot">
                    <span class="dt-foot-info" x-text="dtInfo(filtered.length, page, perPage)"></span>
                    <div class="dt-pages" x-show="totalPages > 1">
                        <button class="dt-page-btn" @click="page = Math.max(1, page - 1)" :disabled="page === 1">&lsaquo;</button>
                        <template x-for="p in pageRange" :key="p + '-' + page">
                            <template x-if="p === '...'"><span class="dt-page-dot">...</span></template>
                            <template x-if="p !== '...'"><button class="dt-page-btn" :class="p === page ? 'dt-page-on' : ''" @click="page = p" x-text="p"></button></template>
                        </template>
                        <button class="dt-page-btn" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">&rsaquo;</button>
                    </div>
                </div>

                <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);" @click.self="closeModal()" x-cloak>
                    <div class="w-full max-w-lg rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);" @click.stop>
                        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                            <div>
                                <h3 class="font-bold text-base" style="color:var(--text-1)" x-text="modalTitle"></h3>
                                <p class="text-xs mt-0.5" style="color:var(--text-3)"
                                   x-text="editing?.provider + (editing?.configured && editing?.updated_at ? ' · Updated ' + fmtDate(editing?.updated_at) : '')"></p>
                            </div>
                            <button @click="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors" style="color:var(--text-3);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="saveProvider()" class="p-6 space-y-4">
                            <div x-show="editing?.provider !== 'ollama'">
                                {{-- Configured: show "key saved" pill + toggle to update --}}
                                <div x-show="editing?.configured">
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                        <div style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;">
                                            <svg style="width:12px;height:12px;color:#16a34a;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            <span style="font-size:12px;font-weight:600;color:#16a34a;">API key is saved</span>
                                        </div>
                                        <button type="button" @click="form.showKeyField = !form.showKeyField"
                                                style="font-size:12px;font-weight:600;color:var(--brand);background:none;border:1px solid transparent;cursor:pointer;padding:4px 10px;border-radius:7px;transition:all .13s;"
                                                onmouseover="this.style.background='var(--brand-light,#eef2ff)';this.style.borderColor='var(--brand-ring,#c7d2fe)'"
                                                onmouseout="this.style.background='none';this.style.borderColor='transparent'">
                                            <span x-text="form.showKeyField ? '✕ Cancel' : '↺ Update key'"></span>
                                        </button>
                                    </div>
                                    <div x-show="form.showKeyField" x-transition style="margin-top:2px;">
                                        <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2);">New API Key</label>
                                        <input type="password" x-model="form.api_key" placeholder="Enter new API key to replace saved key"
                                               class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                                               style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                    </div>
                                </div>
                                {{-- Not configured: always show key input --}}
                                <div x-show="!editing?.configured">
                                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2);">API Key <span style="color:#ef4444">*</span></label>
                                    <input type="password" x-model="form.api_key" placeholder="Enter API key"
                                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                </div>
                            </div>

                            {{-- -- Multiple API Keys section (configured providers only) -- --}}
                            <div x-show="editing?.configured" style="border:1px solid var(--border);border-radius:12px;overflow:hidden;">
                                <div style="padding:10px 14px;background:var(--surface-raised);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <svg style="width:13px;height:13px;color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        <span style="font-size:12.5px;font-weight:700;color:var(--text-1);">API Keys</span>
                                        <span style="font-size:11px;color:var(--text-3);" x-text="'(' + form.keys.length + ' key' + (form.keys.length !== 1 ? 's' : '') + ')'"></span>
                                    </div>
                                    <div style="font-size:11px;color:var(--text-3);">Auto-failover between keys</div>
                                </div>

                                {{-- Key list --}}
                                <div x-show="form.keys.length > 0" style="max-height:200px;overflow-y:auto;">
                                    <template x-for="(k, ki) in form.keys" :key="k.id">
                                        <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-bottom:1px solid var(--border);"
                                             :style="ki === form.keys.length - 1 ? 'border-bottom:none' : ''">
                                            {{-- Primary star --}}
                                            <button type="button" @click="setPrimaryProviderKey(k.id)"
                                                    :title="k.is_primary ? 'Primary key' : 'Set as primary'"
                                                    style="width:20px;height:20px;border:none;background:none;cursor:pointer;flex-shrink:0;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <svg style="width:14px;height:14px;" :style="k.is_primary ? 'color:#f59e0b' : 'color:var(--border)'" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            </button>
                                            {{-- Label + status --}}
                                            <div style="flex:1;min-width:0;">
                                                <span style="font-size:12.5px;font-weight:600;color:var(--text-1);" x-text="k.label"></span>
                                                <span x-show="k.fail_count > 0" style="margin-left:6px;font-size:10px;color:#ef4444;font-weight:600;"
                                                      x-text="k.fail_count + ' fail' + (k.fail_count > 1 ? 's' : '')"></span>
                                            </div>
                                            {{-- Active toggle --}}
                                            <button type="button" x-show="!k.is_primary" @click="toggleProviderKey(k.id)"
                                                    style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;cursor:pointer;"
                                                    :style="k.is_active ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0' : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)'"
                                                    x-text="k.is_active ? 'Active' : 'Off'"></button>
                                            <span x-show="k.is_primary" style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:color-mix(in srgb,var(--brand) 12%,transparent);color:var(--brand);border:1px solid color-mix(in srgb,var(--brand) 25%,transparent);">Primary</span>
                                            {{-- Delete --}}
                                            <button type="button" @click="deleteProviderKey(k.id)"
                                                    style="width:22px;height:22px;border:none;background:none;cursor:pointer;flex-shrink:0;padding:0;color:var(--text-3);display:flex;align-items:center;justify-content:center;border-radius:5px;transition:all .13s;"
                                                    onmouseover="this.style.background='#fef2f2';this.style.color='#ef4444'" onmouseout="this.style.background='none';this.style.color='var(--text-3)'"
                                                    title="Delete key">
                                                <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                {{-- Add new key form --}}
                                <div style="padding:8px 14px;background:var(--surface-raised);border-top:1px solid var(--border);">
                                    <div x-show="!form.addingKey">
                                        <button type="button" @click="form.addingKey = true"
                                                style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--brand);background:none;border:none;cursor:pointer;padding:0;">
                                            <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Add another key
                                        </button>
                                    </div>
                                    <div x-show="form.addingKey" x-transition style="display:flex;flex-direction:column;gap:6px;">
                                        <input type="text" x-model="form.newKeyLabel" placeholder="Key label (e.g. Backup Key)"
                                               style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid var(--border);background:var(--input-bg);color:var(--text-1);font-size:12px;outline:none;">
                                        <div style="display:flex;gap:6px;">
                                            <input type="password" x-model="form.newKeyValue" placeholder="Paste API key here"
                                                   style="flex:1;padding:6px 10px;border-radius:8px;border:1px solid var(--border);background:var(--input-bg);color:var(--text-1);font-size:12px;outline:none;">
                                            <button type="button" @click="addProviderKey()"
                                                    :disabled="!form.newKeyValue.trim() || form.savingKey"
                                                    style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;border:none;cursor:pointer;background:var(--brand);transition:opacity .13s;"
                                                    :style="(!form.newKeyValue.trim() || form.savingKey) ? 'opacity:.5;cursor:not-allowed' : ''">
                                                <span x-text="form.savingKey ? '...' : 'Add'"></span>
                                            </button>
                                            <button type="button" @click="form.addingKey=false;form.newKeyLabel='';form.newKeyValue=''"
                                                    style="padding:6px 10px;border-radius:8px;font-size:12px;font-weight:500;color:var(--text-2);border:1px solid var(--border);cursor:pointer;background:none;">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="editing?.requires_endpoint">
                                <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)" x-text="editing?.api_url_label || 'Endpoint URL'"></label>
                                <input type="text" x-model="form.api_url" class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                                       style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                                       :placeholder="editing?.api_url_label === 'Ollama Host URL' ? 'http://localhost:11434' : 'https://'">
                            </div>

                            <div>
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <label class="block text-sm font-semibold" style="color:var(--text-2);">Default Model</label>
                                    <span class="text-xs" style="color:var(--text-3);" x-text="(editing?.model_count || 0) + ' models available'"></span>
                                </div>
                                <select x-model="form.default_model" class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                                        style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);cursor:pointer;">
                                    <template x-for="model in editing?.models || []" :key="model.key">
                                        <option :value="model.key" x-text="model.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:var(--surface-raised);border:1px solid var(--border);">
                                <label class="flex items-center gap-2 cursor-pointer" style="flex:1;">
                                    <input type="checkbox" x-model="form.set_default" style="width:14px;height:14px;border-radius:4px;accent-color:var(--brand);cursor:pointer;">
                                    <div>
                                        <span class="text-sm font-semibold" style="color:var(--text-1);">Set as default provider</span>
                                        <p class="text-xs mt-0.5" style="color:var(--text-3);">Used for all AI generation by default</p>
                                    </div>
                                </label>
                                <svg x-show="editing?.is_default" style="width:16px;height:16px;color:#f59e0b;flex-shrink:0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>

                            <div x-show="editing?.external_url" style="padding:8px 12px;border-radius:9px;background:var(--surface-raised);border:1px solid var(--border);">
                                <div style="font-size:11px;color:var(--text-3);margin-bottom:3px;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Get your API key from</div>
                                <a :href="editing?.external_url" target="_blank" rel="noopener"
                                   style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:var(--brand);text-decoration:none;">
                                    <svg style="width:11px;height:11px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    <span x-text="editing?.external_label"></span>
                                </a>
                            </div>

                            <div x-show="result" class="rounded-xl px-4 py-3 text-sm"
                                 :style="result?.success ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;'">
                                <span x-text="result?.message"></span>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2" style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                                <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors" style="color:var(--text-2);border:1px solid var(--border);">Cancel</button>
                                <button type="submit" :disabled="saving" class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px" style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                                    <span x-text="saving ? 'Saving...' : 'Save Provider'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{-- ---- BRANDING ---- --}}
        <div x-show="tab === 'branding'" class="st-panel">

            {{-- -- Brand Kit Live Preview -- --}}
            <div class="bk-preview-wrap">
                <div class="bk-preview-bar">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="bk-preview-dot" style="background:#ef4444;"></span>
                        <span class="bk-preview-dot" style="background:#f59e0b;"></span>
                        <span class="bk-preview-dot" style="background:#10b981;"></span>
                        <span style="margin-left:8px;font-size:11px;font-weight:600;color:var(--text-3);">Brand Kit Preview</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                        <span style="font-size:11px;color:var(--text-3);">Live</span>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:160px 1fr;height:210px;" :style="'font-family:\'' + fontFamily + '\',sans-serif;'">
                    {{-- Mock sidebar --}}
                    <div style="display:flex;flex-direction:column;padding:14px 10px;gap:3px;border-right:1px solid rgba(0,0,0,.08);" :style="'background:' + brandColor">
                        <div style="display:flex;align-items:center;gap:7px;padding:4px 6px;margin-bottom:8px;">
                            <div style="width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:12px;font-weight:800;color:#fff;">R</span>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:#fff;opacity:.92;">RyaanCMS</span>
                        </div>
                        <div class="bk-sidebar-item" style="background:rgba(255,255,255,.2);">
                            <div style="width:12px;height:12px;border-radius:3px;background:rgba(255,255,255,.7);flex-shrink:0;"></div>
                            <span style="font-size:11px;color:#fff;font-weight:600;">Dashboard</span>
                        </div>
                        @foreach(['Projects','AI Builder','Marketplace','Settings'] as $mi)
                        <div class="bk-sidebar-item">
                            <div style="width:12px;height:12px;border-radius:3px;background:rgba(255,255,255,.28);flex-shrink:0;"></div>
                            <span style="font-size:11px;color:rgba(255,255,255,.72);">{{ $mi }}</span>
                        </div>
                        @endforeach
                    </div>
                    {{-- Mock content --}}
                    <div style="padding:14px;background:#f8fafc;overflow:hidden;">
                        <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:2px;">Dashboard Overview</div>
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:12px;">Your workspace at a glance</div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-bottom:9px;">
                            @foreach(['12 Projects','48 Builds','1.2k API Calls'] as $stat)
                            <div style="background:#fff;border-radius:9px;padding:8px 10px;border:1px solid #e2e8f0;">
                                <div style="font-size:9px;color:#94a3b8;margin-bottom:2px;">{{ implode(' ', array_slice(explode(' ', $stat), 1)) }}</div>
                                <div style="font-size:16px;font-weight:800;" :style="'color:' + brandColor">{{ explode(' ', $stat)[0] }}</div>
                            </div>
                            @endforeach
                        </div>
                        <div style="background:#fff;border-radius:9px;padding:9px 11px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:9px;">
                            <div style="width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;" :style="'background:' + brandColor">
                                <div style="width:10px;height:10px;border-radius:2px;background:rgba(255,255,255,.8);"></div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:11px;font-weight:600;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">My Portfolio Website</div>
                                <div style="font-size:10px;color:#94a3b8;">Updated 2 hours ago</div>
                            </div>
                            <div style="padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;color:#fff;white-space:nowrap;" :style="'background:' + brandColor">Active</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- -- Colors & Typography -- --}}
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#f5f3ff;">
                        <svg style="width:15px;height:15px;stroke:#7c3aed" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">Brand Identity</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">Colors, typography, and visual style</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;margin-left:auto;flex-shrink:0;">
                        @if(auth()->user()->isAdmin())
                        <button type="button" onclick="saveBrandingGlobal(this)"
                                style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;border-radius:10px;font-size:12px;font-weight:600;background:#faf5ff;color:#7c3aed;border:1.5px solid #ddd6fe;cursor:pointer;transition:all .15s;"
                                title="Set this as the default branding for all users (admin only)">
                            <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                            Site Default
                        </button>
                        @endif
                        <button type="button" @click="saveBrandingAjax()" :disabled="brandSaving" class="sbtn-primary" style="padding:7px 18px;font-size:12.5px;">
                            <template x-if="!brandSaved">
                                <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <span x-text="brandSaving ? 'Saving...' : (brandSaved ? 'Saved' : 'Save')"></span>
                        </button>
                    </div>
                </div>
                <div class="scard-body" style="display:flex;flex-direction:column;gap:24px;">

                    {{-- Color picker --}}
                    <div>
                        <label class="slabel">Primary Brand Color</label>
                        <div style="display:flex;gap:16px;align-items:flex-start;">
                            {{-- Big color swatch --}}
                            <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:6px;">
                                <div style="position:relative;width:72px;height:72px;border-radius:16px;cursor:pointer;border:3px solid #fff;transition:box-shadow .15s;overflow:hidden;"
                                     :style="'background:' + brandColor + ';box-shadow:0 0 0 2px ' + brandColor + '40,0 4px 14px rgba(0,0,0,.1);'">
                                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.18);opacity:0;transition:opacity .15s;pointer-events:none;"
                                         x-ref="colorOverlay"
                                         @mouseover.self="$el.style.opacity='1'" @mouseout.self="$el.style.opacity='0'">
                                        <svg style="width:18px;height:18px;stroke:#fff" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </div>
                                    <input type="color" x-ref="colorInput" name="primary_color" x-model="brandColor"
                                           style="position:absolute;opacity:0;inset:0;cursor:pointer;width:100%;height:100%;"
                                           @mouseover="$refs.colorOverlay.style.opacity='1'" @mouseout="$refs.colorOverlay.style.opacity='0'">
                                </div>
                                <div style="font-size:10.5px;font-weight:700;font-family:monospace;letter-spacing:.04em;" :style="'color:' + brandColor" x-text="brandColor.toUpperCase()"></div>
                            </div>
                            <div style="flex:1;display:flex;flex-direction:column;gap:12px;">
                                {{-- Hex input --}}
                                <div style="display:flex;align-items:center;gap:8px;background:var(--hover-bg);border-radius:10px;padding:9px 12px;border:1.5px solid var(--border);">
                                    <div style="width:16px;height:16px;border-radius:4px;flex-shrink:0;transition:background .2s;" :style="'background:' + brandColor"></div>
                                    <span style="font-size:12px;color:var(--text-3);font-family:monospace;font-weight:700;">#</span>
                                    <input class="bk-hex-input" :value="hexInput" @input="onHexInput($event.target.value)" maxlength="6" placeholder="6366F1">
                                    <button type="button" @click="copyHex()"
                                            style="font-size:11px;font-weight:600;cursor:pointer;background:none;border:none;padding:3px 8px;border-radius:7px;transition:all .15s;white-space:nowrap;"
                                            :style="brandCopied ? 'color:#10b981;background:#f0fdf4;' : 'color:var(--text-3);'"
                                            x-text="brandCopied ? 'Copied!' : 'Copy'"></button>
                                </div>
                                {{-- Color presets --}}
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    @foreach([
                                        ['color'=>'#6366f1','name'=>'Indigo'],  ['color'=>'#8b5cf6','name'=>'Violet'],
                                        ['color'=>'#a855f7','name'=>'Purple'],  ['color'=>'#ec4899','name'=>'Pink'],
                                        ['color'=>'#ef4444','name'=>'Red'],     ['color'=>'#f97316','name'=>'Orange'],
                                        ['color'=>'#eab308','name'=>'Yellow'],  ['color'=>'#10b981','name'=>'Emerald'],
                                        ['color'=>'#14b8a6','name'=>'Teal'],    ['color'=>'#3b82f6','name'=>'Blue'],
                                        ['color'=>'#0ea5e9','name'=>'Sky'],     ['color'=>'#64748b','name'=>'Slate'],
                                    ] as $p)
                                    <button type="button" @click="pickColor('{{ $p['color'] }}')" title="{{ $p['name'] }}"
                                            class="bk-color-swatch" style="background:{{ $p['color'] }};"
                                            :class="brandColor === '{{ $p['color'] }}' ? 'selected' : ''"></button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        {{-- Tints & shades row --}}
                        <div style="margin-top:14px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);margin-bottom:7px;">Tints & Shades - click to apply</div>
                            <div style="display:flex;gap:4px;align-items:stretch;height:24px;">
                                <template x-for="(shade, i) in getShades(brandColor)" :key="i">
                                    <div class="bk-shade" :style="'background:' + shade" @click="pickColor(shade)" :title="shade"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div style="height:1px;background:var(--border);"></div>

                    {{-- Typeface --}}
                    <div>
                        <label class="slabel">Typeface</label>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                            @foreach([
                                ['name'=>'Poppins',    'sample'=>'Geometric soft'],
                                ['name'=>'Inter',      'sample'=>'Clean modern'],
                                ['name'=>'DM Sans',    'sample'=>'Friendly round'],
                                ['name'=>'Nunito',     'sample'=>'Rounded warm'],
                                ['name'=>'Roboto',     'sample'=>'Neutral classic'],
                                ['name'=>'Open Sans',  'sample'=>'Open legible'],
                            ] as $font)
                            <label style="cursor:pointer;display:block;">
                                <input type="radio" name="font_family" value="{{ $font['name'] }}" x-model="fontFamily" style="display:none;">
                                <div class="bk-font-card" :class="fontFamily === '{{ $font['name'] }}' ? 'selected' : ''">
                                    <div style="font-size:22px;font-weight:700;margin-bottom:2px;line-height:1;transition:color .15s;"
                                         :style="'font-family:\'{{ $font['name'] }}\',sans-serif;color:' + (fontFamily==='{{ $font['name'] }}' ? 'var(--brand)' : '#1e293b')">Aa</div>
                                    <div style="font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:1px;transition:color .15s;"
                                         :style="fontFamily==='{{ $font['name'] }}' ? 'color:var(--brand)' : 'color:#334155'">{{ $font['name'] }}</div>
                                    <div style="font-size:10px;color:#94a3b8;">{{ $font['sample'] }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- -- Logo & Favicon -- --}}
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#f0f9ff;">
                        <svg style="width:15px;height:15px;stroke:#0ea5e9" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">Logo & Favicon</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">Used in the sidebar and browser tab</div>
                    </div>
                </div>
                <div class="scard-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        {{-- Logo upload --}}
                        <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                              x-data="{ preview: '{{ $savedLogo ? Storage::url($savedLogo) : '' }}', dragging: false }">
                            @csrf
                            <input type="hidden" name="type" value="logo">
                            <label class="slabel">Site Logo</label>
                            <div class="bk-upload-zone" :class="dragging ? 'bk-upload-drag' : ''"
                                 @click="$refs.logoInput.click()"
                                 @dragover.prevent="dragging=true" @dragleave="dragging=false"
                                 @drop.prevent="dragging=false; preview=URL.createObjectURL($event.dataTransfer.files[0]); $el.closest('form').submit()">
                                <template x-if="preview">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                        <div style="width:64px;height:64px;border-radius:12px;border:1px solid var(--border);overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;">
                                            <img :src="preview" style="width:100%;height:100%;object-fit:contain;">
                                        </div>
                                        <div style="font-size:11.5px;font-weight:600;color:#10b981;display:flex;align-items:center;gap:4px;">
                                            <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Logo uploaded
                                        </div>
                                        <div style="font-size:10.5px;color:var(--text-3);">Click to replace</div>
                                    </div>
                                </template>
                                <template x-if="!preview">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                        <div style="width:48px;height:48px;border-radius:12px;background:var(--hover-bg);border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;">
                                            <svg style="width:20px;height:20px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div style="font-size:12.5px;font-weight:600;color:var(--text-2);">Upload logo</div>
                                        <div style="font-size:11px;color:var(--text-3);">PNG, SVG, JPG &middot; max 2MB</div>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="file" x-ref="logoInput" accept="image/*" style="display:none;"
                                   @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        </form>

                        {{-- Favicon upload --}}
                        <form method="POST" action="{{ route('settings.branding.upload') }}" enctype="multipart/form-data"
                              x-data="{ preview: '{{ $savedFav ? Storage::url($savedFav) : '' }}', dragging: false }">
                            @csrf
                            <input type="hidden" name="type" value="favicon">
                            <label class="slabel">Favicon</label>
                            <div class="bk-upload-zone" :class="dragging ? 'bk-upload-drag' : ''"
                                 @click="$refs.favInput.click()"
                                 @dragover.prevent="dragging=true" @dragleave="dragging=false"
                                 @drop.prevent="dragging=false; preview=URL.createObjectURL($event.dataTransfer.files[0]); $el.closest('form').submit()">
                                <template x-if="preview">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                        <div style="width:48px;height:48px;border-radius:12px;border:1px solid var(--border);overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;">
                                            <img :src="preview" style="width:100%;height:100%;object-fit:contain;">
                                        </div>
                                        <div style="font-size:11.5px;font-weight:600;color:#10b981;display:flex;align-items:center;gap:4px;">
                                            <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Favicon saved
                                        </div>
                                        <div style="font-size:10.5px;color:var(--text-3);">Click to replace</div>
                                    </div>
                                </template>
                                <template x-if="!preview">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                        <div style="width:48px;height:48px;border-radius:12px;background:var(--hover-bg);border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;">
                                            <svg style="width:20px;height:20px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div style="font-size:12.5px;font-weight:600;color:var(--text-2);">Upload favicon</div>
                                        <div style="font-size:11px;color:var(--text-3);">ICO, PNG, SVG &middot; 32&times;32px</div>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="file" x-ref="favInput" accept="image/*,.ico" style="display:none;"
                                   @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        </form>
                    </div>

                    {{-- Placement hint --}}
                    <div style="margin-top:14px;padding:12px 14px;border-radius:10px;background:var(--hover-bg);border:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                        <svg style="width:15px;height:15px;stroke:#94a3b8;flex-shrink:0" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div style="font-size:12px;color:var(--text-3);">The logo appears in the sidebar header. The favicon is shown in the browser tab.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SYSTEM CONFIG --}}
        <div x-show="tab === 'system_config'" class="st-panel" x-cloak>

            {{-- Success toast --}}
            <div x-show="sysSaved" x-transition
                 style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;font-size:13px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;">
                <svg style="width:16px;height:16px;flex-shrink:0;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                System config saved.
            </div>

            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:color-mix(in srgb,var(--brand) 10%,#fff);">
                        <svg style="width:15px;height:15px;stroke:var(--brand)" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">Dashboard Layout</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Show or hide the sidebar and top menu bar</div>
                    </div>
                </div>
                <div class="scard-body" style="display:flex;flex-direction:column;gap:0;">

                    {{-- Sidebar toggle --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border);">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);">Dashboard Sidebar</div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Show the left navigation sidebar</div>
                        </div>
                        <button type="button" @click="sysShowSidebar = !sysShowSidebar; saveSystemConfig()"
                                :aria-checked="sysShowSidebar" role="switch"
                                class="sys-toggle-btn"
                                :class="sysShowSidebar ? 'sys-toggle-on' : 'sys-toggle-off'">
                            <span class="sys-toggle-thumb" :class="sysShowSidebar ? 'sys-toggle-thumb-on' : ''"></span>
                        </button>
                    </div>

                    {{-- Auto-hide sidebar toggle --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border);">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);">Auto-hide Sidebar</div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Sidebar fully hides; hover the left edge to reveal it</div>
                        </div>
                        <button type="button" @click="if(sysShowSidebar){ sysAutoHideSidebar = !sysAutoHideSidebar; saveSystemConfig(); }"
                                :aria-checked="sysAutoHideSidebar" role="switch"
                                class="sys-toggle-btn"
                                :class="!sysShowSidebar ? 'sys-toggle-disabled' : (sysAutoHideSidebar ? 'sys-toggle-on' : 'sys-toggle-off')">
                            <span class="sys-toggle-thumb" :class="sysAutoHideSidebar ? 'sys-toggle-thumb-on' : ''"></span>
                        </button>
                    </div>

                    {{-- Menu toggle --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);">Dashboard Top Menu</div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Show the top navigation bar with breadcrumbs and actions</div>
                        </div>
                        <button type="button" @click="sysShowMenu = !sysShowMenu; saveSystemConfig()"
                                :aria-checked="sysShowMenu" role="switch"
                                class="sys-toggle-btn"
                                :class="sysShowMenu ? 'sys-toggle-on' : 'sys-toggle-off'">
                            <span class="sys-toggle-thumb" :class="sysShowMenu ? 'sys-toggle-thumb-on' : ''"></span>
                        </button>
                    </div>

                </div>
            </div>

            <div class="scard" style="border-left:3px solid #f59e0b;">
                <div class="scard-body" style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:32px;height:32px;border-radius:9px;background:#fffbeb;border:1px solid #fde68a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:15px;height:15px;stroke:#d97706" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#92400e;margin-bottom:3px;">Note</div>
                        <div style="font-size:12.5px;color:#b45309;line-height:1.5;">
                            Hiding the sidebar or top menu takes effect on the next page load. You can always restore them from this page.
                        </div>
                    </div>
                </div>
            </div>

            {{-- AI Builder Settings --}}
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#eff6ff;">
                        <svg style="width:15px;height:15px;stroke:#3b82f6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">AI Builder Preferences</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">Control how the AI Builder generates your applications</div>
                    </div>
                </div>
                <div class="scard-body" style="display:flex;flex-direction:column;gap:0;">

                    {{-- Auto HTML Documentation --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border);gap:16px;">
                        <div style="flex:1;">
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);display:flex;align-items:center;gap:8px;">
                                Auto HTML Documentation
                                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">NEW</span>
                            </div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:3px;line-height:1.5;">
                                Automatically generate a <code style="font-size:11px;background:var(--surface-raised);padding:1px 5px;border-radius:4px;">docs/index.html</code> documentation file with every AI build, including component catalogue, API endpoints, data models, and usage guides.
                            </div>
                        </div>
                        <button type="button"
                                @click="sysAiBuilderDocs = !sysAiBuilderDocs; saveSystemConfig()"
                                :aria-checked="sysAiBuilderDocs" role="switch"
                                class="sys-toggle-btn"
                                :class="sysAiBuilderDocs ? 'sys-toggle-on' : 'sys-toggle-off'"
                                style="flex-shrink:0;margin-top:2px;">
                            <span class="sys-toggle-thumb" :class="sysAiBuilderDocs ? 'sys-toggle-thumb-on' : ''"></span>
                        </button>
                    </div>

                    {{-- Info tip --}}
                    <div style="padding:14px 0 0;">
                        <div style="padding:11px 14px;border-radius:10px;background:var(--hover-bg);border:1px solid var(--border);display:flex;align-items:flex-start;gap:10px;">
                            <svg style="width:14px;height:14px;stroke:#94a3b8;flex-shrink:0;margin-top:1px;" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div style="font-size:12px;color:var(--text-3);line-height:1.5;">
                                When enabled, each AI generation will produce a living HTML documentation page alongside your code. The doc updates automatically on every build with no manual effort needed.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- NOTIFICATIONS --}}
        <div x-show="tab === 'notifications'" class="st-panel" x-cloak>
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#fdf4ff;">
                        <svg style="width:15px;height:15px;stroke:#8b5cf6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">Notification Preferences</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">Choose what you get notified about</div>
                    </div>
                </div>
                <div class="scard-body" x-data="{ emailBuild: true, emailDeploy: true, emailUpdate: false, inappAll: true }">
                    @foreach([
                        ['key'=>'emailBuild',  'label'=>'AI Build completed',   'hint'=>'When a project generation finishes'],
                        ['key'=>'emailDeploy', 'label'=>'Deployment completed',  'hint'=>'When a deployment succeeds or fails'],
                        ['key'=>'emailUpdate', 'label'=>'System updates',        'hint'=>'When a new RyaanCMS version is available'],
                        ['key'=>'inappAll',    'label'=>'In-app notifications',  'hint'=>'Activity feed in the notification panel'],
                    ] as $n)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 0;border-bottom:1px solid var(--border);">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text-1);">{{ $n['label'] }}</div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;">{{ $n['hint'] }}</div>
                        </div>
                        <button type="button" @click="{{ $n['key'] }} = !{{ $n['key'] }}"
                                style="width:40px;height:22px;border-radius:99px;border:none;cursor:pointer;transition:background .2s;flex-shrink:0;position:relative;"
                                :style="{{ $n['key'] }} ? 'background:var(--brand);' : 'background:#e2e8f0;'">
                            <span style="position:absolute;top:2px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .2s;"
                                  :style="{{ $n['key'] }} ? 'left:20px;' : 'left:2px;'"></span>
                        </button>
                    </div>
                    @endforeach
                    <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                        <button class="sbtn-primary" @click="window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Notification preferences saved.' } }))">
                            Save Preferences
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{--         {{-- INTEGRATIONS --}}
        <div x-show="tab === 'integrations'" class="st-panel" x-cloak>
            <div class="scard">
                <div class="scard-hd">
                    <div class="scard-hd-icon" style="background:#f0f9ff;">
                        <svg style="width:15px;height:15px;stroke:#0ea5e9" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">Integrations</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">Connect external services to your workspace</div>
                    </div>
                </div>
                <div class="scard-body" style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        ['icon'=>'M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.342-3.369-1.342-.454-1.155-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.742 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z','label'=>'GitHub','desc'=>'Auto-push generated projects to GitHub repositories','color'=>'#24292e','bg'=>'#f6f8fa','fill'=>true],
                        ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z','label'=>'Webhooks','desc'=>'Send real-time build and deploy events to your HTTP endpoints','color'=>'#7c3aed','bg'=>'#faf5ff','fill'=>false],
                        ['icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z','label'=>'Slack','desc'=>'Get instant notifications in Slack when builds complete','color'=>'#4a154b','bg'=>'#fdf4ff','fill'=>false],
                        ['icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','label'=>'SendGrid','desc'=>'Transactional email delivery for your deployed applications','color'=>'#1a82e2','bg'=>'#eff6ff','fill'=>false],
                        ['icon'=>'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z','label'=>'AWS S3','desc'=>'Store and serve project assets from your S3 bucket','color'=>'#f97316','bg'=>'#fff7ed','fill'=>false],
                        ['icon'=>'M5 3l14 9-14 9V3z','label'=>'Vercel','desc'=>'One-click deploy generated apps directly to Vercel','color'=>'#000000','bg'=>'#f8fafc','fill'=>true],
                    ] as $intg)
                    <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;border:1px solid var(--border);background:var(--surface-raised);">
                        <div style="width:40px;height:40px;border-radius:11px;background:{{ $intg['bg'] }};border:1px solid rgba(0,0,0,.07);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:18px;height:18px;" fill="{{ $intg['fill'] ? $intg['color'] : 'none' }}" stroke="{{ $intg['color'] }}" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $intg['icon'] }}"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:var(--text-1);">{{ $intg['label'] }}</div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;">{{ $intg['desc'] }}</div>
                        </div>
                        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;background:color-mix(in srgb,var(--brand) 8%,transparent);color:var(--brand);border:1px solid color-mix(in srgb,var(--brand) 20%,transparent);white-space:nowrap;flex-shrink:0;">Coming Soon</span>
                    </div>
                    @endforeach
                    <div style="margin-top:6px;padding:14px 16px;border-radius:12px;background:color-mix(in srgb,var(--brand) 5%,transparent);border:1px dashed color-mix(in srgb,var(--brand) 25%,transparent);text-align:center;">
                        <div style="font-size:12.5px;font-weight:600;color:var(--brand);margin-bottom:3px;">More integrations coming in v1.1</div>
                        <div style="font-size:11.5px;color:var(--text-3);">Contact support to request a specific integration</div>
                    </div>
                </div>
            </div>
        </div>
        {{-- ---- TEAM ---- --}}
        @if(auth()->user()->isAdmin())
        @php
            $selfId = auth()->id();
            $teamMembersJson = \App\Models\User::orderByDesc('is_active')->orderBy('name')
                ->get(['id','name','email','username','role','avatar','is_active','created_at'])
                ->map(fn($u) => array_merge($u->toArray(), [
                    'avatar_url' => $u->avatar_url,
                    'is_self'    => $u->id === $selfId,
                ]));
        @endphp
        <script type="application/json" id="team-members-data">{!! json_encode($teamMembersJson) !!}</script>
        @endif
        <div x-show="tab === 'team'" class="st-panel" x-cloak
             x-data="teamManager()">

            <div class="dt-wrap">
                {{-- Header --}}
                <div class="dt-head">
                    <div>
                        <h2 class="dt-title">Team Members</h2>
                        <p class="dt-subtitle">Search, filter, and manage everyone who has access to this workspace.</p>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <button @click="showAddModal = true"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                            style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);flex-shrink:0;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Member
                    </button>
                    @endif
                </div>

                {{-- Toolbar --}}
                <div class="dt-toolbar" style="flex-direction:column;align-items:stretch;gap:10px;">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="dt-search">
                            <svg class="dt-search-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input x-ref="searchInput" x-model="search" @input="page = 1"
                                   @keydown.escape="search = ''; page = 1"
                                   type="text" placeholder="Search name, email…" class="dt-search-input">
                            <button x-show="search" @click="search = ''; page = 1; $refs.searchInput.focus()"
                                    class="dt-clear" title="Clear" x-cloak>
                                <svg style="width:9px;height:9px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="dt-per-page">
                            <span>Show</span>
                            <select x-model.number="perPage" @change="page = 1">
                                <template x-for="n in perPageOpts" :key="n">
                                    <option :value="n" x-text="n"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex rounded-xl overflow-hidden flex-shrink-0" style="border:1px solid var(--border);">
                            <button @click="statusFilter = 'all'; page = 1" class="px-3 py-1.5 text-xs transition-all"
                                    :style="statusFilter==='all' ? 'background:var(--brand);color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">All</button>
                            <button @click="statusFilter = 'active'; page = 1" class="px-3 py-1.5 text-xs border-l transition-all" style="border-color:var(--border)"
                                    :style="statusFilter==='active' ? 'background:#15803d;color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">Active</button>
                            <button @click="statusFilter = 'inactive'; page = 1" class="px-3 py-1.5 text-xs border-l transition-all" style="border-color:var(--border)"
                                    :style="statusFilter==='inactive' ? 'background:#64748b;color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">Inactive</button>
                        </div>
                        <div class="dt-per-page">
                            <span>Sort</span>
                            <select x-model="sortBy" @change="page = 1">
                                <option value="name_asc">Name A → Z</option>
                                <option value="name_desc">Name Z → A</option>
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                            </select>
                        </div>
                        <span class="dt-count">
                            <span x-text="filtered.length"></span>
                            <span x-text="filtered.length === 1 ? ' member' : ' members'"></span>
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="pill in rolePills" :key="pill.val">
                            <button @click="roleFilter = roleFilter === pill.val ? '' : pill.val; page = 1"
                                    class="sys-pill" :class="roleFilter === pill.val ? 'sys-pill-on' : ''"
                                    :style="roleFilter === pill.val ? pill.color : ''">
                                <span x-text="pill.label"></span>
                                <span class="ml-1 opacity-70" x-text="'(' + roleCount(pill.val) + ')'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="dt-table">
                        <thead>
                            <tr>
                                <th class="dt-th">#</th>
                                <th class="dt-th">Member</th>
                                <th class="dt-th">Role</th>
                                <th class="dt-th">Status</th>
                                <th class="dt-th">Joined</th>
                                @if(auth()->user()->isAdmin())
                                <th class="dt-th" style="text-align:right">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="paginated.length === 0">
                                <tr><td colspan="6" class="dt-empty">
                                    <svg style="width:38px;height:38px;margin:0 auto 10px;color:var(--border)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p style="font-size:13px;font-weight:600;color:var(--text-1)">No members found</p>
                                    <p style="font-size:12px;color:var(--text-3);margin-top:4px">Try a different search or filter</p>
                                </td></tr>
                            </template>
                            <template x-for="(m, i) in paginated" :key="m.id">
                                <tr class="dt-tr" :style="m.is_self ? 'background:color-mix(in srgb, var(--brand) 4%, transparent)' : ''">
                                    <td class="dt-td" style="color:var(--text-3);font-size:12px" x-text="(page - 1) * perPage + i + 1"></td>
                                    <td class="dt-td">
                                        <div style="display:flex;align-items:center;gap:10px">
                                            <div style="position:relative;flex-shrink:0">
                                                <img :src="m.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(m.name) + '&background=6366f1&color=fff&size=64'"
                                                     style="width:34px;height:34px;border-radius:9px;display:block" alt="">
                                                <template x-if="m.is_self">
                                                    <span style="position:absolute;bottom:-3px;right:-3px;width:12px;height:12px;border-radius:50%;background:var(--brand);border:2px solid var(--card-bg);display:block"></span>
                                                </template>
                                            </div>
                                            <div>
                                                <div style="display:flex;align-items:center;gap:6px">
                                                    <span style="font-size:13px;font-weight:600;color:var(--text-1)" x-html="highlight(m.name)"></span>
                                                    <template x-if="m.is_self">
                                                        <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:6px;background:var(--brand);color:#fff;letter-spacing:.4px">You</span>
                                                    </template>
                                                </div>
                                                <div style="font-size:11px;color:var(--text-3);margin-top:1px" x-html="highlight(m.email)"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="dt-td">
                                        @if(auth()->user()->isAdmin())
                                        <select @change="!m.is_self && updateRole(m, $event.target.value)"
                                                :disabled="m.is_self"
                                                style="padding:4px 10px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface-raised);font-size:12px;font-weight:600;color:var(--text-1);outline:none;"
                                                :style="m.is_self ? 'cursor:not-allowed;opacity:.6' : 'cursor:pointer'"
                                                :value="m.role">
                                            <option value="admin">Admin</option>
                                            <option value="developer">Developer</option>
                                            <option value="user">User</option>
                                        </select>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                              :style="roleStyle(m.role)"
                                              x-text="m.role.charAt(0).toUpperCase() + m.role.slice(1)"></span>
                                        @endif
                                    </td>
                                    <td class="dt-td">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                              :style="m.is_active
                                                  ? 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0'
                                                  : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)'">
                                            <span style="width:6px;height:6px;border-radius:50%;display:inline-block" :style="m.is_active ? 'background:#22c55e' : 'background:#94a3b8'"></span>
                                            <span x-text="m.is_active ? 'Active' : 'Inactive'"></span>
                                        </span>
                                    </td>
                                    <td class="dt-td" style="color:var(--text-3);font-size:12px" x-text="fmtDate(m.created_at)"></td>
                                    @if(auth()->user()->isAdmin())
                                    <td class="dt-td" style="text-align:right">
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                                            <template x-if="!m.is_self">
                                                <button @click="toggleStatus(m)"
                                                        :title="m.is_active ? 'Deactivate' : 'Activate'"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                        :style="m.is_active ? 'background:#fef2f2;color:#ef4444;border:1px solid #fecaca' : 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0'">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <template x-if="!m.is_self">
                                                <button @click="openEdit(m)" title="Edit"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                        style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe"
                                                        onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <template x-if="!m.is_self">
                                                <button @click="removeMember(m)" title="Remove"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                        style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca"
                                                        onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <template x-if="m.is_self">
                                                <span style="font-size:11px;color:var(--text-3);padding:0 4px">That's you</span>
                                            </template>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="dt-foot">
                    <span class="dt-foot-info" x-text="dtInfo(filtered.length, page, perPage)"></span>
                    <div class="dt-pages" x-show="totalPages > 1">
                        <button class="dt-page-btn" @click="page = Math.max(1, page - 1)" :disabled="page === 1">‹</button>
                        <template x-for="p in pageRange" :key="String(p) + '-' + page">
                            <template x-if="p === '…'">
                                <span class="dt-page-dot">…</span>
                            </template>
                            <template x-if="p !== '…'">
                                <button class="dt-page-btn" :class="p === page ? 'dt-page-on' : ''"
                                        @click="page = p" x-text="p"></button>
                            </template>
                        </template>
                        <button class="dt-page-btn" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">›</button>
                    </div>
                </div>
            </div>

            {{-- Add Member Modal --}}
            @if(auth()->user()->isAdmin())
            <div x-show="showAddModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);" @click.self="showAddModal=false" x-cloak>
                <div class="w-full max-w-md rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);" @click.stop>
                    <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                        <div>
                            <h3 class="font-bold text-base" style="color:var(--text-1)">Add Team Member</h3>
                            <p class="text-xs mt-0.5" style="color:var(--text-3)">Create a new account and add them to the team</p>
                        </div>
                        <button @click="showAddModal=false" class="w-8 h-8 rounded-lg flex items-center justify-center" style="color:var(--text-3);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="addMember()" class="p-6 space-y-4">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label class="slabel">Full Name *</label>
                                <input type="text" x-model="addForm.name" required
                                       class="sfield" style="background:var(--input-bg);border:1.5px solid var(--border);color:var(--text-1);"
                                       placeholder="Jane Smith">
                            </div>
                            <div>
                                <label class="slabel">Role *</label>
                                <select x-model="addForm.role"
                                        style="width:100%;padding:9px 13px;border-radius:10px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);font-size:13.5px;outline:none;">
                                    <option value="user">User</option>
                                    <option value="developer">Developer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="slabel">Email Address *</label>
                            <input type="email" x-model="addForm.email" required
                                   class="sfield" style="background:var(--input-bg);border:1.5px solid var(--border);color:var(--text-1);"
                                   placeholder="jane@example.com">
                        </div>
                        <div>
                            <label class="slabel">Password *</label>
                            <input type="password" x-model="addForm.password" required minlength="8"
                                   class="sfield" style="background:var(--input-bg);border:1.5px solid var(--border);color:var(--text-1);"
                                   placeholder="Min 8 characters">
                        </div>
                        <div x-show="addError" x-cloak
                             style="padding:10px 14px;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:12.5px;" x-text="addError"></div>
                        <div class="flex items-center justify-end gap-3 pt-2" style="border-top:1px solid var(--border);">
                            <button type="button" @click="showAddModal=false" class="px-4 py-2 rounded-xl text-sm font-medium" style="color:var(--text-2);border:1px solid var(--border);">Cancel</button>
                            <button type="submit" :disabled="addSaving"
                                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white"
                                    style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                                <span x-text="addSaving ? 'Adding...' : 'Add Member'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Edit Member Modal --}}
            <div x-show="showEditModal && editTarget" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);" @click.self="showEditModal=false" x-cloak>
                <div class="w-full max-w-md rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);" @click.stop>
                    <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                        <div>
                            <h3 class="font-bold text-base" style="color:var(--text-1)">Edit Member</h3>
                            <p class="text-xs mt-0.5" style="color:var(--text-3)" x-text="editTarget?.name"></p>
                        </div>
                        <button @click="showEditModal=false" class="w-8 h-8 rounded-lg flex items-center justify-center" style="color:var(--text-3);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="saveEdit()" class="p-6 space-y-4">
                        <div>
                            <label class="slabel">Full Name</label>
                            <input type="text" x-model="editForm.name"
                                   class="sfield" style="background:var(--input-bg);border:1.5px solid var(--border);color:var(--text-1);">
                        </div>
                        <div>
                            <label class="slabel">Role</label>
                            <select x-model="editForm.role"
                                    style="width:100%;padding:9px 13px;border-radius:10px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);font-size:13.5px;outline:none;">
                                <option value="user">User</option>
                                <option value="developer">Developer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="slabel">New Password</label>
                            <input type="password" x-model="editForm.password" minlength="8"
                                   class="sfield" style="background:var(--input-bg);border:1.5px solid var(--border);color:var(--text-1);"
                                   placeholder="Leave blank to keep current password">
                        </div>
                        <div>
                            <label class="slabel">Status</label>
                            <button type="button" @click="editForm.is_active = !editForm.is_active"
                                    class="flex items-center gap-3 cursor-pointer select-none">
                                <div class="relative rounded-full transition-colors"
                                     style="width:40px;height:20px"
                                     :style="editForm.is_active ? 'background:#22c55e' : 'background:#d1d5db'">
                                    <div class="absolute top-0.5 bg-white rounded-full shadow transition-all"
                                         style="width:16px;height:16px"
                                         :style="editForm.is_active ? 'left:calc(100% - 18px)' : 'left:2px'"></div>
                                </div>
                                <span style="font-size:13px;color:var(--text-2)"
                                      x-text="editForm.is_active ? 'Active — can sign in' : 'Inactive — blocked'"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2" style="border-top:1px solid var(--border);">
                            <button type="button" @click="showEditModal=false" class="px-4 py-2 rounded-xl text-sm font-medium" style="color:var(--text-2);border:1px solid var(--border);">Cancel</button>
                            <button type="submit" :disabled="editSaving"
                                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white"
                                    style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                                <span x-text="editSaving ? 'Saving...' : 'Save Changes'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- DANGER ZONE --}}
        <div x-show="tab === 'danger'" class="st-panel" x-cloak x-data="{ confirmReset: false, confirmDelete: false, resetPhrase: '', deletePhrase: '' }">
            <div style="padding:12px 16px;border-radius:12px;background:#fff7ed;border:1px solid #fed7aa;color:#c2410c;font-size:12.5px;display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <svg style="width:16px;height:16px;stroke:currentColor;flex-shrink:0" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                These actions are permanent and cannot be undone. Proceed with extreme caution.
            </div>

            {{-- Reset workspace --}}
            <div class="scard" style="border-color:#fecaca;">
                <div class="scard-hd" style="background:#fef2f2;">
                    <div class="scard-hd-icon" style="background:#fecaca;">
                        <svg style="width:15px;height:15px;stroke:#dc2626" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#991b1b;">Reset Workspace</div>
                        <div style="font-size:11.5px;color:#b91c1c;margin-top:1px;">Delete all projects, menus, and generated files</div>
                    </div>
                </div>
                <div class="scard-body">
                    <p style="font-size:12.5px;color:var(--text-2);margin-bottom:14px;line-height:1.6;">
                        This will permanently delete all your <strong>projects</strong>, <strong>AI conversations</strong>, <strong>deployments</strong>, and <strong>custom menus</strong>. Your account, API keys, and branding settings will be preserved.
                    </p>
                    <div x-show="!confirmReset">
                        <button type="button" @click="confirmReset=true"
                                style="padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;color:#dc2626;border:1.5px solid #fecaca;background:#fff;cursor:pointer;transition:all .13s;"
                                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                            Reset Workspace
                        </button>
                    </div>
                    <div x-show="confirmReset" x-transition style="display:flex;flex-direction:column;gap:10px;">
                        <div style="font-size:12px;color:#991b1b;font-weight:600;">Type <code style="background:#fef2f2;padding:1px 5px;border-radius:4px;">RESET MY WORKSPACE</code> to confirm:</div>
                        <input type="text" x-model="resetPhrase" placeholder="RESET MY WORKSPACE"
                               style="padding:9px 13px;border-radius:10px;border:1.5px solid #fecaca;font-size:13px;outline:none;background:#fff;color:#991b1b;font-weight:600;font-family:monospace;">
                        <div style="display:flex;gap:8px;">
                            <button type="button" @click="confirmReset=false;resetPhrase=''"
                                    style="padding:8px 16px;border-radius:9px;font-size:13px;font-weight:500;border:1px solid var(--border);background:var(--surface-raised);color:var(--text-2);cursor:pointer;">Cancel</button>
                            <button type="button"
                                    :disabled="resetPhrase !== 'RESET MY WORKSPACE'"
                                    style="padding:8px 18px;border-radius:9px;font-size:13px;font-weight:700;color:#fff;border:none;cursor:pointer;transition:all .13s;"
                                    :style="resetPhrase==='RESET MY WORKSPACE' ? 'background:#dc2626;opacity:1;' : 'background:#f87171;opacity:.5;cursor:not-allowed;'"
                                    @click="window.dispatchEvent(new CustomEvent('toast', {detail:{type:'info',message:'Reset workspace feature coming soon.'}}))">
                                Confirm Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delete account --}}
            <div class="scard" style="border-color:#fecaca;">
                <div class="scard-hd" style="background:#fef2f2;">
                    <div class="scard-hd-icon" style="background:#fecaca;">
                        <svg style="width:15px;height:15px;stroke:#dc2626" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#991b1b;">Delete Account</div>
                        <div style="font-size:11.5px;color:#b91c1c;margin-top:1px;">Permanently remove your account and all data</div>
                    </div>
                </div>
                <div class="scard-body">
                    <p style="font-size:12.5px;color:var(--text-2);margin-bottom:14px;line-height:1.6;">
                        Your account, all projects, AI keys, settings, and generated files will be permanently deleted. <strong>This cannot be undone.</strong>
                    </p>
                    <div x-show="!confirmDelete">
                        <button type="button" @click="confirmDelete=true"
                                style="padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;background:#dc2626;border:none;cursor:pointer;transition:all .13s;box-shadow:0 2px 8px rgba(220,38,38,.25);"
                                onmouseover="this.style.filter='brightness(1.08)'" onmouseout="this.style.filter=''">
                            Delete My Account
                        </button>
                    </div>
                    <div x-show="confirmDelete" x-transition style="display:flex;flex-direction:column;gap:10px;">
                        <div style="font-size:12px;color:#991b1b;font-weight:600;">Type <code style="background:#fef2f2;padding:1px 5px;border-radius:4px;">DELETE MY ACCOUNT</code> to confirm:</div>
                        <input type="text" x-model="deletePhrase" placeholder="DELETE MY ACCOUNT"
                               style="padding:9px 13px;border-radius:10px;border:1.5px solid #fecaca;font-size:13px;outline:none;background:#fff;color:#991b1b;font-weight:600;font-family:monospace;">
                        <div style="display:flex;gap:8px;">
                            <button type="button" @click="confirmDelete=false;deletePhrase=''"
                                    style="padding:8px 16px;border-radius:9px;font-size:13px;font-weight:500;border:1px solid var(--border);background:var(--surface-raised);color:var(--text-2);cursor:pointer;">Cancel</button>
                            <button type="button"
                                    :disabled="deletePhrase !== 'DELETE MY ACCOUNT'"
                                    style="padding:8px 18px;border-radius:9px;font-size:13px;font-weight:700;color:#fff;border:none;cursor:pointer;transition:all .13s;"
                                    :style="deletePhrase==='DELETE MY ACCOUNT' ? 'background:#dc2626;opacity:1;' : 'background:#f87171;opacity:.5;cursor:not-allowed;'"
                                    @click="window.dispatchEvent(new CustomEvent('toast', {detail:{type:'info',message:'Account deletion feature coming soon.'}}))">
                                Confirm Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /content --}}
</div>{{-- /st-wrap --}}
@endsection

@push('scripts')
<script>
async function saveBrandingGlobal(btn) {
    const form = btn.closest('form');
    if (!form) return;
    const data = new FormData(form);
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg style="width:12px;height:12px;stroke:currentColor;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="60" stroke-dashoffset="20"/></svg> Saving...';
    try {
        const res = await fetch('{{ route("settings.branding.global") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: data,
        });
        const json = await res.json().catch(() => ({}));
        if (res.ok) {
            btn.innerHTML = '<svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saved!';
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Site default branding updated.' } }));
        } else {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: json.message || 'Failed to save.' } }));
            btn.innerHTML = originalText;
        }
    } catch {
        btn.innerHTML = originalText;
    } finally {
        setTimeout(() => { btn.disabled = false; btn.innerHTML = originalText; }, 3000);
    }
}

function aiProviderRowsPayload() {
    const source = document.getElementById('ai-provider-rows');
    if (!source) return [];

    try {
        return JSON.parse(source.textContent || '[]');
    } catch (error) {
        console.error('Could not parse AI provider rows.', error);
        return [];
    }
}

function aiProviderTable(providerRows) {
    providerRows = providerRows ?? aiProviderRowsPayload();

    return {
        ...dtMixin({ perPage: 10 }),

        allProviders: providerRows || [],
        statusFilter: 'all',
        categoryFilter: '',
        sortBy: 'active_first',
        page: 1,
        showModal: false,
        editing: null,
        saving: false,
        testing: null,
        togglingActive: null,
        result: null,
        form: { api_key: '', api_url: '', default_model: '', set_default: false },

        get categoryPills() {
            const seen = {};
            return this.allProviders
                .filter(p => {
                    if (seen[p.category]) return false;
                    seen[p.category] = true;
                    return true;
                })
                .map(p => ({ val: p.category, label: p.category_label }));
        },

        categoryCount(category) {
            return this.allProviders.filter(p => p.category === category).length;
        },

        get filtered() {
            let list = this.allProviders;

            if (this.statusFilter === 'connected') list = list.filter(p => p.has_key);
            if (this.statusFilter === 'missing')   list = list.filter(p => !p.has_key);
            if (this.categoryFilter) list = list.filter(p => p.category === this.categoryFilter);

            const q = this.search.trim().toLowerCase();
            if (q) {
                list = list.filter(p => [
                    p.name,
                    p.provider,
                    p.category_label,
                    p.default_model,
                    p.configured ? 'connected default active' : 'missing not configured',
                ].some(v => String(v || '').toLowerCase().includes(q)));
            }

            list = [...list];
            if (this.sortBy === 'active_first') list.sort((a, b) =>
                (Number(b.configured) - Number(a.configured)) ||
                (Number(b.has_key) - Number(a.has_key)) ||
                a.name.localeCompare(b.name)
            );
            if (this.sortBy === 'name_asc')  list.sort((a, b) => a.name.localeCompare(b.name));
            if (this.sortBy === 'name_desc') list.sort((a, b) => b.name.localeCompare(a.name));
            if (this.sortBy === 'connected') list.sort((a, b) => Number(b.has_key) - Number(a.has_key) || a.name.localeCompare(b.name));
            if (this.sortBy === 'category')  list.sort((a, b) => a.category_label.localeCompare(b.category_label) || a.name.localeCompare(b.name));

            return list;
        },

        get paginated() {
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get pageRange() {
            return this.dtPageRange(this.totalPages, this.page).map(p => typeof p === 'number' ? p : '...');
        },

        get modalTitle() {
            if (!this.editing) return 'AI Provider';
            return (this.editing.configured ? 'Edit ' : 'Connect ') + this.editing.name;
        },

        highlight(text) {
            return this.dtHighlight(text, this.search.trim());
        },

        openEdit(provider) {
            this.editing = { ...provider };
            this.form = {
                api_key: '',
                api_url: provider.api_url || '',
                default_model: provider.default_model || provider.models?.[0]?.key || '',
                set_default: !!provider.is_default,
                showKeyField: !provider.configured,
                // Multiple keys
                keys: [...(provider.keys || [])],
                addingKey: false,
                newKeyLabel: '',
                newKeyValue: '',
                savingKey: false,
            };
            this.result = null;
            this.showModal = true;
        },

        async addProviderKey() {
            if (!this.form.newKeyValue.trim() || this.form.savingKey || !this.editing?.provider_id) return;
            this.form.savingKey = true;
            try {
                const res = await fetch(`/settings/ai-providers/${this.editing.provider_id}/keys`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ label: this.form.newKeyLabel || null, api_key: this.form.newKeyValue }),
                }).then(r => r.json());

                if (res.success) {
                    this.form.keys.push(res.key);
                    this.form.newKeyLabel = '';
                    this.form.newKeyValue = '';
                    this.form.addingKey   = false;
                    this.allProviders = this.allProviders.map(p =>
                        p.provider === this.editing.provider
                            ? { ...p, key_count: this.form.keys.length, keys: [...this.form.keys] }
                            : p
                    );
                }
            } finally {
                this.form.savingKey = false;
            }
        },

        async deleteProviderKey(keyId) {
            if (!confirm('Delete this API key?')) return;
            const res = await fetch(`/settings/ai-providers/${this.editing.provider_id}/keys/${keyId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(r => r.json());
            if (res.success) {
                this.form.keys = this.form.keys.filter(k => k.id !== keyId);
                this.allProviders = this.allProviders.map(p =>
                    p.provider === this.editing.provider
                        ? { ...p, key_count: this.form.keys.length, keys: [...this.form.keys] }
                        : p
                );
            }
        },

        async setPrimaryProviderKey(keyId) {
            const res = await fetch(`/settings/ai-providers/${this.editing.provider_id}/keys/${keyId}/primary`, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(r => r.json());
            if (res.success) {
                this.form.keys = this.form.keys.map(k => ({ ...k, is_primary: k.id === keyId }));
                this.allProviders = this.allProviders.map(p =>
                    p.provider === this.editing.provider
                        ? { ...p, keys: [...this.form.keys] }
                        : p
                );
            }
        },

        async toggleProviderKey(keyId) {
            const res = await fetch(`/settings/ai-providers/${this.editing.provider_id}/keys/${keyId}/toggle`, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(r => r.json());
            if (res.success) {
                this.form.keys = this.form.keys.map(k => k.id === keyId ? { ...k, is_active: res.is_active } : k);
                this.allProviders = this.allProviders.map(p =>
                    p.provider === this.editing.provider
                        ? { ...p, keys: [...this.form.keys], key_count: this.form.keys.length }
                        : p
                );
            }
        },

        closeModal() {
            this.showModal = false;
            this.editing = null;
            this.result = null;
        },

        saveProvider() {
            if (!this.editing) return;
            if (this.editing.provider !== 'ollama' && !this.editing.configured && !this.form.api_key.trim()) {
                this.result = { success: false, message: 'API Key is required for this provider.' };
                return;
            }

            this.saving = true;
            this.result = null;

            fetch('{{ route('settings.ai-provider.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    provider: this.editing.provider,
                    api_key: this.form.api_key.trim(),
                    api_url: this.form.api_url,
                    default_model: this.form.default_model,
                    set_default: this.form.set_default ? '1' : '0',
                }),
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                this.saving = false;
                this.result = data;
                if (!ok || !data.success) return;

                this.allProviders = this.allProviders.map(provider => {
                    if (provider.provider !== this.editing.provider) {
                        return this.form.set_default ? { ...provider, is_default: false } : provider;
                    }

                    return {
                        ...provider,
                        has_key: true,
                        is_active: true,
                        configured: true,
                        provider_id: data.provider_id || provider.provider_id,
                        api_url: this.form.api_url,
                        default_model: this.form.default_model,
                        is_default: this.form.set_default || provider.is_default,
                        updated_at: data.updated_at || new Date().toISOString(),
                        keys: data.keys || provider.keys || [],
                        key_count: Number.isInteger(data.key_count) ? data.key_count : (data.keys || provider.keys || []).length,
                    };
                });

                setTimeout(() => this.closeModal(), 900);
            })
            .catch(() => {
                this.saving = false;
                this.result = { success: false, message: 'Network error. Please try again.' };
            });
        },

        runTest(provider) {
            this.testing = provider.provider;

            fetch('{{ route('settings.ai-provider.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ provider: provider.provider }),
            })
            .then(r => r.json())
            .then(data => {
                this.testing = null;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: data.success ? 'success' : 'error', message: data.message || 'Connection test complete.' }
                }));
            })
            .catch(() => {
                this.testing = null;
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Connection failed.' } }));
            });
        },

        async toggleProviderActive(provider) {
            if (!provider.provider_id || this.togglingActive === provider.provider) return;
            this.togglingActive = provider.provider;
            try {
                const res = await fetch(`/settings/ai-providers/${provider.provider_id}/toggle-active`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                }).then(r => r.json());
                if (res.success) {
                    this.allProviders = this.allProviders.map(p => p.provider === provider.provider
                        ? { ...p, is_active: res.is_active, configured: p.has_key && res.is_active }
                        : p
                    );
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'info', message: provider.name + ' ' + (res.is_active ? 'activated.' : 'deactivated.') }
                    }));
                }
            } finally {
                this.togglingActive = null;
            }
        },

        removeProvider(provider) {
            if (!provider.provider_id || !confirm('Remove ' + provider.name + ' and its API key?')) return;

            fetch('/settings/ai-providers/' + provider.provider_id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                this.allProviders = this.allProviders.map(p => p.provider === provider.provider
                    ? { ...p, has_key: false, is_active: false, configured: false, provider_id: null, is_default: false, updated_at: null, key_count: 0, keys: [] }
                    : p
                );
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: provider.name + ' removed.' } }));
            });
        },

        categoryStyle(category) {
            return {
                text: 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe',
                voice: 'background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff',
                local: 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0',
            }[category] || 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)';
        },

        fmtDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
    };
}

function teamMembersPayload() {
    try {
        const src = document.getElementById('team-members-data');
        return src ? JSON.parse(src.textContent || '[]') : [];
    } catch (e) { return []; }
}

function teamManager() {
    return {
        ...dtMixin({ perPage: 10 }),

        members:       teamMembersPayload(),
        loading:       false,
        fetchError:    '',
        showAddModal:  false,
        showEditModal: false,
        editTarget:    null,
        addSaving:     false,
        editSaving:    false,
        addError:      '',
        addForm:  { name: '', email: '', password: '', role: 'user' },
        editForm: { name: '', email: '', password: '', role: 'user', is_active: true },

        statusFilter: 'all',
        roleFilter:   '',
        sortBy:       'name_asc',

        rolePills: [
            { val: 'admin',     label: 'Admin',     color: 'background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff' },
            { val: 'developer', label: 'Developer',  color: 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe' },
            { val: 'user',      label: 'User',       color: 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0' },
        ],

        get filtered() {
            let list = this.members;
            if (this.statusFilter === 'active')   list = list.filter(m => m.is_active);
            if (this.statusFilter === 'inactive') list = list.filter(m => !m.is_active);
            if (this.roleFilter) list = list.filter(m => m.role === this.roleFilter);
            if (this.search.trim()) {
                const q = this.search.trim().toLowerCase();
                list = list.filter(m =>
                    (m.name  || '').toLowerCase().includes(q) ||
                    (m.email || '').toLowerCase().includes(q) ||
                    (m.role  || '').toLowerCase().includes(q)
                );
            }
            const sorted = [...list].sort((a, b) => {
                if (this.sortBy === 'name_asc')  return (a.name || '').localeCompare(b.name || '');
                if (this.sortBy === 'name_desc') return (b.name || '').localeCompare(a.name || '');
                if (this.sortBy === 'newest')    return new Date(b.created_at) - new Date(a.created_at);
                if (this.sortBy === 'oldest')    return new Date(a.created_at) - new Date(b.created_at);
                return 0;
            });
            const self = sorted.filter(m => m.is_self);
            const others = sorted.filter(m => !m.is_self);
            return [...self, ...others];
        },

        get paginated() {
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get pageRange() {
            return this.dtPageRange(this.totalPages, this.page);
        },

        roleCount(val) {
            return this.members.filter(m => m.role === val).length;
        },

        roleStyle(role) {
            return {
                admin:     'background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff',
                developer: 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe',
                user:      'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0',
            }[role] || 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)';
        },

        highlight(text) {
            return this.dtHighlight(text, this.search.trim());
        },

        init() {},

        fmtDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        openEdit(m) {
            this.editTarget = m;
            this.editForm = { name: m.name, email: m.email, password: '', role: m.role, is_active: !!m.is_active };
            this.showEditModal = true;
        },

        async addMember() {
            this.addError = '';
            this.addSaving = true;
            try {
                const res = await fetch('{{ route("settings.team.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.addForm),
                });
                const data = await res.json();
                if (!res.ok) {
                    const errs = data.errors ? Object.values(data.errors).flat() : [data.message || 'Failed to add member.'];
                    this.addError = errs.join(' ');
                    return;
                }
                this.members = [{ ...data.member, is_self: false }, ...this.members];
                this.showAddModal = false;
                this.addForm = { name: '', email: '', password: '', role: 'user' };
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
            } catch (e) {
                this.addError = 'Network error. Please try again.';
            } finally {
                this.addSaving = false;
            }
        },

        teamUrl(id) {
            return '{{ route("settings.team.update", ["user" => "__ID__"]) }}'.replace('__ID__', id);
        },

        async saveEdit() {
            this.editSaving = true;
            try {
                const payload = { ...this.editForm };
                if (!payload.password) delete payload.password;
                const res = await fetch(this.teamUrl(this.editTarget.id), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const updated = data.member || payload;
                    this.members = this.members.map(m => m.id === this.editTarget.id ? { ...m, ...updated } : m);
                    this.showEditModal = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message || 'Could not save changes.' } }));
                }
            } catch(e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Network error saving changes.' } }));
            } finally {
                this.editSaving = false;
            }
        },

        async updateRole(m, role) {
            const prev = m.role;
            this.members = this.members.map(x => x.id === m.id ? { ...x, role } : x);
            try {
                const res = await fetch(this.teamUrl(m.id), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ role }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    this.members = this.members.map(x => x.id === m.id ? { ...x, role: prev } : x);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message || 'Could not update role.' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                }
            } catch(e) {
                this.members = this.members.map(x => x.id === m.id ? { ...x, role: prev } : x);
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Network error updating role.' } }));
            }
        },

        async toggleStatus(m) {
            const newActive = !m.is_active;
            this.members = this.members.map(x => x.id === m.id ? { ...x, is_active: newActive } : x);
            try {
                const res = await fetch(this.teamUrl(m.id), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ is_active: newActive }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    this.members = this.members.map(x => x.id === m.id ? { ...x, is_active: !newActive } : x);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message || 'Could not update status.' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                }
            } catch(e) {
                this.members = this.members.map(x => x.id === m.id ? { ...x, is_active: !newActive } : x);
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Network error updating status.' } }));
            }
        },

        async removeMember(m) {
            if (!confirm('Remove ' + m.name + ' from the team? This cannot be undone.')) return;
            try {
                const res = await fetch(this.teamUrl(m.id), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.members = this.members.filter(x => x.id !== m.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message || 'Could not remove member.' } }));
                }
            } catch(e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Network error removing member.' } }));
            }
        },
    };
}
</script>
@endpush
