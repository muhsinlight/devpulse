<?php

declare(strict_types=1);

/**
 * Larastan StubFilesExtension reads LARAVEL_VERSION before PHPStan bootstrapFiles run.
 * Inside the Larastan\Larastan namespace that resolves to Larastan\Larastan\LARAVEL_VERSION
 * (PHP 8+ does not fall back to the global constant), so define both early via Composer.
 */

use Illuminate\Foundation\Application;

if (! class_exists(Application::class)) {
    return;
}

$laravelVersion = Application::VERSION;

if (! defined('LARAVEL_VERSION')) {
    define('LARAVEL_VERSION', $laravelVersion);
}

if (! defined('Larastan\Larastan\LARAVEL_VERSION')) {
    define('Larastan\Larastan\LARAVEL_VERSION', $laravelVersion);
}
