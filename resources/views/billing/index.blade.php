@extends('layouts.app')

@section('title', 'Billing — RyaanCMS')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Billing & Subscription</h1>
        <p class="text-gray-400 mt-1">Manage your plan, usage, and payments.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-900/40 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div class="mb-6 p-4 bg-yellow-900/40 border border-yellow-700 text-yellow-300 rounded-xl text-sm">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-900/40 border border-rose-700 text-rose-300 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    {{-- Current Plan Card --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Current Plan</p>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-white">{{ $plan ? $plan->name : 'Free Trial' }}</h2>
                    @if($isOnTrial)
                    <span class="px-2 py-0.5 text-xs font-medium bg-yellow-900/50 text-yellow-300 border border-yellow-700/50 rounded-full">
                        {{ $trialDays }} day{{ $trialDays !== 1 ? 's' : '' }} left in trial
                    </span>
                    @elseif($subscription && $subscription->status === 'active')
                    <span class="px-2 py-0.5 text-xs font-medium bg-emerald-900/50 text-emerald-300 border border-emerald-700/50 rounded-full">Active</span>
                    @elseif(!$subscription || $subscription->isExpired())
                    <span class="px-2 py-0.5 text-xs font-medium bg-rose-900/50 text-rose-300 border border-rose-700/50 rounded-full">Expired</span>
                    @endif
                </div>

                @if($isOnTrial)
                <p class="text-gray-400 text-sm mt-1">Trial ends {{ $subscription->trial_ends_at->format('M d, Y') }}. Upgrade to keep full access.</p>
                @elseif($subscription && $subscription->status === 'active' && $subscription->current_period_end)
                <p class="text-gray-400 text-sm mt-1">Renews {{ $subscription->current_period_end->format('M d, Y') }} · {{ $subscription->daysUntilRenewal() }} days remaining</p>
                @endif
            </div>
            <a href="{{ route('billing.upgrade') }}"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition">
                {{ $subscription && $subscription->status === 'active' ? 'Change Plan' : 'Upgrade Plan' }}
            </a>
        </div>
    </div>

    {{-- Usage stats --}}
    @if($plan)
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">AI Credits / Month</p>
            <p class="text-2xl font-bold text-indigo-400">{{ number_format($plan->credits_per_month) }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Max Projects</p>
            <p class="text-2xl font-bold text-sky-400">{{ $plan->max_projects === -1 ? '∞' : $plan->max_projects }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Monthly Price</p>
            <p class="text-2xl font-bold text-emerald-400">{{ $plan->formattedPrice() }}</p>
        </div>
    </div>
    @endif

    {{-- Features --}}
    @if($plan && $plan->features)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Included Features</h3>
        @php
        $featureLabels = [
            'ai_builder'        => ['label' => 'AI App Builder',       'icon' => '⚡'],
            'blueprint'         => ['label' => 'Blueprint Engine',      'icon' => '🗺️'],
            'basic_kb'          => ['label' => 'Knowledge Base',        'icon' => '📚'],
            'premium_kb'        => ['label' => 'Premium Knowledge Base','icon' => '🧠'],
            'pipeline_agents'   => ['label' => '10-Agent Pipeline',     'icon' => '🤖'],
            'senior_dev_wisdom' => ['label' => 'Senior Dev AI',         'icon' => '👨‍💻'],
            'priority_routing'  => ['label' => 'Priority AI Routing',   'icon' => '🚀'],
            'advanced_arch'     => ['label' => 'Advanced Architecture', 'icon' => '🏗️'],
            'white_label'       => ['label' => 'White Label',           'icon' => '🏷️'],
        ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($plan->features as $feature)
            @php $fl = $featureLabels[$feature] ?? ['label' => $feature, 'icon' => '✓']; @endphp
            <div class="flex items-center gap-2 text-sm text-gray-300">
                <span class="text-emerald-400">✓</span>
                {{ $fl['label'] }}
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Danger zone --}}
    @if($subscription && in_array($subscription->status, ['active', 'trial']))
    <div class="bg-gray-900 border border-rose-900/40 rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-rose-400 uppercase tracking-wider mb-2">Cancel Subscription</h3>
        <p class="text-gray-400 text-sm mb-4">Cancelling keeps access until the end of your current period. Your data is never deleted.</p>
        <form method="POST" action="{{ route('billing.cancel') }}"
            onsubmit="return confirm('Cancel your subscription?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-rose-900/40 hover:bg-rose-800/50 text-rose-300 border border-rose-800 rounded-lg text-sm transition">
                Cancel Subscription
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
