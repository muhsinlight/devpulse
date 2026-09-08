<?php

use App\Models\WebhookEndpoint;
use App\Models\WebhookRequest;
use Illuminate\Console\Scheduling\Schedule;

test('prune command deletes webhook requests older than the retention window', function () {
    $this->freezeTime();
    config(['webhooks.request_retention_days' => 30]);

    $endpoint = WebhookEndpoint::factory()->create();
    $stale = WebhookRequest::factory()->for($endpoint, 'endpoint')->create([
        'received_at' => now()->subDays(31),
    ]);
    $fresh = WebhookRequest::factory()->for($endpoint, 'endpoint')->create([
        'received_at' => now()->subDays(5),
    ]);

    $this->artisan('webhooks:prune-requests')
        ->expectsOutput('Pruned 1 webhook request(s) older than 30 day(s).')
        ->assertSuccessful();

    expect(WebhookRequest::query()->find($stale->id))->toBeNull()
        ->and(WebhookRequest::query()->find($fresh->id))->not->toBeNull();
});

test('prune command keeps requests inside the retention window', function () {
    $this->freezeTime();
    config(['webhooks.request_retention_days' => 30]);

    WebhookRequest::factory()->create([
        'received_at' => now()->subDays(30),
    ]);

    $this->artisan('webhooks:prune-requests')
        ->expectsOutput('Pruned 0 webhook request(s) older than 30 day(s).')
        ->assertSuccessful();

    expect(WebhookRequest::query()->count())->toBe(1);
});

test('prune command is a no-op when retention days is zero', function () {
    $this->freezeTime();
    config(['webhooks.request_retention_days' => 0]);

    WebhookRequest::factory()->create([
        'received_at' => now()->subYears(1),
    ]);

    $this->artisan('webhooks:prune-requests')
        ->expectsOutput('Webhook request retention is disabled.')
        ->assertSuccessful();

    expect(WebhookRequest::query()->count())->toBe(1);
});

test('scheduler runs webhook prune daily', function () {
    $event = collect(app(Schedule::class)->events())->first(
        fn ($scheduledEvent): bool => str_contains((string) $scheduledEvent->command, 'webhooks:prune-requests'),
    );

    expect($event)->not->toBeNull();
    expect($event?->expression)->toBe('0 0 * * *');
});
