@extends('layouts.app')

@section('title', 'Licenses — Central Hub')

@section('content')
<div class="min-h-screen bg-gray-950 text-white p-6">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.hub.index') }}" class="text-indigo-400 text-sm hover:underline">&larr; Hub</a>
            <h1 class="text-2xl font-bold mt-1">License Management</h1>
        </div>
        <button onclick="document.getElementById('issueModal').classList.remove('hidden')"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-sm font-medium transition">
            + Issue License
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-900/40 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search key, email, domain…"
            class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white w-64 focus:outline-none focus:border-indigo-500">
        <select name="plan" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            <option value="">All Plans</option>
            @foreach(['free','starter','pro','enterprise'] as $p)
            <option value="{{ $p }}" {{ request('plan') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            <option value="">All Status</option>
            @foreach(['active','suspended','expired','trial'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition">Filter</button>
    </form>

    {{-- Table --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-800/50">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">License Key</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Credits</th>
                    <th class="px-4 py-3">Domain</th>
                    <th class="px-4 py-3">Expires</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($licenses as $lic)
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3 font-mono text-xs text-indigo-300">{{ $lic->license_key }}</td>
                    <td class="px-4 py-3">
                        <p class="text-gray-200 text-xs font-medium">{{ $lic->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $lic->email }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $lic->plan === 'enterprise' ? 'bg-yellow-900/50 text-yellow-300' :
                               ($lic->plan === 'pro' ? 'bg-indigo-900/50 text-indigo-300' :
                               ($lic->plan === 'starter' ? 'bg-sky-900/50 text-sky-300' : 'bg-gray-800 text-gray-400')) }}">
                            {{ $lic->plan }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $lic->status === 'active' ? 'bg-emerald-900/50 text-emerald-300' :
                               ($lic->status === 'suspended' ? 'bg-rose-900/50 text-rose-300' : 'bg-gray-800 text-gray-400') }}">
                            {{ $lic->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <span class="text-white">{{ number_format($lic->credits_included - $lic->credits_used) }}</span>
                        <span class="text-gray-500"> / {{ number_format($lic->credits_included) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $lic->domain ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $lic->expires_at ? $lic->expires_at->format('Y-m-d') : '∞' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            {{-- Top-up --}}
                            <form method="POST" action="{{ route('admin.hub.topup', $lic->license_key) }}" class="flex gap-1">
                                @csrf
                                <input type="number" name="amount" placeholder="Credits" min="1"
                                    class="w-20 bg-gray-800 border border-gray-700 rounded px-2 py-1 text-xs text-white">
                                <button type="submit" class="px-2 py-1 bg-yellow-700 hover:bg-yellow-600 rounded text-xs">+</button>
                            </form>
                            {{-- Suspend / Activate --}}
                            @if($lic->status === 'active')
                            <form method="POST" action="{{ route('admin.hub.suspend', $lic->license_key) }}">
                                @csrf
                                <button type="submit" class="px-2 py-1 bg-rose-800 hover:bg-rose-700 rounded text-xs">Suspend</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.hub.activate', $lic->license_key) }}">
                                @csrf
                                <button type="submit" class="px-2 py-1 bg-emerald-800 hover:bg-emerald-700 rounded text-xs">Activate</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-600">No licenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-800">{{ $licenses->withQueryString()->links() }}</div>
    </div>

</div>

{{-- Issue License Modal --}}
<div id="issueModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 w-full max-w-lg">
        <h2 class="text-lg font-bold mb-4">Issue New License</h2>
        <form method="POST" action="{{ route('admin.hub.issue') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Plan</label>
                    <select name="plan" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                        @foreach(['free','starter','pro','enterprise'] as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Credits Included</label>
                    <input type="number" name="credits_included" value="1000" min="0" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Customer Name</label>
                <input type="text" name="name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" required>
            </div>
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Email</label>
                <input type="email" name="email" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" required>
            </div>
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Expires At (optional)</label>
                <input type="date" name="expires_at" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('issueModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm bg-gray-700 hover:bg-gray-600 rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-500 rounded-lg transition">Issue License</button>
            </div>
        </form>
    </div>
</div>
@endsection
