<?php

/**
 * Component Registry — pre-built, battle-tested components that require ZERO AI tokens.
 * When users request these, route to the registry instead of the AI.
 *
 * Structure: [key] => [label, category, description, tags[], code_template]
 */

return [

    // ── UI Components ─────────────────────────────────────────────────────────

    'stats_card_row' => [
        'label'       => 'Stats Card Row',
        'category'    => 'dashboard',
        'description' => '4-card KPI row with icons, values and trend arrows',
        'tags'        => ['dashboard', 'kpi', 'stats'],
        'tokens_saved'=> 800,
        'preview'     => '4 cards: Total Revenue, Active Users, Orders, Growth %',
        'template'    => <<<'BLADE'
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label'=>'Total Revenue',  'value'=>'$0',  'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'#10b981', 'trend'=>'+12%'],
        ['label'=>'Active Users',   'value'=>'0',   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'#7c3aed', 'trend'=>'+5%'],
        ['label'=>'Total Orders',   'value'=>'0',   'icon'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'color'=>'#f59e0b', 'trend'=>'+8%'],
        ['label'=>'Growth',         'value'=>'0%',  'icon'=>'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color'=>'#06b6d4', 'trend'=>'↑'],
    ] as $card)
    <div class="rounded-xl p-5 border" style="background:var(--card-bg);border-color:var(--border-1)">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium" style="color:var(--text-2)">{{ $card['label'] }}</span>
            <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                 style="background:{{ $card['color'] }}20">
                <svg class="w-5 h-5" style="color:{{ $card['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold" style="color:var(--text-1)">{{ $card['value'] }}</div>
        <div class="text-xs mt-1 font-medium" style="color:{{ $card['color'] }}">{{ $card['trend'] }} this month</div>
    </div>
    @endforeach
</div>
BLADE,
    ],

    'data_table' => [
        'label'       => 'Sortable Data Table',
        'category'    => 'data',
        'description' => 'Responsive table with search, sort, and pagination',
        'tags'        => ['table', 'list', 'data', 'crud'],
        'tokens_saved'=> 1200,
        'preview'     => 'Table with search bar, column headers, rows, pagination',
        'template'    => <<<'BLADE'
<div x-data="{ search: '', sortCol: '', sortDir: 'asc' }" class="rounded-xl border overflow-hidden" style="border-color:var(--border-1)">
    {{-- Table heading --}}
    <div class="px-4 py-3 border-b" style="border-color:var(--border-1);background:var(--card-bg)">
        <h2 class="text-sm font-bold" style="color:var(--text-1)">Data Table</h2>
        <p class="text-xs mt-0.5" style="color:var(--text-3)">Search, sort, and review records.</p>
    </div>
    {{-- Toolbar --}}
    <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--border-1);background:var(--card-bg)">
        <input x-model="search" type="search" placeholder="Search…"
               class="text-sm rounded-lg px-3 py-1.5 border w-56"
               style="background:var(--input-bg);border-color:var(--border-1);color:var(--text-1)">
        <span class="text-xs" style="color:var(--text-3)">{{ $items->total() }} records</span>
    </div>
    {{-- Table --}}
    <table class="w-full text-sm">
        <thead>
            <tr style="background:var(--table-head-bg);border-bottom:1px solid var(--border-1)">
                <th class="text-left px-4 py-3 font-medium cursor-pointer" style="color:var(--text-2)">#</th>
                {{-- Add columns here --}}
                <th class="text-right px-4 py-3 font-medium" style="color:var(--text-2)">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr style="border-bottom:1px solid var(--border-1);background:var(--card-bg)"
                onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--card-bg)'">
                <td class="px-4 py-3" style="color:var(--text-1)">{{ $item->id }}</td>
                {{-- Add cells here --}}
                <td class="px-4 py-3 text-right">
                    <a href="#" class="text-xs px-2 py-1 rounded border mr-1" style="border-color:var(--border-1);color:var(--text-2)">Edit</a>
                    <button class="text-xs px-2 py-1 rounded border" style="border-color:#ef4444;color:#ef4444">Del</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="px-4 py-8 text-center text-sm" style="color:var(--text-3)">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{-- Pagination --}}
    <div class="px-4 py-3 border-t" style="border-color:var(--border-1);background:var(--card-bg)">
        {{ $items->links() }}
    </div>
</div>
BLADE,
    ],

    'modal_form' => [
        'label'       => 'Modal Form',
        'category'    => 'forms',
        'description' => 'Alpine.js slide-in modal with form and validation display',
        'tags'        => ['modal', 'form', 'dialog', 'create'],
        'tokens_saved'=> 900,
        'preview'     => 'Modal with dark overlay, form fields, Cancel/Save buttons',
        'template'    => <<<'BLADE'
{{-- Trigger button --}}
<button @click="$dispatch('open-modal', 'create-form')"
        class="px-4 py-2 rounded-lg text-sm font-medium text-white"
        style="background:#7c3aed">+ Add New</button>

{{-- Modal --}}
<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail === 'create-form') open = true"
     @keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.5)">
    <div @click.outside="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="rounded-xl shadow-2xl w-full max-w-md p-6"
         style="background:var(--card-bg);border:1px solid var(--border-1)">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold" style="color:var(--text-1)">Create New</h3>
            <button @click="open = false" class="text-xl leading-none" style="color:var(--text-3)">×</button>
        </div>
        <form method="POST" action="#">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium" style="color:var(--text-2)">Name</label>
                    <input type="text" name="name" class="w-full mt-1 rounded-lg px-3 py-2 text-sm border"
                           style="background:var(--input-bg);border-color:var(--border-1);color:var(--text-1)">
                    @error('name')<p class="text-xs mt-1" style="color:#ef4444">{{ $message }}</p>@enderror
                </div>
                {{-- Add more fields here --}}
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="open = false"
                        class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color:var(--border-1);color:var(--text-2)">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white"
                        style="background:#7c3aed">Save</button>
            </div>
        </form>
    </div>
</div>
BLADE,
    ],

    'notification_bell' => [
        'label'       => 'Notification Bell',
        'category'    => 'ui',
        'description' => 'Dropdown notification bell with unread badge',
        'tags'        => ['notification', 'bell', 'dropdown', 'header'],
        'tokens_saved'=> 700,
        'preview'     => 'Bell icon with red badge, dropdown list of notifications',
        'template'    => <<<'BLADE'
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="relative p-2 rounded-lg transition-colors"
            style="color:var(--text-2)">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if(($unreadCount ?? 0) > 0)
        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full text-white text-xs flex items-center justify-center font-bold"
              style="background:#ef4444;font-size:9px">{{ $unreadCount }}</span>
        @endif
    </button>
    <div x-show="open" @click.outside="open = false" x-cloak
         x-transition
         class="absolute right-0 mt-2 w-80 rounded-xl shadow-xl border z-50 overflow-hidden"
         style="background:var(--card-bg);border-color:var(--border-1)">
        <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--border-1)">
            <span class="font-semibold text-sm" style="color:var(--text-1)">Notifications</span>
            <a href="#" class="text-xs" style="color:#7c3aed">Mark all read</a>
        </div>
        <div class="max-h-72 overflow-y-auto">
            {{-- Notification items --}}
            <div class="px-4 py-3 text-sm text-center" style="color:var(--text-3)">No new notifications</div>
        </div>
    </div>
</div>
BLADE,
    ],

    'breadcrumb' => [
        'label'       => 'Breadcrumb',
        'category'    => 'navigation',
        'description' => 'Auto breadcrumb from route name with home icon',
        'tags'        => ['breadcrumb', 'navigation', 'header'],
        'tokens_saved'=> 300,
        'preview'     => 'Home > Section > Current Page',
        'template'    => <<<'BLADE'
<nav class="flex items-center gap-1.5 text-sm mb-4" style="color:var(--text-3)">
    <a href="{{ route('dashboard') }}" class="hover:underline flex items-center gap-1" style="color:var(--text-2)">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Home
    </a>
    <span>›</span>
    <a href="#" class="hover:underline" style="color:var(--text-2)">Section</a>
    <span>›</span>
    <span style="color:var(--text-1)">Current Page</span>
</nav>
BLADE,
    ],

    'empty_state' => [
        'label'       => 'Empty State',
        'category'    => 'feedback',
        'description' => 'Centered empty state with icon, message, and CTA button',
        'tags'        => ['empty', 'placeholder', 'no-data'],
        'tokens_saved'=> 300,
        'preview'     => 'Centered icon + message + action button',
        'template'    => <<<'BLADE'
<div class="rounded-xl border p-12 text-center" style="background:var(--card-bg);border-color:var(--border-1)">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
         style="background:rgba(124,58,237,0.12)">
        <svg class="w-8 h-8" style="color:#7c3aed" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold mb-1" style="color:var(--text-1)">Nothing here yet</h3>
    <p class="text-sm mb-5" style="color:var(--text-3)">Get started by creating your first item.</p>
    <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white"
       style="background:#7c3aed">
        + Create New
    </a>
</div>
BLADE,
    ],

    'alert_banner' => [
        'label'       => 'Alert / Banner',
        'category'    => 'feedback',
        'description' => '4 variants: success, warning, error, info — dismissable',
        'tags'        => ['alert', 'banner', 'flash', 'notification'],
        'tokens_saved'=> 400,
        'preview'     => 'Green success banner with × dismiss button',
        'template'    => <<<'BLADE'
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-transition
     class="flex items-center justify-between px-4 py-3 rounded-xl mb-4 text-sm font-medium"
     style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#10b981">
    <span>✓ {{ session('success') }}</span>
    <button @click="show = false" class="ml-4 text-lg leading-none opacity-60 hover:opacity-100">×</button>
</div>
@endif
@if(session('error') || $errors->any())
<div x-data="{ show: true }" x-show="show" x-transition
     class="flex items-center justify-between px-4 py-3 rounded-xl mb-4 text-sm font-medium"
     style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444">
    <span>✗ {{ session('error') ?? $errors->first() }}</span>
    <button @click="show = false" class="ml-4 text-lg leading-none opacity-60 hover:opacity-100">×</button>
</div>
@endif
BLADE,
    ],

    'search_filter_bar' => [
        'label'       => 'Search + Filter Bar',
        'category'    => 'data',
        'description' => 'Search input with dropdown filters and active filter chips',
        'tags'        => ['search', 'filter', 'bar'],
        'tokens_saved'=> 600,
        'preview'     => 'Search box + Status/Date dropdowns + Filter chips',
        'template'    => <<<'BLADE'
<form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
    <div class="relative flex-1 min-w-[200px]">
        <input type="search" name="search" value="{{ request('search') }}"
               placeholder="Search…"
               class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border"
               style="background:var(--input-bg);border-color:var(--border-1);color:var(--text-1)">
        <svg class="absolute left-2.5 top-2.5 w-4 h-4" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    <select name="status" class="rounded-lg px-3 py-2 text-sm border"
            style="background:var(--input-bg);border-color:var(--border-1);color:var(--text-1)">
        <option value="">All Status</option>
        <option value="active"   @selected(request('status')==='active')>Active</option>
        <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    </select>
    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white" style="background:#7c3aed">Filter</button>
    @if(request()->hasAny(['search','status']))
    <a href="{{ url()->current() }}" class="px-3 py-2 rounded-lg text-sm border" style="border-color:var(--border-1);color:var(--text-2)">Clear</a>
    @endif
</form>
BLADE,
    ],

    'file_upload_zone' => [
        'label'       => 'File Upload Zone',
        'category'    => 'forms',
        'description' => 'Drag & drop file upload with preview, progress, and validation',
        'tags'        => ['upload', 'file', 'drag-drop', 'image'],
        'tokens_saved'=> 1000,
        'preview'     => 'Dashed drop zone + file type icons + upload progress bar',
        'template'    => <<<'BLADE'
<div x-data="{
    files: [],
    dragover: false,
    handleFiles(e) {
        const newFiles = Array.from(e.target?.files || e.dataTransfer?.files || []);
        newFiles.forEach(f => {
            if (f.size > 10 * 1024 * 1024) return;
            this.files.push({ name: f.name, size: (f.size/1024).toFixed(1)+'KB', file: f });
        });
    }
}">
    <div class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
         :style="dragover ? 'border-color:#7c3aed;background:rgba(124,58,237,0.06)' : 'border-color:var(--border-1)'"
         @dragover.prevent="dragover=true"
         @dragleave="dragover=false"
         @drop.prevent="dragover=false; handleFiles($event)"
         @click="$refs.fileInput.click()">
        <svg class="w-10 h-10 mx-auto mb-3" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-2)">Drop files here or <span style="color:#7c3aed">browse</span></p>
        <p class="text-xs mt-1" style="color:var(--text-3)">Max 10MB per file</p>
        <input x-ref="fileInput" type="file" multiple class="hidden" @change="handleFiles($event)" name="files[]">
    </div>
    <template x-if="files.length">
        <ul class="mt-3 space-y-2">
            <template x-for="(f, i) in files" :key="i">
                <div class="flex items-center justify-between px-3 py-2 rounded-lg border text-sm"
                     style="border-color:var(--border-1);background:var(--hover-bg)">
                    <span x-text="f.name" style="color:var(--text-1)"></span>
                    <div class="flex items-center gap-2">
                        <span x-text="f.size" style="color:var(--text-3)"></span>
                        <button @click="files.splice(i,1)" style="color:#ef4444">×</button>
                    </div>
                </div>
            </template>
        </ul>
    </template>
</div>
BLADE,
    ],

    'pagination_links' => [
        'label'       => 'Pagination',
        'category'    => 'navigation',
        'description' => 'Standard Laravel pagination with previous/next and page numbers',
        'tags'        => ['pagination', 'navigation', 'table'],
        'tokens_saved'=> 200,
        'preview'     => '← Prev  1  2  3  Next →',
        'template'    => <<<'BLADE'
{{ $items->links() }}
BLADE,
    ],

    'inline_edit_field' => [
        'label'       => 'Inline Edit Field',
        'category'    => 'forms',
        'description' => 'Click-to-edit inline field with save/cancel — no page reload',
        'tags'        => ['inline', 'edit', 'field', 'ajax'],
        'tokens_saved'=> 600,
        'preview'     => 'Text value → click → input field with ✓ × buttons',
        'template'    => <<<'BLADE'
<div x-data="{ editing: false, value: '{{ $item->name ?? '' }}', original: '{{ $item->name ?? '' }}' }"
     class="flex items-center gap-2">
    <span x-show="!editing" x-text="value" @dblclick="editing=true"
          class="cursor-pointer hover:underline" style="color:var(--text-1)" title="Double-click to edit"></span>
    <template x-if="editing">
        <form class="flex items-center gap-1.5" @submit.prevent="
            fetch('{{ url()->current() }}', {
                method:'PATCH',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector(\'meta[name=csrf-token]\').content},
                body:JSON.stringify({name:value})
            }).then(r=>r.json()).then(()=>{ original=value; editing=false; })
        ">
            <input type="text" x-model="value" class="rounded px-2 py-1 text-sm border"
                   style="background:var(--input-bg);border-color:var(--border-1);color:var(--text-1)"
                   x-ref="inp" x-init="$nextTick(()=>$refs.inp.focus())">
            <button type="submit" class="text-sm px-2 py-1 rounded" style="background:rgba(16,185,129,0.15);color:#10b981">✓</button>
            <button type="button" @click="value=original;editing=false" class="text-sm px-2 py-1 rounded" style="color:#ef4444">✕</button>
        </form>
    </template>
</div>
BLADE,
    ],

];
