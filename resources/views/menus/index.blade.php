@extends('layouts.app')
@section('title', 'Menus')
@section('header', 'Menus')

@section('content')
<div x-data="menuTable()" @open-add-menu.window="showAddModal = true">

    <div class="dt-wrap">

        {{-- Header --}}
        <div class="dt-head">
            <div>
                <h2 class="dt-title">Menu Management</h2>
                <p class="dt-subtitle">Create and manage navigation menus, assign items, and control visibility.</p>
            </div>
            <button type="button" @click="showAddModal = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold text-white border-none cursor-pointer flex-shrink-0"
                    style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Menu
            </button>
        </div>

        {{-- Toolbar --}}
        <div class="dt-toolbar" style="flex-direction:column;align-items:stretch;gap:10px;">
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Search --}}
                <div class="dt-search">
                    <svg class="dt-search-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-ref="searchInput" x-model="search" @input="page = 1"
                           @keydown.escape="search = ''; page = 1"
                           type="text" placeholder="Search menus, category…" class="dt-search-input">
                    <button x-show="search" @click="search = ''; page = 1; $refs.searchInput.focus()"
                            class="dt-clear" title="Clear (Esc)" x-cloak>
                        <svg style="width:9px;height:9px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Per-page --}}
                <div class="dt-per-page">
                    <span>Show</span>
                    <select x-model.number="perPage" @change="page = 1">
                        <template x-for="n in perPageOpts" :key="n">
                            <option :value="n" x-text="n"></option>
                        </template>
                    </select>
                </div>

                {{-- Status pills --}}
                <div class="flex rounded-xl overflow-hidden flex-shrink-0" style="border:1px solid var(--border);">
                    <button @click="statusFilter = 'all'; page = 1" class="px-3 py-1.5 text-xs transition-all"
                            :style="statusFilter === 'all' ? 'background:var(--brand);color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">All</button>
                    <button @click="statusFilter = 'active'; page = 1" class="px-3 py-1.5 text-xs transition-all border-l" style="border-color:var(--border);"
                            :style="statusFilter === 'active' ? 'background:#15803d;color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">Active</button>
                    <button @click="statusFilter = 'inactive'; page = 1" class="px-3 py-1.5 text-xs transition-all border-l" style="border-color:var(--border);"
                            :style="statusFilter === 'inactive' ? 'background:#64748b;color:#fff;font-weight:700' : 'background:var(--surface-raised);color:var(--text-2)'">Inactive</button>
                </div>

                {{-- Sort --}}
                <div class="dt-per-page">
                    <span>Sort</span>
                    <select x-model="sortBy" @change="page = 1">
                        <option value="sort_order">Custom Order</option>
                        <option value="name_asc">Name A → Z</option>
                        <option value="name_desc">Name Z → A</option>
                        <option value="category">Category</option>
                    </select>
                </div>

                <span class="dt-count">
                    <span x-text="filtered.length"></span>
                    <span x-text="filtered.length === 1 ? ' menu' : ' menus'"></span>
                </span>
            </div>

            {{-- Category pills --}}
            <div class="flex flex-wrap gap-1.5">
                <template x-for="pill in categoryPills" :key="pill.val">
                    <button @click="categoryFilter = categoryFilter === pill.val ? '' : pill.val; page = 1"
                            class="sys-pill" :class="categoryFilter === pill.val ? 'sys-pill-on' : ''">
                        <span x-text="pill.label"></span>
                        <span class="ml-1 opacity-70" x-text="'(' + categoryCount(pill.val) + ')'"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="dt-table">
                <thead>
                    <tr>
                        <th class="dt-th">#</th>
                        <th class="dt-th">Name</th>
                        <th class="dt-th">Category</th>
                        <th class="dt-th">Status</th>
                        <th class="dt-th" style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length === 0">
                        <tr>
                            <td colspan="5" class="dt-empty">
                                <svg class="w-10 h-10 mb-3 mx-auto" style="color:var(--border)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                <p class="text-sm font-medium" style="color:var(--text-2)">No menus found</p>
                                <p class="text-xs mt-1">Try a different search or filter</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="(menu, i) in paginated" :key="menu.id">
                        <tr class="dt-tr">
                            <td class="dt-td" style="color:var(--text-3)" x-text="(page - 1) * perPage + i + 1"></td>
                            <td class="dt-td">
                                <p class="font-semibold text-sm" style="color:var(--text-1)" x-html="highlight(menu.name)"></p>
                            </td>
                            <td class="dt-td">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :style="catStyle(menu.category)" x-text="catLabel(menu.category)"></span>
                            </td>
                            <td class="dt-td">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :style="menu.is_active
                                          ? 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0'
                                          : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="menu.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                    <span x-text="menu.is_active ? 'Active' : 'Inactive'"></span>
                                </span>
                            </td>
                            <td class="dt-td" style="text-align:right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <div class="flex flex-col gap-0.5">
                                        <button @click="moveMenu(menu, 'up')" :disabled="moving"
                                                class="w-6 h-6 rounded flex items-center justify-center transition-colors"
                                                style="background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border);" title="Move up">
                                            <svg style="width:10px;height:10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        </button>
                                        <button @click="moveMenu(menu, 'down')" :disabled="moving"
                                                class="w-6 h-6 rounded flex items-center justify-center transition-colors"
                                                style="background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border);" title="Move down">
                                            <svg style="width:10px;height:10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <button @click="toggleActive(menu)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            :style="menu.is_active ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border);'"
                                            :title="menu.is_active ? 'Deactivate' : 'Activate'">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="menu.is_active ? 'M5 13l4 4L19 7' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
                                        </svg>
                                    </button>
                                    <button @click="openEdit(menu)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="doDelete(menu)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Footer / Pagination --}}
        <div class="dt-foot">
            <span class="dt-foot-info" x-text="dtInfo(filtered.length, page, perPage)"></span>
            <div class="dt-pages" x-show="totalPages > 1">
                <button class="dt-page-btn" @click="page = Math.max(1, page - 1)" :disabled="page === 1">&lsaquo;</button>
                <template x-for="p in pageRange" :key="p + '-' + page">
                    <template x-if="p === '...'"><span class="dt-page-dot">...</span></template>
                    <template x-if="p !== '...'">
                        <button class="dt-page-btn" :class="p === page ? 'dt-page-on' : ''" @click="page = p" x-text="p"></button>
                    </template>
                </template>
                <button class="dt-page-btn" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">&rsaquo;</button>
            </div>
        </div>

    </div>{{-- /.dt-wrap --}}

    {{-- ── New Menu Modal ───────────────────────────────────────── --}}
    <div x-show="showAddModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showAddModal = false"
         x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
        <div class="w-full rounded-2xl flex flex-col"
             style="max-width:520px;max-height:92vh;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 flex-shrink-0" style="border-bottom:1px solid var(--border);">
                <h3 class="font-bold text-base" style="color:var(--text-1)">New Menu</h3>
                <button @click="showAddModal = false"
                        class="w-7 h-7 rounded-lg flex items-center justify-center border-none cursor-pointer"
                        style="background:none;color:var(--text-3);"
                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('menus.store') }}" class="flex flex-col overflow-hidden"
                  x-data="addMenuForm()" @submit.prevent="$el.submit()">
                @csrf
                <div class="overflow-y-auto px-6 py-5 flex flex-col gap-4" style="flex:1;">

                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Menu Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" placeholder="e.g. Dashboard, My Apps"
                               class="w-full rounded-xl text-sm outline-none box-border"
                               style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                               required autofocus>
                    </div>

                    {{-- Icon picker --}}
                    <div x-data="iconPicker()">
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Icon</label>
                        <input type="hidden" name="icon" x-model="selected">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center gap-3 rounded-xl text-sm cursor-pointer"
                                style="padding:8px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-2);">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background:var(--surface-raised);">
                                <template x-if="selected">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="var(--brand)" style="width:16px;height:16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="selected"/>
                                    </svg>
                                </template>
                                <template x-if="!selected">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" style="width:16px;height:16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>
                            <span x-text="selected ? iconName(selected) : 'Select icon…'" class="flex-1 text-left text-sm"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="mt-2 rounded-xl overflow-hidden" style="border:1.5px solid var(--border);background:var(--surface-base);">
                            <div class="px-3 pt-3">
                                <input x-model="q" placeholder="Search icons…" type="text"
                                       class="w-full rounded-lg text-xs outline-none box-border"
                                       style="padding:7px 10px;border:1px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                       onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                            </div>
                            <div class="p-2 overflow-y-auto" style="max-height:220px;">
                                <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:3px;">
                                    <template x-for="ic in filteredIcons" :key="ic.n">
                                        <button type="button" @click="selected = ic.d; open = false"
                                                :title="ic.n"
                                                class="flex flex-col items-center justify-center rounded-lg cursor-pointer transition-all"
                                                style="padding:6px 2px;border:none;"
                                                :style="selected === ic.d ? 'background:var(--brand-light)' : 'background:transparent'"
                                                onmouseover="if(this.style.background==='transparent')this.style.background='var(--hover-bg)'"
                                                onmouseout="this.style.background=this.getAttribute('data-sel')==='1'?'var(--brand-light)':'transparent'"
                                                :data-sel="selected === ic.d ? '1' : '0'">
                                            <svg fill="none" viewBox="0 0 24 24" style="width:18px;height:18px;flex-shrink:0;"
                                                 :stroke="selected === ic.d ? 'var(--brand)' : 'var(--text-2)'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="ic.d"/>
                                            </svg>
                                            <span style="font-size:8px;margin-top:2px;color:var(--text-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;" x-text="ic.n"></span>
                                        </button>
                                    </template>
                                    <template x-if="filteredIcons.length === 0">
                                        <p class="text-xs py-4 text-center" style="color:var(--text-3);grid-column:span 8">No icons found</p>
                                    </template>
                                </div>
                            </div>
                            <div class="px-3 pb-3 pt-1 flex justify-end" style="border-top:1px solid var(--border);">
                                <button type="button" @click="selected='';open=false"
                                        class="text-xs font-semibold border-none cursor-pointer"
                                        style="background:none;color:var(--text-3);">Clear icon</button>
                            </div>
                        </div>
                    </div>

                    {{-- URL / Link type --}}
                    <div x-data="urlPicker()">
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Link To</label>
                        <input type="hidden" name="url" :value="resolvedUrl">

                        {{-- Type tabs --}}
                        <div class="flex rounded-xl overflow-hidden mb-2" style="border:1.5px solid var(--border);">
                            <template x-for="t in types" :key="t.k">
                                <button type="button" @click="type = t.k; sel = ''"
                                        class="flex-1 py-1.5 text-xs font-semibold transition-all border-none cursor-pointer"
                                        :style="type === t.k
                                            ? 'background:var(--brand);color:#fff'
                                            : 'background:var(--surface-raised);color:var(--text-2)'"
                                        x-text="t.label"></button>
                            </template>
                        </div>

                        {{-- Application / Plugin / Module dropdown --}}
                        <template x-if="type !== 'custom'">
                            <div>
                                <template x-if="sourceList.length > 0">
                                    <select x-model="sel" class="w-full rounded-xl text-sm outline-none"
                                            style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                                        <option value="">— Select —</option>
                                        <template x-for="s in sourceList" :key="s.url">
                                            <option :value="s.url" x-text="s.label"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="sourceList.length === 0">
                                    <p class="text-xs rounded-xl px-3 py-2.5" style="color:var(--text-3);background:var(--surface-raised);border:1.5px solid var(--border);">
                                        No items available in this category.
                                    </p>
                                </template>
                            </div>
                        </template>

                        {{-- Custom URL --}}
                        <template x-if="type === 'custom'">
                            <input x-model="sel" type="text" placeholder="e.g. /dashboard or https://example.com"
                                   class="w-full rounded-xl text-sm outline-none box-border"
                                   style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        </template>
                    </div>

                    {{-- Category --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold" style="color:var(--text-2)">Category <span style="color:#ef4444">*</span></label>
                            <button type="button" @click="showNewCat = !showNewCat"
                                    class="text-xs font-semibold border-none cursor-pointer"
                                    style="background:none;"
                                    :style="showNewCat ? 'color:#ef4444' : 'color:var(--brand)'"
                                    x-text="showNewCat ? '✕ Cancel' : '+ New Category'"></button>
                        </div>
                        <select name="category" x-ref="catSelect" class="w-full rounded-xl text-sm outline-none"
                                style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                            @foreach($menuCategories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->display_name }}</option>
                            @endforeach
                        </select>
                        <div x-show="showNewCat" x-cloak class="mt-2 p-3 rounded-xl flex flex-col gap-2"
                             style="background:var(--hover-bg);border:1px solid var(--border);">
                            <div class="flex gap-2">
                                <input type="text" x-model="newCatName" placeholder="Category name"
                                       class="flex-1 rounded-lg text-xs outline-none"
                                       style="padding:7px 10px;border:1px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                       onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                                <input type="color" x-model="newCatColor"
                                       style="width:38px;height:33px;border-radius:7px;border:1px solid var(--border);cursor:pointer;padding:2px;">
                            </div>
                            <button type="button" @click="saveNewCategory()" :disabled="savingCat || !newCatName.trim()"
                                    class="w-full py-1.5 rounded-lg text-xs font-bold text-white border-none cursor-pointer"
                                    style="background:var(--brand);" :style="(savingCat || !newCatName.trim()) ? 'opacity:.5' : ''"
                                    x-text="savingCat ? 'Saving…' : 'Add Category'"></button>
                            <p x-show="catError" x-text="catError" class="text-xs m-0" style="color:#ef4444;" x-cloak></p>
                        </div>
                    </div>

                </div>
                <div class="flex justify-end gap-2 px-6 py-4 flex-shrink-0" style="border-top:1px solid var(--border);">
                    <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold cursor-pointer"
                            style="border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-2);">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white border-none cursor-pointer"
                            style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">Create →</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Edit Menu Modal ────────────────────────────────────────── --}}
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showEditModal = false"
         x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
        <div class="w-full rounded-2xl flex flex-col"
             style="max-width:520px;max-height:92vh;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 flex-shrink-0" style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1)" x-text="'Edit: ' + (editMenu?.name ?? '')"></h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3)" x-text="editMenu?.category ? catLabel(editMenu.category) : ''"></p>
                </div>
                <button @click="showEditModal = false"
                        class="w-7 h-7 rounded-lg flex items-center justify-center border-none cursor-pointer"
                        style="background:none;color:var(--text-3);"
                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form x-ref="editForm" method="POST" :action="'/menus/' + (editMenu?.id ?? '')"
                  class="flex flex-col overflow-hidden">
                @csrf @method('PUT')
                <div class="overflow-y-auto px-6 py-5 flex flex-col gap-4" style="flex:1;">

                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Menu Name</label>
                        <input type="text" name="name" x-model="editMenu.name"
                               class="w-full rounded-xl text-sm outline-none box-border"
                               style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                               required>
                    </div>

                    {{-- Icon picker (edit) --}}
                    <div x-data="iconPicker()" x-effect="showEditModal, selected = editMenu.icon || ''">
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Icon</label>
                        <input type="hidden" name="icon" x-model="selected">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center gap-3 rounded-xl text-sm cursor-pointer"
                                style="padding:8px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-2);">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background:var(--surface-raised);">
                                <template x-if="selected">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="var(--brand)" style="width:16px;height:16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="selected"/>
                                    </svg>
                                </template>
                                <template x-if="!selected">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" style="width:16px;height:16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>
                            <span x-text="selected ? iconName(selected) : 'Select icon…'" class="flex-1 text-left text-sm"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="mt-2 rounded-xl overflow-hidden" style="border:1.5px solid var(--border);background:var(--surface-base);">
                            <div class="px-3 pt-3">
                                <input x-model="q" placeholder="Search icons…" type="text"
                                       class="w-full rounded-lg text-xs outline-none box-border"
                                       style="padding:7px 10px;border:1px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                       onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                            </div>
                            <div class="p-2 overflow-y-auto" style="max-height:220px;">
                                <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:3px;">
                                    <template x-for="ic in filteredIcons" :key="ic.n">
                                        <button type="button" @click="selected = ic.d; open = false"
                                                :title="ic.n"
                                                class="flex flex-col items-center justify-center rounded-lg cursor-pointer transition-all"
                                                style="padding:6px 2px;border:none;"
                                                :style="selected === ic.d ? 'background:var(--brand-light)' : 'background:transparent'"
                                                onmouseover="if(this.style.background==='transparent')this.style.background='var(--hover-bg)'"
                                                onmouseout="this.style.background=this.getAttribute('data-sel')==='1'?'var(--brand-light)':'transparent'"
                                                :data-sel="selected === ic.d ? '1' : '0'">
                                            <svg fill="none" viewBox="0 0 24 24" style="width:18px;height:18px;flex-shrink:0;"
                                                 :stroke="selected === ic.d ? 'var(--brand)' : 'var(--text-2)'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="ic.d"/>
                                            </svg>
                                            <span style="font-size:8px;margin-top:2px;color:var(--text-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;" x-text="ic.n"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div class="px-3 pb-3 pt-1 flex justify-end" style="border-top:1px solid var(--border);">
                                <button type="button" @click="selected='';open=false"
                                        class="text-xs font-semibold border-none cursor-pointer"
                                        style="background:none;color:var(--text-3);">Clear icon</button>
                            </div>
                        </div>
                    </div>

                    {{-- URL / Link type (edit) --}}
                    <div x-data="urlPicker()" x-effect="showEditModal, initFromUrl(editMenu.url)">
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Link To</label>
                        <input type="hidden" name="url" :value="resolvedUrl">

                        <div class="flex rounded-xl overflow-hidden mb-2" style="border:1.5px solid var(--border);">
                            <template x-for="t in types" :key="t.k">
                                <button type="button" @click="type = t.k; if(type!=='custom') sel=''"
                                        class="flex-1 py-1.5 text-xs font-semibold transition-all border-none cursor-pointer"
                                        :style="type === t.k
                                            ? 'background:var(--brand);color:#fff'
                                            : 'background:var(--surface-raised);color:var(--text-2)'"
                                        x-text="t.label"></button>
                            </template>
                        </div>

                        <template x-if="type !== 'custom'">
                            <div>
                                <template x-if="sourceList.length > 0">
                                    <select x-model="sel" class="w-full rounded-xl text-sm outline-none"
                                            style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                                        <option value="">— Select —</option>
                                        <template x-for="s in sourceList" :key="s.url">
                                            <option :value="s.url" x-text="s.label"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="sourceList.length === 0">
                                    <p class="text-xs rounded-xl px-3 py-2.5" style="color:var(--text-3);background:var(--surface-raised);border:1.5px solid var(--border);">
                                        No items available in this category.
                                    </p>
                                </template>
                            </div>
                        </template>

                        <template x-if="type === 'custom'">
                            <input x-model="sel" type="text" placeholder="e.g. /dashboard or https://example.com"
                                   class="w-full rounded-xl text-sm outline-none box-border"
                                   style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        </template>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Category</label>
                        <select name="category" x-model="editMenu.category" class="w-full rounded-xl text-sm outline-none"
                                style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                            @foreach($menuCategories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" :value="editMenu.is_active ? '1' : '0'">
                        <div @click="editMenu.is_active = !editMenu.is_active" class="relative cursor-pointer"
                             style="width:38px;height:21px;border-radius:99px;transition:background .15s;"
                             :style="editMenu.is_active ? 'background:#22c55e' : 'background:#d1d5db'">
                            <div style="position:absolute;top:2.5px;width:16px;height:16px;background:#fff;border-radius:99px;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .15s;"
                                 :style="editMenu.is_active ? 'left:19px' : 'left:3px'"></div>
                        </div>
                        <span class="text-sm font-semibold" style="color:var(--text-2);" x-text="editMenu.is_active ? 'Active' : 'Inactive'"></span>
                    </label>

                </div>
                <div class="flex justify-end gap-2 px-6 py-4 flex-shrink-0" style="border-top:1px solid var(--border);">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold cursor-pointer"
                            style="border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-2);">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white border-none cursor-pointer"
                            style="background:var(--brand);">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Items Modal ─────────────────────────────────────────────── --}}
    <div x-show="showItemsModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showItemsModal = false"
         x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
        <div class="w-full flex flex-col"
             style="max-width:680px;max-height:90vh;border-radius:20px;overflow:hidden;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 flex-shrink-0" style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1)" x-text="itemsMenu ? 'Items: ' + itemsMenu.name : 'Menu Items'"></h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3)" x-text="items.length + ' item' + (items.length === 1 ? '' : 's')"></p>
                </div>
                <button @click="showItemsModal = false"
                        class="w-7 h-7 rounded-lg flex items-center justify-center border-none cursor-pointer"
                        style="background:none;color:var(--text-3);"
                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Add item form --}}
            <div class="px-5 py-3 flex-shrink-0" style="border-bottom:1px solid var(--border);background:var(--surface-raised);">
                <div class="flex gap-2 items-end flex-wrap">
                    <div style="flex:1;min-width:130px;">
                        <label class="block mb-1" style="font-size:10.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Label *</label>
                        <input type="text" x-model="newItem.label" @keydown.enter.prevent="addItem()" placeholder="Link text"
                               class="w-full rounded-xl text-sm outline-none box-border"
                               style="padding:7px 11px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="flex:2;min-width:160px;">
                        <label class="block mb-1" style="font-size:10.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">URL</label>
                        <input type="text" x-model="newItem.url" @keydown.enter.prevent="addItem()" placeholder="https:// or /path"
                               class="w-full rounded-xl text-sm outline-none box-border"
                               style="padding:7px 11px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="width:115px;">
                        <label class="block mb-1" style="font-size:10.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Parent</label>
                        <select x-model="newItem.parent_id"
                                class="w-full rounded-xl outline-none"
                                style="padding:7px 11px;font-size:12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                            <option value="">— None —</option>
                            <template x-for="it in items" :key="it.id">
                                <option :value="it.id" x-text="it.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5 pb-0.5">
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold whitespace-nowrap" style="color:var(--text-2)">
                            <input type="checkbox" x-model="newItem.newTab" style="width:13px;height:13px;accent-color:var(--brand);">
                            New tab
                        </label>
                    </div>
                    <button @click="addItem()" :disabled="savingItem || !newItem.label.trim()"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white border-none cursor-pointer whitespace-nowrap"
                            style="background:var(--brand);"
                            :style="(savingItem || !newItem.label.trim()) ? 'opacity:.5' : ''">
                        <span x-text="savingItem ? '…' : '+ Add'"></span>
                    </button>
                </div>
                <p x-show="itemError" x-text="itemError" class="text-xs mt-1.5 m-0" style="color:#ef4444;" x-cloak></p>
            </div>

            {{-- Items list --}}
            <div class="overflow-y-auto flex-1" style="padding:6px 0;">
                <div x-show="loadingItems" class="py-10 text-center" style="color:var(--text-3);">
                    <svg style="width:28px;height:28px;animation:spin 1s linear infinite;stroke:currentColor;opacity:.5;margin:0 auto" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div x-show="!loadingItems && items.length === 0" class="py-10 text-center" style="color:var(--text-3);">
                    <p class="text-sm font-semibold m-0 mb-1" style="color:var(--text-2)">No items yet</p>
                    <p class="text-xs m-0">Use the form above to add your first link.</p>
                </div>
                <template x-for="item in items" :key="item.id">
                    <div class="px-5">
                        {{-- View row --}}
                        <div x-show="editItemId !== item.id"
                             class="flex items-center gap-2.5 py-2.5"
                             style="border-bottom:1px solid var(--border);"
                             onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span x-show="item.parent_label" class="text-xs" style="color:var(--text-3)" x-text="'↳ ' + item.parent_label"></span>
                                    <span class="text-sm font-semibold" style="color:var(--text-1)" x-text="item.label"></span>
                                    <span x-show="item.url" class="font-mono text-xs px-1.5 py-px rounded" style="color:var(--text-3);background:var(--surface-raised);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.url"></span>
                                    <span x-show="item.target === '_blank'" class="text-xs px-1.5 py-px rounded font-bold" style="color:var(--brand);background:var(--brand-ring);">↗ new tab</span>
                                    <span x-show="!item.is_active" class="text-xs px-1.5 py-px rounded font-bold" style="color:#94a3b8;background:#f1f5f9;">hidden</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button @click="startEditItem(item)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                        style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                                    <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="deleteItem(item)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                        style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;">
                                    <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Edit row --}}
                        <div x-show="editItemId === item.id" x-cloak
                             class="py-2.5 flex flex-col gap-2"
                             style="border-bottom:1px solid var(--border);">
                            <div class="flex gap-2 flex-wrap">
                                <div style="flex:1;min-width:120px;">
                                    <label class="block mb-1 text-xs font-bold" style="color:var(--text-3)">Label *</label>
                                    <input type="text" x-model="editItemData.label"
                                           class="w-full rounded-xl text-sm outline-none box-border"
                                           style="padding:7px 10px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                                </div>
                                <div style="flex:2;min-width:150px;">
                                    <label class="block mb-1 text-xs font-bold" style="color:var(--text-3)">URL</label>
                                    <input type="text" x-model="editItemData.url"
                                           class="w-full rounded-xl text-sm outline-none box-border"
                                           style="padding:7px 10px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                                </div>
                                <div style="width:110px;">
                                    <label class="block mb-1 text-xs font-bold" style="color:var(--text-3)">Parent</label>
                                    <select x-model="editItemData.parent_id"
                                            class="w-full rounded-xl outline-none"
                                            style="padding:7px 10px;font-size:12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                                        <option value="">— None —</option>
                                        <template x-for="it in items.filter(x => x.id !== item.id)" :key="it.id">
                                            <option :value="it.id" x-text="it.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer" style="color:var(--text-2)">
                                    <input type="checkbox" :checked="editItemData.target === '_blank'"
                                           @change="editItemData.target = $event.target.checked ? '_blank' : '_self'"
                                           style="width:13px;height:13px;accent-color:var(--brand);">
                                    New tab
                                </label>
                                <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer" style="color:var(--text-2)">
                                    <input type="checkbox" x-model="editItemData.is_active" style="width:13px;height:13px;accent-color:var(--brand);">
                                    Visible
                                </label>
                                <div class="flex-1"></div>
                                <button @click="editItemId = null"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer"
                                        style="border:1px solid var(--border);background:var(--surface-base);color:var(--text-2);">Cancel</button>
                                <button @click="saveEditItem(item)" :disabled="savingEdit || !editItemData.label.trim()"
                                        class="px-4 py-1.5 rounded-lg text-xs font-bold text-white border-none cursor-pointer"
                                        style="background:var(--brand);"
                                        :style="(savingEdit || !editItemData.label.trim()) ? 'opacity:.5' : ''">
                                    <span x-text="savingEdit ? 'Saving…' : 'Save'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <form x-ref="deleteForm" method="POST" :action="'/menus/' + (deleteTarget?.id ?? '')" style="display:none;">
        @csrf @method('DELETE')
    </form>

</div>
@endsection

@push('scripts')
<style>
@keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
</style>
<script>
// ── Icon library (Heroicons outline 24px) ────────────────────────────────
const SB_ICONS=[
 {n:'home',d:'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'},
 {n:'dashboard',d:'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'},
 {n:'apps',d:'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'},
 {n:'collection',d:'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'},
 {n:'shopping-bag',d:'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'},
 {n:'shopping-cart',d:'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'},
 {n:'check-circle',d:'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'},
 {n:'light-bulb',d:'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'},
 {n:'cog',d:'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'},
 {n:'menu',d:'M4 6h16M4 12h16M4 18h7'},
 {n:'menu-alt',d:'M4 6h16M4 12h8m-8 6h16'},
 {n:'tag',d:'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'},
 {n:'user',d:'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'},
 {n:'user-group',d:'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'},
 {n:'users',d:'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'},
 {n:'bell',d:'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'},
 {n:'search',d:'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'},
 {n:'star',d:'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'},
 {n:'heart',d:'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'},
 {n:'bookmark',d:'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z'},
 {n:'folder',d:'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z'},
 {n:'folder-open',d:'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z'},
 {n:'document',d:'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'},
 {n:'document-text',d:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'},
 {n:'chart-bar',d:'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'},
 {n:'chart-pie',d:'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'},
 {n:'calendar',d:'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'},
 {n:'clock',d:'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'},
 {n:'lock',d:'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'},
 {n:'lock-open',d:'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'},
 {n:'shield',d:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
 {n:'key',d:'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'},
 {n:'globe',d:'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'},
 {n:'code',d:'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'},
 {n:'terminal',d:'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'},
 {n:'server',d:'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01'},
 {n:'database',d:'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'},
 {n:'cloud',d:'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'},
 {n:'cloud-upload',d:'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12'},
 {n:'cloud-download',d:'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10'},
 {n:'wifi',d:'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0'},
 {n:'phone',d:'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'},
 {n:'mail',d:'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'},
 {n:'chat',d:'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'},
 {n:'camera',d:'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z'},
 {n:'photograph',d:'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'},
 {n:'film',d:'M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z'},
 {n:'music',d:'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'},
 {n:'map',d:'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'},
 {n:'location',d:'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'},
 {n:'flag',d:'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'},
 {n:'gift',d:'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'},
 {n:'credit-card',d:'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'},
 {n:'currency-dollar',d:'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'},
 {n:'fire',d:'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z'},
 {n:'lightning',d:'M13 10V3L4 14h7v7l9-11h-7z'},
 {n:'refresh',d:'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'},
 {n:'arrow-right',d:'M14 5l7 7m0 0l-7 7m7-7H3'},
 {n:'arrow-left',d:'M10 19l-7-7m0 0l7-7m-7 7h18'},
 {n:'arrow-up',d:'M5 10l7-7m0 0l7 7m-7-7v18'},
 {n:'arrow-down',d:'M19 14l-7 7m0 0l-7-7m7 7V3'},
 {n:'external-link',d:'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'},
 {n:'info',d:'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'},
 {n:'exclamation',d:'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'},
 {n:'eye',d:'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'},
 {n:'eye-off',d:'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21'},
 {n:'pencil',d:'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z'},
 {n:'trash',d:'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'},
 {n:'copy',d:'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z'},
 {n:'share',d:'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'},
 {n:'download',d:'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'},
 {n:'upload',d:'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'},
 {n:'filter',d:'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z'},
 {n:'sort',d:'M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4'},
 {n:'list',d:'M4 6h16M4 10h16M4 14h16M4 18h16'},
 {n:'view-grid',d:'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'},
 {n:'table',d:'M3 10h18M3 14h18M10 3v18M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'},
 {n:'link',d:'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'},
 {n:'paper-clip',d:'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13'},
 {n:'at-symbol',d:'M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207'},
 {n:'hashtag',d:'M7 20l4-16m2 16l4-16M6 9h14M4 15h14'},
 {n:'cube',d:'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'},
 {n:'beaker',d:'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'},
 {n:'academic-cap',d:'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'},
 {n:'briefcase',d:'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'},
 {n:'office-building',d:'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'},
 {n:'color-swatch',d:'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'},
 {n:'puzzle',d:'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z'},
 {n:'plus',d:'M12 4v16m8-8H4'},
 {n:'minus',d:'M20 12H4'},
 {n:'x',d:'M6 18L18 6M6 6l12 12'},
 {n:'check',d:'M5 13l4 4L19 7'},
 {n:'dots-horizontal',d:'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z'},
 {n:'dots-vertical',d:'M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z'},
 {n:'chip',d:'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18'},
 {n:'sparkles',d:'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'},
 {n:'adjustments',d:'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'},
 {n:'template',d:'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'},
 {n:'question-mark',d:'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'},
 {n:'support',d:'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'},
 {n:'identification',d:'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'},
 {n:'truck',d:'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'},
];

// ── URL sources from server ──────────────────────────────────────────────
const URL_SOURCES = @json($urlSources);

// ── Icon Picker Alpine component ─────────────────────────────────────────
function iconPicker() {
    return {
        selected: '',
        open: false,
        q: '',
        get filteredIcons() {
            if (!this.q.trim()) return SB_ICONS;
            const q = this.q.toLowerCase();
            return SB_ICONS.filter(ic => ic.n.includes(q));
        },
        iconName(d) {
            return SB_ICONS.find(ic => ic.d === d)?.n || 'icon';
        },
    };
}

// ── URL Picker Alpine component ──────────────────────────────────────────
function urlPicker() {
    return {
        type: 'module',
        sel: '',
        types: [
            { k: 'application', label: 'App' },
            { k: 'plugin',      label: 'Plugin' },
            { k: 'module',      label: 'Module' },
            { k: 'custom',      label: 'Custom' },
        ],
        get sourceList() {
            if (this.type === 'application') return URL_SOURCES.applications || [];
            if (this.type === 'plugin')      return URL_SOURCES.plugins || [];
            if (this.type === 'module')      return URL_SOURCES.modules || [];
            return [];
        },
        get resolvedUrl() { return this.sel || ''; },
        initFromUrl(url) {
            if (!url) { this.type = 'module'; this.sel = ''; return; }
            const modules = URL_SOURCES.modules || [];
            if (modules.find(m => m.url === url)) { this.type = 'module'; this.sel = url; return; }
            const apps = URL_SOURCES.applications || [];
            if (apps.find(a => a.url === url)) { this.type = 'application'; this.sel = url; return; }
            const plugins = URL_SOURCES.plugins || [];
            if (plugins.find(p => p.url === url)) { this.type = 'plugin'; this.sel = url; return; }
            this.type = 'custom'; this.sel = url;
        },
    };
}

function addMenuForm() {
    return {
        showNewCat: false, newCatName: '', newCatColor: '#6366f1',
        savingCat: false, catError: '',
        async saveNewCategory() {
            if (!this.newCatName.trim()) return;
            this.savingCat = true; this.catError = '';
            try {
                const r = await fetch('/menu-categories/quick', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ name: this.newCatName.trim(), color: this.newCatColor }),
                });
                const j = await r.json().catch(() => ({}));
                if (!j.success) { this.catError = j.message || 'Could not save.'; }
                else {
                    const sel = this.$refs.catSelect;
                    const opt = new Option(j.category.label, j.category.slug, true, true);
                    sel.add(opt); sel.value = j.category.slug;
                    this.newCatName = ''; this.newCatColor = '#6366f1'; this.showNewCat = false;
                }
            } catch { this.catError = 'Network error.'; }
            this.savingCat = false;
        },
    };
}

function menuTable() {
    return {
        search: '', page: 1, perPage: 15, perPageOpts: [10, 15, 25, 50],
        statusFilter: 'all', sortBy: 'sort_order', categoryFilter: '',
        showAddModal: false, showEditModal: false,
        editMenu: { name: '', url: '', icon: '', category: '', is_active: true },
        deleteTarget: null, moving: false,
        allMenus: @json($menus),
        categories: @json($categoryOptions),

        // Items modal
        showItemsModal: false, itemsMenu: null, items: [],
        loadingItems: false, itemError: '',
        newItem: { label: '', url: '', parent_id: '', newTab: false },
        savingItem: false,
        editItemId: null,
        editItemData: { label: '', url: '', target: '_self', parent_id: '', is_active: true },
        savingEdit: false,

        get categoryPills() {
            const seen = new Set();
            return this.allMenus
                .filter(m => { if (seen.has(m.category)) return false; seen.add(m.category); return true; })
                .map(m => ({ val: m.category, label: this.catLabel(m.category) }));
        },

        categoryCount(val) { return this.allMenus.filter(m => m.category === val).length; },

        get filtered() {
            let rows = this.allMenus;
            const q = this.search.trim().toLowerCase();
            if (q) rows = rows.filter(m => m.name.toLowerCase().includes(q) || this.catLabel(m.category).toLowerCase().includes(q));
            if (this.statusFilter === 'active')   rows = rows.filter(m => m.is_active);
            if (this.statusFilter === 'inactive') rows = rows.filter(m => !m.is_active);
            if (this.categoryFilter) rows = rows.filter(m => m.category === this.categoryFilter);
            if (this.sortBy === 'sort_order') rows = [...rows].sort((a,b) => (a.sort_order??0) - (b.sort_order??0) || a.name.localeCompare(b.name));
            if (this.sortBy === 'name_asc')   rows = [...rows].sort((a,b) => a.name.localeCompare(b.name));
            if (this.sortBy === 'name_desc')  rows = [...rows].sort((a,b) => b.name.localeCompare(a.name));
            if (this.sortBy === 'category')   rows = [...rows].sort((a,b) => a.category.localeCompare(b.category));
            return rows;
        },
        get paginated() { return this.filtered.slice((this.page-1)*this.perPage, this.page*this.perPage); },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
        get pageRange() {
            const t = this.totalPages, c = this.page, pages = [];
            if (t <= 7) { for (let i = 1; i <= t; i++) pages.push(i); return pages; }
            pages.push(1);
            if (c > 3) pages.push('...');
            for (let i = Math.max(2, c-1); i <= Math.min(t-1, c+1); i++) pages.push(i);
            if (c < t-2) pages.push('...');
            pages.push(t);
            return pages;
        },

        catLabel(s) { return this.categories.find(c => c.slug === s)?.label || s; },
        catStyle(s) {
            const c = this.categories.find(x => x.slug === s)?.color || '#475569';
            return 'background:'+c+'18;color:'+c+';border:1px solid '+c+'35';
        },
        highlight(text) {
            const q = this.search.trim();
            if (!q) return text;
            return text.replace(new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')', 'gi'), '<mark style="background:var(--brand-ring);color:var(--brand);border-radius:2px;padding:0 1px">$1</mark>');
        },
        dtInfo(total, page, perPage) {
            if (total === 0) return 'No menus';
            const from = (page-1)*perPage+1, to = Math.min(page*perPage, total);
            return `Showing ${from}–${to} of ${total} menu${total===1?'':'s'}`;
        },

        async toggleActive(menu) {
            try {
                const r = await fetch('/menus/' + menu.id + '/toggle', {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
                const j = await r.json();
                if (j.success) { const m = this.allMenus.find(x => x.id === menu.id); if (m) m.is_active = j.is_active; }
            } catch {}
        },

        openEdit(m) { this.editMenu = {...m}; this.showEditModal = true; },

        async moveMenu(menu, direction) {
            if (this.moving) return;
            this.moving = true;
            try {
                const r = await fetch('/menus/' + menu.id + '/move', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ direction }),
                });
                const j = await r.json();
                if (j.success) {
                    const m1 = this.allMenus.find(m => m.id === j.menu.id);
                    const m2 = this.allMenus.find(m => m.id === j.adjacent.id);
                    if (m1) m1.sort_order = j.menu.sort_order;
                    if (m2) m2.sort_order = j.adjacent.sort_order;
                }
            } catch {}
            this.moving = false;
        },

        doDelete(m) {
            if (!confirm('Delete "'+m.name+'"?\nAll items inside will be deleted too.')) return;
            this.deleteTarget = m;
            this.$nextTick(() => this.$refs.deleteForm.submit());
        },

        async openItems(menu) {
            this.itemsMenu = menu; this.items = []; this.editItemId = null;
            this.itemError = ''; this.newItem = { label: '', url: '', parent_id: '', newTab: false };
            this.showItemsModal = true; this.loadingItems = true;
            try {
                const r = await fetch('/menus/'+menu.id+'/items-data', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                const j = await r.json();
                if (j.success) this.items = j.items;
            } catch {}
            this.loadingItems = false;
        },

        async addItem() {
            if (!this.newItem.label.trim() || this.savingItem) return;
            this.savingItem = true; this.itemError = '';
            try {
                const r = await fetch('/menus/'+this.itemsMenu.id+'/items', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ label: this.newItem.label.trim(), url: this.newItem.url.trim()||null, target: this.newItem.newTab?'_blank':'_self', parent_id: this.newItem.parent_id||null }),
                });
                const j = await r.json();
                if (j.success) { this.items = j.items; this.newItem = { label:'', url:'', parent_id:'', newTab:false }; this._syncCount(this.itemsMenu.id, j.items.length); }
                else this.itemError = j.message || 'Could not add item.';
            } catch { this.itemError = 'Network error.'; }
            this.savingItem = false;
        },

        startEditItem(item) {
            this.editItemId = item.id;
            this.editItemData = { label: item.label, url: item.url||'', target: item.target||'_self', parent_id: item.parent_id||'', is_active: item.is_active };
        },

        async saveEditItem(item) {
            if (!this.editItemData.label.trim() || this.savingEdit) return;
            this.savingEdit = true;
            try {
                const r = await fetch('/menus/'+this.itemsMenu.id+'/items/'+item.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ label: this.editItemData.label.trim(), url: this.editItemData.url.trim()||null, target: this.editItemData.target, parent_id: this.editItemData.parent_id||null, is_active: this.editItemData.is_active?1:0 }),
                });
                const j = await r.json();
                if (j.success) { this.items = j.items; this.editItemId = null; }
            } catch {}
            this.savingEdit = false;
        },

        async deleteItem(item) {
            if (!confirm('Delete "'+item.label+'"?')) return;
            try {
                const r = await fetch('/menus/'+this.itemsMenu.id+'/items/'+item.id, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
                const j = await r.json();
                if (j.success) { this.items = j.items; this._syncCount(this.itemsMenu.id, j.items.length); }
            } catch {}
        },

        _syncCount(menuId, count) { const m = this.allMenus.find(x => x.id === menuId); if (m) m.all_items_count = count; },
    };
}
</script>
@endpush
