<?php

namespace App\Providers;

use App\Events\LoanApproved;
use App\Events\LoanCancelled;
use App\Events\LoanDelivered;
use App\Events\LoanRequested;
use App\Events\LoanReturned;
use App\Listeners\LoanActivityListener;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        Model::shouldBeStrict(! app()->isProduction());

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        // Grant all permissions to Superuser
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superuser') ? true : null;
        });

        Event::listen([
            LoanRequested::class,
            LoanApproved::class,
            LoanDelivered::class,
            LoanReturned::class,
            LoanCancelled::class,
        ], LoanActivityListener::class);
    }
}
