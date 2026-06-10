@extends('layouts.app')
@section('title', 'System Updates')
@section('header', 'System Updates')

@push('head')
<style>
.up-wrap{display:grid;grid-template-columns:210px 1fr;gap:24px;align-items:start;max-width:880px;}
.st-nav{background:var(--card-bg);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow);position:sticky;top:24px;}
.st-nav-user{padding:16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:var(--hover-bg);}
.st-nav-item{display:flex;align-items:center;gap:10px;padding:10px 14px;font-size:13px;font-weight:500;color:var(--text-2);cursor:pointer;border:none;background:transparent;width:100%;text-align:left;transition:all .13s;border-radius:0;border-left:3px solid transparent;text-decoration:none;}
.st-nav-item:hover{background:var(--hover-bg);color:var(--text-1);}
.st-nav-item.active{color:var(--brand);background:var(--brand-light,#eef2ff);border-left-color:var(--brand);font-weight:700;}
.st-nav-ico{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--hover-bg);flex-shrink:0;}
.st-nav-item.active .st-nav-ico{background:var(--brand);box-shadow:0 2px 6px var(--brand-ring);}
.st-nav-item.active .st-nav-ico svg{stroke:#fff!important;}
.st-nav-sep{height:1px;background:var(--border);margin:4px 0;}
.st-nav-label{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--text-3);padding:8px 14px 4px;}
.ucard{border-radius:14px;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;margin-bottom:16px;}
.ucard-hd{padding:16px 20px;border-bottom:1px solid var(--border);background:var(--hover-bg);display:flex;align-items:center;gap:10px;}
.ucard-body{padding:20px;}
.vbadge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:700;}
.vbadge-current{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}
.vbadge-latest{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;}
.vbadge-uptodate{background:#f0fdf4;color:#15803d;border:1px solid #86efac;}
.vbadge-update{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;}
.ubtn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;border:none;}
.ubtn-primary{background:var(--brand);color:#fff;}
.ubtn-primary:hover{filter:brightness(1.1);transform:translateY(-1px);}
.ubtn-ghost{background:transparent;color:var(--text-2);border:1.5px solid var(--border);}
.ubtn-ghost:hover{background:var(--hover-bg);color:var(--text-1);}
.ubtn:disabled{opacity:.55;cursor:not-allowed;transform:none!important;filter:none!important;}
.step-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:var(--hover-bg);color:var(--text-2);border:1px solid var(--border);}
.hist-row:hover{background:var(--hover-bg);}
.prog-bar-wrap{width:100%;height:6px;border-radius:3px;background:var(--hover-bg);overflow:hidden;}
.prog-bar{height:100%;border-radius:3px;background:var(--brand);transition:width .4s ease;}
.info-row{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-3);}
.info-row svg{width:14px;height:14px;stroke:currentColor;flex-shrink:0;}
.changelog-body{font-size:13px;color:var(--text-2);line-height:1.7;padding:12px 16px;background:var(--hover-bg);border-radius:8px;margin-top:10px;}
</style>
@endpush

@section('content')
<div class="up-wrap" x-data="updatesApp()" x-init="init()">

    {{-- ── LEFT NAV (mirrors settings layout) ── --}}
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
            <a href="{{ route('settings.index') }}#profile" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
                Profile
            </a>
            <div class="st-nav-sep"></div>
            <div class="st-nav-label">Workspace</div>
            <a href="{{ route('settings.index') }}#ai" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                AI Providers
            </a>
            <a href="{{ route('settings.index') }}#branding" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg></div>
                Branding
            </a>
            <div class="st-nav-sep"></div>
            <div class="st-nav-label">System</div>
            <a href="{{ route('settings.updates') }}" class="st-nav-item active" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#fff" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
                Updates
            </a>
            <a href="{{ route('settings.index') }}#system_config" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg></div>
                System Config
            </a>
            <div class="st-nav-sep"></div>
            <div class="st-nav-label">More</div>
            <a href="{{ route('settings.index') }}#notifications" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                Notifications
            </a>
            <a href="{{ route('settings.index') }}#integrations" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                Integrations
            </a>
            <a href="{{ route('settings.index') }}#team" class="st-nav-item" style="text-decoration:none;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#94a3b8" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                Team
            </a>
            <a href="{{ route('settings.index') }}#danger" class="st-nav-item" style="text-decoration:none;color:#ef4444 !important;">
                <div class="st-nav-ico"><svg style="width:14px;height:14px;stroke:#ef4444" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                Danger Zone
            </a>
        </div>
    </nav>

    {{-- ── CONTENT ── --}}
    <div>

        {{-- ── Version Status Card ── --}}
        <div class="ucard">
            <div class="ucard-hd">
                <div style="width:32px;height:32px;border-radius:9px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;stroke:#6366f1" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">RyaanCMS Updates</div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">One-click updates — sequential versioning applied automatically</div>
                </div>
                <button @click="checkUpdates()" :disabled="checking"
                        class="ubtn ubtn-ghost" style="font-size:12px;padding:7px 14px;">
                    <svg :class="checking ? 'spin' : ''" style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span x-text="checking ? 'Checking...' : 'Check Now'"></span>
                </button>
            </div>
            <div class="ucard-body">

                {{-- Version row --}}
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;color:var(--text-3);font-weight:600;">INSTALLED</span>
                        <span class="vbadge vbadge-current">v{{ $currentVersion }}</span>
                    </div>
                    <svg style="width:16px;height:16px;stroke:var(--text-3);flex-shrink:0" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;color:var(--text-3);font-weight:600;">LATEST</span>
                        <span class="vbadge" :class="hasUpdates ? 'vbadge-update' : 'vbadge-uptodate'"
                              x-text="'v' + latestVersion"></span>
                    </div>
                    <div x-show="!hasUpdates && !checking"
                         style="display:flex;align-items:center;gap:5px;font-size:12px;color:#16a34a;font-weight:600;">
                        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Up to date
                    </div>
                </div>

                @if($error)
                <div style="padding:12px 14px;border-radius:10px;font-size:12.5px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;margin-bottom:16px;">
                    <strong>Could not reach update server:</strong> {{ $error }}
                    <div style="margin-top:4px;color:#b91c1c;font-size:11.5px;">Check your server's outbound internet connection or try again later.</div>
                </div>
                @endif

                {{-- Server requirements --}}
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;padding:12px 14px;border-radius:10px;background:var(--hover-bg);border:1px solid var(--border);">
                    <div class="info-row">
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                        PHP {{ $serverInfo['php'] }}
                    </div>
                    <div class="info-row">
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Laravel {{ $serverInfo['laravel'] }}
                    </div>
                    <div class="info-row" :style="{{ $serverInfo['zip'] ? "'color:var(--text-3)'" : "'color:#dc2626'" }}">
                        @if($serverInfo['zip'])
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        ZipArchive OK
                        @else
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        ZipArchive missing — contact host
                        @endif
                    </div>
                    <div class="info-row" :style="{{ $serverInfo['writable'] ? "'color:var(--text-3)'" : "'color:#dc2626'" }}">
                        @if($serverInfo['writable'])
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Storage writable
                        @else
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        storage/ not writable — run chmod 775
                        @endif
                    </div>
                </div>

                {{-- Pending updates list --}}
                <div x-show="hasUpdates">
                    <div style="font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:10px;"
                         x-text="pendingUpdates.length + ' update' + (pendingUpdates.length > 1 ? 's' : '') + ' available'"></div>

                    {{-- Sequential path indicator --}}
                    <div x-show="pendingUpdates.length > 1" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:14px;padding:10px 12px;border-radius:8px;background:var(--hover-bg);border:1px solid var(--border);">
                        <svg style="width:13px;height:13px;stroke:var(--brand);flex-shrink:0" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span style="font-size:11.5px;color:var(--text-2);">Sequential update path:</span>
                        <span class="step-pill" style="background:var(--brand-light);color:var(--brand);border-color:transparent;">v{{ $currentVersion }}</span>
                        <template x-for="(v, i) in pendingUpdates" :key="v.version">
                            <div style="display:flex;align-items:center;gap:4px;">
                                <svg style="width:12px;height:12px;stroke:var(--text-3)" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span class="step-pill" x-text="'v'+v.version"></span>
                            </div>
                        </template>
                    </div>

                    <template x-for="(upd, i) in pendingUpdates" :key="upd.version">
                        <div style="border:1px solid var(--border);border-radius:12px;margin-bottom:10px;overflow:hidden;">
                            <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--hover-bg);">
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <span style="font-size:14px;font-weight:700;color:var(--text-1);" x-text="'v' + upd.version"></span>
                                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;text-transform:uppercase;"
                                              :style="upd.critical ? 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca' : 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0'"
                                              x-text="upd.critical ? '⚠ Critical' : upd.type || 'Feature'"></span>
                                    </div>
                                    <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;" x-text="'Released: ' + upd.released_at"></div>
                                </div>
                                <button @click="applyUpdate(upd.version)" :disabled="updating"
                                        class="ubtn ubtn-primary" style="font-size:12px;padding:7px 14px;">
                                    <svg style="width:13px;height:13px;stroke:#fff" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span x-text="i === 0 ? 'Update' : 'Skip to this'"></span>
                                </button>
                                <button @click="upd._open = !upd._open" style="background:none;border:none;cursor:pointer;padding:4px;">
                                    <svg style="width:16px;height:16px;stroke:var(--text-3);transition:transform .2s;" :style="upd._open ? 'transform:rotate(180deg)' : ''" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <div x-show="upd._open" x-transition style="padding:0 16px 14px;">
                                <div class="changelog-body" x-text="upd.changelog"></div>
                            </div>
                        </div>
                    </template>

                    {{-- Update All button --}}
                    <div x-show="pendingUpdates.length > 1" style="margin-top:6px;">
                        <button @click="applyUpdate('latest')" :disabled="updating" class="ubtn ubtn-primary" style="width:100%;justify-content:center;padding:11px;">
                            <svg style="width:14px;height:14px;stroke:#fff" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="'Update All — ' + pendingUpdates.length + ' steps to v' + latestVersion"></span>
                        </button>
                    </div>
                </div>

                {{-- Up to date state --}}
                <div x-show="!hasUpdates && !checking"
                     style="text-align:center;padding:24px 0;color:var(--text-3);">
                    <svg style="width:36px;height:36px;stroke:#22c55e;margin:0 auto 8px;" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div style="font-size:14px;font-weight:700;color:var(--text-1);">You're running the latest version</div>
                    <div style="font-size:12.5px;margin-top:4px;">RyaanCMS v{{ $currentVersion }} is up to date.</div>
                </div>

                {{-- Progress overlay --}}
                <div x-show="updating" x-transition style="margin-top:16px;padding:16px;border-radius:12px;background:var(--hover-bg);border:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <svg class="spin" style="width:16px;height:16px;stroke:var(--brand)" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span style="font-size:13px;font-weight:600;color:var(--text-1);" x-text="progressMsg"></span>
                    </div>
                    <div class="prog-bar-wrap">
                        <div class="prog-bar" :style="'width:' + progress + '%'"></div>
                    </div>
                    <div style="margin-top:6px;font-size:11.5px;color:var(--text-3);">
                        Do not close this tab while updating is in progress.
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Update History ── --}}
        @if($history->isNotEmpty())
        <div class="ucard">
            <div class="ucard-hd">
                <div style="width:32px;height:32px;border-radius:9px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:15px;height:15px;stroke:#16a34a" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">Update History</div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">All applied updates</div>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);">
                            <th style="padding:10px 20px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Version</th>
                            <th style="padding:10px 16px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Type</th>
                            <th style="padding:10px 16px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Status</th>
                            <th style="padding:10px 16px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Date</th>
                            <th style="padding:10px 16px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $h)
                        <tr class="hist-row" style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 20px;">
                                <span style="font-weight:700;color:var(--text-1);">v{{ $h->version }}</span>
                                @if($h->package_key)
                                <div style="font-size:11px;color:var(--text-3);">{{ $h->package_key }}</div>
                                @endif
                            </td>
                            <td style="padding:10px 16px;color:var(--text-2);">{{ ucfirst($h->type) }}</td>
                            <td style="padding:10px 16px;">
                                @if($h->status === 'success')
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;background:#f0fdf4;color:#15803d;">
                                    <svg style="width:10px;height:10px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Success
                                </span>
                                @elseif($h->status === 'failed')
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;background:#fef2f2;color:#dc2626;" title="{{ $h->error_message }}">
                                    <svg style="width:10px;height:10px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Failed
                                </span>
                                @else
                                <span style="padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;background:#fff7ed;color:#c2410c;">{{ ucfirst($h->status) }}</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;color:var(--text-3);">
                                {{ $h->started_at ? $h->started_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td style="padding:10px 16px;color:var(--text-3);">
                                {{ $h->duration ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── SSL Fix Card ── --}}
        <div class="ucard" id="ssl-fix">
            <div class="ucard-hd">
                <div style="width:32px;height:32px;border-radius:9px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:15px;height:15px;stroke:#f59e0b" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:var(--text-1);">SSL Fix — ERR_SSL_VERSION_OR_CIPHER_MISMATCH</div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:1px;">How to fix HTTPS on shared hosting</div>
                </div>
            </div>
            <div class="ucard-body" style="display:flex;flex-direction:column;gap:14px;">
                <div style="padding:12px 14px;border-radius:10px;background:#fef3c7;border:1px solid #fde68a;font-size:12.5px;color:#92400e;">
                    <strong>Root cause:</strong> Your domain has no valid SSL certificate installed, or the hosting server only supports old TLS 1.0/1.1 which modern browsers reject.
                </div>

                {{-- Option 1: cPanel AutoSSL --}}
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:8px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:99px;background:var(--brand);color:#fff;font-size:11px;font-weight:800;margin-right:6px;">1</span>
                        Fix via cPanel AutoSSL (Recommended — Free)
                    </div>
                    <ol style="font-size:12.5px;color:var(--text-2);line-height:2;padding-left:20px;">
                        <li>Login to your <strong>cPanel</strong></li>
                        <li>Go to <strong>Security → SSL/TLS</strong></li>
                        <li>Click <strong>"Manage SSL Sites"</strong> → Check if your domain has a certificate</li>
                        <li>If not: go back → <strong>"SSL/TLS Status"</strong> → Click <strong>"Run AutoSSL"</strong></li>
                        <li>Wait 2–5 minutes, then refresh your site</li>
                    </ol>
                    <div style="font-size:11.5px;color:var(--text-3);">Most cPanel hosts (Hostinger, Namecheap, Bluehost etc.) offer free Let's Encrypt via AutoSSL.</div>
                </div>

                <div style="height:1px;background:var(--border);"></div>

                {{-- Option 2: Cloudflare --}}
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:8px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:99px;background:#f59e0b;color:#fff;font-size:11px;font-weight:800;margin-right:6px;">2</span>
                        Fix via Cloudflare (Free — works even without host SSL)
                    </div>
                    <ol style="font-size:12.5px;color:var(--text-2);line-height:2;padding-left:20px;">
                        <li>Create a free account at <strong>cloudflare.com</strong></li>
                        <li>Add your domain → Cloudflare gives you two nameservers</li>
                        <li>In your domain registrar, replace existing nameservers with Cloudflare's</li>
                        <li>In Cloudflare Dashboard → <strong>SSL/TLS → Overview</strong> → Set mode to <strong>"Flexible"</strong></li>
                        <li>Wait up to 24h for DNS propagation — then HTTPS works automatically</li>
                    </ol>
                    <div style="font-size:11.5px;color:var(--text-3);">Cloudflare handles TLS at the edge, bypassing your host's old cipher suites entirely.</div>
                </div>

                <div style="height:1px;background:var(--border);"></div>

                {{-- Option 3: .htaccess --}}
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:8px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:99px;background:#6366f1;color:#fff;font-size:11px;font-weight:800;margin-right:6px;">3</span>
                        After SSL is fixed — force HTTPS in .htaccess
                    </div>
                    <div style="font-size:12.5px;color:var(--text-2);margin-bottom:8px;">Once your certificate is active, add this to <code>public/.htaccess</code> to force HTTPS for all visitors:</div>
                    <pre style="background:#0f172a;color:#e2e8f0;padding:14px;border-radius:10px;font-size:11.5px;overflow-x:auto;line-height:1.8;"># Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# HSTS (tells browsers to always use HTTPS)
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains"</pre>
                </div>
            </div>
        </div>

    </div>{{-- /content --}}
</div>{{-- /up-wrap --}}

{{-- Toast --}}
<div x-data x-show="$store.toast.show" x-transition
     style="position:fixed;bottom:24px;right:24px;z-index:9999;min-width:280px;max-width:380px;padding:14px 18px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.18);font-size:13px;font-weight:500;"
     :style="$store.toast.success ? 'background:#f0fdf4;color:#15803d;border:1px solid #86efac' : 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca'">
    <span x-text="$store.toast.message"></span>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
</style>

<script>
function updatesApp() {
    return {
        hasUpdates: @json($hasUpdates),
        latestVersion: @json($latest),
        pendingUpdates: @json($pending).map(v => ({...v, _open: false})),
        checking: false,
        updating: false,
        progress: 0,
        progressMsg: '',

        init() {
            Alpine.store('toast', { show: false, message: '', success: true });
        },

        toast(message, success = true) {
            Alpine.store('toast', { show: true, message, success });
            setTimeout(() => Alpine.store('toast').show = false, 4500);
        },

        async checkUpdates() {
            this.checking = true;
            try {
                const r = await fetch('{{ route('settings.updates.check') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                               'Accept': 'application/json' }
                });
                const d = await r.json();
                if (d.success) {
                    this.latestVersion  = d.latest;
                    this.hasUpdates     = d.hasUpdates;
                    this.pendingUpdates = (d.pending || []).map(v => ({...v, _open: false}));
                    this.toast(d.hasUpdates
                        ? `${d.count} update${d.count > 1 ? 's' : ''} available — v${d.latest} is ready.`
                        : 'RyaanCMS is up to date.');
                } else {
                    this.toast(d.message || 'Could not check for updates.', false);
                }
            } catch (e) {
                this.toast('Network error: ' + e.message, false);
            } finally {
                this.checking = false;
            }
        },

        async applyUpdate(version) {
            if (this.updating) return;
            this.updating    = true;
            this.progress    = 5;
            this.progressMsg = version === 'latest'
                ? `Preparing ${this.pendingUpdates.length} sequential updates…`
                : `Preparing update to v${version}…`;

            const tick = setInterval(() => {
                if (this.progress < 90) this.progress += Math.random() * 8;
            }, 1200);

            try {
                const r = await fetch('{{ route('settings.updates.apply') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ version })
                });
                const d = await r.json();
                clearInterval(tick);
                this.progress = 100;

                if (d.success) {
                    this.toast(d.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.progressMsg = 'Update failed.';
                    this.toast(d.message || 'Update failed.', false);
                    this.updating = false;
                    this.progress = 0;
                }
            } catch (e) {
                clearInterval(tick);
                this.toast('Network error: ' + e.message, false);
                this.updating = false;
                this.progress = 0;
            }
        }
    };
}
</script>
@endsection
