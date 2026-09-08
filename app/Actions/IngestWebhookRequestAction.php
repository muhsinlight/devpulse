<?php

namespace App\Actions;

use App\Events\WebhookRequestReceived;
use App\Models\WebhookEndpoint;
use App\Models\WebhookRequest;
use Illuminate\Http\Request;
use Throwable;
use Torann\GeoIP\Facades\GeoIP;

class IngestWebhookRequestAction
{
    private const int MaxBodyBytes = 65_536;

    public function handle(Request $request, string $token): ?WebhookRequest
    {
        $endpoint = WebhookEndpoint::query()
            ->where('token', $token)
            ->where('is_active', true)
            ->first();

        if ($endpoint === null) {
            return null;
        }

        $rawBody = $request->getContent();

        if ($endpoint->hmac_required && ! $this->hasValidHmac($request, $endpoint, $rawBody)) {
            abort(401);
        }

        $decoded = json_decode($rawBody, true);
        $payload = is_array($decoded) ? $decoded : null;

        if (strlen($rawBody) > self::MaxBodyBytes) {
            $rawBody = substr($rawBody, 0, self::MaxBodyBytes)."\n[truncated]";
        }

        $ipAddress = $request->ip();

        $webhookRequest = $endpoint->requests()->create([
            'ip_address' => $ipAddress,
            ...$this->locateIp($ipAddress),
            'method' => strtoupper($request->method()),
            'headers' => $this->captureHeaders($request),
            'query_params' => $request->query() === [] ? null : $request->query(),
            'payload' => $payload,
            'raw_body' => $rawBody === '' ? null : $rawBody,
            'content_type' => $request->header('Content-Type'),
            'received_at' => now(),
        ]);

        $endpoint->update([
            'last_received_at' => $webhookRequest->received_at,
        ]);

        try {
            WebhookRequestReceived::dispatch($webhookRequest);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $webhookRequest;
    }

    /**
     * @return array{ip_iso_code: string|null, ip_country: string|null, ip_city: string|null}
     */
    private function locateIp(?string $ip): array
    {
        $empty = [
            'ip_iso_code' => null,
            'ip_country' => null,
            'ip_city' => null,
        ];

        if ($ip === null || $ip === '') {
            return $empty;
        }

        try {
            $location = GeoIP::getLocation($ip);
        } catch (Throwable) {
            return $empty;
        }

        if ($location->default) {
            return $empty;
        }

        $country = $location->country;
        $city = $location->city;
        $isoCode = $location->iso_code;

        return [
            'ip_iso_code' => is_string($isoCode) && $isoCode !== '' ? $isoCode : null,
            'ip_country' => is_string($country) && $country !== '' ? $country : null,
            'ip_city' => is_string($city) && $city !== '' ? $city : null,
        ];
    }

    private function hasValidHmac(Request $request, WebhookEndpoint $endpoint, string $rawBody): bool
    {
        $secret = $endpoint->hmac_secret;

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $provided = $this->providedSignature($request);

        if ($provided === null) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $provided);
    }

    private function providedSignature(Request $request): ?string
    {
        $headers = [
            $request->header('X-Hub-Signature-256'),
            $request->header('X-Webhook-Signature'),
            $request->header('X-Signature'),
        ];

        foreach ($headers as $header) {
            if (! is_string($header) || $header === '') {
                continue;
            }

            if (preg_match('/^sha256=(.+)$/i', $header, $matches) === 1) {
                return strtolower($matches[1]);
            }

            return strtolower($header);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function captureHeaders(Request $request): array
    {
        /** @var array<string, mixed> $headers */
        $headers = collect($request->headers->all())
            ->except(['cookie'])
            ->map(function (array $values): mixed {
                return count($values) === 1 ? $values[0] : $values;
            })
            ->all();

        return $headers;
    }
}
