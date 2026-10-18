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
<main style="max-width: 26rem; padding-top: 4rem">
    <h1><span aria-hidden="true">🌙</span> NightGuardian demo</h1>
    <p class="lead">This demo is protected. Type the key to continue.</p>
    <form method="post" action="{{ route('unlock') }}" class="card">
        @csrf
        <label for="key">Key</label>
        <input id="key" name="key" type="password" autofocus autocomplete="off" required style="width: 100%; padding: .55rem .7rem; margin: .3rem 0 1rem; border: 1px solid var(--line); border-radius: .4rem; background: transparent; color: inherit; font: inherit">
        @isset($wrong)<p style="color: var(--danger)">That is not the key.</p>@endisset
        <button class="button">Open the demo</button>
    </form>
</main>
</body>
</html>
