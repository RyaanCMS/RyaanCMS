@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@php
$hour = now()->hour;
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

@section('content')
<div class="space-y-6">

    {{-- ══════════ WELCOME BANNER ══════════ --}}
    <div class="rounded-2xl overflow-hidden relative"
         style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 40%,#1e1b4b 100%);
                border:1px solid rgba(139,92,246,0.3);
                box-shadow:0 4px 24px rgba(99,102,241,.25);">
        <!-- decorative glows -->
        <div class="absolute right-0 top-0 w-72 h-72 rounded-full pointer-events-none"
             style="background:radial-gradient(circle,rgba(139,92,246,0.25),transparent);
                    transform:translate(20%,-20%)"></div>
        <div class="absolute left-0 bottom-0 w-48 h-48 rounded-full pointer-events-none"
             style="background:radial-gradient(circle,rgba(99,102,241,0.15),transparent);
                    transform:translate(-20%,20%)"></div>
        <div class="relative px-7 py-7">
            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold"
                              style="background:rgba(167,139,250,0.15);border:1px solid rgba(167,139,250,0.25);color:#c4b5fd;">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            AI Ready
                        </span>
                    </div>
                    <p class="text-sm font-medium mb-0.5" style="color:#a5b4fc;">{{ $greeting }},</p>
                    <h2 class="text-2xl font-bold mb-2" style="color:#ffffff;">{{ auth()->user()->name }} 👋</h2>
                    <p class="text-sm" style="color:#a5b4fc;">
                        You have <span class="font-bold" style="color:#c4b5fd;">{{ $stats['projects'] }}
                        project{{ $stats['projects'] != 1 ? 's' : '' }}</span> running. Keep building!
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a href="https://github.com/ryaancms" target="_blank" rel="noopener"
                       class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl font-medium text-sm
                              transition-all hover:-translate-y-px"
                       style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#e2e8f0;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                        <span>GitHub</span>
                    </a>
                    <a href="{{ route('projects.create') }}"
                       class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl font-semibold text-sm
                              transition-all hover:-translate-y-px text-white"
                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6);
                              box-shadow:0 4px 16px rgba(99,102,241,.45);"
                       onmouseover="this.style.filter='brightness(1.1)'"
                       onmouseout="this.style.filter=''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>New Project</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ STAT CARDS ══════════ --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @php
        $statCards = [
            [
                'label'     => 'Total Projects',
                'value'     => $stats['projects'],
                'unit'      => '',
                'iconColor' => '#6366f1',
                'accent'    => '#6366f1',
                'iconBg'    => 'linear-gradient(135deg,#eef2ff,#e0e7ff)',
                'numColor'  => '#3730a3',
                'badge'     => 'Active',
                'icon'      => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
            ],
            [
                'label'     => 'AI Messages',
                'value'     => number_format($stats['ai_messages']),
                'unit'      => '',
                'iconColor' => '#8b5cf6',
                'accent'    => '#8b5cf6',
                'iconBg'    => 'linear-gradient(135deg,#fdf4ff,#ede9fe)',
                'numColor'  => '#6d28d9',
                'badge'     => 'Conversations',
                'icon'      => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
            ],
            [
                'label'     => 'Deployments',
                'value'     => $stats['deployments'],
                'unit'      => '',
                'iconColor' => '#0ea5e9',
                'accent'    => '#0ea5e9',
                'iconBg'    => 'linear-gradient(135deg,#ecfeff,#e0f2fe)',
                'numColor'  => '#0369a1',
                'badge'     => 'Shipped',
                'icon'      => 'M5 12h14M12 5l7 7-7 7',
            ],
            [
                'label'     => 'Storage Used',
                'value'     => round($stats['storage']/1048576,1),
                'unit'      => 'MB',
                'iconColor' => '#10b981',
                'accent'    => '#10b981',
                'iconBg'    => 'linear-gradient(135deg,#ecfdf5,#d1fae5)',
                'numColor'  => '#065f46',
                'badge'     => 'Used',
                'icon'      => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
            ],
        ];
        @endphp

        @foreach($statCards as $sc)
        <div class="rounded-2xl overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-lg"
             style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
            <!-- Colored top accent bar -->
            <div class="h-1" style="background:{{ $sc['accent'] }};opacity:0.7;"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background:{{ $sc['iconBg'] }}; border:1px solid {{ $sc['accent'] }}22;">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             style="color:{{ $sc['iconColor'] }}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $sc['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                          style="background:{{ $sc['accent'] }}15; color:{{ $sc['accent'] }}; border:1px solid {{ $sc['accent'] }}22;">
                        {{ $sc['badge'] }}
                    </span>
                </div>
                <p class="text-3xl font-black tracking-tight mb-0.5" style="color:{{ $sc['numColor'] }}; letter-spacing:-0.03em;">
                    {{ $sc['value'] }}@if($sc['unit'])<span class="text-base font-semibold ml-0.5" style="color:{{ $sc['accent'] }};">{{ $sc['unit'] }}</span>@endif
                </p>
                <p class="text-xs font-medium" style="color:var(--text-3);">{{ $sc['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══════════ MAIN GRID ══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Recent Projects ── --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl overflow-hidden"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--border);">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                             style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold" style="color:var(--text-1);">Recent Projects</h3>
                    </div>
                    <a href="{{ route('projects.index') }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                       style="color:#6366f1; background:#eef2ff;"
                       onmouseover="this.style.background='#e0e7ff'"
                       onmouseout="this.style.background='#eef2ff'">View all →</a>
                </div>

                @if($recentProjects->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
                         style="background:linear-gradient(135deg,#eef2ff,#ede9fe);">
                        <svg class="w-8 h-8" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm mb-1" style="color:#9ca3af;">No projects yet</h4>
                    <p class="text-xs mb-5" style="color:var(--text-3);">Create your first AI-powered app</p>
                    <a href="{{ route('projects.create') }}"
                       class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 12px rgba(99,102,241,.3);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Create First Project</span>
                    </a>
                </div>
                @else
                @php
                $typeMap = [
                    'laravel'   => ['from'=>'#f97316','to'=>'#ef4444','emoji'=>'⚡','bg'=>'#fff7ed','txt'=>'#c2410c'],
                    'react'     => ['from'=>'#06b6d4','to'=>'#3b82f6','emoji'=>'⚛️','bg'=>'#ecfeff','txt'=>'#0e7490'],
                    'nextjs'    => ['from'=>'#6b7280','to'=>'#374151','emoji'=>'▲','bg'=>'#f9fafb','txt'=>'#374151'],
                    'ecommerce' => ['from'=>'#10b981','to'=>'#059669','emoji'=>'🛒','bg'=>'#ecfdf5','txt'=>'#065f46'],
                    'crm'       => ['from'=>'#8b5cf6','to'=>'#7c3aed','emoji'=>'👥','bg'=>'#fdf4ff','txt'=>'#6d28d9'],
                    'saas'      => ['from'=>'#f59e0b','to'=>'#d97706','emoji'=>'🚀','bg'=>'#fffbeb','txt'=>'#92400e'],
                    'default'   => ['from'=>'#6366f1','to'=>'#8b5cf6','emoji'=>'🗂️','bg'=>'#eef2ff','txt'=>'#4338ca'],
                ];
                @endphp
                @foreach($recentProjects as $proj)
                @php $tc = $typeMap[$proj->type] ?? $typeMap['default']; @endphp
                <div class="group flex items-center px-5 py-3.5 transition-colors cursor-pointer"
                     style="border-bottom:1px solid var(--border);"
                     onclick="window.location='{{ route('projects.show', $proj) }}'"
                     onmouseover="this.style.background='var(--hover-bg)'"
                     onmouseout="this.style.background=''">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0 mr-4"
                         style="background:linear-gradient(135deg,{{ $tc['from'] }}18,{{ $tc['to'] }}28);
                                border:1px solid {{ $tc['from'] }}33;">
                        {{ $tc['emoji'] }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate" style="color:var(--text-1);">{{ $proj->name }}</p>
                        <div class="flex items-center space-x-2 mt-0.5">
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md"
                                  style="background:{{ $tc['bg'] }};color:{{ $tc['txt'] }};">{{ $proj->type }}</span>
                            <span class="text-[10px]" style="color:var(--text-3);">{{ $proj->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-3 flex-shrink-0">
                        <span class="text-[10px] font-semibold px-2 py-1 rounded-full border"
                              style="{{ $proj->status === 'active'
                                 ? 'background:#ecfdf5;color:#065f46;border-color:#a7f3d0'
                                 : 'background:var(--card-sub);color:var(--text-3);border-color:var(--border)' }}">
                            {{ ucfirst($proj->status) }}
                        </span>
                        <a href="{{ route('projects.show', $proj) }}"
                           class="opacity-0 group-hover:opacity-100 text-[10px] font-bold px-3 py-1.5 rounded-lg
                                  transition-all text-white whitespace-nowrap"
                           style="background:linear-gradient(135deg,{{ $tc['from'] }},{{ $tc['to'] }});">
                            Open →
                        </a>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        {{-- ── Right Column ── --}}
        <div class="space-y-5">

            {{-- Quick Start --}}
            <div class="rounded-2xl overflow-hidden"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <div class="flex items-center space-x-2.5 px-5 py-4" style="border-bottom:1px solid var(--border);">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                         style="background:#6366f1;">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold" style="color:var(--text-1);">Quick Start</h3>
                </div>
                <div class="p-3 space-y-0.5">
                    @foreach([
                        ['name'=>'eCommerce Store','emoji'=>'🛒'],
                        ['name'=>'CRM System',     'emoji'=>'👥'],
                        ['name'=>'Restaurant App', 'emoji'=>'🍽️'],
                        ['name'=>'SaaS Platform',  'emoji'=>'🚀'],
                        ['name'=>'Clinic System',  'emoji'=>'🏥'],
                    ] as $tpl)
                    <a href="{{ route('projects.create') }}?template={{ Str::slug($tpl['name']) }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                       style="color:var(--text-2);"
                       onmouseover="this.style.background='var(--hover-bg)';this.style.color='var(--text-1)';"
                       onmouseout="this.style.background='';this.style.color='var(--text-2)';">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-sm"
                                  style="background:#f1f5f9; border:1px solid #e2e8f0;">{{ $tpl['emoji'] }}</span>
                            <span>{{ $tpl['name'] }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5" style="color:#cbd5e1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Install Plugin --}}
            <div class="rounded-2xl overflow-hidden"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <div class="flex items-center space-x-2.5 px-5 py-4" style="border-bottom:1px solid var(--border);">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                         style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold flex-1" style="color:var(--text-1);">Packages</h3>
                    <a href="{{ route('marketplace.installed') }}" class="text-xs" style="color:#6366f1;">View all →</a>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('marketplace.upload-install') }}"
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all group"
                       style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;"
                       onmouseover="this.style.filter='brightness(0.97)'"
                       onmouseout="this.style.filter=''">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:#6366f1;">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold" style="color:#4338ca;">Upload & Install</p>
                            <p class="text-xs" style="color:#6d28d9;">Install a downloaded .zip package</p>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('marketplace.index') }}"
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all"
                       style="color:var(--text-2);"
                       onmouseover="this.style.background='var(--hover-bg)'"
                       onmouseout="this.style.background=''">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #bbf7d0;">
                            <svg class="w-4 h-4" style="color:#16a34a" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm" style="color:#9ca3af;">Browse Marketplace</p>
                            <p class="text-xs" style="color:var(--text-3);">Find plugins & applications</p>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- AI Activity --}}
            <div class="rounded-2xl overflow-hidden"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <div class="flex items-center space-x-2.5 px-5 py-4" style="border-bottom:1px solid var(--border);">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                         style="background:#6366f1;">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold" style="color:var(--text-1);">AI Activity</h3>
                </div>

                @if($recentActivity->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 px-5 text-center">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
                         style="background:#eef2ff; border:1px solid #e0e7ff;">
                        <svg class="w-5 h-5" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-medium" style="color:var(--text-3);">Start building to see AI activity</p>
                </div>
                @else
                <div>
                    @foreach($recentActivity as $conv)
                    <div class="flex items-start px-4 py-3 transition-colors"
                         style="border-bottom:1px solid var(--border);"
                         onmouseover="this.style.background='var(--hover-bg)'"
                         onmouseout="this.style.background=''">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 mr-3"
                             style="background:#eef2ff; border:1px solid #e0e7ff;">
                            <svg class="w-3.5 h-3.5" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium truncate" style="color:var(--text-1);">
                                {{ Str::limit($conv->title ?? 'AI Conversation', 32) }}
                            </p>
                            <div class="flex items-center space-x-1.5 mt-0.5">
                                <span class="text-[10px] font-medium" style="color:#6366f1;">{{ $conv->project->name ?? '—' }}</span>
                                <span style="color:var(--border)">·</span>
                                <span class="text-[10px]" style="color:var(--text-3);">{{ $conv->message_count }} msgs</span>
                                <span style="color:var(--border)">·</span>
                                <span class="text-[10px]" style="color:var(--text-3);">{{ $conv->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
