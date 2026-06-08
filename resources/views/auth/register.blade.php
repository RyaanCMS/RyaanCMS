@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-slate-800 mb-1">Create your account</h2>
    <p class="text-slate-500 text-sm mb-8">Start building AI-powered apps today — it's free</p>

    @if($errors->any())
    <div class="mb-5 px-4 py-3.5 rounded-xl" style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239;">
        <ul class="text-sm space-y-0.5">
            @foreach($errors->all() as $error)
            <li class="flex items-center space-x-2">
                <span class="w-1 h-1 rounded-full bg-rose-500 flex-shrink-0"></span>
                <span>{{ $error }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-3 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-slate-50 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   placeholder="Your full name">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-3 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-slate-50 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   placeholder="you@example.com">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
            <input type="password" name="password" required minlength="8"
                   class="w-full px-4 py-3 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-slate-50 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   placeholder="Minimum 8 characters">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Confirm Password</label>
            <input type="password" name="password_confirmation" required
                   class="w-full px-4 py-3 rounded-xl text-sm border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-slate-50 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   placeholder="Repeat password">
        </div>

        <button type="submit"
                class="w-full py-3.5 rounded-xl text-white font-bold text-sm transition-all hover:-translate-y-0.5 mt-2"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 6px 20px rgba(99,102,241,.3);">
            Create Account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 ml-1">Sign in →</a>
    </p>

    <p class="mt-4 text-center text-xs text-slate-400 leading-relaxed">
        By creating an account you agree to our<br>
        <a href="{{ route('terms') }}" class="text-indigo-500 hover:underline">Terms of Service</a>
        and
        <a href="{{ route('privacy') }}" class="text-indigo-500 hover:underline">Privacy Policy</a>
    </p>
</div>
@endsection
