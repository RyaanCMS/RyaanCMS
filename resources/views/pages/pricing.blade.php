@extends('layouts.app')

@section('title', 'Pricing — RyaanCMS')

@section('content')
<div class="min-h-screen bg-gray-950 text-white">

    {{-- Hero --}}
    <div class="text-center pt-20 pb-12 px-4">
        <span class="inline-block px-3 py-1 text-xs font-semibold bg-indigo-900/50 text-indigo-300 border border-indigo-700/50 rounded-full mb-4">Simple, Transparent Pricing</span>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Start free. Scale when ready.</h1>
        <p class="text-gray-400 text-lg max-w-xl mx-auto">No credit card required for the 14-day trial. Cancel anytime.</p>
    </div>

    {{-- Plans --}}
    <div class="max-w-6xl mx-auto px-4 pb-24">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            @php
            $plans = \App\Models\Plan::active()->get();
            $highlights = [
                'free_trial' => ['color' => 'gray',   'badge' => '14 Days Free'],
                'starter'    => ['color' => 'sky',    'badge' => 'Most Popular'],
                'pro'        => ['color' => 'indigo', 'badge' => 'Best Value'],
                'enterprise' => ['color' => 'yellow', 'badge' => 'Full Power'],
            ];
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

            @foreach($plans as $plan)
            @php
                $hl = $highlights[$plan->key] ?? ['color' => 'gray', 'badge' => ''];
                $isPro = $plan->key === 'pro';
            @endphp
            <div class="relative flex flex-col bg-gray-900 border {{ $isPro ? 'border-indigo-500 ring-2 ring-indigo-500/30' : 'border-gray-800' }} rounded-2xl p-6">
                @if($hl['badge'])
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 text-xs font-bold bg-{{ $hl['color'] }}-600 rounded-full whitespace-nowrap">{{ $hl['badge'] }}</span>
                @endif

                <p class="text-{{ $hl['color'] === 'gray' ? 'gray-400' : $hl['color'].'-400' }} text-xs font-semibold uppercase tracking-widest mb-2">{{ $plan->name }}</p>
                <div class="mb-4">
                    <span class="text-4xl font-bold text-white">{{ $plan->formattedPrice() }}</span>
                    @if($plan->price_monthly > 0)
                    <span class="text-gray-500 text-sm ml-1">per month</span>
                    @endif
                </div>

                <ul class="space-y-2 mb-8 flex-1">
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $plan->credits_per_month === -1 ? 'Unlimited' : number_format($plan->credits_per_month) }} AI credits/mo
                    </li>
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $plan->max_projects === -1 ? 'Unlimited' : $plan->max_projects }} project{{ $plan->max_projects !== 1 ? 's' : '' }}
                    </li>
                    @foreach($plan->features ?? [] as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $featureLabels[$feature] ?? $feature }}
                    </li>
                    @endforeach
                </ul>

                @if($plan->key === 'free_trial')
                <a href="{{ route('register') }}"
                    class="block text-center py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition text-sm">
                    Start Free Trial
                </a>
                @else
                <a href="{{ auth()->check() ? route('billing.initiate', $plan->key) : route('register') }}"
                    class="block text-center py-2.5 {{ $isPro ? 'bg-indigo-600 hover:bg-indigo-500' : 'bg-gray-700 hover:bg-gray-600' }} text-white rounded-xl font-medium transition text-sm">
                    Get {{ $plan->name }}
                </a>
                @endif
            </div>
            @endforeach
        </div>

        {{-- FAQ --}}
        <div class="mt-20 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-center mb-8">Frequently Asked Questions</h2>
            @php
            $faqs = [
                ['q' => 'Do I need a credit card to start?', 'a' => 'No. Your 14-day free trial starts immediately with just your email and password. No payment details required.'],
                ['q' => 'What happens after the trial ends?', 'a' => 'You\'ll be prompted to upgrade. Your projects and data are kept safe — nothing is deleted.'],
                ['q' => 'Can I upgrade or downgrade anytime?', 'a' => 'Yes. You can switch plans at any time. Upgrades take effect immediately.'],
                ['q' => 'What are AI credits?', 'a' => 'Each AI generation uses credits. Building a full app uses more credits than a small edit. Credits reset every month.'],
            ];
            @endphp
            <div class="space-y-4">
                @foreach($faqs as $faq)
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <p class="font-semibold text-white mb-1">{{ $faq['q'] }}</p>
                    <p class="text-gray-400 text-sm">{{ $faq['a'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
