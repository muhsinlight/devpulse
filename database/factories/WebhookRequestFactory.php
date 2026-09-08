<?php

namespace Database\Factories;

use App\Models\WebhookEndpoint;
use App\Models\WebhookRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookRequest>
 */
class WebhookRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'webhook_endpoint_id' => WebhookEndpoint::factory(),
            'ip_address' => fake()->ipv4(),
            'ip_iso_code' => 'US',
            'ip_country' => 'United States',
            'ip_city' => 'New York',
            'method' => 'POST',
            'headers' => ['content-type' => 'application/json'],
            'query_params' => null,
            'payload' => ['ok' => true],
            'raw_body' => '{"ok":true}',
            'content_type' => 'application/json',
            'received_at' => now(),
        ];
    }
}
