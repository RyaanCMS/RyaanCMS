@extends('layouts.app')

@section('title', 'Telemetry — Central Hub')

@section('content')
<div class="min-h-screen bg-gray-950 text-white p-6">

    <div class="mb-6">
        <a href="{{ route('admin.hub.index') }}" class="text-indigo-400 text-sm hover:underline">&larr; Hub</a>
        <h1 class="text-2xl font-bold mt-1">Telemetry Insights</h1>
        <p class="text-gray-500 text-sm mt-0.5">Anonymized usage patterns — no PII, no raw prompts</p>
    </div>

    {{-- Period tabs --}}
    <div class="flex gap-3 mb-6">
        <span class="px-3 py-1.5 bg-indigo-700 text-indigo-100 rounded-lg text-sm font-medium">7 Days</span>
        <span class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-lg text-sm">30 Days</span>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @php
        $s = $summary7;
        $cards = [
            ['label' => 'Events (7d)',     'value' => number_format($s['events_today'] * 7), 'color' => 'indigo'],
            ['label' => 'Events Today',    'value' => number_format($s['events_today']),      'color' => 'sky'],
            ['label' => 'Unique Installs', 'value' => number_format($s['unique_domains']),    'color' => 'emerald'],
            ['label' => 'AI Usage Rate',   'value' => $s['ai_usage_rate'] . '%',              'color' => 'yellow'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ $c['label'] }}</p>
            <p class="text-3xl font-bold text-{{ $c['color'] }}-400">{{ $c['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- Top domains 7d --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Top AI Domains (7d)</h2>
            @php $maxD7 = max(array_values($summary7['top_domains']) ?: [1]); @endphp
            @forelse($summary7['top_domains'] as $domain => $cnt)
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm text-gray-300 capitalize w-32 truncate">{{ $domain }}</span>
                <div class="flex-1 bg-gray-800 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full" style="width:{{ round(($cnt/$maxD7)*100) }}%"></div>
                </div>
                <span class="text-xs text-gray-400 w-8 text-right">{{ $cnt }}</span>
            </div>
            @empty
            <p class="text-gray-600 text-sm">No data.</p>
            @endforelse
        </div>

        {{-- Top domains 30d --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Top AI Domains (30d)</h2>
            @php $maxD30 = max(array_values($summary30['top_domains']) ?: [1]); @endphp
            @forelse($summary30['top_domains'] as $domain => $cnt)
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm text-gray-300 capitalize w-32 truncate">{{ $domain }}</span>
                <div class="flex-1 bg-gray-800 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full" style="width:{{ round(($cnt/$maxD30)*100) }}%"></div>
                </div>
                <span class="text-xs text-gray-400 w-8 text-right">{{ $cnt }}</span>
            </div>
            @empty
            <p class="text-gray-600 text-sm">No data.</p>
            @endforelse
        </div>

        {{-- Top entities 30d --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Top Entities (30d)</h2>
            @php $maxE = max(array_values($summary30['top_entities']) ?: [1]); @endphp
            @forelse(array_slice($summary30['top_entities'], 0, 15, true) as $entity => $cnt)
            <div class="flex items-center gap-3 mb-2">
                <span class="text-sm text-gray-300 capitalize w-28 truncate">{{ $entity }}</span>
                <div class="flex-1 bg-gray-800 rounded-full h-1.5">
                    <div class="bg-sky-500 h-1.5 rounded-full" style="width:{{ round(($cnt/$maxE)*100) }}%"></div>
                </div>
                <span class="text-xs text-gray-400 w-6 text-right">{{ $cnt }}</span>
            </div>
            @empty
            <p class="text-gray-600 text-sm">No data.</p>
            @endforelse
        </div>

        {{-- Language + Country distribution --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Languages (30d)</h2>
            @php $maxL = max(array_values($summary30['language_dist']) ?: [1]); @endphp
            @forelse(array_slice($summary30['language_dist'], 0, 8, true) as $lang => $cnt)
            <div class="flex items-center gap-3 mb-2">
                <span class="text-sm text-gray-300 uppercase w-12">{{ $lang }}</span>
                <div class="flex-1 bg-gray-800 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width:{{ round(($cnt/$maxL)*100) }}%"></div>
                </div>
                <span class="text-xs text-gray-400 w-6 text-right">{{ $cnt }}</span>
            </div>
            @empty
            <p class="text-gray-600 text-sm">No language data.</p>
            @endforelse

            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 mt-6">Countries (30d)</h2>
            @php $maxC = max(array_values($summary30['country_dist']) ?: [1]); @endphp
            @forelse(array_slice($summary30['country_dist'], 0, 5, true) as $country => $cnt)
            <div class="flex items-center gap-3 mb-2">
                <span class="text-sm text-gray-300 uppercase w-12">{{ $country }}</span>
                <div class="flex-1 bg-gray-800 rounded-full h-1.5">
                    <div class="bg-orange-500 h-1.5 rounded-full" style="width:{{ round(($cnt/$maxC)*100) }}%"></div>
                </div>
                <span class="text-xs text-gray-400 w-6 text-right">{{ $cnt }}</span>
            </div>
            @empty
            <p class="text-gray-600 text-sm">No country data.</p>
            @endforelse
        </div>

    </div>

</div>
@endsection
