<?php

namespace App\Jobs;

use App\Actions\CheckMonitorAction;
use App\Models\Monitor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckMonitorJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 90;

    public int $uniqueFor = 90;

    /**
     * Create a new job instance.
     */
    public function __construct(public Monitor $monitor) {}

    public function uniqueId(): string
    {
        return (string) $this->monitor->id;
    }

    /**
     * Execute the job.
     */
    public function handle(CheckMonitorAction $checkMonitor): void
    {
        $this->monitor->refresh();

        if (! $this->monitor->is_active) {
            return;
        }

        $checkMonitor->handle($this->monitor);
    }
}
