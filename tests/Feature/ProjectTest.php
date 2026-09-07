<?php

use App\Models\Project;
use App\Models\User;

test('guests are redirected to login when visiting projects index', function () {
    $this->get(route('projects.index'))
        ->assertRedirect(route('login'));
});

test('users can view their projects index', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.id', $project->id)
            ->where('projects.0.name', $project->name));
});

test('users do not see other users projects on index', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Project::factory()->for($otherUser)->create(['name' => 'Secret Project']);

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Index')
            ->has('projects', 0));
});

test('users can view the create project screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('projects.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects/Create'));
});

test('users can create a project', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('projects.store'), [
        'name' => 'KPSSHUB API',
        'description' => 'KPSS preparation platform backend',
        'color' => '#3b82f6',
    ]);

    $project = Project::query()->where('user_id', $user->id)->first();

    expect($project)->not->toBeNull()
        ->and($project->name)->toBe('KPSSHUB API')
        ->and($project->slug)->toBe('kpsshub-api')
        ->and($project->description)->toBe('KPSS preparation platform backend')
        ->and($project->color)->toBe('#3b82f6');

    $response->assertRedirect(route('projects.show', $project));
});

test('project creation requires a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name' => '',
            'description' => 'Missing name',
        ])
        ->assertSessionHasErrors('name');

    expect(Project::query()->count())->toBe(0);
});

test('users can view their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Show')
            ->where('project.id', $project->id)
            ->where('project.name', $project->name));
});

test('users cannot view another users project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for(User::factory())->create();

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertForbidden();
});

test('users can update their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $response = $this->actingAs($user)->put(route('projects.update', $project), [
        'name' => 'New Name',
        'description' => 'Updated description',
        'color' => '#ef4444',
    ]);

    $project->refresh();

    expect($project->name)->toBe('New Name')
        ->and($project->slug)->toBe('new-name')
        ->and($project->description)->toBe('Updated description')
        ->and($project->color)->toBe('#ef4444');

    $response->assertRedirect(route('projects.show', $project));
});

test('users cannot update another users project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for(User::factory())->create();

    $this->actingAs($user)
        ->put(route('projects.update', $project), [
            'name' => 'Hacked',
            'description' => null,
            'color' => '#10b981',
        ])
        ->assertForbidden();
});

test('users can delete their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('projects.destroy', $project));

    expect(Project::query()->find($project->id))->toBeNull();
    $response->assertRedirect(route('projects.index'));
});

test('users cannot delete another users project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for(User::factory())->create();

    $this->actingAs($user)
        ->delete(route('projects.destroy', $project))
        ->assertForbidden();

    expect(Project::query()->find($project->id))->not->toBeNull();
});

test('project slugs stay unique per user', function () {
    $user = User::factory()->create();

    Project::factory()->for($user)->create([
        'name' => 'My API',
        'slug' => 'my-api',
    ]);

    $this->actingAs($user)->post(route('projects.store'), [
        'name' => 'My API',
        'description' => null,
        'color' => '#10b981',
    ]);

    $slugs = Project::query()->where('user_id', $user->id)->pluck('slug')->all();

    expect($slugs)->toContain('my-api', 'my-api-1');
});
