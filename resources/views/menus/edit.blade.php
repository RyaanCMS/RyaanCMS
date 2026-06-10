@extends('layouts.app')
@section('title', $menu->name)

@section('header-actions')
<a href="{{ route('menus.index') }}" style="font-size:13px;font-weight:600;color:var(--text-3);text-decoration:none;">← Back to Menus</a>
@endsection

@section('content')
<div x-data="menuEditor()">

    {{-- ── Top bar: menu settings ────────────────────────────────── --}}
    <form method="POST" action="{{ route('menus.update', $menu) }}"
          style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 18px;border-radius:14px;border:1px solid var(--border);background:var(--card-bg);margin-bottom:20px;box-shadow:var(--shadow);">
        @csrf @method('PUT')
        <h2 style="font-size:15px;font-weight:800;color:var(--text-1);margin:0;flex-shrink:0;">{{ $menu->name }}</h2>
        <svg style="width:14px;height:14px;color:var(--border);flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <input type="text" name="name" value="{{ old('name', $menu->name) }}" placeholder="Menu name"
               style="border-radius:9px;padding:7px 12px;font-size:13px;font-weight:600;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;width:180px;"
               onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
               required>
        <select name="category"
                style="border-radius:9px;padding:7px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
            @foreach($menuCategories as $cat)
            <option value="{{ $cat->slug }}" {{ old('category', $menu->category) === $cat->slug ? 'selected' : '' }}>{{ $cat->display_name }}</option>
            @endforeach
        </select>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-left:4px;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ $menu->is_active ? 'checked' : '' }} style="width:15px;height:15px;accent-color:var(--brand);cursor:pointer;">
            <span style="font-size:13px;font-weight:600;color:var(--text-2);">Active</span>
        </label>
        <button type="submit"
                style="margin-left:auto;padding:7px 18px;border-radius:9px;font-size:13px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;box-shadow:0 2px 6px var(--brand-ring);">
            Save
        </button>
    </form>

    {{-- ── Add item bar ──────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('menus.items.store', $menu) }}"
          style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;padding:14px 18px;border-radius:14px;border:1px solid var(--border);background:var(--card-bg);margin-bottom:20px;box-shadow:var(--shadow);">
        @csrf
        <div style="flex:1;min-width:140px;">
            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em;">Label *</label>
            <input type="text" name="label" placeholder="e.g. About, Contact"
                   style="width:100%;border-radius:9px;padding:8px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'"
                   required>
        </div>
        <div style="flex:2;min-width:160px;">
            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em;">URL</label>
            <input type="text" name="url" placeholder="/about  or  https://…"
                   style="width:100%;border-radius:9px;padding:8px 12px;font-size:13px;font-family:monospace;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
        </div>
        @if($allItems->isNotEmpty())
        <div style="min-width:130px;">
            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em;">Parent</label>
            <select name="parent_id"
                    style="width:100%;border-radius:9px;padding:8px 12px;font-size:13px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
                <option value="">Top level</option>
                @foreach($allItems as $item)
                <option value="{{ $item->id }}">{{ $item->label }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px;" x-data="{newTab:false}">
            <input type="hidden" name="target" :value="newTab ? '_blank' : '_self'">
            <div @click="newTab=!newTab" :style="newTab ? 'background:#22c55e' : 'background:#d1d5db'"
                 style="width:34px;height:18px;border-radius:99px;position:relative;cursor:pointer;transition:background .15s;flex-shrink:0;">
                <div style="position:absolute;top:2px;width:14px;height:14px;background:#fff;border-radius:99px;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .15s;" :style="newTab ? 'left:18px' : 'left:2px'"></div>
            </div>
            <span style="font-size:12.5px;font-weight:600;color:var(--text-2);white-space:nowrap;cursor:pointer;" @click="newTab=!newTab">New tab</span>
        </div>
        <button type="submit"
                style="padding:9px 20px;border-radius:9px;font-size:13px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;box-shadow:0 2px 6px var(--brand-ring);white-space:nowrap;">
            + Add
        </button>
    </form>

    {{-- ── Items list ────────────────────────────────────────────── --}}
    <div style="border-radius:14px;border:1px solid var(--border);background:var(--card-bg);overflow:hidden;box-shadow:var(--shadow);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-bottom:1px solid var(--border);">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:14px;font-weight:800;color:var(--text-1);">Menu Items</span>
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:var(--hover-bg);color:var(--text-3);border:1px solid var(--border);">{{ $tableItems->count() }}</span>
            </div>
        </div>

        @if($tableItems->isEmpty())
        <div style="text-align:center;padding:48px 20px;color:var(--text-3);">
            <svg style="width:36px;height:36px;margin:0 auto 10px;stroke:currentColor;opacity:.35" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/></svg>
            <p style="font-size:13.5px;font-weight:600;margin:0 0 3px;color:var(--text-2);">No items yet</p>
            <p style="font-size:12px;margin:0;">Use the form above to add your first link</p>
        </div>
        @else
        <div>
            @foreach($tableItems as $item)
            <div style="border-bottom:1px solid var(--border);">
                {{-- Item row --}}
                <div style="display:flex;align-items:center;gap:12px;padding:11px 18px;"
                     onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                    @if($item->parent_id)
                    <span style="width:20px;height:16px;flex-shrink:0;border-left:2px solid var(--border);border-bottom:2px solid var(--border);border-radius:0 0 0 4px;margin-left:12px;"></span>
                    @endif
                    <div style="flex:1;min-width:0;">
                        <span style="font-size:13.5px;font-weight:600;color:var(--text-1);">{{ $item->label }}</span>
                        @if($item->parent_id)
                        <span style="font-size:10.5px;color:var(--text-3);margin-left:6px;">↳ {{ $item->parent?->label }}</span>
                        @endif
                    </div>
                    <span style="font-size:12px;font-family:monospace;color:var(--text-3);flex-shrink:0;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item->url ?: '—' }}</span>
                    @if($item->target === '_blank')
                    <span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:99px;background:#f1f5f9;color:#64748b;flex-shrink:0;">new tab</span>
                    @endif
                    @if(!$item->is_active)
                    <span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:99px;background:#fef9c3;color:#a16207;flex-shrink:0;">hidden</span>
                    @endif
                    <div style="display:flex;gap:5px;flex-shrink:0;">
                        <button type="button"
                                @click="editingId = editingId === {{ $item->id }} ? null : {{ $item->id }}"
                                style="width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--border);background:var(--surface-base);cursor:pointer;color:var(--text-3);"
                                :style="editingId === {{ $item->id }} ? 'border-color:var(--brand);color:var(--brand);' : ''"
                                title="Edit">
                            <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('menus.items.destroy', [$menu, $item]) }}" onsubmit="return confirm('Delete this item?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1.5px solid #fecaca;background:#fef2f2;cursor:pointer;color:#ef4444;" title="Delete">
                                <svg style="width:12px;height:12px;stroke:currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Inline edit form --}}
                <div x-show="editingId === {{ $item->id }}" x-cloak
                     style="background:var(--surface-raised);border-top:1px solid var(--border);padding:14px 18px;">
                    <form method="POST" action="{{ route('menus.items.update', [$menu, $item]) }}"
                          style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px;">
                        @csrf @method('PUT')
                        <div style="flex:1;min-width:120px;">
                            <label style="display:block;font-size:10.5px;font-weight:700;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Label</label>
                            <input type="text" name="label" value="{{ $item->label }}" required
                                   style="width:100%;border-radius:8px;padding:6px 10px;font-size:12.5px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        </div>
                        <div style="flex:2;min-width:140px;">
                            <label style="display:block;font-size:10.5px;font-weight:700;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">URL</label>
                            <input type="text" name="url" value="{{ $item->url }}"
                                   style="width:100%;border-radius:8px;padding:6px 10px;font-size:12.5px;font-family:monospace;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                        </div>
                        <div style="min-width:120px;">
                            <label style="display:block;font-size:10.5px;font-weight:700;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Parent</label>
                            <select name="parent_id"
                                    style="width:100%;border-radius:8px;padding:6px 10px;font-size:12.5px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-1);outline:none;">
                                <option value="">Top level</option>
                                @foreach($allItems as $p)
                                @if($p->id !== $item->id)
                                <option value="{{ $p->id }}" {{ $item->parent_id == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:5px;padding-bottom:1px;">
                            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;">
                                <input type="hidden" name="target" value="_self">
                                <input type="checkbox" name="target" value="_blank" {{ $item->target === '_blank' ? 'checked' : '' }}
                                       style="width:13px;height:13px;accent-color:var(--brand);cursor:pointer;">
                                <span style="font-size:12px;font-weight:600;color:var(--text-2);">New tab</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}
                                       style="width:13px;height:13px;accent-color:var(--brand);cursor:pointer;">
                                <span style="font-size:12px;font-weight:600;color:var(--text-2);">Visible</span>
                            </label>
                        </div>
                        <div style="display:flex;gap:6px;padding-bottom:1px;">
                            <button type="button" @click="editingId=null"
                                    style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid var(--border);background:var(--surface-base);color:var(--text-2);cursor:pointer;">Cancel</button>
                            <button type="submit"
                                    style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;background:var(--brand);color:#fff;border:none;cursor:pointer;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function menuEditor() {
    return {
        editingId: null,
    };
}
</script>
@endpush
