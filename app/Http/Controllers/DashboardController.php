<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\Deployment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'projects'    => $user->projects()->count(),
            'ai_messages' => AIConversation::where('user_id', $user->id)->sum('message_count'),
            'deployments' => Deployment::whereHas('project', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'success')->count(),
            'storage'     => $user->projects()->sum('storage_used'),
        ];

        $recentProjects = $user->projects()
            ->latest()
            ->limit(6)
            ->get();

        $recentActivity = AIConversation::where('user_id', $user->id)
            ->with('project')
            ->latest()
            ->limit(5)
            ->get();

        $recentDeployments = Deployment::whereHas('project', fn($q) => $q->where('user_id', $user->id))
            ->with('project')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'recentProjects', 'recentActivity', 'recentDeployments'
        ));
    }
}
