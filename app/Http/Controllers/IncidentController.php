<?php

namespace App\Http\Controllers;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    /**
     * Display all incidents for the authenticated user's monitors.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Incident::class);

        $incidents = Incident::query()
            ->whereHas(
                'monitor.project',
                fn ($query) => $query->where('user_id', $request->user()->id),
            )
            ->with([
                'monitor:id,project_id,name,url,status',
                'monitor.project:id,name,color,slug',
            ])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [IncidentStatus::Open->value])
            ->latest('opened_at')
            ->get();

        return Inertia::render('Incidents/Index', [
            'incidents' => $incidents,
        ]);
    }

    /**
     * Display the specified incident.
     */
    public function show(Incident $incident): Response
    {
        Gate::authorize('view', $incident);

        $incident->load([
            'monitor:id,project_id,name,url,method,status',
            'monitor.project:id,name,color,slug,user_id',
        ]);

        return Inertia::render('Incidents/Show', [
            'incident' => $incident,
        ]);
    }
}
