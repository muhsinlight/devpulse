<?php

use App\Events\WebhookRequestReceived;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WebhookRequest;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Torann\GeoIP\Facades\GeoIP;
use Torann\GeoIP\Location;

test('guests are redirected when visiting webhooks index', function () {
    $this->get(route('webhooks.index'))
        ->assertRedirect(route('login'));
});

test('users can view their webhooks index', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->for($project)->create();

    $this->actingAs($user)
        ->get(route('webhooks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Webhooks/Index')
            ->has('webhooks', 1)
            ->where('webhooks.0.id', $webhook->id)
            ->missing('webhooks.0.hmac_secret'));
});

test('users do not see other users webhooks on index', function () {
    $user = User::factory()->create();
    WebhookEndpoint::factory()->create();

    $this->actingAs($user)
        ->get(route('webhooks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Webhooks/Index')
            ->has('webhooks', 0));
});

test('users can create a webhook endpoint', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('webhooks.store', $project), [
        'name' => 'Stripe events',
    ]);

    $webhook = WebhookEndpoint::query()->where('project_id', $project->id)->first();

    expect($webhook)->not->toBeNull()
        ->and($webhook->name)->toBe('Stripe events')
        ->and($webhook->is_active)->toBeTrue()
        ->and($webhook->hmac_required)->toBeFalse()
        ->and(strlen($webhook->token))->toBe(48);

    $response->assertRedirect(route('webhooks.show', $webhook));
});

test('users can create a webhook that requires hmac signatures', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)->post(route('webhooks.store', $project), [
        'name' => 'GitHub',
        'hmac_required' => true,
    ]);

    $webhook = WebhookEndpoint::query()->where('project_id', $project->id)->first();

    expect($webhook)->not->toBeNull()
        ->and($webhook->hmac_required)->toBeTrue()
        ->and($webhook->hmac_secret)->not->toBeNull()
        ->and(strlen((string) $webhook->hmac_secret))->toBe(48);
});

test('users cannot create a webhook on another users project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->post(route('webhooks.store', $project), [
            'name' => 'Hacked',
        ])
        ->assertForbidden();
});

test('users can view their webhook show page with ingest url', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->for($project)->create();

    $this->actingAs($user)
        ->get(route('webhooks.show', $webhook))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Webhooks/Show')
            ->where('webhook.id', $webhook->id)
            ->where('webhook.ingest_url', route('webhooks.ingest', $webhook->token))
            ->where('webhook.hmac_secret', $webhook->hmac_secret)
            ->where('retention_days', (int) config('webhooks.request_retention_days'))
            ->has('requests'));
});

test('users cannot view another users webhook', function () {
    $user = User::factory()->create();
    $webhook = WebhookEndpoint::factory()->create();

    $this->actingAs($user)
        ->get(route('webhooks.show', $webhook))
        ->assertForbidden();
});

test('users can rotate the ingest token', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->for($project)->create();
    $oldToken = $webhook->token;

    $this->actingAs($user)
        ->post(route('webhooks.rotate', $webhook))
        ->assertRedirect(route('webhooks.show', $webhook));

    $webhook->refresh();

    expect($webhook->token)->not->toBe($oldToken)
        ->and(strlen($webhook->token))->toBe(48);

    $this->postJson(route('webhooks.ingest', $oldToken), ['ping' => true])
        ->assertNotFound();
});

test('guests can ingest a json payload without csrf', function () {
    Event::fake([WebhookRequestReceived::class]);

    $webhook = WebhookEndpoint::factory()->create();

    $this->postJson(route('webhooks.ingest', $webhook->token), [
        'event' => 'invoice.paid',
    ])->assertOk()
        ->assertJson(['ok' => true]);

    $request = WebhookRequest::query()->first();

    expect($request)->not->toBeNull()
        ->and($request->method)->toBe('POST')
        ->and($request->payload)->toMatchArray(['event' => 'invoice.paid'])
        ->and($request->ip_country)->toBeNull()
        ->and($request->ip_city)->toBeNull();

    $webhook->refresh();
    expect($webhook->last_received_at)->not->toBeNull();

    Event::assertDispatched(WebhookRequestReceived::class);
});

test('ingest stores geoip city and country for a public ip', function () {
    Event::fake([WebhookRequestReceived::class]);

    GeoIP::shouldReceive('getLocation')
        ->once()
        ->andReturn(new Location([
            'ip' => '8.8.8.8',
            'iso_code' => 'US',
            'country' => 'United States',
            'city' => 'Mountain View',
            'default' => false,
        ]));

    $webhook = WebhookEndpoint::factory()->create();

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->postJson(route('webhooks.ingest', $webhook->token), [
            'event' => 'dns',
        ])
        ->assertOk();

    $request = WebhookRequest::query()->first();

    expect($request?->ip_iso_code)->toBe('US')
        ->and($request?->ip_country)->toBe('United States')
        ->and($request?->ip_city)->toBe('Mountain View');
});

test('unknown ingest tokens return 404', function () {
    $this->postJson(route('webhooks.ingest', 'missing-token'), ['a' => 1])
        ->assertNotFound();

    expect(WebhookRequest::query()->count())->toBe(0);
});

test('hmac required endpoints return 401 without a signature', function () {
    $webhook = WebhookEndpoint::factory()->hmacRequired()->create();

    $this->postJson(route('webhooks.ingest', $webhook->token), ['a' => 1])
        ->assertUnauthorized();

    expect(WebhookRequest::query()->count())->toBe(0);
});

test('hmac required endpoints reject an invalid signature with 401', function () {
    $webhook = WebhookEndpoint::factory()->hmacRequired()->create();

    $this->withHeaders(['X-Hub-Signature-256' => 'sha256=deadbeef'])
        ->postJson(route('webhooks.ingest', $webhook->token), ['a' => 1])
        ->assertUnauthorized();

    expect(WebhookRequest::query()->count())->toBe(0);
});

test('hmac required endpoints accept a valid github style signature', function () {
    Event::fake([WebhookRequestReceived::class]);

    $webhook = WebhookEndpoint::factory()->hmacRequired()->create();
    $payload = ['event' => 'push'];
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $signature = hash_hmac('sha256', $body, (string) $webhook->hmac_secret);

    $this->withHeaders(['X-Hub-Signature-256' => 'sha256='.$signature])
        ->postJson(route('webhooks.ingest', $webhook->token), $payload)
        ->assertOk();

    expect(WebhookRequest::query()->count())->toBe(1);
});

test('users can rotate the hmac secret', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->hmacRequired()->for($project)->create();
    $oldSecret = $webhook->hmac_secret;

    $this->actingAs($user)
        ->post(route('webhooks.rotate-hmac', $webhook))
        ->assertRedirect(route('webhooks.show', $webhook));

    $webhook->refresh();

    expect($webhook->hmac_secret)->not->toBe($oldSecret)
        ->and($webhook->hmac_required)->toBeTrue();

    $payload = ['event' => 'push'];
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $oldSignature = hash_hmac('sha256', $body, (string) $oldSecret);

    $this->withHeaders(['X-Hub-Signature-256' => 'sha256='.$oldSignature])
        ->postJson(route('webhooks.ingest', $webhook->token), $payload)
        ->assertUnauthorized();
});

test('inactive endpoints reject ingest', function () {
    $webhook = WebhookEndpoint::factory()->inactive()->create();

    $this->postJson(route('webhooks.ingest', $webhook->token), ['a' => 1])
        ->assertNotFound();

    expect(WebhookRequest::query()->count())->toBe(0);
});

test('get query parameters are stored on ingest', function () {
    Event::fake([WebhookRequestReceived::class]);

    $webhook = WebhookEndpoint::factory()->create();

    $this->get(route('webhooks.ingest', ['token' => $webhook->token, 'ping' => '1']))
        ->assertOk();

    $request = WebhookRequest::query()->first();

    expect($request?->method)->toBe('GET')
        ->and($request?->query_params)->toMatchArray(['ping' => '1']);
});

test('users can update and pause a webhook', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->for($project)->create();

    $this->actingAs($user)
        ->put(route('webhooks.update', $webhook), [
            'name' => 'Renamed',
            'is_active' => false,
        ])
        ->assertRedirect(route('webhooks.show', $webhook));

    $webhook->refresh();

    expect($webhook->name)->toBe('Renamed')
        ->and($webhook->is_active)->toBeFalse();
});

test('webhook private channel callback allows owners only', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->for($project)->create();
    $foreign = WebhookEndpoint::factory()->create();

    $callback = Broadcast::connection()->getChannels()['webhooks.{id}'] ?? null;

    expect($callback)->toBeCallable()
        ->and($callback($user, (string) $webhook->id))->toBeTrue()
        ->and($callback($user, (string) $foreign->id))->toBeFalse();
});

test('users cannot rotate another users hmac secret', function () {
    $user = User::factory()->create();
    $webhook = WebhookEndpoint::factory()->hmacRequired()->create();

    $this->actingAs($user)
        ->post(route('webhooks.rotate-hmac', $webhook))
        ->assertForbidden();
});

test('users can delete their webhook', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $webhook = WebhookEndpoint::factory()->for($project)->create();

    $this->actingAs($user)
        ->delete(route('webhooks.destroy', $webhook))
        ->assertRedirect(route('projects.show', $project));

    expect(WebhookEndpoint::query()->find($webhook->id))->toBeNull();
});
