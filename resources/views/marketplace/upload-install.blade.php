@extends('layouts.app')
@section('title', 'Install Package')
@section('header', 'Install Package')

@section('header-actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('marketplace.index') }}"
       class="text-sm px-3 py-1.5 rounded-lg transition-colors"
       style="color:var(--text-2); background:var(--hover-bg);">
        Browse Marketplace
    </a>
    <a href="{{ route('marketplace.installed') }}"
       class="text-sm px-3 py-1.5 rounded-lg transition-colors"
       style="color:#6366f1; background:#eef2ff;">
        All Installed →
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Alerts --}}
    @if(session('success'))
    <div class="flex items-start space-x-3 px-5 py-4 rounded-xl"
         style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="flex items-start space-x-3 px-5 py-4 rounded-xl"
         style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239;">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="text-sm space-y-0.5">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- ── Upload Panel ── --}}
        <div class="lg:col-span-3 rounded-2xl overflow-hidden"
             style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">

            <div class="px-6 py-4 flex items-center space-x-3" style="border-bottom:1px solid var(--border);">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-sm" style="color:var(--text-1);">Upload & Install Package</h2>
                    <p class="text-xs" style="color:var(--text-3);">Upload a .zip downloaded from the marketplace</p>
                </div>
            </div>

            <form action="{{ route('marketplace.upload-install.store') }}" method="POST" enctype="multipart/form-data"
                  class="p-6 space-y-5" x-data="{ dragging: false, fileName: null }">
                @csrf

                {{-- Drop zone --}}
                <div @dragover.prevent="dragging=true" @dragleave="dragging=false"
                     @drop.prevent="dragging=false; fileName=$event.dataTransfer.files[0]?.name; $refs.fileInput.files=$event.dataTransfer.files"
                     :class="dragging ? 'border-indigo-400 bg-indigo-50' : ''"
                     class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
                     style="border-color:var(--border);"
                     onclick="$refs.fileInput.click()">
                    <input type="file" name="package" accept=".zip" x-ref="fileInput" class="hidden"
                           @change="fileName=$event.target.files[0]?.name">
                    <div x-show="!fileName" class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl mx-auto flex items-center justify-center"
                             style="background:linear-gradient(135deg,#eef2ff,#ede9fe);">
                            <svg class="w-6 h-6" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold" style="color:var(--text-1);">Drop your .zip package here</p>
                        <p class="text-xs" style="color:var(--text-3);">or click to browse — max 50 MB</p>
                    </div>
                    <div x-show="fileName" class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl mx-auto flex items-center justify-center"
                             style="background:#f0fdf4; border:1px solid #bbf7d0;">
                            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-green-700" x-text="fileName"></p>
                        <p class="text-xs text-green-500">File selected — ready to install</p>
                    </div>
                </div>

                {{-- Project selector --}}
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">
                        Install into Project
                    </label>
                    <select name="project_id" required
                            class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all"
                            style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                        <option value="">— Select a project —</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- License info box --}}
                <div class="flex items-start space-x-3 px-4 py-3 rounded-xl"
                     style="background:#fff7ed; border:1px solid #fed7aa;">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:#c2410c" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold" style="color:#c2410c;">Single-domain license</p>
                        <p class="text-xs mt-0.5" style="color:#9a3412;">
                            This package will be locked to <strong>{{ request()->getHost() }}</strong>.
                            Using the same package on another domain is not permitted.
                        </p>
                    </div>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 py-3 rounded-xl font-semibold text-sm text-white transition-all hover:-translate-y-px"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 4px 12px rgba(99,102,241,.3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Upload & Install</span>
                </button>
            </form>
        </div>

        {{-- ── How it works ── --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl p-5"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <h3 class="font-bold text-sm mb-4" style="color:var(--text-1);">How Installation Works</h3>
                <div class="space-y-3">
                    @foreach([
                        ['step'=>'1','color'=>'#6366f1','title'=>'Browse Marketplace','desc'=>'Find a plugin or app you need.'],
                        ['step'=>'2','color'=>'#8b5cf6','title'=>'Download Package','desc'=>'Download the .zip file to your computer.'],
                        ['step'=>'3','color'=>'#a855f7','title'=>'Upload Here','desc'=>'Upload the .zip in this form and select your project.'],
                        ['step'=>'4','color'=>'#ec4899','title'=>'Auto-Activated','desc'=>'License is tied to this domain automatically.'],
                    ] as $s)
                    <div class="flex items-start space-x-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                             style="background:{{ $s['color'] }};">{{ $s['step'] }}</div>
                        <div>
                            <p class="text-xs font-semibold" style="color:var(--text-1);">{{ $s['title'] }}</p>
                            <p class="text-xs" style="color:var(--text-3);">{{ $s['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl p-5"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <h3 class="font-bold text-sm mb-3" style="color:var(--text-1);">License Protection</h3>
                <div class="space-y-2">
                    @foreach([
                        ['icon'=>'M9 12l2 2 4-4','txt'=>'One license key per installation'],
                        ['icon'=>'M9 12l2 2 4-4','txt'=>'Locked to single domain only'],
                        ['icon'=>'M9 12l2 2 4-4','txt'=>'Cannot be used on multiple servers'],
                        ['icon'=>'M12 15v2m-6 4h12a2 2 0 002-2v-6','txt'=>'Redistribution is blocked automatically'],
                    ] as $li)
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#10b981" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $li['icon'] }}"/>
                        </svg>
                        <span class="text-xs" style="color:var(--text-2);">{{ $li['txt'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recently Installed ── --}}
    @if($installed->isNotEmpty())
    <div class="rounded-2xl overflow-hidden"
         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
            <h3 class="font-bold text-sm" style="color:var(--text-1);">Recently Installed</h3>
            <a href="{{ route('marketplace.installed') }}" class="text-xs" style="color:#6366f1;">View all →</a>
        </div>
        <div class="divide-y" style="border-color:var(--border);">
            @foreach($installed->take(5) as $inst)
            <div class="flex items-center px-6 py-3 gap-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                     style="background:linear-gradient(135deg,#eef2ff,#ede9fe);">
                    {{ $inst->item?->icon ?? '🔌' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate" style="color:var(--text-1);">
                        {{ $inst->item?->name ?? 'Local Package' }}
                    </p>
                    <div class="flex items-center space-x-2 mt-0.5">
                        <span class="text-xs" style="color:var(--text-3);">
                            {{ $inst->project?->name ?? '—' }}
                        </span>
                        @if($inst->domain)
                        <span class="text-[10px] px-1.5 py-0.5 rounded-md font-mono"
                              style="background:#f0fdf4; color:#166534;">{{ $inst->domain }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <span class="text-[10px] font-semibold px-2 py-1 rounded-full"
                          style="{{ $inst->status === 'active'
                              ? 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0'
                              : 'background:var(--hover-bg);color:var(--text-3);border:1px solid var(--border)' }}">
                        {{ ucfirst($inst->status) }}
                    </span>
                    @if(!$inst->activated_at)
                    <button data-inst="{{ $inst->id }}" data-key="{{ $inst->license_key }}"
                            onclick="activateModal(this)"
                            class="text-xs px-3 py-1 rounded-lg font-medium transition-colors text-white"
                            style="background:#f59e0b;">Activate</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Activate modal --}}
<div id="activateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.4); backdrop-filter:blur(4px);">
    <div class="w-full max-w-sm rounded-2xl p-6 space-y-4"
         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:0 20px 60px rgba(0,0,0,.15);">
        <h3 class="font-bold" style="color:var(--text-1);">Activate License</h3>
        <p class="text-sm" style="color:var(--text-3);">Enter the license key to activate this installation.</p>
        <form id="activateForm" method="POST" class="space-y-3">
            @csrf
            <input type="text" name="license_key" id="licenseKeyInput" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                   class="w-full px-3 py-2.5 rounded-xl text-sm border font-mono outline-none focus:ring-2 focus:ring-indigo-300"
                   style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
            <div class="flex space-x-3">
                <button type="button" onclick="document.getElementById('activateModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                        style="background:var(--hover-bg); color:var(--text-2);">Cancel</button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                        style="background:#6366f1;">Activate</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function activateModal(btn) {
    const id  = btn.dataset.inst;
    const key = btn.dataset.key;
    const form = document.getElementById('activateForm');
    form.action = '/marketplace/activate/' + id;
    document.getElementById('licenseKeyInput').value = key || '';
    document.getElementById('activateModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
