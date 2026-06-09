@extends('layouts.app')
@section('title', 'Marketplace Admin')
@section('header', 'Marketplace Approval Panel')

@push('head')
<style>
.adm-card { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; }
.adm-tab { padding:7px 18px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; transition:all .13s; color:var(--text-3); }
.adm-tab.active { background:#eef2ff; color:#4f46e5; }
.adm-tab:hover:not(.active) { background:var(--hover-bg); color:var(--text-1); }
.badge-pending  { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
.badge-approved { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
.badge-rejected { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.adm-btn-approve { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:5px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; transition:all .13s; }
.adm-btn-approve:hover { background:#d1fae5; }
.adm-btn-approve:disabled { opacity:.5; cursor:not-allowed; }
.adm-btn-reject { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; padding:5px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; transition:all .13s; }
.adm-btn-reject:hover { background:#fee2e2; }
.adm-btn-reject:disabled { opacity:.5; cursor:not-allowed; }
.reject-modal { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:60; display:flex; align-items:center; justify-content:center; padding:16px; }
.reject-box { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:24px; width:100%; max-width:420px; box-shadow:0 20px 40px rgba(0,0,0,.2); }
.form-input { width:100%; padding:9px 13px; border-radius:10px; font-size:13px; background:var(--hover-bg); border:1px solid var(--border); color:var(--text-1); }
.form-input:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="adminPanel()">

    {{-- Header stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="adm-card p-5">
            <p class="text-xs font-semibold mb-1" style="color:var(--text-3);">PENDING REVIEW</p>
            <p class="text-3xl font-black" style="color:#d97706;">{{ $pendingCount }}</p>
        </div>
        <div class="adm-card p-5">
            <p class="text-xs font-semibold mb-1" style="color:var(--text-3);">SHOWING FILTER</p>
            <p class="text-3xl font-black" style="color:#6366f1;">{{ ucfirst($filter) }}</p>
        </div>
        <div class="adm-card p-5">
            <p class="text-xs font-semibold mb-1" style="color:var(--text-3);">TOTAL ON PAGE</p>
            <p class="text-3xl font-black" style="color:var(--text-1);">{{ $items->total() }}</p>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="adm-card p-2 flex gap-1 flex-wrap">
        @foreach(['pending' => 'Pending Review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All Items', 'templates' => '🎨 Built-in Templates'] as $f => $label)
        <a href="{{ route('marketplace.admin.panel', ['filter' => $f]) }}"
           class="adm-tab {{ $filter === $f ? 'active' : '' }}">
            {{ $label }}
            @if($f === 'pending' && $pendingCount > 0)
            <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold text-white"
                  style="background:#f59e0b;">{{ $pendingCount }}</span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- Items table (hidden for templates tab) --}}
    @if($filter !== 'templates' && $items->isEmpty())
    <div class="adm-card py-20 text-center">
        <p class="text-4xl mb-3">✅</p>
        <p class="font-semibold" style="color:var(--text-1);">
            {{ $filter === 'pending' ? 'No items pending review' : 'No items found' }}
        </p>
        <p class="text-sm mt-1" style="color:var(--text-3);">
            {{ $filter === 'pending' ? 'All submissions have been reviewed.' : 'Switch to a different filter.' }}
        </p>
    </div>
    @elseif($filter !== 'templates')
    <div class="adm-card overflow-hidden">
        <div class="px-6 py-4" style="border-bottom:1px solid var(--border);">
            <p class="text-sm font-semibold" style="color:var(--text-1);">
                {{ $items->total() }} submission{{ $items->total() != 1 ? 's' : '' }}
            </p>
        </div>

        <div class="divide-y" style="border-color:var(--border);">
            @foreach($items as $item)
            <div class="px-6 py-5 flex items-start gap-4 transition-colors"
                 x-data="{ rowStatus: '{{ $item->status }}', rowRemoved: false }"
                 x-show="!rowRemoved"
                 onmouseover="this.style.background='var(--hover-bg)'"
                 onmouseout="this.style.background=''">

                {{-- Icon --}}
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0 mt-0.5"
                     style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                    {{ $item->icon ?? '📦' }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-bold text-sm" style="color:var(--text-1);">{{ $item->name }}</p>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold"
                              :class="rowStatus === 'approved' ? 'badge-approved' : (rowStatus === 'rejected' ? 'badge-rejected' : 'badge-pending')"
                              x-text="rowStatus.charAt(0).toUpperCase() + rowStatus.slice(1)"></span>
                    </div>

                    <div class="flex items-center gap-x-3 gap-y-1 flex-wrap mt-1">
                        <span class="text-xs" style="color:var(--text-3);">
                            By <span class="font-semibold" style="color:var(--text-2);">{{ $item->developer?->name ?? 'Unknown' }}</span>
                            &lt;{{ $item->developer?->email ?? '' }}&gt;
                        </span>
                        <span class="text-xs" style="color:var(--text-3);">
                            {{ ucfirst($item->category) }} &middot; {{ ucfirst($item->type) }} &middot; v{{ $item->version }} &middot; {{ $item->price_formatted }}
                        </span>
                        <span class="text-xs" style="color:var(--text-3);">
                            Submitted {{ $item->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs mt-1.5 leading-relaxed" style="color:var(--text-2); max-width:540px;">
                        {{ $item->description }}
                    </p>

                    {{-- Demo URL --}}
                    @if($item->demo_url_submission)
                    <a href="{{ $item->demo_url_submission }}" target="_blank" rel="noopener"
                       class="text-xs mt-1 inline-block font-medium" style="color:#6366f1;">
                        Demo URL →
                    </a>
                    @endif

                    {{-- Rejection reason (if already rejected) --}}
                    @if($item->status === 'rejected' && $item->rejection_reason)
                    <div class="mt-2 px-3 py-1.5 rounded-lg text-xs"
                         style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c;">
                        <span class="font-semibold">Rejection reason:</span> {{ $item->rejection_reason }}
                    </div>
                    @endif

                    {{-- Feedback message --}}
                    <p x-show="feedback" x-text="feedback"
                       :style="feedbackOk ? 'color:#15803d' : 'color:#ef4444'"
                       style="font-size:12px; margin-top:6px; font-weight:600;"></p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0 mt-0.5">
                    @if($item->status !== 'approved')
                    <button class="adm-btn-approve"
                            :disabled="working"
                            x-on:click="approve({{ $item->id }})">
                        ✓ Approve
                    </button>
                    @endif

                    @if($item->status !== 'rejected')
                    <button class="adm-btn-reject"
                            :disabled="working"
                            x-on:click="openReject({{ $item->id }}, '{{ addslashes($item->name) }}')">
                        ✕ Reject
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if(method_exists($items, 'links')){{ $items->links() }}@endif
    @endif

    {{-- ── Built-in Templates section ──────────────────────────────────── --}}
    @if($filter === 'templates')
    <div class="adm-card overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid var(--border);">
            <div>
                <p class="text-sm font-bold" style="color:var(--text-1);">Built-in Template Library</p>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">{{ count($allTemplates) }} templates ship with RyaanCMS. These cannot be approved or rejected — they are always available to all users.</p>
            </div>
            <a href="{{ route('marketplace.templates') }}"
               class="text-xs font-semibold px-4 py-2 rounded-xl"
               style="background:#eef2ff; color:#4f46e5; border:1px solid #c7d2fe;">
               Browse Templates →
            </a>
        </div>

        <div class="divide-y" style="border-color:var(--border);">
            @foreach($allTemplates as $key => $tpl)
            @php
                $stat = $usage->get($key);
                $installs = $stat->install_count ?? 0;
                $actives  = $stat->active_count  ?? 0;
                $slug     = str_replace('template.', '', $key);
                $colors = [
                    'template.restaurant' => 'linear-gradient(135deg,#1a0a00,#5c2a00)',
                    'template.ecommerce'  => 'linear-gradient(135deg,#1a0010,#3d001a)',
                    'template.portfolio'  => 'linear-gradient(135deg,#0b0c10,#1a1d2e)',
                    'template.saas'       => 'linear-gradient(135deg,#0f172a,#1e3a5f)',
                    'template.agency'     => 'linear-gradient(135deg,#080808,#1a1a00)',
                ];
                $bg = $colors[$key] ?? 'linear-gradient(135deg,#1e1e2e,#2d2d44)';
            @endphp
            <div class="flex items-center px-6 py-4 gap-4"
                 onmouseover="this.style.background='var(--hover-bg)'"
                 onmouseout="this.style.background=''">

                {{-- Preview icon --}}
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
                     style="background:{{ $bg }};">
                    {{ $tpl['icon'] }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-bold text-sm" style="color:var(--text-1);">{{ $tpl['name'] }}</p>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold"
                              style="background:#eef2ff; color:#4f46e5; border:1px solid #c7d2fe;">Built-in</span>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold"
                              style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">Always Available</span>
                    </div>
                    <p class="text-xs mt-1" style="color:var(--text-2);">{{ $tpl['category'] }} · {{ $tpl['description'] }}</p>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        @foreach($tpl['tags'] as $tag)
                        <span class="text-[10px] px-1.5 py-0.5 rounded"
                              style="background:var(--hover-bg); color:var(--text-3);">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Stats --}}
                <div class="flex items-center gap-5 flex-shrink-0 text-center">
                    <div>
                        <p class="text-xl font-black" style="color:var(--text-1);">{{ $installs }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-3);">Installs</p>
                    </div>
                    <div>
                        <p class="text-xl font-black" style="color:#065f46;">{{ $actives }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-3);">Active</p>
                    </div>
                    <a href="{{ route('marketplace.template.download', $key) }}"
                       class="flex items-center gap-1 text-xs font-semibold px-3 py-2 rounded-xl"
                       style="background:var(--hover-bg); color:var(--text-2); border:1px solid var(--border);">
                        ⬇ Download ZIP
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Reject modal --}}
    <div class="reject-modal" x-show="rejectOpen" x-cloak
         x-on:keydown.escape.window="rejectOpen = false">
        <div x-on:click.stop class="reject-box">
            <h3 class="font-bold text-base mb-1" style="color:var(--text-1);">Reject Submission</h3>
            <p class="text-xs mb-4" style="color:var(--text-3);">
                Item: <span class="font-semibold" style="color:var(--text-2);" x-text="rejectName"></span>
            </p>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2);">Rejection Reason *</label>
                <textarea x-model="rejectReason" class="form-input resize-none" rows="4"
                          placeholder="Explain why this item was rejected. This will be shown to the submitter."></textarea>
            </div>
            <div class="flex gap-3">
                <button class="adm-btn-reject flex-1 py-2.5"
                        :disabled="working || !rejectReason.trim()"
                        x-on:click="submitReject()">
                    Confirm Rejection
                </button>
                <button x-on:click="rejectOpen = false"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold"
                        style="background:var(--hover-bg); color:var(--text-2);">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function adminPanel() {
    return {
        working:     false,
        feedback:    '',
        feedbackOk:  true,
        rejectOpen:  false,
        rejectId:    null,
        rejectName:  '',
        rejectReason:'',

        openReject(id, name) {
            this.rejectId     = id;
            this.rejectName   = name;
            this.rejectReason = '';
            this.rejectOpen   = true;
        },

        async approve(id) {
            this.working = true;
            try {
                const res  = await fetch(`/marketplace/admin/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    // Update row status in-place
                    const row = document.querySelector(`[data-item-id="${id}"]`);
                    if (row) {
                        const badge = row.querySelector('[data-badge]');
                        if (badge) badge.textContent = 'Approved';
                    }
                    // Reload page to reflect changes
                    setTimeout(() => location.reload(), 600);
                }
            } catch(e) {}
            this.working = false;
        },

        async submitReject() {
            if (!this.rejectReason.trim()) return;
            this.working = true;
            try {
                const res  = await fetch(`/marketplace/admin/${this.rejectId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: this.rejectReason }),
                });
                const data = await res.json();
                if (data.success) {
                    this.rejectOpen = false;
                    setTimeout(() => location.reload(), 400);
                }
            } catch(e) {}
            this.working = false;
        },
    };
}
</script>
@endpush
