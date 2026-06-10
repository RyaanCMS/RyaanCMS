@extends('layouts.app')
@section('title', 'Create Menu')
@section('header', 'Create Menu')

@section('content')
<div class="max-w-lg">
    <div class="rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow);">
        <div class="px-6 py-5" style="border-bottom:1px solid var(--border);">
            <h3 class="font-bold text-base" style="color:var(--text-1)">New Navigation Menu</h3>
            <p class="text-sm mt-0.5" style="color:var(--text-3)">Create a menu to manage navigation links for your site.</p>
        </div>
        <form method="POST" action="{{ route('menus.store') }}" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Menu Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                       style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                       placeholder="e.g. Main Navigation, Footer Links"
                       onfocus="this.style.borderColor='var(--brand)'"
                       onblur="this.style.borderColor='var(--border)'"
                       required>
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Category</label>
                <select name="category"
                        class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                        style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                        onfocus="this.style.borderColor='var(--brand)'"
                        onblur="this.style.borderColor='var(--border)'">
                    @foreach($menuCategories as $category)
                    <option value="{{ $category->slug }}" {{ old('category') === $category->slug ? 'selected' : '' }}>{{ $category->display_name }}{{ $category->is_active ? '' : ' (Inactive)' }}</option>
                    @endforeach
                </select>
                @error('category')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('menus.index') }}" class="text-sm font-medium transition-colors" style="color:var(--text-3)">← Cancel</a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                        style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                    Create Menu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
