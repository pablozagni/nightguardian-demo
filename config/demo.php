<?php

return [
    /*
    | If set, the live demo (/try) asks for this key first. Leave it empty for local use and for the public site,
    | where /try is protected by the per-IP limit below instead.
    */
    'key' => env('DEMO_KEY'),

    // How long the "slow request" button takes (the SDK reports requests over NIGHTGUARDIAN_SLOW_REQUEST_MS, 2 s).
    'slow_request_seconds' => (float) env('DEMO_SLOW_REQUEST_SECONDS', 2.5),

    // Largest burst the "burst" button accepts.
    'burst_max' => (int) env('DEMO_BURST_MAX', 20),

    // Actions per minute and per IP on the live demo (the public site is open: this keeps it from being abused).
    'try_rate_limit' => (int) env('DEMO_TRY_RATE_LIMIT', 20),

    // Where the NightGuardian panel of the demo lives; used for the links shown after each button.
    'panel_url' => env('NIGHTGUARDIAN_PANEL_URL') ?: 'https://demo.nightguardian.com.ar',

    // The panel's visitor account, shown on the landing page (same values as the collector's demo mode).
    'visitor_email' => env('NIGHTGUARDIAN_DEMO_VISITOR_EMAIL') ?: 'visitor@demo.example.com',
    'visitor_password' => env('NIGHTGUARDIAN_DEMO_VISITOR_PASSWORD') ?: 'visitor-demo',

    // "Support the project": a button only appears when its URL is set.
    'sponsor_github_url' => env('NIGHTGUARDIAN_SPONSOR_GITHUB_URL'),
    'donate_mp_url' => env('NIGHTGUARDIAN_DONATE_MP_URL'),

    // The repository whose star count the landing page shows next to "View on GitHub" (fetched server-side and cached for an hour).
    'github_repo' => env('DEMO_GITHUB_REPO') ?: 'pablozagni/nightguardian',
    'github_stars' => (bool) env('DEMO_GITHUB_STARS', true),

    // The public address of this site (canonical links, Open Graph, sitemap).
    'site_url' => env('NIGHTGUARDIAN_SITE_URL') ?: 'https://nightguardian.com.ar',
];
