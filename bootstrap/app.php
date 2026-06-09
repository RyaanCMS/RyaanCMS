<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
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
            'ai.provider' => \App\Http\Middleware\EnsureAIProvider::class,
            'project.owner' => \App\Http\Middleware\EnsureProjectOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Flat cPanel deployment: public_html/ is both the app root and the document root.
// No public/ subdirectory exists, so public_path() must return basePath directly.
if (!is_dir(dirname(__DIR__).'/public')) {
    $app->usePublicPath('');
    $app->instance('path.public', dirname(__DIR__));
}

return $app;
