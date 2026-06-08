@extends('layouts.app')
@section('title', 'Projects')
@section('header', 'My Projects')

@section('header-actions')
<a href="{{ route('projects.create') }}"
   class="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
   style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>New Project</span>
</a>
@endsection

@section('content')

@php
$typeMap = [
    'laravel'    => ['emoji'=>'⚡','from'=>'#f97316','to'=>'#ef4444','bg'=>'#fff7ed','txt'=>'#c2410c','label'=>'Laravel'],
    'react'      => ['emoji'=>'⚛️','from'=>'#06b6d4','to'=>'#3b82f6','bg'=>'#ecfeff','txt'=>'#0e7490','label'=>'React'],
    'nextjs'     => ['emoji'=>'▲', 'from'=>'#6b7280','to'=>'#374151','bg'=>'#f9fafb','txt'=>'#374151','label'=>'Next.js'],
    'ecommerce'  => ['emoji'=>'🛒','from'=>'#10b981','to'=>'#059669','bg'=>'#ecfdf5','txt'=>'#065f46','label'=>'eCommerce'],
    'crm'        => ['emoji'=>'👥','from'=>'#8b5cf6','to'=>'#7c3aed','bg'=>'#fdf4ff','txt'=>'#6d28d9','label'=>'CRM'],
    'hrm'        => ['emoji'=>'🧑‍💼','from'=>'#0ea5e9','to'=>'#6366f1','bg'=>'#f0f9ff','txt'=>'#0369a1','label'=>'HRM'],
    'erp'        => ['emoji'=>'🏭','from'=>'#d97706','to'=>'#b45309','bg'=>'#fffbeb','txt'=>'#92400e','label'=>'ERP'],
    'saas'       => ['emoji'=>'🚀','from'=>'#f59e0b','to'=>'#d97706','bg'=>'#fffbeb','txt'=>'#92400e','label'=>'SaaS'],
    'hospital'   => ['emoji'=>'🏥','from'=>'#ef4444','to'=>'#dc2626','bg'=>'#fef2f2','txt'=>'#991b1b','label'=>'Hospital'],
    'school'     => ['emoji'=>'🎓','from'=>'#06b6d4','to'=>'#0891b2','bg'=>'#ecfeff','txt'=>'#0e7490','label'=>'School'],
    'restaurant' => ['emoji'=>'🍽️','from'=>'#f97316','to'=>'#ea580c','bg'=>'#fff7ed','txt'=>'#c2410c','label'=>'Restaurant'],
    'pos'        => ['emoji'=>'🏪','from'=>'#10b981','to'=>'#059669','bg'=>'#ecfdf5','txt'=>'#065f46','label'=>'POS'],
    'inventory'  => ['emoji'=>'📦','from'=>'#6366f1','to'=>'#4f46e5','bg'=>'#eef2ff','txt'=>'#3730a3','label'=>'Inventory'],
    'api'        => ['emoji'=>'🔌','from'=>'#64748b','to'=>'#475569','bg'=>'#f8fafc','txt'=>'#475569','label'=>'API'],
    'default'    => ['emoji'=>'🗂️','from'=>'#6366f1','to'=>'#8b5cf6','bg'=>'#eef2ff','txt'=>'#4338ca','label'=>'App'],
];
@endphp

@if($projects->isEmpty())

<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-5"
         style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
        <svg class="w-10 h-10" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </div>
    <h2 class="text-xl font-bold mb-2" style="color:var(--text-1);">No projects yet</h2>
    <p class="text-sm mb-7 max-w-sm" style="color:var(--text-3);">
        Create your first AI-powered application. From eCommerce to SaaS — build anything with a single prompt.
    </p>
    <a href="{{ route('projects.create') }}"
       class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-0.5"
       style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 4px 16px rgba(99,102,241,.3);">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Create First Project</span>
    </a>
</div>

@else

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($projects as $project)
    @php $tc = $typeMap[$project->type] ?? $typeMap['default']; @endphp

    <div class="group rounded-2xl overflow-hidden transition-all hover:-translate-y-0.5"
         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);"
         onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.borderColor='{{ $tc['from'] }}44';"
         onmouseout="this.style.boxShadow='var(--shadow)';this.style.borderColor='var(--border)';">

        {{-- Colored header strip --}}
        <div class="h-24 flex items-center justify-center relative"
             style="background:linear-gradient(135deg,{{ $tc['from'] }}18,{{ $tc['to'] }}28); border-bottom:1px solid var(--border);">
            <span class="text-4xl select-none">{{ $tc['emoji'] }}</span>
            {{-- Status badge --}}
            <div class="absolute top-3 right-3">
                @if($project->status === 'active')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                      style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                </span>
                @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                      style="background:var(--card-sub); color:var(--text-3); border:1px solid var(--border);">
                    {{ ucfirst($project->status) }}
                </span>
                @endif
            </div>
            {{-- Type badge --}}
            <div class="absolute top-3 left-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                      style="background:{{ $tc['bg'] }}; color:{{ $tc['txt'] }}; border:1px solid {{ $tc['from'] }}33;">
                    {{ $tc['label'] }}
                </span>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-5">
            <div class="flex items-start justify-between mb-1.5">
                <h3 class="font-semibold text-base truncate pr-2" style="color:var(--text-1);">{{ $project->name }}</h3>
                {{-- Kebab menu --}}
                <div class="relative flex-shrink-0" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                            style="color:var(--text-3);"
                            onmouseover="this.style.background='var(--hover-bg)'"
                            onmouseout="this.style.background=''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                         class="absolute right-0 top-8 z-20 w-40 rounded-xl py-1.5 overflow-hidden"
                         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow-lg);">
                        <a href="{{ route('projects.show', $project) }}"
                           class="flex items-center space-x-2 px-3.5 py-2 text-sm transition-colors"
                           style="color:var(--text-2);"
                           onmouseover="this.style.background='var(--hover-bg)'"
                           onmouseout="this.style.background=''">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>View Details</span>
                        </a>
                        <div style="border-top:1px solid var(--border); margin:3px 0;"></div>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST"
                              onsubmit="return confirm('Delete {{ addslashes($project->name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full flex items-center space-x-2 px-3.5 py-2 text-sm transition-colors"
                                    style="color:#ef4444;"
                                    onmouseover="this.style.background='#fef2f2'"
                                    onmouseout="this.style.background=''">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <p class="text-xs mb-4 line-clamp-2" style="color:var(--text-3);">
                {{ $project->description ?: 'No description added.' }}
            </p>

            {{-- Tech Stack --}}
            @if($project->tech_stack)
            <div class="flex flex-wrap gap-1.5 mb-4">
                @foreach(array_slice($project->tech_stack, 0, 3) as $tech)
                <span class="px-2 py-0.5 text-[10px] font-medium rounded-md"
                      style="background:var(--card-sub); color:var(--text-2); border:1px solid var(--border);">{{ $tech }}</span>
                @endforeach
                @if(count($project->tech_stack) > 3)
                <span class="px-2 py-0.5 text-[10px] rounded-md"
                      style="background:var(--card-sub); color:var(--text-3);">+{{ count($project->tech_stack) - 3 }}</span>
                @endif
            </div>
            @endif

            {{-- Meta row --}}
            <div class="flex items-center justify-between text-[10px] mb-4" style="color:var(--text-3);">
                <span>{{ $project->files_count ?? 0 }} files</span>
                <span>{{ $project->storage_used_formatted }}</span>
                <span>{{ $project->updated_at->diffForHumans() }}</span>
            </div>

            {{-- Actions --}}
            <div class="flex space-x-2">
                <a href="{{ route('builder.show', $project) }}"
                   class="flex-1 flex items-center justify-center space-x-1.5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                   style="background:linear-gradient(135deg,{{ $tc['from'] }},{{ $tc['to'] }}); box-shadow:0 2px 8px {{ $tc['from'] }}44;">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <span>Open Builder</span>
                </a>
                @if($project->deployment_url)
                <a href="{{ $project->deployment_url }}" target="_blank" rel="noopener"
                   class="w-9 flex items-center justify-center rounded-xl transition-colors"
                   style="border:1px solid var(--border); color:var(--text-2);"
                   onmouseover="this.style.background='var(--hover-bg)'"
                   onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-6">{{ $projects->links() }}</div>

@endif
@endsection
