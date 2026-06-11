@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-slate-800 mb-1">Welcome back</h2>
    <p class="text-slate-500 text-sm mb-8">Sign in to your account to continue</p>

    @if($errors->any())
    <div class="mb-5 px-4 py-3.5 rounded-xl flex items-start space-x-3"
         style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239;">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-3 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-slate-50 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   placeholder="you@example.com">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-slate-600">Password</label>
                <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Forgot password?</a>
            </div>
            <input type="password" name="password" required
                   class="w-full px-4 py-3 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-slate-50 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   placeholder="••••••••">
        </div>

        <div class="flex items-center">
            <input type="checkbox" id="remember" name="remember"
                   class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
            <label for="remember" class="ml-2 text-sm text-slate-500 select-none">Remember me for 30 days</label>
        </div>

        <button type="submit"
                class="w-full py-3.5 rounded-xl text-white font-bold text-sm transition-all hover:-translate-y-0.5 mt-2"
                style="background:var(--brand); box-shadow:0 6px 20px var(--brand-ring);">
            Sign In
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 ml-1">Create one free →</a>
    </p>

</div>
@endsection
