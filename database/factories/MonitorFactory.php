<?php

namespace Database\Factories;

use App\Enums\HttpMethod;
use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<Monitor>
 */
class MonitorFactory extends Factory
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
            'name' => implode(' ', Arr::wrap(fake()->words(3))).' Health',
            'url' => fake()->url(),
            'method' => HttpMethod::Get,
            'headers' => null,
            'body' => null,
            'check_interval' => fake()->randomElement([1, 5, 15, 30, 60]),
            'expected_status_code' => 200,
            'timeout_seconds' => 10,
            'status' => MonitorStatus::Pending,
            'last_checked_at' => null,
            'last_status_code' => null,
            'last_response_time_ms' => null,
            'uptime_percentage' => 100,
            'is_active' => true,
            'next_check_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
            'next_check_at' => null,
        ]);
    }

    public function notDue(): static
    {
        return $this->state(fn (): array => [
            'next_check_at' => now()->addHour(),
        ]);
    }
}
