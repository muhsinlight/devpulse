<?php

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('webhooks.{id}', function (User $user, string $id): bool {
    $endpoint = WebhookEndpoint::query()->with('project')->find($id);

    return $endpoint !== null
        && $endpoint->project->user_id === $user->id;
});
