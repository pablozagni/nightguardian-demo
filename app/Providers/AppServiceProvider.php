<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        // The live demo (/try) is open to everybody: a limit per IP keeps it from being abused.
        RateLimiter::for('demo-try', fn (Request $request) => Limit::perMinute(max(1, (int) config('demo.try_rate_limit')))
            ->by((string) $request->ip())
            ->response(fn (Request $request, array $headers) => response()->view('too-many', [], 429, $headers)));
    }
}
