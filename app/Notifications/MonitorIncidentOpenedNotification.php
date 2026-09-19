<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MonitorIncidentOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Incident $incident)
    {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): string
    {
        $this->incident->loadMissing('monitor.project');

        $monitor = $this->incident->monitor;
        $project = $monitor->project;
        $error = $this->incident->last_error_message ?: 'No error message';
        $statusCode = $this->incident->opened_status_code ?? 'n/a';
        $url = route('incidents.show', $this->incident);

        return implode("\n", [
            '🔴 Monitor DOWN',
            "Project: {$project->name}",
            "Monitor: {$monitor->name}",
            "URL: {$monitor->url}",
            "Status: {$statusCode}",
            "Error: {$error}",
            "Incident: {$url}",
        ]);
    }
}
