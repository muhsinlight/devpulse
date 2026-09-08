<?php

namespace App\Console\Commands;

use App\Models\WebhookRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('webhooks:prune-requests')]
#[Description('Delete captured webhook requests older than the configured retention period')]
class PruneWebhookRequestsCommand extends Command
{
    private const int ChunkSize = 500;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) config('webhooks.request_retention_days');

        if ($days < 1) {
            $this->info('Webhook request retention is disabled.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);
        $deleted = 0;

        do {
            $ids = WebhookRequest::query()
                ->where('received_at', '<', $cutoff)
                ->orderBy('id')
                ->limit(self::ChunkSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted += WebhookRequest::query()->whereIn('id', $ids)->delete();
        } while ($ids->count() === self::ChunkSize);

        $this->info("Pruned {$deleted} webhook request(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
