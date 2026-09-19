<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactMessage;
use App\Notifications\ContactMessageReceivedNotification;
use App\Services\TelegramBot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreContactRequest $request, TelegramBot $telegram): RedirectResponse
    {
        $payload = $request->safe()->only(['name', 'email', 'message']);

        Mail::to((string) (config('services.contact.to') ?: config('mail.from.address')))
            ->send(new ContactMessage(
                name: $payload['name'],
                email: $payload['email'],
                body: $payload['message'],
            ));

        if ($telegram->isConfigured()) {
            Notification::route('telegram', (string) config('services.telegram.chat_id'))
                ->notify(new ContactMessageReceivedNotification(
                    name: $payload['name'],
                    email: $payload['email'],
                    body: $payload['message'],
                ));
        }

        return back()->with('success', 'Your message has been sent.');
    }
}
