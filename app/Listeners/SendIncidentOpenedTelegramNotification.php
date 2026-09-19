<?php

namespace App\Listeners;

use App\Events\IncidentOpened;
use App\Notifications\MonitorIncidentOpenedNotification;
use App\Services\TelegramBot;
use Illuminate\Support\Facades\Notification;

class SendIncidentOpenedTelegramNotification
{
    public function __construct(public TelegramBot $telegram) {}

    public function handle(IncidentOpened $event): void
    {
        if (! $this->telegram->isConfigured()) {
            return;
        }

        Notification::route('telegram', (string) config('services.telegram.chat_id'))
            ->notify(new MonitorIncidentOpenedNotification($event->incident));
    }
}
