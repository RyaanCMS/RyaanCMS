@extends('layouts.app')
@section('title', $item->name)
@section('header', 'Marketplace')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-3 gap-6">

        <!-- Main Content -->
        <div class="col-span-2 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <div class="flex items-start space-x-5 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-gray-800 border border-gray-700 flex items-center justify-center text-4xl flex-shrink-0">
                        @switch($item->type)
                            @case('module') 📦 @break
                            @case('theme') 🎨 @break
                            @case('agent') 🤖 @break
                            @default 🔌
                        @endswitch
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">{{ $item->name }}</h1>
                        <p class="text-gray-400">{{ $item->description }}</p>
                        <div class="flex items-center space-x-3 mt-3">
                            <span class="badge badge-info">{{ $item->category }}</span>
                            <span class="badge bg-gray-800 text-gray-400 border border-gray-700">v{{ $item->version }}</span>
                            <span class="text-xs text-gray-600">{{ number_format($item->downloads) }} downloads</span>
                        </div>
                    </div>
                </div>

                @if($item->long_description)
                <div class="prose prose-invert max-w-none text-gray-300 text-sm leading-relaxed">
                    {{ $item->long_description }}
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <div class="text-3xl font-bold text-white mb-1">{{ $item->price_formatted }}</div>
                <p class="text-sm text-gray-500 mb-4">by {{ $item->developer->name }}</p>

                <button onclick="document.getElementById('installModal').classList.remove('hidden')"
                        class="w-full btn-primary text-center mb-3">
                    Install in Project
                </button>

                @if($item->demo_url)
                <a href="{{ $item->demo_url }}" target="_blank" class="w-full btn-secondary text-center block">
                    View Demo
                </a>
                @endif
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <h4 class="text-sm font-semibold text-white mb-3">Details</h4>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="text-gray-300">{{ ucfirst($item->type) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd class="text-gray-300">{{ $item->category }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Version</dt><dd class="text-gray-300">{{ $item->version }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Downloads</dt><dd class="text-gray-300">{{ number_format($item->downloads) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Install Modal -->
<div id="installModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 max-w-md w-full">
        <h3 class="text-lg font-semibold text-white mb-4">Install "{{ $item->name }}"</h3>
        <form method="POST" action="{{ route('marketplace.install', $item) }}">
            @csrf
            <label class="block text-sm font-medium text-gray-300 mb-2">Select Project</label>
            <select name="project_id" class="input-field mb-4" required>
                <option value="">Choose a project...</option>
                @foreach(auth()->user()->projects()->get() as $proj)
                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                @endforeach
            </select>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 btn-primary">Install</button>
                <button type="button" onclick="document.getElementById('installModal').classList.add('hidden')" class="flex-1 btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
