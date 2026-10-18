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
 * Every button of the live demo (/try). Each action does one thing that NightGuardian should (or should not) report.
 */
class DemoController extends Controller
{
    public function index(): View
    {
        Artisan::call('nightguardian:status');

        return view('try', [
            'status' => trim(Artisan::output()),
            'locked' => (string) config('demo.key') !== '',
            'burstMax' => (int) config('demo.burst_max'),
            'debug' => (bool) config('app.debug'),
            'limit' => (int) config('demo.try_rate_limit'),
        ]);
    }

    public function unlock(Request $request): RedirectResponse|Response
    {
        $key = (string) config('demo.key');

        if ($key !== '' && hash_equals($key, (string) $request->input('key'))) {
            $request->session()->regenerate();
            $request->session()->put('demo_unlocked', true);

            return redirect()->route('try');
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

        return $this->done('handled');
    }

    public function logs(): RedirectResponse
    {
        Log::error('Demo log error: the nightly export failed for customer {customer}', ['customer' => 'ACME-77']);
        Log::warning('Demo log warning: disk usage is at 91%');

        return $this->done('logs');
    }

    public function slowRequest(): RedirectResponse
    {
        usleep((int) ((float) config('demo.slow_request_seconds') * 1_000_000));

        return $this->done('slow_request');
    }

    public function slowQuery(): RedirectResponse
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $this->done('slow_query_skipped', ['driver' => DB::connection()->getDriverName()]);
        }

        DB::select('SELECT SLEEP(1.5) AS slept');

        return $this->done('slow_query');
    }

    public function job(): RedirectResponse
    {
        DemoFailingJob::dispatch();

        return $this->done('job');
    }

    public function runQueue(): RedirectResponse
    {
        Artisan::call('queue:work', ['--stop-when-empty' => true, '--tries' => 1, '--sleep' => 0]);

        return $this->done('queue', output: trim(Artisan::output()));
    }

    public function armSchedule(): RedirectResponse
    {
        Cache::put('demo.fail_schedule', true, 600);

        return $this->done('arm');
    }

    public function runScheduler(): RedirectResponse
    {
        Artisan::call('schedule:run');

        return $this->done('scheduler', output: trim(Artisan::output()));
    }

    public function burst(Request $request): RedirectResponse
    {
        $max = (int) config('demo.burst_max');
        $count = max(1, min($max, (int) $request->input('count', 10)));

        for ($i = 0; $i < $count; $i++) {
            $this->failTheSameWay();
        }

        return $this->done('burst', ['count' => $count]);
    }

    public function deploy(): RedirectResponse
    {
        Artisan::call('nightguardian:deploy', ['--release' => 'demo-'.date('Ymd-His'), '--deployer' => 'demo app']);

        return $this->done('deploy', output: trim(Artisan::output()));
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
        return redirect()->route('try');
    }

    private function failTheSameWay(): void
    {
        try {
            throw new RuntimeException('Demo burst error: the queue backend refused the connection');
        } catch (Throwable $e) {
            NightGuardian::report($e);
        }
    }

    /**
     * Flashes the result shown on /try: a title and a short explanation in the visitor's language
     * (or the output of the command that was run).
     *
     * @param  array<string, string|int>  $replace
     */
    private function done(string $key, array $replace = [], string $output = ''): RedirectResponse
    {
        /** @var array{0: string, 1: string} $texts */
        $texts = __('site.try.results.'.$key);

        $fill = static fn (string $text): string => str_replace(
            array_map(static fn (string $name): string => ':'.$name, array_keys($replace)),
            array_map('strval', array_values($replace)),
            $text,
        );

        return redirect()->route('try')->with('result', [
            'title' => $fill($texts[0]),
            'body' => $output !== '' ? $output : $fill($texts[1]),
        ]);
    }
}
