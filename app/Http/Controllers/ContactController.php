<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactMessage;
use App\Services\CloudflareTurnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreContactRequest $request, CloudflareTurnstile $turnstile): RedirectResponse
    {
        if (! $turnstile->verify($request->input('turnstile_token'), $request->ip())) {
            return back()
                ->withErrors([
                    'turnstile_token' => 'Please complete the bot check and try again.',
                ])
                ->withInput();
        }

        $payload = $request->safe()->only(['name', 'email', 'message']);

        Mail::to((string) (config('services.contact.to') ?: config('mail.from.address')))
            ->send(new ContactMessage(
                name: $payload['name'],
                email: $payload['email'],
                body: $payload['message'],
            ));

        return back()->with('success', 'Your message has been sent.');
    }
}
