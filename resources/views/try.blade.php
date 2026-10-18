@extends('layouts.site', [
    'title' => __('site.meta.try_title'),
    'description' => __('site.meta.try_description'),
    'canonical' => rtrim((string) config('demo.site_url'), '/').'/try',
])

@section('body')
@php
    $c = __('site.try.cards');
    $n = __('site.try.negative');
    $panel = rtrim((string) config('demo.panel_url'), '/');
@endphp
<header class="nav">
    <div class="wrap">
        <a class="brand" href="{{ route('home') }}">@include('partials.logo') NightGuardian</a>
        <nav class="nav-links" style="display:flex" aria-label="Main">
            <a href="{{ route('home') }}">{{ __('site.try.back') }}</a>
            <div class="lang" role="group" aria-label="{{ __('site.nav.language') }}">
                <a href="{{ route('landing', 'en') }}" hreflang="en" @if (app()->getLocale() === 'en') aria-current="true" @endif>EN</a>
                <a href="{{ route('landing', 'es') }}" hreflang="es" @if (app()->getLocale() === 'es') aria-current="true" @endif>ES</a>
            </div>
        </nav>
    </div>
</header>

<main class="wrap page">
    <h1>{{ __('site.try.title') }}</h1>
    <p class="lead">{{ __('site.try.lead') }}</p>
    <p class="actions" style="margin:.4rem 0 1.4rem">
        <a class="btn btn-primary btn-sm" href="{{ $panel }}" rel="noopener" target="_blank">{{ __('site.try.panel') }} ↗</a>
        <span class="muted" style="align-self:center;font-size:.88rem">{{ __('site.try.panel_hint') }}</span>
    </p>

    @if (session('result'))
        <div class="flash" role="status">
            <strong>{{ session('result')['title'] }}</strong>
            @if (session('result')['body'] !== '')
                <pre>{{ session('result')['body'] }}</pre>
            @endif
            <a class="flash-link" href="{{ $panel }}/issues" rel="noopener" target="_blank">{{ __('site.try.result_panel') }} ↗</a>
        </div>
    @endif

    @if ($errors->any())
        <div class="flash" role="status">
            <strong>{{ __('site.try.validation') }}</strong>
            <pre>{{ implode("\n", $errors->all()) }}</pre>
        </div>
    @endif

    @if ($debug)
        <div class="warn">{{ __('site.try.debug_warning') }}</div>
    @endif

    <h2>{{ __('site.try.arrive') }}</h2>
    <div class="grid cols-3">
        <section class="tcard">
            <h3>{{ $c['exception']['title'] }}</h3>
            <p>{{ $c['exception']['text'] }}</p>
            <p class="expect">{{ $c['exception']['expect'] }}</p>
            <a class="btn btn-danger btn-sm" href="{{ route('demo.exception') }}">{{ $c['exception']['button'] }}</a>
        </section>

        <section class="tcard">
            <h3>{{ $c['handled']['title'] }}</h3>
            <p>{{ $c['handled']['text'] }}</p>
            <p class="expect">{{ $c['handled']['expect'] }}</p>
            <form method="post" action="{{ route('demo.handled') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['handled']['button'] }}</button></form>
        </section>

        <section class="tcard">
            <h3>{{ $c['logs']['title'] }}</h3>
            <p>{{ $c['logs']['text'] }}</p>
            <p class="expect">{{ $c['logs']['expect'] }}</p>
            <form method="post" action="{{ route('demo.logs') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['logs']['button'] }}</button></form>
        </section>

        <section class="tcard">
            <h3>{{ $c['json']['title'] }}</h3>
            <p>{{ $c['json']['text'] }}</p>
            <p class="expect">{{ $c['json']['expect'] }}</p>
            <button class="btn btn-primary btn-sm" type="button" id="json-button">{{ $c['json']['button'] }}</button>
            <pre id="json-result" class="result" hidden></pre>
        </section>

        <section class="tcard">
            <h3>{{ $c['slow_request']['title'] }}</h3>
            <p>{{ $c['slow_request']['text'] }}</p>
            <p class="expect">{{ $c['slow_request']['expect'] }}</p>
            <form method="post" action="{{ route('demo.slow-request') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['slow_request']['button'] }}</button></form>
        </section>

        <section class="tcard">
            <h3>{{ $c['slow_query']['title'] }}</h3>
            <p>{{ $c['slow_query']['text'] }}</p>
            <p class="expect">{{ $c['slow_query']['expect'] }}</p>
            <form method="post" action="{{ route('demo.slow-query') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['slow_query']['button'] }}</button></form>
        </section>

        <section class="tcard">
            <h3>{{ $c['job']['title'] }}</h3>
            <p>{{ $c['job']['text'] }}</p>
            <p class="expect">{{ $c['job']['expect'] }}</p>
            <div class="row">
                <form method="post" action="{{ route('demo.job') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['job']['button'] }}</button></form>
                <form method="post" action="{{ route('demo.run-queue') }}">@csrf<button class="btn btn-ghost btn-sm">{{ $c['job']['button2'] }}</button></form>
            </div>
        </section>

        <section class="tcard">
            <h3>{{ $c['schedule']['title'] }}</h3>
            <p>{{ $c['schedule']['text'] }}</p>
            <p class="expect">{{ $c['schedule']['expect'] }}</p>
            <div class="row">
                <form method="post" action="{{ route('demo.arm-schedule') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['schedule']['button'] }}</button></form>
                <form method="post" action="{{ route('demo.run-scheduler') }}">@csrf<button class="btn btn-ghost btn-sm">{{ $c['schedule']['button2'] }}</button></form>
            </div>
        </section>

        <section class="tcard">
            <h3>{{ $c['burst']['title'] }}</h3>
            <p>{{ $c['burst']['text'] }}</p>
            <p class="expect">{{ $c['burst']['expect'] }}</p>
            <form method="post" action="{{ route('demo.burst') }}" class="row">
                @csrf
                <label>N <input type="number" name="count" value="{{ min(10, $burstMax) }}" min="1" max="{{ $burstMax }}"></label>
                <button class="btn btn-primary btn-sm">{{ $c['burst']['button'] }}</button>
            </form>
        </section>

        <section class="tcard">
            <h3>{{ $c['deploy']['title'] }}</h3>
            <p>{{ $c['deploy']['text'] }}</p>
            <p class="expect">{{ $c['deploy']['expect'] }}</p>
            <form method="post" action="{{ route('demo.deploy') }}">@csrf<button class="btn btn-primary btn-sm">{{ $c['deploy']['button'] }}</button></form>
        </section>
    </div>

    <h2>{{ __('site.try.not_arrive') }}</h2>
    <p class="lead">{{ __('site.try.not_arrive_lead') }}</p>
    <div class="grid cols-3">
        <section class="tcard">
            <h3>{{ $n['not_found']['title'] }}</h3>
            <p>{{ $n['not_found']['text'] }}</p>
            <a class="btn btn-ghost btn-sm" href="{{ route('demo.not-found') }}">{{ $n['not_found']['button'] }}</a>
        </section>
        <section class="tcard">
            <h3>{{ $n['validation']['title'] }}</h3>
            <p>{{ $n['validation']['text'] }}</p>
            <form method="post" action="{{ route('demo.validation') }}">@csrf<input type="hidden" name="email" value="not-an-email"><button class="btn btn-ghost btn-sm">{{ $n['validation']['button'] }}</button></form>
        </section>
        <section class="tcard">
            <h3>{{ $n['csrf']['title'] }}</h3>
            <p>{{ $n['csrf']['text'] }}</p>
            <form method="post" action="{{ route('demo.csrf') }}"><button class="btn btn-ghost btn-sm">{{ $n['csrf']['button'] }}</button></form>
        </section>
    </div>

    <h2>{{ __('site.try.status') }}</h2>
    <p class="lead">{{ __('site.try.status_lead') }}</p>
    <pre class="status">{{ $status }}</pre>

    <p class="muted" style="margin-top:2rem;font-size:.9rem">
        {{ __('site.try.footer') }}
        {{ __('site.try.limited', ['count' => $limit]) }}
        @if ($locked) {{ __('site.try.locked_note') }} @endif
    </p>
</main>

<script>
    document.getElementById('json-button').addEventListener('click', async function () {
        const box = document.getElementById('json-result');
        box.hidden = false;
        box.textContent = '…';
        try {
            const response = await fetch(@json(route('demo.json')), {headers: {'Accept': 'application/json'}});
            const body = await response.text();
            box.textContent = 'HTTP ' + response.status + '\nX-NightGuardian-Reference: ' + (response.headers.get('X-NightGuardian-Reference') || '(none)') + '\n\n' + body.slice(0, 300);
        } catch (error) {
            box.textContent = String(error);
        }
    });
</script>
@endsection
