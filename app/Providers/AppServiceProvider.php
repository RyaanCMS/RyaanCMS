<?php

namespace App\Providers;

use App\Models\Project;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Global $errors fallback so Blade views never crash with "Undefined variable $errors".
        // ShareErrorsFromSession middleware overwrites this with real session errors for every
        // normal web request; this only kicks in on exception-path rendering where the
        // middleware stack did not complete.
        View::share('errors', new ViewErrorBag());

        Gate::policy(Project::class, ProjectPolicy::class);

        // Global gate: admins can do anything
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) return true;
        });
    }
}
