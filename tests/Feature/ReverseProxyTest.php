<?php

test('treats the request as secure when the reverse proxy sends x-forwarded-proto', function () {
    $response = $this->get(route('home'), [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Port' => '443',
    ]);

    $response->assertOk();
    expect(request()->secure())->toBeTrue();
});
