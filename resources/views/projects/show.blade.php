<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->code }} - {{ $project->name }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded p-6 space-y-2 text-sm">
                <div><span class="text-gray-500">Status:</span>
                    <span class="{{ $project->is_late ? 'text-red-600 font-semibold' : '' }}">{{ $project->display_status }}</span>
                </div>
                <div><span class="text-gray-500">Anggaran:</span> Rp {{ number_format($project->budget, 0, ',', '.') }}</div>
                <div><span class="text-gray-500">Mulai:</span> {{ $project->start_date?->format('d M Y') ?? '-' }}</div>
                <div><span class="text-gray-500">Deadline:</span> {{ $project->deadline?->format('d M Y') ?? '-' }}</div>
                <div><span class="text-gray-500">Wilayah:</span> {{ $project->region?->name ?? '-' }}</div>
                <div><span class="text-gray-500">Manajer:</span> {{ $project->manager?->name ?? '-' }}</div>
                <div><span class="text-gray-500">Petugas:</span>
                    {{ $project->officers->pluck('name')->implode(', ') ?: '-' }}
                </div>
                <div>
                    <span class="text-gray-500">Progres:</span> {{ $project->progress_percent }}%
                    <div class="w-full bg-gray-200 rounded h-2 mt-1">
                        <div class="bg-indigo-600 h-2 rounded" style="width: {{ $project->progress_percent }}%"></div>
                    </div>
                </div>
                @if ($project->description)
                    <p class="pt-2 text-gray-700">{{ $project->description }}</p>
                @endif
            </div>

            <div id="map" style="height: 320px;" class="rounded shadow"></div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h3 class="font-semibold mb-4">Kurva S (rencana vs aktual)</h3>
            @if ($sCurve)
                <div style="height: 300px;"><canvas id="chartKurvaS"></canvas></div>
                <p class="text-xs text-gray-500 mt-2">Rencana dihitung linear dari tanggal mulai sampai deadline.</p>
            @else
                <p class="text-sm text-gray-500">Kurva S membutuhkan tanggal mulai dan deadline yang valid.</p>
            @endif
        </div>

        @can('report', $project)
            <form method="POST" action="{{ route('reports.store', $project) }}" enctype="multipart/form-data"
                  class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                <h3 class="font-semibold">Laporan Harian Baru</h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <label class="block text-sm">Tanggal
                        <input type="date" name="report_date" value="{{ old('report_date', today()->format('Y-m-d')) }}"
                               max="{{ today()->format('Y-m-d') }}" class="w-full border-gray-300 rounded" required>
                    </label>
                    <label class="block text-sm">Progres (%)
                        <input type="number" name="progress_percent" min="0" max="100"
                               value="{{ old('progress_percent', $project->progress_percent) }}"
                               class="w-full border-gray-300 rounded" required>
                    </label>
                </div>

                <label class="block text-sm">Catatan
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded">{{ old('notes') }}</textarea>
                </label>

                <label class="block text-sm">Foto (maksimal 5, masing-masing 4 MB)
                    <input type="file" name="photos[]" accept="image/*" multiple class="block w-full mt-1">
                </label>

                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Kirim Laporan</button>
            </form>
        @endcan

        <div class="bg-white shadow rounded p-6">
            <h3 class="font-semibold mb-4">Timeline Laporan</h3>

            @forelse ($reports as $r)
                @php
                    $sebelumnya = $reports->get($loop->index + 1)?->progress_percent ?? 0;
                    $selisih = $r->progress_percent - $sebelumnya;
                @endphp
                <div class="relative pl-6 pb-6 border-l-2 border-indigo-200 ml-2 last:pb-0">
                    <span class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-indigo-600 border-2 border-white"></span>

                    <div class="flex justify-between text-sm">
                        <div>
                            <strong>{{ $r->report_date->format('d M Y') }}</strong>
                            oleh {{ $r->user->name }}
                            - progres <strong>{{ $r->progress_percent }}%</strong>
                            <span class="{{ $selisih >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ({{ $selisih >= 0 ? '+' : '' }}{{ $selisih }})
                            </span>
                        </div>
                        @if (auth()->user()->hasRole('admin') || $r->user_id === auth()->id())
                            <div class="flex items-center gap-3">
                                <a href="{{ route('reports.edit', [$project, $r]) }}" class="text-indigo-600">Edit</a>
                                <form method="POST" action="{{ route('reports.destroy', [$project, $r]) }}"
                                      onsubmit="return confirm('Hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600">Hapus</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if ($r->notes)
                        <p class="text-sm text-gray-700 mt-1">{{ $r->notes }}</p>
                    @endif

                    @if ($r->photos->count())
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach ($r->photos as $ph)
                                <a href="{{ asset('storage/' . $ph->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $ph->path) }}" alt="{{ $ph->caption }}"
                                         class="h-24 w-24 object-cover rounded border">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada laporan.</p>
            @endforelse
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const map = L.map('map').setView([-6.2088, 106.8456], 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const geojson = @json($geojson);
        if (geojson) {
            const layer = L.geoJSON(JSON.parse(geojson), {
                pointToLayer: (f, latlng) => L.marker(latlng)
            }).addTo(map);
            map.fitBounds(layer.getBounds(), { maxZoom: 16, padding: [30, 30] });
        }

        const kurva = @json($sCurve);
        if (kurva) {
            new Chart(document.getElementById('chartKurvaS'), {
                type: 'line',
                data: {
                    labels: kurva.labels,
                    datasets: [
                        { label: 'Rencana', data: kurva.planned, borderColor: '#6b7280',
                          borderDash: [6, 4], pointRadius: 0, tension: 0.2 },
                        { label: 'Aktual', data: kurva.actual, borderColor: '#2563eb',
                          backgroundColor: '#2563eb', pointRadius: 2, tension: 0.2 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: { y: { min: 0, max: 100, ticks: { callback: v => v + '%' } } }
                }
            });
        }
    </script>
</x-app-layout>