<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

test('password defaults require mixed case and a number', function () {
    $failing = Validator::make(
        ['password' => 'password'],
        ['password' => ['required', Password::defaults()]],
    );

    expect($failing->fails())->toBeTrue()
        ->and($failing->errors()->has('password'))->toBeTrue();

    $passing = Validator::make(
        ['password' => 'Password1'],
        ['password' => ['required', Password::defaults()]],
    );

    expect($passing->passes())->toBeTrue();
});
