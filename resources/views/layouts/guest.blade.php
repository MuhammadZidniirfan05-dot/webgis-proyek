<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'WebGIS Proyek') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-6 flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-lg bg-indigo-600 text-white">
                <x-icon name="map-pin" class="h-5 w-5" />
            </span>
            <span class="text-lg font-semibold text-slate-900">WebGIS Proyek</span>
        </a>

        <div class="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:max-w-md sm:p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>