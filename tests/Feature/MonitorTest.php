<?php

use App\Enums\HttpMethod;
use App\Enums\MonitorStatus;
use App\Models\Monitor;
use App\Models\MonitorResult;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

test('guests are redirected when visiting monitors index', function () {
    $this->get(route('monitors.index'))
        ->assertRedirect(route('login'));
});

test('guests are redirected when visiting monitor create', function () {
    $project = Project::factory()->create();

    $this->get(route('monitors.create', $project))
        ->assertRedirect(route('login'));
});

test('users can view their monitors index', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    $this->actingAs($user)
        ->get(route('monitors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Monitors/Index')
            ->has('monitors', 1)
            ->where('monitors.0.id', $monitor->id));
});

test('users do not see other users monitors on index', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();
    Monitor::factory()->for($otherProject)->create();

    $this->actingAs($user)
        ->get(route('monitors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Monitors/Index')
            ->has('monitors', 0));
});

test('users can view the create monitor screen for their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('monitors.create', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Monitors/Create')
            ->where('project.id', $project->id));
});

test('users cannot view create monitor for another users project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->get(route('monitors.create', $project))
        ->assertForbidden();
});

test('users can create a monitor', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('monitors.store', $project), [
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

    $monitor = Monitor::query()->where('project_id', $project->id)->first();

    expect($monitor)->not->toBeNull()
        ->and($monitor->name)->toBe('Production API')
        ->and($monitor->url)->toBe('https://api.example.com/health')
        ->and($monitor->method)->toBe(HttpMethod::Get)
        ->and($monitor->status)->toBe(MonitorStatus::Pending);

    $response->assertRedirect(route('monitors.show', $monitor));
});

test('users cannot create a monitor on another users project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->post(route('monitors.store', $project), [
            'name' => 'Hacked',
            'url' => 'https://api.example.com/health',
            'method' => 'GET',
            'check_interval' => 5,
            'expected_status_code' => 200,
            'timeout_seconds' => 10,
        ])
        ->assertForbidden();

    expect(Monitor::query()->count())->toBe(0);
});

test('monitor creation validates url and interval', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('monitors.store', $project), [
            'name' => 'Bad Monitor',
            'url' => 'not-a-url',
            'method' => 'GET',
            'check_interval' => 7,
            'expected_status_code' => 200,
            'timeout_seconds' => 10,
        ])
        ->assertSessionHasErrors(['url', 'check_interval']);
});

test('users can view their monitor', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Monitors/Show')
            ->where('monitor.id', $monitor->id)
            ->has('results'));
});

test('users cannot view another users monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->create();

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertForbidden();
});

test('users can update their monitor', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'Old Name',
    ]);

    $response = $this->actingAs($user)->put(route('monitors.update', $monitor), [
        'name' => 'New Name',
        'url' => 'https://api.example.com/v2/health',
        'method' => 'GET',
        'headers' => ['Accept' => 'application/json'],
        'body' => null,
        'check_interval' => 15,
        'expected_status_code' => 204,
        'timeout_seconds' => 20,
        'is_active' => true,
    ]);

    $monitor->refresh();

    expect($monitor->name)->toBe('New Name')
        ->and($monitor->check_interval)->toBe(15)
        ->and($monitor->expected_status_code)->toBe(204);

    $response->assertRedirect(route('monitors.show', $monitor));
});

test('users cannot update another users monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->create();

    $this->actingAs($user)
        ->put(route('monitors.update', $monitor), [
            'name' => 'Hacked',
            'url' => 'https://api.example.com/health',
            'method' => 'GET',
            'check_interval' => 5,
            'expected_status_code' => 200,
            'timeout_seconds' => 10,
        ])
        ->assertForbidden();
});

test('users can delete their monitor', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    $response = $this->actingAs($user)->delete(route('monitors.destroy', $monitor));

    expect(Monitor::query()->find($monitor->id))->toBeNull();
    $response->assertRedirect(route('projects.show', $project));
});

test('users cannot delete another users monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->create();

    $this->actingAs($user)
        ->delete(route('monitors.destroy', $monitor))
        ->assertForbidden();

    expect(Monitor::query()->find($monitor->id))->not->toBeNull();
});

test('manual check marks monitor online on success', function () {
    Http::fake([
        'https://api.example.com/health' => Http::response(['ok' => true], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
    ]);

    $this->actingAs($user)
        ->post(route('monitors.check', $monitor))
        ->assertRedirect(route('monitors.show', $monitor));

    $monitor->refresh();

    expect($monitor->status)->toBe(MonitorStatus::Online)
        ->and($monitor->last_status_code)->toBe(200)
        ->and($monitor->last_checked_at)->not->toBeNull()
        ->and($monitor->results()->count())->toBe(1)
        ->and($monitor->results()->first()->is_success)->toBeTrue();
});

test('manual check marks monitor offline on unexpected status', function () {
    Http::fake([
        'https://api.example.com/health' => Http::response('error', 500),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));

    $monitor->refresh();
    $result = $monitor->results()->first();

    expect($monitor->status)->toBe(MonitorStatus::Offline)
        ->and($result->is_success)->toBeFalse()
        ->and($result->status_code)->toBe(500)
        ->and($result->error_message)->toContain('Expected status 200');
});

test('manual check marks monitor offline on connection failure', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection timed out');
    });

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));

    $monitor->refresh();
    $result = $monitor->results()->first();

    expect($monitor->status)->toBe(MonitorStatus::Offline)
        ->and($result->is_success)->toBeFalse()
        ->and($result->error_message)->toContain('Connection timed out');
});

test('users cannot check another users monitor', function () {
    Http::fake();

    $user = User::factory()->create();
    $monitor = Monitor::factory()->create();

    $this->actingAs($user)
        ->post(route('monitors.check', $monitor))
        ->assertForbidden();

    Http::assertNothingSent();
});

test('uptime percentage is recalculated after mixed results', function () {
    Http::fake([
        'https://api.example.com/health' => Http::sequence()
            ->push(['ok' => true], 200)
            ->push('fail', 500),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));
    $this->actingAs($user)->post(route('monitors.check', $monitor));

    $monitor->refresh();

    expect(MonitorResult::query()->where('monitor_id', $monitor->id)->count())->toBe(2)
        ->and((float) $monitor->uptime_percentage)->toBe(50.0);
});

test('project show includes monitors and counts', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    Monitor::factory()->for($project)->count(2)->create();

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Show')
            ->has('monitors', 2)
            ->where('project.monitors_count', 2));
});
