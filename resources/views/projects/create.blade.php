@php
    $userId     = auth()->check() ? auth()->id() : null;
    $brandColor = \App\Models\Setting::get('branding.primary_color', '#6366f1', $userId);
    $fontFamily = \App\Models\Setting::get('branding.font_family',   'Inter',   $userId);
    $fontSlug   = strtolower(str_replace(' ', '+', $fontFamily));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New Project — RyaanCMS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ $fontSlug }}:300,400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand:       {{ $brandColor }};
            --brand-dark:  color-mix(in srgb, {{ $brandColor }} 80%, #000);
            --brand-light: color-mix(in srgb, {{ $brandColor }} 10%, #fff);
            --brand-ring:  color-mix(in srgb, {{ $brandColor }} 25%, transparent);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: '{{ $fontFamily }}', 'Inter', system-ui, sans-serif; }
        .backdrop {
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f4ff 0%, #f5f3ff 50%, #eef9f0 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
        }
        .card {
            width: 100%; max-width: 700px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 32px 80px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.06);
            overflow: hidden;
            animation: popIn .22s cubic-bezier(.16,1,.3,1);
        }
        @keyframes popIn {
            from { opacity:0; transform: translateY(20px) scale(.97); }
            to   { opacity:1; transform: translateY(0)    scale(1); }
        }
        /* ── Header ── */
        .card-header {
            padding: 20px 24px 18px;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(to right, #fafbff, #f5f3ff10);
            display: flex; align-items: center; gap: 14px;
        }
        .hdr-icon {
            width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
            background: var(--brand-light);
            border: 1px solid var(--brand-ring);
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .hdr-title { font-size: 16px; font-weight: 800; color: #1e293b; letter-spacing:-.01em; }
        .hdr-sub   { font-size: 12px; color: #94a3b8; margin-top: 2px; }
        .close-btn {
            margin-left: auto; width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
            border: 1px solid #e2e8f0; background: #fff; color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; cursor: pointer; text-decoration: none; transition: all .15s;
        }
        .close-btn:hover { background:#fee2e2; border-color:#fca5a5; color:#dc2626; }
        /* ── Body ── */
        .card-body { padding: 24px; }
        .field-label {
            display: block; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .07em;
            color: #64748b; margin-bottom: 7px;
        }
        .field-label span { color: #f43f5e; }
        .text-input, .sel-input, .txt-input {
            width: 100%; font-size: 14px; color: #1e293b; font-family: inherit;
            border: 2px solid #e2e8f0; border-radius: 11px;
            padding: 11px 14px; outline: none; transition: border-color .15s, box-shadow .15s;
            background: #fff;
        }
        .text-input:focus, .sel-input:focus, .txt-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px var(--brand-ring);
        }
        .txt-input { resize: none; }
        .sel-input { appearance: none; cursor: pointer; }
        /* Type grid */
        .type-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
        }
        .type-chip {
            display: flex; align-items: center; gap: 8px;
            padding: 9px 12px; border-radius: 10px; cursor: pointer;
            border: 2px solid #e2e8f0; background: #f8fafc;
            transition: all .15s; user-select: none;
        }
        .type-chip:hover { border-color: var(--brand-ring); background: var(--brand-light); }
        .type-chip.active { border-color: var(--brand); background: var(--brand-light); box-shadow: 0 0 0 3px var(--brand-ring); }
        .type-chip .chip-icon { font-size: 16px; flex-shrink: 0; }
        .type-chip .chip-text { font-size: 12.5px; font-weight: 600; color: #374151; }
        .type-chip.active .chip-text { color: var(--brand); }
        /* Prompt textarea */
        .prompt-area {
            width: 100%; font-size: 13.5px; font-family: inherit; color: #1e293b;
            border: 2px solid #e2e8f0; border-radius: 12px;
            padding: 13px 15px; resize: none; outline: none;
            line-height: 1.65; transition: border-color .15s, box-shadow .15s;
            background: #fafbff;
        }
        .prompt-area:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-ring); background: #fff; }
        .prompt-area::placeholder { color: #c4c9d4; }
        /* Suggestions */
        .suggest-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .suggest-chip {
            padding: 4px 11px; border-radius: 99px; font-size: 11.5px;
            border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;
            cursor: pointer; transition: all .14s;
        }
        .suggest-chip:hover { background: var(--brand-light); color: var(--brand); border-color: var(--brand-ring); }
        /* ── Footer ── */
        .card-footer {
            padding: 16px 24px 20px;
            border-top: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .hint { font-size: 11.5px; color: #cbd5e1; }
        .hint kbd {
            background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px;
            padding: 1px 5px; font-size: 11px; color: #64748b; font-family: inherit;
        }
        .btn-cancel {
            font-size: 13px; padding: 8px 16px; border-radius: 10px;
            border: 1px solid #e2e8f0; background: none; color: #64748b;
            cursor: pointer; transition: background .14s; text-decoration: none; display: inline-flex; align-items: center;
        }
        .btn-cancel:hover { background: #f8fafc; }
        .btn-create {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 22px; border-radius: 11px; font-size: 13.5px; font-weight: 700;
            background: color-mix(in srgb,var(--brand) 10%,#fff); color: var(--brand);
            border: 1.5px solid color-mix(in srgb,var(--brand) 28%,transparent);
            cursor: pointer; transition: all .15s;
        }
        .btn-create:hover { background: var(--brand); color: #fff; border-color: var(--brand); box-shadow: 0 4px 14px var(--brand-ring); transform: translateY(-1px); }
        .btn-create:disabled { background: #e2e8f0; color: #94a3b8; box-shadow: none; cursor: not-allowed; transform: none; }
        .spin { animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="backdrop" x-data="createApp()" @keydown.ctrl.enter.window="submit()" @keydown.meta.enter.window="submit()">

    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <div class="hdr-icon">✨</div>
            <div>
                <div class="hdr-title">Create New Project</div>
                <div class="hdr-sub">Fill in the details, then describe what you want to build</div>
            </div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('projects.index') }}"
               class="close-btn">×</a>
        </div>

        {{-- Errors --}}
        @if($errors->any())
        <div style="padding:12px 24px; background:#fef2f2; border-bottom:1px solid #fecaca;">
            @foreach($errors->all() as $e)
            <p style="font-size:12px; color:#dc2626; margin:2px 0;">• {{ $e }}</p>
            @endforeach
        </div>
        @endif

        <form id="createForm" method="POST" action="{{ route('projects.store') }}" @submit.prevent="submit()">
        @csrf

        <div class="card-body" style="display:flex; flex-direction:column; gap:20px;">

            {{-- Row 1: Name + Type --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label class="field-label">Project Name <span>*</span></label>
                    <input type="text" name="name" x-model="name" required
                           class="text-input"
                           placeholder="e.g. Hospital Management System"
                           value="{{ old('name') }}">
                </div>
                <div>
                    <label class="field-label">App Type <span>*</span></label>
                    <select name="type" x-model="appType" class="sel-input" required>
                        @foreach(config('ryaan.project_types', [
                            'laravel'    => ['label'=>'Laravel App'],
                            'react'      => ['label'=>'React App'],
                            'nextjs'     => ['label'=>'Next.js App'],
                            'ecommerce'  => ['label'=>'eCommerce'],
                            'crm'        => ['label'=>'CRM'],
                            'hrm'        => ['label'=>'HRM / HR'],
                            'erp'        => ['label'=>'ERP'],
                            'saas'       => ['label'=>'SaaS Platform'],
                            'hospital'   => ['label'=>'Hospital'],
                            'school'     => ['label'=>'School / ERP'],
                            'restaurant' => ['label'=>'Restaurant'],
                            'pos'        => ['label'=>'POS / Retail'],
                            'inventory'  => ['label'=>'Inventory'],
                            'api'        => ['label'=>'API Only'],
                        ]) as $key => $cfg)
                        <option value="{{ $key }}" {{ old('type','laravel') === $key ? 'selected' : '' }}>
                            {{ is_array($cfg) ? $cfg['label'] : $cfg }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Build type chips --}}
            <div>
                <label class="field-label">Build Type</label>
                <div class="type-grid">
                    @foreach([
                        ['key'=>'full_stack',  'icon'=>'⚡', 'label'=>'Full Stack'],
                        ['key'=>'website',     'icon'=>'🌐', 'label'=>'Website'],
                        ['key'=>'application', 'icon'=>'📱', 'label'=>'Application'],
                        ['key'=>'api',         'icon'=>'🔌', 'label'=>'API Only'],
                        ['key'=>'plugin',      'icon'=>'🧩', 'label'=>'Plugin'],
                        ['key'=>'website_app', 'icon'=>'🌐', 'label'=>'Web + App'],
                    ] as $bt)
                    <div class="type-chip" :class="buildType==='{{ $bt['key'] }}' ? 'active' : ''"
                         @click="buildType='{{ $bt['key'] }}'">
                        <span class="chip-icon">{{ $bt['icon'] }}</span>
                        <span class="chip-text">{{ $bt['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="build_type" :value="buildType">
            </div>

            {{-- What to build (main prompt) --}}
            <div>
                <label class="field-label">What do you want to build? <span>*</span></label>
                <textarea x-model="prompt" name="initial_prompt" rows="5" class="prompt-area" required
                          placeholder="Describe your app in detail... e.g. Build a complete Hospital Management System with patient registration, doctor appointments, billing, pharmacy, and role-based access for Admin, Doctor, Nurse, and Patient."></textarea>
                {{-- Suggestion chips --}}
                <div class="suggest-row">
                    @foreach([
                        'Complete CRM with leads, pipeline, activities, and sales reports',
                        'eCommerce with products, cart, orders, bKash/Nagad payment gateway',
                        'Hospital Management with patients, doctors, billing, and pharmacy',
                        'School ERP with students, attendance, exams, fees, and portal',
                        'HRM system with employees, payroll, attendance, and leave management',
                        'SaaS platform with multi-tenancy, subscriptions, and team management',
                        'Restaurant system with menu, orders, kitchen display, and billing',
                        'Inventory management with warehouses, purchase orders, and reports',
                    ] as $s)
                    <button type="button" class="suggest-chip"
                            @click="prompt = '{{ addslashes($s) }}'">{{ Str::limit($s, 44) }}</button>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="card-footer">
            <p class="hint"><kbd>Ctrl</kbd> + <kbd>Enter</kbd> to create</p>
            <div style="display:flex; align-items:center; gap:8px;">
                <a href="{{ route('projects.index') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-create" :disabled="submitting || !name.trim() || !prompt.trim()">
                    <template x-if="!submitting">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </template>
                    <template x-if="submitting">
                        <svg class="spin" width="14" height="14" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:.75"/>
                        </svg>
                    </template>
                    <span x-text="submitting ? 'Creating...' : 'Create & Launch'"></span>
                </button>
            </div>
        </div>

        </form>
    </div>
</div>

<script>
function createApp() {
    return {
        name:       '{{ old('name') }}',
        appType:    '{{ old('type', 'laravel') }}',
        buildType:  'full_stack',
        prompt:     '{{ old('initial_prompt') }}',
        submitting: false,

        submit() {
            if (!this.name.trim() || !this.prompt.trim()) return;
            this.submitting = true;
            document.getElementById('createForm').submit();
        },
    };
}
</script>
</body>
</html>
