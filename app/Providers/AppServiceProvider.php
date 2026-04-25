<?php

namespace App\Providers;

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
        // Grant all permissions to Superuser
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('superuser') ? true : null;
        });

        \Illuminate\Support\Facades\Event::listen([
            \App\Events\LoanRequested::class,
            \App\Events\LoanApproved::class,
            \App\Events\LoanDelivered::class,
            \App\Events\LoanReturned::class,
        ], \App\Listeners\LoanActivityListener::class);
    }
}
