@php
    $locale = app()->getLocale();
    $links = [
        '#features' => __('site.nav.features'),
        '#how' => __('site.nav.how'),
        '#screens' => __('site.nav.screens'),
        '#install' => __('site.nav.install'),
        '#sdks' => __('site.nav.sdks'),
    ];
    if (config('demo.sponsor_github_url') || config('demo.donate_mp_url')) {
        $links['#support'] = __('site.nav.support');
    }
@endphp
<header class="nav">
    <div class="wrap">
        <a class="brand" href="{{ route('home') }}">@include('partials.logo') NightGuardian</a>

        <nav class="nav-links" aria-label="Main">
            @foreach ($links as $href => $label)
                <a href="{{ $href }}">{{ $label }}</a>
            @endforeach
            <div class="lang" role="group" aria-label="{{ __('site.nav.language') }}">
                <a href="{{ route('landing', 'en') }}" hreflang="en" @if ($locale === 'en') aria-current="true" @endif>EN</a>
                <a href="{{ route('landing', 'es') }}" hreflang="es" @if ($locale === 'es') aria-current="true" @endif>ES</a>
            </div>
            <a class="btn btn-primary btn-sm" href="{{ route('try') }}">{{ __('site.nav.try') }}</a>
        </nav>

        <details class="nav-menu">
            <summary>{{ __('site.nav.menu') }} ☰</summary>
            <div class="menu-panel">
                @foreach ($links as $href => $label)
                    <a href="{{ $href }}">{{ $label }}</a>
                @endforeach
                <a href="{{ route('try') }}"><strong>{{ __('site.nav.try') }}</strong></a>
                <div class="lang" role="group" aria-label="{{ __('site.nav.language') }}" style="margin:.4rem .5rem">
                    <a href="{{ route('landing', 'en') }}" hreflang="en" @if ($locale === 'en') aria-current="true" @endif>EN</a>
                    <a href="{{ route('landing', 'es') }}" hreflang="es" @if ($locale === 'es') aria-current="true" @endif>ES</a>
                </div>
            </div>
        </details>
    </div>
</header>
