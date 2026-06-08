<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $userId = $user->id;

        $stats = [
            'projects'    => Project::where('user_id', $userId)->count(),
            'ai_messages' => AIConversation::where('user_id', $userId)->sum('message_count'),
            'deployments' => Deployment::whereHas('project', fn($q) => $q->where('user_id', $userId))
                                ->where('status', 'success')->count(),
            'storage'     => Project::where('user_id', $userId)->sum('storage_used'),
        ];

        $recentProjects = Project::where('user_id', $userId)
            ->latest()
            ->limit(6)
            ->get();

        // Eager-load project to prevent N+1
        $recentActivity = AIConversation::where('user_id', $userId)
            ->with('project:id,name,slug,type')
            ->latest()
            ->limit(5)
            ->get();

        $recentDeployments = Deployment::with('project:id,name,slug')
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'recentProjects', 'recentActivity', 'recentDeployments'
        ));
    }
}
