<?php

namespace Database\Factories;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
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
            'status' => IncidentStatus::Open,
            'opened_at' => now()->subMinutes(15),
            'resolved_at' => null,
            'last_error_message' => 'Expected status 200, got 500',
            'opened_status_code' => 500,
            'resolved_status_code' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => IncidentStatus::Resolved,
            'resolved_at' => now(),
            'resolved_status_code' => 200,
        ]);
    }
}
