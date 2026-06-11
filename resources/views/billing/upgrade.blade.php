@extends('layouts.app')

@section('title', 'Upgrade Plan — RyaanCMS')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-white mb-2">Choose Your Plan</h1>
        <p class="text-gray-400">Upgrade anytime. Cancel anytime. No hidden fees.</p>
    </div>

    @if(session('warning'))
    <div class="mb-6 p-4 bg-yellow-900/40 border border-yellow-700 text-yellow-300 rounded-xl text-sm text-center">
        {{ session('warning') }}
    </div>
    @endif

    @php
    $featureLabels = [
        'ai_builder'        => 'AI App Builder',
        'blueprint'         => 'Blueprint Engine',
        'basic_kb'          => 'Knowledge Base',
        'premium_kb'        => 'Premium Knowledge Base',
        'pipeline_agents'   => '10-Agent Pipeline',
        'senior_dev_wisdom' => 'Senior Dev AI',
        'priority_routing'  => 'Priority AI Routing',
        'advanced_arch'     => 'Advanced Architecture',
        'white_label'       => 'White Label',
    ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        @php $isCurrent = $plan->key === $currentPlan; $isPro = $plan->key === 'pro'; @endphp
        <div class="relative flex flex-col bg-gray-900 border {{ $isPro ? 'border-indigo-500 ring-2 ring-indigo-500/30' : ($isCurrent ? 'border-emerald-700' : 'border-gray-800') }} rounded-2xl p-6">
            @if($isPro)
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 text-xs font-bold bg-indigo-600 rounded-full">Best Value</span>
            @endif
            @if($isCurrent)
            <span class="absolute -top-3 right-4 px-3 py-0.5 text-xs font-bold bg-emerald-700 rounded-full">Current Plan</span>
            @endif

            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">{{ $plan->name }}</p>
            <div class="mb-1">
                <span class="text-4xl font-bold text-white">${{ number_format($plan->price_monthly / 100) }}</span>
                <span class="text-gray-500 text-sm">/month</span>
            </div>
            <p class="text-gray-500 text-xs mb-5">Billed monthly via EPS</p>

            <ul class="space-y-2 mb-8 flex-1 text-sm">
                <li class="flex gap-2 text-gray-300">
                    <span class="text-emerald-400 mt-0.5 shrink-0">✓</span>
                    {{ number_format($plan->credits_per_month) }} AI credits/month
                </li>
                <li class="flex gap-2 text-gray-300">
                    <span class="text-emerald-400 mt-0.5 shrink-0">✓</span>
                    {{ $plan->max_projects === -1 ? 'Unlimited projects' : $plan->max_projects . ' projects' }}
                </li>
                @foreach($plan->features ?? [] as $feature)
                <li class="flex gap-2 text-gray-300">
                    <span class="text-emerald-400 mt-0.5 shrink-0">✓</span>
                    {{ $featureLabels[$feature] ?? $feature }}
                </li>
                @endforeach
            </ul>

            @if($isCurrent)
            <button disabled class="w-full py-2.5 bg-gray-800 text-gray-500 rounded-xl text-sm font-medium cursor-not-allowed">
                Current Plan
            </button>
            @else
            <form method="POST" action="{{ route('billing.initiate', $plan->key) }}">
                @csrf
                <button type="submit"
                    class="w-full py-2.5 {{ $isPro ? 'bg-indigo-600 hover:bg-indigo-500' : 'bg-gray-700 hover:bg-gray-600' }} text-white rounded-xl text-sm font-medium transition">
                    Upgrade to {{ $plan->name }}
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>

    <p class="text-center text-gray-600 text-sm mt-8">
        Questions? <a href="mailto:support@ryaancms.com" class="text-indigo-400 hover:underline">Contact support</a>
    </p>

</div>
@endsection
