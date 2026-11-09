<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-5xl grid md:grid-cols-2 bg-white shadow-md rounded-xl overflow-hidden">

        {{-- Kiri: penjelasan sistem --}}
        <div class="bg-indigo-700 text-white p-8 flex flex-col justify-center">
            <h1 class="text-2xl font-semibold">WebGIS Monitoring Proyek Infrastruktur</h1>
            <p class="mt-3 text-indigo-100 text-sm leading-relaxed">
                Aplikasi peta untuk memantau proyek infrastruktur di lapangan: lokasi, status,
                anggaran, progres harian, dan foto, dalam satu tempat.
            </p>
            <ul class="mt-6 space-y-3 text-sm">
                <li><strong>Peta interaktif</strong> dengan filter status, wilayah, dan radius jarak.</li>
                <li><strong>Status terlambat otomatis</strong> dihitung dari deadline.</li>
                <li><strong>Laporan harian</strong> petugas: progres, catatan, dan foto.</li>
                <li><strong>Dashboard dan Kurva S</strong>, plus export Excel dan PDF.</li>
                <li><strong>Pengingat deadline</strong> lewat email dan <strong>audit log</strong>.</li>
            </ul>
            <p class="mt-6 text-xs text-indigo-200">Dibangun dengan Laravel, PostgreSQL + PostGIS, dan Leaflet.</p>
        </div>

        {{-- Kanan: form login + pilihan role --}}
        <div class="p-8">
            <h2 class="text-xl font-semibold text-gray-800">Masuk</h2>

            <x-auth-session-status class="mb-4 mt-2" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-4">
                @csrf
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                  :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                  required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-between mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @else
                        <span></span>
                    @endif
                    <x-primary-button>{{ __('Log in') }}</x-primary-button>
                </div>
            </form>

            @if (config('app.show_demo_accounts'))
                @php
                    $semuaDemo = [
                        'manajer_proyek' => ['Manajer Proyek', 'Kelola proyek miliknya'],
                        'petugas_lapangan' => ['Petugas Lapangan', 'Isi laporan harian'],
                        'klien' => ['Klien', 'Hanya melihat'],
                    ];
                    $demo = array_intersect_key($semuaDemo, array_flip(config('app.demo_roles')));
                @endphp

                <div class="mt-8 border-t pt-5">
                    <h3 class="text-sm font-semibold text-gray-700">Coba sebagai</h3>
                    <p class="text-xs text-gray-500 mt-1">Klik salah satu role untuk langsung masuk, tanpa password.</p>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @foreach ($demo as $role => [$nama, $deskripsi])
                            <form method="POST" action="{{ route('demo.login', $role) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left border border-gray-200 rounded-lg p-3 hover:border-indigo-500 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <div class="text-sm font-medium text-gray-800">{{ $nama }}</div>
                                    <div class="text-xs text-indigo-600 mt-1">{{ $deskripsi }}</div>
                                </button>
                            </form>
                        @endforeach

                        @if (config('app.admin_request_enabled') && config('app.admin_approver_email'))
                            <a href="{{ route('admin-access.form') }}"
                               class="block text-left border border-amber-300 bg-amber-50 rounded-lg p-3 hover:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <div class="text-sm font-medium text-gray-800">Admin</div>
                                <div class="text-xs text-amber-700 mt-1">Perlu izin pemilik (via email)</div>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>