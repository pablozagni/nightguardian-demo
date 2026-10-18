<?php

namespace App\Http\Controllers;

use App\Jobs\DemoFailingJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use NightGuardian\Laravel\Facades\NightGuardian;
use RuntimeException;
use Throwable;

/**
 * Every button of the demo page. Each action does one thing that NightGuardian should (or should not) report.
 */
class DemoController extends Controller
{
    public function index(): View
    {
        Artisan::call('nightguardian:status');

        return view('demo', [
            'status' => trim(Artisan::output()),
            'locked' => (string) config('demo.key') !== '',
            'burstMax' => (int) config('demo.burst_max'),
            'debug' => (bool) config('app.debug'),
        ]);
    }

    public function unlock(Request $request): RedirectResponse|Response
    {
        $key = (string) config('demo.key');

        if ($key !== '' && hash_equals($key, (string) $request->input('key'))) {
            $request->session()->regenerate();
            $request->session()->put('demo_unlocked', true);

            return redirect()->route('home');
        }

        return response()->view('unlock', ['wrong' => true], 401);
    }

    // --- must reach the collector -------------------------------------------------------------------------------

    /** An unhandled exception: Laravel answers 500 and NightGuardian shows its page with the reference code. */
    public function exception(): never
    {
        throw new RuntimeException('Demo unhandled exception: the invoice total could not be calculated');
    }

    /** An exception you catch yourself but still want to know about, with extra context. */
    public function handled(): RedirectResponse
    {
        try {
            throw new RuntimeException('Demo handled exception: the payment gateway timed out');
        } catch (Throwable $e) {
            NightGuardian::report($e, ['order_id' => 4821, 'gateway' => 'acme-pay', 'attempt' => 2]);
        }

        return $this->done('Handled error reported', 'NightGuardian::report() was called with the context order_id, gateway and attempt. The page did not break.');
    }

    public function logs(): RedirectResponse
    {
        Log::error('Demo log error: the nightly export failed for customer {customer}', ['customer' => 'ACME-77']);
        Log::warning('Demo log warning: disk usage is at 91%');

        return $this->done('Two log lines written', 'Log::error() and Log::warning() were called. Only the error arrives with the default level; the warning needs NIGHTGUARDIAN_LOG_LEVEL=warning.');
    }

    public function slowRequest(): RedirectResponse
    {
        usleep((int) ((float) config('demo.slow_request_seconds') * 1_000_000));

        return $this->done('Slow request finished', 'This request took a few seconds, above the 2 second threshold.');
    }

    public function slowQuery(): RedirectResponse
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $this->done('Slow query skipped', 'SLEEP() needs MySQL or MariaDB; this demo is running on '.DB::connection()->getDriverName().'.');
        }

        DB::select('SELECT SLEEP(1.5) AS slept');

        return $this->done('Slow query finished', 'SELECT SLEEP(1.5) ran, above the 1 second threshold.');
    }

    public function job(): RedirectResponse
    {
        DemoFailingJob::dispatch();

        return $this->done('Job queued', 'A job that always fails is waiting in the database queue. Run "php artisan queue:work --once" in a console, or press "Process the queue now".');
    }

    public function runQueue(): RedirectResponse
    {
        Artisan::call('queue:work', ['--stop-when-empty' => true, '--tries' => 1, '--sleep' => 0]);

        return $this->done('Queue processed', trim(Artisan::output()) ?: 'The queue was empty.');
    }

    public function armSchedule(): RedirectResponse
    {
        Cache::put('demo.fail_schedule', true, 600);

        return $this->done('Failing task armed', 'The scheduled command demo:fail will fail the next time the scheduler runs (run "php artisan schedule:work" or press "Run the scheduler now").');
    }

    public function runScheduler(): RedirectResponse
    {
        Artisan::call('schedule:run');

        return $this->done('Scheduler ran', trim(Artisan::output()) ?: 'Nothing was due.');
    }

    public function burst(Request $request): RedirectResponse
    {
        $max = (int) config('demo.burst_max');
        $count = max(1, min($max, (int) $request->input('count', 20)));

        for ($i = 0; $i < $count; $i++) {
            $this->failTheSameWay();
        }

        return $this->done("Burst of {$count} errors", "{$count} identical errors were reported: one issue with {$count} events. Use it to try the alert threshold (default 50 events in 60 minutes).");
    }

    public function deploy(): RedirectResponse
    {
        Artisan::call('nightguardian:deploy', ['--release' => 'demo-'.date('Ymd-His'), '--deployer' => 'demo app']);

        return $this->done('Deploy marked', trim(Artisan::output()));
    }

    // --- must NOT reach the collector ----------------------------------------------------------------------------

    public function notFound(): never
    {
        abort(404);
    }

    public function validation(Request $request): never
    {
        $request->validate(['email' => ['required', 'email']]);

        throw ValidationException::withMessages(['email' => 'Always invalid in the demo.']);
    }

    public function csrfExpired(): RedirectResponse
    {
        return redirect()->route('home');
    }

    private function failTheSameWay(): void
    {
        try {
            throw new RuntimeException('Demo burst error: the queue backend refused the connection');
        } catch (Throwable $e) {
            NightGuardian::report($e);
        }
    }

    private function done(string $title, string $body): RedirectResponse
    {
        return redirect()->route('home')->with('result', ['title' => $title, 'body' => $body]);
    }
}
