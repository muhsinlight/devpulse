<?php

use App\Models\User;

test('guests see the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Welcome'));
});

test('the document head links to the DevPulse favicon', function () {
    $this->get(route('home'))
        ->assertSee('/favicon-32x32.png', false)
        ->assertSee('/images/logo-mark.png', false)
        ->assertSee('/favicon.svg', false)
        ->assertDontSee('laravel.com/img', false);
});

test('authenticated users are redirected from home to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});
