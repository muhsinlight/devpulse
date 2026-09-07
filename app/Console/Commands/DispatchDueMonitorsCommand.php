<?php

namespace App\Console\Commands;

use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('monitors:dispatch-due')]
#[Description('Dispatch queued health checks for monitors that are due')]
class DispatchDueMonitorsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dispatched = 0;

        Monitor::query()
            ->due()
            ->orderBy('id')
            ->chunkById(100, function ($monitors) use (&$dispatched): void {
                foreach ($monitors as $monitor) {
                    $monitor->scheduleNextCheck();
                    CheckMonitorJob::dispatch($monitor);
                    $dispatched++;
                }
            });

        $this->info("Dispatched {$dispatched} monitor check(s).");

        return self::SUCCESS;
    }
}
