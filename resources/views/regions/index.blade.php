<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Wilayah</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('regions.store') }}"
              class="bg-white shadow rounded p-4 flex flex-wrap gap-3 items-end text-sm">
            @csrf
            <label class="block">Nama
                <input name="name" value="{{ old('name') }}" required maxlength="100"
                       class="border-gray-300 rounded">
            </label>
            <label class="block">Tingkat
                <select name="level" class="border-gray-300 rounded">
                    @foreach ($levels as $l)
                        <option value="{{ $l }}" @selected(old('level', 'kecamatan') === $l)>{{ $l }}</option>
                    @endforeach
                </select>
            </label>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">+ Tambah wilayah</button>
        </form>

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Tingkat</th>
                        <th class="p-3">Jumlah proyek</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($regions as $r)
                        <tr class="border-t">
                            <td class="p-3">{{ $r->name }}</td>
                            <td class="p-3">{{ $r->level }}</td>
                            <td class="p-3">{{ $r->projects_count }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a href="{{ route('regions.edit', $r) }}" class="text-indigo-600">Edit</a>
                                <form method="POST" action="{{ route('regions.destroy', $r) }}" class="inline"
                                      onsubmit="return confirm('Hapus wilayah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 ml-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="4">Belum ada wilayah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $regions->links() }}</div>
    </div>
</x-app-layout>