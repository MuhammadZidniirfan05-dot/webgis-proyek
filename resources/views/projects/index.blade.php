<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Proyek</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        @role('admin|manajer_proyek')
            <a href="{{ route('projects.create') }}"
               class="inline-block mb-4 px-4 py-2 bg-indigo-600 text-white rounded">+ Proyek Baru</a>
        @endrole

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Kode</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Deadline</th>
                        <th class="p-3">Progres</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $p)
                        <tr class="border-t">
                            <td class="p-3">{{ $p->code }}</td>
                            <td class="p-3">{{ $p->name }}</td>
                            <td class="p-3">
                                <span class="{{ $p->is_late ? 'text-red-600 font-semibold' : '' }}">{{ $p->display_status }}</span>
                            </td>
                            <td class="p-3">{{ $p->deadline?->format('d M Y') ?? '-' }}</td>
                            <td class="p-3">{{ $p->progress_percent }}%</td>
                            <td class="p-3 whitespace-nowrap">
                                @can('update', $p)
                                    <a href="{{ route('projects.edit', $p) }}" class="text-indigo-600">Edit</a>
                                @endcan
                                @can('delete', $p)
                                    <form method="POST" action="{{ route('projects.destroy', $p) }}" class="inline"
                                          onsubmit="return confirm('Hapus proyek ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 ml-2">Hapus</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="6">Belum ada proyek.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $projects->links() }}</div>
    </div>
</x-app-layout>