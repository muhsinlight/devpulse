<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WebhookEndpointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome');
})->name('home');

Route::post('contact', ContactController::class)
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::any('hooks/{token}', [WebhookEndpointController::class, 'ingest'])
    ->name('webhooks.ingest');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('monitors', [MonitorController::class, 'index'])->name('monitors.index');
    Route::get('projects/{project}/monitors/create', [MonitorController::class, 'create'])->name('monitors.create');
    Route::post('projects/{project}/monitors', [MonitorController::class, 'store'])->name('monitors.store');
    Route::get('monitors/{monitor}', [MonitorController::class, 'show'])->name('monitors.show');
    Route::get('monitors/{monitor}/edit', [MonitorController::class, 'edit'])->name('monitors.edit');
    Route::put('monitors/{monitor}', [MonitorController::class, 'update'])->name('monitors.update');
    Route::delete('monitors/{monitor}', [MonitorController::class, 'destroy'])->name('monitors.destroy');
    Route::post('monitors/{monitor}/check', [MonitorController::class, 'check'])->name('monitors.check');

    Route::get('webhooks', [WebhookEndpointController::class, 'index'])->name('webhooks.index');
    Route::get('projects/{project}/webhooks/create', [WebhookEndpointController::class, 'create'])->name('webhooks.create');
    Route::post('projects/{project}/webhooks', [WebhookEndpointController::class, 'store'])->name('webhooks.store');
    Route::get('webhooks/{webhookEndpoint}', [WebhookEndpointController::class, 'show'])->name('webhooks.show');
    Route::get('webhooks/{webhookEndpoint}/edit', [WebhookEndpointController::class, 'edit'])->name('webhooks.edit');
    Route::put('webhooks/{webhookEndpoint}', [WebhookEndpointController::class, 'update'])->name('webhooks.update');
    Route::delete('webhooks/{webhookEndpoint}', [WebhookEndpointController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('webhooks/{webhookEndpoint}/rotate', [WebhookEndpointController::class, 'rotate'])->name('webhooks.rotate');
    Route::post('webhooks/{webhookEndpoint}/rotate-hmac', [WebhookEndpointController::class, 'rotateHmac'])->name('webhooks.rotate-hmac');

    Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
});

require __DIR__.'/auth.php';
