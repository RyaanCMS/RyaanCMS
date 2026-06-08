@extends('layouts.app')
@section('title', $project->name . ' — Autonomous Pipeline')
@section('header', $project->name)

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('builder.show', $project) }}"
       class="flex items-center space-x-1.5 text-xs px-3 py-1.5 rounded-lg font-medium transition-colors text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        <span>AI Builder</span>
    </a>
    <span class="text-xs font-semibold px-2.5 py-1 rounded-full" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#5b21b6;">
        🤖 Autonomous Pipeline
    </span>
</div>
@endsection

@section('content')
<div class="flex h-full" x-data="pipelineApp()" x-init="init()" style="background:#0f1117;">

    {{-- ══════════════════════ LEFT: Config + History ══════════════════════ --}}
    <div class="flex-shrink-0 flex flex-col" style="width:340px; border-right:1px solid #1e2536; background:#111827;">

        {{-- Header --}}
        <div class="p-4 border-b" style="border-color:#1e2536;">
            <div class="flex items-center space-x-2 mb-1">
                <span class="text-lg">🤖</span>
                <span class="font-bold text-white text-sm">Autonomous Build</span>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed">10 AI agents work together to build a complete app. Just describe what you want.</p>
        </div>

        {{-- Prompt Input --}}
        <div class="p-4 border-b" style="border-color:#1e2536;">
            <label class="block text-xs font-semibold text-gray-400 mb-2">WHAT DO YOU WANT TO BUILD?</label>
            <textarea x-model="prompt"
                      :disabled="isRunning"
                      rows="5"
                      placeholder="e.g. Build a SaaS project management tool with teams, tasks, kanban boards, and billing. Users can sign up, create projects, invite teammates, and track progress."
                      class="w-full text-sm rounded-lg resize-none focus:outline-none transition"
                      style="background:#0f1117; border:1px solid #2d3748; color:#e2e8f0; padding:10px; line-height:1.6;"
                      :style="isRunning ? 'opacity:0.5;cursor:not-allowed;' : ''"></textarea>

            {{-- Provider Select --}}
            @if($providers->count())
            <div class="mt-3">
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">AI PROVIDER</label>
                <select x-model="selectedProvider" :disabled="isRunning"
                        class="w-full text-sm rounded-lg focus:outline-none"
                        style="background:#0f1117; border:1px solid #2d3748; color:#e2e8f0; padding:8px 10px;">
                    <option value="">Default Provider</option>
                    @foreach($providers as $p)
                    <option value="{{ $p->provider }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Start Button --}}
            <button @click="startPipeline()"
                    :disabled="isRunning || prompt.trim().length < 10"
                    class="mt-3 w-full flex items-center justify-center space-x-2 font-semibold text-sm py-2.5 rounded-lg transition-all"
                    :style="isRunning || prompt.trim().length < 10
                        ? 'background:#1e2536;color:#4a5568;cursor:not-allowed;'
                        : 'background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;cursor:pointer;box-shadow:0 0 20px rgba(99,102,241,0.3);'">
                <template x-if="!isRunning">
                    <span class="flex items-center space-x-2">
                        <span>🚀</span>
                        <span>Start Autonomous Build</span>
                    </span>
                </template>
                <template x-if="isRunning">
                    <span class="flex items-center space-x-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span>Pipeline Running...</span>
                    </span>
                </template>
            </button>
        </div>

        {{-- Build Loop Status --}}
        <div class="p-4 border-b" style="border-color:#1e2536;" x-show="loopStatus !== null">
            <div class="text-xs font-semibold text-gray-400 mb-2">BUILD LOOP</div>
            <div class="flex items-center space-x-2 p-3 rounded-lg" style="background:#0f1117;"
                 :style="loopStatus === 'success' ? 'border:1px solid #22c55e33;' : loopStatus === 'fail' ? 'border:1px solid #ef444433;' : 'border:1px solid #1e2536;'">
                <span x-text="loopStatus === 'success' ? '✅' : loopStatus === 'fail' ? '⚠️' : loopStatus === 'max_retries' ? '⚡' : '🔄'" class="text-lg"></span>
                <div>
                    <div class="text-xs font-semibold" :class="loopStatus === 'success' ? 'text-green-400' : loopStatus === 'fail' ? 'text-yellow-400' : 'text-gray-300'"
                         x-text="loopStatus === 'success' ? 'Validation Passed' : loopStatus === 'fail' ? 'Errors Detected — Fixing...' : loopStatus === 'max_retries' ? 'Max retries reached' : 'Validating...'"></div>
                    <div class="text-xs text-gray-500 mt-0.5" x-text="`Attempt ${loopAttempt} / 5`"></div>
                </div>
            </div>
        </div>

        {{-- Generated Files --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="text-xs font-semibold text-gray-400 mb-2 flex items-center justify-between">
                <span>GENERATED FILES</span>
                <span class="text-gray-600" x-text="`${generatedFiles.length} files`"></span>
            </div>
            <template x-if="generatedFiles.length === 0">
                <div class="text-xs text-gray-600 italic text-center mt-4">Files will appear here as agents run</div>
            </template>
            <div class="space-y-1">
                <template x-for="file in generatedFiles" :key="file.path">
                    <div class="flex items-center space-x-2 py-1 px-2 rounded group hover:bg-white/5 transition-colors">
                        <span class="text-xs" x-text="fileIcon(file.path)"></span>
                        <span class="text-xs text-gray-400 truncate" x-text="file.path" :title="file.path"></span>
                        <span x-show="file.action === 'patched'" class="text-xs text-yellow-500 opacity-70 ml-auto flex-shrink-0">patched</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Previous Runs --}}
        @if($runs->count())
        <div class="border-t p-4" style="border-color:#1e2536;">
            <div class="text-xs font-semibold text-gray-400 mb-2">PREVIOUS RUNS</div>
            <div class="space-y-1.5">
                @foreach($runs as $run)
                <div class="flex items-center justify-between px-2.5 py-2 rounded-lg cursor-pointer hover:bg-white/5 transition-colors"
                     style="border:1px solid #1e2536;">
                    <div class="flex items-center space-x-2 min-w-0">
                        @php
                            $icon = match($run->status) {
                                'completed' => '✅',
                                'failed'    => '❌',
                                'running'   => '⏳',
                                default     => '🕐',
                            };
                        @endphp
                        <span class="text-sm">{{ $icon }}</span>
                        <div class="min-w-0">
                            <div class="text-xs text-gray-300 truncate" title="{{ $run->prompt }}">{{ Str::limit($run->prompt, 30) }}</div>
                            <div class="text-xs text-gray-600">{{ $run->created_at->diffForHumans() }} · {{ $run->total_files }} files</div>
                        </div>
                    </div>
                    @if($run->quality_report)
                    <div class="flex-shrink-0 text-xs font-bold px-1.5 py-0.5 rounded"
                         style="background:#1e2536; color:#a78bfa;">
                        {{ $run->quality_report['scores']['overall'] ?? '?' }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ══════════════════════ RIGHT: Pipeline Visualization ══════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <div class="flex items-center justify-between px-6 py-3 border-b" style="border-color:#1e2536; background:#111827;">
            <div class="flex items-center space-x-3">
                <div class="text-sm font-semibold text-white" x-text="currentPhaseLabel || 'Ready to build'"></div>
                <div x-show="isRunning" class="flex items-center space-x-1.5 text-xs text-purple-400">
                    <div class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></div>
                    <span>Live</span>
                </div>
            </div>
            <div class="flex items-center space-x-4 text-xs text-gray-500">
                <span x-show="totalFiles > 0">📄 <span x-text="totalFiles"></span> files</span>
                <span x-show="isDone && qualityReport">
                    ⭐ Score: <span class="text-purple-400 font-bold" x-text="qualityReport?.scores?.overall ?? '—'"></span>/100
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6">

            {{-- Pipeline Flow Diagram --}}
            <div class="mb-8">
                <div class="text-xs font-semibold text-gray-500 mb-4 tracking-widest">PIPELINE FLOW</div>

                {{-- Phase labels --}}
                <div class="flex items-start gap-3 mb-6 flex-wrap">
                    <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                         :style="currentPhase === 'PLAN' && isRunning ? 'background:#312e81;color:#a5b4fc;border:1px solid #4338ca;' : 'background:#1e2536;color:#4a5568;border:1px solid transparent;'">
                        <span>📋</span><span>PLAN</span>
                    </div>
                    <div class="text-gray-700 text-xs self-center">→</div>
                    <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                         :style="currentPhase === 'GENERATE' && isRunning ? 'background:#134e4a;color:#5eead4;border:1px solid #0f766e;' : 'background:#1e2536;color:#4a5568;border:1px solid transparent;'">
                        <span>⚡</span><span>GENERATE</span>
                    </div>
                    <div class="text-gray-700 text-xs self-center">→</div>
                    <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                         :style="currentPhase === 'RUN' || currentPhase === 'RETRY' ? 'background:#1c1917;color:#fb923c;border:1px solid #c2410c;' : 'background:#1e2536;color:#4a5568;border:1px solid transparent;'">
                        <span>🔄</span><span x-text="retryCount > 0 ? `RETRY (${retryCount}/5)` : 'RUN'"></span>
                    </div>
                    <div class="text-gray-700 text-xs self-center">→</div>
                    <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                         :style="currentPhase === 'TEST' ? 'background:#14532d;color:#86efac;border:1px solid #15803d;' : 'background:#1e2536;color:#4a5568;border:1px solid transparent;'">
                        <span>🧪</span><span>TEST</span>
                    </div>
                    <div class="text-gray-700 text-xs self-center">→</div>
                    <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                         :style="isDone ? 'background:#1e1b4b;color:#c4b5fd;border:1px solid #6d28d9;' : 'background:#1e2536;color:#4a5568;border:1px solid transparent;'">
                        <span>✅</span><span>SUCCESS</span>
                    </div>
                </div>

                {{-- 10 Agent Cards --}}
                <div class="grid grid-cols-5 gap-3">
                    <template x-for="agent in agents" :key="agent.index">
                        <div class="rounded-xl p-3 transition-all duration-300"
                             :style="agentCardStyle(agent)">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xl" x-text="agent.icon"></span>
                                <span class="text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0"
                                      :style="agent.status === 'done' ? 'background:#166534;color:#86efac;' : agent.status === 'running' ? 'background:#312e81;color:#a5b4fc;' : 'background:#1e2536;color:#6b7280;'"
                                      x-text="agent.index"></span>
                            </div>
                            <div class="text-xs font-semibold text-white leading-tight mb-1" x-text="agent.name"></div>
                            <div class="text-xs text-gray-500 leading-snug" x-text="agent.description"></div>

                            {{-- Status indicator --}}
                            <div class="mt-2" x-show="agent.status !== 'idle'">
                                <template x-if="agent.status === 'running'">
                                    <div class="flex items-center space-x-1 text-xs text-purple-400">
                                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span>Working...</span>
                                    </div>
                                </template>
                                <template x-if="agent.status === 'done'">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-green-400 font-medium">✓ Done</span>
                                        <span class="text-xs text-gray-600" x-text="formatDuration(agent.duration_ms)"></span>
                                    </div>
                                </template>
                                <template x-if="agent.status === 'error'">
                                    <div class="text-xs text-red-400">✗ Failed</div>
                                </template>
                            </div>

                            {{-- Files saved badge --}}
                            <div x-show="agent.files_saved > 0" class="mt-1.5">
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium" style="background:#14532d33;color:#86efac;">
                                    📄 <span x-text="agent.files_saved"></span> files
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Live Log --}}
            <div x-show="log.length > 0" class="mb-6">
                <div class="text-xs font-semibold text-gray-500 mb-2 tracking-widest">LIVE LOG</div>
                <div class="rounded-xl p-4 font-mono text-xs overflow-y-auto max-h-48" style="background:#0a0e1a; border:1px solid #1e2536;">
                    <template x-for="(entry, i) in log.slice(-30)" :key="i">
                        <div class="leading-relaxed"
                             :class="entry.type === 'error' ? 'text-red-400' : entry.type === 'phase' ? 'text-yellow-400 font-bold' : entry.type === 'success' ? 'text-green-400' : 'text-gray-400'"
                             x-text="entry.text"></div>
                    </template>
                </div>
            </div>

            {{-- Quality Report --}}
            <div x-show="isDone && qualityReport" class="mb-6">
                <div class="text-xs font-semibold text-gray-500 mb-3 tracking-widest">QUALITY REPORT</div>
                <div class="rounded-xl p-5" style="background:#0f1117; border:1px solid #2d1f63;">

                    {{-- Score ring + grade --}}
                    <div class="flex items-center space-x-6 mb-5">
                        <div class="relative w-20 h-20 flex-shrink-0">
                            <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1e2536" stroke-width="3"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#8b5cf6" stroke-width="3"
                                        stroke-dasharray="100" :stroke-dashoffset="100 - (qualityReport?.scores?.overall ?? 0)"
                                        stroke-linecap="round" style="transition: stroke-dashoffset 1s ease;"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold text-white" x-text="qualityReport?.scores?.overall ?? '—'"></span>
                                <span class="text-xs text-gray-500">/100</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white mb-1" x-text="qualityReport?.grade ?? '—'"></div>
                            <div class="text-sm text-gray-400">Overall Grade</div>
                        </div>

                        {{-- 4 dimension bars --}}
                        <div class="flex-1 grid grid-cols-2 gap-3">
                            <template x-for="dim in ['ui','backend','security','performance']" :key="dim">
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-xs text-gray-500 capitalize" x-text="dim"></span>
                                        <span class="text-xs font-bold text-white" x-text="qualityReport?.scores?.[dim] ?? '—'"></span>
                                    </div>
                                    <div class="h-1.5 rounded-full" style="background:#1e2536;">
                                        <div class="h-1.5 rounded-full transition-all duration-1000"
                                             :style="`width:${qualityReport?.scores?.[dim] ?? 0}%;background:${scoreColor(qualityReport?.scores?.[dim] ?? 0)};`"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Strengths / Issues / Recommendations --}}
                    <div class="grid grid-cols-3 gap-4 text-xs">
                        <div x-show="qualityReport?.strengths?.length">
                            <div class="font-semibold text-green-400 mb-2">✅ Strengths</div>
                            <ul class="space-y-1">
                                <template x-for="s in qualityReport?.strengths ?? []" :key="s">
                                    <li class="text-gray-400" x-text="s"></li>
                                </template>
                            </ul>
                        </div>
                        <div x-show="qualityReport?.issues?.length">
                            <div class="font-semibold text-yellow-400 mb-2">⚠️ Issues</div>
                            <ul class="space-y-1">
                                <template x-for="s in qualityReport?.issues ?? []" :key="s">
                                    <li class="text-gray-400" x-text="s"></li>
                                </template>
                            </ul>
                        </div>
                        <div x-show="qualityReport?.recommendations?.length">
                            <div class="font-semibold text-blue-400 mb-2">💡 Recommendations</div>
                            <ul class="space-y-1">
                                <template x-for="s in qualityReport?.recommendations ?? []" :key="s">
                                    <li class="text-gray-400" x-text="s"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Done actions --}}
            <div x-show="isDone" class="flex items-center space-x-3">
                <a :href="'{{ route('builder.show', $project) }}'"
                   class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all"
                   style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;">
                    <span>🔧</span>
                    <span>Open in Builder</span>
                </a>
                <a :href="'{{ route('projects.package', $project) }}'"
                   class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all"
                   style="background:#1e2536;color:#e2e8f0;border:1px solid #2d3748;">
                    <span>📦</span>
                    <span>Download Package</span>
                </a>
                <button @click="resetPipeline()"
                        class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all"
                        style="background:#1e2536;color:#9ca3af;border:1px solid #2d3748;">
                    <span>🔄</span>
                    <span>New Build</span>
                </button>
            </div>

        </div>{{-- /overflow-y-auto --}}
    </div>{{-- /right panel --}}
</div>
@endsection

@push('scripts')
<script>
function pipelineApp() {
    return {
        // ── State ──────────────────────────────────────────
        prompt:           '',
        selectedProvider: '',
        isRunning:        false,
        isDone:           false,
        currentPhase:     '',
        currentPhaseLabel:'',
        loopStatus:       null,
        loopAttempt:      0,
        retryCount:       0,
        totalFiles:       0,
        qualityReport:    null,
        runId:            null,
        log:              [],
        generatedFiles:   [],

        agents: [
            { index:1,  name:'Requirement Analyst', icon:'📋', description:'FRS/SRS spec',        status:'idle', duration_ms:0, files_saved:0 },
            { index:2,  name:'Product Manager',     icon:'🗂️', description:'Modules & stories',  status:'idle', duration_ms:0, files_saved:0 },
            { index:3,  name:'Task Planner',        icon:'📅', description:'Build order plan',    status:'idle', duration_ms:0, files_saved:0 },
            { index:4,  name:'System Architect',    icon:'🏗️', description:'Folder structure',   status:'idle', duration_ms:0, files_saved:0 },
            { index:5,  name:'Database Agent',      icon:'🗄️', description:'Migrations & seeds', status:'idle', duration_ms:0, files_saved:0 },
            { index:6,  name:'Backend Agent',       icon:'⚙️', description:'Controllers & routes',status:'idle', duration_ms:0, files_saved:0 },
            { index:7,  name:'UI/UX Designer',      icon:'🎨', description:'Views & preview',     status:'idle', duration_ms:0, files_saved:0 },
            { index:8,  name:'Testing Agent',       icon:'🧪', description:'Feature tests',       status:'idle', duration_ms:0, files_saved:0 },
            { index:9,  name:'Debugger Agent',      icon:'🔧', description:'Auto-fix errors',     status:'idle', duration_ms:0, files_saved:0 },
            { index:10, name:'Quality Reviewer',    icon:'⭐', description:'Score & grade',       status:'idle', duration_ms:0, files_saved:0 },
        ],

        eventSource:   null,
        pollTimer:     null,
        pollOffset:    0,
        pollMode:      false,
        sseRetries:    0,

        // ── Init ───────────────────────────────────────────
        init() {},

        // ── Start pipeline ─────────────────────────────────
        async startPipeline() {
            if (this.isRunning || this.prompt.trim().length < 10) return;

            this.resetState();
            this.isRunning = true;

            try {
                const res = await fetch('{{ route('pipeline.start', $project) }}', {
                    method:  'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body:    JSON.stringify({ prompt: this.prompt, provider: this.selectedProvider }),
                });
                const data = await res.json();
                this.runId = data.run_id;

                // Try SSE first; fall back to polling if connection fails within 5s
                this.sseRetries = 0;
                this.openStream(this.runId);
            } catch (e) {
                this.addLog('error', 'Failed to start pipeline: ' + e.message);
                this.isRunning = false;
            }
        },

        // ── SSE Stream ─────────────────────────────────────
        openStream(runId) {
            const url = `{{ route('pipeline.stream', [$project, '__RUN__']) }}`.replace('__RUN__', runId);
            this.eventSource = new EventSource(url);

            this.eventSource.onmessage = (e) => {
                this.sseRetries = 0; // SSE is working
                try { this.handleEvent(JSON.parse(e.data)); } catch (_) {}
            };

            this.eventSource.onerror = () => {
                this.sseRetries++;
                if (this.sseRetries >= 2) {
                    // SSE not supported on this host — switch to polling
                    this.eventSource.close();
                    this.eventSource = null;
                    if (!this.isDone) {
                        this.addLog('info', '⚡ Switching to polling mode (shared hosting detected)...');
                        this.pollMode   = true;
                        this.pollOffset = 0;
                        this.startPolling(runId);
                    }
                }
            };
        },

        // ── Polling fallback (shared hosting) ──────────────
        startPolling(runId) {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(() => this.doPoll(runId), 3000);
            // First poll immediately
            this.doPoll(runId);
        },

        async doPoll(runId) {
            if (this.isDone) {
                clearInterval(this.pollTimer);
                return;
            }
            try {
                const url = `{{ route('pipeline.poll', [$project, '__RUN__']) }}`.replace('__RUN__', runId)
                          + `?offset=${this.pollOffset}`;
                const res  = await fetch(url);
                const data = await res.json();

                this.pollOffset = data.offset;

                (data.events || []).forEach(ev => {
                    try { this.handleEvent(ev); } catch (_) {}
                });

                if (['completed','failed'].includes(data.status) && this.pollOffset > 0) {
                    clearInterval(this.pollTimer);
                }
            } catch (_) {}
        },

        // ── Event dispatcher ───────────────────────────────
        handleEvent(ev) {
            switch (ev.type) {

                case 'pipeline_start':
                    this.addLog('phase', `🚀 Pipeline started (Run #${ev.run_id})`);
                    break;

                case 'phase':
                    this.currentPhase      = ev.phase;
                    this.currentPhaseLabel = ev.label;
                    this.addLog('phase', `◆ ${ev.label}`);
                    break;

                case 'agent_start':
                    this.setAgentStatus(ev.agent_index, 'running');
                    this.addLog('info', `  ${ev.icon} [${ev.agent_index}/10] ${ev.agent_name}: ${ev.description}`);
                    break;

                case 'agent_done':
                    this.setAgentStatus(ev.agent_index, 'done', ev.duration_ms, ev.files_saved ?? 0);
                    this.addLog('info', `  ✓ ${ev.agent_name} done in ${this.formatDuration(ev.duration_ms)} · ${ev.files_saved ?? 0} files`);
                    break;

                case 'chunk':
                    // Streaming text from an agent — append to log quietly
                    if (ev.text) this.addLog('info', `    ${ev.text}`);
                    break;

                case 'file_saved':
                    this.totalFiles++;
                    this.generatedFiles.push({ path: ev.path, action: ev.action ?? 'created', agent: ev.agent_index });
                    break;

                case 'files_saved':
                    if (Array.isArray(ev.files)) {
                        ev.files.forEach(f => {
                            this.totalFiles++;
                            this.generatedFiles.push({ path: f.path, action: 'created', agent: ev.agent_index });
                        });
                    }
                    break;

                case 'build_status':
                    this.loopStatus  = ev.status;
                    this.loopAttempt = ev.attempt ?? 0;
                    if (ev.status === 'fail') {
                        this.retryCount++;
                        const stuckNote = ev.stuck_count > 0 ? ` (${ev.stuck_count} stuck — escalating strategy)` : '';
                        this.addLog('error', `  ⚠ ${ev.error_count} error(s) — Debugger attempt ${ev.attempt + 1}/5${stuckNote}`);
                        this.setAgentStatus(9, 'idle');
                    } else if (ev.status === 'success') {
                        this.addLog('success', `  ✅ Validation passed on attempt ${ev.attempt + 1}`);
                    } else if (ev.status === 'max_retries') {
                        this.addLog('error', `  ⚡ Max retries reached — continuing with best effort`);
                    }
                    break;

                case 'fix_outcome':
                    if (ev.resolved_count > 0)
                        this.addLog('success', `  🧠 Fixed ${ev.resolved_count} error(s) this pass` + (ev.new_count > 0 ? `, ${ev.new_count} new error(s) introduced` : ''));
                    if (ev.stuck_count > 0)
                        this.addLog('error', `  🔴 ${ev.stuck_count} error(s) stuck — escalating to rewrite/redesign strategy`);
                    break;

                case 'quality_report':
                    this.qualityReport = ev.scores ? ev : null;
                    break;

                case 'pipeline_done':
                    this.isDone        = true;
                    this.isRunning     = false;
                    this.currentPhaseLabel = '✅ Build Complete';
                    this.qualityReport = ev.quality ?? this.qualityReport;
                    this.totalFiles    = ev.total_files ?? this.totalFiles;
                    this.addLog('success', `🎉 Pipeline complete! ${ev.total_files} files · ${ev.total_tokens?.toLocaleString() ?? '—'} tokens`);
                    if (this.eventSource) this.eventSource.close();
                    break;

                case 'error':
                    this.addLog('error', `✗ Error: ${ev.message}`);
                    this.isRunning = false;
                    if (ev.agent_index) this.setAgentStatus(ev.agent_index, 'error');
                    if (this.eventSource) this.eventSource.close();
                    break;
            }
        },

        // ── Helpers ────────────────────────────────────────
        setAgentStatus(index, status, duration_ms = 0, files_saved = 0) {
            const agent = this.agents.find(a => a.index === index);
            if (!agent) return;
            agent.status   = status;
            if (duration_ms)  agent.duration_ms = duration_ms;
            if (files_saved)  agent.files_saved  += files_saved;
        },

        addLog(type, text) {
            this.log.push({ type, text });
            if (this.log.length > 200) this.log.shift();
        },

        formatDuration(ms) {
            if (!ms) return '';
            if (ms < 1000) return `${ms}ms`;
            return `${(ms / 1000).toFixed(1)}s`;
        },

        fileIcon(path) {
            if (!path) return '📄';
            const ext = path.split('.').pop().toLowerCase();
            const icons = { php:'🐘', blade:'🎨', js:'🟨', ts:'🔷', css:'🎀', sql:'🗄️', json:'📋', html:'🌐', md:'📝' };
            return icons[ext] ?? (path.includes('test') || path.includes('Test') ? '🧪' : '📄');
        },

        scoreColor(score) {
            if (score >= 90) return '#22c55e';
            if (score >= 75) return '#84cc16';
            if (score >= 60) return '#f59e0b';
            return '#ef4444';
        },

        agentCardStyle(agent) {
            const base = 'border:1px solid ';
            if (agent.status === 'running') return base + '#4338ca; background:linear-gradient(135deg,#1e1b4b,#1a1040);';
            if (agent.status === 'done')    return base + '#15803d44; background:#0a1a0f;';
            if (agent.status === 'error')   return base + '#dc262644; background:#1a0a0a;';
            return base + '#1e2536; background:#111827;';
        },

        resetState() {
            this.isRunning        = false;
            this.isDone           = false;
            this.currentPhase     = '';
            this.currentPhaseLabel= '';
            this.loopStatus       = null;
            this.loopAttempt      = 0;
            this.retryCount       = 0;
            this.totalFiles       = 0;
            this.qualityReport    = null;
            this.runId            = null;
            this.log              = [];
            this.generatedFiles   = [];
            this.pollOffset       = 0;
            this.pollMode         = false;
            this.sseRetries       = 0;
            this.agents.forEach(a => { a.status = 'idle'; a.duration_ms = 0; a.files_saved = 0; });
            if (this.eventSource) { this.eventSource.close(); this.eventSource = null; }
            if (this.pollTimer)   { clearInterval(this.pollTimer); this.pollTimer = null; }
        },

        resetPipeline() {
            this.resetState();
            this.prompt = '';
        },
    };
}
</script>
@endpush
