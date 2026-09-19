<?php

use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MonitorIncidentOpenedNotification;
use App\Notifications\MonitorIncidentResolvedNotification;
use App\Services\TelegramBot;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

test('opening an incident sends a telegram notification when configured', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id' => '123456',
    ]);

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

    Notification::assertSentOnDemand(MonitorIncidentOpenedNotification::class);
});

test('resolving an incident sends a telegram notification when configured', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id' => '123456',
    ]);

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

test('incidents do not notify telegram when bot is not configured', function () {
    config([
        'services.telegram.bot_token' => null,
        'services.telegram.chat_id' => null,
    ]);

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

    Notification::assertNothingSent();
});

test('telegram bot posts sendMessage to the telegram api', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id' => '123456',
    ]);

    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true]),
    ]);

    $sent = app(TelegramBot::class)->sendMessage('hello');

    expect($sent)->toBeTrue();

    Http::assertSent(function (Request $request): bool {
        return str_contains($request->url(), 'api.telegram.org/bottest-token/sendMessage')
            && $request['chat_id'] === '123456'
            && $request['text'] === 'hello';
    });
});
