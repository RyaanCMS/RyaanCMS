@extends('layouts.app')
@section('title', 'My Marketplace Items')
@section('header', 'My Marketplace Items')

@section('header-actions')
<button x-data x-on:click="$dispatch('open-submit-modal')"
        class="flex items-center space-x-2 text-sm px-4 py-2 rounded-xl font-semibold text-white transition-all hover:-translate-y-px"
        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 12px rgba(99,102,241,.3);">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>Submit Item</span>
</button>
@endsection

@push('head')
<style>
.mi-card { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; transition:border-color .15s; }
.mi-card:hover { border-color:#c7d2fe; }
.badge-pending  { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
.badge-approved { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
.badge-rejected { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.form-label { font-size:13px; font-weight:600; color:var(--text-2); margin-bottom:6px; display:block; }
.form-input {
    width:100%; padding:9px 13px; border-radius:10px; font-size:13px;
    background:var(--hover-bg); border:1px solid var(--border); color:var(--text-1);
    transition:border-color .15s;
}
.form-input:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-5" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">

    @if(session('success'))
    <div class="px-5 py-3.5 rounded-xl text-sm font-medium"
         style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46;">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="px-5 py-3.5 rounded-xl text-sm font-medium"
         style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c;">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
    </div>
    @endif

    {{-- Items list --}}
    @if($items->isEmpty())
    <div class="mi-card flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 text-3xl"
             style="background:linear-gradient(135deg,#eef2ff,#ede9fe);">📦</div>
        <h3 class="font-bold text-lg mb-2" style="color:var(--text-1);">No items submitted yet</h3>
        <p class="text-sm mb-6 max-w-sm" style="color:var(--text-3);">
            Build something great and share it with the RyaanCMS community.
            Approved items appear in the marketplace for all users.
        </p>
        <button x-on:click="open = true"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                style="background:#6366f1;">Submit Your First Item</button>
    </div>
    @else

    <div class="space-y-3">
        @foreach($items as $item)
        <div class="mi-card p-5 flex items-center gap-4">

            {{-- Icon --}}
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
                 style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                {{ $item->icon ?? '📦' }}
            </div>

            {{-- Details --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-bold text-sm" style="color:var(--text-1);">{{ $item->name }}</p>
                    <span class="text-[10px] px-2.5 py-0.5 rounded-full font-semibold
                        {{ $item->status === 'approved' ? 'badge-approved' : ($item->status === 'rejected' ? 'badge-rejected' : 'badge-pending') }}">
                        {{ $item->status_label }}
                    </span>
                </div>
                <p class="text-xs mt-0.5" style="color:var(--text-3);">
                    {{ ucfirst($item->category) }} &middot; {{ ucfirst($item->type) }}
                    &middot; v{{ $item->version }} &middot; {{ $item->price_formatted }}
                    &middot; {{ number_format($item->downloads) }} downloads
                </p>
                <p class="text-xs mt-1 truncate" style="color:var(--text-3); max-width:480px;">
                    {{ $item->description }}
                </p>

                @if($item->status === 'rejected' && $item->rejection_reason)
                <div class="mt-2 px-3 py-2 rounded-lg text-xs"
                     style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c;">
                    <span class="font-semibold">Rejection reason:</span> {{ $item->rejection_reason }}
                </div>
                @endif

                @if($item->status === 'pending')
                <p class="text-[11px] mt-1.5 font-medium" style="color:#d97706;">
                    Under review — we'll notify you once a decision is made.
                </p>
                @endif
            </div>

            {{-- Date + link --}}
            <div class="flex-shrink-0 text-right">
                <p class="text-[11px]" style="color:var(--text-3);">{{ $item->created_at->format('M j, Y') }}</p>
                @if($item->status === 'approved')
                <a href="{{ route('marketplace.show', $item) }}"
                   class="text-[11px] font-semibold mt-1 inline-block"
                   style="color:#6366f1;">View in Marketplace →</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{ $items->links() }}

    <div>
        <button x-on:click="open = true"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                style="background:#6366f1;">+ Submit New Item</button>
    </div>
    @endif

    {{-- Submit Modal --}}
    <div x-show="open" x-cloak
         x-on:open-submit-modal.window="open = true"
         x-on:keydown.escape.window="open = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.55);">
        <div x-on:click.stop
             class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl"
             style="background:var(--card-bg); border:1px solid var(--border);">

            <div class="px-6 py-5 flex items-center justify-between"
                 style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1);">Submit to Marketplace</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3);">Admin will review before it goes live</p>
                </div>
                <button x-on:click="open = false" class="text-2xl leading-none" style="color:var(--text-3);">&times;</button>
            </div>

            <form method="POST" action="{{ route('marketplace.submit') }}"
                  enctype="multipart/form-data"
                  class="px-6 py-5 space-y-4">
                @csrf

                {{-- Name + Icon --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="form-label">Item Name *</label>
                        <input type="text" name="name" class="form-input" required
                               placeholder="e.g. Multi-Tenant Auth Module"
                               value="{{ old('name') }}">
                    </div>
                    <div>
                        <label class="form-label">Icon (emoji)</label>
                        <input type="text" name="icon" class="form-input text-center text-xl"
                               maxlength="4" placeholder="📦" value="{{ old('icon', '📦') }}">
                    </div>
                </div>

                {{-- Category + Type --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-input" required>
                            @foreach(config('ryaan.marketplace_categories', []) as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-input" required>
                            @foreach(['plugin'=>'Plugin','theme'=>'Theme','module'=>'Module','template'=>'Template','agent'=>'AI Agent','application'=>'Application'] as $k => $v)
                            <option value="{{ $k }}" {{ old('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="form-label">
                        Short Description *
                        <span style="color:var(--text-3); font-weight:400;">(max 500 chars)</span>
                    </label>
                    <textarea name="description" class="form-input resize-none" rows="3" required
                              maxlength="500"
                              placeholder="What does this item do?">{{ old('description') }}</textarea>
                </div>

                {{-- Version + Price --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Version *</label>
                        <input type="text" name="version" class="form-input" required
                               value="{{ old('version', '1.0.0') }}" placeholder="1.0.0">
                    </div>
                    <div>
                        <label class="form-label">
                            Price <span style="color:var(--text-3); font-weight:400;">(0 = Free)</span>
                        </label>
                        <input type="number" name="price" class="form-input" min="0" step="0.01"
                               value="{{ old('price', '0') }}" placeholder="0.00">
                    </div>
                </div>

                {{-- Demo URL --}}
                <div>
                    <label class="form-label">
                        Demo URL <span style="color:var(--text-3); font-weight:400;">(optional)</span>
                    </label>
                    <input type="url" name="demo_url" class="form-input"
                           value="{{ old('demo_url') }}" placeholder="https://demo.yourdomain.com">
                </div>

                {{-- Package ZIP --}}
                <div>
                    <label class="form-label">
                        Package ZIP <span style="color:var(--text-3); font-weight:400;">(optional, max 50 MB)</span>
                    </label>
                    <input type="file" name="package_file" accept=".zip" class="form-input" style="padding:7px 13px;">
                    <p class="text-[11px] mt-1" style="color:var(--text-3);">
                        Should contain a <code style="background:var(--hover-bg);padding:1px 4px;border-radius:4px;">ryaan-manifest.json</code> at the root. Reviewed by admin before publishing.
                    </p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        Submit for Review
                    </button>
                    <button type="button" x-on:click="open = false"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold"
                            style="background:var(--hover-bg); color:var(--text-2);">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
