<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Proyek</h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css">

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('projects.update', $project) }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <label class="block">Kode
                    <input name="code" value="{{ old('code', $project->code) }}" class="w-full border-gray-300 rounded" required>
                </label>
                <label class="block">Nama
                    <input name="name" value="{{ old('name', $project->name) }}" class="w-full border-gray-300 rounded" required>
                </label>
                <label class="block">Status
                    <select name="status" class="w-full border-gray-300 rounded">
                        @foreach (\App\Models\Project::STATUSES as $s)
                            <option value="{{ $s }}" @selected(old('status', $project->status) === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">Anggaran (Rp)
                    <input type="number" step="0.01" min="0" name="budget" value="{{ old('budget', $project->budget) }}" class="w-full border-gray-300 rounded" required>
                </label>
                <label class="block">Tanggal mulai
                    <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded">
                </label>
                <label class="block">Deadline
                    <input type="date" name="deadline" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded">
                </label>
                <label class="block">Wilayah
                    <select name="region_id" class="w-full border-gray-300 rounded">
                        <option value="">-</option>
                        @foreach ($regions as $r)
                            <option value="{{ $r->id }}" @selected(old('region_id', $project->region_id) == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="block">Deskripsi
                <textarea name="description" rows="3" class="w-full border-gray-300 rounded">{{ old('description', $project->description) }}</textarea>
            </label>

            <div>
                <div class="mb-1">Lokasi (geser titik, atau gambar ulang untuk mengganti)</div>
                <div id="map" style="height: 420px;" class="rounded border"></div>
                <input type="hidden" name="geojson" id="geojson" value="{{ old('geojson', $geojson) }}">
            </div>

            <div class="flex items-center">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan Perubahan</button>
                <a href="{{ route('projects.index') }}" class="ml-4 text-gray-600">Batal</a>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        const map = L.map('map').setView([-6.2088, 106.8456], 11);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const drawn = new L.FeatureGroup().addTo(map);
        const input = document.getElementById('geojson');

        // Tampilkan geometri lama agar bisa diedit
        if (input.value) {
            L.geoJSON(JSON.parse(input.value), {
                pointToLayer: (f, latlng) => L.marker(latlng),
                onEachFeature: (f, layer) => drawn.addLayer(layer)
            });
            if (drawn.getLayers().length) {
                map.fitBounds(drawn.getBounds(), { maxZoom: 16, padding: [30, 30] });
            }
        }

        map.addControl(new L.Control.Draw({
            edit: { featureGroup: drawn },
            draw: { polygon: true, marker: true,
                    polyline: false, rectangle: false, circle: false, circlemarker: false }
        }));

        function simpan() {
            const layers = drawn.getLayers();
            input.value = layers.length ? JSON.stringify(layers[0].toGeoJSON().geometry) : '';
        }

        map.on(L.Draw.Event.CREATED, e => {
            drawn.clearLayers();          // satu geometri per proyek
            drawn.addLayer(e.layer);
            simpan();
        });
        map.on(L.Draw.Event.EDITED, simpan);
        map.on(L.Draw.Event.DELETED, simpan);
    </script>
</x-app-layout>