@extends('layouts.app')
@section('title', $item->name)
@section('header', 'Marketplace')

@push('head')
<style>
.show-card { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow); }
.show-tag  { font-size:11px; font-weight:600; padding:3px 10px; border-radius:6px; background:var(--hover-bg); color:var(--text-2); border:1px solid var(--border); }
.show-meta-row { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px; }
.show-meta-row:last-child { border-bottom:none; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Back link --}}
    <a href="{{ route('marketplace.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text-3);text-decoration:none;margin-bottom:18px;transition:color .13s;"
       onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--text-3)'">
        ← Back to Marketplace
    </a>

    <div class="grid grid-cols-3 gap-6">

        {{-- Main Content --}}
        <div class="col-span-2 space-y-5">

            {{-- Hero card --}}
            <div class="show-card" style="border-left:3px solid var(--brand);">
                <div style="padding:24px 24px 20px;">
                    <div style="display:flex;align-items:flex-start;gap:18px;">
                        <div style="width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0;background:color-mix(in srgb,var(--brand) 10%,#fff);border:1px solid color-mix(in srgb,var(--brand) 20%,transparent);">
                            @switch($item->type)
                                @case('module') 📦 @break
                                @case('theme') 🎨 @break
                                @case('agent') 🤖 @break
                                @default 🔌
                            @endswitch
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h1 style="font-size:20px;font-weight:800;color:var(--text-1);margin:0 0 6px;letter-spacing:-.01em;">{{ $item->name }}</h1>
                            <p style="font-size:13.5px;color:var(--text-2);line-height:1.6;margin:0 0 12px;">{{ $item->description }}</p>
                            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
                                <span class="show-tag">{{ $item->category }}</span>
                                <span class="show-tag">{{ ucfirst($item->type) }}</span>
                                <span class="show-tag">v{{ $item->version }}</span>
                                <span style="font-size:11.5px;color:var(--text-3);">{{ number_format($item->downloads) }} downloads</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($item->long_description)
                <div style="padding:0 24px 24px;font-size:13.5px;color:var(--text-2);line-height:1.75;border-top:1px solid var(--border);padding-top:20px;margin-top:4px;">
                    {!! nl2br(e($item->long_description)) !!}
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Price + actions --}}
            <div class="show-card" style="padding:20px;">
                <div style="font-size:28px;font-weight:900;color:var(--text-1);letter-spacing:-.02em;margin-bottom:4px;">
                    {{ $item->price_formatted }}
                </div>
                <p style="font-size:12px;color:var(--text-3);margin-bottom:8px;">by {{ $item->developer->name ?? 'RyaanCMS' }}</p>
                <p style="font-size:12px;color:var(--text-3);line-height:1.5;margin-bottom:18px;">One purchase covers multiple project installs on this domain.</p>

                <button onclick="document.getElementById('installModal').classList.remove('hidden')"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px;border-radius:11px;font-size:13.5px;font-weight:700;background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);cursor:pointer;transition:all .15s;margin-bottom:10px;"
                        onmouseover="this.style.background='var(--brand)';this.style.color='#fff'"
                        onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)'">
                    Install in Project
                </button>

                @if($item->demo_url)
                <a href="{{ $item->demo_url }}" target="_blank" rel="noopener"
                   style="display:flex;align-items:center;justify-content:center;width:100%;padding:10px;border-radius:11px;font-size:13px;font-weight:600;background:var(--hover-bg);color:var(--text-2);border:1px solid var(--border);text-decoration:none;transition:all .13s;"
                   onmouseover="this.style.borderColor='var(--brand)';this.style.color='var(--brand)'"
                   onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-2)'">
                    View Demo →
                </a>
                @endif
            </div>

            {{-- Details --}}
            <div class="show-card" style="padding:18px 20px;">
                <p style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px;">Package Details</p>
                <dl>
                    @foreach([
                        ['Type',      ucfirst($item->type)],
                        ['Category',  $item->category],
                        ['Version',   'v' . $item->version],
                        ['Downloads', number_format($item->downloads)],
                    ] as [$label, $val])
                    <div class="show-meta-row">
                        <dt style="color:var(--text-3);">{{ $label }}</dt>
                        <dd style="color:var(--text-1);font-weight:600;">{{ $val }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</div>

{{-- Install Modal --}}
<div id="installModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);"
     onclick="if(event.target===this) this.classList.add('hidden')">
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:18px;padding:24px;width:100%;max-width:400px;box-shadow:0 24px 60px rgba(0,0,0,.18);">
        <h3 style="font-size:15px;font-weight:800;color:var(--text-1);margin-bottom:4px;">Install "{{ $item->name }}"</h3>
        <p style="font-size:12px;color:var(--text-3);margin-bottom:20px;">Select a project. Existing purchases are reused on this domain.</p>
        <form method="POST" action="{{ route('marketplace.install', $item) }}">
            @csrf
            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;">Project</label>
            <select name="project_id" required
                    style="width:100%;padding:10px 13px;border-radius:11px;font-size:13px;background:var(--hover-bg);border:1.5px solid var(--border);color:var(--text-1);outline:none;margin-bottom:18px;"
                    onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                <option value="">Choose a project…</option>
                @foreach(auth()->user()->projects()->get() as $proj)
                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                @endforeach
            </select>
            <div style="display:flex;gap:10px;">
                <button type="submit"
                        style="flex:1;padding:10px;border-radius:11px;font-size:13.5px;font-weight:700;background:color-mix(in srgb,var(--brand) 10%,#fff);color:var(--brand);border:1.5px solid color-mix(in srgb,var(--brand) 25%,transparent);cursor:pointer;transition:all .15s;"
                        onmouseover="this.style.background='var(--brand)';this.style.color='#fff'"
                        onmouseout="this.style.background='color-mix(in srgb,var(--brand) 10%,#fff)';this.style.color='var(--brand)'">
                    Install
                </button>
                <button type="button" onclick="document.getElementById('installModal').classList.add('hidden')"
                        style="flex:1;padding:10px;border-radius:11px;font-size:13px;font-weight:600;background:var(--hover-bg);color:var(--text-2);border:1px solid var(--border);cursor:pointer;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
