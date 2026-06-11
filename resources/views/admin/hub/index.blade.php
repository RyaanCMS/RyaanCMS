@extends('layouts.app')

@section('title', 'Central Hub — RyaanCMS Registry')

@section('content')
<div class="min-h-screen bg-gray-950 text-white p-6">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Central Hub</h1>
            <p class="text-gray-400 mt-1">Manage all RyaanCMS licenses, installs, credits &amp; telemetry</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.hub.licenses') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-sm font-medium transition">Licenses</a>
            <a href="{{ route('admin.hub.installs') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium transition">Installs</a>
            <a href="{{ route('admin.hub.telemetry') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium transition">Telemetry</a>
        </div>
    </div>

    {{-- Revenue KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">MRR</p>
            <p class="text-3xl font-bold text-emerald-400">{{ $revenue['mrr'] }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">ARR</p>
            <p class="text-3xl font-bold text-sky-400">{{ $revenue['arr'] }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Active Trials</p>
            <p class="text-3xl font-bold text-yellow-400">{{ $revenue['active_trials'] }}</p>
            <p class="text-xs text-gray-600 mt-1">+{{ $revenue['trials_today'] }} today</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Paid Customers</p>
            <p class="text-3xl font-bold text-indigo-400">{{ $revenue['active_paid'] }}</p>
            <p class="text-xs text-gray-600 mt-1">+{{ $revenue['new_this_month'] }} this month</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @php
        $cards = [
            ['label' => 'Total Licenses',   'value' => $stats['total'],          'color' => 'indigo'],
            ['label' => 'Active Licenses',  'value' => $stats['active'],         'color' => 'emerald'],
            ['label' => 'Active Installs',  'value' => $stats['active_installs'],'color' => 'sky'],
            ['label' => 'Suspended',        'value' => $stats['suspended'],      'color' => 'rose'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ $c['label'] }}</p>
            <p class="text-4xl font-bold text-{{ $c['color'] }}-400">{{ number_format($c['value']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Credit Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Credits Issued (All)</p>
            <p class="text-3xl font-bold text-yellow-400">{{ number_format($creditStats['total_included']) }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Credits Used (All)</p>
            <p class="text-3xl font-bold text-orange-400">{{ number_format($creditStats['total_used']) }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Usage Today</p>
            <p class="text-3xl font-bold text-pink-400">{{ number_format($creditStats['used_today']) }}</p>
        </div>
    </div>

    {{-- Plans + Telemetry row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        {{-- Plans breakdown --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Licenses by Plan</h2>
            @foreach(['enterprise','pro','starter','free'] as $plan)
            @php $cnt = $stats['by_plan'][$plan] ?? 0; @endphp
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $plan === 'enterprise' ? 'bg-yellow-400' : ($plan === 'pro' ? 'bg-indigo-400' : ($plan === 'starter' ? 'bg-sky-400' : 'bg-gray-500')) }}"></span>
                    <span class="capitalize text-sm text-gray-300">{{ $plan }}</span>
                </div>
                <span class="text-sm font-semibold text-white">{{ $cnt }}</span>
            </div>
            @endforeach
        </div>

        {{-- Telemetry top domains --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Top AI Domains (30d)</h2>
            @forelse(array_slice($telSummary['top_domains'], 0, 8, true) as $domain => $cnt)
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-300 capitalize">{{ $domain }}</span>
                <div class="flex items-center gap-2">
                    <div class="w-24 bg-gray-800 rounded-full h-1.5">
                        @php $max = max(array_values($telSummary['top_domains']) ?: [1]); @endphp
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width:{{ round(($cnt/$max)*100) }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8 text-right">{{ $cnt }}</span>
                </div>
            </div>
            @empty
            <p class="text-gray-600 text-sm">No telemetry yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent pings --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-300">Recent Install Pings</h2>
            <a href="{{ route('admin.hub.installs') }}" class="text-xs text-indigo-400 hover:text-indigo-300">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-800/50">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Domain</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Version</th>
                    <th class="px-4 py-3">Users</th>
                    <th class="px-4 py-3">Last Ping</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($recentPings as $install)
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3 text-gray-300 font-mono text-xs">{{ $install->domain }}</td>
                    <td class="px-4 py-3">
                        @php $plan = $install->license->plan ?? 'free'; @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $plan === 'enterprise' ? 'bg-yellow-900/50 text-yellow-300' : ($plan === 'pro' ? 'bg-indigo-900/50 text-indigo-300' : 'bg-gray-800 text-gray-400') }}">{{ $plan }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">v{{ $install->app_version ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $install->user_count }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $install->last_ping ? $install->last_ping->diffForHumans() : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-600">No installs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
