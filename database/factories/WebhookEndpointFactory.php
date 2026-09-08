<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEndpoint>
 */
class WebhookEndpointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => implode(' ', Arr::wrap(fake()->words(2))).' Webhook',
            'token' => Str::random(48),
            'is_active' => true,
            'hmac_required' => false,
            'hmac_secret' => null,
            'last_received_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function hmacRequired(): static
    {
        return $this->state(fn (): array => [
            'hmac_required' => true,
            'hmac_secret' => Str::random(48),
        ]);
    }
}
