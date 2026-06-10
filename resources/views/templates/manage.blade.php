@extends('layouts.app')
@section('title', 'Templates')
@section('header', 'Templates')

@php
// Precompute template data for JS — avoids Blade parsing issues with [] inside @json()
$tmplData = [];
foreach ($templates as $k => $t) {
    $tmplData[] = [
        'key'    => $k,
        'name'   => $t['name'],
        'desc'   => $t['description'],
        'cat'    => $t['category'],
        'tags'   => implode(' ', $t['tags'] ?? []),
        'status' => $statusMap[$k] ?? 'available',
    ];
}
$tmplDataJson  = json_encode($tmplData);
$activeKeyJson = json_encode($activeKey);
@endphp

@push('head')
<style>
.tmpl-toolbar {
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    padding:14px 0 6px;
}
.tmpl-search {
    display:flex;align-items:center;gap:8px;
    background:var(--surface-base);border:1.5px solid var(--border);
    border-radius:11px;padding:0 12px;flex:1;min-width:200px;max-width:380px;
    transition:border-color .13s,box-shadow .13s;
}
.tmpl-search:focus-within { border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-ring); }
.tmpl-search svg { color:var(--text-3);flex-shrink:0;width:15px;height:15px; }
.tmpl-search input { border:none;outline:none;background:transparent;font-size:13px;color:var(--text-1);padding:9px 0;width:100%;font-family:inherit; }
.tmpl-search input::placeholder { color:var(--text-3); }

.tmpl-pill {
    padding:5px 12px;border-radius:99px;font-size:11.5px;font-weight:700;
    border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-3);
    cursor:pointer;transition:all .13s;
}
.tmpl-pill.on { background:var(--brand);color:#fff;border-color:var(--brand); }

.tmpl-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
    gap:14px;
    margin-top:14px;
}
.tmpl-card {
    border-radius:16px;overflow:hidden;
    background:var(--surface-base);
    border:1px solid var(--border);
    border-left:3px solid transparent;
    box-shadow:var(--shadow);
    transition:box-shadow .22s,transform .22s,border-left-color .18s;
}
.tmpl-card:hover {
    border-left-color: var(--tc);
    box-shadow: 0 10px 36px color-mix(in srgb,var(--tc) 22%,transparent),
                0 2px 10px  color-mix(in srgb,var(--tc) 10%,transparent);
    transform: translateY(-3px);
}
.tmpl-card-header {
    height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;
    position:relative;border-bottom:1px solid var(--border);overflow:hidden;
}
.tmpl-card-body { padding:14px 16px;display:flex;flex-direction:column;gap:10px; }
.tmpl-card-name { font-size:13.5px;font-weight:700;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.tmpl-card-desc { font-size:11.5px;color:var(--text-3);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5; }
.btn-tmpl {
    display:inline-flex;align-items:center;justify-content:center;
    padding:7px 14px;border-radius:9px;font-size:12px;font-weight:700;
    border:1.5px solid color-mix(in srgb,var(--tc) 25%,transparent);
    background:color-mix(in srgb,var(--tc) 10%,#fff);
    color:var(--tc);cursor:pointer;transition:all .18s;flex:1;
}
.btn-tmpl:hover:not(:disabled) {
    background:var(--tc);color:#fff;border-color:var(--tc);
    box-shadow:0 3px 10px color-mix(in srgb,var(--tc) 30%,transparent);
    transform:translateY(-1px);
}
.btn-tmpl:disabled { opacity:.55;cursor:not-allowed; }

.tmpl-hero {
    border-radius:18px;border:2px solid var(--brand);overflow:hidden;
    background:var(--surface-base);
    box-shadow:0 0 0 4px color-mix(in srgb,var(--brand) 8%,transparent),var(--shadow-lg);
    display:flex;margin-bottom:24px;
}
.tmpl-hero-thumb {
    width:240px;min-height:160px;flex-shrink:0;position:relative;
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;
}
.tmpl-hero-body { flex:1;padding:24px;display:flex;flex-direction:column;justify-content:center;gap:12px; }
@media(max-width:640px){
    .tmpl-hero { flex-direction:column; }
    .tmpl-hero-thumb { width:100%;min-height:90px; }
}
</style>
@endpush

@section('content')
<div x-data="templateManager()">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <p style="font-size:13px;color:var(--text-3);margin:0 0 2px;">
        @if($siteProject)
            Site: <strong style="color:var(--text-2);">{{ $siteProject->name }}</strong>
            &nbsp;&middot;&nbsp;<a href="{{ route('home') }}" target="_blank" style="color:var(--brand);text-decoration:none;font-weight:600;">View Site →</a>
        @else
            No public site configured.
            <a href="{{ route('settings.index') }}" style="color:var(--brand);font-weight:600;text-decoration:none;">Configure in Settings →</a>
        @endif
    </p>

    {{-- ── Toolbar ─────────────────────────────────────────────────────── --}}
    <div class="tmpl-toolbar">
        <div class="tmpl-search">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input x-model="search" @input="catFilter=''" type="text" placeholder="Search templates…">
            <button x-show="search" @click="search=''" style="color:var(--text-3);background:none;border:none;cursor:pointer;line-height:1;" x-cloak>
                <svg style="width:10px;height:10px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <button @click="catFilter=''" type="button" :class="catFilter==='' ? 'tmpl-pill on' : 'tmpl-pill'">All</button>
        @foreach(array_unique(array_column($templates, 'category')) as $cat)
        <button @click="catFilter = catFilter==='{{ $cat }}' ? '' : '{{ $cat }}'" type="button"
                :class="catFilter==='{{ $cat }}' ? 'tmpl-pill on' : 'tmpl-pill'">{{ $cat }}</button>
        @endforeach

        <span style="font-size:12px;color:var(--text-3);margin-left:auto;" x-text="visibleCount + ' template' + (visibleCount===1?'':'s')"></span>
    </div>

    {{-- ── Active template hero ────────────────────────────────────────── --}}
    @if($activeKey && isset($templates[$activeKey]))
    @php $a = $templates[$activeKey]; @endphp
    <div class="tmpl-hero">
        <div class="tmpl-hero-thumb" style="background:linear-gradient(135deg,{{ $a['color'] }}20,{{ $a['color'] }}06);">
            <div style="position:absolute;top:10px;left:10px;background:var(--brand);color:#fff;font-size:9.5px;font-weight:800;padding:3px 9px;border-radius:99px;letter-spacing:.06em;">✓ ACTIVE</div>
            <div style="width:52px;height:52px;border-radius:14px;background:{{ $a['color'] }};display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px {{ $a['color'] }}44;">
                <span style="font-size:24px;line-height:1;">{{ $a['icon'] }}</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:{{ $a['color'] }};">{{ $a['category'] }}</div>
        </div>
        <div class="tmpl-hero-body">
            <div>
                <h2 style="font-size:18px;font-weight:800;color:var(--text-1);margin:0 0 4px;">{{ $a['name'] }}</h2>
                <p style="font-size:12.5px;color:var(--text-3);margin:0;line-height:1.5;">{{ $a['description'] }}</p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:5px;">
                @foreach($a['tags'] ?? [] as $tag)
                <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:99px;background:var(--hover-bg);color:var(--text-3);border:1px solid var(--border);">{{ $tag }}</span>
                @endforeach
            </div>
            <div style="display:flex;gap:8px;padding-top:4px;">
                @if($siteProject)
                <a href="{{ route('site.serve', $siteProject) }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:9px;background:var(--brand);color:#fff;font-size:12.5px;font-weight:600;text-decoration:none;">
                    <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Live Preview
                </a>
                <a href="{{ route('builder.show', ['project' => $siteProject, 'template' => $activeKey]) }}"
                   style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:9px;background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;font-size:12.5px;font-weight:700;text-decoration:none;">
                    AI Customize
                </a>
                @endif
                <button type="button" @click="deactivate('{{ $activeKey }}')" :disabled="loading==='{{ $activeKey }}'"
                        style="padding:7px 16px;border-radius:9px;font-size:12.5px;font-weight:600;border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-2);cursor:pointer;"
                        x-text="loading==='{{ $activeKey }}' ? 'Deactivating…' : 'Deactivate'">
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Templates grid ──────────────────────────────────────────────── --}}
    <div class="tmpl-grid">
        @foreach($templates as $key => $tpl)
        @php $status = $statusMap[$key] ?? 'available'; $isActive = $status === 'active'; @endphp

        <div class="tmpl-card" style="--tc:{{ $tpl['color'] }}" x-show="isVisible('{{ $key }}')">

            {{-- Header --}}
            <div class="tmpl-card-header" style="background:linear-gradient(135deg,{{ $tpl['color'] }}18,{{ $tpl['color'] }}06);">
                @if($isActive)
                <div style="position:absolute;top:8px;left:8px;background:var(--brand);color:#fff;font-size:9px;font-weight:800;padding:2px 8px;border-radius:99px;">✓ ACTIVE</div>
                @elseif($status === 'installed')
                <div style="position:absolute;top:8px;left:8px;font-size:9px;font-weight:800;padding:2px 8px;border-radius:99px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;">Installed</div>
                @endif
                <div style="width:48px;height:48px;border-radius:13px;background:{{ $tpl['color'] }};display:flex;align-items:center;justify-content:center;box-shadow:0 5px 16px {{ $tpl['color'] }}44;">
                    <span style="font-size:22px;line-height:1;">{{ $tpl['icon'] }}</span>
                </div>
                <div style="font-size:10.5px;font-weight:700;color:{{ $tpl['color'] }};">{{ $tpl['category'] }}</div>
            </div>

            {{-- Body --}}
            <div class="tmpl-card-body">
                <div class="tmpl-card-name">{{ $tpl['name'] }}</div>
                <div class="tmpl-card-desc">{{ $tpl['description'] }}</div>
                <div style="display:flex;gap:6px;">
                    @if($isActive)
                    <button type="button" @click="deactivate('{{ $key }}')" :disabled="loading==='{{ $key }}'"
                            class="btn-tmpl"
                            x-text="loading==='{{ $key }}' ? 'Deactivating…' : 'Deactivate'">
                    </button>
                    @elseif($siteProject)
                    <button type="button" @click="activate('{{ $key }}')" :disabled="loading==='{{ $key }}'"
                            class="btn-tmpl"
                            x-text="loading==='{{ $key }}' ? 'Activating…' : '{{ $status === 'installed' ? 'Activate' : 'Install & Activate' }}'">
                    </button>
                    @else
                    <span class="btn-tmpl" style="opacity:.5;cursor:default;pointer-events:none;">No site project</span>
                    @endif
                    @if($siteProject)
                    <a href="{{ route('builder.show', ['project' => $siteProject, 'template' => $key]) }}"
                       class="btn-tmpl"
                       style="text-decoration:none;background:#eef2ff;color:#4f46e5;border-color:#c7d2fe;">
                        AI Customize
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Empty state --}}
    <div x-show="visibleCount === 0" x-cloak
         style="text-align:center;padding:60px 20px;color:var(--text-3);">
        <svg style="width:40px;height:40px;margin:0 auto 12px;stroke:currentColor;opacity:.4" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p style="font-size:14px;font-weight:600;margin:0 0 4px;">No templates found</p>
        <p style="font-size:12.5px;margin:0;">Try a different search or category</p>
    </div>

    {{-- Toast --}}
    <template x-if="message">
        <div :style="messageOk ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;'"
             style="position:fixed;bottom:24px;right:24px;padding:13px 20px;border-radius:12px;font-size:13px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;max-width:340px;"
             x-text="message"
             x-transition:enter="transition duration-200"
             x-transition:leave="transition duration-150">
        </div>
    </template>
</div>

<script>
(function () {
    const TMPL_DATA  = {!! $tmplDataJson !!};
    const ACTIVE_KEY = {!! $activeKeyJson !!};

    window.templateManager = function () {
        return {
            search:       '',
            catFilter:    '',
            loading:      null,
            message:      null,
            messageOk:    true,
            visibleCount: 0,

            isVisible(key) {
                if (key === ACTIVE_KEY) return false;
                const d = TMPL_DATA.find(t => t.key === key);
                if (!d) return false;
                if (this.catFilter && d.cat !== this.catFilter) return false;
                const q = this.search.trim().toLowerCase();
                if (!q) return true;
                return [d.name, d.desc, d.cat, d.tags].some(v => v.toLowerCase().includes(q));
            },

            recount() {
                this.visibleCount = TMPL_DATA.filter(t => this.isVisible(t.key)).length;
            },

            async activate(key) {
                this.loading = key;
                this.message = null;
                try {
                    const r = await fetch('/templates/' + key + '/activate-site', {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    });
                    const j = await r.json().catch(() => ({}));
                    this.messageOk = !!j.success;
                    this.message   = j.message || (j.success ? 'Template activated!' : 'Activation failed.');
                    if (j.success) setTimeout(() => location.reload(), 1200);
                } catch { this.message = 'Network error.'; this.messageOk = false; }
                this.loading = null;
                setTimeout(() => this.message = null, 4000);
            },

            async deactivate(key) {
                this.loading = key;
                this.message = null;
                try {
                    const r = await fetch('/templates/' + key + '/deactivate-site', {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    });
                    const j = await r.json().catch(() => ({}));
                    this.messageOk = !!j.success;
                    this.message   = j.message || (j.success ? 'Template deactivated.' : 'Deactivation failed.');
                    if (j.success) setTimeout(() => location.reload(), 1200);
                } catch { this.message = 'Network error.'; this.messageOk = false; }
                this.loading = null;
                setTimeout(() => this.message = null, 4000);
            },

            init() {
                this.$watch('search',    () => this.recount());
                this.$watch('catFilter', () => this.recount());
                this.recount();
            },
        };
    };
})();
</script>
@endsection
