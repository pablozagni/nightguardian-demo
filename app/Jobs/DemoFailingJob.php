<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

/**
 * A queued job that always fails (once: no retries), to see a failed job in NightGuardian.
 */
class DemoFailingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function handle(): void
    {
        throw new RuntimeException('Demo job failed: the payment provider did not answer');
    }
}
