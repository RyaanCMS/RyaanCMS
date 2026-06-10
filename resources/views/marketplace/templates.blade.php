@extends('layouts.app')
@section('title', 'Templates')
@section('header', 'Website Templates')

@push('head')
<style>
.tpl-page {
    width: min(1600px, calc(100vw - 48px));
    margin: 0 auto;
}
.tpl-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.tpl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(265px, 1fr));
    gap: 18px;
}
.tpl-card {
    --c: var(--brand);
    background: color-mix(in srgb, var(--surface-base, #fff) 96%, #f8fafc);
    border: 1px solid var(--border);
    border-left: 3px solid transparent;
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow .22s ease, transform .22s ease, border-color .18s ease, background .18s ease;
    display: flex;
    flex-direction: column;
    min-height: 320px;
}
.tpl-card:hover {
    background: #fff;
    border-left-color: var(--c);
    box-shadow: 0 10px 28px color-mix(in srgb, var(--c) 12%, transparent),
                0 2px 8px color-mix(in srgb, var(--c) 7%, transparent);
    transform: translateY(-3px);
}
.tpl-card-stripe {
    height: 3px;
    width: 100%;
    flex-shrink: 0;
    background: color-mix(in srgb, var(--c) 70%, #fff);
}
.tpl-preview {
    height: 78px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 18px 20px 6px;
    font-size: 24px;
    position: relative;
    background: transparent;
}
.tpl-preview > span {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--c) 10%, #fff);
    border: 1px solid color-mix(in srgb, var(--c) 20%, transparent);
    color: var(--c);
    font-weight: 800;
    flex-shrink: 0;
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
.tpl-body {
    padding: 10px 20px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}
.tpl-title {
    font-size: 14px;
    font-weight: 640;
    color: #7c8a9b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tpl-category {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 99px;
    font-weight: 650;
    background: #fcfdff;
    color: #a3afbf;
    border: 1px solid #eef3f8;
}
.tpl-desc {
    font-size: 12.5px;
    color: #a8b4c3;
    line-height: 1.6;
    font-weight: 400;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    min-height: 40px;
}
.tpl-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: auto;
}
.tpl-tag {
    font-size: 10px;
    font-weight: 580;
    padding: 2px 7px;
    border-radius: 5px;
    background: #fcfdff;
    color: #a3afbf;
    border: 1px solid #eef3f8;
}
.tpl-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.tpl-actions > button,
.tpl-actions > a {
    min-height: 34px;
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
.tpl-upload-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 220px auto auto;
    gap: 12px;
    align-items: end;
}
@media (max-width: 768px) {
    .tpl-upload-grid { grid-template-columns: 1fr; }
    .tpl-page { width: min(100%, calc(100vw - 24px)); }
}
</style>
@endpush

@section('content')
<div class="tpl-page space-y-6" x-data="templateBrowser()">

    {{-- Header bar --}}
    <div class="tpl-toolbar">
        <div>
            <p class="text-sm font-semibold" style="color:var(--text-1);">{{ count($templates) }} website templates available</p>
            <p class="text-xs mt-0.5" style="color:var(--text-3);">Select Core CMS to publish any template on the main website.</p>
        </div>
        {{-- Project selector --}}
        <div class="flex items-center gap-3">
            <button type="button"
                    class="py-2 px-3 rounded-lg text-xs font-bold"
                    style="background:var(--brand);color:#fff;border:1px solid var(--brand);"
                    x-on:click="uploadOpen = !uploadOpen">
                Upload Template
            </button>
            <label class="text-sm font-semibold" style="color:var(--text-2);">Project:</label>
            <select x-model="selectedProject" class="form-select" style="width:200px">
                <option value="">— Select Project —</option>
                @foreach($projects as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(session('success'))
    <div class="px-5 py-3.5 rounded-xl text-sm font-medium"
         style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="px-5 py-3.5 rounded-xl text-sm font-medium"
         style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;">
        {{ $errors->first() }}
    </div>
    @endif

    <div x-show="uploadOpen" x-cloak
         class="p-5 rounded-2xl"
         style="background:var(--card-bg);border:1px solid var(--border);">
        <form action="{{ route('marketplace.template.upload-install') }}" method="POST" enctype="multipart/form-data"
              class="tpl-upload-grid">
            @csrf
            <div>
                <label class="text-xs font-semibold block mb-2" style="color:var(--text-2);">Template ZIP</label>
                <input type="file" name="package" accept=".zip" required
                       class="w-full text-sm rounded-lg px-3 py-2"
                       style="background:var(--hover-bg);border:1px solid var(--border);color:var(--text-1);">
            </div>

            <div>
                <label class="text-xs font-semibold block mb-2" style="color:var(--text-2);">Install To</label>
                <select name="project_id" x-model="selectedProject" class="form-select" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2 text-xs font-semibold pb-2" style="color:var(--text-2);">
                <input type="hidden" name="activate" value="0">
                <input type="checkbox" name="activate" value="1" checked>
                Activate
            </label>

            <button type="submit"
                    class="py-2.5 px-4 rounded-lg text-xs font-bold"
                    style="background:var(--brand);color:#fff;border:1px solid var(--brand);">
                Upload & Install
            </button>
        </form>
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
    <div class="tpl-grid">
        @foreach($templates as $key => $tpl)
        @php
            $colors = [
                'template.ryaancms'   => '#6366f1',
                'template.restaurant' => '#d97706',
                'template.ecommerce'  => '#e11d48',
                'template.portfolio'  => '#7c3aed',
                'template.saas'       => '#2563eb',
                'template.agency'     => '#18181b',
            ];
            $color = $tpl['color'] ?? ($colors[$key] ?? '#6366f1');
        @endphp
        <div class="tpl-card" style="--c:{{ $color }}" x-data="{ key: '{{ $key }}' }">
            <div class="tpl-card-stripe"></div>

            {{-- Preview --}}
            <div class="tpl-preview">
                <span>{{ $tpl['icon'] }}</span>

                {{-- Status badge from installedMap --}}
                @php
                    // We'll render badges via Alpine since selectedProject drives state
                @endphp
                <template x-if="getStatus('{{ $key }}') === 'active'">
                    <div class="tpl-active-badge">● LIVE</div>
                </template>
                @if($tpl['is_global'] ?? false)
                <template x-if="mainTemplateKey === '{{ $key }}'">
                    <div class="tpl-active-badge">● MAIN SITE</div>
                </template>
                @endif
                <template x-if="getStatus('{{ $key }}') === 'installed'">
                    <div class="tpl-badge-pending">Installed</div>
                </template>
            </div>

            {{-- Info --}}
            <div class="tpl-body">
                <div class="flex items-start justify-between mb-1">
                    <p class="tpl-title">{{ $tpl['name'] }}</p>
                    <span class="tpl-category ml-2 flex-shrink-0">{{ $tpl['category'] }}</span>
                </div>
                <p class="tpl-desc">{{ $tpl['description'] }}</p>

                {{-- Tags --}}
                <div class="tpl-tags">
                    @foreach($tpl['tags'] as $tag)
                    <span class="tpl-tag">{{ $tag }}</span>
                    @endforeach
                </div>

                {{-- Buttons --}}
                <div class="tpl-actions">
                    @if($tpl['is_global'] ?? false)
                    <template x-if="mainTemplateKey !== '{{ $key }}'">
                        <button class="flex-1 py-2 rounded-lg text-xs font-bold transition-all"
                                :disabled="working === '{{ $key }}'"
                                style="background:color-mix(in srgb,var(--c) 10%,#fff);color:var(--c);border:1.5px solid color-mix(in srgb,var(--c) 25%,transparent);"
                                x-on:click="activateMain('{{ $key }}')">
                            <span x-show="working !== '{{ $key }}'">Activate Main Website</span>
                            <span x-show="working === '{{ $key }}'">Activating…</span>
                        </button>
                    </template>

                    <template x-if="mainTemplateKey === '{{ $key }}'">
                        <a href="{{ route('home') }}" target="_blank" class="live-btn flex-1 justify-center py-2">
                            🌐 View Main Site
                        </a>
                    </template>
                    @else
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
                                style="background:color-mix(in srgb,var(--c) 10%,#fff);color:var(--c);border:1.5px solid color-mix(in srgb,var(--c) 25%,transparent);"
                                onmouseover="this.style.background='var(--c)';this.style.color='#fff'"
                                onmouseout="this.style.background='color-mix(in srgb,var(--c) 10%,#fff)';this.style.color='var(--c)'"
                                x-on:click="install('{{ $key }}')">
                            <span x-show="working !== '{{ $key }}'" x-text="String(selectedProject) === String(coreCmsProjectId) ? 'Install & Activate' : '⬇ Install'"></span>
                            <span x-show="working === '{{ $key }}'">Installing…</span>
                        </button>
                    </template>

                    <template x-if="selectedProject && getStatus('{{ $key }}') === 'installed'">
                        <button class="flex-1 py-2 rounded-lg text-xs font-bold transition-all"
                                :disabled="working === '{{ $key }}'"
                                style="background:color-mix(in srgb,var(--c) 10%,#fff);color:var(--c);border:1.5px solid color-mix(in srgb,var(--c) 25%,transparent);"
                                onmouseover="this.style.background='var(--c)';this.style.color='#fff'"
                                onmouseout="this.style.background='color-mix(in srgb,var(--c) 10%,#fff)';this.style.color='var(--c)'"
                                x-on:click="activate('{{ $key }}')">
                            <span x-show="working !== '{{ $key }}'">⚡ Activate</span>
                            <span x-show="working === '{{ $key }}'">Activating…</span>
                        </button>
                    </template>

                    <template x-if="selectedProject && getStatus('{{ $key }}') === 'active'">
                        <a :href="'/site/' + selectedProject" target="_blank" class="live-btn flex-1 justify-center py-2">
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
                    @endif

                    <button x-show="selectedProject" x-cloak
                            class="py-2 px-3 rounded-lg text-xs font-bold"
                            style="background:color-mix(in srgb,var(--c) 10%,#fff);color:var(--c);border:1px solid color-mix(in srgb,var(--c) 22%,transparent);"
                            x-on:click="customize('{{ $key }}')">
                        AI Customize
                    </button>
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
        uploadOpen: @json($errors->any()),
        feedback: '',
        feedbackOk: true,
        liveUrl: '',
        mainTemplateKey: @json($mainTemplateKey),
        coreCmsProjectId: @json($coreCmsProjectId),
        // local state: key => status for selected project
        localStatus: {},

        init() {
            this.$watch('selectedProject', (pid) => {
                this.refreshStatus(pid);
                this.feedback = '';
                this.liveUrl  = '';
            });

            if (this.coreCmsProjectId) {
                this.selectedProject = String(this.coreCmsProjectId);
                this.refreshStatus(this.selectedProject);
                return;
            }

            @if($projects->count() === 1)
            this.selectedProject = '{{ $projects->first()->id }}';
            @endif
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

        customize(key) {
            if (!this.selectedProject) {
                this.feedback = 'Select a project before customizing a template.';
                this.feedbackOk = false;
                return;
            }

            window.location.href = `/builder/${this.selectedProject}?template=${encodeURIComponent(key)}`;
        },

        async activateMain(key) {
            this.working = key;
            try {
                const res = await this._post(`/marketplace/templates/${key}/activate-main`);
                const data = await res.json();
                this.feedback = data.message || (data.success ? 'Main website template activated.' : 'Activation failed.');
                this.feedbackOk = data.success;
                if (data.success) {
                    this.mainTemplateKey = key;
                    this.liveUrl = data.url || '{{ route('home') }}';
                }
            } catch {
                this.feedback = 'Network error.';
                this.feedbackOk = false;
            }
            this.working = null;
        },

        async install(key) {
            if (!this.selectedProject) return;
            this.working = key;
            try {
                const res  = await this._post(`/projects/${this.selectedProject}/templates/${key}/install`);
                const data = await res.json();
                if (data.success) {
                    this.localStatus[key] = data.status || 'installed';
                    this.feedback   = data.message;
                    this.feedbackOk = true;
                    this.liveUrl    = data.url || '';
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
