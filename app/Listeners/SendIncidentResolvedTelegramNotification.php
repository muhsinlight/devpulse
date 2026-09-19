<?php

namespace App\Listeners;

use App\Events\IncidentResolved;
use App\Notifications\MonitorIncidentResolvedNotification;
use App\Services\TelegramBot;
use Illuminate\Support\Facades\Notification;

class SendIncidentResolvedTelegramNotification
{
    public function __construct(public TelegramBot $telegram) {}

    public function handle(IncidentResolved $event): void
    {
        if (! $this->telegram->isConfigured()) {
            return;
        }

        Notification::route('telegram', (string) config('services.telegram.chat_id'))
            ->notify(new MonitorIncidentResolvedNotification($event->incident));
    }
}
