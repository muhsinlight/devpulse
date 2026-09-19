<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MonitorIncidentResolvedNotification extends Notification implements ShouldQueue
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
        $statusCode = $this->incident->resolved_status_code ?? 'n/a';
        $url = route('incidents.show', $this->incident);

        return implode("\n", [
            '🟢 Monitor RECOVERED',
            "Project: {$project->name}",
            "Monitor: {$monitor->name}",
            "URL: {$monitor->url}",
            "Status: {$statusCode}",
            "Incident: {$url}",
        ]);
    }
}
