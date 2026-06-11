@extends('layouts.app')

@section('title', 'Installs — Central Hub')

@section('content')
<div class="min-h-screen bg-gray-950 text-white p-6">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.hub.index') }}" class="text-indigo-400 text-sm hover:underline">&larr; Hub</a>
            <h1 class="text-2xl font-bold mt-1">Active Installs</h1>
        </div>
        <a href="{{ route('admin.hub.installs', ['stale' => 1]) }}"
            class="px-4 py-2 text-sm bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Show Stale (&gt; 30d)
        </a>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-800/50">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Domain</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Version</th>
                    <th class="px-4 py-3">PHP</th>
                    <th class="px-4 py-3">Users</th>
                    <th class="px-4 py-3">Projects</th>
                    <th class="px-4 py-3">Prompts (Total)</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Last Ping</th>
                    <th class="px-4 py-3">First Seen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($installs as $install)
                @php $stale = $install->isStale(); @endphp
                <tr class="hover:bg-gray-800/30 transition {{ $stale ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 font-mono text-xs text-gray-300">{{ $install->domain }}</td>
                    <td class="px-4 py-3">
                        @php $plan = $install->license->plan ?? 'free'; @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $plan === 'enterprise' ? 'bg-yellow-900/50 text-yellow-300' :
                               ($plan === 'pro' ? 'bg-indigo-900/50 text-indigo-300' : 'bg-gray-800 text-gray-400') }}">
                            {{ $plan }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">v{{ $install->app_version ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $install->php_version ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-300">{{ $install->user_count }}</td>
                    <td class="px-4 py-3 text-gray-300">{{ $install->project_count }}</td>
                    <td class="px-4 py-3 text-gray-300">{{ number_format($install->prompts_total) }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs font-mono">{{ $install->last_ip ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs {{ $stale ? 'text-rose-400' : 'text-gray-400' }}">
                        {{ $install->last_ping ? $install->last_ping->diffForHumans() : '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $install->first_seen ? $install->first_seen->format('Y-m-d') : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-4 py-10 text-center text-gray-600">No installs found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-800">{{ $installs->withQueryString()->links() }}</div>
    </div>

</div>
@endsection
