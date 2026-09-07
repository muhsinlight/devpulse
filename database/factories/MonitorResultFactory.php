<?php

namespace Database\Factories;

use App\Models\Monitor;
use App\Models\MonitorResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitorResult>
 */
class MonitorResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'monitor_id' => Monitor::factory(),
            'status_code' => 200,
            'response_time_ms' => fake()->numberBetween(20, 400),
            'is_success' => true,
            'error_message' => null,
            'checked_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => 500,
            'is_success' => false,
            'error_message' => 'Expected status 200, got 500',
        ]);
    }
}
