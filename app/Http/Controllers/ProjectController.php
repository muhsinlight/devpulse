<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Project::class);

        $projects = $request->user()
            ->projects()
            ->withCount([
                'monitors',
                'monitors as active_monitors_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->latest()
            ->get()
            ->map(fn (Project $project): array => [
                ...$project->toArray(),
                'webhooks_count' => 0,
            ]);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        Gate::authorize('create', Project::class);

        return Inertia::render('Projects/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->projects()->create($request->validated());

        return redirect()->route('projects.show', $project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        Gate::authorize('view', $project);

        $project->loadCount([
            'monitors',
            'monitors as active_monitors_count' => fn ($query) => $query->where('is_active', true),
        ]);

        $monitors = $project->monitors()
            ->latest()
            ->get();

        return Inertia::render('Projects/Show', [
            'project' => [
                ...$project->toArray(),
                'webhooks_count' => 0,
            ],
            'monitors' => $monitors,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response
    {
        Gate::authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('projects.show', $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index');
    }
}
