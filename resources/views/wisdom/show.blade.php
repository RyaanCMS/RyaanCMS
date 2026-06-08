@extends('layouts.app')

@section('title', 'Wisdom Entry')

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('wisdom.index') }}" class="text-sm" style="color:var(--text-3)">← Ledger</a>
        <span style="color:var(--text-3)">/</span>
        <span class="text-sm" style="color:var(--text-1)">Entry</span>
    </div>

    @php
        $typeColor = match($wisdom->entry_type) {
            'lesson'   => '#10b981',
            'decision' => '#7c3aed',
            'autopsy'  => '#ef4444',
            'outcome'  => '#06b6d4',
            default    => '#6b7280',
        };
    @endphp

    <div class="rounded-xl border p-6 space-y-5" style="background:var(--card-bg);border-color:var(--border-1)">

        {{-- Meta row --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs px-2.5 py-1 rounded-full font-semibold"
                  style="background:{{ $typeColor }}20;color:{{ $typeColor }}">
                {{ ucfirst($wisdom->entry_type) }}
            </span>
            <span class="text-xs px-2.5 py-1 rounded-full"
                  style="background:rgba(124,58,237,0.12);color:#7c3aed">
                {{ $wisdom->domain }}
            </span>
            <span class="text-xs px-2.5 py-1 rounded-full"
                  style="background:var(--hover-bg);color:var(--text-2)">
                Confidence: {{ number_format($wisdom->confidence * 100, 0) }}%
            </span>
            <span class="text-xs px-2.5 py-1 rounded-full"
                  style="background:var(--hover-bg);color:var(--text-2)">
                Reused {{ $wisdom->reuse_count }}×
            </span>
            @if($wisdom->is_rule_candidate)
            <span class="text-xs px-2.5 py-1 rounded-full"
                  style="background:rgba(245,158,11,0.15);color:#f59e0b">⭐ Rule candidate</span>
            @endif
        </div>

        {{-- Lesson --}}
        @if($wisdom->lesson)
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--text-3)">Lesson</div>
            <div class="text-base" style="color:var(--text-1)">{{ $wisdom->lesson }}</div>
        </div>
        @endif

        {{-- What went right --}}
        @if($wisdom->what_went_right)
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:#10b981">What Went Right</div>
            <div class="text-sm" style="color:var(--text-1)">{{ $wisdom->what_went_right }}</div>
        </div>
        @endif

        {{-- What went wrong --}}
        @if($wisdom->what_went_wrong)
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:#ef4444">What Went Wrong</div>
            <div class="text-sm" style="color:var(--text-1)">{{ $wisdom->what_went_wrong }}</div>
        </div>
        @endif

        {{-- Recommendation --}}
        @if($wisdom->recommendation)
        <div class="rounded-lg p-4 border-l-4" style="background:var(--hover-bg);border-color:#7c3aed">
            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:#7c3aed">Recommendation</div>
            <div class="text-sm" style="color:var(--text-1)">{{ $wisdom->recommendation }}</div>
        </div>
        @endif

        {{-- Outcome --}}
        @if($wisdom->outcome)
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--text-3)">Outcome</div>
            <div class="text-sm" style="color:var(--text-2)">{{ $wisdom->outcome }}</div>
        </div>
        @endif

        {{-- Tags --}}
        @if(!empty($wisdom->tags))
        <div class="flex flex-wrap gap-1.5 pt-1">
            @foreach($wisdom->tags as $tag)
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--hover-bg);color:var(--text-3)">
                #{{ $tag }}
            </span>
            @endforeach
        </div>
        @endif

        {{-- Economics --}}
        @if($wisdom->ai_cost_estimate || $wisdom->ai_cost_saved)
        <div class="grid grid-cols-2 gap-3 pt-1">
            @if($wisdom->ai_cost_estimate)
            <div class="rounded-lg p-3 border text-center" style="border-color:var(--border-1)">
                <div class="text-lg font-bold" style="color:#f59e0b">${{ number_format($wisdom->ai_cost_estimate, 4) }}</div>
                <div class="text-xs" style="color:var(--text-3)">AI cost estimate</div>
            </div>
            @endif
            @if($wisdom->ai_cost_saved)
            <div class="rounded-lg p-3 border text-center" style="border-color:var(--border-1)">
                <div class="text-lg font-bold" style="color:#10b981">${{ number_format($wisdom->ai_cost_saved, 4) }}</div>
                <div class="text-xs" style="color:var(--text-3)">AI cost saved</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Footer row --}}
        <div class="flex items-center justify-between pt-2 border-t" style="border-color:var(--border-1)">
            <span class="text-xs" style="color:var(--text-3)">Created {{ $wisdom->created_at->diffForHumans() }}</span>
            <div class="flex gap-2">
                @unless($wisdom->is_rule_candidate)
                <form method="POST" action="{{ route('wisdom.promote', $wisdom) }}">
                    @csrf
                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border font-medium"
                            style="border-color:#f59e0b;color:#f59e0b">⭐ Promote to Rule</button>
                </form>
                @endunless
                <form method="POST" action="{{ route('wisdom.destroy', $wisdom) }}"
                      onsubmit="return confirm('Delete this entry permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border font-medium"
                            style="border-color:#ef4444;color:#ef4444">Delete</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
