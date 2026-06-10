<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $userId = $user->id;

        $stats = Cache::remember("dashboard:stats:{$userId}", 60, function () use ($userId) {
            $deploySucc = Deployment::whereHas('project', fn($q) => $q->where('user_id', $userId))
                ->where('status', 'success')
                ->count();
            $deployTotal = Deployment::whereHas('project', fn($q) => $q->where('user_id', $userId))->count();

            return [
                'projects'          => Project::where('user_id', $userId)->count(),
                'ai_messages'       => AIConversation::where('user_id', $userId)->sum('message_count'),
                'ai_conversations'  => AIConversation::where('user_id', $userId)->count(),
                'deployments'       => $deploySucc,
                'deployments_total' => $deployTotal,
                'deploy_rate'       => $deployTotal > 0 ? round(($deploySucc / $deployTotal) * 100) : 0,
                'storage'           => Project::where('user_id', $userId)->sum('storage_used'),
                'modules_installed' => \App\Models\MarketplaceInstallation::where('user_id', $userId)->count(),
                'projects_week'     => Project::where('user_id', $userId)
                    ->where('created_at', '>=', now()->subWeek())->count(),
                'ai_week'           => AIConversation::where('user_id', $userId)
                    ->where('created_at', '>=', now()->subWeek())->count(),
            ];
        });

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
