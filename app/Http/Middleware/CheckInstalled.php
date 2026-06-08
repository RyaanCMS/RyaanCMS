<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstalled
{
    public function handle(Request $request, Closure $next)
    {
        $installed = file_exists(storage_path('app/.installed'));

        if (!$installed && !$request->is('install*')) {
            return redirect('/install');
        }

        if ($installed && $request->is('install*')) {
            return redirect('/');
        }

        return $next($request);
    }
}
