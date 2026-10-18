<?php

use App\Support\GitHubStars;
use App\Support\Qr;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    config(['demo.site_url' => 'https://nightguardian.example.org']);
});

// --- language ------------------------------------------------------------------------------------------------------

it('shows the landing page in English by default', function () {
    $this->get('/')->assertOk()->assertSee('Your apps break at', false)->assertSee('Try it live')->assertSee('<html lang="en">', false);
});

it('follows the language of the browser on /', function () {
    $this->withHeaders(['Accept-Language' => 'es-AR,es;q=0.9,en;q=0.5'])->get('/')
        ->assertOk()->assertSee('Probalo en vivo')->assertSee('<html lang="es">', false);

    $this->withHeaders(['Accept-Language' => 'fr-FR,fr;q=0.9'])->get('/')->assertSee('<html lang="en">', false);
});

it('lets the visitor choose a language with /en and /es and remembers it', function () {
    $response = $this->withHeaders(['Accept-Language' => 'en-US'])->get('/es');

    $response->assertOk()->assertSee('<html lang="es">', false)->assertCookie('lang', 'es', false);

    // The cookie wins over the browser on the next visit.
    $this->withHeaders(['Accept-Language' => 'en-US'])->withUnencryptedCookie('lang', 'es')->get('/')->assertSee('<html lang="es">', false);
});

it('has a language selector that marks the current language', function () {
    $this->get('/es')->assertSee('hreflang="en"', false)->assertSee('hreflang="es"', false)->assertSee('aria-current="true"', false);
});

it('answers 404 to unknown languages', function () {
    $this->get('/fr')->assertNotFound();
});

// --- content ---------------------------------------------------------------------------------------------------------

it('covers what the product is, the problem, features, how it works, screenshots, install, SDKs and links', function (string $locale, array $texts) {
    $html = $this->get('/'.$locale)->assertOk()->getContent();

    foreach ($texts as $text) {
        expect($html)->toContain($text);
    }

    expect($html)->toContain('<svg viewBox="0 0 920 210"')           // how it works diagram
        ->toContain('img/panel-issue-dark.png')                       // screenshots from the repos
        ->toContain('docker compose up -d --build')                   // install, step 1
        ->toContain('composer require nightguardian/laravel')         // install, step 2
        ->toContain('NIGHTGUARDIAN_TOKEN=ng_')                        // install, step 3
        ->toContain('nightguardian/laravel 2.x')                      // SDK table
        ->toContain('nightguardian-node')
        ->toContain('docs/protocol/v1.md')                            // protocol link
        ->toContain('https://github.com/pablozagni/nightguardian')   // repo links
        ->toContain('https://github.com/pablozagni/nightguardian-laravel')
        ->toContain('https://www.z-system.com.ar');
})->with([
    'english' => ['en', ['The problem', 'What you get', 'How it works', 'Install in 3 steps', 'Looking for a maintainer', 'Made in Argentina by', 'MIT licence']],
    'spanish' => ['es', ['El problema', 'Qué obtenés', 'Cómo funciona', 'Instalación en 3 pasos', 'Buscamos maintainer', 'Hecho en Argentina por', 'Licencia MIT']],
]);

it('links the live demo and the demo panel with the visitor credentials', function () {
    config(['demo.panel_url' => 'https://demo.example.org', 'demo.visitor_email' => 'visitor@demo.example.org', 'demo.visitor_password' => 'visit-me']);

    $this->get('/en')->assertSee('href="'.route('try').'"', false)->assertSee('https://demo.example.org')->assertSee('visitor@demo.example.org')->assertSee('visit-me');
});

it('shows the screenshot of the error page in the language of the page', function () {
    $this->get('/en')->assertSee('error-page-en-dark.png');
    $this->get('/es')->assertSee('error-page-es-dark.png');
});

it('ships every image it refers to', function () {
    $html = $this->get('/en')->getContent();
    preg_match_all('#(?:src|href)="[^"]*?/(img/[^"]+|favicon\.svg|css/site\.css)#', $html, $m);

    expect($m[1])->not->toBeEmpty();
    foreach (array_unique($m[1]) as $asset) {
        expect(file_exists(public_path($asset)))->toBeTrue("missing {$asset}");
    }

    expect(file_exists(public_path('og.png')))->toBeTrue();
});

// --- support (only the buttons that have a URL) -----------------------------------------------------------------------

it('shows no support section when no URL is configured', function () {
    config(['demo.sponsor_github_url' => null, 'demo.donate_mp_url' => null]);

    $this->get('/en')->assertDontSee('Support the project')->assertDontSee('Sponsor on GitHub')->assertDontSee('Mercado Pago');
});

it('shows only the GitHub Sponsors button when only that URL is set', function () {
    config(['demo.sponsor_github_url' => 'https://github.com/sponsors/someone', 'demo.donate_mp_url' => '']);

    $this->get('/en')->assertSee('Support the project')->assertSee('https://github.com/sponsors/someone')->assertSee('Sponsor on GitHub')
        ->assertDontSee('Donate with Mercado Pago')->assertDontSee('class="qr"', false);
});

it('shows Mercado Pago with a QR code generated from the link when only that URL is set', function () {
    config(['demo.sponsor_github_url' => null, 'demo.donate_mp_url' => 'https://mpago.la/2abcDEF']);

    $this->get('/es')->assertSee('Apoyá el proyecto')->assertSee('https://mpago.la/2abcDEF')->assertSee('Donar con Mercado Pago')
        ->assertDontSee('Apoyar en GitHub Sponsors')->assertSee('class="qr"', false)->assertSee('<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg', false);
});

it('shows both buttons when both URLs are set', function () {
    config(['demo.sponsor_github_url' => 'https://github.com/sponsors/someone', 'demo.donate_mp_url' => 'https://mpago.la/2abcDEF']);

    $this->get('/en')->assertSee('Sponsor on GitHub')->assertSee('Donate with Mercado Pago');
});

it('generates a QR code that depends on the link', function () {
    $a = Qr::svg('https://mpago.la/aaa');
    $b = Qr::svg('https://mpago.la/bbb');

    expect($a)->toStartWith('<svg')->and($b)->toStartWith('<svg')->and($a)->not->toBe($b);
});

// --- SEO -----------------------------------------------------------------------------------------------------------------

it('has a title, a description, a canonical link and language alternates', function () {
    $this->get('/en')
        ->assertSee('<title>NightGuardian — open-source, self-hosted error monitoring for Laravel</title>', false)
        ->assertSee('<meta name="description" content="NightGuardian groups the errors', false)
        ->assertSee('<link rel="canonical" href="https://nightguardian.example.org/en">', false)
        ->assertSee('<link rel="alternate" hreflang="es" href="https://nightguardian.example.org/es">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="https://nightguardian.example.org/">', false);

    $this->get('/es')->assertSee('<link rel="canonical" href="https://nightguardian.example.org/es">', false)->assertSee('monitoreo de errores open source', false);
});

it('has Open Graph and Twitter cards with an image', function () {
    $this->get('/')
        ->assertSee('<meta property="og:title"', false)
        ->assertSee('<meta property="og:description"', false)
        ->assertSee('<meta property="og:image" content="https://nightguardian.example.org/og.png">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta property="og:locale" content="en_US">', false);

    $this->get('/es')->assertSee('<meta property="og:locale" content="es_AR">', false);
});

it('describes the product to search engines', function () {
    $html = $this->get('/en')->getContent();
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
    $data = json_decode($m[1] ?? '', true);

    expect($data['@type'])->toBe('SoftwareApplication')->and($data['name'])->toBe('NightGuardian')->and($data['offers']['price'])->toBe('0');
});

it('serves a sitemap with the public pages', function () {
    $response = $this->get('/sitemap.xml')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('application/xml');
    $response->assertSee('<loc>https://nightguardian.example.org/</loc>', false)
        ->assertSee('<loc>https://nightguardian.example.org/en</loc>', false)
        ->assertSee('<loc>https://nightguardian.example.org/es</loc>', false)
        ->assertSee('<loc>https://nightguardian.example.org/try</loc>', false);
});

it('points robots at the sitemap and keeps crawlers out of the action routes', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('Sitemap: https://nightguardian.com.ar/sitemap.xml')->toContain('Disallow: /demo/');
});

// --- the live demo is public, with a limit per IP ---------------------------------------------------------------------------

it('does not need a key and shows the panel link and the limit', function () {
    config(['demo.key' => null, 'demo.panel_url' => 'https://demo.example.org']);

    $this->get('/try')->assertOk()->assertSee('Open the demo panel')->assertSee('https://demo.example.org')->assertSee('limited to 20 actions per minute');
});

it('is translated to Spanish', function () {
    $this->withHeaders(['Accept-Language' => 'es'])->get('/try')->assertOk()->assertSee('Lanzar una excepción')->assertSee('Errores que no deben llegar');
});

it('limits the actions to 20 per minute per IP, configurable', function () {
    RateLimiter::clear('demo-try');
    config(['demo.try_rate_limit' => 20]);

    foreach (range(1, 20) as $i) {
        $this->post('/demo/logs')->assertRedirect('/try');
    }

    $this->post('/demo/logs')->assertStatus(429)->assertSee('Too many actions');
    // Another visitor is not affected.
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])->post('/demo/logs')->assertRedirect('/try');
});

it('honours a different limit', function () {
    RateLimiter::clear('demo-try');
    config(['demo.try_rate_limit' => 2]);

    $this->post('/demo/logs');
    $this->post('/demo/logs');

    $this->post('/demo/logs')->assertStatus(429);
});

it('does not count the page itself against the limit', function () {
    config(['demo.try_rate_limit' => 1]);

    foreach (range(1, 5) as $i) {
        $this->get('/try')->assertOk();
    }
});

it('limits the burst to 20 events', function () {
    $this->post('/demo/burst', ['count' => 500])->assertSessionHas('result.title', 'Burst of 20 errors');
});

it('shows a direct link to the demo panel after each button', function () {
    config(['demo.panel_url' => 'https://demo.example.org']);

    $this->followingRedirects()->post('/demo/handled')->assertOk()->assertSee('Handled error reported')->assertSee('https://demo.example.org/issues')->assertSee('Look for it in the demo panel');
});

it('runs the queue and the scheduler without a console', function () {
    config(['queue.default' => 'database']);

    $this->post('/demo/run-queue')->assertSessionHas('result.title', 'Queue processed');
    $this->post('/demo/run-scheduler')->assertSessionHas('result.title', 'Scheduler ran');
});

// --- open source: GitHub button, stars and repository links -----------------------------------------------------------

it('shows the Open source label with a View on GitHub button in the header and in the hero', function (string $locale, string $label) {
    $html = $this->get('/'.$locale)->assertOk()->assertSee('Open source · MIT')->assertSee($label)->getContent();

    // header (desktop nav + mobile menu) and hero
    expect(substr_count($html, 'class="btn btn-gh btn-sm"'))->toBe(3)
        ->and($html)->toContain('<div class="hero-tag">')
        ->and($html)->toContain('class="gh-icon"')
        ->and($html)->toContain('href="https://github.com/pablozagni/nightguardian"');
})->with([['en', 'View on GitHub'], ['es', 'Ver en GitHub']]);

it('links every repository from the install section', function () {
    $html = $this->get('/en')->assertOk()->getContent();
    $install = substr($html, (int) strpos($html, 'id="install"'), 6000);

    expect($install)->toContain('https://github.com/pablozagni/nightguardian"')
        ->toContain('https://github.com/pablozagni/nightguardian-laravel"')
        ->toContain('https://github.com/pablozagni/nightguardian-laravel/blob/main/docs/configuration.md');
});

it('shows the star count fetched on the server and caches it', function () {
    config(['demo.github_stars' => true]);
    Http::fake(['api.github.com/*' => Http::response(['stargazers_count' => 1234])]);

    $this->get('/en')->assertSee('★ 1.2k', false)->assertSee('1234 stars on GitHub');
    $this->get('/es')->assertSee('★ 1.2k', false);

    Http::assertSentCount(1);
});

it('hides the star count when GitHub does not answer and keeps the last good number', function () {
    config(['demo.github_stars' => true]);
    Http::fake(['api.github.com/*' => Http::response([], 403)]);

    $this->get('/en')->assertOk()->assertSee('View on GitHub')->assertDontSee('★');

    Cache::forget('github.stars.'.config('demo.github_repo'));
    Cache::forever('github.stars.'.config('demo.github_repo').'.last', 987);

    $this->get('/en')->assertSee('★ 987', false);
});

it('formats star counts', function (int $stars, string $text) {
    expect(GitHubStars::format($stars))->toBe($text);
})->with([[7, '7'], [950, '950'], [1000, '1k'], [1234, '1.2k'], [9999, '10k'], [15300, '15k']]);
