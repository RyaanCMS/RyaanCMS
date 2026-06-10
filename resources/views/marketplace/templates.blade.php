@extends('layouts.app')
@section('title', 'Templates')
@section('header', 'Website Templates')

@push('head')
<style>
.tpl-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all .2s;
}
.tpl-card:hover { border-color: #c7d2fe; box-shadow: 0 8px 32px rgba(99,102,241,.1); transform: translateY(-2px); }
.tpl-preview {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 72px;
    position: relative;
}
.tpl-active-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 4px 12px;
    border-radius: 100px;
}
.tpl-badge-pending {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #b45309;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 4px 12px;
    border-radius: 100px;
}
.form-select {
    background: var(--hover-bg);
    border: 1px solid var(--border);
    color: var(--text-1);
    border-radius: 10px;
    padding: 9px 13px;
    font-size: 13px;
    width: 100%;
    cursor: pointer;
}
.form-select:focus { outline: none; border-color: #6366f1; }
.live-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #6366f1;
    padding: 5px 14px;
    border-radius: 8px;
    border: 1px solid #c7d2fe;
    background: #eef2ff;
    text-decoration: none;
    transition: all .15s;
}
.live-btn:hover { background: #e0e7ff; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="templateBrowser()">

    {{-- Header bar --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold" style="color:var(--text-1);">{{ count($templates) }} built-in templates available</p>
            <p class="text-xs mt-0.5" style="color:var(--text-3);">Install and activate a template to publish it on your main domain.</p>
        </div>
        {{-- Project selector --}}
        <div class="flex items-center gap-3">
            <label class="text-sm font-semibold" style="color:var(--text-2);">Project:</label>
            <select x-model="selectedProject" class="form-select" style="width:200px">
                <option value="">— Select Project —</option>
                @foreach($projects as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Feedback flash --}}
    <div x-show="feedback" x-cloak
         :style="feedbackOk ? 'background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46' : 'background:#fef2f2;border:1px solid #fecaca;color:#b91c1c'"
         class="px-5 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
        <span x-text="feedback"></span>
        <a x-show="liveUrl" :href="liveUrl" target="_blank"
           class="live-btn ml-4">🌐 View Live Site</a>
    </div>

    {{-- Template grid --}}
    <div class="grid grid-cols-3 gap-5">
        @foreach($templates as $key => $tpl)
        @php
            $colors = [
                'template.restaurant' => 'linear-gradient(135deg,#1a0a00,#5c2a00)',
                'template.ecommerce'  => 'linear-gradient(135deg,#1a0010,#3d001a)',
                'template.portfolio'  => 'linear-gradient(135deg,#0b0c10,#1a1d2e)',
                'template.saas'       => 'linear-gradient(135deg,#0f172a,#1e3a5f)',
                'template.agency'     => 'linear-gradient(135deg,#080808,#1a1a00)',
            ];
            $bg = $colors[$key] ?? 'linear-gradient(135deg,#1e1e2e,#2d2d44)';
        @endphp
        <div class="tpl-card" x-data="{ key: '{{ $key }}' }">

            {{-- Preview --}}
            <div class="tpl-preview" style="background:{{ $bg }}">
                <span>{{ $tpl['icon'] }}</span>

                {{-- Status badge from installedMap --}}
                @php
                    // We'll render badges via Alpine since selectedProject drives state
                @endphp
                <template x-if="getStatus('{{ $key }}') === 'active'">
                    <div class="tpl-active-badge">● LIVE</div>
                </template>
                <template x-if="getStatus('{{ $key }}') === 'installed'">
                    <div class="tpl-badge-pending">Installed</div>
                </template>
            </div>

            {{-- Info --}}
            <div class="p-5">
                <div class="flex items-start justify-between mb-1">
                    <p class="font-bold text-sm" style="color:var(--text-1);">{{ $tpl['name'] }}</p>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold ml-2 flex-shrink-0"
                          style="background:var(--hover-bg); color:var(--text-3);">{{ $tpl['category'] }}</span>
                </div>
                <p class="text-xs mb-4 leading-relaxed" style="color:var(--text-3);">{{ $tpl['description'] }}</p>

                {{-- Tags --}}
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach($tpl['tags'] as $tag)
                    <span class="text-[10px] px-2 py-0.5 rounded"
                          style="background:var(--hover-bg);color:var(--text-3);">{{ $tag }}</span>
                    @endforeach
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2">
                    <template x-if="!selectedProject">
                        <button class="flex-1 py-2 rounded-lg text-xs font-semibold"
                                style="background:var(--hover-bg);color:var(--text-3);"
                                x-on:click="$dispatch('select-project-required')">
                            Select Project First
                        </button>
                    </template>

                    <template x-if="selectedProject && getStatus('{{ $key }}') === null">
                        <button class="flex-1 py-2 rounded-lg text-xs font-bold transition-all"
                                :disabled="working === '{{ $key }}'"
                                style="background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);"
                                onmouseover="this.style.background='var(--brand)';this.style.color='#fff'"
                                onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)'"
                                x-on:click="install('{{ $key }}')">
                            <span x-show="working !== '{{ $key }}'">⬇ Install</span>
                            <span x-show="working === '{{ $key }}'">Installing…</span>
                        </button>
                    </template>

                    <template x-if="selectedProject && getStatus('{{ $key }}') === 'installed'">
                        <button class="flex-1 py-2 rounded-lg text-xs font-bold transition-all"
                                :disabled="working === '{{ $key }}'"
                                style="background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);"
                                onmouseover="this.style.background='var(--brand)';this.style.color='#fff'"
                                onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)'"
                                x-on:click="activate('{{ $key }}')">
                            <span x-show="working !== '{{ $key }}'">⚡ Activate</span>
                            <span x-show="working === '{{ $key }}'">Activating…</span>
                        </button>
                    </template>

                    <template x-if="selectedProject && getStatus('{{ $key }}') === 'active'">
                        <a href="{{ route('home') }}" target="_blank" class="live-btn flex-1 justify-center py-2">
                            🌐 View Live
                        </a>
                    </template>

                    {{-- Download ZIP (always visible) --}}
                    <a href="{{ route('marketplace.template.download', $key) }}"
                       title="Download as ZIP"
                       class="py-2 px-3 rounded-lg text-xs font-semibold"
                       style="background:var(--hover-bg); color:var(--text-2); border:1px solid var(--border);">
                        ⬇
                    </a>

                    {{-- Uninstall (when installed or active) --}}
                    <template x-if="selectedProject && getStatus('{{ $key }}') !== null">
                        <button class="py-2 px-3 rounded-lg text-xs font-semibold"
                                style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;"
                                :disabled="working === '{{ $key }}'"
                                x-on:click="uninstall('{{ $key }}')">
                            ✕
                        </button>
                    </template>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<script>
// installedMap from PHP: { 'template.key': { 'project_id': 'status', ... }, ... }
const installedMap = @json($installedMap);

function templateBrowser() {
    return {
        selectedProject: '',
        working: null,
        feedback: '',
        feedbackOk: true,
        liveUrl: '',
        // local state: key => status for selected project
        localStatus: {},

        init() {
            // Auto-select first project if only one
            @if($projects->count() === 1)
            this.selectedProject = '{{ $projects->first()->id }}';
            @endif

            this.$watch('selectedProject', (pid) => {
                this.refreshStatus(pid);
                this.feedback = '';
                this.liveUrl  = '';
            });
        },

        refreshStatus(pid) {
            this.localStatus = {};
            if (!pid) return;
            for (const [key, projectMap] of Object.entries(installedMap)) {
                if (projectMap[pid] !== undefined) {
                    this.localStatus[key] = projectMap[pid];
                }
            }
        },

        getStatus(key) {
            return this.localStatus[key] ?? null;
        },

        async install(key) {
            if (!this.selectedProject) return;
            this.working = key;
            try {
                const res  = await this._post(`/projects/${this.selectedProject}/templates/${key}/install`);
                const data = await res.json();
                if (data.success) {
                    this.localStatus[key] = 'installed';
                    this.feedback   = data.message;
                    this.feedbackOk = true;
                } else {
                    this.feedback   = data.message || 'Install failed.';
                    this.feedbackOk = false;
                }
            } catch { this.feedback = 'Network error.'; this.feedbackOk = false; }
            this.working = null;
        },

        async activate(key) {
            if (!this.selectedProject) return;
            this.working = key;
            try {
                const res  = await this._post(`/projects/${this.selectedProject}/templates/${key}/activate`);
                const data = await res.json();
                if (data.success) {
                    // Set all others to installed, this one to active
                    for (const k in this.localStatus) {
                        if (this.localStatus[k] === 'active') this.localStatus[k] = 'installed';
                    }
                    this.localStatus[key] = 'active';
                    this.feedback   = data.message;
                    this.feedbackOk = true;
                    this.liveUrl    = data.url;
                } else {
                    this.feedback   = data.message || 'Activation failed.';
                    this.feedbackOk = false;
                }
            } catch { this.feedback = 'Network error.'; this.feedbackOk = false; }
            this.working = null;
        },

        async uninstall(key) {
            if (!confirm('Uninstall this template?')) return;
            this.working = key;
            try {
                const res  = await this._delete(`/projects/${this.selectedProject}/templates/${key}`);
                const data = await res.json();
                if (data.success) {
                    delete this.localStatus[key];
                    this.feedback   = data.message;
                    this.feedbackOk = true;
                    this.liveUrl    = '';
                }
            } catch {}
            this.working = null;
        },

        _post(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
            });
        },

        _delete(url) {
            return fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
            });
        },
    };
}
</script>
@endpush
