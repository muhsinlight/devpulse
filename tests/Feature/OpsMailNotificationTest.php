<?php

use App\Enums\IncidentStatus;
use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MonitorCreatedNotification;
use App\Notifications\MonitorIncidentOpenedNotification;
use App\Notifications\MonitorIncidentResolvedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config([
        'services.telegram.bot_token' => null,
        'services.telegram.chat_id' => null,
        'services.contact.to' => 'ops@example.com',
        'mail.from.address' => 'hello@example.com',
    ]);
});

test('opening an incident emails ops when telegram is not configured', function () {
    Notification::fake();
    Http::fake([
        'https://api.example.com/health' => Http::response('error', 500),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
        'status' => MonitorStatus::Pending,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));

    Notification::assertSentOnDemand(
        MonitorIncidentOpenedNotification::class,
        function (MonitorIncidentOpenedNotification $notification, array $channels, object $notifiable): bool {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'ops@example.com';
        },
    );
});

test('resolving an incident emails ops when telegram is not configured', function () {
    Notification::fake();
    Http::fake([
        'https://api.example.com/health' => Http::sequence()
            ->push('error', 500)
            ->push(['ok' => true], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));
    $this->actingAs($user)->post(route('monitors.check', $monitor));

    Notification::assertSentOnDemand(MonitorIncidentResolvedNotification::class);
});

test('creating a monitor emails ops when telegram is not configured', function () {
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

test('monitor down mail message uses the ops alert template', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Acme']);
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'Payments API',
        'url' => 'https://api.example.com/health',
        'last_response_time_ms' => 3200,
    ]);
    $incident = $monitor->incidents()->create([
        'status' => IncidentStatus::Open,
        'opened_at' => now(),
        'last_error_message' => 'Expected status 200, got 500',
        'opened_status_code' => 500,
    ]);

    $html = (string) (new MonitorIncidentOpenedNotification($incident))->toMail($user)->render();

    expect($html)->toContain('Monitor DOWN')
        ->and($html)->toContain('Payments API')
        ->and($html)->toContain('Acme')
        ->and($html)->toContain('View incident');
});
