<?php

namespace Database\Seeders;

use App\Enums\HttpMethod;
use App\Enums\IncidentStatus;
use App\Enums\MonitorStatus;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorResult;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WebhookRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed local/demo records. Never runs in production.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $user = User::query()->where('email', 'test@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $production = Project::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'acme-production'],
            [
                'name' => 'Acme Production',
                'description' => 'Public APIs and billing webhooks',
                'color' => '#10b981',
            ],
        );

        $internal = Project::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'internal-tools'],
            [
                'name' => 'Internal Tools',
                'description' => 'Staging and staff services',
                'color' => '#6366f1',
            ],
        );

        $this->seedOnlineMonitor($production);
        $this->seedOfflineMonitor($production);
        $this->seedPendingMonitor($internal);
        $this->seedWebhookInbox($production);
    }

    private function seedOnlineMonitor(Project $project): Monitor
    {
        $monitor = Monitor::query()->updateOrCreate(
            ['project_id' => $project->id, 'name' => 'Production API Health'],
            [
                'url' => 'https://api.acme.test/health',
                'method' => HttpMethod::Get,
                'headers' => null,
                'body' => null,
                'check_interval' => 5,
                'expected_status_code' => 200,
                'timeout_seconds' => 10,
                'status' => MonitorStatus::Online,
                'last_checked_at' => now()->subMinutes(2),
                'last_status_code' => 200,
                'last_response_time_ms' => 118,
                'uptime_percentage' => 99.20,
                'is_active' => true,
                'next_check_at' => now()->addMinutes(3),
            ],
        );

        if ($monitor->results()->exists()) {
            return $monitor;
        }

        MonitorResult::factory()->for($monitor)->create([
            'status_code' => 200,
            'response_time_ms' => 110,
            'is_success' => true,
            'checked_at' => now()->subHours(6),
        ]);

        $outageStart = now()->subHours(5)->subMinutes(12);
        $outageEnd = now()->subHours(5);

        MonitorResult::factory()->failed()->for($monitor)->create([
            'checked_at' => $outageStart,
            'response_time_ms' => 2400,
        ]);

        MonitorResult::factory()->for($monitor)->create([
            'status_code' => 200,
            'response_time_ms' => 102,
            'is_success' => true,
            'checked_at' => $outageEnd,
        ]);

        MonitorResult::factory()->for($monitor)->create([
            'status_code' => 200,
            'response_time_ms' => 118,
            'is_success' => true,
            'checked_at' => now()->subMinutes(2),
        ]);

        Incident::factory()->resolved()->for($monitor)->create([
            'opened_at' => $outageStart,
            'resolved_at' => $outageEnd,
            'last_error_message' => 'Expected status 200, got 500',
            'opened_status_code' => 500,
            'resolved_status_code' => 200,
        ]);

        return $monitor;
    }

    private function seedOfflineMonitor(Project $project): Monitor
    {
        $monitor = Monitor::query()->updateOrCreate(
            ['project_id' => $project->id, 'name' => 'Payments Gateway'],
            [
                'url' => 'https://payments.acme.test/ready',
                'method' => HttpMethod::Get,
                'headers' => null,
                'body' => null,
                'check_interval' => 1,
                'expected_status_code' => 200,
                'timeout_seconds' => 10,
                'status' => MonitorStatus::Offline,
                'last_checked_at' => now()->subMinute(),
                'last_status_code' => 503,
                'last_response_time_ms' => 41,
                'uptime_percentage' => 86.40,
                'is_active' => true,
                'next_check_at' => now()->addMinute(),
            ],
        );

        if ($monitor->results()->exists()) {
            return $monitor;
        }

        MonitorResult::factory()->for($monitor)->create([
            'status_code' => 200,
            'response_time_ms' => 90,
            'is_success' => true,
            'checked_at' => now()->subHours(2),
        ]);

        $openedAt = now()->subMinutes(18);

        MonitorResult::factory()->failed()->for($monitor)->create([
            'status_code' => 503,
            'error_message' => 'Expected status 200, got 503',
            'response_time_ms' => 41,
            'checked_at' => $openedAt,
        ]);

        MonitorResult::factory()->failed()->for($monitor)->create([
            'status_code' => 503,
            'error_message' => 'Expected status 200, got 503',
            'response_time_ms' => 38,
            'checked_at' => now()->subMinute(),
        ]);

        Incident::factory()->for($monitor)->create([
            'status' => IncidentStatus::Open,
            'opened_at' => $openedAt,
            'resolved_at' => null,
            'last_error_message' => 'Expected status 200, got 503',
            'opened_status_code' => 503,
        ]);

        return $monitor;
    }

    private function seedPendingMonitor(Project $project): Monitor
    {
        return Monitor::query()->updateOrCreate(
            ['project_id' => $project->id, 'name' => 'Staff Dashboard'],
            [
                'url' => 'https://staff.acme.test/health',
                'method' => HttpMethod::Get,
                'headers' => null,
                'body' => null,
                'check_interval' => 15,
                'expected_status_code' => 200,
                'timeout_seconds' => 10,
                'status' => MonitorStatus::Pending,
                'last_checked_at' => null,
                'last_status_code' => null,
                'last_response_time_ms' => null,
                'uptime_percentage' => 100,
                'is_active' => true,
                'next_check_at' => now()->addMinutes(15),
            ],
        );
    }

    private function seedWebhookInbox(Project $project): void
    {
        $endpoint = WebhookEndpoint::query()->firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Stripe events'],
            [
                'token' => Str::random(48),
                'is_active' => true,
                'hmac_required' => false,
                'hmac_secret' => null,
                'last_received_at' => now()->subMinutes(8),
            ],
        );

        if ($endpoint->requests()->exists()) {
            return;
        }

        WebhookRequest::factory()->for($endpoint, 'endpoint')->create([
            'payload' => ['type' => 'invoice.paid', 'id' => 'evt_demo_1'],
            'raw_body' => '{"type":"invoice.paid","id":"evt_demo_1"}',
            'received_at' => now()->subHours(3),
        ]);

        WebhookRequest::factory()->for($endpoint, 'endpoint')->create([
            'payload' => ['type' => 'customer.updated', 'id' => 'evt_demo_2'],
            'raw_body' => '{"type":"customer.updated","id":"evt_demo_2"}',
            'received_at' => now()->subMinutes(8),
        ]);
    }
}
