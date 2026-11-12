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
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-2 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm">Lewati ke konten</a>

    <div x-data="{ menu: false }" class="min-h-screen lg:flex">
        @include('layouts.navigation')

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Bilah atas khusus ponsel --}}
            <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-slate-200 bg-white px-4 lg:hidden">
                <button type="button" @click="menu = true" aria-label="Buka menu"
                        class="-ml-2 rounded-md p-2 text-slate-600 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                    <x-icon name="menu" class="h-6 w-6" />
                </button>
                <span class="font-semibold text-slate-900">WebGIS Proyek</span>
            </header>

            @isset($header)
                <div class="border-b border-slate-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main id="konten" class="flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>