<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The language of every page: the one chosen with the selector (cookie), else the browser's, else English.
 * `/en` and `/es` choose explicitly (see LandingController).
 */
class SetLocale
{
    public const LOCALES = ['en', 'es'];

    public const COOKIE = 'lang';

    public function handle(Request $request, Closure $next): Response
    {
        $chosen = $request->cookie(self::COOKIE);

        App::setLocale(
            is_string($chosen) && in_array($chosen, self::LOCALES, true)
                ? $chosen
                : ($request->getPreferredLanguage(self::LOCALES) ?? 'en')
        );

        return $next($request);
    }
}
