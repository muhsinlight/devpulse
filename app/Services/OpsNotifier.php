<?php

namespace App\Services;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class OpsNotifier
{
    public function __construct(public TelegramBot $telegram) {}

    /**
     * Prefer Telegram when configured; otherwise email via the default mailer (Resend).
     */
    public function notify(Notification $notification): void
    {
        if ($this->prefersTelegram()) {
            NotificationFacade::route('telegram', (string) config('services.telegram.chat_id'))
                ->notify($notification);

            return;
        }

        $email = $this->opsEmail();

        if ($email === null) {
            return;
        }

        NotificationFacade::route('mail', $email)->notify($notification);
    }

    public function prefersTelegram(): bool
    {
        return $this->telegram->isConfigured();
    }

    public function opsEmail(): ?string
    {
        $email = config('services.contact.to') ?: config('mail.from.address');

        return filled($email) ? (string) $email : null;
    }

    /**
     * @return array<int, string>
     */
    public function channels(): array
    {
        if ($this->prefersTelegram()) {
            return ['telegram'];
        }

        return $this->opsEmail() !== null ? ['mail'] : [];
    }
}
