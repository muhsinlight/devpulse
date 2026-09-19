<?php

use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Services\TelegramBot;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('test suite does not load real telegram credentials from env', function () {
    expect(config('services.telegram.bot_token'))->toBeEmpty()
        ->and(config('services.telegram.chat_id'))->toBeEmpty()
        ->and(app(TelegramBot::class)->isConfigured())->toBeFalse();
});

test('monitor checks do not call the telegram api', function () {
    Http::fake([
        'https://api.example.com/health' => Http::response('error', 500),
        'https://api.telegram.org/*' => Http::response(['ok' => true]),
    ]);

    // Simulate a developer who has Telegram in .env (config only for this test).
    config([
        'services.telegram.bot_token' => 'should-not-send',
        'services.telegram.chat_id' => '999',
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
        'status' => MonitorStatus::Pending,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.telegram.org'));
});
