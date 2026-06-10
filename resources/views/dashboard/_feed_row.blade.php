<div class="db-feed-row">
    @if($item['kind'] === 'ai')
    <div class="db-feed-dot" style="background:#fdf4ff;border:1px solid #ede9fe;">
        <svg fill="none" viewBox="0 0 24 24" stroke="#8b5cf6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
    </div>
    @elseif(($item['status'] ?? '') === 'success')
    <div class="db-feed-dot" style="background:#f0fdf4;border:1px solid #bbf7d0;">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    @elseif(($item['status'] ?? '') === 'failed')
    <div class="db-feed-dot" style="background:#fef2f2;border:1px solid #fecaca;">
        <svg fill="none" viewBox="0 0 24 24" stroke="#dc2626" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </div>
    @else
    <div class="db-feed-dot" style="background:#fffbeb;border:1px solid #fde68a;">
        <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    @endif
    <div style="flex:1;min-width:0;">
        <div class="db-feed-title">{{ Str::limit($item['title'], 52) }}</div>
        <div class="db-feed-meta">
            @if(!empty($item['proj']))<span style="color:var(--brand);font-weight:600;">{{ $item['proj'] }}</span> &middot; @endif
            {{ $item['sub'] }} &middot; {{ $item['time']->diffForHumans() }}
        </div>
    </div>
</div>
