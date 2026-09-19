<?php

use App\Models\Project;
use App\Models\User;
use App\Notifications\MonitorCreatedNotification;
use Illuminate\Support\Facades\Notification;

test('creating a monitor sends a telegram notification when configured', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id' => '123456',
    ]);

    Notification::fake();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)->post(route('monitors.store', $project), [
        'name' => 'Production API',
        'url' => 'https://api.example.com/health',
        'method' => 'GET',
        'headers' => null,
        'body' => null,
        'check_interval' => 5,
        'expected_status_code' => 200,
        'timeout_seconds' => 10,
        'is_active' => true,
    ]);

    Notification::assertSentOnDemand(MonitorCreatedNotification::class);
});

test('creating a monitor does not notify when telegram and ops email are unset', function () {
    config([
        'services.telegram.bot_token' => null,
        'services.telegram.chat_id' => null,
        'services.contact.to' => null,
        'mail.from.address' => null,
    ]);

    Notification::fake();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)->post(route('monitors.store', $project), [
        'name' => 'Production API',
        'url' => 'https://api.example.com/health',
        'method' => 'GET',
        'headers' => null,
        'body' => null,
        'check_interval' => 5,
        'expected_status_code' => 200,
        'timeout_seconds' => 10,
        'is_active' => true,
    ]);

    Notification::assertNothingSent();
});
