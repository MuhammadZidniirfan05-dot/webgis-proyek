<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Wilayah</h2>
    </x-slot>

    <div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('regions.update', $region) }}"
              class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')

            <label class="block text-sm">Nama
                <input name="name" value="{{ old('name', $region->name) }}" required maxlength="100"
                       class="w-full border-gray-300 rounded">
            </label>
            <label class="block text-sm">Tingkat
                <select name="level" class="w-full border-gray-300 rounded">
                    @foreach ($levels as $l)
                        <option value="{{ $l }}" @selected(old('level', $region->level) === $l)>{{ $l }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex items-center">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                <a href="{{ route('regions.index') }}" class="ml-4 text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>