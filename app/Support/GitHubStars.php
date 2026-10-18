<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * The star count of the repository, fetched on the server (no JavaScript, no third-party widget on the page) and cached.
 * It never blocks or breaks the landing page: any failure just hides the number.
 */
class GitHubStars
{
    private const FRESH_FOR = 3600;

    private const RETRY_AFTER = 600;

    /** Stars of the configured repository, or null when disabled or unknown. */
    public static function count(): ?int
    {
        $repo = config('demo.github_repo');

        if (! config('demo.github_stars') || ! is_string($repo) || $repo === '') {
            return null;
        }

        $key = 'github.stars.'.$repo;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $stars = self::fetch($repo);

        if ($stars !== null) {
            Cache::put($key, $stars, self::FRESH_FOR);
            Cache::forever($key.'.last', $stars);

            return $stars;
        }

        // GitHub is down or rate-limited: keep showing the last good number and try again in a few minutes.
        $last = Cache::get($key.'.last');
        Cache::put($key, $last, self::RETRY_AFTER);

        return $last;
    }

    /** 950 → "950", 1234 → "1.2k", 15300 → "15k". */
    public static function format(int $stars): string
    {
        if ($stars < 1000) {
            return (string) $stars;
        }

        $thousands = $stars / 1000;

        return ($thousands < 10 ? rtrim(rtrim(number_format($thousands, 1, '.', ''), '0'), '.') : (string) round($thousands)).'k';
    }

    private static function fetch(string $repo): ?int
    {
        try {
            $response = Http::acceptJson()
                ->withHeaders(['User-Agent' => 'nightguardian-site'])
                ->connectTimeout(1)
                ->timeout(2)
                ->get('https://api.github.com/repos/'.$repo);

            $stars = $response->successful() ? $response->json('stargazers_count') : null;

            return is_int($stars) ? $stars : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
