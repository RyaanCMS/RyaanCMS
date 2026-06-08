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

        $hasProvider = $user->aiProviders()->where('is_active', true)->exists();
        $hasEnvProvider = config('ai.providers.claude.api_key') || config('ai.providers.openai.api_key');

        if (!$hasProvider && !$hasEnvProvider) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'error'    => 'No AI provider configured yet. Go to Settings → AI Providers to add your API key.',
                    'redirect' => route('settings.index') . '#ai-providers',
                    'needs_provider' => true,
                ], 422);
            }

            return redirect()->route('settings.index')
                ->with('info', 'Add an AI provider API key in Settings to start using AI features.');
        }

        return $next($request);
    }
}
