<?php

namespace App\Notifications;

use App\Models\Incident;
use App\Services\OpsNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return app(OpsNotifier::class)->channels();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->incident->loadMissing('monitor.project');

        $monitor = $this->incident->monitor;
        $project = $monitor->project;

        return (new MailMessage)
            ->subject("Monitor RECOVERED: {$monitor->name}")
            ->markdown('mail.ops.alert', [
                'title' => 'Monitor RECOVERED',
                'intro' => 'The monitored endpoint is healthy again and the incident was resolved.',
                'rows' => [
                    'Incident' => '#'.$this->incident->id,
                    'Project' => $project->name,
                    'Monitor' => $monitor->name,
                    'URL' => $monitor->url,
                    'Status' => (string) ($this->incident->resolved_status_code ?? 'n/a'),
                    'Resolved' => $this->incident->resolved_at?->toDateTimeString() ?? 'n/a',
                ],
                'actionUrl' => route('incidents.show', $this->incident),
                'actionLabel' => 'View incident',
            ]);
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
