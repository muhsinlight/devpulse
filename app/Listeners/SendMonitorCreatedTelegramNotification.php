<?php

namespace App\Listeners;

use App\Events\MonitorCreated;
use App\Notifications\MonitorCreatedNotification;
use App\Services\OpsNotifier;

class SendMonitorCreatedTelegramNotification
{
    public function __construct(public OpsNotifier $ops) {}

    public function handle(MonitorCreated $event): void
    {
        $this->ops->notify(new MonitorCreatedNotification($event->monitor));
    }
}
