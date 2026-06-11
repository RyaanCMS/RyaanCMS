@extends('layouts.app')
@section('title', $project->name.' — Builder')
@section('header', $project->name)
@section('main-class', '')

@section('header-actions')
<div class="flex items-center space-x-2">
    <span class="text-sm text-gray-500">{{ $project->type }}</span>
    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
    <span class="text-sm text-green-400">Active</span>
    @if(false)
    <button @click="newProjectOpen = true"
       class="flex items-center space-x-1.5 text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors"
       style="background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;"
       onmouseover="this.style.background='color-mix(in srgb,var(--brand) 8%,#fff)';this.style.color='var(--brand)';this.style.borderColor='color-mix(in srgb,var(--brand) 20%,transparent)'"
       onmouseout="this.style.background='#f8fafc';this.style.color='#64748b';this.style.borderColor='#e2e8f0'">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>New Project</span>
    </button>

    {{-- ── New Project Attachment Modal — teleported to <body> so z-index always works --}}
    <template x-teleport="body">
    <div x-show="newProjectOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="newProjectOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(10,10,18,.75); backdrop-filter:blur(4px);"
         @click.self="newProjectOpen = false">

        <div x-show="newProjectOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="w-full rounded-2xl overflow-hidden"
             style="max-width:600px; background:#fff; box-shadow:0 32px 80px rgba(0,0,0,.5);">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #f1f5f9; background:#fafbff;">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg"
                         style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">🗂️</div>
                    <div>
                        <div class="text-sm font-bold" style="color:#1e293b;">New Project</div>
                        <div class="text-xs" style="color:#94a3b8;">Fill in details to create your project</div>
                    </div>
                </div>
                <button @click="newProjectOpen = false"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-lg transition-colors"
                        style="border:1px solid #e2e8f0; color:#94a3b8;"
                        onmouseover="this.style.background='#fee2e2';this.style.color='#dc2626'"
                        onmouseout="this.style.background='';this.style.color='#94a3b8'">×</button>
            </div>

            {{-- Modal form --}}
            <form method="POST" action="{{ route('projects.store') }}" x-data="{ creating: false }" @submit="creating = true">
                @csrf
                <div class="p-6 space-y-4">
                    {{-- Project name --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:#475569; text-transform:uppercase; letter-spacing:.06em;">Project Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Hospital Management System"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl focus:outline-none transition-all"
                               style="border:2px solid #e2e8f0; color:#1e293b;"
                               onfocus="this.style.borderColor='var(--brand)'; this.style.boxShadow='0 0 0 3px var(--brand-ring)'"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:#475569; text-transform:uppercase; letter-spacing:.06em;">Project Type *</label>
                        <select name="type" required
                                class="w-full text-sm px-3.5 py-2.5 rounded-xl focus:outline-none appearance-none"
                                style="border:2px solid #e2e8f0; color:#1e293b; background:#fff;"
                                onfocus="this.style.borderColor='var(--brand)'"
                                onblur="this.style.borderColor='#e2e8f0'">
                            @foreach(config('ryaan.project_types', []) as $key => $typeData)
                            <option value="{{ $key }}">{{ is_array($typeData) ? $typeData['label'] : $typeData }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:#475569; text-transform:uppercase; letter-spacing:.06em;">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief description (optional)"
                                  class="w-full text-sm px-3.5 py-2.5 rounded-xl resize-none focus:outline-none"
                                  style="border:2px solid #e2e8f0; color:#1e293b; font-family:inherit;"
                                  onfocus="this.style.borderColor='var(--brand)'; this.style.boxShadow='0 0 0 3px var(--brand-ring)'"
                                  onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"></textarea>
                    </div>
                </div>

                {{-- Modal footer --}}
                <div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #f1f5f9; background:#fafbff;">
                    <button type="button" @click="newProjectOpen = false"
                            class="text-sm px-4 py-2 rounded-xl transition-colors"
                            style="color:#64748b; border:1px solid #e2e8f0;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''">Cancel</button>
                    <button type="submit"
                            :disabled="creating"
                            class="flex items-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-xl text-white transition-all"
                            :style="creating ? 'background:#e2e8f0;color:#94a3b8' : 'background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);'">
                        <svg x-show="!creating" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg x-show="creating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="creating ? 'Creating...' : 'Create & Open Brief'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>{{-- /.modal --}}
    </template>{{-- /.x-teleport --}}
    @endif
    <a href="{{ route('pipeline.show', $project) }}"
       class="ml-2 flex items-center space-x-1.5 text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors"
       style="background:linear-gradient(135deg,#1e1b4b,#2e1065); color:#a78bfa; border:1px solid #4c1d95;">
        <span>🤖</span>
        <span>Auto Build</span>
    </a>
    <a href="{{ route('projects.package', $project) }}"
       class="flex items-center space-x-1.5 text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors"
       style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); color:#16a34a; border:1px solid #bbf7d0;">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <span>Package</span>
    </a>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<style>
/* ── Builder mobile tab bar ── */
.builder-tab-bar { display: none; }
@media (max-width: 640px) {
    .builder-tab-bar {
        display: flex;
        flex-shrink: 0;
        background: #111827;
        border-bottom: 2px solid #1f2937;
    }
    .builder-tab-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 11px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        cursor: pointer;
        transition: color .15s, border-color .15s;
    }
    .builder-tab-btn.btab-active {
        color: #a5b4fc;
        border-bottom-color: #6366f1;
    }
    .builder-tab-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
    /* Hide drag handle on mobile */
    .builder-drag-handle { display: none !important; }
    /* Hide inactive panel on mobile */
    .builder-panel-hidden { display: none !important; }
    /* Active chat panel: full width */
    .builder-chat-panel {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        border-right: none !important;
    }
}
</style>
@endpush

@section('content')
<div class="flex flex-col h-full" x-data="builderApp()" x-init="init()">

    <!-- ══════════════════════════════════════
         Mobile Tab Bar (hidden on desktop)
    ══════════════════════════════════════ -->
    <div class="builder-tab-bar">
        <button class="builder-tab-btn" :class="builderPage === 'prompt' ? 'btab-active' : ''"
                @click="builderPage = 'prompt'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Prompt
        </button>
        <button class="builder-tab-btn" :class="builderPage === 'preview' ? 'btab-active' : ''"
                @click="builderPage = 'preview'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            Preview
        </button>
    </div>

    <!-- ══════════════════════════════════════
         Main panels row
    ══════════════════════════════════════ -->
    <div class="flex flex-1 min-h-0 overflow-hidden">

    <!-- ══════════════════════════════════════
         AI Chat Panel — LEFT (1st column)
    ══════════════════════════════════════ -->
    <div class="builder-chat-panel flex-shrink-0 flex flex-col"
         :class="{ 'builder-panel-hidden': builderPage !== 'prompt' }"
         :style="`width:${chatPanelWidth}px; min-width:260px; max-width:640px; background:#fff; border-right:1px solid #e5e7eb;`">

        <!-- ═══════════════ BLUEPRINT PANEL (hidden — runs automatically via chat) ═══════════════ -->
        <div style="display:none;">

            <!-- Discovery section -->
            <div class="p-3 border-b border-gray-200" style="background:#fff;">
                <div class="text-xs font-bold text-gray-700 mb-2">Phase 1 — Discovery Engine</div>
                <div class="text-xs text-gray-500 mb-2">Describe what you want to build. RyaanCMS will plan the structure automatically.</div>
                <textarea x-model="discoveryText" rows="3" placeholder="e.g. Create a laundry marketplace like Uber — customers book services, providers accept jobs, admin manages everything..."
                          class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                <button @click="runDiscovery()"
                        :disabled="discovering || !discoveryText.trim()"
                        class="mt-2 w-full text-xs font-semibold py-2 rounded-lg transition-colors"
                        :style="discovering ? 'background:#d1fae5;color:#6b7280;cursor:not-allowed' : 'background:#059669;color:#fff;cursor:pointer'"
                        onmouseover="if(!this.disabled) this.style.background='#047857'"
                        onmouseout="if(!this.disabled) this.style.background='#059669'">
                    <span x-show="!discovering">🔍 Discover Blueprint (~300 tokens)</span>
                    <span x-show="discovering">⏳ Discovering...</span>
                </button>
                <p x-show="discoveryError" x-text="discoveryError" class="mt-1 text-xs text-red-600"></p>
            </div>

            <!-- Blueprint display -->
            <div x-show="blueprint" class="p-3 space-y-3">

                <!-- App summary -->
                <div class="rounded-xl p-3" style="background:#ecfdf5;border:1px solid #a7f3d0;">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg" x-text="blueprint?.icon || '📱'"></span>
                        <div>
                            <div class="text-xs font-bold text-emerald-800" x-text="blueprint?.name || 'My App'"></div>
                            <div class="text-xs text-emerald-600" x-text="(blueprint?.app_type || '') + ' · ' + (blueprint?.business_model || '')"></div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <template x-for="f in (blueprint?.features || []).slice(0,6)" :key="f">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-white text-emerald-700 border border-emerald-200" x-text="f"></span>
                        </template>
                    </div>
                </div>

                <!-- Matched Domain Packs -->
                <div x-show="(blueprint?.matched_packs || []).length > 0">
                    <div class="text-xs font-bold text-gray-600 mb-1.5">Phase 2 — Domain Packs Matched</div>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="pack in (blueprint?.matched_packs || [])" :key="pack">
                            <span class="text-xs px-2 py-1 rounded-lg font-medium" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;" x-text="pack"></span>
                        </template>
                    </div>
                </div>

                <!-- Entity list with Generate CRUD buttons -->
                <div>
                    <div class="text-xs font-bold text-gray-600 mb-1.5">Phase 7 — Generate CRUD (Zero AI Cost)</div>
                    <div class="space-y-1.5">
                        <template x-for="entity in (blueprint?.suggested_entities || blueprint?.entities || [])" :key="entity.name">
                            <div class="flex items-center justify-between p-2 rounded-lg" style="background:#fff;border:1px solid #e5e7eb;">
                                <div>
                                    <span class="text-xs font-semibold text-gray-800" x-text="entity.name"></span>
                                    <span class="text-xs text-gray-400 ml-1" x-text="(entity.fields || []).length + ' fields'"></span>
                                </div>
                                <button @click="generateCrud(entity)"
                                        :disabled="generating === entity.name"
                                        class="text-xs px-2.5 py-1 rounded-lg font-semibold transition-colors"
                                        :style="generating === entity.name
                                            ? 'background:#f3f4f6;color:#9ca3af;cursor:not-allowed'
                                            : 'background:#4f46e5;color:#fff;cursor:pointer'"
                                        onmouseover="if(!this.disabled)this.style.background='#4338ca'"
                                        onmouseout="if(!this.disabled)this.style.background='#4f46e5'">
                                    <span x-show="generating !== entity.name">⚡ Generate</span>
                                    <span x-show="generating === entity.name">⏳</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Custom entity builder -->
                <div>
                    <button @click="showEntityBuilder = !showEntityBuilder"
                            class="w-full text-xs font-semibold py-2 rounded-lg border border-dashed border-gray-300 text-gray-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
                        + Add Custom Entity
                    </button>
                    <div x-show="showEntityBuilder" class="mt-2 p-3 rounded-xl" style="background:#fff;border:1px solid #e5e7eb;">
                        <div class="text-xs font-bold text-gray-700 mb-2">New Entity</div>
                        <input x-model="newEntity.name" type="text" placeholder="Entity name (e.g. Customer)"
                               class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 mb-2">
                        <div class="space-y-1 mb-2" id="customFields">
                            <template x-for="(field, idx) in newEntity.fields" :key="idx">
                                <div class="flex items-center gap-1">
                                    <input x-model="field.name" type="text" placeholder="field_name"
                                           class="flex-1 text-xs px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                    <select x-model="field.type" class="text-xs px-1 py-1 border border-gray-300 rounded focus:outline-none">
                                        <option value="string">string</option>
                                        <option value="text">text</option>
                                        <option value="integer">integer</option>
                                        <option value="decimal">decimal</option>
                                        <option value="boolean">boolean</option>
                                        <option value="date">date</option>
                                        <option value="enum">enum</option>
                                    </select>
                                    <button @click="newEntity.fields.splice(idx,1)" class="text-red-400 hover:text-red-600 text-xs px-1">✕</button>
                                </div>
                            </template>
                        </div>
                        <button @click="newEntity.fields.push({name:'',type:'string',required:false,label:''})"
                                class="w-full text-xs py-1 rounded border border-dashed border-gray-300 text-gray-400 hover:text-gray-600 mb-2">
                            + Add Field
                        </button>
                        <button @click="generateCrud(newEntity)"
                                :disabled="!newEntity.name || newEntity.fields.length === 0"
                                class="w-full text-xs font-semibold py-1.5 rounded-lg"
                                :style="(!newEntity.name || newEntity.fields.length===0) ? 'background:#f3f4f6;color:#9ca3af;cursor:not-allowed' : 'background:#4f46e5;color:#fff;cursor:pointer'">
                            ⚡ Generate CRUD (Free)
                        </button>
                    </div>
                </div>

                <!-- Domain Pack quick-load -->
                <div>
                    <div class="text-xs font-bold text-gray-600 mb-1.5">Load from Domain Pack</div>
                    <div class="grid grid-cols-2 gap-1">
                        <template x-for="pack in availablePacks" :key="pack.key">
                            <button @click="loadDomainPack(pack.key)"
                                    class="text-xs px-2 py-1.5 rounded-lg text-left transition-colors"
                                    style="background:#f9fafb;border:1px solid #e5e7eb;color:#374151;"
                                    onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'"
                                    onmouseout="this.style.background='#f9fafb';this.style.borderColor='#e5e7eb'">
                                <span x-text="pack.icon"></span>
                                <span x-text="pack.name.split('—')[0].trim()" class="ml-1"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Generation result feedback -->
                <div x-show="crudResult" class="rounded-xl p-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <div class="text-xs font-bold text-emerald-700 mb-1">✅ Generated (Zero AI tokens!)</div>
                    <div class="text-xs text-emerald-600" x-text="crudResult"></div>
                </div>
            </div>

            <!-- Empty state: no blueprint yet -->
            <div x-show="!blueprint" class="flex-1 flex flex-col items-center justify-center px-5 py-8 text-center">
                <div class="text-4xl mb-3">🗺️</div>
                <div class="text-sm font-semibold text-gray-700 mb-1">No Blueprint Yet</div>
                <div class="text-xs text-gray-400">Describe your app above to extract a structured blueprint — AI plans, RyaanCMS generates.</div>
            </div>
        </div>

        <!-- Chat Header -->
        <div class="px-4 py-3 flex flex-col" style="border-bottom:1px solid #e5e7eb; background:#fff;">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:linear-gradient(135deg,#6366f1,#9333ea)">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-sm font-semibold" style="color:#111827;">AI Builder</span>
                </div>
                <button @click="newConversation()"
                        class="text-xs font-medium px-2.5 py-1 rounded-lg transition-colors"
                        style="color:var(--brand); background:color-mix(in srgb,var(--brand) 8%,#fff); border:1px solid color-mix(in srgb,var(--brand) 18%,transparent);"
                        onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">
                    + New Chat
                </button>
            </div>

            <!-- Provider + model selector -->
            @php
                $activeProviders = auth()->user()->aiProviders()->where('is_active', true)->get();
            @endphp

            @if($activeProviders->isEmpty() && !config('ai.providers.claude.api_key') && !config('ai.providers.openai.api_key'))
            <div class="mx-3 mb-2 px-3 py-2 rounded-lg flex items-center gap-2"
                 style="background:#fffbeb; border:1px solid #fde68a;">
                <span class="text-sm flex-shrink-0">⚠️</span>
                <span class="text-xs" style="color:#92400e;">No AI provider yet. <a href="{{ route('settings.index') }}#ai" class="font-semibold underline">Add an API key in Settings</a> to start building.</span>
            </div>
            @endif

            <div class="flex items-center gap-2">
                <select x-model="selectedProvider"
                        @change="onProviderChange()"
                        class="text-xs rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-purple-300 flex-shrink-0"
                        style="background:#faf5ff; border:1px solid #e9d5ff; color:#7e22ce; font-weight:600; max-width:140px;">
                    @foreach($activeProviders as $ap)
                        @if(config('ai.providers.'.$ap->provider))
                        <option value="{{ $ap->provider }}">{{ $ap->name }}</option>
                        @endif
                    @endforeach
                    @if($activeProviders->isEmpty())
                    <option value="claude">Claude API</option>
                    @endif
                </select>
                <select x-model="selectedModel"
                        class="flex-1 text-xs rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-300"
                        style="background:#f9fafb; border:1px solid #d1d5db; color:#374151;">
                    @foreach($activeProviders as $ap)
                        @foreach(config('ai.providers.'.$ap->provider.'.models', []) as $modelKey => $modelName)
                        <option value="{{ $modelKey }}" data-provider="{{ $ap->provider }}">{{ $modelName }}</option>
                        @endforeach
                    @endforeach
                    @if($activeProviders->isEmpty())
                        @foreach(config('ai.providers.claude.models', []) as $modelKey => $modelName)
                        <option value="{{ $modelKey }}" data-provider="claude">{{ $modelName }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="flex-1 overflow-y-auto" x-ref="chatMessages" style="background:#f9fafb;">

            <!-- Welcome state -->
            <div x-show="turns.length === 0" class="flex flex-col items-center justify-center h-full px-5 py-8 text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background:color-mix(in srgb,var(--brand) 10%,#fff);border:1px solid color-mix(in srgb,var(--brand) 20%,transparent);">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h4 class="font-semibold text-sm mb-1" style="color:#111827;">AI Builder Ready</h4>
                <p class="text-xs mb-5" style="color:#6b7280;">Describe what you want to build</p>
                <div class="space-y-2 text-left w-full">
                    @foreach(['Create a user authentication system', 'Add a product listing page', 'Build a REST API endpoint', 'Create a dashboard with charts'] as $suggestion)
                    <button @click="sendMessage('{{ $suggestion }}')"
                            class="w-full text-left text-xs p-3 rounded-xl transition-all"
                            style="background:#fff; border:1px solid #e5e7eb; color:#374151;"
                            onmouseover="this.style.borderColor='#a5b4fc'; this.style.background='#f5f3ff'"
                            onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff'">
                        "{{ $suggestion }}"
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Turns list -->
            <div x-show="turns.length > 0" class="p-3 space-y-4">
                <template x-for="turn in turns" :key="turn.id">
                    <div>
                        <!-- User prompt bubble -->
                        <div class="flex items-start gap-2.5 mb-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold text-white"
                                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">U</div>
                            <div class="flex-1 min-w-0">
                                <div class="rounded-2xl rounded-tl-sm px-3.5 py-2.5 inline-block max-w-full"
                                     style="background:#ede9fe; border:1px solid #ddd6fe;">
                                    <p class="text-sm leading-relaxed break-words" style="color:#3730a3;" x-text="turn.prompt"></p>
                                </div>
                                <p class="text-xs mt-0.5 ml-1" style="color:#9ca3af;" x-text="turn.time"></p>
                            </div>
                        </div>

                        <!-- Activity card -->
                        <div class="ml-9">
                            <div class="rounded-xl overflow-hidden"
                                 :style="turn.status === 'running'
                                     ? 'border:1px solid #a5b4fc; box-shadow:0 0 0 3px rgba(165,180,252,.15);'
                                     : turn.status === 'error'
                                         ? 'border:1px solid #fca5a5;'
                                         : 'border:1px solid #e5e7eb;'">

                                <!-- Card header -->
                                <div class="flex items-center gap-2 px-3 py-2 cursor-pointer select-none transition-colors"
                                     :style="turn.status === 'running' ? 'background:#eef2ff;' : 'background:#f9fafb;'"
                                     @click="if(turn.status !== 'running') turn.expanded = !turn.expanded"
                                     onmouseover="if(this.__status !== 'running') this.style.background='#f3f4f6'"
                                     onmouseout="if(this.__status !== 'running') this.style.background='#f9fafb'">

                                    <div x-show="turn.status === 'running'"
                                         class="w-3.5 h-3.5 rounded-full border-2 border-t-transparent animate-spin flex-shrink-0"
                                         style="border-color:var(--brand); border-top-color:transparent;"></div>
                                    <svg x-show="turn.status === 'completed'" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#22c55e;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <svg x-show="turn.status === 'error'" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#ef4444;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>

                                    <span class="text-xs font-semibold flex-1 truncate"
                                          :style="turn.status === 'running' ? 'color:#4f46e5;' : turn.status === 'error' ? 'color:#dc2626;' : 'color:#374151;'">
                                        <template x-if="turn.status === 'running'">
                                            <span x-text="turn.activities.length > 0 ? turn.activities[turn.activities.length-1].text + '…' : 'Starting…'"></span>
                                        </template>
                                        <template x-if="turn.status === 'completed'">
                                            <span x-text="turn.files.length > 0 ? turn.files.length + ' file' + (turn.files.length !== 1 ? 's' : '') + ' modified' : (turn.activities.length + ' steps completed')"></span>
                                        </template>
                                        <template x-if="turn.status === 'error'">
                                            <span>Failed — tap to expand</span>
                                        </template>
                                    </span>

                                    <svg x-show="turn.status !== 'running'"
                                         class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                                         :class="turn.expanded ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#9ca3af;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>

                                <!-- Activity steps -->
                                <div x-show="turn.expanded || turn.status === 'running'"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     style="border-top:1px solid #e5e7eb;">
                                    <template x-for="(act, idx) in turn.activities" :key="idx">
                                        <div class="flex items-center gap-2.5 px-3 py-1.5"
                                             style="border-bottom:1px solid #f3f4f6;">
                                            <span class="text-xs w-4 text-center flex-shrink-0" x-text="act.icon"></span>
                                            <span class="text-xs flex-1 leading-relaxed" style="color:#6b7280;" x-text="act.text"></span>
                                            <div x-show="act.status === 'running'"
                                                 class="w-2.5 h-2.5 rounded-full border border-t-transparent animate-spin flex-shrink-0"
                                                 style="border-color:var(--brand); border-top-color:transparent;"></div>
                                            <svg x-show="act.status === 'done'" class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#22c55e;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <div x-show="turn.status === 'running'" class="flex items-center gap-1 px-3 py-2">
                                        <div class="w-1.5 h-1.5 rounded-full animate-bounce" style="background:var(--brand); animation-delay:0ms"></div>
                                        <div class="w-1.5 h-1.5 rounded-full animate-bounce" style="background:var(--brand); animation-delay:150ms"></div>
                                        <div class="w-1.5 h-1.5 rounded-full animate-bounce" style="background:var(--brand); animation-delay:300ms"></div>
                                    </div>
                                </div>

                                <!-- File chips -->
                                <div x-show="turn.files.length > 0 && (turn.expanded || turn.status === 'running')"
                                     class="px-3 py-2 flex flex-wrap gap-1"
                                     style="background:#fafafe; border-top:1px solid #e5e7eb;">
                                    <template x-for="file in turn.files.slice(0, 8)" :key="file">
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-md font-mono"
                                              style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;">
                                            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span x-text="file.split('/').pop()"></span>
                                        </span>
                                    </template>
                                    <span x-show="turn.files.length > 8"
                                          class="text-xs self-center px-1" style="color:#9ca3af;"
                                          x-text="'+' + (turn.files.length - 8) + ' more'"></span>
                                </div>

                                <!-- AI response -->
                                <div x-show="turn.response"
                                     class="px-3 py-3"
                                     style="background:#fff; border-top:1px solid #e5e7eb;">
                                    <div class="flex items-start gap-2">
                                        <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                             style="background:color-mix(in srgb,var(--brand) 10%,#fff);border:1px solid color-mix(in srgb,var(--brand) 20%,transparent);">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="text-xs font-semibold" style="color:var(--brand);">RyaanCMS</span>
                                            </div>
                                            <p class="text-xs leading-relaxed break-words whitespace-pre-wrap" style="color:#374151;" x-text="cleanResponse(turn.response)"></p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        <!-- Chat Input -->
        <div class="p-3" style="border-top:1px solid #e5e7eb; background:#fff;">

            <!-- Attachment + URL chips -->
            <div x-show="attachments.length > 0 || urlPreview" class="flex flex-wrap gap-1.5 mb-2">
                <template x-for="(att, i) in attachments" :key="i">
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium"
                         style="background:#f3f4f6; border:1px solid #e5e7eb; color:#374151;">
                        <span x-text="att.icon"></span>
                        <span x-text="att.name" class="max-w-[110px] truncate"></span>
                        <button @click="removeAttachment(i)" class="ml-0.5 transition-colors" style="color:#9ca3af;"
                                onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">✕</button>
                    </div>
                </template>
                <div x-show="urlPreview" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium"
                     style="background:#f3f4f6; border:1px solid #e5e7eb; color:#374151;">
                    <span>🌐</span>
                    <span x-text="urlPreview" class="max-w-[160px] truncate"></span>
                    <button @click="urlPreview=''; urlInput=''" class="ml-0.5" style="color:#9ca3af;"
                            onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">✕</button>
                </div>
            </div>

            <!-- URL input row -->
            <div x-show="showUrlInput" x-transition class="flex items-center gap-2 mb-2">
                <input type="url" x-model="urlInput"
                       @keydown.enter="addUrl()" @keydown.escape="showUrlInput=false; urlInput=''"
                       placeholder="Paste website URL (https://...)"
                       class="flex-1 rounded-xl px-3 py-2 text-sm outline-none transition-colors"
                       style="background:#f9fafb; border:1px solid #d1d5db; color:#111827;"
                       onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='#d1d5db'">
                <button @click="addUrl()"
                        class="px-3 py-2 text-white rounded-xl text-xs font-semibold transition-colors whitespace-nowrap"
                        style="background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);">Add URL</button>
                <button @click="showUrlInput=false; urlInput=''"
                        class="px-3 py-2 rounded-xl text-xs transition-colors"
                        style="background:#f3f4f6; color:#374151;">✕</button>
            </div>

            <!-- Todo Tracker Panel -->
            <div x-show="showTodos && todos.length > 0" x-transition class="mb-2 rounded-xl overflow-hidden"
                 style="border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);background:color-mix(in srgb,var(--brand) 4%,#fff);">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 12px;border-bottom:1px solid color-mix(in srgb,var(--brand) 12%,transparent);">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <svg style="width:13px;height:13px;stroke:var(--brand)" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span style="font-size:11.5px;font-weight:700;color:var(--brand);">Build Tasks</span>
                        <span x-text="todos.filter(t=>t.done).length + '/' + todos.length"
                              style="font-size:10px;font-weight:600;padding:1px 7px;border-radius:99px;background:var(--brand);color:#fff;"></span>
                    </div>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <button @click="todos = todos.map(t => ({...t, done: true}))"
                                style="font-size:10px;font-weight:600;color:var(--text-3);background:none;border:none;cursor:pointer;padding:2px 6px;border-radius:6px;"
                                title="Mark all done"
                                onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--text-3)'">All done</button>
                        <button @click="todos = []"
                                style="font-size:10px;color:var(--text-3);background:none;border:none;cursor:pointer;padding:2px 6px;border-radius:6px;"
                                title="Clear all"
                                onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--text-3)'">Clear</button>
                    </div>
                </div>
                <div style="padding:6px 8px;display:flex;flex-direction:column;gap:2px;max-height:180px;overflow-y:auto;">
                    <template x-for="(todo, i) in todos" :key="i">
                        <div style="display:flex;align-items:center;gap:8px;padding:5px 6px;border-radius:8px;transition:background .1s;"
                             onmouseover="this.style.background='rgba(0,0,0,.03)'" onmouseout="this.style.background=''">
                            <button @click="todos[i].done = !todos[i].done"
                                    style="width:16px;height:16px;border-radius:4px;border:1.5px solid;flex-shrink:0;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;background:none;"
                                    :style="todo.done ? 'border-color:var(--brand);background:var(--brand);' : 'border-color:#d1d5db;'">
                                <svg x-show="todo.done" style="width:9px;height:9px;stroke:#fff" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <span style="font-size:12px;flex:1;line-height:1.3;transition:all .15s;"
                                  :style="todo.done ? 'color:var(--text-3);text-decoration:line-through;' : 'color:#1e293b;'"
                                  x-text="todo.text"></span>
                            <button @click="todos.splice(i, 1)"
                                    style="font-size:11px;color:transparent;background:none;border:none;cursor:pointer;padding:2px;border-radius:4px;flex-shrink:0;"
                                    onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='transparent'">✕</button>
                        </div>
                    </template>
                    <!-- Add task inline -->
                    <div style="display:flex;align-items:center;gap:6px;padding:4px 6px;">
                        <div style="width:16px;height:16px;border-radius:4px;border:1.5px dashed #d1d5db;flex-shrink:0;"></div>
                        <input type="text" x-model="todoInput"
                               @keydown.enter="if(todoInput.trim()){todos.push({text:todoInput.trim(),done:false});todoInput='';}"
                               @keydown.escape="todoInput=''"
                               placeholder="Add a task…"
                               style="flex:1;border:none;outline:none;font-size:12px;background:transparent;color:#374151;">
                    </div>
                </div>
            </div>

            <!-- Main textarea box -->
            <div class="rounded-2xl overflow-hidden transition-all"
                 style="background:#fff; border:1.5px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,.06);"
                 onfocusin="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 3px rgba(99,102,241,.12)'"
                 onfocusout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)'">
                <textarea x-model="chatInput"
                          @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                          :disabled="isThinking"
                          :placeholder="isRecording ? '🎤 Listening… speak now in any language' : 'Describe what to build… (Enter to send, Shift+Enter for new line)'"
                          rows="3"
                          class="w-full bg-transparent text-sm px-4 pt-3 resize-none outline-none disabled:opacity-50"
                          style="color:#111827;"
                          placeholder="Describe what to build…">
                </textarea>

                <!-- Toolbar -->
                <div class="flex items-center justify-between px-2 py-1.5" style="border-top:1px solid #f3f4f6;">
                    <div class="flex items-center gap-0.5">
                        <button @click="$refs.fileInput.click()" title="Attach file"
                                class="p-2 rounded-xl transition-all"
                                :style="attachments.length ? 'color:var(--brand); background:color-mix(in srgb,var(--brand) 8%,#fff);' : 'color:#9ca3af;'"
                                onmouseover="if(!this.__active) { this.style.color='var(--brand)'; this.style.background='color-mix(in srgb,var(--brand) 8%,#fff)'; }"
                                onmouseout="if(!this.__active) { this.style.color='#9ca3af'; this.style.background=''; }">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </button>
                        <button @click="showUrlInput=!showUrlInput" title="Read content from a website URL"
                                class="p-2 rounded-xl transition-all"
                                :style="urlPreview ? 'color:var(--brand); background:color-mix(in srgb,var(--brand) 8%,#fff);' : 'color:#9ca3af;'"
                                onmouseover="this.style.color='var(--brand)'; this.style.background='color-mix(in srgb,var(--brand) 8%,#fff)';"
                                onmouseout="this.style.color='#9ca3af'; this.style.background='';">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </button>
                        <button @click="toggleVoice()" title="Voice input"
                                class="p-2 rounded-xl transition-all"
                                :style="isRecording ? 'color:#ef4444; background:#fef2f2;' : 'color:#9ca3af;'"
                                :class="isRecording ? 'animate-pulse' : ''"
                                onmouseover="this.style.color='var(--brand)'; this.style.background='color-mix(in srgb,var(--brand) 8%,#fff)';"
                                onmouseout="if(!this.__rec) { this.style.color='#9ca3af'; this.style.background=''; }">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </button>
                        <!-- Tasks toggle button -->
                        <button @click="showTodos = !showTodos" title="Build task tracker"
                                class="p-2 rounded-xl transition-all relative"
                                :style="todos.length ? 'color:var(--brand); background:color-mix(in srgb,var(--brand) 10%,#fff);' : 'color:#9ca3af;'"
                                onmouseover="this.style.color='var(--brand)'; this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';"
                                onmouseout="if(!this.classList.contains('active-tasks')) { this.style.color=window._taskHasItems?'var(--brand)':'#9ca3af'; this.style.background=window._taskHasItems?'color-mix(in srgb,var(--brand) 10%,#fff)':''; }">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <!-- Badge: pending count -->
                            <span x-show="todos.filter(t=>!t.done).length > 0"
                                  x-text="todos.filter(t=>!t.done).length"
                                  style="position:absolute;top:2px;right:2px;min-width:14px;height:14px;padding:0 3px;border-radius:99px;background:var(--brand);color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1;"></span>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span x-show="isRecording && !isThinking" class="text-xs font-medium" style="color:#ef4444;">● REC</span>
                        <!-- Stop button — only visible while AI is generating -->
                        <button x-show="isThinking" @click="stopGeneration()"
                                class="rounded-xl px-3 py-1.5 text-xs font-semibold transition-all flex items-center gap-1.5 border"
                                style="color:#ef4444;border-color:#fca5a5;background:#fff1f2;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                            </svg>
                            Stop
                        </button>
                        <button x-show="!isThinking" @click="sendMessage()"
                                :disabled="!chatInput.trim() && !attachments.length && !urlPreview"
                                class="text-white rounded-xl px-4 py-1.5 text-xs font-semibold transition-all flex items-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
                                style="background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                            Send
                        </button>
                    </div>
                </div>
            </div>

            <input type="file" x-ref="fileInput" multiple
                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.md,.mdx,.json,.xml,.html,.htm,.php,.js,.ts,.jsx,.tsx,.css,.scss,.sass,.py,.java,.rb,.go,.rs,.sql,.env,.yaml,.yml,.toml,.sh,.bat,.ini,.conf,.log"
                   @change="handleFiles($event)" class="hidden">

            <!-- Internal usage diagnostics (hidden from normal builder UI) -->
            <div class="hidden mt-2 rounded-lg px-3 py-2" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span style="color:#15803d;font-weight:600;">Token Usage</span>
                    <span x-show="cacheHits > 0"
                          class="px-1.5 py-0.5 rounded text-white text-xs font-semibold"
                          style="background:#16a34a;font-size:10px;">
                        <span x-text="cacheHits"></span> cached
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs" style="color:#374151;">
                    <span>Tokens: <strong x-text="tokensUsed.toLocaleString()"></strong></span>
                    <span x-show="tokensSaved > 0" style="color:#16a34a;">
                        &nbsp;· Saved: ~<span x-text="tokensSaved.toLocaleString()"></span>
                    </span>
                </div>
                <div x-show="cacheHits > 0" class="text-xs mt-0.5" style="color:#15803d;">
                    Cache hits save ~90% on system prompt cost
                </div>
            </div>
        </div>
    </div>

    <!-- Drag handle between chat panel and builder -->
    <div class="builder-drag-handle w-1 flex-shrink-0 cursor-col-resize select-none relative group"
         style="background:#e5e7eb;"
         @mousedown.prevent="startChatResize($event)">
        <div class="absolute inset-y-0 -left-1 -right-1 group-hover:bg-indigo-400/30 transition-colors"></div>
    </div>

    <!-- ══════════════════════════════════════
         Main Builder Area — CENTER (2nd column)
    ══════════════════════════════════════ -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden"
         :class="{ 'builder-panel-hidden': builderPage !== 'preview' }">

        <!-- Toolbar -->
        <div class="flex items-center bg-gray-900 border-b border-gray-800 px-3 py-1.5 gap-1 flex-shrink-0">

            <!-- View mode pills -->
            <div class="flex items-center bg-gray-800 rounded-lg p-0.5 gap-0.5">
                <button @click="viewMode = 'editor'"
                        :class="viewMode === 'editor' ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'"
                        class="flex items-center space-x-1.5 px-2.5 py-1 rounded-md text-xs font-medium transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <span>Code</span>
                </button>
                <button @click="viewMode = 'split'"
                        :class="viewMode === 'split' ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'"
                        class="flex items-center space-x-1.5 px-2.5 py-1 rounded-md text-xs font-medium transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                    </svg>
                    <span>Split</span>
                </button>
                <button @click="viewMode = 'preview'"
                        :class="viewMode === 'preview' ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'"
                        class="flex items-center space-x-1.5 px-2.5 py-1 rounded-md text-xs font-medium transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Preview</span>
                </button>
            </div>

            <!-- Active file -->
            <div x-show="activeFile && viewMode !== 'preview'" class="flex items-center space-x-2 ml-3">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0"></span>
                <span x-text="activeFile?.path" class="text-xs text-gray-500 font-mono truncate max-w-xs"></span>
                <span x-show="activeFile?.read_only" x-cloak
                      class="text-[10px] px-1.5 py-0.5 rounded border"
                      style="color:#a16207;background:#fef9c3;border-color:#fde68a;">Template source</span>
            </div>

            <div class="flex-1"></div>

            <!-- Refresh preview -->
            <button @click="refreshPreview()"
                    x-show="viewMode !== 'editor'"
                    class="flex items-center space-x-1.5 text-xs text-gray-500 hover:text-gray-300 px-2.5 py-1 rounded-lg hover:bg-gray-800 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Refresh</span>
            </button>

            <!-- Save button -->
            <button @click="saveFile()" :disabled="saving" x-show="activeFile && viewMode !== 'preview' && !activeFile?.read_only"
                    class="flex items-center space-x-1.5 text-xs bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white px-3 py-1.5 rounded-lg transition-colors">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span x-text="saving ? 'Saving…' : 'Save'"></span>
            </button>
        </div>

        <!-- Editor + Preview panes -->
        <div class="flex-1 flex overflow-hidden">

            <!-- Code Editor pane -->
            <div :class="{
                    'hidden':  viewMode === 'preview',
                    'w-1/2 border-r border-gray-800': viewMode === 'split',
                    'flex-1':  viewMode === 'editor'
                 }"
                 class="flex flex-col overflow-hidden">
                <textarea id="codeEditor"
                          class="flex-1 w-full bg-gray-950 text-gray-300 font-mono text-sm p-4 resize-none outline-none"
                          x-ref="editor"
                          @keydown.ctrl.s.prevent="saveFile()"
                          x-model="editorContent"
                          :readonly="activeFile?.read_only"
                          :placeholder="activeFile ? '' : '← Select a file or ask AI to generate code'">
                </textarea>
            </div>

            <!-- Live Preview pane -->
            <div :class="{
                    'hidden':  viewMode === 'editor',
                    'w-1/2':   viewMode === 'split',
                    'flex-1':  viewMode === 'preview'
                 }"
                 class="flex flex-col overflow-hidden bg-white">
                <!-- Preview header bar -->
                <div class="flex items-center px-3 py-1.5 bg-gray-900 border-b border-gray-800 flex-shrink-0">
                    <div class="flex items-center space-x-1.5 mr-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                    </div>
                    <div class="flex-1 bg-gray-800 rounded-md px-2.5 py-1 text-xs text-gray-500 font-mono text-center mx-4 max-w-xs">
                        localhost / preview
                    </div>
                    <div class="flex items-center space-x-1 ml-auto">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-xs text-green-400 font-medium">Live</span>
                    </div>
                </div>
                <!-- iframe -->
                <div class="flex-1 relative overflow-hidden">
                    <iframe id="previewFrame" class="absolute inset-0 w-full h-full border-0"
                            src="about:blank" title="Live Preview"></iframe>
                    <!-- Empty state overlay -->
                    <div x-show="!hasPreviewContent"
                         class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 text-center px-8">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 bg-indigo-50 border border-indigo-100">
                            <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Preview will appear here</p>
                        <p class="text-xs text-gray-400">Ask AI to generate code, then click Refresh</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════
         File Tree Panel — RIGHT (3rd column)
         Visible in Code + Split mode; hidden in Preview mode
    ══════════════════════════════════════ -->
    <div x-show="viewMode !== 'preview'"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         :class="{ 'builder-panel-hidden': builderPage !== 'preview' }"
         class="w-64 flex-shrink-0 bg-gray-900 border-l border-gray-800 flex flex-col">
        <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Files</span>
            <div class="flex space-x-1">
                <button @click="createFile()" class="p-1 text-gray-500 hover:text-gray-300 rounded" title="New File">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </button>
                <button @click="createFolder()" class="p-1 text-gray-500 hover:text-gray-300 rounded" title="New Folder">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto py-1">
            <template x-for="item in fileTree" :key="'ft_' + (item._vid || item.id)">
                <div x-show="treeVisible(item)"
                     class="flex items-center py-1 cursor-pointer text-xs transition-colors group select-none"
                     :class="item.type === 'file' && activeFile?.id === item.id
                         ? 'bg-indigo-600/20 text-indigo-300 border-l-2 border-indigo-500'
                         : item.type === 'directory'
                             ? 'text-gray-300 hover:bg-gray-800'
                             : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200'"
                     :style="`padding-left:${item.depth * 10 + 8}px; padding-right:6px;`"
                     @click="item.type === 'directory' ? toggleDir(item._vid) : openFile(item)">

                    <!-- Directory row -->
                    <template x-if="item.type === 'directory'">
                        <span class="flex items-center gap-1 flex-1 min-w-0">
                            <svg class="w-2 h-2 flex-shrink-0 text-gray-500 transition-transform"
                                 :class="collapsedDirs[item._vid] ? '' : 'rotate-90'"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#fbbf24;" fill="currentColor" viewBox="0 0 20 20">
                                <path x-show="!collapsedDirs[item._vid]" d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                <path x-show="collapsedDirs[item._vid]"  fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="item.name" class="truncate font-medium text-gray-300"></span>
                        </span>
                    </template>

                    <!-- File row -->
                    <template x-if="item.type === 'file'">
                        <span class="flex items-center gap-1.5 flex-1 min-w-0">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" :style="`color:${extColor(item.name)}`"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span x-text="item.name" class="truncate flex-1" :title="item.path"></span>
                            <span x-show="item.read_only" class="text-[9px] px-1 rounded"
                                  style="background:#fef9c3;color:#a16207;border:1px solid #fde68a;">TPL</span>
                        </span>
                    </template>

                    <button x-show="item.type === 'file' && !item.read_only"
                            @click.stop="deleteFile(item)"
                            class="opacity-0 group-hover:opacity-100 p-0.5 text-gray-600 hover:text-red-400 rounded transition-all flex-shrink-0">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <div x-show="fileTree.length === 0" class="px-4 py-8 text-center text-xs text-gray-600">
                No files yet. Ask AI to generate code!
            </div>
        </div>
    </div>

    </div>{{-- /panels row --}}
</div>{{-- /builder-root --}}
@endsection

@php
$messageData = $messages->map(fn($m) => [
    'id'              => $m->id,
    'role'            => $m->role,
    'content'         => $m->content,
    'model'           => $m->model,
    'generated_files' => $m->generated_files,
]);
@endphp

@php
    $initProvider = $conversation->provider ?? ($activeProviders->first()?->provider ?? 'claude');
    $firstProviderModels = $activeProviders->first()
        ? config('ai.providers.' . $activeProviders->first()->provider . '.models', ['claude-sonnet-4-6' => 'Claude Sonnet'])
        : ['claude-sonnet-4-6' => 'Claude Sonnet'];
    $initModel = $conversation->model ?? array_key_first($firstProviderModels) ?? 'claude-sonnet-4-6';
    $providerModelsMap = $activeProviders->mapWithKeys(function ($p) {
        return [$p->provider => array_keys(config('ai.providers.' . $p->provider . '.models', []))];
    })->toArray();
    $providerDefaultsMap = $activeProviders->mapWithKeys(function ($p) {
        return [$p->provider => config('ai.providers.' . $p->provider . '.default_model', '')];
    })->toArray();
    $templateOptions = collect($templates)->map(fn($tpl, $key) => [
        'key'      => $key,
        'name'     => $tpl['name'],
        'category' => $tpl['category'],
    ])->values();
@endphp
@push('scripts')
<script>
function builderApp() {
    return {
        viewMode: 'preview',
        builderPage: 'prompt',
        hasPreviewContent: false,
        _blobUrl: null,
        chatPanelWidth: 384,
        files: @json($files),
        templateFiles: [],
        fileTree: [],
        collapsedDirs: {},
        messages: @json($messageData),
        turns: [],
        currentTurnId: null,
        activeFile: null,
        editorContent: '',
        chatInput: '',
        isThinking: false,
        saving: false,
        attachments: [],
        todos: [],
        showTodos: false,
        todoInput: '',
        urlInput: '',
        urlPreview: '',
        showUrlInput: false,
        selectedTemplateKey: @json($selectedTemplateKey),
        templateOptions: @json($templateOptions),
        isRecording: false,
        recognition: null,
        _streamAbortCtrl: null,
        selectedProvider: '{{ $initProvider }}',
        selectedModel: '{{ $initModel }}',
        providerModels: @json($providerModelsMap),
        providerDefaults: @json($providerDefaultsMap),
        conversationId: {{ $conversation->id }},
        projectId: {{ $project->id }},
        projectName: @json($project->name),
        tokensUsed: {{ (int) $project->ai_tokens_used }},
        tokensSaved: 0,
        cacheHits: 0,
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,

        selectedTemplate() {
            return this.templateOptions.find(t => t.key === this.selectedTemplateKey) || null;
        },

        selectedTemplateName() {
            return this.selectedTemplate()?.name || 'Selected template';
        },

        defaultTemplatePrompt() {
            const tpl = this.selectedTemplate();
            if (!tpl) return '';
            return `Customize the ${tpl.name} template for ${this.projectName}. Update the copy, layout, colors, imagery notes, sections, and calls to action to match this project. Generate a polished preview.html.`;
        },

        init() {
            // Last-known-good preview snapshot — restored on any JS error in the iframe
            this._lastGoodHtml     = null;
            this._lastGoodFilename = null;
            this._stagedHtml       = null;   // candidate waiting for error-free confirmation
            this._commitTimer      = null;   // confirms staged preview as "good" after 2.5s

            this.initTurns();
            this.buildFileTree();
            if (!this.selectedTemplateKey && this.files.length > 0) this.openFile(this.files[0]);
            this.$watch('turns', () => this.scrollChat());
            this.$watch('files', () => this.buildFileTree());
            this.$watch('selectedTemplateKey', async (key) => {
                await this.loadTemplateFiles(true);
                key ? this.loadTemplatePreview() : this.autoPreview();
            });
            this.$nextTick(() => {
                this.selectedTemplateKey ? this.loadTemplatePreview() : this.autoPreview();
                this.scrollChat();
                this.onProviderChange();
            });

            // When AI starts, reveal preview pane if user is in code-only mode
            this.$watch('isThinking', (val) => {
                if (val && this.viewMode === 'editor') this.viewMode = 'split';
                // On mobile: auto-switch to Preview tab when AI finishes
                if (!val && this.builderPage === 'prompt' && window.innerWidth <= 640) {
                    this.builderPage = 'preview';
                }
            });

            // Catch JS / Alpine errors reported from inside the iframe
            window.addEventListener('message', (e) => {
                if (e.data?.type !== 'ryaan-preview-error') return;
                clearTimeout(this._commitTimer);
                this._stagedHtml = null;
                // Restore last known good preview — never stay blank
                if (this._lastGoodHtml) {
                    const iframe = document.getElementById('previewFrame');
                    if (!iframe) return;
                    const blob = new Blob([this._lastGoodHtml], { type: 'text/html; charset=utf-8' });
                    if (this._blobUrl) URL.revokeObjectURL(this._blobUrl);
                    this._blobUrl = URL.createObjectURL(blob);
                    iframe.src   = this._blobUrl;
                }
            });

            // Listen for CRUD-generated files from the blueprint panel
            window.addEventListener('files-updated', (e) => {
                const newFiles = e.detail || [];
                newFiles.forEach(f => {
                    if (!f) return;
                    const idx = this.files.findIndex(ef => ef.path === f.path || ef.id === f.id);
                    if (idx >= 0) { this.files[idx] = { ...this.files[idx], ...f }; }
                    else { this.files.push(f); }
                });
                if (newFiles.length > 0) {
                    this.openFile(newFiles[0]);
                    this.$nextTick(() => this._instantPreview());
                }
            });

            // Pre-fill chat from ?prompt= and optionally auto-send with ?autostart=1
            const _params = new URLSearchParams(window.location.search);
            if (_params.has('template')) {
                const key = _params.get('template');
                if (this.templateOptions.some(t => t.key === key)) {
                    this.selectedTemplateKey = key;
                }
            }
            if (_params.has('prompt')) {
                this.chatInput = _params.get('prompt') || '';
            } else if (this.selectedTemplateKey) {
                this.chatInput = this.defaultTemplatePrompt();
            }

            if (this.selectedTemplateKey) {
                this.loadTemplateFiles(true);
            } else if (this.files.length > 0 && !this.activeFile) {
                this.openFile(this.files[0]);
            }

            if (_params.has('prompt') || _params.has('template')) {
                history.replaceState({}, '', window.location.pathname);
                if (_params.get('autostart') === '1') {
                    this.$nextTick(() => setTimeout(() => this.sendMessage(), 800));
                }
            }
        },

        cleanResponse(text) {
            if (!text) return '';
            let clean = text;
            // Strip fenced code blocks (complete or partial streaming)
            clean = clean.replace(/```[\w]*[\s\S]*?```/g, '');
            // Strip complete JSON objects containing "files":
            clean = clean.replace(/\{[\s\S]*?"files"\s*:[\s\S]*?\}/g, '');
            // Strip partial/streaming JSON that starts with { and has "files": (not yet closed)
            clean = clean.replace(/\{[\s\S]*?"files"\s*:[\s\S]*/g, '');
            // Strip "Next steps" block with npm/composer/localhost lines
            clean = clean.replace(/\n{0,2}(Next steps?|Setup instructions?|Getting started|To (get started|run|launch)):?[\s\S]*$/i, '');
            clean = clean.replace(/[\n\r]?\s*[•\-]\s*Run `npm [^`\n]*`[^\n]*/gi, '');
            clean = clean.replace(/[\n\r]?\s*[•\-]\s*Run `composer [^`\n]*`[^\n]*/gi, '');
            clean = clean.replace(/[\n\r]?\s*[•\-]\s*Run `?php artisan[^`\n]*`?[^\n]*/gi, '');
            clean = clean.replace(/[\n\r]?[^\n]*(http:\/\/localhost:\d+)[^\n]*/gi, '');
            // Collapse multiple blank lines
            clean = clean.replace(/\n{3,}/g, '\n\n').trim();
            return clean || '';
        },

        initTurns() {
            const msgs = this.messages;
            let i = 0;
            while (i < msgs.length) {
                const u = msgs[i];
                if (u.role !== 'user') { i++; continue; }
                const a = msgs[i + 1]?.role === 'assistant' ? msgs[i + 1] : null;
                this.turns.push({
                    id: u.id,
                    prompt: u.content,
                    time: '',
                    status: 'completed',
                    activities: a?.generated_files?.length > 0
                        ? a.generated_files.map(f => ({ icon: '📄', text: 'Created: ' + (typeof f === 'string' ? f : (f.path || f.name || f)), status: 'done' }))
                        : [],
                    files: a?.generated_files?.map(f => typeof f === 'string' ? f : (f.path || f.name || f)) || [],
                    response: a?.content || '',
                    model: a?.model || '',
                    expanded: false,
                });
                i += a ? 2 : 1;
            }
        },

        startActivitySimulation(prompt) {
            const turnId = Date.now();
            this.currentTurnId = turnId;
            const now = new Date();
            const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            this.turns.push({
                id: turnId,
                prompt,
                time,
                status: 'running',
                activities: [],
                files: [],
                response: '',
                model: '',
                expanded: true,
            });
            this.scrollChat();

            // Detect large system generation (2-phase backend + frontend build)
            const isLargeSystem = /\b(create|build|make|generate|develop|implement)\b[\s\S]{0,80}\b(system|management|platform|portal|application|shop|store|ecommerce|crm|erp|cms|booking|inventory|complete|full)\b/i.test(prompt);

            const steps = isLargeSystem ? [
                { delay: 0,       icon: '🔍', text: 'Analyzing your request' },
                { delay: 900,     icon: '🧠', text: 'Planning system architecture' },
                { delay: 2500,    icon: '🗄️', text: 'Designing database schema' },
                { delay: 5000,    icon: '⚙️', text: 'Generating backend — Phase 1 of 2' },
                { delay: 20000,   icon: '📦', text: 'Creating models & migrations' },
                { delay: 45000,   icon: '🔒', text: 'Building controllers & policies' },
                { delay: 75000,   icon: '🎨', text: 'Generating frontend views — Phase 2 of 2' },
                { delay: 105000,  icon: '💅', text: 'Styling with Tailwind CSS' },
                { delay: 135000,  icon: '💾', text: 'Writing all files to project' },
                { delay: 160000,  icon: '🔧', text: 'Finalizing and validating output' },
            ] : [
                { delay: 0,    icon: '🔍', text: 'Analyzing your request' },
                { delay: 700,  icon: '🧠', text: 'Understanding project context' },
                { delay: 1600, icon: '📋', text: 'Planning implementation' },
                { delay: 2800, icon: '⚡', text: 'Generating code' },
                { delay: 5000, icon: '💾', text: 'Writing files' },
                { delay: 8000, icon: '🔧', text: 'Finalizing output' },
            ];

            steps.forEach(({ delay, icon, text }) => {
                setTimeout(() => {
                    const t = this.turns.find(t => t.id === turnId);
                    if (!t || t.status !== 'running') return;
                    if (t.activities.length > 0) t.activities[t.activities.length - 1].status = 'done';
                    t.activities.push({ icon, text, status: 'running' });
                    this.scrollChat();
                }, delay);
            });
        },

        completeCurrentTurn(response, model, generatedFiles, isError = false) {
            const t = this.turns.find(t => t.id === this.currentTurnId);
            if (!t) return;
            if (t.activities.length > 0) t.activities[t.activities.length - 1].status = 'done';
            const filePaths = (generatedFiles || []).map(f => typeof f === 'string' ? f : (f.path || f.name || f));
            filePaths.forEach(fp => {
                if (!t.activities.find(a => a.text.includes(fp.split('/').pop()))) {
                    t.activities.push({ icon: '📄', text: 'Created: ' + fp, status: 'done' });
                }
            });
            t.status   = isError ? 'error' : 'completed';
            t.response = response;
            t.model    = model || '';
            t.files    = filePaths;
            t.expanded = false;
            this.currentTurnId = null;
            if (!isError && response) this.syncTasksFromResponse(response);
        },

        syncTasksFromResponse(text) {
            // Extract numbered list items like "1. Task text" or "- Task text"
            const numbered = [...text.matchAll(/^\s*(\d+)\.\s+(.{5,120}?)(?:\s*$)/gm)].map(m => m[2].trim());
            const bulleted = numbered.length === 0
                ? [...text.matchAll(/^\s*[-*•]\s+(.{5,120}?)(?:\s*$)/gm)].map(m => m[1].trim())
                : [];
            const extracted = (numbered.length ? numbered : bulleted)
                .filter(t => !/^(```|#{1,3}|note:|tip:|warning:)/i.test(t))
                .slice(0, 10);
            if (!extracted.length) return;

            // Mark existing todos done if text appears in the response (AI confirmed it)
            this.todos = this.todos.map(todo => {
                const key = todo.text.toLowerCase().replace(/[^a-z0-9]/g, '').slice(0, 30);
                const mentioned = extracted.some(e => e.toLowerCase().replace(/[^a-z0-9]/g, '').slice(0, 30) === key);
                return mentioned ? { ...todo, done: true } : todo;
            });

            // Add genuinely new tasks not already tracked
            const existingKeys = new Set(this.todos.map(t => t.text.toLowerCase().slice(0, 40)));
            const newTasks = extracted.filter(e => !existingKeys.has(e.toLowerCase().slice(0, 40)));
            if (newTasks.length) {
                this.todos = [...this.todos, ...newTasks.map(text => ({ text, done: false }))];
                this.showTodos = true;
            }
        },

        stripForPreview(content) {
            if (!content) return '';

            const trimmed = content.trim();
            const phpOpen = '<' + '?php';
            const phpEcho = '<' + '?=';

            // Pure PHP file (controller, model, migration) — no HTML tags at all
            if (trimmed.startsWith(phpOpen) && !/<(!DOCTYPE|html|head|body|div|main|section)/i.test(content)) {
                return '';
            }

            return content
                .replace(new RegExp(phpEcho + '[\\s\\S]*?' + '\\?>', 'g'), '...')
                .replace(new RegExp(phpOpen + '[\\s\\S]*?' + '\\?>', 'g'), '')
                .replace(new RegExp(phpOpen + '[^\\n]*', 'g'), '')
                .replace(/\{\{--[\s\S]*?--\}\}/g, '')
                .replace(/\{\{[\s\S]*?\}\}/g, '...')
                .replace(/\{!![\s\S]*?!!\}/g, '...')
                .replace(/@@extends\([^)]*\)/g, '')
                .replace(/@@section\([^)]*\)/g, '')
                .replace(/@@endsection/g, '')
                .replace(/@@yield\([^)]*\)/g, '')
                .replace(/@@include\([^)]*\)/g, '')
                .replace(/@@php[\s\S]*?@@endphp/g, '')
                .replace(/@@(if|foreach|for|while|switch|unless)\s*\([^)]*\)/g, '')
                .replace(/@@(endif|endforeach|endfor|endwhile|endswitch|endunless)/g, '')
                .replace(/@@\w+(\([^)]*\))?/g, '');
        },

        isPreviewable(filename) {
            if (!filename) return false;
            return /\.(html|htm|blade\.php)$/i.test(filename);
        },

        loadTemplatePreview() {
            if (!this.selectedTemplateKey) {
                this.autoPreview();
                return;
            }

            const iframe = document.getElementById('previewFrame');
            if (!iframe) return;

            iframe.src = `/builder/${this.projectId}/template-preview?template=${encodeURIComponent(this.selectedTemplateKey)}&v=${Date.now()}`;
            this.hasPreviewContent = true;
            if (this.viewMode === 'editor') this.viewMode = 'split';
        },

        async loadTemplateFiles(openFirst = false) {
            if (!this.selectedTemplateKey) {
                this.templateFiles = [];
                if (this.activeFile?.template_source) {
                    this.activeFile = null;
                    this.editorContent = '';
                    if (this.files.length > 0) this.openFile(this.files[0]);
                }
                this.buildFileTree();
                return;
            }

            try {
                const res = await fetch(`/builder/${this.projectId}/template-files?template=${encodeURIComponent(this.selectedTemplateKey)}`, {
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.templateFiles = data.success ? (data.files || []) : [];
                this.buildFileTree();

                if (openFirst && this.templateFiles.length > 0) {
                    this.openFile(this.templateFiles[0]);
                }
            } catch {
                this.templateFiles = [];
                this.buildFileTree();
            }
        },

        autoPreview() {
            const fs = this.files.filter(f => f.type !== 'directory');

            // React / Vite project — render inline with Babel
            if (fs.some(f => /\.(jsx|tsx)$/i.test(f.name || ''))) {
                this.buildReactInlinePreview(fs);
                return;
            }

            // Any files exist → let the server route decide what to show.
            // It returns the HTML file content when one exists, or a helpful
            // "X files generated — ask for preview.html" notice card otherwise.
            if (fs.length > 0) {
                const iframe = document.getElementById('previewFrame');
                if (iframe) {
                    iframe.src = `/builder/${this.projectId}/preview?v=${Date.now()}`;
                    this.hasPreviewContent = true;
                }
                return;
            }

            // Truly empty project — show the local "get started" overview
            this.renderProjectOverview();
        },

        loadBladePreview(file, allFs) {
            const iframe = document.getElementById('previewFrame');
            if (!iframe) return;
            iframe.src = `/builder/${this.projectId}/preview?v=${Date.now()}`;
            this.hasPreviewContent = true;
        },

        // Shown when only PHP/Blade files exist with no standalone preview.html
        renderPhpPreviewNotice(fs) {
            const fileRows = fs.map(f =>
                `<li style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:11px;opacity:.6;">📄</span>
                    <span style="font-family:monospace;font-size:11px;color:#374151;">${f.path || f.name}</span>
                 </li>`
            ).join('');
            const html = `<!DOCTYPE html><html><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Preview</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:system-ui,-apple-system,sans-serif;background:#f9fafb;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:28px;max-width:480px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.06)}.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;background:#fff7ed;color:#c2410c;border-radius:20px;font-size:11px;font-weight:600;margin-bottom:16px;border:1px solid #fed7aa}h2{font-size:17px;font-weight:700;color:#111827;margin-bottom:6px}p{font-size:13px;color:#6b7280;margin-bottom:20px;line-height:1.5}.files-label{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:8px}ul{list-style:none;max-height:280px;overflow-y:auto}.tip{margin-top:18px;padding:12px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;font-size:12px;color:#15803d;line-height:1.5}.powered{display:flex;align-items:center;gap:5px;margin-top:20px;font-size:10px;color:#9ca3af}</style></head><body>
<div class="card">
  <div class="badge">🖥️ Laravel / PHP App</div>
  <h2>${fs.length} file${fs.length !== 1 ? 's' : ''} generated</h2>
  <p>This is a server-side Laravel application. The preview below shows your generated files. To run the full app, set it up in your local Laravel environment.</p>
  ${fs.length > 0 ? `<div class="files-label">Generated files</div><ul>${fileRows}</ul>` : ''}
  <div class="tip">💡 <b>Tip:</b> Ask the AI to also generate a <code>preview.html</code> — a standalone static version of your dashboard that renders instantly without a PHP server.</div>
  <div class="powered"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Powered by RyaanCMS v{{ config('version.current', '1.0.0') }}</div>
</div></body></html>`;
            this.renderPreview(html, 'overview.html');
        },

        // Render a React/Vite project directly in the iframe using CDN React + @babel/standalone.
        // No build step needed — strips imports/exports and runs JSX via Babel in the browser.
        buildReactInlinePreview(fs) {
            // All JSX/TSX component files, excluding entry points and config
            const componentFiles = fs
                .filter(f => /\.(jsx|tsx)$/i.test(f.name || '') &&
                             !/^(main|index)\.(jsx|tsx)$/i.test(f.name || '') &&
                             f.content)
                // Deepest paths first so sub-components are defined before parents
                .sort((a, b) =>
                    (b.path || b.name || '').split('/').length -
                    (a.path || a.name || '').split('/').length
                );

            // Strip import/export syntax and Tailwind directives from each file
            const processJsx = (content) => {
                let p = (content || '').replace(/\/\/.*RyaanCMS.*/g, '');
                // Multi-line imports: import ... from '...'
                p = p.replace(/import[\s\S]*?from\s*['"][^'"]*['"]\s*;?\n?/g, '');
                // Bare imports: import './styles.css'
                p = p.replace(/import\s+['"][^'"]*['"]\s*;?\n?/g, '');
                // export default function / class / const / let / var
                p = p.replace(/export\s+default\s+(?=function\b|class\b|const\b|let\b|var\b)/g, '');
                // export default SomeIdentifier;
                p = p.replace(/^export\s+default\s+(\w+)\s*;?\n?/gm, '');
                // export const/function/class/let/var
                p = p.replace(/^export\s+(const|function|class|let|var)\b/gm, '$1');
                // export { X, Y }
                p = p.replace(/^export\s*\{[^}]*\}\s*(?:from[^;\n]*)?\s*;?\n?/gm, '');
                // "use client" / "use server" directives
                p = p.replace(/^['"]use (client|server)['"];?\n?/gm, '');
                return p;
            };

            // Collect non-Tailwind CSS
            const cssContent = fs
                .filter(f => /\.css$/i.test(f.name || '') && f.content)
                .map(f => (f.content || '').replace(/@(import|tailwind|apply|layer|config)[^;\n]*[;\n]/g, ''))
                .join('\n');

            const allCode = componentFiles
                .map(f => `/* ── ${f.name} ── */\n${processJsx(f.content)}`)
                .join('\n\n');

            const preview = `<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Preview</title>
<script src="https://cdn.tailwindcss.com"><\/script>
<script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"><\/script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"><\/script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"><\/script>
<script src="https://unpkg.com/recharts/umd/Recharts.js"><\/script>
${cssContent ? `<style>${cssContent.replace(/<\//g, '<\\/')}</style>` : ''}
</head>
<body>
<div id="root"></div>
<script type="text/babel">
const { useState, useEffect, useRef, useCallback, useMemo, createContext, useContext, Fragment, forwardRef, memo } = React;
const { LineChart, BarChart, PieChart, AreaChart, Line, Bar, Pie, Area, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, Cell, RadialBarChart, RadialBar } = window.Recharts || {};

${allCode}

class ErrorBoundary extends React.Component {
  constructor(p) { super(p); this.state = { err: null }; }
  static getDerivedStateFromError(e) { return { err: e }; }
  render() {
    if (this.state.err) {
      const _s = {padding:'24px',color:'#dc2626',fontFamily:'sans-serif',fontSize:'13px',background:'#fff1f2',borderRadius:'8px',margin:'16px'};
      return React.createElement('div', {style:_s}, '⚠ Preview error: ' + this.state.err.message);
    }
    return this.props.children;
  }
}

const RootApp = typeof App !== 'undefined' ? App
              : typeof Dashboard !== 'undefined' ? Dashboard
              : () => <div className="p-8 text-center text-gray-400">Preview ready — components loaded</div>;

ReactDOM.createRoot(document.getElementById('root')).render(
  <ErrorBoundary><RootApp /></ErrorBoundary>
);
<\/script>
</body></html>`;

            this.renderPreview(preview, 'react-preview.html');
        },

        renderProjectOverview() {
            const fs = this.files.filter(f => f.type !== 'directory');
            const fileRows = fs.map(f =>
                `<li style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px;">📄</span>
                    <span style="font-family:monospace;font-size:12px;color:#374151;">${f.path || f.name}</span>
                 </li>`
            ).join('');

            const html = `<!DOCTYPE html><html><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Project Preview</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:#f9fafb;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:32px;max-width:500px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:color-mix(in srgb,var(--brand) 8%,#fff);color:var(--brand);border-radius:20px;font-size:12px;font-weight:600;margin-bottom:20px}
h1{font-size:22px;font-weight:700;color:#111827;margin-bottom:6px}
.sub{font-size:14px;color:#6b7280;margin-bottom:24px}
.section{font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px}
ul{list-style:none}
.empty{text-align:center;padding:24px;color:#9ca3af;font-size:13px;background:#f9fafb;border-radius:10px}
.hint{margin-top:20px;padding:14px 16px;background:#f5f3ff;border-radius:10px;border:1px solid #e0e7ff;font-size:13px;color:#4338ca;line-height:1.5}
.attr{display:flex;align-items:center;gap:5px;margin-top:24px;font-size:11px;color:#9ca3af}
</style></head><body>
<div class="card">
  <div class="badge">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    RyaanCMS AI Builder
  </div>
  <h1>Project Ready</h1>
  <p class="sub">Describe what you want to build in the chat panel.</p>
  ${fs.length > 0
    ? `<div class="section">Files (${fs.length})</div><ul>${fileRows}</ul>`
    : `<div class="empty">No files yet — ask AI to generate your first feature</div>`}
  <div class="hint">💡 <strong>Try:</strong> "Create a complete hotel management system with dashboard, room booking, guest management, and invoicing"</div>
  <div class="attr">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    Powered by RyaanCMS v{{ config('version.current', '1.0.0') }}
  </div>
</div>
</body></html>`;
            this.renderPreview(html, 'overview.html');
        },

        loadPreview(file) {
            const iframe = document.getElementById('previewFrame');
            if (!iframe) return;
            iframe.src = `/builder/${this.projectId}/preview?v=${Date.now()}`;
            this.hasPreviewContent = true;
        },

        // ── Preview validation ────────────────────────────────────────────────

        _isValidPreview(html) {
            if (!html || html.trim().length < 80) return false;
            // Must be a complete HTML document (truncated files lack </html>)
            if (!/\<\/html\s*\>/i.test(html)) return false;
            // If Alpine app() is used, the function body must be present
            if (/x-data=["']app\(\)["']/i.test(html) && !/function\s+app\s*\(\s*\)/i.test(html)) return false;
            return true;
        },

        _injectErrorBoundary(html) {
            const script = `<script>
window.onerror = function(msg, src, line) {
    window.parent.postMessage({type:'ryaan-preview-error', msg:msg, line:line}, '*');
    return true;
};
window.addEventListener('unhandledrejection', function(e) {
    window.parent.postMessage({type:'ryaan-preview-error', msg:String(e.reason)}, '*');
});
<\/script>`;
            // Insert just before </head>; fall back to prepending
            return html.includes('</head>')
                ? html.replace('</head>', script + '</head>')
                : script + html;
        },

        renderPreview(html, filename) {
            const iframe = document.getElementById('previewFrame');
            if (!iframe) return;
            const isPhp = filename && /\.(php|blade\.php)$/i.test(filename);
            const content = isPhp ? this.stripForPreview(html) : html;
            if (isPhp && !content.trim()) {
                this.renderProjectOverview();
                return;
            }

            // Guard: don't swap to broken/incomplete HTML — keep last preview
            if (!this._isValidPreview(content)) return;

            // Inject error boundary so Alpine/JS crashes are reported to parent
            const guarded = this._injectErrorBoundary(content);

            // Stage the new content. Only commit as "last good" after 2.5 s with no error.
            this._stagedHtml = guarded;
            clearTimeout(this._commitTimer);
            this._commitTimer = setTimeout(() => {
                if (this._stagedHtml) {
                    this._lastGoodHtml     = this._stagedHtml;
                    this._lastGoodFilename = filename;
                    this._stagedHtml       = null;
                }
            }, 2500);

            const blob = new Blob([guarded], { type: 'text/html; charset=utf-8' });
            if (this._blobUrl) URL.revokeObjectURL(this._blobUrl);
            this._blobUrl = URL.createObjectURL(blob);
            iframe.src = this._blobUrl;
            this.hasPreviewContent = true;
        },

        // ── Instant preview helpers ──────────────────────────────────────────────

        _instantPreview() {
            const allFs = this.files.filter(f => f.type !== 'directory');
            if (!allFs.length) return;

            // Switch to split so preview becomes visible immediately
            if (this.viewMode === 'editor') this.viewMode = 'split';

            // Priority: preview.html → index.html → any .html → build card
            const order = ['preview.html', 'index.html', 'welcome.html', 'app.html'];
            let target = null;
            for (const name of order) {
                target = allFs.find(f => (f.path || f.name || '').toLowerCase().endsWith(name));
                if (target?.content) break;
                target = null;
            }
            if (!target) {
                target = allFs.find(f => /\.(html|htm)$/i.test(f.path || f.name || '') && f.content);
            }

            if (target) {
                // HTML content available — render directly (no server round-trip)
                this.renderPreview(target.content, target.path || target.name);
            }
            // PHP/Blade projects: no HTML to render — keep existing preview unchanged
        },

        refreshPreview() {
            const fs = this.files.filter(f => f.type !== 'directory');

            // React project — always use inline renderer
            if (fs.some(f => /\.(jsx|tsx)$/i.test(f.name || ''))) {
                this.buildReactInlinePreview(fs);
                return;
            }

            // If active file is previewable and user hit refresh, use editor content
            if (this.activeFile && this.isPreviewable(this.activeFile.name) && this.editorContent) {
                if (/\.(html|htm)$/i.test(this.activeFile.name)) {
                    this.renderPreview(this.editorContent, this.activeFile.name);
                } else {
                    this.loadBladePreview({ ...this.activeFile, content: this.editorContent }, fs);
                }
                return;
            }

            // Otherwise re-run auto-preview logic
            this.autoPreview();
        },

        scrollChat() {
            this.$nextTick(() => {
                const el = this.$refs.chatMessages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        onProviderChange() {
            const models = this.providerModels[this.selectedProvider] || [];
            if (!models.includes(this.selectedModel)) {
                this.selectedModel = this.providerDefaults[this.selectedProvider] || models[0] || '';
            }
            // Filter visible options in the select so only matching provider's models show
            this.$nextTick(() => {
                const sel = this.$el.querySelector('select[x-model="selectedModel"]');
                if (!sel) return;
                Array.from(sel.options).forEach(opt => {
                    const prov = opt.dataset.provider || '';
                    opt.hidden = prov !== '' && prov !== this.selectedProvider;
                });
            });
        },

        startChatResize(e) {
            const startX = e.clientX;
            const startW = this.chatPanelWidth;
            const onMove = ev => {
                this.chatPanelWidth = Math.max(260, Math.min(640, startW + ev.clientX - startX));
            };
            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            };
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        buildFileTree() {
            const dirMap = {};
            const tree   = [];
            const sorted = [...this.templateFiles, ...this.files].sort((a, b) => {
                const pa = (a.path || a.name).toLowerCase();
                const pb = (b.path || b.name).toLowerCase();
                return pa < pb ? -1 : pa > pb ? 1 : 0;
            });

            for (const file of sorted) {
                const fullPath = file.path || file.name;
                const parts    = fullPath.replace(/\\/g, '/').split('/');
                const filename = parts[parts.length - 1];

                // Create virtual directory nodes for each ancestor
                for (let d = 0; d < parts.length - 1; d++) {
                    const dirPath = parts.slice(0, d + 1).join('/');
                    if (!dirMap[dirPath]) {
                        const node = {
                            _vid:   'dir:' + dirPath,
                            type:   'directory',
                            name:   parts[d],
                            path:   dirPath,
                            depth:  d,
                            _parentPath: d > 0 ? parts.slice(0, d).join('/') : null,
                        };
                        dirMap[dirPath] = node;
                        tree.push(node);
                    }
                }

                // File leaf node
                tree.push({
                    ...file,
                    name:  filename,
                    path:  fullPath,
                    depth: parts.length - 1,
                    _parentPath: parts.length > 1 ? parts.slice(0, -1).join('/') : null,
                });
            }

            this.fileTree = tree;
        },

        treeVisible(item) {
            let ancestor = item._parentPath;
            while (ancestor) {
                if (this.collapsedDirs['dir:' + ancestor]) return false;
                const lastSlash = ancestor.lastIndexOf('/');
                ancestor = lastSlash >= 0 ? ancestor.slice(0, lastSlash) : null;
            }
            return true;
        },

        toggleDir(vid) {
            this.collapsedDirs = { ...this.collapsedDirs, [vid]: !this.collapsedDirs[vid] };
        },

        extColor(filename) {
            const ext = (filename || '').split('.').pop().toLowerCase();
            const map = {
                php: '#8b5cf6', blade: '#f97316', js: '#eab308', ts: '#3b82f6',
                vue: '#22c55e', jsx: '#38bdf8', tsx: '#38bdf8', css: '#06b6d4',
                scss: '#ec4899', html: '#f97316', htm: '#f97316', json: '#a3e635',
                md: '#94a3b8', env: '#ef4444', xml: '#fb923c', sql: '#0ea5e9',
                txt: '#94a3b8', sh: '#6ee7b7', yaml: '#fbbf24', yml: '#fbbf24',
            };
            return map[ext] || '#6b7280';
        },

        async openFile(file) {
            if (file.type === 'directory') return;
            this.activeFile = file;
            try {
                // Use in-memory content if available to avoid a round-trip
                if (file.content !== undefined) {
                    this.editorContent = file.content || '';
                } else {
                    const res = await fetch(`/builder/${this.projectId}/files/${file.id}`, {
                        headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    this.editorContent = data.content || '';
                }
                // Only auto-render pure HTML files — PHP/Blade require a server and would
                // overwrite a correct preview.html rendering with stripped/broken content
                if (/\.(html|htm)$/i.test(file.name) && this.viewMode !== 'editor') {
                    this.$nextTick(() => this.renderPreview(this.editorContent, file.name));
                }
            } catch(e) {
                this.editorContent = file.content || '';
            }
        },

        async saveFile() {
            if (!this.activeFile || this.saving) return;
            if (this.activeFile.read_only) return;
            this.saving = true;
            try {
                await fetch(`/builder/${this.projectId}/files/${this.activeFile.id}`, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ content: this.editorContent })
                });
                // Refresh preview after saving a previewable file
                if (this.isPreviewable(this.activeFile.name) && this.viewMode !== 'editor') {
                    this.renderPreview(this.editorContent, this.activeFile.name);
                }
            } finally {
                this.saving = false;
            }
        },

        async sendMessage(text = null) {
            const message  = text || this.chatInput.trim();
            const hasFiles = this.attachments.length > 0;
            const hasUrl   = !!this.urlPreview;
            const hasTemplate = !!this.selectedTemplateKey;
            if ((!message && !hasFiles && !hasUrl) || this.isThinking) return;

            if (this.isRecording) { this.recognition?.stop(); this.isRecording = false; }

            this.chatInput  = '';
            this.isThinking = true;

            const displayParts = [message];
            if (hasFiles) displayParts.push('📎 ' + this.attachments.map(a => a.icon + ' ' + a.name).join(', '));
            if (hasUrl)   displayParts.push('🌐 ' + this.urlPreview);
            if (hasTemplate) displayParts.unshift('Template: ' + this.selectedTemplateName());
            const displayPrompt = displayParts.filter(Boolean).join('\n');

            try {
                if (hasFiles || hasUrl) {
                    // Attachments: use regular JSON endpoint (streaming doesn't support FormData)
                    this.startActivitySimulation(displayPrompt);
                    await this.sendRegularMessage(message, displayPrompt);
                } else {
                    // Text-only: real SSE streaming — activity events come from server
                    this.startTurn(displayPrompt);
                    await this.sendStreamingMessage(message);
                }
            } catch(e) {
                if (e?.name !== 'AbortError') {
                    this.completeCurrentTurn('❌ ' + (e?.message || 'Connection error. Please try again.'), '', [], true);
                }
            } finally {
                this._streamAbortCtrl = null;
                this.isThinking = false;
                this.scrollChat();
            }
        },

        // Create a turn card without the fake simulation (for streaming mode)
        startTurn(prompt) {
            const turnId = Date.now();
            this.currentTurnId = turnId;
            const now  = new Date();
            const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            this.turns.push({
                id: turnId, prompt, time,
                status: 'running', activities: [], files: [], response: '', model: '', expanded: true,
            });
            this.scrollChat();
        },

        stopGeneration() {
            if (this._streamAbortCtrl) {
                this._streamAbortCtrl.abort();
                this._streamAbortCtrl = null;
            }
            this.isThinking = false;
            const t = this.turns.find(t => t.id === this.currentTurnId);
            if (t) {
                if (t.activities.length > 0) t.activities[t.activities.length - 1].status = 'done';
                t.activities.push({ icon: '⏹️', text: 'Generation stopped by user', status: 'done' });
                t.status = 'completed';
                t.expanded = false;
            }
            this.currentTurnId = null;
        },

        async sendStreamingMessage(message) {
            this._streamAbortCtrl = new AbortController();
            let response;
            try {
                response = await fetch(`/builder/${this.projectId}/stream`, {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept':       'text/event-stream',
                    },
                    body:   JSON.stringify({
                        message,
                        conversation_id: this.conversationId,
                        provider:        this.selectedProvider,
                        model:           this.selectedModel,
                        template_key:    this.selectedTemplateKey || null,
                    }),
                    signal: this._streamAbortCtrl.signal,
                });
            } catch (e) {
                if (e.name === 'AbortError') return;
                throw e;
            }

            if (!response.ok) {
                const errText = await response.text().catch(() => 'HTTP ' + response.status);
                throw new Error(errText);
            }

            const reader  = response.body.getReader();
            const decoder = new TextDecoder();
            let   buffer  = '';

            while (true) {
                let done, value;
                try {
                    ({ done, value } = await reader.read());
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    throw e;
                }
                if (done) break;

                buffer += decoder.decode(value, { stream: true });

                // Process complete SSE lines from the buffer
                while (buffer.includes('\n')) {
                    const pos  = buffer.indexOf('\n');
                    const line = buffer.slice(0, pos).trimEnd();
                    buffer = buffer.slice(pos + 1);

                    if (!line.startsWith('data: ')) continue;
                    const raw = line.slice(6).trim();
                    if (!raw || raw === '[DONE]') continue;

                    try {
                        const event = JSON.parse(raw);
                        this.handleStreamEvent(event);
                    } catch (_) {}
                }
            }
        },

        handleStreamEvent(event) {
            const t = this.turns.find(t => t.id === this.currentTurnId);
            if (!t) return;

            if (event.type === 'activity') {
                // Mark previous step done, add new running step
                if (t.activities.length > 0) t.activities[t.activities.length - 1].status = 'done';
                t.activities.push({ icon: event.icon || '⚡', text: event.text, status: 'running' });
                this.scrollChat();

            } else if (event.type === 'chunk') {
                t._rawResponse = (t._rawResponse || '') + event.text;
                // Response shown only on 'done' via event.message — no raw code visible during streaming

            } else if (event.type === 'files') {
                // Add or update files in the tree as they arrive
                (event.files || []).forEach(f => {
                    if (!f) return;
                    const existingIdx = this.files.findIndex(ef => ef.id === f.id || ef.path === f.path);
                    if (existingIdx >= 0) {
                        this.files[existingIdx] = { ...this.files[existingIdx], ...f };
                    } else {
                        this.files.push(f);
                    }
                    const fp = f.path || f.name || '';
                    if (fp && !t.files.includes(fp)) t.files.push(fp);
                });

                // Instant preview — no waiting for 'done'
                this.$nextTick(() => this._instantPreview());

            } else if (event.type === 'done') {
                if (event.tokens_used) this.tokensUsed += event.tokens_used;
                if (event.cache_hit) { this.cacheHits++; this.tokensSaved += (event.tokens_saved || 7850); }
                this.completeCurrentTurn(event.message, event.model, event.files || []);

                // Ensure all done-event files are in the tree (add or update)
                (event.files || []).forEach(f => {
                    if (!f) return;
                    const existingIdx = this.files.findIndex(ef => ef.id === f.id || ef.path === f.path);
                    if (existingIdx >= 0) {
                        this.files[existingIdx] = { ...this.files[existingIdx], ...f };
                    } else {
                        this.files.push(f);
                    }
                });

                // Open first file in editor
                const allFiles = event.files || [];
                if (allFiles.length > 0) {
                    this.openFile(allFiles[0]);
                }

                // Always refresh preview after build completes, switch to preview if in editor-only mode
                this.$nextTick(() => {
                    if (this.viewMode === 'editor') this.viewMode = 'split';
                    this.autoPreview();
                });

            } else if (event.type === 'error') {
                this.completeCurrentTurn('❌ ' + (event.message || 'Something went wrong.'), '', [], true);
            }
        },

        async sendRegularMessage(message, displayPrompt) {
            const fd = new FormData();
            fd.append('message', message || 'Please analyze the attached content and suggest what to build.');
            fd.append('conversation_id', this.conversationId);
            fd.append('provider', this.selectedProvider);
            fd.append('model', this.selectedModel);
            if (this.selectedTemplateKey) fd.append('template_key', this.selectedTemplateKey);
            if (this.urlPreview) fd.append('url', this.urlPreview);
            this.attachments.forEach(att => fd.append('files[]', att.file, att.name));

            this.attachments = [];
            this.urlPreview  = '';

            const res  = await fetch(`/builder/${this.projectId}/chat`, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body:    fd,
            });
            const data = await res.json();

            if (data.success) {
                if (data.tokens_used) this.tokensUsed += data.tokens_used;
                if (data.cache_hit) { this.cacheHits++; this.tokensSaved += (data.tokens_saved || 7850); }
                this.completeCurrentTurn(data.message, data.model, data.files_generated);
                if (data.files_generated?.length > 0) {
                    this.files = [...this.files, ...data.files_generated.filter(nf => !this.files.find(f => f.path === nf.path))];
                    this.openFile(data.files_generated[0]);
                }
                this.$nextTick(() => {
                    if (this.viewMode === 'editor') this.viewMode = 'split';
                    this.autoPreview();
                });
            } else {
                this.completeCurrentTurn(
                    '❌ ' + (data.error || 'Something went wrong. Please check your AI API key in Settings.'),
                    '', [], true
                );
            }
        },

        handleFiles(event) {
            Array.from(event.target.files).forEach(file => {
                this.attachments.push({ file, name: file.name, icon: this.fileIcon(file.type, file.name) });
            });
            event.target.value = '';
        },

        removeAttachment(i) { this.attachments.splice(i, 1); },

        fileIcon(mime, name) {
            if (mime.startsWith('image/'))                                        return '🖼️';
            if (mime === 'application/pdf')                                       return '📄';
            if (mime.includes('word')          || /\.docx?$/.test(name))         return '📝';
            if (mime.includes('spreadsheet')   || mime.includes('excel')
                || /\.(xlsx?|csv)$/.test(name))                                  return '📊';
            if (mime.includes('presentation')  || /\.pptx?$/.test(name))         return '📑';
            if (/\.(json|xml|yaml|yml|toml)$/.test(name))                        return '⚙️';
            if (/\.sql$/.test(name))                                              return '🗄️';
            if (/\.(php|js|ts|jsx|tsx|py|java|rb|go|rs|sh|bat)$/.test(name))    return '💻';
            return '📁';
        },

        addUrl() {
            const url = this.urlInput.trim();
            if (!url || !/^https?:\/\//i.test(url)) {
                alert('Please enter a valid URL starting with https://');
                return;
            }
            this.urlPreview   = url;
            this.showUrlInput = false;
            this.urlInput     = '';
        },

        toggleVoice() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) { alert('Voice input requires Chrome or Edge browser.'); return; }
            if (this.isRecording) { this.recognition?.stop(); return; }
            this.recognition                = new SR();
            this.recognition.continuous     = true;
            this.recognition.interimResults = true;
            this.recognition.lang           = '';
            let final = '';
            this.recognition.onresult = (e) => {
                let interim = '';
                for (let i = e.resultIndex; i < e.results.length; i++) {
                    const t = e.results[i][0].transcript;
                    e.results[i].isFinal ? (final += t + ' ') : (interim = t);
                }
                this.chatInput = final + interim;
            };
            this.recognition.onend   = () => { this.isRecording = false; };
            this.recognition.onerror = (e) => { this.isRecording = false; };
            this.recognition.start();
            this.isRecording = true;
        },

        async createFile() {
            const path = prompt('Enter file path (e.g., app/Models/Product.php):');
            if (!path) return;
            const res = await fetch(`/builder/${this.projectId}/files`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ path, type: 'file' })
            });
            const data = await res.json();
            if (data.success) { this.files.push(data.file); this.openFile(data.file); }
        },

        async createFolder() {
            const path = prompt('Enter folder path (e.g., app/Services):');
            if (!path) return;
            const res = await fetch(`/builder/${this.projectId}/files`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ path, type: 'directory' })
            });
            const data = await res.json();
            if (data.success) this.files.push(data.file);
        },

        async deleteFile(file) {
            if (file.read_only) return;
            if (!confirm(`Delete ${file.name}?`)) return;
            await fetch(`/builder/${this.projectId}/files/${file.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken }
            });
            this.files = this.files.filter(f => f.id !== file.id);
            if (this.activeFile?.id === file.id) { this.activeFile = null; this.editorContent = ''; }
        },

        async newConversation() {
            const res = await fetch(`/builder/${this.projectId}/conversations`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ provider: this.selectedProvider })
            });
            const data = await res.json();
            if (data.success) { this.conversationId = data.conversation.id; this.messages = []; this.turns = []; }
        },
    }
}

// blueprintPanel() removed — blueprint now runs automatically inside the chat flow.
// No button needed. User just types, AI architect works invisibly.
function blueprintPanel() { return {}; } // kept as no-op to avoid reference errors
function _blueprintPanelFull_unused() {
    return {
        activeTab:         'chat',
        blueprint:         null,
        discoveryText:     '',
        discovering:       false,
        discoveryError:    '',
        generating:        null,
        crudResult:        '',
        showEntityBuilder: false,
        availablePacks:    [],
        newEntity:         { name: '', fields: [] },
        projectId:         {{ $project->id }},
        csrfToken:         document.querySelector('meta[name="csrf-token"]')?.content,

        async loadBlueprint() {
            try {
                const res = await fetch(`/builder/${this.projectId}/blueprint`);
                const data = await res.json();
                if (data.success) {
                    this.blueprint    = data.blueprint;
                    this.availablePacks = data.domain_packs || [];
                }
            } catch {}
        },

        async runDiscovery() {
            if (!this.discoveryText.trim()) return;
            this.discovering   = true;
            this.discoveryError = '';
            try {
                const fd = new FormData();
                fd.append('description', this.discoveryText);
                const res  = await fetch(`/builder/${this.projectId}/discover`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken },
                    body: fd,
                });
                const data = await res.json();
                if (data.success) {
                    this.blueprint = data.blueprint;
                    this.crudResult = '';
                } else {
                    this.discoveryError = data.error || 'Discovery failed.';
                }
            } catch (e) {
                this.discoveryError = 'Network error: ' + e.message;
            } finally {
                this.discovering = false;
            }
        },

        async generateCrud(entity) {
            if (!entity?.name) return;
            this.generating  = entity.name;
            this.crudResult  = '';
            try {
                const res  = await fetch(`/builder/${this.projectId}/generate-crud`, {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ entity }),
                });
                const data = await res.json();
                if (data.success) {
                    this.crudResult = data.message + '\n' + (data.files_generated || []).map(f => '  • ' + (f.path || f.name)).join('\n');
                    // Notify builderApp to refresh file list
                    window.dispatchEvent(new CustomEvent('files-updated', { detail: data.files_generated || [] }));
                } else {
                    this.crudResult = 'Error: ' + (data.error || 'Generation failed.');
                }
            } catch (e) {
                this.crudResult = 'Network error: ' + e.message;
            } finally {
                this.generating = null;
            }
        },

        async loadDomainPack(packKey) {
            try {
                const res  = await fetch(`/builder/domain-packs`);
                const data = await res.json();
                if (data.success && data.packs[packKey]) {
                    const pack = data.packs[packKey];
                    if (!this.blueprint) {
                        this.blueprint = {
                            app_type: packKey,
                            name: pack.name.split('—')[0].trim(),
                            features: pack.features || [],
                            matched_packs: [packKey],
                            suggested_entities: pack.entities || [],
                            entities: pack.entities || [],
                        };
                    } else {
                        // Merge pack entities in
                        const existing = new Set((this.blueprint.suggested_entities || []).map(e => e.name));
                        for (const e of (pack.entities || [])) {
                            if (!existing.has(e.name)) {
                                (this.blueprint.suggested_entities = this.blueprint.suggested_entities || []).push(e);
                                existing.add(e.name);
                            }
                        }
                        if (!this.blueprint.matched_packs) this.blueprint.matched_packs = [];
                        if (!this.blueprint.matched_packs.includes(packKey)) this.blueprint.matched_packs.push(packKey);
                    }
                }
            } catch {}
        },
    }
}
</script>
@endpush
