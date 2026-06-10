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
                        <option value="name_asc">Name A → Z</option>
                        <option value="name_desc">Name Z → A</option>
                        <option value="items_desc">Most Items</option>
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
                        <th class="dt-th">Items</th>
                        <th class="dt-th">Status</th>
                        <th class="dt-th" style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length === 0">
                        <tr>
                            <td colspan="6" class="dt-empty">
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
                                <span class="font-semibold" style="color:var(--text-2)" x-text="menu.all_items_count ?? 0"></span>
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
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openItems(menu)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                            style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
                                        <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                        </svg>
                                        Items
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
        <div class="w-full max-w-md rounded-2xl overflow-hidden"
             style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
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

            <form method="POST" action="{{ route('menus.store') }}" class="px-6 py-5 flex flex-col gap-4"
                  x-data="addMenuForm()" @submit.prevent="$el.submit()">
                @csrf
                <div>
                    <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">
                        Menu Name <span style="color:#ef4444">*</span>
                    </label>
                    <input type="text" name="name" placeholder="e.g. Main Navigation, Footer Links"
                           class="w-full rounded-xl text-sm outline-none box-border"
                           style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           required autofocus>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold" style="color:var(--text-2)">Category <span style="color:#ef4444">*</span></label>
                        <button type="button" @click="showNewCat = !showNewCat"
                                class="text-xs font-semibold border-none cursor-pointer"
                                style="background:none;"
                                :style="showNewCat ? 'color:#ef4444' : 'color:var(--brand)'"
                                x-text="showNewCat ? '✕ Cancel' : '+ New Category'"></button>
                    </div>
                    <select name="category" x-ref="catSelect"
                            class="w-full rounded-xl text-sm outline-none"
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
                <div class="flex justify-end gap-2 pt-1" style="border-top:1px solid var(--border);margin-top:4px;">
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
        <div class="w-full max-w-md rounded-2xl overflow-hidden"
             style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
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
                  class="px-6 py-5 flex flex-col gap-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Menu Name</label>
                    <input type="text" name="name" x-model="editMenu.name"
                           class="w-full rounded-xl text-sm outline-none box-border"
                           style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           required>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1.5" style="color:var(--text-2)">Category</label>
                    <select name="category" x-model="editMenu.category"
                            class="w-full rounded-xl text-sm outline-none"
                            style="padding:9px 12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);">
                        @foreach($menuCategories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" :value="editMenu.is_active ? '1' : '0'">
                    <div @click="editMenu.is_active = !editMenu.is_active"
                         class="relative cursor-pointer"
                         style="width:38px;height:21px;border-radius:99px;transition:background .15s;"
                         :style="editMenu.is_active ? 'background:#22c55e' : 'background:#d1d5db'">
                        <div style="position:absolute;top:2.5px;width:16px;height:16px;background:#fff;border-radius:99px;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .15s;"
                             :style="editMenu.is_active ? 'left:19px' : 'left:3px'"></div>
                    </div>
                    <span class="text-sm font-semibold" style="color:var(--text-2);" x-text="editMenu.is_active ? 'Active' : 'Inactive'"></span>
                </label>
                <div class="flex justify-end gap-2 pt-1" style="border-top:1px solid var(--border);margin-top:4px;">
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
        statusFilter: 'all', sortBy: 'name_asc', categoryFilter: '',
        showAddModal: false, showEditModal: false,
        editMenu: { name: '', category: '', is_active: true },
        deleteTarget: null,
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
            if (this.sortBy === 'name_asc')   rows = [...rows].sort((a,b) => a.name.localeCompare(b.name));
            if (this.sortBy === 'name_desc')  rows = [...rows].sort((a,b) => b.name.localeCompare(a.name));
            if (this.sortBy === 'items_desc') rows = [...rows].sort((a,b) => (b.all_items_count??0) - (a.all_items_count??0));
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

        openEdit(m) { this.editMenu = {...m}; this.showEditModal = true; },
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
