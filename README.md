# 🌙 NightGuardian demo

[![CI](https://github.com/pablozagni/nightguardian-demo/actions/workflows/ci.yml/badge.svg)](https://github.com/pablozagni/nightguardian-demo/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

[Español](README.es.md)

A small Laravel 13 application with **one page full of buttons**. Each button makes the app do one thing that
[NightGuardian](https://github.com/pablozagni/nightguardian) should report — or deliberately should **not** report.
Use it to try a collector, to see how the SDK behaves, or to check that your alerts work.

It installs the real SDK from Packagist (`composer require nightguardian/laravel`), exactly like your own app would.

## What the buttons do

| Button | What it does | What arrives at the collector |
|---|---|---|
| **Throw an exception** | An exception nobody catches → HTTP 500 | A page with a reference code (`NG-XXXX-XXXX`) and a feedback box; an exception issue; the feedback you send, attached to it |
| **Report a handled error** | `NightGuardian::report($e, [...])` with context | An exception marked *handled*, with `order_id`, `gateway`, `attempt` |
| **Write the logs** | `Log::error()` and `Log::warning()` | The error as a log issue (the warning only with `NIGHTGUARDIAN_LOG_LEVEL=warning`) |
| **Call the JSON endpoint** | A failing request that asks for JSON | An exception issue; the response body stays Laravel's JSON and only gets the `X-NightGuardian-Reference` header |
| **Make a slow request** | ~2.5 s request (threshold 2 s) | A slow request event |
| **Run a slow query** | `SELECT SLEEP(1.5)` (threshold 1 s) — needs MySQL/MariaDB | A slow query event with the SQL |
| **Queue the job** / **Process the queue now** | A job that always fails, on the database queue | A failed job event |
| **Arm the failing task** / **Run the scheduler now** | A scheduled command (`demo:fail`) that fails once | A failed scheduled task event |
| **Send the burst** | N identical errors (1–200) | One issue whose event count grows by N — use 50+ to try the alert threshold |
| **Mark a deploy** | `php artisan nightguardian:deploy` | A deploy marker |
| 404 / validation error / 419 | Normal application behaviour | **Nothing.** NightGuardian ignores them on purpose |

The page also shows the output of `php artisan nightguardian:status` at the bottom.

## Quick start

You need PHP 8.3+, Composer, and a running NightGuardian collector with a project and a token
(in the collector: *Projects → your project → Tokens → Create token*; the panel shows the two lines you need).

```bash
git clone https://github.com/pablozagni/nightguardian-demo.git
cd nightguardian-demo
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite && php artisan migrate     # SQLite by default
```

Edit `.env`:

```dotenv
NIGHTGUARDIAN_URL=https://nightguardian.example.com     # your collector
NIGHTGUARDIAN_TOKEN=ng_...                              # the project token
```

Run it and open <http://localhost:8000>:

```bash
php artisan serve
```

Keep `APP_DEBUG=false` (the default in `.env.example`): with `true`, Laravel shows its own debug page and
NightGuardian's friendly page with the reference code does not appear.

### Failing jobs and scheduled tasks

The page has buttons to run both without a console, but this is how it works for real. In two more consoles:

```bash
php artisan queue:work          # processes the failing job queued by the "Queue the job" button
php artisan schedule:work       # runs the failing task armed by the "Arm the failing task" button
```

### The slow query button

`SELECT SLEEP()` needs MySQL or MariaDB. In `.env` set `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME` and
`DB_PASSWORD`, create the database, and run `php artisan migrate`. With SQLite the button explains that it is skipped.

## Putting it on a server

If you publish the demo, **set a key** so that nobody else can fill your collector with errors:

```dotenv
DEMO_KEY=a-long-random-string
```

With `DEMO_KEY` set, every page asks for the key first (5 attempts per minute). Leave it empty for local use.
Use a throwaway project in your collector: the demo creates real issues, alerts and, with a burst, a lot of events.

## Notes

- Events are sent **after** the response, so they appear in the collector a moment after you press a button. If the
  collector is down, the SDK keeps them in `storage/nightguardian/spool` and sends them later; `nightguardian:status` shows it.
- The tests (`vendor/bin/pest`) run with the SDK switched off: they never contact a collector.

## License

MIT, see [LICENSE](LICENSE).
