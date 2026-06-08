<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProjectOwner
{
    public function handle(Request $request, Closure $next)
    {
        $project = $request->route('project');

        if ($project && $project->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have access to this project.');
        }

        return $next($request);
    }
}
