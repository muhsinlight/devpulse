<?php

namespace App\Http\Controllers;

use App\Enums\MonitorStatus;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\WebhookRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard for authenticated users.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $ownedMonitors = Monitor::query()->whereHas(
            'project',
            fn ($query) => $query->where('user_id', $user->id),
        );

        $averageUptime = (clone $ownedMonitors)->avg('uptime_percentage');
        $averageResponseTime = (clone $ownedMonitors)->avg('last_response_time_ms');

        $ownedOpenIncidents = Incident::query()
            ->open()
            ->whereHas(
                'monitor.project',
                fn ($query) => $query->where('user_id', $user->id),
            );

        $openIncidents = (clone $ownedOpenIncidents)
            ->with([
                'monitor:id,project_id,name,url,status',
                'monitor.project:id,name,color,slug',
            ])
            ->latest('opened_at')
            ->limit(8)
            ->get();

        $webhookRequestsToday = WebhookRequest::query()
            ->whereHas(
                'endpoint.project',
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->where('received_at', '>=', now()->startOfDay())
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_projects' => $user->projects()->count(),
                'total_monitors' => (clone $ownedMonitors)->count(),
                'online_monitors' => (clone $ownedMonitors)->where('status', MonitorStatus::Online)->count(),
                'offline_monitors' => (clone $ownedMonitors)->where('status', MonitorStatus::Offline)->count(),
                'degraded_monitors' => (clone $ownedMonitors)->where('status', MonitorStatus::Degraded)->count(),
                'pending_monitors' => (clone $ownedMonitors)->where('status', MonitorStatus::Pending)->count(),
                'average_uptime' => $averageUptime === null ? null : round((float) $averageUptime, 2),
                'average_response_time_ms' => $averageResponseTime === null
                    ? null
                    : (int) round((float) $averageResponseTime),
                'total_webhook_requests_today' => $webhookRequestsToday,
                'open_incidents' => (clone $ownedOpenIncidents)->count(),
            ],
            'openIncidents' => $openIncidents,
        ]);
    }
}
