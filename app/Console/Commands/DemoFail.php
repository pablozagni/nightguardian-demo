<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class DemoFail extends Command
{
    protected $signature = 'demo:fail';

    protected $description = 'A command that always fails (used as a failing scheduled task by the demo)';

    public function handle(): int
    {
        throw new RuntimeException('Demo scheduled task failed: could not close the day');
    }
}
