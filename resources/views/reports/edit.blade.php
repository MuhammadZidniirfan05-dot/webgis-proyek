<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Laporan - {{ $project->code }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('reports.update', [$project, $report]) }}"
              enctype="multipart/form-data" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <label class="block text-sm">Tanggal
                    <input type="date" name="report_date"
                           value="{{ old('report_date', $report->report_date->format('Y-m-d')) }}"
                           max="{{ today()->format('Y-m-d') }}" class="w-full border-gray-300 rounded" required>
                </label>
                <label class="block text-sm">Progres (%)
                    <input type="number" name="progress_percent" min="0" max="100"
                           value="{{ old('progress_percent', $report->progress_percent) }}"
                           class="w-full border-gray-300 rounded" required>
                </label>
            </div>

            <label class="block text-sm">Catatan
                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded">{{ old('notes', $report->notes) }}</textarea>
            </label>

            @if ($report->photos->count())
                <div class="text-sm">
                    <div class="mb-1">Foto saat ini (centang untuk menghapus)</div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($report->photos as $ph)
                            <label class="block">
                                <img src="{{ asset('storage/' . $ph->path) }}"
                                     class="h-24 w-24 object-cover rounded border">
                                <input type="checkbox" name="remove_photos[]" value="{{ $ph->id }}"> Hapus
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <label class="block text-sm">Tambah foto (total maksimal 5)
                <input type="file" name="photos[]" accept="image/*" multiple class="block w-full mt-1">
            </label>

            <div class="flex items-center">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan Perubahan</button>
                <a href="{{ route('projects.show', $project) }}" class="ml-4 text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>