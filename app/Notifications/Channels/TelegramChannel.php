<?php

namespace App\Notifications\Channels;

use App\Services\TelegramBot;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    public function __construct(public TelegramBot $telegram) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTelegram')) {
            return;
        }

        if (! method_exists($notifiable, 'routeNotificationFor')) {
            return;
        }

        /** @var string $message */
        $message = $notification->toTelegram($notifiable);

        if (! filled($message)) {
            return;
        }

        $chatId = $notifiable->routeNotificationFor('telegram', $notification);

        $this->telegram->sendMessage($message, $chatId !== null ? (string) $chatId : null);
    }
}
