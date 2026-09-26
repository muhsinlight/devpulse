<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script>
            (function () {
                try {
                    var key = 'devpulse-theme';
                    var stored = localStorage.getItem(key);
                    var theme =
                        stored === 'light' || stored === 'dark'
                            ? stored
                            : window.matchMedia('(prefers-color-scheme: dark)').matches
                              ? 'dark'
                              : 'light';
                    document.documentElement.setAttribute('data-theme', theme);
                } catch (e) {}
            })();
        </script>

        <link rel="icon" href="{{ asset('favicon-32x32.png') }}?v={{ filemtime(public_path('favicon-32x32.png')) }}" type="image/png" sizes="32x32">
        <link rel="icon" href="{{ asset('images/logo-mark.png') }}?v={{ filemtime(public_path('images/logo-mark.png')) }}" type="image/png" sizes="any">
        <link rel="icon" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}" type="image/svg+xml">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ filemtime(public_path('apple-touch-icon.png')) }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'DevPulse') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
