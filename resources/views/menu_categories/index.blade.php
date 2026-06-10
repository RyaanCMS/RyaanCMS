@extends('layouts.app')
@section('title', 'Menu Categories')
@section('header', 'Menu Categories')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('menus.index') }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors"
       style="color:var(--text-2);border:1px solid var(--border);">
        Menus
    </a>
    <button x-data @click="$dispatch('open-add-menu-category')"
            class="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
            style="background:var(--brand); box-shadow:0 2px 8px var(--brand-ring);">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>New Category</span>
    </button>
</div>
@endsection

@section('content')
<div x-data="menuCategoryTable()" @open-add-menu-category.window="showAddModal = true" class="space-y-5">
    <div class="dt-wrap">
        <div class="dt-head">
            <div>
                <h2 class="dt-title">Menu Category Table</h2>
                <p class="dt-subtitle">Create and manage the categories used by Menu Management dropdowns.</p>
            </div>
        </div>

        <div class="dt-toolbar" style="flex-direction:column;align-items:stretch;gap:10px;">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="dt-search">
                    <svg class="dt-search-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-ref="searchInput"
                           x-model="search" @input="page = 1"
                           @keydown.escape="search = ''; page = 1"
                           type="text"
                           placeholder="Search name, slug, description..."
                           class="dt-search-input">
                    <button x-show="search" @click="search = ''; page = 1; $refs.searchInput.focus()"
                            class="dt-clear" title="Clear (Esc)" x-cloak>
                        <svg style="width:9px;height:9px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="dt-per-page">
                    <span>Show</span>
                    <select x-model.number="perPage" @change="page = 1">
                        <template x-for="n in perPageOpts" :key="n">
                            <option :value="n" x-text="n"></option>
                        </template>
                    </select>
                </div>

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

                <div class="dt-per-page">
                    <span>Sort</span>
                    <select x-model="sortBy" @change="page = 1">
                        <option value="order_asc">Order</option>
                        <option value="name_asc">Name A-Z</option>
                        <option value="name_desc">Name Z-A</option>
                        <option value="menus_desc">Most Menus</option>
                        <option value="newest">Newest</option>
                    </select>
                </div>

                <span class="dt-count">
                    <span x-text="filtered.length"></span>
                    <span x-text="filtered.length === 1 ? ' category' : ' categories'"></span>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="dt-table">
                <thead>
                    <tr>
                        <th class="dt-th">#</th>
                        <th class="dt-th">Category</th>
                        <th class="dt-th">Slug</th>
                        <th class="dt-th">Menus</th>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <p class="text-sm font-medium">No menu categories found</p>
                                <p class="text-xs mt-1">Try a different search or filter</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="(category, i) in paginated" :key="category.id">
                        <tr class="dt-tr">
                            <td class="dt-td" style="color:var(--text-3)" x-text="(page - 1) * perPage + i + 1"></td>
                            <td class="dt-td">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg flex-shrink-0" :style="'background:' + category.color + ';box-shadow:0 0 0 3px ' + category.color + '22'"></span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-sm" style="color:var(--text-1)" x-html="highlight(category.name)"></p>
                                            <span x-show="category.is_system" class="sys-badge sys-badge-gray" x-cloak>System</span>
                                        </div>
                                        <p class="text-[11px] mt-0.5" style="color:var(--text-3)" x-text="category.description || 'No description'"></p>
                                        <p x-show="category.parent_name" class="text-[11px] mt-0.5" style="color:var(--text-3)" x-cloak>
                                            Parent:
                                            <span x-text="category.parent_name"></span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="dt-td">
                                <code class="text-[11px] px-2 py-1 rounded-lg" style="background:var(--surface-raised);color:var(--text-2);border:1px solid var(--border);" x-text="category.slug"></code>
                            </td>
                            <td class="dt-td">
                                <span class="text-sm font-semibold" style="color:var(--text-2)" x-text="category.menus_count || 0"></span>
                            </td>
                            <td class="dt-td">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :style="category.is_active
                                          ? 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0'
                                          : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border)'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="category.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                    <span x-text="category.is_active ? 'Active' : 'Inactive'"></span>
                                </span>
                            </td>
                            <td class="dt-td" style="color:var(--text-3)" x-text="fmtDate(category.created_at)"></td>
                            <td class="dt-td" style="text-align:right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="toggleActive(category)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            :style="category.is_active ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border);'"
                                            :title="category.is_active ? 'Deactivate' : 'Activate'">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="category.is_active ? 'M5 13l4 4L19 7' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
                                        </svg>
                                    </button>
                                    <button @click="openEdit(category)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;"
                                            title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="doDelete(category)"
                                            :disabled="category.is_system"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            :style="category.is_system
                                                ? 'background:var(--surface-raised);color:var(--text-3);border:1px solid var(--border);opacity:.55;cursor:not-allowed;'
                                                : 'background:#fef2f2;color:#ef4444;border:1px solid #fecaca;'"
                                            :title="category.is_system ? 'System categories cannot be deleted' : 'Delete'">
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

        <div class="dt-foot">
            <span class="dt-foot-info" x-text="dtInfo(filtered.length, page, perPage)"></span>
            <div class="dt-pages" x-show="totalPages > 1">
                <button class="dt-page-btn" @click="page = Math.max(1, page - 1)" :disabled="page === 1">&lt;</button>
                <template x-for="p in pageRange" :key="p + '-' + page">
                    <template x-if="p === '...'">
                        <span class="dt-page-dot">...</span>
                    </template>
                    <template x-if="p !== '...'">
                        <button class="dt-page-btn" :class="p === page ? 'dt-page-on' : ''" @click="page = p" x-text="p"></button>
                    </template>
                </template>
                <button class="dt-page-btn" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">&gt;</button>
            </div>
        </div>
    </div>

    <div x-show="showAddModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showAddModal = false"
         x-cloak>
        <div class="w-full max-w-md rounded-2xl overflow-hidden"
             style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1)">New Menu Category</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3)">Add a reusable category for menus.</p>
                </div>
                <button @click="showAddModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors" style="color:var(--text-3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('menu-categories.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Category Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Blog Menu"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           required autofocus>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Slug</label>
                    <input type="text" name="slug" placeholder="blog-menu"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                    <p class="text-xs mt-1.5" style="color:var(--text-3);">Leave blank to generate from the name.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Menu Category</label>
                    <select name="parent_id"
                            class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                        <option value="">Top Level Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->display_name }}{{ $category->is_active ? '' : ' (Inactive)' }}
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Description</label>
                    <input type="text" name="description" placeholder="Where this category is used"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Color</label>
                        <input type="color" name="color" value="#475569"
                               class="w-full h-10 rounded-xl px-2 py-1"
                               style="background:var(--input-bg);border:1px solid var(--border);">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Order</label>
                        <input type="number" name="sort_order" value="100" min="0" max="9999"
                               class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                               style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded" style="accent-color:var(--brand)">
                    <span class="text-sm" style="color:var(--text-2)">Active</span>
                </label>
                <div class="flex items-center justify-end gap-3 pt-2" style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                    <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                            style="color:var(--text-2);border:1px solid var(--border);">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                            style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showEditModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45);"
         @click.self="showEditModal = false"
         x-cloak>
        <div class="w-full max-w-md rounded-2xl overflow-hidden"
             style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                <div>
                    <h3 class="font-bold text-base" style="color:var(--text-1)">Edit Menu Category</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-3)" x-text="editCategory?.name"></p>
                </div>
                <button @click="showEditModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors" style="color:var(--text-3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" :action="'/menu-categories/' + (editCategory?.id ?? '')" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Category Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" x-model="editCategory.name"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Slug</label>
                    <input type="text" name="slug" x-model="editCategory.slug"
                           :readonly="editCategory.is_system"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           :style="editCategory.is_system ? 'background:var(--surface-raised);border:1px solid var(--border);color:var(--text-3);' : 'background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);'">
                    <p class="text-xs mt-1.5" style="color:var(--text-3);" x-text="editCategory.is_system ? 'System slugs are locked because they control menu placement.' : 'Changing the slug will update menus in this category.'"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Menu Category</label>
                    <select name="parent_id" x-model="editCategory.parent_id"
                            class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                        <option value="">Top Level Category</option>
                        <template x-for="category in parentOptions" :key="category.id">
                            <option :value="category.id" x-text="category.display_name + (category.is_active ? '' : ' (Inactive)')"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Description</label>
                    <input type="text" name="description" x-model="editCategory.description"
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Color</label>
                        <input type="color" name="color" x-model="editCategory.color"
                               class="w-full h-10 rounded-xl px-2 py-1"
                               style="background:var(--input-bg);border:1px solid var(--border);">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:var(--text-2)">Order</label>
                        <input type="number" name="sort_order" x-model="editCategory.sort_order" min="0" max="9999"
                               class="w-full rounded-xl px-4 py-2.5 text-sm outline-none transition-all"
                               style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" :value="editCategory.is_active ? '1' : '0'">
                    <button type="button" @click="editCategory.is_active = !editCategory.is_active"
                            class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative w-10 h-5 rounded-full transition-colors"
                             :style="editCategory.is_active ? 'background:#22c55e' : 'background:#d1d5db'">
                            <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                                 :style="editCategory.is_active ? 'left:calc(100% - 18px)' : 'left:2px'"></div>
                        </div>
                        <span class="text-sm" style="color:var(--text-2)" x-text="editCategory.is_active ? 'Active' : 'Inactive'"></span>
                    </button>
                </label>
                <div class="flex items-center justify-end gap-3 pt-2" style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                            style="color:var(--text-2);border:1px solid var(--border);">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:-translate-y-px"
                            style="background:var(--brand);box-shadow:0 2px 8px var(--brand-ring);">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form x-ref="deleteForm" method="POST" :action="'/menu-categories/' + (deleteTarget?.id ?? '')" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script>
function menuCategoryTable() {
    return {
        ...dtMixin({ perPage: 10 }),

        statusFilter: 'all',
        sortBy: 'order_asc',
        page: 1,
        showAddModal: false,
        showEditModal: false,
        editCategory: { name: '', slug: '', description: '', parent_id: '', color: '#475569', sort_order: 100, is_active: true, is_system: false },
        deleteTarget: null,
        allCategories: @json($categories),

        get filtered() {
            let list = this.allCategories;

            if (this.statusFilter === 'active') list = list.filter(c => c.is_active);
            if (this.statusFilter === 'inactive') list = list.filter(c => !c.is_active);

            const q = this.search.trim().toLowerCase();
            if (q) {
                list = list.filter(c => (
                    c.name.toLowerCase().includes(q) ||
                    c.slug.toLowerCase().includes(q) ||
                    (c.parent_name || '').toLowerCase().includes(q) ||
                    (c.description || '').toLowerCase().includes(q)
                ));
            }

            list = [...list];
            if (this.sortBy === 'order_asc') list.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0) || a.name.localeCompare(b.name));
            if (this.sortBy === 'name_asc') list.sort((a, b) => a.name.localeCompare(b.name));
            if (this.sortBy === 'name_desc') list.sort((a, b) => b.name.localeCompare(a.name));
            if (this.sortBy === 'menus_desc') list.sort((a, b) => (b.menus_count || 0) - (a.menus_count || 0));
            if (this.sortBy === 'newest') list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

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
            return this.dtPageRange(this.totalPages, this.page).map(p => typeof p === 'number' ? p : '...');
        },

        get parentOptions() {
            const currentId = Number(this.editCategory?.id || 0);
            return this.allCategories.filter(category => {
                if (!currentId) return true;
                return Number(category.id) !== currentId && !this.isDescendantOf(category, currentId);
            });
        },

        highlight(text) {
            return this.dtHighlight(text, this.search.trim());
        },

        async toggleActive(category) {
            try {
                const r = await fetch('/menu-categories/' + category.id + '/toggle', {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
                const j = await r.json();
                if (j.success) { const c = this.allCategories.find(x => x.id === category.id); if (c) c.is_active = j.is_active; }
            } catch {}
        },

        openEdit(category) {
            this.editCategory = { ...category, parent_id: category.parent_id || '' };
            this.showEditModal = true;
        },

        isDescendantOf(category, parentId) {
            const seen = new Set();
            let current = category;

            while (current?.parent_id) {
                if (Number(current.parent_id) === Number(parentId)) return true;
                if (seen.has(current.parent_id)) return false;

                seen.add(current.parent_id);
                current = this.allCategories.find(item => Number(item.id) === Number(current.parent_id));
            }

            return false;
        },

        doDelete(category) {
            if (category.is_system) return;
            if ((category.menus_count || 0) > 0) {
                alert('Move menus out of this category before deleting it.');
                return;
            }
            if (!confirm('Delete "' + category.name + '"?')) return;
            this.deleteTarget = category;
            this.$nextTick(() => this.$refs.deleteForm.submit());
        },

        fmtDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
    }
}
</script>
@endpush
