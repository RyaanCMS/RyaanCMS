<?php

namespace App\Http\Controllers;

use App\Models\ProjectWisdom;
use App\Services\AI\WisdomEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WisdomController extends Controller
{
    public function __construct(private WisdomEngine $wisdomEngine) {}

    public function index(Request $request)
    {
        try {
            $query = ProjectWisdom::query()
                ->where(function ($q) {
                    $q->where('user_id', Auth::id())->orWhereNull('user_id');
                })
                ->orderByDesc('confidence')
                ->orderByDesc('reuse_count');

            if ($request->domain && $request->domain !== 'all') {
                $query->where('domain', $request->domain);
            }
            if ($request->entry_type && $request->entry_type !== 'all') {
                $query->where('entry_type', $request->entry_type);
            }
            if ($request->search) {
                $q = '%' . $request->search . '%';
                $query->where(function ($sub) use ($q) {
                    $sub->where('lesson', 'like', $q)
                        ->orWhere('recommendation', 'like', $q)
                        ->orWhere('outcome', 'like', $q);
                });
            }

            $entries = $query->paginate(20)->withQueryString();
            $domains = ProjectWisdom::where('user_id', Auth::id())
                ->distinct('domain')->pluck('domain')->sort()->values();
            $needsMigration = false;
        } catch (\Throwable) {
            $entries        = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $domains        = collect();
            $needsMigration = true;
        }

        $stats = $this->wisdomEngine->getWisdomStats();

        return view('wisdom.index', compact('entries', 'stats', 'domains', 'needsMigration'));
    }

    public function show(ProjectWisdom $wisdom)
    {
        try {
            $this->authorizeEntry($wisdom);
            $wisdom->incrementReuse();
        } catch (\Throwable) {}
        return view('wisdom.show', compact('wisdom'));
    }

    public function destroy(ProjectWisdom $wisdom)
    {
        try {
            $this->authorizeEntry($wisdom);
            $wisdom->delete();
        } catch (\Throwable) {}
        return redirect()->route('wisdom.index')->with('success', 'Entry deleted.');
    }

    public function promote(ProjectWisdom $wisdom)
    {
        try {
            $this->authorizeEntry($wisdom);
            $wisdom->markAsRuleCandidate('Manual promotion by ' . Auth::user()->name);
        } catch (\Throwable) {}
        return back()->with('success', 'Entry marked as rule candidate.');
    }

    public function stats()
    {
        return response()->json($this->wisdomEngine->getWisdomStats());
    }

    private function authorizeEntry(ProjectWisdom $wisdom): void
    {
        if ($wisdom->user_id && $wisdom->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
