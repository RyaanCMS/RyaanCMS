<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $project->name }} — Launch</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin:0; font-family:'Inter',system-ui,sans-serif; }
        .doc-backdrop {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0f14 0%, #1a1025 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .doc-card {
            width: 100%;
            max-width: 680px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.08);
            overflow: hidden;
            animation: slideUp .25s cubic-bezier(.16,1,.3,1);
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(24px) scale(.98); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        .doc-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fafbff;
        }
        .doc-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg,#eef2ff,#ede9fe);
            border: 1px solid #c7d2fe;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .doc-meta { flex: 1; min-width: 0; }
        .doc-name {
            font-size: 15px; font-weight: 700;
            color: #1e293b; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .doc-sub {
            font-size: 12px; color: #94a3b8; margin-top: 2px;
            display: flex; align-items: center; gap: 8px;
        }
        .badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 11px; font-weight: 600;
        }
        .badge-purple { background:#eef2ff; color:#6366f1; border:1px solid #c7d2fe; }
        .badge-green  { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
        .badge-gray   { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
        .doc-close {
            width: 32px; height: 32px; border-radius: 8px;
            border: 1px solid #e2e8f0; background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #94a3b8; flex-shrink: 0;
            text-decoration: none; font-size: 18px; line-height: 1;
            transition: all .15s;
        }
        .doc-close:hover { background:#fee2e2; border-color:#fca5a5; color:#dc2626; }
        .doc-body { padding: 24px; }
        .doc-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: #94a3b8; margin-bottom: 8px;
        }
        .doc-textarea {
            width: 100%; box-sizing: border-box;
            border: 2px solid #e2e8f0; border-radius: 12px;
            padding: 14px 16px; font-size: 14px; line-height: 1.7;
            color: #1e293b; resize: none; outline: none;
            transition: border-color .15s;
            font-family: inherit;
        }
        .doc-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .doc-textarea::placeholder { color: #cbd5e1; }
        .chips-row {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px;
        }
        .chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 8px; font-size: 12px;
            border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;
            cursor: pointer; transition: all .15s;
        }
        .chip:hover, .chip.active {
            background: #eef2ff; color: #6366f1; border-color: #c7d2fe;
        }
        .suggestions-row {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px;
        }
        .suggestion {
            padding: 5px 12px; border-radius: 99px; font-size: 11.5px;
            border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;
            cursor: pointer; transition: all .15s;
        }
        .suggestion:hover { background:#eef2ff; color:#6366f1; border-color:#c7d2fe; }
        .doc-divider { height: 1px; background: #f1f5f9; margin: 20px 0 16px; }
        .doc-footer {
            padding: 16px 24px 20px;
            display: flex; align-items: center; justify-content: space-between;
            border-top: 1px solid #f1f5f9;
        }
        .doc-hint { font-size: 11.5px; color: #cbd5e1; }
        .doc-hint kbd {
            background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px;
            padding: 1px 5px; font-size: 11px; color: #64748b;
        }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600;
            border: 1px solid #ddd6fe; background: #f5f3ff; color: #7c3aed;
            cursor: pointer; text-decoration: none; transition: all .15s;
        }
        .btn-secondary:hover { background: #ede9fe; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 22px; border-radius: 11px; font-size: 13px; font-weight: 700;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            color: #fff; border: none; cursor: pointer; transition: all .15s;
            box-shadow: 0 4px 12px rgba(99,102,241,.3);
        }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(99,102,241,.4); transform: translateY(-1px); }
        .btn-primary:disabled {
            background: #e2e8f0; color: #94a3b8;
            box-shadow: none; cursor: not-allowed; transform: none;
        }
        .files-section { padding: 0 24px 20px; }
        .files-toggle {
            width: 100%; display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px; border-radius: 10px; font-size: 12.5px;
            border: 1px solid #e2e8f0; background: #fafbff; color: #64748b;
            cursor: pointer; transition: all .15s;
        }
        .files-toggle:hover { background: #f1f5f9; }
        .files-list {
            margin-top: 8px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;
        }
        .file-row {
            display: flex; align-items: center; padding: 8px 14px; font-size: 12px;
            color: #64748b; border-bottom: 1px solid #f8fafc;
        }
        .file-row:last-child { border-bottom: none; }
        /* Delete form inline */
        .del-form { display: inline; }
        .del-btn {
            font-size: 11px; padding: 3px 10px; border-radius: 6px;
            border: 1px solid #e2e8f0; background: none; color: #94a3b8;
            cursor: pointer; transition: all .12s;
        }
        .del-btn:hover { background:#fee2e2; border-color:#fca5a5; color:#dc2626; }
    </style>
</head>
<body>
<div class="doc-backdrop" x-data="launchApp()" @keydown.ctrl.enter.window="launch()" @keydown.meta.enter.window="launch()">

    <div class="doc-card">

        {{-- ── Document Header ────────────────────────────────────────── --}}
        <div class="doc-header">
            <div class="doc-icon">
                @switch($project->type)
                    @case('laravel')    ⚡ @break
                    @case('react')      ⚛️ @break
                    @case('nextjs')     ▲  @break
                    @case('ecommerce')  🛒 @break
                    @case('crm')        👥 @break
                    @case('saas')       🚀 @break
                    @case('hospital')   🏥 @break
                    @case('school')     🏫 @break
                    @case('restaurant') 🍽️ @break
                    @case('erp')        🏢 @break
                    @case('hrm')        👔 @break
                    @case('pos')        🖥️ @break
                    @default            🗂️
                @endswitch
            </div>
            <div class="doc-meta">
                <div class="doc-name">{{ $project->name }}</div>
                <div class="doc-sub">
                    <span class="badge badge-purple">{{ ucfirst($project->type) }}</span>
                    <span class="badge {{ $project->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                        {{ ucfirst($project->status) }}
                    </span>
                    <span>{{ $files->count() }} files</span>
                    <span>{{ $project->updated_at->diffForHumans() }}</span>
                </div>
            </div>
            <a href="{{ route('projects.index') }}" class="doc-close" title="Back to projects">×</a>
        </div>

        {{-- ── Document Body ───────────────────────────────────────────── --}}
        <div class="doc-body">

            {{-- Instruction textarea --}}
            <div class="doc-label">What do you want to build?</div>
            <textarea
                x-model="prompt"
                class="doc-textarea"
                rows="5"
                placeholder="Describe your app in detail... e.g. Build a complete Hospital Management System with patient registration, doctor appointments, billing module, and role-based access for Admin, Doctor, Nurse, and Patient."
                autofocus
            ></textarea>

            {{-- Build type chips --}}
            <div style="margin-top:16px;">
                <div class="doc-label">Build type</div>
                <div class="chips-row">
                    @foreach([
                        ['key'=>'full_stack',  'label'=>'Full Stack',    'icon'=>'⚡'],
                        ['key'=>'website',     'label'=>'Website',       'icon'=>'🌐'],
                        ['key'=>'application', 'label'=>'Application',   'icon'=>'📱'],
                        ['key'=>'api',         'label'=>'API Only',      'icon'=>'🔌'],
                        ['key'=>'plugin',      'label'=>'Plugin',        'icon'=>'🧩'],
                        ['key'=>'website_app', 'label'=>'Web + App',     'icon'=>'🌐'],
                    ] as $bt)
                    <button
                        type="button"
                        class="chip"
                        :class="buildType === '{{ $bt['key'] }}' ? 'active' : ''"
                        @click="buildType = '{{ $bt['key'] }}'">
                        <span>{{ $bt['icon'] }}</span>
                        <span>{{ $bt['label'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Suggestion chips --}}
            <div style="margin-top:16px;">
                <div class="doc-label" style="margin-bottom:6px;">Quick suggestions</div>
                <div class="suggestions-row">
                    @foreach([
                        'CRM system with leads, customers, deals, and sales pipeline',
                        'eCommerce store with products, cart, orders, and payment gateway',
                        'Hospital Management with patient registration, appointments, billing',
                        'School Management with students, attendance, fees, and exams',
                        'HRM system with employees, payroll, attendance, and leave management',
                        'Restaurant system with menu, orders, kitchen display, and billing',
                        'SaaS platform with multi-tenancy, subscriptions, and team management',
                        'Inventory system with warehouses, stock, purchase orders, and reports',
                    ] as $s)
                    <button type="button" class="suggestion" @click="prompt = '{{ addslashes($s) }}'">
                        {{ Str::limit($s, 45) }}
                    </button>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ── Document Footer ────────────────────────────────────────── --}}
        <div class="doc-footer">
            <div class="doc-hint">
                <kbd>Ctrl</kbd> + <kbd>Enter</kbd> to launch
                &nbsp;·&nbsp;
                <form class="del-form" method="POST" action="{{ route('projects.destroy', $project) }}"
                      onsubmit="return confirm('Delete {{ addslashes($project->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="del-btn">Delete project</button>
                </form>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <a href="{{ route('projects.themes', $project) }}" class="btn-secondary">
                    🎨 Themes
                </a>
                <a href="{{ route('projects.plugins', $project) }}" class="btn-secondary">
                    🧩 Plugins
                </a>
                <a href="{{ route('pipeline.show', $project) }}" class="btn-secondary">
                    🤖 Auto Build
                </a>
                <button type="button" class="btn-primary" :disabled="!prompt.trim()" @click="launch()">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Launch Builder
                </button>
            </div>
        </div>

        {{-- ── Existing files (collapsible) ──────────────────────────── --}}
        @if($files->count() > 0)
        <div class="files-section" x-data="{ open: false }">
            <button type="button" class="files-toggle" @click="open = !open">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    {{ $files->count() }} existing files
                </span>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     :style="open ? 'transform:rotate(180deg)' : ''" style="transition:.2s">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="files-list">
                @foreach($files->take(20) as $file)
                <div class="file-row">
                    <span style="margin-right:8px;">
                        @php
                        $ext = pathinfo($file->name, PATHINFO_EXTENSION);
                        echo match($ext) {
                            'php'   => '🐘',
                            'blade' => '🔷',
                            'js','jsx','ts','tsx' => '🟨',
                            'css','scss' => '🎨',
                            'html'  => '🌐',
                            'md'    => '📄',
                            'json'  => '📋',
                            'sql'   => '🗄️',
                            default => '📄',
                        };
                        @endphp
                    </span>
                    <span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $file->path }}</span>
                </div>
                @endforeach
                @if($files->count() > 20)
                <div style="padding:8px 14px; font-size:12px; color:#94a3b8; background:#f8fafc; text-align:center;">
                    +{{ $files->count() - 20 }} more files
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>{{-- /.doc-card --}}

</div>{{-- /.doc-backdrop --}}

{{-- Hidden launch form --}}
<form id="launchForm" method="GET" action="{{ route('builder.show', $project) }}" style="display:none">
    <input type="hidden" id="launchPrompt" name="prompt" value="">
    <input type="hidden" name="build_type" id="launchType" value="">
    <input type="hidden" name="autostart" value="1">
</form>

<script>
function launchApp() {
    return {
        prompt: '',
        buildType: 'full_stack',
        launch() {
            if (!this.prompt.trim()) return;
            document.getElementById('launchPrompt').value = this.prompt;
            document.getElementById('launchType').value   = this.buildType;
            document.getElementById('launchForm').submit();
        },
    };
}
</script>

</body>
</html>
