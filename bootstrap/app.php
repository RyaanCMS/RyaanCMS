<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\CheckInstalled::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        $middleware->alias([
            'ai.provider'    => \App\Http\Middleware\EnsureAIProvider::class,
            'project.owner'  => \App\Http\Middleware\EnsureProjectOwner::class,
            'subscribed'     => \App\Http\Middleware\CheckSubscription::class,
            'plan'           => \App\Http\Middleware\PlanGate::class,
            'central.auth'   => \App\Http\Middleware\CentralApiAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
