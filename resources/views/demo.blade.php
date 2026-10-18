<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>NightGuardian demo</title>
    @include('partials.styles')
</head>
<body>
<main>
    <header>
        <h1><span aria-hidden="true">🌙</span> NightGuardian demo</h1>
        <p class="lead">Every button below makes this app do one thing that <a href="https://github.com/pablozagni/nightguardian">NightGuardian</a>
            should report (or should deliberately <em>not</em> report). Press it, then look at your collector.</p>
    </header>

    @if (session('result'))
        <div class="flash" role="status">
            <strong>{{ session('result')['title'] }}</strong>
            <pre>{{ session('result')['body'] }}</pre>
        </div>
    @endif

    @if ($errors->any())
        <div class="flash" role="status">
            <strong>Validation failed (Laravel answered with a normal validation error)</strong>
            <pre>{{ implode("\n", $errors->all()) }}</pre>
        </div>
    @endif

    @unless ($debug === false)
        <div class="warn">
            <code>APP_DEBUG</code> is <code>true</code>: Laravel shows its own debug page, so the friendly page with the reference code
            will not appear. Set <code>APP_DEBUG=false</code> in <code>.env</code>.
        </div>
    @endunless

    <h2>Errors that should arrive</h2>
    <div class="grid">
        <section class="card">
            <h3>1. Unhandled exception</h3>
            <p>Throws an exception nobody catches. Laravel answers <strong>500</strong> and NightGuardian shows its own page with a
                reference code (<code>NG-XXXX-XXXX</code>) and a "what were you doing?" box.</p>
            <p class="expect">Collector: an exception issue; after sending the comment, a feedback entry on it.</p>
            <a class="button danger" href="{{ route('demo.exception') }}">Throw an exception</a>
        </section>

        <section class="card">
            <h3>2. Handled error with context</h3>
            <p>Catches an exception and reports it with <code>NightGuardian::report($e, [...])</code>. The page keeps working.</p>
            <p class="expect">Collector: an exception marked as handled, with <code>order_id</code>, <code>gateway</code> and <code>attempt</code> as context.</p>
            <form method="post" action="{{ route('demo.handled') }}">@csrf<button class="button">Report a handled error</button></form>
        </section>

        <section class="card">
            <h3>3. Log lines</h3>
            <p>Calls <code>Log::error()</code> and <code>Log::warning()</code>.</p>
            <p class="expect">Collector: the error as a log issue. The warning arrives only if <code>NIGHTGUARDIAN_LOG_LEVEL=warning</code>.</p>
            <form method="post" action="{{ route('demo.logs') }}">@csrf<button class="button">Write the logs</button></form>
        </section>

        <section class="card">
            <h3>4. Error in a JSON request</h3>
            <p>Calls an endpoint asking for JSON. The body is Laravel's own JSON; NightGuardian only adds the
                <code>X-NightGuardian-Reference</code> header (an API client must never get an HTML page).</p>
            <p class="expect">Collector: an exception issue. Below: the status and the header.</p>
            <button class="button" type="button" id="json-button">Call the JSON endpoint</button>
            <pre id="json-result" class="result" hidden></pre>
        </section>

        <section class="card">
            <h3>5. Slow request</h3>
            <p>Takes about 2.5 s to answer (threshold: 2 s, <code>NIGHTGUARDIAN_SLOW_REQUEST_MS</code>).</p>
            <p class="expect">Collector: a slow request event.</p>
            <form method="post" action="{{ route('demo.slow-request') }}">@csrf<button class="button">Make a slow request</button></form>
        </section>

        <section class="card">
            <h3>6. Slow query</h3>
            <p>Runs <code>SELECT SLEEP(1.5)</code> (threshold: 1 s, <code>NIGHTGUARDIAN_SLOW_QUERY_MS</code>).</p>
            <p class="expect">Collector: a slow query event with the SQL.</p>
            <form method="post" action="{{ route('demo.slow-query') }}">@csrf<button class="button">Run a slow query</button></form>
        </section>

        <section class="card">
            <h3>7. Failing job</h3>
            <p>Queues a job that always fails (database queue). Then process the queue: press the second button, or run
                <code>php artisan queue:work --once</code> in a console.</p>
            <p class="expect">Collector: a failed job with its queue and attempts.</p>
            <div class="row">
                <form method="post" action="{{ route('demo.job') }}">@csrf<button class="button">Queue the job</button></form>
                <form method="post" action="{{ route('demo.run-queue') }}">@csrf<button class="button ghost">Process the queue now</button></form>
            </div>
        </section>

        <section class="card">
            <h3>8. Failing scheduled task</h3>
            <p>Arms the command <code>demo:fail</code>, which fails the next time the scheduler runs. Run
                <code>php artisan schedule:work</code> in a console, or press the second button.</p>
            <p class="expect">Collector: a failed scheduled task.</p>
            <div class="row">
                <form method="post" action="{{ route('demo.arm-schedule') }}">@csrf<button class="button">Arm the failing task</button></form>
                <form method="post" action="{{ route('demo.run-scheduler') }}">@csrf<button class="button ghost">Run the scheduler now</button></form>
            </div>
        </section>

        <section class="card">
            <h3>9. Burst of identical errors</h3>
            <p>Reports the same error <em>N</em> times: one issue with N events. To try the alert threshold use 50 or more
                (default: 50 events in 60 minutes).</p>
            <p class="expect">Collector: one issue whose event count grows by N; a threshold alert once it passes the limit.</p>
            <form method="post" action="{{ route('demo.burst') }}" class="row">
                @csrf
                <label>N <input type="number" name="count" value="20" min="1" max="{{ $burstMax }}"></label>
                <button class="button">Send the burst</button>
            </form>
        </section>

        <section class="card">
            <h3>10. Mark a deploy</h3>
            <p>Runs <code>php artisan nightguardian:deploy</code> with a new release name. Issues can then be "resolved in release".</p>
            <p class="expect">Collector: a new row in Deploys.</p>
            <form method="post" action="{{ route('demo.deploy') }}">@csrf<button class="button">Mark a deploy</button></form>
        </section>
    </div>

    <h2>Errors that must <em>not</em> arrive</h2>
    <p class="lead">These are normal behaviour of an application, not bugs. NightGuardian ignores them on purpose: after pressing them,
        your collector must show nothing new.</p>
    <div class="grid">
        <section class="card">
            <h3>404 Not Found</h3>
            <p>Opens a page that does not exist.</p>
            <a class="button ghost" href="{{ route('demo.not-found') }}">Open a missing page</a>
        </section>

        <section class="card">
            <h3>Validation error</h3>
            <p>Sends an invalid form. Laravel redirects back with the errors.</p>
            <form method="post" action="{{ route('demo.validation') }}">@csrf<input type="hidden" name="email" value="not-an-email"><button class="button ghost">Submit an invalid form</button></form>
        </section>

        <section class="card">
            <h3>419 Page Expired</h3>
            <p>Posts a form without its CSRF token.</p>
            <form method="post" action="{{ route('demo.csrf') }}"><button class="button ghost">Submit without a token</button></form>
        </section>
    </div>

    <h2>SDK status</h2>
    <p class="lead">The output of <code>php artisan nightguardian:status</code>: whether the SDK is active, where it sends events and what is waiting in its disk spool.</p>
    <pre class="status">{{ $status }}</pre>

    <footer>
        <p>Events are sent <em>after</em> the response, so they show up in the collector a moment after you press a button.
            <a href="https://github.com/pablozagni/nightguardian-demo">Source and instructions</a> · MIT</p>
        @if ($locked)
            <p class="muted">This demo is protected with a key (<code>DEMO_KEY</code>).</p>
        @endif
    </footer>
</main>

<script>
    document.getElementById('json-button').addEventListener('click', async function () {
        const box = document.getElementById('json-result');
        box.hidden = false;
        box.textContent = 'Calling…';
        try {
            const response = await fetch(@json(route('demo.json')), {headers: {'Accept': 'application/json'}});
            const body = await response.text();
            box.textContent = 'HTTP ' + response.status + '\nX-NightGuardian-Reference: ' + (response.headers.get('X-NightGuardian-Reference') || '(none)') + '\n\n' + body.slice(0, 300);
        } catch (error) {
            box.textContent = 'The request failed: ' + error;
        }
    });
</script>
</body>
</html>
