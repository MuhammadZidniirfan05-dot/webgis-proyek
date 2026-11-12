@php
    $bisaLapor = auth()->user()->can('report', $project);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('projects.index') }}" class="text-sm text-slate-500 hover:text-indigo-700">Kembali ke daftar proyek</a>
                <h1 class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xl font-semibold text-slate-900">
                    <span>{{ $project->name }}</span>
                    <x-status-badge :status="$project->display_status" />
                </h1>
                <p class="text-sm text-slate-500">{{ $project->code }}</p>
            </div>
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}"
                   class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                    Edit proyek dan petugas
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-flash />

        {{-- Ringkasan dan peta --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-900">Ringkasan</h2>
                <x-progress-bar :value="$project->progress_percent" :late="$project->is_late" class="mt-3" />
                @if ($project->is_late)
                    <p class="mt-2 text-xs font-medium text-red-600">
                        Melewati deadline {{ (int) abs($project->deadline->diffInDays(today())) }} hari
                    </p>
                @endif

                <dl class="mt-4 divide-y divide-slate-100 text-sm">
                    @foreach ([
                        ['Anggaran', 'Rp ' . number_format($project->budget, 0, ',', '.')],
                        ['Mulai', $project->start_date?->format('d M Y') ?? '-'],
                        ['Deadline', $project->deadline?->format('d M Y') ?? '-'],
                        ['Wilayah', $project->region?->name ?? '-'],
                        ['Manajer', $project->manager?->name ?? '-'],
                        ['Petugas', $project->officers->pluck('name')->implode(', ') ?: '-'],
                    ] as [$label, $nilai])
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-900">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($project->description)
                    <p class="mt-3 border-t border-slate-100 pt-3 text-sm leading-relaxed text-slate-600">{{ $project->description }}</p>
                @endif
            </section>

            <div class="lg:col-span-2">
                <div id="map" class="isolate h-72 rounded-xl border border-slate-200 sm:h-96 lg:h-full lg:min-h-[24rem]"></div>
            </div>
        </div>

        {{-- Kurva S --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-900">Kurva S: rencana dan aktual</h2>
            @if ($sCurve)
                <div class="mt-4 h-64 sm:h-80"><canvas id="chartKurvaS"></canvas></div>
                <p class="mt-2 text-xs text-slate-500">Rencana dihitung linear dari tanggal mulai sampai deadline.</p>
            @else
                <p class="mt-3 text-sm text-slate-500">Isi tanggal mulai dan deadline proyek untuk menampilkan Kurva S.</p>
            @endif
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Form laporan --}}
            @if ($bisaLapor)
                <form method="POST" action="{{ route('reports.store', $project) }}" enctype="multipart/form-data"
                      class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
                    @csrf
                    <h2 class="text-sm font-semibold text-slate-900">Laporan harian baru</h2>

                    <label class="block text-sm text-slate-700">Tanggal
                        <input type="date" name="report_date" value="{{ old('report_date', today()->format('Y-m-d')) }}"
                               max="{{ today()->format('Y-m-d') }}" required
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="block text-sm text-slate-700">Progres (%)
                        <input type="number" name="progress_percent" min="0" max="100" required
                               value="{{ old('progress_percent', $project->progress_percent) }}"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="block text-sm text-slate-700">Catatan
                        <textarea name="notes" rows="3"
                                  class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </label>
                    <label class="block text-sm text-slate-700">Foto
                        <span class="text-xs text-slate-400">(maksimal 5, masing-masing 4 MB)</span>
                        <input type="file" name="photos[]" accept="image/*" multiple
                               class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                    </label>

                    <x-primary-button class="w-full">Kirim laporan</x-primary-button>
                </form>
            @endif

            {{-- Timeline --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 {{ $bisaLapor ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                <h2 class="text-sm font-semibold text-slate-900">Riwayat laporan</h2>

                @forelse ($reports as $r)
                    @php
                        $sebelumnya = $reports->get($loop->index + 1)?->progress_percent ?? 0;
                        $selisih = $r->progress_percent - $sebelumnya;
                    @endphp
                    <div class="relative mt-5 border-l-2 border-indigo-100 pb-2 pl-6">
                        <span class="absolute -left-[7px] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-indigo-600"></span>

                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="text-sm">
                                <span class="font-semibold text-slate-900">{{ $r->report_date->format('d M Y') }}</span>
                                <span class="text-slate-500">oleh {{ $r->user->name }}</span>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="font-semibold tabular-nums text-slate-900">{{ $r->progress_percent }}%</span>
                                    <span class="text-xs font-medium {{ $selisih >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $selisih >= 0 ? '+' : '' }}{{ $selisih }} dari laporan sebelumnya
                                    </span>
                                </div>
                            </div>

                            @if (auth()->user()->hasRole('admin') || $r->user_id === auth()->id())
                                <div class="flex items-center gap-3 text-sm">
                                    <a href="{{ route('reports.edit', [$project, $r]) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                    <form method="POST" action="{{ route('reports.destroy', [$project, $r]) }}"
                                          onsubmit="return confirm('Hapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        @if ($r->notes)
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $r->notes }}</p>
                        @endif

                        @if ($r->photos->count())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($r->photos as $ph)
                                    <a href="{{ asset('storage/' . $ph->path) }}" target="_blank" rel="noopener"
                                       class="block overflow-hidden rounded-lg border border-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                                        <img src="{{ asset('storage/' . $ph->path) }}" alt="{{ $ph->caption ?? 'Foto laporan ' . $r->report_date->format('d M Y') }}"
                                             class="h-20 w-20 object-cover" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-500">
                        Belum ada laporan.
                        @if ($bisaLapor) Kirim laporan harian pertama lewat form di samping. @endif
                    </p>
                @endforelse
            </section>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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
                style: { color: '#4338ca', weight: 3 },
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
                        { label: 'Rencana', data: kurva.planned, borderColor: '#94a3b8',
                          borderDash: [6, 4], pointRadius: 0, tension: 0.2 },
                        { label: 'Aktual', data: kurva.actual, borderColor: '#4338ca',
                          backgroundColor: '#4338ca', pointRadius: 2, tension: 0.2 }
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