<?php

use App\Models\User;

test('the first user can register when public registration is disabled', function () {
    config(['app.registration_enabled' => false]);

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/Register'));

    $this->post('/register', [
        'name' => 'Test Developer',
        'email' => 'owner@devpulse.io',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    expect(User::query()->where('email', 'owner@devpulse.io')->exists())->toBeTrue();
});

test('login and welcome show a register link when no users exist', function () {
    config(['app.registration_enabled' => false]);

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/Login')
            ->where('canRegister', true));

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('canRegister', true));
});

test('registration routes return not found when disabled and a user already exists', function () {
    config(['app.registration_enabled' => false]);
    User::factory()->create();

    $this->get(route('register'))->assertNotFound();

    $this->post('/register', [
        'name' => 'Test Developer',
        'email' => 'test@devpulse.io',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertNotFound();

    expect(User::query()->where('email', 'test@devpulse.io')->exists())->toBeFalse();
});

test('welcome page hides register links when registration is disabled and a user exists', function () {
    config(['app.registration_enabled' => false]);
    User::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('canRegister', false));
});

test('login page hides register link when registration is disabled and a user exists', function () {
    config(['app.registration_enabled' => false]);
    User::factory()->create();

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/Login')
            ->where('canRegister', false));
});
