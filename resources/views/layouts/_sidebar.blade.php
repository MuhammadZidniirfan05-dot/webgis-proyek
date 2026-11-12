@php
    $menu = [
        ['Dashboard', 'dashboard', 'dashboard', null, 'layout-dashboard'],
        ['Peta', 'map.index', 'map.*', null, 'map-pin'],
        ['Proyek', 'projects.index', 'projects.*', null, 'folder'],
        ['Wilayah', 'regions.index', 'regions.*', 'admin', 'layers'],
        ['Pengguna', 'users.index', 'users.*', 'admin', 'users'],
        ['Audit log', 'audit.index', 'audit.*', 'admin', 'file-text'],
    ];
    $pengguna = auth()->user();
    $namaPeran = str_replace('_', ' ', $pengguna->getRoleNames()->first() ?? 'tanpa role');
@endphp

<div class="flex h-full flex-col">
    <a href="{{ route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 px-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-300">
        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-500 text-white">
            <x-icon name="map-pin" class="h-5 w-5" />
        </span>
        <span class="leading-tight">
            <span class="block text-sm font-semibold text-white">WebGIS Proyek</span>
            <span class="block text-xs text-indigo-300">Monitoring infrastruktur</span>
        </span>
    </a>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menu utama">
        @foreach ($menu as [$label, $rute, $pola, $peran, $ikon])
            @continue($peran && ! $pengguna->hasRole($peran))
            @php($aktif = request()->routeIs($pola))
            <a href="{{ route($rute) }}"
               @if ($aktif) aria-current="page" @endif
               @class([
                   'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300',
                   'bg-indigo-800 text-white' => $aktif,
                   'text-indigo-200 hover:bg-indigo-900 hover:text-white' => ! $aktif,
               ])>
                <x-icon :name="$ikon" class="h-5 w-5 shrink-0" />
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-indigo-900 p-4">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-800 text-sm font-semibold text-white">
                {{ mb_strtoupper(mb_substr($pengguna->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <div class="truncate text-sm font-medium text-white">{{ $pengguna->name }}</div>
                <div class="truncate text-xs capitalize text-indigo-300">{{ $namaPeran }}</div>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <a href="{{ route('profile.edit') }}" class="flex-1 rounded-md px-3 py-2 text-center text-sm text-indigo-100 hover:bg-indigo-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300">Profil</a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button class="w-full rounded-md px-3 py-2 text-sm text-indigo-100 hover:bg-indigo-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300">Keluar</button>
            </form>
        </div>
    </div>
</div>