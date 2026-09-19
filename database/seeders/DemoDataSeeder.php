<?php

namespace Database\Seeders;

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

        Project::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'internal-tools'],
            [
                'name' => 'Internal Tools',
                'description' => 'Staging and staff services',
                'color' => '#6366f1',
            ],
        );

        $this->seedWebhookInbox($production);
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
