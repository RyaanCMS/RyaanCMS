@extends('layouts.app')
@section('title', 'Edit Menu - '.$menu->name)
@section('header', 'Edit Menu')

@section('header-actions')
<a href="{{ route('menus.index') }}" class="text-sm font-medium transition-colors" style="color:var(--text-3)">Back to Menus</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="menuEditor()">

    <div class="space-y-5">
        <div class="rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow);">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--border);">
                <h3 class="font-bold text-sm" style="color:var(--text-1)">Menu Settings</h3>
            </div>
            <form method="POST" action="{{ route('menus.update', $menu) }}" class="p-5 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">Name</label>
                    <input type="text" name="name" value="{{ old('name', $menu->name) }}"
                           class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           required>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">Category</label>
                    <select name="category"
                            class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                            onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        @foreach($menuCategories as $category)
                        <option value="{{ $category->slug }}" {{ old('category', $menu->category) === $category->slug ? 'selected' : '' }}>{{ $category->name }}{{ $category->is_active ? '' : ' (Inactive)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $menu->is_active ? 'checked' : '' }}
                               class="w-4 h-4 rounded" style="accent-color:var(--brand)">
                        <span class="text-sm" style="color:var(--text-2)">Active</span>
                    </label>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-white"
                            style="background:var(--brand);">Save</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow);">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--border);">
                <h3 class="font-bold text-sm" style="color:var(--text-1)">Add Menu Item</h3>
            </div>
            <form method="POST" action="{{ route('menus.items.store', $menu) }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">Label *</label>
                    <input type="text" name="label" x-ref="labelInput" value="{{ old('label') }}"
                           class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           placeholder="Link label" required>
                </div>

                @if(!empty($siteLinks))
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">
                        Quick Link
                        <span class="font-normal ml-1" style="color:var(--text-3)">pick a page to auto-fill URL</span>
                    </label>
                    <select @change="onQuickLinkSelect($event)"
                            class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                            onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        <option value="">Select a page</option>
                        @foreach($siteLinks as $group => $links)
                        <optgroup label="{{ $group }}">
                            @foreach($links as $link)
                            <option value="{{ $link['url'] }}" data-label="{{ $link['label'] }}">
                                {{ $link['label'] }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                        <optgroup label="Custom">
                            <option value="__custom__">Type custom URL</option>
                        </optgroup>
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold mb-1.5 flex items-center gap-2" style="color:var(--text-2)">
                        URL
                        <span x-show="selectedLinkLabel" class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold"
                              style="background:#eef2ff;color:#4338ca;" x-text="selectedLinkLabel"></span>
                    </label>
                    <input type="text" name="url" x-ref="urlInput" value="{{ old('url') }}"
                           class="w-full rounded-xl px-3 py-2 text-sm outline-none font-mono"
                           style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           placeholder="/about or https://example.com"
                           @input="selectedLinkLabel = ''">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">Icon</label>
                        <input type="text" name="icon" value="{{ old('icon') }}"
                               class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                               style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                               placeholder="#">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">Target</label>
                        <select name="target"
                                class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                                style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                            <option value="_self">Same Tab</option>
                            <option value="_blank">New Tab</option>
                        </select>
                    </div>
                </div>

                @if($allItems->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2)">Parent Item</label>
                    <select name="parent_id"
                            class="w-full rounded-xl px-3 py-2 text-sm outline-none"
                            style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                        <option value="">Top Level</option>
                        @foreach($allItems as $item)
                        <option value="{{ $item->id }}">{{ $item->label }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
                        style="background:var(--brand);">
                    Add Item
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="rounded-2xl overflow-hidden" style="background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow);">
            <div class="px-5 py-4 space-y-4" style="border-bottom:1px solid var(--border);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-2">
                        <h3 class="font-bold text-sm" style="color:var(--text-1)">Menu Structure</h3>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                              style="background:#eef2ff;color:#4338ca;">
                            {{ $menu->name }}
                        </span>
                    </div>
                    <span class="text-xs" style="color:var(--text-3)">
                        <span x-text="filteredItems.length"></span> of {{ $tableItems->count() }} items
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input type="search" x-model.debounce.150ms="search" @input="page = 1"
                               class="w-full rounded-xl pl-9 pr-3 py-2 text-sm outline-none"
                               style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                               placeholder="Search menu structure">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--text-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z"/>
                        </svg>
                    </div>
                    <div class="px-3 py-2 rounded-xl text-xs font-semibold whitespace-nowrap"
                         style="background:var(--card-sub);border:1px solid var(--border);color:var(--text-2);">
                        Showing 20 per page
                    </div>
                </div>
            </div>

            @if($tableItems->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                     style="background:#f1f5f9;">
                    <svg class="w-7 h-7" style="color:#94a3b8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold mb-1" style="color:var(--text-1)">No items yet</p>
                <p class="text-xs" style="color:var(--text-3)">Add your first menu item using the form on the left.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background:var(--card-sub);border-bottom:1px solid var(--border);">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-2)">Item</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-2)">URL</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-2)">Parent</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-2)">Target</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-2)">Status</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-2)">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="--divide-color:var(--border)">
                        @foreach($tableItems as $item)
                        <tr x-show="visibleItemIds.includes({{ $item->id }})" x-cloak
                            class="align-top transition-colors"
                            onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                            <td class="px-4 py-3 min-w-[190px]">
                                <div class="flex items-center gap-2">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-lg text-base flex-shrink-0"
                                          style="background:var(--card-sub);border:1px solid var(--border)">
                                        {{ $item->icon ?: '#' }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate" style="color:var(--text-1)">{{ $item->label }}</p>
                                        @if($item->installation_id)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold"
                                              style="background:#eff6ff;color:#1d4ed8;">Plugin</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 min-w-[220px]">
                                <span class="font-mono text-xs break-all" style="color:var(--text-3)">{{ $item->url ?: '(no URL)' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap" style="color:var(--text-2)">
                                {{ $item->parent?->label ?: 'Top Level' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap" style="color:var(--text-2)">
                                {{ $item->target === '_blank' ? 'New Tab' : 'Same Tab' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-semibold"
                                      style="{{ $item->is_active ? 'background:#ecfdf5;color:#047857;' : 'background:#f1f5f9;color:#64748b;' }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="{{ $item->is_active ? 'background:#10b981;' : 'background:#94a3b8;' }}"></span>
                                    {{ $item->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="editingItemId = editingItemId === {{ $item->id }} ? null : {{ $item->id }}"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                            style="color:var(--text-3);border:1px solid var(--border);">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('menus.items.destroy', [$menu, $item]) }}" onsubmit="return confirm('Delete this item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                                style="color:#ef4444;border:1px solid #fecaca;background:#fef2f2">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="visibleItemIds.includes({{ $item->id }}) && editingItemId === {{ $item->id }}" x-cloak>
                            <td colspan="6" class="px-4 py-4" style="background:var(--card-sub);border-top:1px solid var(--border);">
                                <form method="POST" action="{{ route('menus.items.update', [$menu, $item]) }}" class="space-y-3">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold mb-1" style="color:var(--text-2)">Label</label>
                                            <input type="text" name="label" value="{{ $item->label }}" required
                                                   class="w-full rounded-lg px-3 py-1.5 text-sm outline-none"
                                                   style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold mb-1" style="color:var(--text-2)">URL</label>
                                            <input type="text" name="url" value="{{ $item->url }}"
                                                   class="w-full rounded-lg px-3 py-1.5 text-sm outline-none font-mono"
                                                   style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold mb-1" style="color:var(--text-2)">Parent</label>
                                            <select name="parent_id"
                                                    class="w-full rounded-lg px-3 py-1.5 text-sm outline-none"
                                                    style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                                <option value="">Top Level</option>
                                                @foreach($allItems as $parentItem)
                                                    @if($parentItem->id !== $item->id)
                                                    <option value="{{ $parentItem->id }}" {{ $item->parent_id == $parentItem->id ? 'selected' : '' }}>
                                                        {{ $parentItem->label }}
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold mb-1" style="color:var(--text-2)">Icon</label>
                                            <input type="text" name="icon" value="{{ $item->icon }}"
                                                   class="w-full rounded-lg px-3 py-1.5 text-sm outline-none"
                                                   style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);"
                                                   placeholder="#">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold mb-1" style="color:var(--text-2)">Target</label>
                                            <select name="target"
                                                    class="w-full rounded-lg px-3 py-1.5 text-sm outline-none"
                                                    style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                                <option value="_self" {{ $item->target === '_self' ? 'selected' : '' }}>Same Tab</option>
                                                <option value="_blank" {{ $item->target === '_blank' ? 'selected' : '' }}>New Tab</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center space-x-2 cursor-pointer pb-1.5">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}
                                                       style="accent-color:var(--brand)">
                                                <span class="text-xs" style="color:var(--text-2)">Active</span>
                                            </label>
                                        </div>
                                    </div>
                                    @if($installations->isNotEmpty())
                                    <div>
                                        <label class="block text-xs font-semibold mb-1" style="color:var(--text-2)">Link to Plugin</label>
                                        <select name="installation_id"
                                                class="w-full rounded-lg px-3 py-1.5 text-sm outline-none"
                                                style="background:var(--input-bg);border:1px solid var(--border);color:var(--text-1);">
                                            <option value="">None</option>
                                            @foreach($installations as $inst)
                                            <option value="{{ $inst->id }}" {{ $item->installation_id == $inst->id ? 'selected' : '' }}>
                                                {{ $inst->item->name ?? 'Plugin #'.$inst->id }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="flex items-center gap-2">
                                        <button type="submit"
                                                class="px-4 py-1.5 rounded-lg text-xs font-semibold text-white"
                                                style="background:var(--brand);">Save Changes</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div x-show="filteredItems.length === 0" x-cloak class="py-12 text-center px-6">
                <p class="text-sm font-semibold mb-1" style="color:var(--text-1)">No matching menu items</p>
                <p class="text-xs" style="color:var(--text-3)">Try another search term.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4" style="border-top:1px solid var(--border);">
                <p class="text-xs" style="color:var(--text-3)">
                    Showing <span x-text="filteredItems.length ? pageStart + 1 : 0"></span>-<span x-text="pageEnd"></span>
                    of <span x-text="filteredItems.length"></span>
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold disabled:opacity-40"
                            style="background:var(--card-sub);border:1px solid var(--border);color:var(--text-2);">Previous</button>
                    <span class="text-xs" style="color:var(--text-3)">
                        Page <span x-text="page"></span> / <span x-text="totalPages"></span>
                    </span>
                    <button type="button" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold disabled:opacity-40"
                            style="background:var(--card-sub);border:1px solid var(--border);color:var(--text-2);">Next</button>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@php
    $menuEditorItems = $tableItems->map(fn($item) => [
        'id'     => $item->id,
        'label'  => $item->label,
        'url'    => $item->url,
        'icon'   => $item->icon,
        'target' => $item->target === '_blank' ? 'new tab' : 'same tab',
        'parent' => $item->parent?->label ?: 'top level',
        'status' => $item->is_active ? 'active' : 'hidden',
        'plugin' => $item->installation?->item?->name,
    ])->values();
@endphp
@push('scripts')
<script>
function menuEditor() {
    return {
        selectedLinkLabel: '',
        search: '',
        page: 1,
        perPage: 20,
        editingItemId: null,
        items: @json($menuEditorItems),

        get filteredItems() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.items;

            return this.items.filter(item => [
                item.label,
                item.url,
                item.icon,
                item.target,
                item.parent,
                item.status,
                item.plugin,
            ].filter(Boolean).some(value => String(value).toLowerCase().includes(q)));
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
        },

        get pageStart() {
            if (this.page > this.totalPages) this.page = this.totalPages;
            return (this.page - 1) * this.perPage;
        },

        get pageEnd() {
            return Math.min(this.pageStart + this.perPage, this.filteredItems.length);
        },

        get visibleItemIds() {
            return this.filteredItems.slice(this.pageStart, this.pageEnd).map(item => item.id);
        },

        onQuickLinkSelect(event) {
            const opt = event.target.selectedOptions[0];
            if (!opt || !opt.value) return;

            if (opt.value === '__custom__') {
                this.selectedLinkLabel = '';
                this.$nextTick(() => this.$refs.urlInput.focus());
                event.target.value = '';
                return;
            }

            this.$refs.urlInput.value = opt.value;
            this.selectedLinkLabel = opt.dataset.label || '';

            const labelEl = this.$refs.labelInput;
            if (labelEl && !labelEl.value) {
                labelEl.value = opt.dataset.label || '';
            }
        },

        onPluginSelect(event) {
            const url = event.target.selectedOptions[0]?.dataset.url;
            if (url) this.$refs.urlInput.value = url;
        }
    }
}
</script>
@endpush
