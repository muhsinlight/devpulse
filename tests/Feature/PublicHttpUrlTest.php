<?php

use App\Models\Project;
use App\Models\User;
use App\Rules\PublicHttpUrl;
use Illuminate\Support\Facades\Validator;

test('public http url accepts public https hosts', function () {
    $validator = Validator::make(
        ['url' => 'https://api.example.com/health'],
        ['url' => ['required', 'url', new PublicHttpUrl]],
    );

    expect($validator->passes())->toBeTrue();
});

test('public http url rejects localhost and private addresses', function (string $url) {
    $validator = Validator::make(
        ['url' => $url],
        ['url' => ['required', 'url', new PublicHttpUrl]],
    );

    expect($validator->fails())->toBeTrue();
})->with([
    'http://127.0.0.1/health',
    'http://localhost/health',
    'http://api.local/health',
    'http://10.0.0.5/health',
    'http://192.168.1.1/health',
    'http://169.254.169.254/latest/meta-data',
    'ftp://example.com/file',
]);

test('users cannot create a monitor targeting a private url', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)->from(route('monitors.create', $project))->post(route('monitors.store', $project), [
        'name' => 'Internal API',
        'url' => 'http://127.0.0.1/health',
        'method' => 'GET',
        'headers' => null,
        'body' => null,
        'check_interval' => 5,
        'expected_status_code' => 200,
        'timeout_seconds' => 10,
        'is_active' => true,
    ]);

    $response
        ->assertRedirect(route('monitors.create', $project))
        ->assertSessionHasErrors('url');

    expect($project->monitors()->count())->toBe(0);
});
