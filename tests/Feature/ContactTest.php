<?php

use App\Mail\ContactMessage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

test('queues a contact message when turnstile is not configured', function () {
    Mail::fake();
    Http::preventStrayRequests();

    config(['services.contact.to' => 'ops@example.com']);

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
});

test('rejects a contact message when turnstile verification fails', function () {
    Mail::fake();
    Http::preventStrayRequests();

    config([
        'services.turnstile.site_key' => 'site-key',
        'services.turnstile.secret' => 'secret-key',
        'services.contact.to' => 'ops@example.com',
    ]);

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => false,
        ]),
    ]);

    $response = $this->from(route('home'))->post(route('contact.store'), [
        'name' => 'Alex Morgan',
        'email' => 'alex@company.com',
        'message' => 'I have a question about billing.',
        'turnstile_token' => 'invalid-token',
    ]);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('turnstile_token');

    Mail::assertNothingOutgoing();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        && $request['response'] === 'invalid-token'
        && $request['secret'] === 'secret-key');
});

test('queues a contact message when turnstile verification succeeds', function () {
    Mail::fake();
    Http::preventStrayRequests();

    config([
        'services.turnstile.site_key' => 'site-key',
        'services.turnstile.secret' => 'secret-key',
        'services.contact.to' => 'ops@example.com',
    ]);

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    $response = $this->from(route('home'))->post(route('contact.store'), [
        'name' => 'Alex Morgan',
        'email' => 'alex@company.com',
        'message' => 'I have a question about billing.',
        'turnstile_token' => 'valid-token',
    ]);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('success');

    Mail::assertQueued(ContactMessage::class);

    Http::assertSent(fn (Request $request): bool => $request['response'] === 'valid-token');
});

test('rejects a contact message when required fields are missing', function () {
    Mail::fake();

    $response = $this->from(route('home'))->post(route('contact.store'), []);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors(['name', 'email', 'message']);

    Mail::assertNothingOutgoing();
});
