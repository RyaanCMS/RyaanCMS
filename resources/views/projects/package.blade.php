@extends('layouts.app')
@section('title', 'Package — '.$project->name)
@section('header', 'Package & Publish')

@section('header-actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('builder.show', $project) }}"
       class="text-sm px-3 py-1.5 rounded-lg transition-colors"
       style="color:var(--text-2); background:var(--hover-bg);">
        ← Back to Builder
    </a>
    @if(!empty($project->settings['package_file']))
    <a href="{{ route('projects.package.download', $project) }}"
       class="flex items-center space-x-2 text-sm px-4 py-2 rounded-xl font-semibold text-white transition-all hover:-translate-y-px"
       style="background:linear-gradient(135deg,#10b981,#06b6d4); box-shadow:0 4px 12px rgba(16,185,129,.3);">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>Download Package</span>
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    @if(session('success'))
    @php $msg = session('success'); @endphp
    <div class="px-5 py-4 rounded-xl" style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;">
        <p class="text-sm font-semibold">{{ $msg }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="px-5 py-4 rounded-xl" style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239;">
        @foreach($errors->all() as $e)
        <p class="text-sm">{{ $e }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Package Form ── --}}
        <div class="lg:col-span-2">
            <form action="{{ route('projects.package.build', $project) }}" method="POST"
                  class="rounded-2xl overflow-hidden"
                  style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);"
                  x-data="packageBuilder()">
                @csrf

                <div class="px-6 py-4 flex items-center space-x-3" style="border-bottom:1px solid var(--border);">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                         style="background:linear-gradient(135deg,#10b981,#06b6d4);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm" style="color:var(--text-1);">Build Package for Marketplace</h2>
                        <p class="text-xs" style="color:var(--text-3);">
                            Package <strong>{{ $project->name }}</strong> ({{ $files->count() }} files) into a distributable .zip
                        </p>
                    </div>
                </div>

                <div class="p-6 space-y-5">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Package Name *</label>
                            <input type="text" name="pkg_name" required
                                   value="{{ old('pkg_name', $project->name) }}"
                                   class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all"
                                   style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Version *</label>
                            <input type="text" name="version" required value="{{ old('version', '1.0.0') }}" placeholder="1.0.0"
                                   class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all"
                                   style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Description *</label>
                        <textarea name="description" required rows="2"
                                  class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 resize-none transition-all"
                                  style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">{{ old('description', $project->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Type *</label>
                            <select name="type" required
                                    class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all"
                                    style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                                @foreach(['plugin','application','theme','module','agent'] as $t)
                                <option value="{{ $t }}" {{ old('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Category *</label>
                            <select name="category" required
                                    class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all"
                                    style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                                @foreach($categories as $slug => $label)
                                <option value="{{ $slug }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Icon (emoji)</label>
                            <input type="text" name="icon" maxlength="4" placeholder="🔌"
                                   value="{{ old('icon', '🔌') }}"
                                   class="w-full px-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all text-2xl text-center"
                                   style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Icon Color</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="icon_color" value="{{ old('icon_color', '#6366f1') }}"
                                       class="w-10 h-10 rounded-lg border cursor-pointer"
                                       style="border-color:var(--border);">
                                <input type="text" x-ref="colorHex" value="{{ old('icon_color', '#6366f1') }}"
                                       class="flex-1 px-3 py-2.5 rounded-xl text-sm border outline-none font-mono"
                                       style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-2);">Price (USD, 0 = Free)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium" style="color:var(--text-3);">$</span>
                            <input type="number" name="price" min="0" step="0.01" value="{{ old('price', 0) }}"
                                   class="w-full pl-7 pr-3 py-2.5 rounded-xl text-sm border outline-none focus:ring-2 focus:ring-indigo-300 transition-all"
                                   style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                        </div>
                    </div>

                    {{-- Menu Items builder --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold" style="color:var(--text-2);">Sidebar Menu Items</label>
                            <button type="button" @click="addMenuItem()"
                                    class="text-xs px-2.5 py-1 rounded-lg font-medium transition-colors text-white"
                                    style="background:#6366f1;">+ Add Item</button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, idx) in menuItems" :key="idx">
                                <div class="flex items-center gap-2 p-3 rounded-xl" style="background:var(--hover-bg); border:1px solid var(--border);">
                                    <input type="text" x-model="item.label" placeholder="Label" required
                                           class="flex-1 px-2.5 py-1.5 rounded-lg text-xs border outline-none"
                                           style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                                    <input type="text" x-model="item.route" placeholder="/my-plugin"
                                           class="flex-1 px-2.5 py-1.5 rounded-lg text-xs border outline-none font-mono"
                                           style="background:var(--input-bg); border-color:var(--border); color:var(--text-1);">
                                    <input type="color" x-model="item.color"
                                           class="w-7 h-7 rounded cursor-pointer border"
                                           style="border-color:var(--border);">
                                    <button type="button" @click="menuItems.splice(idx,1)"
                                            class="text-red-400 hover:text-red-600 p-1 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <p x-show="menuItems.length === 0" class="text-xs text-center py-4" style="color:var(--text-3);">
                            No menu items — add one if your plugin needs a navigation entry.
                        </p>

                        <input type="hidden" name="menu_items" :value="JSON.stringify(menuItems)">
                    </div>

                    <button type="submit"
                            class="w-full flex items-center justify-center space-x-2 py-3 rounded-xl font-semibold text-sm text-white transition-all hover:-translate-y-px"
                            style="background:linear-gradient(135deg,#10b981,#06b6d4); box-shadow:0 4px 12px rgba(16,185,129,.3);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Build Package (.zip)</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Sidebar info ── --}}
        <div class="space-y-4">

            {{-- Files included --}}
            <div class="rounded-2xl overflow-hidden"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <div class="px-5 py-3 flex items-center justify-between" style="border-bottom:1px solid var(--border);">
                    <h3 class="text-xs font-bold" style="color:var(--text-1);">Files in Package</h3>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background:#eef2ff; color:#4338ca;">{{ $files->count() }}</span>
                </div>
                <div class="divide-y max-h-64 overflow-y-auto" style="border-color:var(--border);">
                    @forelse($files as $f)
                    <div class="flex items-center px-4 py-2 gap-2">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#94a3b8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-xs font-mono truncate" style="color:var(--text-2);">{{ $f->path }}</span>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-xs" style="color:var(--text-3);">
                        No files — use the builder to create files first.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Last built --}}
            @if(!empty($project->settings['package_manifest']))
            @php $pm = $project->settings['package_manifest']; @endphp
            <div class="rounded-2xl p-5"
                 style="background:var(--card-bg); border:1px solid var(--border); box-shadow:var(--shadow);">
                <h3 class="text-xs font-bold mb-3" style="color:var(--text-1);">Last Build</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color:var(--text-3);">Version</span>
                        <span class="text-xs font-mono font-semibold" style="color:var(--text-1);">{{ $pm['version'] ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color:var(--text-3);">License Key</span>
                        <button onclick="navigator.clipboard.writeText('{{ $pm['license_key'] ?? '' }}').then(()=>this.textContent='Copied!')"
                                class="text-xs font-mono truncate max-w-[120px] text-indigo-600 hover:underline">
                            {{ Str::limit($pm['license_key'] ?? '—', 12) }}…
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color:var(--text-3);">Built at</span>
                        <span class="text-xs" style="color:var(--text-2);">{{ isset($pm['built_at']) ? \Carbon\Carbon::parse($pm['built_at'])->diffForHumans() : '—' }}</span>
                    </div>
                </div>
                <a href="{{ route('projects.package.download', $project) }}"
                   class="mt-4 flex items-center justify-center space-x-2 py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
                   style="background:linear-gradient(135deg,#10b981,#06b6d4);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Download .zip</span>
                </a>
            </div>
            @endif

            {{-- Submit to marketplace --}}
            <div class="rounded-2xl p-5"
                 style="background:linear-gradient(135deg,#eef2ff,#ede9fe); border:1px solid #c7d2fe;">
                <h3 class="text-xs font-bold mb-1" style="color:#4338ca;">Ready to publish?</h3>
                <p class="text-xs mb-3" style="color:#6d28d9;">
                    After building your package, submit it to the marketplace so others can discover and install it.
                </p>
                <a href="{{ route('marketplace.my-items') }}"
                   class="block text-center py-2 rounded-xl text-xs font-semibold text-white"
                   style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    Go to My Marketplace Items →
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function packageBuilder() {
    return {
        menuItems: @json(old('menu_items') ? json_decode(old('menu_items'), true) : []),
        addMenuItem() {
            this.menuItems.push({ label: '', route: '', color: '#6366f1' });
        }
    };
}
</script>
@endpush
@endsection
