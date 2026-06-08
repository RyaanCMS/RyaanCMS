@extends('layouts.app')
@section('title', 'My Marketplace Items')
@section('header', 'My Marketplace Items')

@section('content')
<div class="max-w-4xl mx-auto">
    @if($items->isEmpty())
    <div class="text-center py-16">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="text-xl font-semibold text-white mb-2">No items yet</h3>
        <p class="text-gray-500 mb-6">Publish plugins, themes, or modules to the marketplace</p>
        <button onclick="document.getElementById('submitModal').classList.remove('hidden')"
                class="btn-primary">Submit Your First Item</button>
    </div>
    @else
    <div class="space-y-3">
        @foreach($items as $item)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-xl">📦</div>
                <div>
                    <h4 class="font-semibold text-white">{{ $item->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $item->category }} · v{{ $item->version }} · {{ number_format($item->downloads) }} downloads</p>
                </div>
            </div>
            <span class="badge {{ $item->is_published ? 'badge-success' : 'badge-warning' }}">
                {{ $item->is_published ? 'Published' : 'Pending Review' }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

    <div class="mt-6">
        <button onclick="document.getElementById('submitModal').classList.remove('hidden')"
                class="btn-primary">+ Submit New Item</button>
    </div>
</div>

<!-- Submit Modal -->
<div id="submitModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 max-w-lg w-full max-h-screen overflow-y-auto">
        <h3 class="text-lg font-semibold text-white mb-4">Submit Marketplace Item</h3>
        <form method="POST" action="{{ route('marketplace.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Item Name</label>
                <input type="text" name="name" class="input-field" required placeholder="My Awesome Plugin">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
                    <select name="category" class="input-field" required>
                        @foreach(config('ryaan.marketplace_categories') as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Type</label>
                    <select name="type" class="input-field" required>
                        <option value="plugin">Plugin</option>
                        <option value="theme">Theme</option>
                        <option value="module">Module</option>
                        <option value="template">Template</option>
                        <option value="agent">AI Agent</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                <textarea name="description" class="input-field resize-none" rows="3" required placeholder="Brief description..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Version</label>
                    <input type="text" name="version" class="input-field" value="1.0.0" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Price ($)</label>
                    <input type="number" name="price" class="input-field" value="0" min="0" step="0.01" placeholder="0 = Free">
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 btn-primary">Submit for Review</button>
                <button type="button" onclick="document.getElementById('submitModal').classList.add('hidden')" class="flex-1 btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
