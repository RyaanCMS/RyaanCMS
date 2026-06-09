@extends('layouts.app')
@section('title', 'Installed — ' . $project->name)
@section('header', 'Installed Packages')

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('marketplace.templates') }}"
       class="flex items-center gap-2 text-sm px-4 py-2 rounded-xl font-semibold transition-all hover:-translate-y-px"
       style="background:var(--hover-bg); color:var(--text-2); border:1px solid var(--border);">
        + Add Template
    </a>
    <a href="{{ route('marketplace.modules') }}"
       class="flex items-center gap-2 text-sm px-4 py-2 rounded-xl font-semibold text-white transition-all hover:-translate-y-px"
       style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 4px 12px rgba(99,102,241,.3);">
        + Add Module
    </a>
</div>
@endsection

@section('content')
@php
    $totalCount = $templates->count() + $modules->count() + $installations->count();
@endphp

<div class="max-w-5xl mx-auto space-y-5" x-data="installedPage()">

    {{-- Project badge + stats --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg"
                 style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                📁
            </div>
            <div>
                <p class="text-sm font-bold" style="color:var(--text-1);">{{ $project->name }}</p>
                <p class="text-xs" style="color:var(--text-3);">
                    {{ $templates->count() }} template{{ $templates->count() != 1 ? 's' : '' }}
                    · {{ $modules->count() }} module{{ $modules->count() != 1 ? 's' : '' }}
                    · {{ $installations->count() }} package{{ $installations->count() != 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        {{-- Filter tabs --}}
        <div class="flex items-center gap-1 p-1 rounded-xl" style="background:var(--hover-bg);">
            @foreach(['all' => 'All ('.$totalCount.')', 'template' => 'Templates', 'module' => 'Modules', 'package' => 'Packages'] as $f => $label)
            <button x-on:click="filter = '{{ $f }}'"
                    :style="filter === '{{ $f }}' ? 'background:var(--card-bg); color:var(--text-1); box-shadow:0 1px 4px rgba(0,0,0,.07); font-weight:700;' : 'color:var(--text-3);'"
                    class="px-3 py-1.5 text-xs rounded-lg transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Feedback banner --}}
    <div x-show="feedback" x-cloak
         :style="feedbackOk ? 'background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46' : 'background:#fef2f2;border:1px solid #fecaca;color:#b91c1c'"
         class="px-5 py-3 rounded-xl text-sm font-medium flex items-center justify-between">
        <span x-text="feedback"></span>
        <button x-on:click="feedback=''" class="text-lg leading-none opacity-50 hover:opacity-100">×</button>
    </div>

    @if($totalCount === 0)
    {{-- Empty state --}}
    <div class="rounded-2xl flex flex-col items-center justify-center py-24 text-center"
         style="background:var(--card-bg); border:1px solid var(--border);">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="font-bold mb-2 text-base" style="color:var(--text-1);">Nothing installed yet</h3>
        <p class="text-sm mb-6" style="color:var(--text-3);">Install templates or modules from the marketplace to see them here.</p>
        <div class="flex gap-3">
            <a href="{{ route('marketplace.templates') }}"
               class="px-5 py-2.5 rounded-xl text-sm font-semibold"
               style="background:var(--hover-bg); color:var(--text-2);">Browse Templates</a>
            <a href="{{ route('marketplace.modules') }}"
               class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
               style="background:#6366f1;">Browse Modules</a>
        </div>
    </div>
    @else

    {{-- Installed list --}}
    <div class="rounded-2xl overflow-hidden"
         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">

        {{-- ── TEMPLATES ───────────────────────────────────────────────── --}}
        @if($templates->isNotEmpty())
        <div x-show="filter === 'all' || filter === 'template'">

            <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--border); background:var(--hover-bg);">
                <span class="text-xs font-bold tracking-widest uppercase" style="color:var(--text-3);">Templates</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                      style="background:#eef2ff; color:#6366f1;">{{ $templates->count() }}</span>
            </div>

            @foreach($templates as $tpl)
            @php
                $meta = $allTemplates[$tpl->module_key] ?? null;
                $slug = str_replace('template.', '', $tpl->module_key);
            @endphp
            <div class="flex items-center px-5 py-4 gap-4 transition-colors"
                 onmouseover="this.style.background='var(--hover-bg)'"
                 onmouseout="this.style.background=''"
                 style="border-bottom:1px solid var(--border);">

                {{-- Icon --}}
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                     style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                    {{ $meta['icon'] ?? '🎨' }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-bold" style="color:var(--text-1);">{{ $meta['name'] ?? $tpl->module_key }}</p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                              style="background:#eef2ff; color:#6366f1; border:1px solid #c7d2fe;">Template</span>
                        @if($tpl->status === 'active')
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold"
                              style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">● LIVE</span>
                        @else
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold"
                              style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;">Installed</span>
                        @endif
                    </div>
                    @if($meta)
                    <p class="text-xs mt-0.5 truncate" style="color:var(--text-3);">{{ $meta['category'] }} · {{ $meta['description'] }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($tpl->status === 'active')
                    <a href="{{ route('site.serve', $project) }}" target="_blank"
                       class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg"
                       style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">
                        🌐 View Live
                    </a>
                    <button x-on:click="deactivateTemplate('{{ $tpl->module_key }}')"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg"
                            style="background:#fef9c3; color:#a16207; border:1px solid #fde68a;">
                        Deactivate
                    </button>
                    @else
                    <button x-on:click="activateTemplate('{{ $tpl->module_key }}')"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg text-white"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        ⚡ Activate
                    </button>
                    @endif

                    <a href="{{ route('marketplace.template.download', $tpl->module_key) }}"
                       class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg"
                       style="background:var(--hover-bg); color:var(--text-2); border:1px solid var(--border);">
                        ⬇ ZIP
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── BUILT-IN MODULES ────────────────────────────────────────── --}}
        @if($modules->isNotEmpty())
        <div x-show="filter === 'all' || filter === 'module'">

            <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--border); background:var(--hover-bg);">
                <span class="text-xs font-bold tracking-widest uppercase" style="color:var(--text-3);">Built-in Modules</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                      style="background:#f0fdf4; color:#15803d;">{{ $modules->count() }}</span>
            </div>

            @foreach($modules as $mod)
            @php $meta = $allModules[$mod->module_key] ?? null; @endphp
            <div class="flex items-center px-5 py-4 gap-4 transition-colors"
                 onmouseover="this.style.background='var(--hover-bg)'"
                 onmouseout="this.style.background=''"
                 style="border-bottom:1px solid var(--border);">

                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                     style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #bbf7d0;">
                    {{ $meta['icon'] ?? '🔌' }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-bold" style="color:var(--text-1);">{{ $meta['name'] ?? $mod->module_key }}</p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                              style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;">Module</span>
                        @if(in_array($mod->status, ['installed', 'active']))
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold"
                              style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">Active</span>
                        @else
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold"
                              style="background:#f9fafb; color:#6b7280; border:1px solid #e5e7eb;">Inactive</span>
                        @endif
                    </div>
                    @if($meta)
                    <p class="text-xs mt-0.5 truncate" style="color:var(--text-3);">{{ $meta['description'] ?? '' }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-[10px] font-mono px-2 py-1 rounded"
                          style="background:var(--hover-bg); color:var(--text-3);">{{ $mod->module_key }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── MARKETPLACE PACKAGES ─────────────────────────────────────── --}}
        @if($installations->isNotEmpty())
        <div x-show="filter === 'all' || filter === 'package'">

            <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--border); background:var(--hover-bg);">
                <span class="text-xs font-bold tracking-widest uppercase" style="color:var(--text-3);">Marketplace Packages</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                      style="background:#fffbeb; color:#b45309;">{{ $installations->count() }}</span>
            </div>

            @foreach($installations as $inst)
            <div class="flex items-center px-5 py-4 gap-4 transition-colors"
                 onmouseover="this.style.background='var(--hover-bg)'"
                 onmouseout="this.style.background=''"
                 style="border-bottom:1px solid var(--border);">

                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                     style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border:1px solid #fde68a;">
                    {{ $inst->item?->icon ?? '📦' }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-bold" style="color:var(--text-1);">{{ $inst->item?->name ?? 'Local Package' }}</p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                              style="background:#fffbeb; color:#b45309; border:1px solid #fde68a;">Package</span>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold"
                              style="{{ $inst->status === 'active' ? 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0' : 'background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb' }}">
                            {{ ucfirst($inst->status) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                        <span class="text-xs font-mono" style="color:var(--text-3);">v{{ $inst->version }}</span>
                        @if($inst->license_key)
                        <span class="text-[10px] font-mono truncate max-w-[160px]" style="color:var(--text-3);"
                              title="{{ $inst->license_key }}">
                            🔑 {{ substr($inst->license_key, 0, 8) }}…
                        </span>
                        @endif
                        @if($inst->activated_at)
                        <span class="text-[10px]" style="color:var(--text-3);">Activated {{ $inst->activated_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($inst->package_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($inst->package_path))
                    <a href="{{ route('marketplace.installed') }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-lg"
                       style="background:var(--hover-bg); color:var(--text-2); border:1px solid var(--border);">
                        ⬇ ZIP
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function installedPage() {
    return {
        filter: 'all',
        feedback: '',
        feedbackOk: true,

        async activateTemplate(key) {
            const projectId = {{ $project->id }};
            const res  = await fetch(`/projects/${projectId}/templates/${key}/activate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            this.feedback   = data.message || (data.success ? 'Activated!' : 'Failed.');
            this.feedbackOk = data.success;
            if (data.success) setTimeout(() => location.reload(), 700);
        },

        async deactivateTemplate(key) {
            const projectId = {{ $project->id }};
            const res  = await fetch(`/projects/${projectId}/templates/${key}/deactivate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            this.feedback   = data.message || (data.success ? 'Deactivated.' : 'Failed.');
            this.feedbackOk = data.success;
            if (data.success) setTimeout(() => location.reload(), 700);
        },
    };
}
</script>
@endpush
