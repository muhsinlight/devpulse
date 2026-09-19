<?php

use App\Models\User;

beforeEach(function () {
    config(['app.registration_enabled' => true]);
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register with hashed password', function () {
    $response = $this->post('/register', [
        'name' => 'Test Developer',
        'email' => 'test@devpulse.io',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@devpulse.io')->first();
    expect($user)->not->toBeNull();
    expect($user->password)->not->toBe('Password1');
    expect(password_verify('Password1', $user->password))->toBeTrue();
});

test('registration rejects weak passwords', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test Developer',
        'email' => 'weak@devpulse.io',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertRedirect('/register')
        ->assertSessionHasErrors('password');

    $this->assertGuest();
});
