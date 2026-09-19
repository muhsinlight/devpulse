<?php

namespace App\Notifications;

use App\Models\Incident;
use App\Services\OpsNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return app(OpsNotifier::class)->channels();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->incident->loadMissing('monitor.project');

        $monitor = $this->incident->monitor;
        $project = $monitor->project;
        $responseTime = $monitor->last_response_time_ms !== null
            ? "{$monitor->last_response_time_ms}ms"
            : 'n/a';

        return (new MailMessage)
            ->subject("Monitor DOWN: {$monitor->name}")
            ->markdown('mail.ops.alert', [
                'title' => 'Monitor DOWN',
                'intro' => 'A monitored endpoint failed its health check and an incident was opened.',
                'rows' => [
                    'Incident' => '#'.$this->incident->id,
                    'Project' => $project->name,
                    'Monitor' => $monitor->name,
                    'URL' => $monitor->url,
                    'Status' => (string) ($this->incident->opened_status_code ?? 'n/a'),
                    'Response time' => $responseTime,
                    'Opened' => $this->incident->opened_at?->toDateTimeString() ?? 'n/a',
                    'Error' => $this->incident->last_error_message ?: 'No error message',
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
        $error = $this->incident->last_error_message ?: 'No error message';
        $statusCode = $this->incident->opened_status_code ?? 'n/a';
        $responseTime = $monitor->last_response_time_ms !== null
            ? "{$monitor->last_response_time_ms}ms"
            : 'n/a';
        $openedAt = $this->incident->opened_at?->toDateTimeString() ?? 'n/a';
        $url = route('incidents.show', $this->incident);

        return implode("\n", [
            '🔴 Monitor DOWN',
            "Incident #{$this->incident->id}",
            "Project: {$project->name}",
            "Monitor: {$monitor->name}",
            "URL: {$monitor->url}",
            "Status: {$statusCode}",
            "Response time: {$responseTime}",
            "Opened: {$openedAt}",
            "Error: {$error}",
            "Incident: {$url}",
        ]);
    }
}
