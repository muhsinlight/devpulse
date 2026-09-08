<?php

namespace App\Actions;

use App\Enums\IncidentStatus;
use App\Enums\MonitorStatus;
use App\Events\IncidentOpened;
use App\Events\IncidentResolved;
use App\Models\Monitor;
use App\Models\MonitorResult;

class RecordMonitorIncidentAction
{
    public function handle(Monitor $monitor, MonitorResult $result, MonitorStatus $previousStatus): void
    {
        if ($this->wentOffline($previousStatus, $monitor->status)) {
            $this->openIfNone($monitor, $result);

            return;
        }

        if ($this->recovered($previousStatus, $monitor->status)) {
            $this->resolveOpen($monitor, $result);
        }
    }

    private function wentOffline(MonitorStatus $previousStatus, MonitorStatus $currentStatus): bool
    {
        return $currentStatus === MonitorStatus::Offline
            && $previousStatus !== MonitorStatus::Offline;
    }

    private function recovered(MonitorStatus $previousStatus, MonitorStatus $currentStatus): bool
    {
        return $previousStatus === MonitorStatus::Offline
            && $currentStatus === MonitorStatus::Online;
    }

    private function openIfNone(Monitor $monitor, MonitorResult $result): void
    {
        $alreadyOpen = $monitor->incidents()->open()->exists();

        if ($alreadyOpen) {
            return;
        }

        $incident = $monitor->incidents()->create([
            'status' => IncidentStatus::Open,
            'opened_at' => $result->checked_at,
            'last_error_message' => $result->error_message,
            'opened_status_code' => $result->status_code,
        ]);

        IncidentOpened::dispatch($incident);
    }

    private function resolveOpen(Monitor $monitor, MonitorResult $result): void
    {
        $incident = $monitor->incidents()->open()->latest('opened_at')->first();

        if ($incident === null) {
            return;
        }

        $incident->update([
            'status' => IncidentStatus::Resolved,
            'resolved_at' => $result->checked_at,
            'resolved_status_code' => $result->status_code,
        ]);

        IncidentResolved::dispatch($incident);
    }
}
