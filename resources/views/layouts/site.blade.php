@php
    $site = rtrim((string) config('demo.site_url'), '/');
    $locale = app()->getLocale();
    $title = $title ?? __('site.meta.title');
    $description = $description ?? __('site.meta.description');
    $canonical = $canonical ?? $site.'/';
    $ogLocale = $locale === 'es' ? 'es_AR' : 'en_US';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#080d1c">
    <link rel="canonical" href="{{ $canonical }}">
    @if (! empty($alternates))
        <link rel="alternate" hreflang="en" href="{{ $site }}/en">
        <link rel="alternate" hreflang="es" href="{{ $site }}/es">
        <link rel="alternate" hreflang="x-default" href="{{ $site }}/">
    @endif
    @if (! empty($noindex))
        <meta name="robots" content="noindex">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="NightGuardian">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $site }}/og.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $site }}/og.png">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    @stack('head')
</head>
<body>
@yield('body')
</body>
</html>
