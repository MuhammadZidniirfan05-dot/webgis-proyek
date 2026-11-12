<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Proyek</h1>
                <p class="mt-0.5 text-sm text-slate-500">{{ $projects->total() }} proyek terdaftar</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('projects.export.excel') }}"
                   class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">Export Excel</a>
                <a href="{{ route('projects.export.pdf') }}"
                   class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">Export PDF</a>
                @role('admin|manajer_proyek')
                    <a href="{{ route('projects.create') }}"
                       class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">Proyek baru</a>
                @endrole
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        <x-flash />

        @if ($projects->count())
            {{-- Tabel: layar menengah ke atas --}}
            <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Proyek</th>
                            <th class="px-4 py-3 font-medium">Wilayah</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Deadline</th>
                            <th class="px-4 py-3 font-medium">Progres</th>
                            <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($projects as $p)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3">
                                    <a href="{{ route('projects.show', $p) }}" class="font-medium text-slate-900 hover:text-indigo-700">{{ $p->name }}</a>
                                    <div class="text-xs text-slate-500">{{ $p->code }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $p->region?->name ?? '-' }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$p->display_status" /></td>
                                <td class="whitespace-nowrap px-4 py-3 {{ $p->is_late ? 'font-medium text-red-600' : 'text-slate-600' }}">
                                    {{ $p->deadline?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-3"><x-progress-bar :value="$p->progress_percent" :late="$p->is_late" class="w-36" /></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    @can('update', $p)
                                        <a href="{{ route('projects.edit', $p) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                    @endcan
                                    @can('delete', $p)
                                        <form method="POST" action="{{ route('projects.destroy', $p) }}" class="ml-3 inline"
                                              onsubmit="return confirm('Hapus proyek ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu: ponsel --}}
            <div class="space-y-3 md:hidden">
                @foreach ($projects as $p)
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('projects.show', $p) }}" class="block font-medium text-slate-900">{{ $p->name }}</a>
                                <div class="text-xs text-slate-500">{{ $p->code }} &middot; {{ $p->region?->name ?? 'tanpa wilayah' }}</div>
                            </div>
                            <x-status-badge :status="$p->display_status" class="shrink-0" />
                        </div>
                        <x-progress-bar :value="$p->progress_percent" :late="$p->is_late" class="mt-3" />
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="{{ $p->is_late ? 'font-medium text-red-600' : 'text-slate-500' }}">
                                Deadline {{ $p->deadline?->format('d M Y') ?? '-' }}
                            </span>
                            <span>
                                @can('update', $p)
                                    <a href="{{ route('projects.edit', $p) }}" class="font-medium text-indigo-600">Edit</a>
                                @endcan
                                @can('delete', $p)
                                    <form method="POST" action="{{ route('projects.destroy', $p) }}" class="ml-3 inline"
                                          onsubmit="return confirm('Hapus proyek ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-600">Hapus</button>
                                    </form>
                                @endcan
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $projects->links() }}</div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <p class="font-medium text-slate-900">Belum ada proyek</p>
                @role('admin|manajer_proyek')
                    <p class="mt-1 text-sm text-slate-500">Tambahkan proyek pertama dan tandai lokasinya di peta.</p>
                    <a href="{{ route('projects.create') }}"
                       class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Proyek baru</a>
                @else
                    <p class="mt-1 text-sm text-slate-500">Proyek akan muncul di sini setelah ditambahkan oleh manajer.</p>
                @endrole
            </div>
        @endif
    </div>
</x-app-layout>