<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloudflareTurnstile
{
    public function isEnabled(): bool
    {
        return filled(config('services.turnstile.site_key'))
            && filled(config('services.turnstile.secret'));
    }

    public function verify(?string $token, ?string $ip): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (! filled($token)) {
            return false;
        }

        $response = Http::asForm()
            ->connectTimeout(3)
            ->timeout(5)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

        return $response->successful() && $response->json('success') === true;
    }
}
