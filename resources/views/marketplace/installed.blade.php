@extends('layouts.app')
@section('title', 'Installed Packages')
@section('header', 'Installed Packages')

@section('header-actions')
<a href="{{ route('marketplace.upload-install') }}"
   class="flex items-center space-x-2 text-sm px-4 py-2 rounded-xl font-semibold transition-all hover:-translate-y-px"
   style="background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);"
   onmouseover="this.style.background='var(--brand)';this.style.color='#fff'"
   onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)'">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
    </svg>
    <span>Install Package</span>
</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-5 py-3.5 rounded-xl text-sm font-medium"
         style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;">
        {{ session('success') }}
    </div>
    @endif

    @if($installed->isEmpty())
    <div class="rounded-2xl flex flex-col items-center justify-center py-24 text-center"
         style="background:var(--card-bg); border:1px solid var(--border);">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
             style="background:linear-gradient(135deg,#eef2ff,#ede9fe);">
            <svg class="w-8 h-8" style="color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <h3 class="font-bold mb-2" style="color:var(--text-1);">No packages installed</h3>
        <p class="text-sm mb-6" style="color:var(--text-3);">Browse the marketplace or upload a downloaded package.</p>
        <div class="flex space-x-3">
            <a href="{{ route('marketplace.index') }}"
               class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors"
               style="background:var(--hover-bg); color:var(--text-2);">Browse Marketplace</a>
            <a href="{{ route('marketplace.upload-install') }}"
               class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
               style="background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);"
               onmouseover="this.style.background='var(--brand)';this.style.color='#fff'"
               onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)'">
            Upload Package
        </a>
        </div>
    </div>
    @else

    <div class="rounded-2xl overflow-hidden"
         style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid var(--border);">
            <p class="text-sm font-semibold" style="color:var(--text-1);">
                {{ $installed->total() }} package{{ $installed->total() != 1 ? 's' : '' }} installed
            </p>
        </div>

        <div class="divide-y" style="border-color:var(--border);">
            @foreach($installed as $inst)
            <div class="flex items-center px-6 py-4 gap-4 group transition-colors"
                 x-data="instRow('{{ $inst->id }}', '{{ $inst->status }}')"
                 onmouseover="this.style.background='var(--hover-bg)'"
                 onmouseout="this.style.background=''">

                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                     style="background:color-mix(in srgb,var(--brand) 10%,#fff);border:1px solid color-mix(in srgb,var(--brand) 20%,transparent);">
                    {{ $inst->item?->icon ?? '🔌' }}
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold truncate" style="color:var(--text-1);">
                        {{ $inst->item?->name ?? 'Local Package' }}
                    </p>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                        <span class="text-xs" style="color:var(--text-3);">
                            Project: <span style="color:var(--text-2); font-weight:500;">{{ $inst->project?->name ?? '—' }}</span>
                        </span>
                        <span class="text-xs font-mono" style="color:var(--text-3);">v{{ $inst->version }}</span>
                        @if($inst->domain)
                        <span class="text-[10px] px-1.5 py-0.5 rounded font-mono"
                              style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                            {{ $inst->domain }}
                        </span>
                        @endif
                        @if($inst->activated_at)
                        <span class="text-[10px]" style="color:var(--text-3);">
                            Activated {{ $inst->activated_at->diffForHumans() }}
                        </span>
                        @endif
                    </div>

                    {{-- Feedback message --}}
                    <p x-show="feedback" x-text="feedback"
                       :style="feedbackOk ? 'color:#15803d;' : 'color:#ef4444;'"
                       style="font-size:11.5px;margin-top:4px;font-weight:500;"></p>

                    {{-- License key row --}}
                    <div class="flex items-center space-x-2 mt-1.5">
                        <svg class="w-3 h-3 flex-shrink-0" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span class="text-[10px] font-mono truncate max-w-xs" style="color:var(--text-3);">
                            {{ $inst->license_key ?? 'No license key' }}
                        </span>
                        @if($inst->license_key)
                        <button onclick="navigator.clipboard.writeText('{{ $inst->license_key }}').then(()=>this.textContent='Copied!')"
                                class="text-[10px] px-2 py-0.5 rounded transition-colors opacity-0 group-hover:opacity-100"
                                style="background:var(--hover-bg); color:var(--brand);">Copy</button>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Status badge --}}
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full border"
                          :style="status === 'active'
                              ? 'background:#ecfdf5;color:#065f46;border-color:#a7f3d0'
                              : (status === 'installed'
                                  ? 'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe'
                                  : 'background:#f9fafb;color:#6b7280;border-color:#e5e7eb')"
                          x-text="status.charAt(0).toUpperCase() + status.slice(1)"></span>

                    {{-- Toggle button --}}
                    <button @click="doToggle()" :disabled="working"
                            :style="(status === 'active' || status === 'installed')
                                ? 'background:#fef9c3;color:#a16207;border:1px solid #fde68a;'
                                : 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;'"
                            style="padding:5px 13px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .13s;"
                            x-text="(status === 'active' || status === 'installed') ? 'Deactivate' : 'Activate'">
                    </button>

                    {{-- Uninstall button --}}
                    <button @click="doUninstall()" :disabled="working"
                            style="padding:5px 12px;border-radius:8px;font-size:11.5px;font-weight:700;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;cursor:pointer;transition:all .13s;"
                            onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                        Uninstall
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{ $installed->links() }}
    @endif
</div>

<script>
function instRow(id, initialStatus) {
    return {
        id: id,
        status: initialStatus,
        working: false,
        feedback: '',
        feedbackOk: true,

        async doToggle() {
            this.working = true;
            try {
                const res = await fetch(`/marketplace/installations/${this.id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    this.status      = data.status;
                    this.feedback    = data.message;
                    this.feedbackOk  = true;
                } else {
                    this.feedback   = data.message || 'Toggle failed.';
                    this.feedbackOk = false;
                }
            } catch {
                this.feedback   = 'Network error — please try again.';
                this.feedbackOk = false;
            }
            this.working = false;
        },

        async doUninstall() {
            if (!confirm('Uninstall this package? This cannot be undone.')) return;
            this.working = true;
            try {
                const res = await fetch(`/marketplace/installations/${this.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    this.$el.style.opacity = '0';
                    this.$el.style.transition = 'opacity .3s';
                    setTimeout(() => this.$el.remove(), 320);
                } else {
                    this.feedback   = data.message || 'Uninstall failed.';
                    this.feedbackOk = false;
                    this.working    = false;
                }
            } catch {
                this.feedback   = 'Network error — please try again.';
                this.feedbackOk = false;
                this.working    = false;
            }
        },
    };
}
</script>
@endsection
