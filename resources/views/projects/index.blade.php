@extends('layouts.app')
@section('title', 'Projects')
@section('header', 'My Projects')

@section('header-actions')
<a href="{{ route('projects.create') }}" class="flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-gray-700">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>New Project</span>
</a>
@endsection

@section('content')

@if($projects->isEmpty())
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/20 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    </div>
    <h2 class="text-2xl font-bold text-white mb-2">No projects yet</h2>
    <p class="text-gray-500 mb-8 max-w-md">Create your first AI-powered application. From eCommerce to SaaS — build anything with a single prompt.</p>
    <a href="{{ route('projects.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-medium transition-all hover:-translate-y-0.5 shadow-lg shadow-indigo-500/20">
        Create First Project
    </a>
</div>

@else

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($projects as $project)
    <div class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-2xl overflow-hidden transition-all group hover:-translate-y-0.5 hover:shadow-xl hover:shadow-black/50">
        <!-- Card Header -->
        <div class="h-28 bg-gradient-to-br from-indigo-900/40 via-gray-800/50 to-purple-900/30 flex items-center justify-center border-b border-gray-800 relative">
            <div class="text-5xl">
                @switch($project->type)
                    @case('laravel') ⚡ @break
                    @case('react') ⚛️ @break
                    @case('nextjs') ▲ @break
                    @case('ecommerce') 🛒 @break
                    @case('crm') 👥 @break
                    @case('saas') 🚀 @break
                    @default 🗂️
                @endswitch
            </div>
            <div class="absolute top-3 right-3">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $project->status === 'active' ? 'bg-green-500/20 text-green-400 border border-green-500/20' : 'bg-gray-700 text-gray-400' }}">
                    {{ ucfirst($project->status) }}
                </span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="p-5">
            <div class="flex items-start justify-between mb-2">
                <h3 class="font-semibold text-white text-base truncate pr-2">{{ $project->name }}</h3>
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-300 p-1 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                    </button>
                    <div x-show="open" x-transition class="absolute right-0 top-7 z-20 w-40 bg-gray-800 border border-gray-700 rounded-xl shadow-xl py-1">
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit</span>
                        </a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full flex items-center space-x-2 px-3 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $project->description ?: 'No description' }}</p>

            <!-- Tech Stack -->
            <div class="flex flex-wrap gap-1.5 mb-4">
                @foreach(array_slice($project->tech_stack ?? [], 0, 3) as $tech)
                <span class="px-2 py-0.5 text-xs bg-gray-800 text-gray-400 rounded-md border border-gray-700">{{ $tech }}</span>
                @endforeach
                @if(count($project->tech_stack ?? []) > 3)
                <span class="px-2 py-0.5 text-xs bg-gray-800 text-gray-500 rounded-md">+{{ count($project->tech_stack) - 3 }}</span>
                @endif
            </div>

            <!-- Stats -->
            <div class="flex items-center justify-between text-xs text-gray-600 mb-4">
                <span>{{ $project->files_count }} files</span>
                <span>{{ $project->storage_used_formatted }}</span>
                <span>{{ $project->updated_at->diffForHumans() }}</span>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
                <a href="{{ route('builder.show', $project) }}"
                   class="flex-1 flex items-center justify-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-xl text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span>Open Builder</span>
                </a>
                @if($project->deployment_url)
                <a href="{{ $project->deployment_url }}" target="_blank"
                   class="flex items-center justify-center w-10 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-xl transition-colors border border-gray-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{ $projects->links() }}
@endif

@endsection
