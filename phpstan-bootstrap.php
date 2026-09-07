<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

$laravelVersion = Application::VERSION;

if (! defined('LARAVEL_VERSION')) {
    define('LARAVEL_VERSION', $laravelVersion);
}

if (! defined('Larastan\Larastan\LARAVEL_VERSION')) {
    define('Larastan\Larastan\LARAVEL_VERSION', $laravelVersion);
}
