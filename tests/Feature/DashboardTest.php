<?php

use App\Enums\MonitorStatus;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WebhookRequest;

test('guests are redirected when visiting the dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated users see empty dashboard stats', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.total_projects', 0)
            ->where('stats.total_monitors', 0)
            ->where('stats.online_monitors', 0)
            ->where('stats.offline_monitors', 0)
            ->where('stats.average_uptime', null)
            ->where('stats.average_response_time_ms', null)
            ->where('stats.total_webhook_requests_today', 0)
            ->where('stats.open_incidents', 0)
            ->has('openIncidents', 0));
});

test('dashboard stats only include the authenticated users data', function () {
    $this->freezeTime();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    Monitor::factory()->for($project)->create([
        'status' => MonitorStatus::Online,
        'uptime_percentage' => 99.5,
        'last_response_time_ms' => 100,
    ]);

    $offline = Monitor::factory()->for($project)->create([
        'status' => MonitorStatus::Offline,
        'uptime_percentage' => 90.5,
        'last_response_time_ms' => 200,
    ]);

    Incident::factory()->for($offline)->create();

    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    WebhookRequest::factory()->for($endpoint, 'endpoint')->create([
        'received_at' => now(),
    ]);
    WebhookRequest::factory()->for($endpoint, 'endpoint')->create([
        'received_at' => now()->subDay(),
    ]);

    $otherProject = Project::factory()->create();
    $otherMonitor = Monitor::factory()->for($otherProject)->create([
        'status' => MonitorStatus::Offline,
        'uptime_percentage' => 10,
        'last_response_time_ms' => 9000,
    ]);
    Incident::factory()->for($otherMonitor)->create();
    WebhookRequest::factory()->create([
        'received_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.total_projects', 1)
            ->where('stats.total_monitors', 2)
            ->where('stats.online_monitors', 1)
            ->where('stats.offline_monitors', 1)
            ->where('stats.average_uptime', 95)
            ->where('stats.average_response_time_ms', 150)
            ->where('stats.total_webhook_requests_today', 1)
            ->where('stats.open_incidents', 1)
            ->has('openIncidents', 1)
            ->where('openIncidents.0.monitor_id', $offline->id));
});
