<?php

use App\Actions\CheckMonitorAction;
use App\Enums\MonitorStatus;
use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

test('dispatch due command pushes jobs for due active monitors', function () {
    Queue::fake();
    $this->freezeTime();

    $monitor = Monitor::factory()->create([
        'check_interval' => 5,
        'next_check_at' => now()->subMinute(),
    ]);

    $this->artisan('monitors:dispatch-due')
        ->expectsOutput('Dispatched 1 monitor check(s).')
        ->assertSuccessful();

    Queue::assertPushed(CheckMonitorJob::class, function (CheckMonitorJob $job) use ($monitor) {
        return $job->monitor->is($monitor);
    });

    expect($monitor->fresh()->next_check_at?->toDateTimeString())
        ->toBe(now()->addMinutes(5)->toDateTimeString());
});

test('dispatch due command skips monitors that are not due', function () {
    Queue::fake();
    $this->freezeTime();

    Monitor::factory()->notDue()->create();

    $this->artisan('monitors:dispatch-due')
        ->expectsOutput('Dispatched 0 monitor check(s).')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('dispatch due command skips inactive monitors', function () {
    Queue::fake();
    $this->freezeTime();

    Monitor::factory()->inactive()->create([
        'next_check_at' => now()->subMinute(),
    ]);

    $this->artisan('monitors:dispatch-due')
        ->expectsOutput('Dispatched 0 monitor check(s).')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('check monitor job runs the action and records a result', function () {
    Http::fake([
        'https://api.example.com/health' => Http::response(['ok' => true], 200),
    ]);

    $this->freezeTime();

    $monitor = Monitor::factory()->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
        'check_interval' => 5,
        'next_check_at' => now()->subMinute(),
    ]);

    (new CheckMonitorJob($monitor))->handle(app(CheckMonitorAction::class));

    $monitor->refresh();

    expect($monitor->status)->toBe(MonitorStatus::Online)
        ->and($monitor->results()->count())->toBe(1)
        ->and($monitor->results()->first()->is_success)->toBeTrue()
        ->and($monitor->next_check_at?->toDateTimeString())
        ->toBe(now()->addMinutes(5)->toDateTimeString());
});

test('check monitor job is a no-op when monitor is inactive', function () {
    Http::fake();

    $monitor = Monitor::factory()->inactive()->create([
        'url' => 'https://api.example.com/health',
    ]);

    (new CheckMonitorJob($monitor))->handle(app(CheckMonitorAction::class));

    Http::assertNothingSent();
    expect($monitor->results()->count())->toBe(0);
});

test('claimed due monitor is not dispatched again on a second command run', function () {
    Queue::fake();
    $this->freezeTime();

    $monitor = Monitor::factory()->create([
        'check_interval' => 5,
        'next_check_at' => now()->subMinute(),
    ]);

    $this->artisan('monitors:dispatch-due')->assertSuccessful();
    $this->artisan('monitors:dispatch-due')
        ->expectsOutput('Dispatched 0 monitor check(s).')
        ->assertSuccessful();

    Queue::assertPushed(CheckMonitorJob::class, 1);
    expect($monitor->fresh()->next_check_at?->toDateTimeString())
        ->toBe(now()->addMinutes(5)->toDateTimeString());
});

test('creating a monitor sets next_check_at to now', function () {
    $this->freezeTime();

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
    ])->assertRedirect();

    $monitor = Monitor::query()->first();

    expect($monitor->next_check_at?->toDateTimeString())->toBe(now()->toDateTimeString());
});

test('deactivating a monitor clears next_check_at', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'check_interval' => 5,
        'next_check_at' => now()->addMinutes(5),
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('monitors.update', $monitor), [
        'name' => $monitor->name,
        'url' => $monitor->url,
        'method' => $monitor->method->value,
        'headers' => null,
        'body' => null,
        'check_interval' => 5,
        'expected_status_code' => 200,
        'timeout_seconds' => 10,
        'is_active' => false,
    ])->assertRedirect();

    expect($monitor->fresh()->next_check_at)->toBeNull();
});
