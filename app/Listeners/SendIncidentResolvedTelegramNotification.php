<?php

namespace App\Listeners;

use App\Events\IncidentResolved;
use App\Notifications\MonitorIncidentResolvedNotification;
use App\Services\OpsNotifier;

class SendIncidentResolvedTelegramNotification
{
    public function __construct(public OpsNotifier $ops) {}

    public function handle(IncidentResolved $event): void
    {
        $this->ops->notify(new MonitorIncidentResolvedNotification($event->incident));
    }
}
