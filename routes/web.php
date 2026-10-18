<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// --- the public site -----------------------------------------------------------------------------------------------

Route::get('/', [LandingController::class, 'home'])->name('home');
Route::get('/{locale}', [LandingController::class, 'localized'])->whereIn('locale', ['en', 'es'])->name('landing');
Route::get('/sitemap.xml', [LandingController::class, 'sitemap'])->name('sitemap');

// --- the live demo ---------------------------------------------------------------------------------------------------

Route::post('/unlock', [DemoController::class, 'unlock'])->middleware('throttle:5,1')->name('unlock');

Route::middleware('demo.key')->group(function () {
    Route::get('/try', [DemoController::class, 'index'])->name('try');

    // Every action counts against the per-IP limit (DEMO_TRY_RATE_LIMIT per minute).
    Route::middleware('throttle:demo-try')->group(function () {
        // Errors that must reach the collector
        Route::get('/demo/exception', [DemoController::class, 'exception'])->name('demo.exception');
        Route::post('/demo/handled', [DemoController::class, 'handled'])->name('demo.handled');
        Route::post('/demo/logs', [DemoController::class, 'logs'])->name('demo.logs');
        Route::get('/demo/json-error', [DemoController::class, 'exception'])->name('demo.json');
        Route::post('/demo/slow-request', [DemoController::class, 'slowRequest'])->name('demo.slow-request');
        Route::post('/demo/slow-query', [DemoController::class, 'slowQuery'])->name('demo.slow-query');
        Route::post('/demo/job', [DemoController::class, 'job'])->name('demo.job');
        Route::post('/demo/run-queue', [DemoController::class, 'runQueue'])->name('demo.run-queue');
        Route::post('/demo/arm-schedule', [DemoController::class, 'armSchedule'])->name('demo.arm-schedule');
        Route::post('/demo/run-scheduler', [DemoController::class, 'runScheduler'])->name('demo.run-scheduler');
        Route::post('/demo/burst', [DemoController::class, 'burst'])->name('demo.burst');
        Route::post('/demo/deploy', [DemoController::class, 'deploy'])->name('demo.deploy');

        // Errors that must NOT reach the collector
        Route::get('/demo/not-found', [DemoController::class, 'notFound'])->name('demo.not-found');
        Route::post('/demo/validation', [DemoController::class, 'validation'])->name('demo.validation');
        Route::post('/demo/csrf-expired', [DemoController::class, 'csrfExpired'])->name('demo.csrf');
    });
});
