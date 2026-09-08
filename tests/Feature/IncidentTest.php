<?php

use App\Actions\CheckMonitorAction;
use App\Enums\IncidentStatus;
use App\Enums\MonitorStatus;
use App\Events\IncidentOpened;
use App\Events\IncidentResolved;
use App\Jobs\CheckMonitorJob;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

test('guests are redirected when visiting incidents index', function () {
    $this->get(route('incidents.index'))
        ->assertRedirect(route('login'));
});

test('users can view their incidents index', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();
    $incident = Incident::factory()->for($monitor)->create();

    $this->actingAs($user)
        ->get(route('incidents.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Incidents/Index')
            ->has('incidents', 1)
            ->where('incidents.0.id', $incident->id));
});

test('users do not see other users incidents on index', function () {
    $user = User::factory()->create();
    Incident::factory()->create();

    $this->actingAs($user)
        ->get(route('incidents.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Incidents/Index')
            ->has('incidents', 0));
});

test('users can view their incident', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();
    $incident = Incident::factory()->for($monitor)->create();

    $this->actingAs($user)
        ->get(route('incidents.show', $incident))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Incidents/Show')
            ->where('incident.id', $incident->id));
});

test('users cannot view another users incident', function () {
    $user = User::factory()->create();
    $incident = Incident::factory()->create();

    $this->actingAs($user)
        ->get(route('incidents.show', $incident))
        ->assertForbidden();
});

test('first failed check opens an incident', function () {
    Event::fake([IncidentOpened::class]);
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

    $incident = $monitor->incidents()->first();

    expect($monitor->fresh()->status)->toBe(MonitorStatus::Offline)
        ->and($monitor->incidents()->count())->toBe(1)
        ->and($incident?->status)->toBe(IncidentStatus::Open)
        ->and($incident?->opened_status_code)->toBe(500);

    Event::assertDispatched(IncidentOpened::class);
});

test('repeated failures do not open a second incident', function () {
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
    $this->actingAs($user)->post(route('monitors.check', $monitor));

    expect($monitor->incidents()->count())->toBe(1)
        ->and($monitor->incidents()->open()->count())->toBe(1);
});

test('successful check after an outage resolves the open incident', function () {
    Event::fake([IncidentResolved::class]);
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

    $incident = $monitor->incidents()->first();

    expect($monitor->fresh()->status)->toBe(MonitorStatus::Online)
        ->and($incident?->fresh()->status)->toBe(IncidentStatus::Resolved)
        ->and($incident?->fresh()->resolved_status_code)->toBe(200);

    Event::assertDispatched(IncidentResolved::class);
});

test('a new outage after recovery opens a second incident', function () {
    Http::fake([
        'https://api.example.com/health' => Http::sequence()
            ->push('error', 500)
            ->push(['ok' => true], 200)
            ->push('error', 500),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));
    $this->actingAs($user)->post(route('monitors.check', $monitor));
    $this->actingAs($user)->post(route('monitors.check', $monitor));

    expect($monitor->incidents()->count())->toBe(2)
        ->and($monitor->incidents()->open()->count())->toBe(1)
        ->and($monitor->incidents()->where('status', IncidentStatus::Resolved)->count())->toBe(1);
});

test('pending to online does not open an incident', function () {
    Http::fake([
        'https://api.example.com/health' => Http::response(['ok' => true], 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://api.example.com/health',
        'expected_status_code' => 200,
        'status' => MonitorStatus::Pending,
    ]);

    $this->actingAs($user)->post(route('monitors.check', $monitor));

    expect($monitor->incidents()->count())->toBe(0)
        ->and($monitor->fresh()->status)->toBe(MonitorStatus::Online);
});

test('inactive monitor job does not open an incident', function () {
    Http::fake();

    $monitor = Monitor::factory()->inactive()->create([
        'url' => 'https://api.example.com/health',
        'status' => MonitorStatus::Online,
    ]);

    (new CheckMonitorJob($monitor))->handle(app(CheckMonitorAction::class));

    Http::assertNothingSent();
    expect($monitor->incidents()->count())->toBe(0);
});

test('monitor show includes the open incident', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();
    $incident = Incident::factory()->for($monitor)->create();

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Monitors/Show')
            ->where('openIncident.id', $incident->id)
            ->has('incidents', 1));
});
