@extends('layouts.app')
@section('title', 'Templates')

@section('content')
<div style="max-width:1200px;margin:0 auto;padding:28px 24px;" x-data="templateManager()">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:var(--text-1);margin:0 0 4px;">Templates</h1>
            <p style="font-size:13.5px;color:var(--text-3);margin:0;">
                @if($siteProject)
                    Managing templates for <strong style="color:var(--text-2);">{{ $siteProject->name }}</strong>
                    &nbsp;&middot;&nbsp;<a href="{{ route('home') }}" target="_blank" style="color:var(--brand);text-decoration:none;font-weight:600;">View Site &rarr;</a>
                @else
                    Choose a project to manage as your public site
                @endif
            </p>
        </div>
        @if(!$siteProject)
        <a href="{{ route('settings.index') }}#system_config"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:var(--brand);color:#fff;font-size:13px;font-weight:600;text-decoration:none;">
            <svg style="width:14px;height:14px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Configure Public Site
        </a>
        @endif
    </div>

    {{-- No site project banner --}}
    @if(!$siteProject)
    <div style="padding:20px 24px;border-radius:14px;background:color-mix(in srgb,var(--brand) 6%,transparent);border:1.5px dashed color-mix(in srgb,var(--brand) 30%,transparent);margin-bottom:28px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:color-mix(in srgb,var(--brand) 12%,transparent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:20px;height:20px;stroke:var(--brand)" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size:13.5px;font-weight:700;color:var(--text-1);margin-bottom:2px;">No public site project set</div>
            <div style="font-size:12.5px;color:var(--text-3);">Go to <strong>Settings &rarr; System Config</strong> and select a project under &ldquo;Public Site Project&rdquo;. After that, templates activated here will serve at your root domain.</div>
        </div>
    </div>
    @endif

    {{-- Active Template (hero card) --}}
    @if($activeKey && isset($templates[$activeKey]))
    @php $activeTpl = $templates[$activeKey]; @endphp
    <div style="margin-bottom:32px;">
        <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:10px;">Active Template</div>
        <div style="border-radius:18px;border:2.5px solid var(--brand);overflow:hidden;background:var(--card-bg);box-shadow:0 0 0 4px color-mix(in srgb,var(--brand) 10%,transparent),var(--shadow-lg);display:flex;gap:0;">
            {{-- Preview area --}}
            <div style="width:340px;min-height:200px;flex-shrink:0;position:relative;overflow:hidden;background:linear-gradient(135deg,{{ $activeTpl['color'] }}22,{{ $activeTpl['color'] }}08);">
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;">
                    <div style="width:64px;height:64px;border-radius:16px;background:{{ $activeTpl['color'] }};display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px {{ $activeTpl['color'] }}44;">
                        <span style="font-size:28px;line-height:1;">{{ $activeTpl['icon'] }}</span>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:16px;font-weight:800;color:{{ $activeTpl['color'] }};">{{ $activeTpl['name'] }}</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;">{{ $activeTpl['category'] }}</div>
                    </div>
                </div>
                <div style="position:absolute;top:12px;left:12px;background:var(--brand);color:#fff;font-size:10px;font-weight:800;padding:4px 10px;border-radius:99px;letter-spacing:.04em;">
                    &#10003; ACTIVE
                </div>
            </div>
            {{-- Info area --}}
            <div style="flex:1;padding:28px 28px;display:flex;flex-direction:column;justify-content:center;gap:14px;">
                <div>
                    <h2 style="font-size:20px;font-weight:800;color:var(--text-1);margin:0 0 6px;">{{ $activeTpl['name'] }}</h2>
                    <p style="font-size:13px;color:var(--text-3);margin:0;line-height:1.5;">{{ $activeTpl['description'] }}</p>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($activeTpl['tags'] ?? [] as $tag)
                    <span style="font-size:10.5px;font-weight:600;padding:3px 10px;border-radius:99px;background:var(--hover-bg);color:var(--text-3);border:1px solid var(--border);">{{ $tag }}</span>
                    @endforeach
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding-top:4px;">
                    @if($siteProject)
                    <a href="{{ route('site.serve', $siteProject) }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:10px;background:var(--brand);color:#fff;font-size:13px;font-weight:600;text-decoration:none;">
                        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Live Preview
                    </a>
                    @endif
                    <button type="button"
                            @click="deactivate('{{ $activeKey }}')"
                            :disabled="loading === '{{ $activeKey }}'"
                            style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;border:1.5px solid var(--border);background:var(--surface-raised);color:var(--text-2);cursor:pointer;"
                            x-text="loading === '{{ $activeKey }}' ? 'Deactivating...' : 'Deactivate'">
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- All templates grid --}}
    <div style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:14px;">
        {{ $activeKey ? 'All Templates' : 'Available Templates' }}
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
        @foreach($templates as $key => $tpl)
        @php
            $status = $statusMap[$key] ?? 'available';
            $isActive = $status === 'active';
        @endphp
        @if($isActive) @continue @endif

        <div style="border-radius:16px;border:1.5px solid var(--border);background:var(--card-bg);overflow:hidden;box-shadow:var(--shadow);transition:box-shadow .2s,border-color .2s;position:relative;"
             @mouseenter="$el.style.boxShadow='var(--shadow-lg)';$el.style.borderColor='color-mix(in srgb,var(--brand) 30%,var(--border))'"
             @mouseleave="$el.style.boxShadow='var(--shadow)';$el.style.borderColor='var(--border)'">

            {{-- Preview thumbnail --}}
            <div style="height:160px;position:relative;overflow:hidden;cursor:pointer;background:linear-gradient(135deg,{{ $tpl['color'] }}18,{{ $tpl['color'] }}06);">
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;">
                    <div style="width:52px;height:52px;border-radius:14px;background:{{ $tpl['color'] }};display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px {{ $tpl['color'] }}44;">
                        <span style="font-size:24px;line-height:1;">{{ $tpl['icon'] }}</span>
                    </div>
                    <div style="font-size:11.5px;font-weight:700;color:{{ $tpl['color'] }};">{{ $tpl['category'] }}</div>
                </div>
                {{-- Hover overlay with action buttons --}}
                <div class="tmpl-hover-overlay"
                     style="position:absolute;inset:0;background:rgba(0,0,0,.55);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;opacity:0;transition:opacity .18s;">
                    @if($siteProject)
                    <button type="button"
                            @click.stop="activate('{{ $key }}')"
                            :disabled="loading === '{{ $key }}'"
                            style="padding:8px 24px;border-radius:10px;background:var(--brand);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;width:160px;"
                            x-text="loading==='{{ $key }}' ? 'Activating...' : 'Activate'">
                    </button>
                    <a href="{{ route('site.serve', $siteProject) }}" target="_blank"
                       style="padding:7px 24px;border-radius:10px;background:rgba(255,255,255,.15);backdrop-filter:blur(4px);color:#fff;font-size:12.5px;font-weight:600;text-decoration:none;border:1px solid rgba(255,255,255,.3);width:160px;text-align:center;box-sizing:border-box;">
                        Live Preview
                    </a>
                    @endif
                </div>
            </div>

            {{-- Card footer --}}
            <div style="padding:14px 16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                    <div style="font-size:14px;font-weight:700;color:var(--text-1);">{{ $tpl['name'] }}</div>
                    @if($status === 'installed')
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;flex-shrink:0;">Installed</span>
                    @endif
                </div>
                <p style="font-size:11.5px;color:var(--text-3);margin:0 0 12px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $tpl['description'] }}</p>
                <div style="display:flex;align-items:center;gap:8px;">
                    @if($siteProject)
                    <button type="button"
                            @click="activate('{{ $key }}')"
                            :disabled="loading === '{{ $key }}'"
                            style="flex:1;padding:7px 14px;border-radius:9px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;transition:all .15s;"
                            :style="loading==='{{ $key }}' ? 'background:var(--hover-bg);color:var(--text-3);' : 'background:var(--brand);color:#fff;'"
                            x-text="loading==='{{ $key }}' ? 'Activating...' : '{{ $status === 'installed' ? 'Activate' : 'Install & Activate' }}'">
                    </button>
                    @else
                    <span style="flex:1;padding:7px 14px;border-radius:9px;font-size:12px;font-weight:600;background:var(--hover-bg);color:var(--text-3);text-align:center;cursor:default;">No site project</span>
                    @endif
                    <a href="{{ route('marketplace.templates') }}" title="Details"
                       style="padding:7px 10px;border-radius:9px;border:1.5px solid var(--border);background:var(--surface-raised);color:var(--text-3);text-decoration:none;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Toast feedback --}}
    <template x-if="message">
        <div x-transition:enter="transition duration-200" x-transition:leave="transition duration-150"
             :style="messageOk ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;'"
             style="position:fixed;bottom:24px;right:24px;padding:14px 20px;border-radius:14px;font-size:13.5px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;max-width:360px;"
             x-text="message">
        </div>
    </template>
</div>

<style>
.tmpl-hover-overlay { pointer-events: none; }
div:hover > .tmpl-hover-overlay { opacity: 1 !important; pointer-events: auto !important; }
</style>

<script>
function templateManager() {
    return {
        loading: null,
        message: null,
        messageOk: true,

        async activate(key) {
            this.loading = key;
            this.message = null;
            try {
                const res = await fetch(`/templates/${key}/activate-site`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                const json = await res.json().catch(() => ({}));
                this.messageOk = !!json.success;
                this.message   = json.message || (json.success ? 'Template activated!' : 'Activation failed.');
                if (json.success) setTimeout(() => location.reload(), 1200);
            } catch {
                this.message   = 'Network error.';
                this.messageOk = false;
            }
            this.loading = null;
            setTimeout(() => this.message = null, 4000);
        },

        async deactivate(key) {
            this.loading = key;
            this.message = null;
            try {
                const res = await fetch(`/templates/${key}/deactivate-site`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                const json = await res.json().catch(() => ({}));
                this.messageOk = !!json.success;
                this.message   = json.message || (json.success ? 'Template deactivated.' : 'Deactivation failed.');
                if (json.success) setTimeout(() => location.reload(), 1200);
            } catch {
                this.message   = 'Network error.';
                this.messageOk = false;
            }
            this.loading = null;
            setTimeout(() => this.message = null, 4000);
        },
    };
}
</script>
@endsection
