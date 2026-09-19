<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramBot
{
    public function isConfigured(): bool
    {
        return filled(config('services.telegram.bot_token'))
            && filled(config('services.telegram.chat_id'));
    }

    public function sendMessage(string $text, ?string $chatId = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $chatId ??= (string) config('services.telegram.chat_id');
        $token = (string) config('services.telegram.bot_token');

        try {
            Http::baseUrl("https://api.telegram.org/bot{$token}")
                ->connectTimeout(3)
                ->timeout(10)
                ->retry(times: 2, sleepMilliseconds: 200, throw: false)
                ->post('/sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'disable_web_page_preview' => true,
                ])
                ->throw();

            return true;
        } catch (RequestException $exception) {
            Log::warning('Telegram sendMessage failed.', [
                'status' => $exception->response?->status(),
            ]);

            return false;
        } catch (Throwable $exception) {
            Log::warning('Telegram sendMessage failed.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
