<?php

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Database\Seeders\DemoDataSeeder;

test('demo seeder creates sample projects monitors webhooks and incidents', function () {
    $this->seed(DemoDataSeeder::class);

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and(Project::query()->where('user_id', $user->id)->count())->toBe(2)
        ->and(Monitor::query()->count())->toBe(3)
        ->and(WebhookEndpoint::query()->count())->toBe(1)
        ->and(Incident::query()->open()->count())->toBe(1)
        ->and(Incident::query()->where('status', IncidentStatus::Resolved)->count())->toBe(1);
});

test('demo seeder is a no-op in production', function () {
    app()->instance('env', 'production');

    (new DemoDataSeeder)->run();

    expect(User::query()->count())->toBe(0)
        ->and(Project::query()->count())->toBe(0)
        ->and(Incident::query()->count())->toBe(0);
});
