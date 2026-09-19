<?php

use App\Models\User;

test('registration routes return not found when disabled', function () {
    config(['app.registration_enabled' => false]);

    $this->get(route('register'))->assertNotFound();

    $this->post('/register', [
        'name' => 'Test Developer',
        'email' => 'test@devpulse.io',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertNotFound();

    expect(User::query()->where('email', 'test@devpulse.io')->exists())->toBeFalse();
});

test('welcome page hides register links when registration is disabled', function () {
    config(['app.registration_enabled' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('canRegister', false));
});

test('login page hides register link when registration is disabled', function () {
    config(['app.registration_enabled' => false]);

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/Login')
            ->where('canRegister', false));
});
