<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Cookie;

class LandingController extends Controller
{
    /** `/`: the language comes from the cookie or the browser (SetLocale). */
    public function home(): View
    {
        return view('landing', ['locale' => App::getLocale(), 'explicit' => false]);
    }

    /** `/en` and `/es`: an explicit language, remembered in a cookie. */
    public function localized(string $locale): Response
    {
        abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

        App::setLocale($locale);

        return response(view('landing', ['locale' => $locale, 'explicit' => true]))
            ->withCookie(new Cookie(SetLocale::COOKIE, $locale, now()->addYear(), '/', null, null, false, false, 'lax'));
    }

    public function sitemap(): Response
    {
        $site = rtrim((string) config('demo.site_url'), '/');
        $urls = [
            ['loc' => $site.'/', 'priority' => '1.0'],
            ['loc' => $site.'/en', 'priority' => '0.9'],
            ['loc' => $site.'/es', 'priority' => '0.9'],
            ['loc' => $site.'/try', 'priority' => '0.8'],
        ];

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
