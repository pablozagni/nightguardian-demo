<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

// The failing task only runs after the "arm" button of the demo page was pressed (once), so that running
// the scheduler does not keep producing errors by itself.
Schedule::command('demo:fail')
    ->everyMinute()
    // The scheduler evaluates `when()` more than once per run, so the flag is cleared afterwards, not when read.
    ->when(fn (): bool => (bool) Cache::get('demo.fail_schedule', false))
    ->after(fn () => Cache::forget('demo.fail_schedule'));
