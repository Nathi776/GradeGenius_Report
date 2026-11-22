<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ParentChildPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, ParentChildPolicy::class);

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });
    }
}
