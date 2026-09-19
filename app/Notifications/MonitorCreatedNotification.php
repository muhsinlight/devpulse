<?php

namespace App\Notifications;

use App\Models\Monitor;
use App\Services\OpsNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonitorCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Monitor $monitor)
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
        $this->monitor->loadMissing('project');

        $project = $this->monitor->project;

        return (new MailMessage)
            ->subject("Monitor created: {$this->monitor->name}")
            ->markdown('mail.ops.alert', [
                'title' => 'Monitor created',
                'intro' => 'A new HTTP monitor was added to DevPulse.',
                'rows' => [
                    'Project' => $project->name,
                    'Monitor' => $this->monitor->name,
                    'URL' => $this->monitor->url,
                    'Method' => $this->monitor->method->value,
                    'Interval' => $this->monitor->check_interval.' min',
                    'Expected status' => (string) $this->monitor->expected_status_code,
                ],
                'actionUrl' => route('monitors.show', $this->monitor),
                'actionLabel' => 'View monitor',
            ]);
    }

    public function toTelegram(object $notifiable): string
    {
        $this->monitor->loadMissing('project');

        $project = $this->monitor->project;
        $method = $this->monitor->method->value;
        $url = route('monitors.show', $this->monitor);

        return implode("\n", [
            '🆕 Monitor created',
            "Project: {$project->name}",
            "Monitor: {$this->monitor->name}",
            "URL: {$this->monitor->url}",
            "Method: {$method}",
            "Interval: {$this->monitor->check_interval} min",
            "Expected status: {$this->monitor->expected_status_code}",
            "View: {$url}",
        ]);
    }
}
