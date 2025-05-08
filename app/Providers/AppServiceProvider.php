<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\SecureSessionMiddleware;
use Illuminate\Http\Middleware\TrustProxies as LaravelTrustProxies;
use App\Http\Middleware\TrustProxies;

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
    public function boot()
    {
        // Prevent lazy loading
        Model::preventLazyLoading(!app()->isProduction());

        // Secure cookies
        $this->app->bind(
            StartSession::class,
            SecureSessionMiddleware::class
        );
        
        // Add security headers
        $this->app->bind(
            TrustProxies::class,
            TrustProxies::class
        );
    }
}
