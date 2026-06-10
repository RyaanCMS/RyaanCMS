@extends('layouts.app')
@section('title', 'Menus')
@section('header', 'Menus')

@section('content')
<div x-data="menuTable()" @open-add-menu.window="showAddModal = true">

    {{-- Header row --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="display:flex;align-items:center;gap:8px;background:var(--surface-base);border:1.5px solid var(--border);border-radius:11px;padding:0 12px;width:260px;transition:border-color .13s;">
                <svg style="width:14px;height:14px;color:var(--text-3);flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input x-model="search" @input="page=1" @keydown.escape="search='';page=1" type="text"
                       placeholder="Search menus…"
                       style="border:none;outline:none;background:transparent;font-size:13px;color:var(--text-1);padding:9px 0;width:100%;font-family:inherit;"
                       onfocus="this.parentElement.style.borderColor='var(--brand)'"
                       onblur="this.parentElement.style.borderColor='var(--border)'">
                <button x-show="search" @click="search='';page=1" x-cloak style="background:none;border:none;cursor:pointer;color:var(--text-3);line-height:1;padding:0;">
                    <svg style="width:10px;height:10px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <span style="font-size:12px;color:var(--text-3);" x-text="filtered.length + ' menu' + (filtered.length===1?'':'s')"></span>
        </div>
        <button type="button" @click="showAddModal = true"
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:var(--brand);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;box-shadow:0 2px 8px var(--brand-ring);">
            <svg style="width:14px;height:14px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Menu
        </button>
    </div>

    {{-- Table --}}
    <div style="border-radius:16px;border:1px solid var(--border);overflow:hidden;background:var(--card-bg);box-shadow:var(--shadow);">
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                        <th style="text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Name</th>
                        <th style="text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Category</th>
                        <th style="text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Items</th>
                        <th style="text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Status</th>
                        <th style="text-align:right;padding:11px 16px;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length === 0">
                        <tr>
                            <td colspan="5" style="padding:48px 16px;text-align:center;color:var(--text-3);">
                                <svg style="width:36px;height:36px;margin:0 auto 10px;stroke:currentColor;opacity:.35" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                <p style="font-size:13.5px;font-weight:600;margin:0 0 3px;color:var(--text-2);">No menus yet</p>
                                <p style="font-size:12px;margin:0;">Click "New Menu" to create one</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="(menu, i) in paginated" :key="menu.id">
                        <tr style="border-bottom:1px solid var(--border);"
                            onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                            <td style="padding:12px 16px;">
                                <span style="font-weight:600;color:var(--text-1);" x-text="menu.name"></span>
                            </td>
                            <td style="padding:12px 16px;">
                                <span style="font-size:11.5px;font-weight:600;padding:2px 9px;border-radius:99px;"
                                      :style="catStyle(menu.category)"
                                      x-text="catLabel(menu.category)"></span>
                            </td>
                            <td style="padding:12px 16px;">
                                <span style="font-weight:600;color:var(--text-2);" x-text="menu.all_items_count ?? 0"></span>
                            </td>
                            <td style="padding:12px 16px;">
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;"
                                      :style="menu.is_active ? 'color:#15803d' : 'color:var(--text-3)'">
                                    <span style="width:7px;height:7px;border-radius:99px;flex-shrink:0;"
                                          :style="menu.is_active ? 'background:#22c55e' : 'background:#94a3b8'"></span>
                                    <span x-text="menu.is_active ? 'Active' : 'Inactive'"></span>
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                    <button @click="openItems(menu)"
                                            style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;background:var(--brand);color:#fff;font-size:12px;font-weight:600;border:none;cursor:pointer;">
                                        <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        Items
                                    </button>
                                    <button @click="openEdit(menu)"
                                            style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--border);background:var(--surface-base);cursor:pointer;color:var(--text-3);"
                                            title="Rename / settings">
                                        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="doDelete(menu)"
                                            style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1.5px solid #fecaca;background:#fef2f2;cursor:pointer;color:#ef4444;"
                                            title="Delete">
                                        <svg style="width:13px;height:13px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="totalPages > 1" style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid var(--border);font-size:12px;color:var(--text-3);">
            <span x-text="'Page ' + page + ' of ' + totalPages"></span>
            <div style="display:flex;gap:4px;">
                <button @click="page=Math.max(1,page-1)" :disabled="page===1"
                        style="padding:4px 10px;border-radius:7px;border:1px solid var(--border);background:var(--surface-base);cursor:pointer;color:var(--text-2);font-size:12px;" :style="page===1?'opacity:.4':''">‹ Prev</button>
                <button @click="page=Math.min(totalPages,page+1)" :disabled="page===totalPages"
                        style="padding:4px 10px;border-radius:7px;border:1px solid var(--border);background:var(--surface-base);cursor:pointer;color:var(--text-2);font-size:12px;" :style="page===totalPages?'opacity:.4':''">Next ›</button>
            </div>
        </div>
    </div>

    {{-- ── New Menu Modal ───────────────────────────────────────── --}}
    <div x-show="showAddModal" x-cloak
         style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,.45);"
         @click.self="showAddModal=false"
         x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
        <div style="width:100%;max-width:420px;border-radius:20px;overflow:hidden;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
                <h3 style="font-size:15px;font-weight:800;color:var(--text-1);margin:0;">New Menu</h3>
                <button @click="showAddModal=false" style="width:28px;height:28px;border-radius:8px;border:none;background:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center;justify-content:center;"
                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('menus.store') }}" style="padding:20px;display:flex;flex-direction:column;gap:14px;"
                  x-data="addMenuForm()" @submit.prevent="$el.submit()">
                @csrf
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:700;color:var(--text-2);margin-bottom:6px;">Menu Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Main Navigation, Footer Links"
                           style="width:100%;border-radius:10px;padding:9px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           required autofocus>
                </div>
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <label style="font-size:12.5px;font-weight:700;color:var(--text-2);">Category <span style="color:#ef4444">*</span></label>
                        <button type="button" @click="showNewCat=!showNewCat"
                                style="font-size:12px;font-weight:600;background:none;border:none;cursor:pointer;"
                                :style="showNewCat ? 'color:#ef4444' : 'color:var(--brand)'"
                                x-text="showNewCat ? '✕ Cancel' : '+ New Category'"></button>
                    </div>
                    <select name="category" x-ref="catSelect"
                            style="width:100%;border-radius:10px;padding:9px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
                        @foreach($menuCategories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->display_name }}</option>
                        @endforeach
                    </select>
                    <div x-show="showNewCat" x-cloak style="margin-top:8px;padding:12px;border-radius:10px;background:var(--hover-bg);border:1px solid var(--border);display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;gap:8px;">
                            <input type="text" x-model="newCatName" placeholder="Category name"
                                   style="flex:1;border-radius:8px;padding:7px 10px;font-size:12.5px;border:1px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;"
                                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                            <input type="color" x-model="newCatColor" style="width:38px;height:33px;border-radius:7px;border:1px solid var(--border);cursor:pointer;padding:2px;">
                        </div>
                        <button type="button" @click="saveNewCategory()" :disabled="savingCat||!newCatName.trim()"
                                style="width:100%;padding:7px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:var(--brand);border:none;cursor:pointer;"
                                :style="(savingCat||!newCatName.trim())?'opacity:.5':''"
                                x-text="savingCat ? 'Saving…' : 'Add Category'"></button>
                        <p x-show="catError" x-text="catError" style="font-size:11.5px;color:#ef4444;margin:0;" x-cloak></p>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:4px;border-top:1px solid var(--border);margin-top:4px;">
                    <button type="button" @click="showAddModal=false"
                            style="padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-2);cursor:pointer;"
                            onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">Cancel</button>
                    <button type="submit"
                            style="padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;box-shadow:0 2px 8px var(--brand-ring);">
                        Create →
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Edit Menu Modal ────────────────────────────────────────── --}}
    <div x-show="showEditModal" x-cloak
         style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,.45);"
         @click.self="showEditModal=false"
         x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
        <div style="width:100%;max-width:420px;border-radius:20px;overflow:hidden;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);"
             @click.stop
             x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
                <h3 style="font-size:15px;font-weight:800;color:var(--text-1);margin:0;" x-text="'Edit: ' + (editMenu?.name ?? '')"></h3>
                <button @click="showEditModal=false" style="width:28px;height:28px;border-radius:8px;border:none;background:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center;justify-content:center;"
                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form x-ref="editForm" method="POST" :action="'/menus/' + (editMenu?.id ?? '')" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:700;color:var(--text-2);margin-bottom:6px;">Menu Name</label>
                    <input type="text" name="name" x-model="editMenu.name"
                           style="width:100%;border-radius:10px;padding:9px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                           required>
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:700;color:var(--text-2);margin-bottom:6px;">Category</label>
                    <select name="category" x-model="editMenu.category"
                            style="width:100%;border-radius:10px;padding:9px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
                        @foreach($menuCategories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="hidden" name="is_active" :value="editMenu.is_active ? '1' : '0'">
                    <div @click="editMenu.is_active=!editMenu.is_active" style="width:38px;height:21px;border-radius:99px;position:relative;cursor:pointer;transition:background .15s;" :style="editMenu.is_active ? 'background:#22c55e' : 'background:#d1d5db'">
                        <div style="position:absolute;top:2.5px;width:16px;height:16px;background:#fff;border-radius:99px;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .15s;" :style="editMenu.is_active ? 'left:19px' : 'left:3px'"></div>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:var(--text-2);" x-text="editMenu.is_active ? 'Active' : 'Inactive'"></span>
                </label>
                <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:4px;border-top:1px solid var(--border);margin-top:4px;">
                    <button type="button" @click="showEditModal=false"
                            style="padding:8px 14px;border-radius:10px;font-size:13px;font-weight:600;border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-2);cursor:pointer;">Cancel</button>
                    <button type="submit"
                            style="padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Items Modal ─────────────────────────────────────────────── --}}
    <div x-show="showItemsModal" x-cloak
         style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,.45);"
         @click.self="showItemsModal=false"
         x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
        <div style="width:100%;max-width:660px;max-height:90vh;border-radius:20px;overflow:hidden;background:var(--card-bg);border:1px solid var(--border);box-shadow:var(--shadow-lg);display:flex;flex-direction:column;"
             @click.stop
             x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);flex-shrink:0;">
                <div>
                    <h3 style="font-size:15px;font-weight:800;color:var(--text-1);margin:0;" x-text="itemsMenu ? 'Items: ' + itemsMenu.name : 'Menu Items'"></h3>
                    <p style="font-size:11.5px;color:var(--text-3);margin:3px 0 0;" x-text="items.length + ' item' + (items.length===1?'':'s')"></p>
                </div>
                <button @click="showItemsModal=false" style="width:28px;height:28px;border-radius:8px;border:none;background:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center;justify-content:center;"
                        onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    <svg style="width:14px;height:14px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Add item form --}}
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);flex-shrink:0;background:var(--surface-raised);">
                <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:1;min-width:130px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em;">Label *</label>
                        <input type="text" x-model="newItem.label" @keydown.enter.prevent="addItem()" placeholder="Link text"
                               style="width:100%;border-radius:9px;padding:8px 11px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="flex:2;min-width:160px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em;">URL</label>
                        <input type="text" x-model="newItem.url" @keydown.enter.prevent="addItem()" placeholder="https:// or /path"
                               style="width:100%;border-radius:9px;padding:8px 11px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="width:120px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em;">Parent</label>
                        <select x-model="newItem.parent_id"
                                style="width:100%;border-radius:9px;padding:8px 11px;font-size:12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
                            <option value="">— None —</option>
                            <template x-for="it in items" :key="it.id">
                                <option :value="it.id" x-text="it.label"></option>
                            </template>
                        </select>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px;">
                        <label style="display:flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--text-2);cursor:pointer;white-space:nowrap;">
                            <input type="checkbox" x-model="newItem.newTab" style="width:13px;height:13px;accent-color:var(--brand);">
                            New tab
                        </label>
                    </div>
                    <button @click="addItem()" :disabled="savingItem||!newItem.label.trim()"
                            style="padding:8px 16px;border-radius:9px;font-size:13px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;white-space:nowrap;"
                            :style="(savingItem||!newItem.label.trim())?'opacity:.5':''">
                        <span x-text="savingItem ? '…' : '+ Add'"></span>
                    </button>
                </div>
                <p x-show="itemError" x-text="itemError" style="font-size:12px;color:#ef4444;margin:6px 0 0;" x-cloak></p>
            </div>

            {{-- Items list --}}
            <div style="overflow-y:auto;flex:1;padding:8px 0;">
                {{-- Loading --}}
                <div x-show="loadingItems" style="padding:40px;text-align:center;color:var(--text-3);">
                    <svg style="width:28px;height:28px;animation:spin 1s linear infinite;stroke:currentColor;opacity:.5;margin:0 auto" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>

                {{-- Empty --}}
                <div x-show="!loadingItems && items.length === 0" style="padding:40px;text-align:center;color:var(--text-3);">
                    <p style="font-size:13px;font-weight:600;color:var(--text-2);margin:0 0 4px;">No items yet</p>
                    <p style="font-size:12px;margin:0;">Use the form above to add your first link.</p>
                </div>

                {{-- Item rows --}}
                <template x-for="item in items" :key="item.id">
                    <div style="padding:0 16px;">
                        {{-- View row --}}
                        <div x-show="editItemId !== item.id"
                             style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);"
                             onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
                                    <span x-show="item.parent_label" style="font-size:11px;color:var(--text-3);" x-text="'↳ ' + item.parent_label"></span>
                                    <span style="font-size:13px;font-weight:600;color:var(--text-1);" x-text="item.label"></span>
                                    <span x-show="item.url" style="font-size:11.5px;color:var(--text-3);background:var(--surface-raised);padding:1px 7px;border-radius:5px;font-family:monospace;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.url"></span>
                                    <span x-show="item.target==='_blank'" style="font-size:10.5px;color:var(--brand);background:var(--brand-ring);padding:1px 6px;border-radius:5px;font-weight:700;">↗ new tab</span>
                                    <span x-show="!item.is_active" style="font-size:10.5px;color:#94a3b8;background:#f1f5f9;padding:1px 6px;border-radius:5px;font-weight:700;">hidden</span>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                                <button @click="startEditItem(item)"
                                        style="width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);background:var(--surface-base);cursor:pointer;color:var(--text-3);">
                                    <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="deleteItem(item)"
                                        style="width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;color:#ef4444;">
                                    <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Edit row --}}
                        <div x-show="editItemId === item.id" x-cloak
                             style="padding:10px 0;border-bottom:1px solid var(--border);display:flex;flex-direction:column;gap:8px;">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <div style="flex:1;min-width:120px;">
                                    <label style="font-size:11px;font-weight:700;color:var(--text-3);display:block;margin-bottom:3px;">Label *</label>
                                    <input type="text" x-model="editItemData.label"
                                           style="width:100%;border-radius:8px;padding:7px 10px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                                </div>
                                <div style="flex:2;min-width:150px;">
                                    <label style="font-size:11px;font-weight:700;color:var(--text-3);display:block;margin-bottom:3px;">URL</label>
                                    <input type="text" x-model="editItemData.url"
                                           style="width:100%;border-radius:8px;padding:7px 10px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                                           onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                                </div>
                                <div style="width:110px;">
                                    <label style="font-size:11px;font-weight:700;color:var(--text-3);display:block;margin-bottom:3px;">Parent</label>
                                    <select x-model="editItemData.parent_id"
                                            style="width:100%;border-radius:8px;padding:7px 10px;font-size:12px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
                                        <option value="">— None —</option>
                                        <template x-for="it in items.filter(x => x.id !== item.id)" :key="it.id">
                                            <option :value="it.id" x-text="it.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:16px;">
                                <label style="display:flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--text-2);cursor:pointer;">
                                    <input type="checkbox" :checked="editItemData.target==='_blank'" @change="editItemData.target=$event.target.checked?'_blank':'_self'" style="width:13px;height:13px;accent-color:var(--brand);">
                                    New tab
                                </label>
                                <label style="display:flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--text-2);cursor:pointer;">
                                    <input type="checkbox" x-model="editItemData.is_active" style="width:13px;height:13px;accent-color:var(--brand);">
                                    Visible
                                </label>
                                <div style="flex:1"></div>
                                <button @click="editItemId=null" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid var(--border);background:var(--surface-base);cursor:pointer;color:var(--text-2);">Cancel</button>
                                <button @click="saveEditItem(item)" :disabled="savingEdit||!editItemData.label.trim()"
                                        style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;"
                                        :style="(savingEdit||!editItemData.label.trim())?'opacity:.5':''">
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
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
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
        search: '', page: 1, perPage: 15,
        showAddModal: false, showEditModal: false,
        editMenu: { name: '', category: '', is_active: true },
        deleteTarget: null,
        allMenus: @json($menus),
        categories: @json($categoryOptions),

        // Items modal state
        showItemsModal: false,
        itemsMenu: null,
        items: [],
        loadingItems: false,
        itemError: '',
        newItem: { label: '', url: '', parent_id: '', newTab: false },
        savingItem: false,
        editItemId: null,
        editItemData: { label: '', url: '', target: '_self', parent_id: '', is_active: true },
        savingEdit: false,

        get filtered() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.allMenus;
            return this.allMenus.filter(m =>
                m.name.toLowerCase().includes(q) ||
                this.catLabel(m.category).toLowerCase().includes(q)
            );
        },
        get paginated() {
            return this.filtered.slice((this.page-1)*this.perPage, this.page*this.perPage);
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length/this.perPage)); },

        catLabel(s) { return this.categories.find(c => c.slug===s)?.label || s; },
        catStyle(s) {
            const c = this.categories.find(x => x.slug===s)?.color || '#475569';
            return 'background:'+c+'12;color:'+c+';border:1px solid '+c+'30';
        },
        openEdit(m) { this.editMenu = {...m}; this.showEditModal = true; },
        doDelete(m) {
            if (!confirm('Delete "'+m.name+'"?\nAll items inside will be deleted too.')) return;
            this.deleteTarget = m;
            this.$nextTick(() => this.$refs.deleteForm.submit());
        },

        // Items modal
        async openItems(menu) {
            this.itemsMenu = menu;
            this.items = [];
            this.editItemId = null;
            this.itemError = '';
            this.newItem = { label: '', url: '', parent_id: '', newTab: false };
            this.showItemsModal = true;
            this.loadingItems = true;
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
                const body = {
                    label: this.newItem.label.trim(),
                    url: this.newItem.url.trim() || null,
                    target: this.newItem.newTab ? '_blank' : '_self',
                    parent_id: this.newItem.parent_id || null,
                };
                const r = await fetch('/menus/'+this.itemsMenu.id+'/items', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(body),
                });
                const j = await r.json();
                if (j.success) {
                    this.items = j.items;
                    this.newItem = { label: '', url: '', parent_id: '', newTab: false };
                    this._syncCount(this.itemsMenu.id, j.items.length);
                } else {
                    this.itemError = j.message || 'Could not add item.';
                }
            } catch { this.itemError = 'Network error.'; }
            this.savingItem = false;
        },

        startEditItem(item) {
            this.editItemId = item.id;
            this.editItemData = { label: item.label, url: item.url || '', target: item.target || '_self', parent_id: item.parent_id || '', is_active: item.is_active };
        },

        async saveEditItem(item) {
            if (!this.editItemData.label.trim() || this.savingEdit) return;
            this.savingEdit = true;
            try {
                const body = {
                    label: this.editItemData.label.trim(),
                    url: this.editItemData.url.trim() || null,
                    target: this.editItemData.target,
                    parent_id: this.editItemData.parent_id || null,
                    is_active: this.editItemData.is_active ? 1 : 0,
                };
                const r = await fetch('/menus/'+this.itemsMenu.id+'/items/'+item.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(body),
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
                if (j.success) {
                    this.items = j.items;
                    this._syncCount(this.itemsMenu.id, j.items.length);
                }
            } catch {}
        },

        _syncCount(menuId, count) {
            const m = this.allMenus.find(x => x.id === menuId);
            if (m) m.all_items_count = count;
        },
    };
}
</script>
@endpush
