<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Central\CentralInstall;
use App\Models\Central\CentralLicense;
use App\Services\Billing\SubscriptionService;
use App\Services\Central\CreditManager;
use App\Services\Central\LicenseManager;
use App\Services\Central\TelemetryAggregator;
use Illuminate\Http\Request;

class HubController extends Controller
{
    public function __construct(
        private LicenseManager $licenseManager,
        private CreditManager $creditManager,
        private TelemetryAggregator $telemetry,
        private SubscriptionService $subscriptionService,
    ) {}

    public function index()
    {
        $stats        = $this->licenseManager->stats();
        $creditStats  = $this->creditManager->globalStats();
        $telSummary   = $this->telemetry->summary(30);
        $recentPings  = CentralInstall::orderByDesc('last_ping')->limit(20)->get();
        $revenue      = $this->subscriptionService->revenueStats();

        return view('admin.hub.index', compact(
            'stats', 'creditStats', 'telSummary', 'recentPings', 'revenue'
        ));
    }

    public function licenses(Request $request)
    {
        $query = CentralLicense::query();

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('license_key', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('domain', 'like', "%{$s}%"));
        }

        $licenses = $query->orderByDesc('created_at')->paginate(25);
        return view('admin.hub.licenses', compact('licenses'));
    }

    public function issueLicense(Request $request)
    {
        $request->validate([
            'plan'             => 'required|in:free,starter,pro,enterprise',
            'email'            => 'required|email',
            'name'             => 'required|string|max:255',
            'credits_included' => 'required|integer|min:0',
            'expires_at'       => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $license = $this->licenseManager->issue($request->plan, [
            'email'            => $request->email,
            'name'             => $request->name,
            'credits_included' => (int) $request->credits_included,
            'expires_at'       => $request->expires_at,
            'notes'            => $request->notes,
        ]);

        return back()->with('success', "License issued: {$license->license_key}");
    }

    public function suspendLicense(Request $request, string $key)
    {
        $this->licenseManager->suspend($key, $request->input('reason', ''));
        return back()->with('success', "License {$key} suspended.");
    }

    public function activateLicense(string $key)
    {
        $this->licenseManager->activate($key);
        return back()->with('success', "License {$key} activated.");
    }

    public function topupCredits(Request $request, string $key)
    {
        $request->validate(['amount' => 'required|integer|min:1']);
        $this->creditManager->topup($key, (int) $request->amount, 'topup', 'Admin top-up');
        return back()->with('success', "Added {$request->amount} credits to {$key}.");
    }

    public function telemetry()
    {
        $summary7   = $this->telemetry->summary(7);
        $summary30  = $this->telemetry->summary(30);
        return view('admin.hub.telemetry', compact('summary7', 'summary30'));
    }

    public function installs(Request $request)
    {
        $query = CentralInstall::with('license');

        if ($request->filled('stale')) {
            $query->where('last_ping', '<', now()->subDays(30));
        }

        $installs = $query->orderByDesc('last_ping')->paginate(30);
        return view('admin.hub.installs', compact('installs'));
    }
}
