@extends('layouts.app')
@section('title', 'Menu Management')
@section('header', 'Menu Management')

@section('content')
<div x-data="menuTable()" @open-add-menu.window="showAddModal = true" class="space-y-5">

    {{-- Data table card --}}
    <div class="dt-wrap">
        <div class="dt-head">
            <div>
                <h2 class="dt-title">Menus Table</h2>
                <p class="dt-subtitle">Search, filter, and manage every navigation menu in one place.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <button type="button" @click="showAddModal = true"
                        class="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                        style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Menu</span>
                </button>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="dt-toolbar" style="flex-direction:column;align-items:stretch;gap:10px;">
            {{-- Row 1: search + per-page + status + sort + count --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Search box --}}
                <div class="dt-search">
                    <svg class="dt-search-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-ref="searchInput"
                           x-model="search" @input="page = 1"
                           @keydown.escape="search = ''; page = 1"
                           type="text"
                           placeholder="Search name, slug, category, status…"
                           class="dt-search-input">
                    <button x-show="search" @click="search = ''; page = 1; $refs.searchInput.focus()"
                            class="dt-clear" title="Clear (Esc)" x-cloak>
                        <svg style="width:9px;height:9px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Per-page selector --}}
                <div class="dt-per-page">
                    <span>Show</span>
                    <select x-model.number="perPage" @change="page = 1">
                        <template x-for="n in perPageOpts" :key="n">
                            <option :value="n" x-text="n"></option>
                        </template>
                    </select>
                </div>

                {{-- Status filter --}}
                <div class="flex rounded-xl overflow-hidden flex-shrink-0" style="border:1px solid var(--border);">
                    <button @click="statusFilter = 'all'; page = 1"
                            class="px-3 py-1.5 text-xs transition-all"
                            :style="statusFilter === 'all'
                                ? 'background:var(--brand);color:#fff;font-weight:700'
                                : 'background:var(--surface-raised);color:var(--text-2)'">All</button>
                    <button @click="statusFilter = 'active'; page = 1"
                            class="px-3 py-1.5 text-xs transition-all border-l"
                            style="border-color:var(--border);"
                            :style="statusFilter === 'active'
                                ? 'background:#15803d;color:#fff;font-weight:700'
                                : 'background:var(--surface-raised);color:var(--text-2)'">Active</button>
                    <button @click="statusFilter = 'inactive'; page = 1"
                            class="px-3 py-1.5 text-xs transition-all border-l"
                            style="border-color:var(--border);"
                            :style="statusFilter === 'inactive'
                                ? 'background:#64748b;color:#fff;font-weight:700'
                                : 'background:var(--surface-raised);color:var(--text-2)'">Inactive</button>
                </div>

                {{-- Sort --}}
                <div class="dt-per-page">
                    <span>Sort</span>
                    <select x-model="sortBy" @change="page = 1">
                        <option value="name_asc">Name A → Z</option>
                        <option value="name_desc">Name Z → A</option>
                        <option value="items_desc">Most Items</option>
                        <option value="newest">Newest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>

                {{-- Result count --}}
                <span class="dt-count">
                    <span x-text="filtered.length"></span>
                    <span x-text="filtered.length === 1 ? ' menu' : ' menus'"></span>
                </span>
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
                        <th class="dt-th">Created</th>
                        <th class="dt-th" style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length === 0">
                        <tr>
                            <td colspan="7" class="dt-empty">
                                <svg class="w-10 h-10 mb-3 mx-auto" style="color:var(--border)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium">No menus found</p>
                                <p class="text-xs mt-1">Try a different search or filter</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="(menu, i) in paginated" :key="menu.id">
                        <tr class="dt-tr">
                            {{-- # --}}
                            <td class="dt-td" style="color:var(--text-3)"
                                x-text="(page - 1) * perPage + i + 1"></td>
                            {{-- Name + Slug --}}
                            <td class="dt-td">
                                <p class="font-semibold text-sm" style="color:var(--text-1)" x-html="highlight(menu.name)"></p>
                                <p class="text-[11px] font-mono mt-0.5" style="color:var(--text-3)" x-html="highlight(menu.slug)"></p>
                            </td>
                            {{-- Category badge --}}
                            <td class="dt-td">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :style="catStyle(menu.category)"
                                      x-text="catLabel(menu.category)"></span>
                            </td>
                            {{-- Items count --}}
                            <td class="dt-td">
                                <span class="text-sm font-semibold" style="color:var(--text-2)"
                                      x-text="(menu.all_items_count ?? 0)"></span>
                            </td>
                            {{-- Status --}}
                            <td class="dt-td">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :style="menu.is_active
                                          ? 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0'
                                          : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="menu.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                    <span x-text="menu.is_active ? 'Active' : 'Inactive'"></span>
                                </span>
                            </td>
                            {{-- Created --}}
                            <td class="dt-td" style="color:var(--text-3)"
                                x-text="fmtDate(menu.created_at)"></td>
                            {{-- Actions --}}
                            <td class="dt-td" style="text-align:right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Edit Items --}}
                                    <a :href="'/menus/' + menu.id"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-all hover:-translate-y-px"
                                       style="background:var(--brand);">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                        </svg>
                                        Items
                                    </a>
                                    {{-- Quick Edit --}}
                                    <button @click="openEdit(menu)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;"
                                            title="Edit"
                                            onmouseover="this.style.background='#dbeafe'"
                                            onmouseout="this.style.background='#eff6ff'">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button @click="doDelete(menu)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;"
                                            title="Delete"
                                            onmouseover="this.style.background='#fee2e2'"
                                            onmouseout="this.style.background='#fef2f2'">
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

        {{-- Footer: info + pagination --}}
        <div class="dt-foot">
            <span class="dt-foot-info" x-text="dtInfo(filtered.length, page, perPage)"></span>
            <div class="dt-pages" x-show="totalPages > 1">
                <button class="dt-page-btn" @click="page = Math.max(1, page - 1)" :disabled="page === 1">‹</button>
                <template x-for="p in pageRange" :key="p + '-' + page">
                    <template x-if="p === '…'">
                        <span class="dt-page-dot">…</span>
                    </template>
                    <template x-if="p !== '…'">
                        <button class="dt-page-btn" :class="p === page ? 'dt-page-on' : ''"
                                @click="page = p" x-text="p"></button>
                    </template>
                </template>
                <button class="dt-page-btn" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">›</button>
            </div>
        </div>

    </div>{{-- /table card --}}

    {{-- ── Add Menu Modal ─────────────────────────────────────── --}}
    <div x-show="showAddModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showAddModal = false"
         x-cloak>
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-md rounded-2xl overflow-hidden"
             style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1)">New Menu</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3)">Create a navigation menu for your site</p>
                </div>
                <button @click="showAddModal = false"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                        style="color:var(--text-3);"
                        onmouseover="this.style.background='var(--hover-bg)'"
                        onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            {{-- Form --}}
            <form method="POST" action="{{ route('menus.store') }}" class="p-6 space-y-4"
                  x-data="addMenuForm()" @submit.prevent="submitAddMenu($el)">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Menu Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Main Navigation, Footer Links"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'"
                           onblur="this.style.borderColor='var(--border)'"
                           required autofocus>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-semibold" style="color:var(--text-2)">Category <span style="color:#ef4444">*</span></label>
                        <button type="button" @click="showNewCat = !showNewCat"
                                class="text-xs font-semibold transition-colors"
                                :style="showNewCat ? 'color:#ef4444' : 'color:var(--brand)'">
                            <span x-text="showNewCat ? '✕ Cancel' : '+ New Category'"></span>
                        </button>
                    </div>
                    <select name="category" x-ref="catSelect"
                            class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                            onfocus="this.style.borderColor='var(--brand)'"
                            onblur="this.style.borderColor='var(--border)'">
                        @foreach($menuCategories as $category)
                        <option value="{{ $category->slug }}">{{ $category->display_name }}{{ $category->is_active ? '' : ' (Inactive)' }}</option>
                        @endforeach
                    </select>

                    {{-- Inline new category form --}}
                    <div x-show="showNewCat" x-cloak
                         class="rounded-xl p-3 space-y-2 mt-2"
                         style="background:var(--hover-bg);border:1px solid var(--border);">
                        <p class="text-xs font-semibold" style="color:var(--text-3);">New Category</p>
                        <div class="flex gap-2">
                            <input type="text" x-model="newCatName" placeholder="Category name"
                                   class="flex-1 rounded-lg px-3 py-2 text-sm outline-none"
                                   style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                                   onfocus="this.style.borderColor='var(--brand)'"
                                   onblur="this.style.borderColor='var(--border)'">
                            <input type="color" x-model="newCatColor" title="Color"
                                   style="width:40px;height:36px;border-radius:8px;border:1px solid var(--border);cursor:pointer;padding:2px;">
                        </div>
                        <button type="button" @click="saveNewCategory()"
                                :disabled="savingCat || !newCatName.trim()"
                                class="w-full py-1.5 rounded-lg text-xs font-semibold text-white disabled:opacity-50"
                                style="background:var(--brand);"
                                x-text="savingCat ? 'Saving…' : 'Add Category'">
                        </button>
                        <p x-show="catError" x-text="catError" class="text-xs" style="color:#ef4444;" x-cloak></p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2" style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                    <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                            style="color:var(--text-2);border:1px solid var(--border);"
                            onmouseover="this.style.background='var(--hover-bg)'"
                            onmouseout="this.style.background=''">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                            style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                        Create &amp; Add Items →
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Edit Menu Modal ────────────────────────────────────── --}}
    <div x-show="showEditModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showEditModal = false"
         x-cloak>
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-md rounded-2xl overflow-hidden"
             style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1)">Edit Menu</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3)" x-text="editMenu?.name"></p>
                </div>
                <button @click="showEditModal = false"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                        style="color:var(--text-3);"
                        onmouseover="this.style.background='var(--hover-bg)'"
                        onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            {{-- Form --}}
            <form x-ref="editForm" method="POST"
                  :action="'/menus/' + (editMenu?.id ?? '')"
                  class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Menu Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" x-model="editMenu.name"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'"
                           onblur="this.style.borderColor='var(--border)'"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Category <span style="color:#ef4444">*</span></label>
                    <select name="category" x-model="editMenu.category"
                            class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                            onfocus="this.style.borderColor='var(--brand)'"
                            onblur="this.style.borderColor='var(--border)'">
                        @foreach($menuCategories as $category)
                        <option value="{{ $category->slug }}">{{ $category->display_name }}{{ $category->is_active ? '' : ' (Inactive)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Status</label>
                    <input type="hidden" name="is_active" :value="editMenu.is_active ? '1' : '0'">
                    <button type="button" @click="editMenu.is_active = !editMenu.is_active"
                            class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative w-10 h-5 rounded-full transition-colors"
                             :style="editMenu.is_active ? 'background:#22c55e' : 'background:#d1d5db'">
                            <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                                 :style="editMenu.is_active ? 'left:calc(100% - 18px)' : 'left:2px'"></div>
                        </div>
                        <span class="text-sm" style="color:var(--text-2)"
                              x-text="editMenu.is_active ? 'Active — menu is visible' : 'Inactive — menu is hidden'"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between gap-3 pt-2" style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                    <a :href="'/menus/' + (editMenu?.id ?? '')"
                       class="text-xs font-medium transition-colors" style="color:var(--brand);">
                        → Edit Menu Items
                    </a>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                                style="color:var(--text-2);border:1px solid var(--border);"
                                onmouseover="this.style.background='var(--hover-bg)'"
                                onmouseout="this.style.background=''">Cancel</button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                                style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Hidden delete form --}}
    <form x-ref="deleteForm" method="POST" :action="'/menus/' + (deleteTarget?.id ?? '')" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

</div>{{-- /menuTable --}}
@endsection

@push('scripts')
<script>
function addMenuForm() {
    return {
        showNewCat: false,
        newCatName: '',
        newCatColor: '#6366f1',
        savingCat: false,
        catError: '',

        async saveNewCategory() {
            if (!this.newCatName.trim()) return;
            this.savingCat = true;
            this.catError  = '';
            try {
                const res  = await fetch('/menu-categories/quick', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name: this.newCatName.trim(), color: this.newCatColor }),
                });
                const json = await res.json().catch(() => ({}));
                if (!json.success) {
                    this.catError = json.message || 'Could not save category.';
                } else {
                    // Append option + select it
                    const sel = this.$refs.catSelect;
                    const opt = new Option(json.category.label, json.category.slug, true, true);
                    sel.add(opt);
                    sel.value = json.category.slug;
                    // Reset
                    this.newCatName  = '';
                    this.newCatColor = '#6366f1';
                    this.showNewCat  = false;
                }
            } catch { this.catError = 'Network error.'; }
            this.savingCat = false;
        },

        submitAddMenu(form) {
            form.submit();
        },
    };
}

function menuTable() {
    return {
        ...dtMixin({ perPage: 10 }),

        statusFilter:  'all',
        catFilter:     '',
        sortBy:        'name_asc',
        page:          1,
        showAddModal:  false,
        showEditModal: false,
        editMenu:      { name: '', category: 'header', is_active: true },
        deleteTarget:  null,

        allMenus: @json($menus),
        categories: @json($categoryOptions),

        init() {
            this.$watch('perPage', () => { this.page = 1; });
        },

        catCount(val) {
            return this.allMenus.filter(m => m.category === val).length;
        },

        get filtered() {
            let list = this.allMenus;

            if (this.statusFilter === 'active')   list = list.filter(m => m.is_active);
            if (this.statusFilter === 'inactive') list = list.filter(m => !m.is_active);
            if (this.catFilter) list = list.filter(m => m.category === this.catFilter);

            const q = this.search.trim().toLowerCase();
            if (q) {
                list = list.filter(m => {
                    const label  = this.catLabel(m.category).toLowerCase();
                    const status = m.is_active ? 'active' : 'inactive';
                    return (
                        m.name.toLowerCase().includes(q) ||
                        (m.slug || '').toLowerCase().includes(q) ||
                        label.includes(q) ||
                        m.category.toLowerCase().includes(q) ||
                        status.includes(q) ||
                        String(m.all_items_count || 0) === q
                    );
                });
            }

            list = [...list];
            if (this.sortBy === 'name_asc')   list.sort((a, b) => a.name.localeCompare(b.name));
            if (this.sortBy === 'name_desc')  list.sort((a, b) => b.name.localeCompare(a.name));
            if (this.sortBy === 'items_desc') list.sort((a, b) => (b.all_items_count || 0) - (a.all_items_count || 0));
            if (this.sortBy === 'newest')     list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            if (this.sortBy === 'oldest')     list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

            return list;
        },

        get paginated() {
            const s = (this.page - 1) * this.perPage;
            return this.filtered.slice(s, s + this.perPage);
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get pageRange() {
            return this.dtPageRange(this.totalPages, this.page);
        },

        highlight(text) {
            return this.dtHighlight(text, this.search.trim());
        },

        openEdit(menu) {
            this.editMenu = { ...menu };
            this.showEditModal = true;
        },

        doDelete(menu) {
            if (!confirm('Delete "' + menu.name + '"?\nThis will remove all menu items too.')) return;
            this.deleteTarget = menu;
            this.$nextTick(() => this.$refs.deleteForm.submit());
        },
        catLabel(cat) {
            return this.categoryBySlug(cat)?.label || cat;
        },

        catStyle(cat) {
            const category = this.categoryBySlug(cat);
            const color = category?.color || '#475569';
            return 'background:' + color + '12;color:' + color + ';border:1px solid ' + color + '35';
        },

        categoryBySlug(slug) {
            return this.categories.find(category => category.slug === slug);
        },

        get catPills() {
            return this.categories.map(category => ({
                val: category.slug,
                label: category.label,
                activeBg: 'background:' + category.color + ';border-color:' + category.color + ';color:#fff;'
            }));
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
    }
}
</script>
@endpush
