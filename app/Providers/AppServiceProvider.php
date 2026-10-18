<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NightGuardian\Laravel\Console\DeployCommand;
use NightGuardian\Laravel\Console\StatusCommand;

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
        // The SDK registers its Artisan commands only when running in a console. The demo page runs two of them
        // (status and deploy) from a web request, so it registers them here.
        if (! $this->app->runningInConsole()) {
            $this->commands([StatusCommand::class, DeployCommand::class]);
        }
    }
}
