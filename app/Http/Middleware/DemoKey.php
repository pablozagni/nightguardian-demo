<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When DEMO_KEY is set, the demo is locked until the visitor types that key.
 */
class DemoKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) config('demo.key');

        if ($key === '' || $request->session()->get('demo_unlocked') === true) {
            return $next($request);
        }

        return response()->view('unlock', [], 401);
    }
}
