<?php

use App\Mail\ContactMessage;
use App\Notifications\ContactMessageReceivedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

test('queues a contact message email', function () {
    Mail::fake();
    Notification::fake();
    Http::preventStrayRequests();

    config([
        'services.contact.to' => 'ops@example.com',
        'services.telegram.bot_token' => null,
        'services.telegram.chat_id' => null,
    ]);

    $response = $this->from(route('home'))->post(route('contact.store'), [
        'name' => 'Alex Morgan',
        'email' => 'alex@company.com',
        'message' => 'I have a question about billing.',
    ]);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('success');

    Mail::assertQueued(ContactMessage::class, function (ContactMessage $mail): bool {
        return $mail->hasTo('ops@example.com')
            && $mail->name === 'Alex Morgan'
            && $mail->email === 'alex@company.com'
            && $mail->body === 'I have a question about billing.';
    });

    Notification::assertNothingSent();
});

test('sends a telegram notification for contact when configured', function () {
    Mail::fake();
    Notification::fake();

    config([
        'services.contact.to' => 'ops@example.com',
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id' => '123456',
    ]);

    $response = $this->from(route('home'))->post(route('contact.store'), [
        'name' => 'Alex Morgan',
        'email' => 'alex@company.com',
        'message' => 'I have a question about billing.',
    ]);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('success');

    Mail::assertQueued(ContactMessage::class);
    Notification::assertSentOnDemand(ContactMessageReceivedNotification::class);
});

test('rejects a contact message when required fields are missing', function () {
    Mail::fake();
    Notification::fake();

    $response = $this->from(route('home'))->post(route('contact.store'), []);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors(['name', 'email', 'message']);

    Mail::assertNothingOutgoing();
    Notification::assertNothingSent();
});
