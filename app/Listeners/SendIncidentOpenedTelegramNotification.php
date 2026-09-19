<?php

namespace App\Listeners;

use App\Events\IncidentOpened;
use App\Notifications\MonitorIncidentOpenedNotification;
use App\Services\OpsNotifier;

class SendIncidentOpenedTelegramNotification
{
    public function __construct(public OpsNotifier $ops) {}

    public function handle(IncidentOpened $event): void
    {
        $this->ops->notify(new MonitorIncidentOpenedNotification($event->incident));
    }
}
