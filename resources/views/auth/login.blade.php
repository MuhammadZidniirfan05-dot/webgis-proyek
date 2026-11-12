<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - {{ config('app.name', 'WebGIS Proyek') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="flex min-h-screen items-center justify-center p-4 sm:p-6">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:grid-cols-5">

        {{-- Kiri: penjelasan sistem --}}
        <section class="relative overflow-hidden bg-indigo-950 p-6 text-white sm:p-8 md:col-span-2 md:p-10">
            <svg class="pointer-events-none absolute inset-0 h-full w-full text-indigo-400 opacity-25"
                 viewBox="0 0 400 600" preserveAspectRatio="xMidYMid slice" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M0 100H400M0 200H400M0 300H400M0 400H400M0 500H400M100 0V600M200 0V600M300 0V600" stroke-width="1" opacity=".5"/>
                <path d="M0 330C90 300 170 360 260 310S360 270 400 285" stroke-width="2"/>
                <path d="M70 430L190 370L300 410L330 510L160 550Z" stroke-width="2"/>
                <circle cx="230" cy="450" r="92" stroke-dasharray="6 6" stroke-width="1.5"/>
                <circle cx="230" cy="450" r="5" fill="currentColor" stroke="none"/>
            </svg>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-indigo-500 text-white">
                        <x-icon name="map-pin" class="h-5 w-5" />
                    </span>
                    <span class="font-semibold">WebGIS Proyek</span>
                </div>

                <h1 class="mt-8 text-2xl font-semibold leading-snug md:mt-14 md:text-3xl">
                    Pantau proyek infrastruktur dari satu peta
                </h1>
                <p class="mt-3 text-sm leading-relaxed text-indigo-200">
                    Lokasi, progres harian, foto, dan anggaran semua proyek tercatat rapi dan mudah ditelusuri.
                </p>

                <ul class="mt-8 hidden space-y-3 text-sm text-indigo-100 md:block">
                    @foreach ([
                        'Peta interaktif dengan filter status, wilayah, dan radius jarak',
                        'Status terlambat dihitung otomatis dari deadline',
                        'Laporan harian petugas lapangan lengkap dengan foto',
                        'Dashboard, Kurva S, serta export Excel dan PDF',
                    ] as $butir)
                        <li class="flex gap-3">
                            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-300" />
                            <span>{{ $butir }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Kanan: form masuk dan pilihan peran --}}
        <section class="p-6 sm:p-8 md:col-span-3 md:p-10">
            <h2 class="text-xl font-semibold text-slate-900">Masuk</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan akunmu, atau coba sebagai salah satu peran di bawah.</p>

            <x-auth-session-status class="mb-4 mt-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                                  :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" class="mt-1 block w-full" type="password" name="password"
                                  required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label for="remember_me" class="inline-flex items-center gap-2">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <x-primary-button class="w-full">Masuk</x-primary-button>
            </form>

            @if (config('app.show_demo_accounts'))
                @php
                    $semuaDemo = [
                        'manajer_proyek' => ['Manajer proyek', 'Mengelola proyek miliknya', 'M'],
                        'petugas_lapangan' => ['Petugas lapangan', 'Mengisi laporan harian', 'P'],
                        'klien' => ['Klien', 'Hanya melihat', 'K'],
                    ];
                    $demo = array_intersect_key($semuaDemo, array_flip(config('app.demo_roles')));
                @endphp

                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-slate-500">atau coba sebagai</span></div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($demo as $role => [$nama, $deskripsi, $inisial])
                        <form method="POST" action="{{ route('demo.login', $role) }}">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center gap-3 rounded-xl border border-slate-200 p-3 text-left transition hover:border-indigo-400 hover:bg-indigo-50/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">{{ $inisial }}</span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-slate-900">{{ $nama }}</span>
                                    <span class="block text-xs text-slate-500">{{ $deskripsi }}</span>
                                </span>
                            </button>
                        </form>
                    @endforeach

                    @if (config('app.admin_request_enabled') && config('app.admin_approver_email'))
                        <a href="{{ route('admin-access.form') }}"
                           class="flex items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-3 transition hover:border-amber-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-amber-100 text-sm font-semibold text-amber-800">A</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-slate-900">Admin</span>
                                <span class="block text-xs text-amber-800">Perlu izin pemilik lewat email</span>
                            </span>
                        </a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
</body>
</html>