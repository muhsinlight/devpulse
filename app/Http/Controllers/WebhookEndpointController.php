<?php

namespace App\Http\Controllers;

use App\Actions\IngestWebhookRequestAction;
use App\Http\Requests\StoreWebhookEndpointRequest;
use App\Http\Requests\UpdateWebhookEndpointRequest;
use App\Models\Project;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WebhookEndpointController extends Controller
{
    /**
     * Display all webhook endpoints for the authenticated user.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', WebhookEndpoint::class);

        $webhooks = WebhookEndpoint::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('project:id,name,color,slug')
            ->withCount('requests')
            ->latest()
            ->get();

        return Inertia::render('Webhooks/Index', [
            'webhooks' => $webhooks,
        ]);
    }

    /**
     * Show the form for creating a webhook endpoint under a project.
     */
    public function create(Project $project): Response
    {
        Gate::authorize('create', [WebhookEndpoint::class, $project]);

        return Inertia::render('Webhooks/Create', [
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created webhook endpoint.
     */
    public function store(StoreWebhookEndpointRequest $request, Project $project): RedirectResponse
    {
        $webhookEndpoint = $project->webhooks()->create($request->validated());

        return redirect()->route('webhooks.show', $webhookEndpoint);
    }

    /**
     * Display the specified webhook endpoint.
     */
    public function show(WebhookEndpoint $webhookEndpoint): Response
    {
        Gate::authorize('view', $webhookEndpoint);

        $webhookEndpoint->load('project:id,name,color,slug,user_id');

        $requests = $webhookEndpoint->requests()
            ->latest('received_at')
            ->limit(50)
            ->get();

        return Inertia::render('Webhooks/Show', [
            'webhook' => [
                ...$webhookEndpoint->toArray(),
                'ingest_url' => route('webhooks.ingest', $webhookEndpoint->token),
                'hmac_secret' => $webhookEndpoint->hmac_secret,
            ],
            'requests' => $requests,
            'retention_days' => (int) config('webhooks.request_retention_days'),
        ]);
    }

    /**
     * Show the form for editing the specified webhook endpoint.
     */
    public function edit(WebhookEndpoint $webhookEndpoint): Response
    {
        Gate::authorize('update', $webhookEndpoint);

        $webhookEndpoint->load('project:id,name,color,slug');

        return Inertia::render('Webhooks/Edit', [
            'webhook' => $webhookEndpoint,
        ]);
    }

    /**
     * Update the specified webhook endpoint.
     */
    public function update(UpdateWebhookEndpointRequest $request, WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $webhookEndpoint->update($request->validated());

        return redirect()->route('webhooks.show', $webhookEndpoint);
    }

    /**
     * Remove the specified webhook endpoint.
     */
    public function destroy(WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        Gate::authorize('delete', $webhookEndpoint);

        $project = $webhookEndpoint->project;
        $webhookEndpoint->delete();

        return redirect()->route('projects.show', $project);
    }

    /**
     * Replace the ingest token with a new random value.
     */
    public function rotate(WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        Gate::authorize('update', $webhookEndpoint);

        $webhookEndpoint->rotateToken();

        return redirect()
            ->route('webhooks.show', $webhookEndpoint)
            ->with('success', 'Ingest URL rotated. Update any services that post to the old URL.');
    }

    /**
     * Replace the HMAC secret and require signed inbound requests.
     */
    public function rotateHmac(WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        Gate::authorize('update', $webhookEndpoint);

        $webhookEndpoint->rotateHmacSecret();

        return redirect()
            ->route('webhooks.show', $webhookEndpoint)
            ->with('success', 'HMAC secret rotated. Update any services that sign with the old secret.');
    }

    /**
     * Capture an inbound webhook HTTP request.
     */
    public function ingest(Request $request, string $token, IngestWebhookRequestAction $ingest): JsonResponse
    {
        $webhookRequest = $ingest->handle($request, $token);

        if ($webhookRequest === null) {
            abort(404);
        }

        return response()->json(['ok' => true]);
    }
}
