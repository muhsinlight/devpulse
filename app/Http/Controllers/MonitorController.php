<?php

namespace App\Http\Controllers;

use App\Actions\CheckMonitorAction;
use App\Enums\MonitorStatus;
use App\Http\Requests\StoreMonitorRequest;
use App\Http\Requests\UpdateMonitorRequest;
use App\Models\Monitor;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller
{
    /**
     * Display all monitors for the authenticated user.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Monitor::class);

        $monitors = Monitor::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('project:id,name,color,slug')
            ->latest()
            ->get();

        return Inertia::render('Monitors/Index', [
            'monitors' => $monitors,
        ]);
    }

    /**
     * Show the form for creating a monitor under a project.
     */
    public function create(Project $project): Response
    {
        Gate::authorize('create', [Monitor::class, $project]);

        return Inertia::render('Monitors/Create', [
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created monitor.
     */
    public function store(StoreMonitorRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $monitor = $project->monitors()->create([
            ...$validated,
            'status' => MonitorStatus::Pending,
            'uptime_percentage' => 100,
            'next_check_at' => ($validated['is_active'] ?? true) ? now() : null,
        ]);

        return redirect()->route('monitors.show', $monitor);
    }

    /**
     * Display the specified monitor.
     */
    public function show(Monitor $monitor): Response
    {
        Gate::authorize('view', $monitor);

        $monitor->load('project:id,name,color,slug,user_id');

        $results = $monitor->results()
            ->latest('checked_at')
            ->limit(50)
            ->get();

        return Inertia::render('Monitors/Show', [
            'monitor' => $monitor,
            'results' => $results,
        ]);
    }

    /**
     * Show the form for editing the specified monitor.
     */
    public function edit(Monitor $monitor): Response
    {
        Gate::authorize('update', $monitor);

        $monitor->load('project:id,name,color,slug');

        return Inertia::render('Monitors/Edit', [
            'monitor' => $monitor,
        ]);
    }

    /**
     * Update the specified monitor.
     */
    public function update(UpdateMonitorRequest $request, Monitor $monitor): RedirectResponse
    {
        $monitor->fill($request->validated());

        if (! $monitor->is_active) {
            $monitor->next_check_at = null;
        } elseif ($monitor->isDirty(['check_interval', 'is_active'])) {
            $monitor->next_check_at = now()->addMinutes($monitor->check_interval);
        }

        $monitor->save();

        return redirect()->route('monitors.show', $monitor);
    }

    /**
     * Remove the specified monitor.
     */
    public function destroy(Monitor $monitor): RedirectResponse
    {
        Gate::authorize('delete', $monitor);

        $project = $monitor->project;
        $monitor->delete();

        return redirect()->route('projects.show', $project);
    }

    /**
     * Run a manual health check for the monitor.
     */
    public function check(Monitor $monitor, CheckMonitorAction $checkMonitor): RedirectResponse
    {
        Gate::authorize('check', $monitor);

        $result = $checkMonitor->handle($monitor);

        return redirect()
            ->route('monitors.show', $monitor)
            ->with(
                'success',
                $result->is_success
                    ? 'Check completed successfully.'
                    : 'Check completed with a failure.'
            );
    }
}
