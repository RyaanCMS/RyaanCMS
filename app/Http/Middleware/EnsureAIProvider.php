<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAIProvider
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) return $next($request);

        // Check if user has at least one AI provider configured
        $hasProvider = $user->aiProviders()->where('is_active', true)->exists();
        $hasEnvProvider = config('ai.providers.claude.api_key') || config('ai.providers.openai.api_key');

        if (!$hasProvider && !$hasEnvProvider) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'No AI provider configured. Please add an API key in Settings.',
                    'redirect'=> route('settings.index'),
                ], 422);
            }

            return redirect()->route('settings.index')
                ->with('warning', 'Please configure an AI provider API key to use the builder.');
        }

        return $next($request);
    }
}
