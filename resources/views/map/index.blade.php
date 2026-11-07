<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peta Proyek</h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-4 mb-4 grid md:grid-cols-5 gap-3 items-end">
            <label class="block text-sm">Status
                <select id="f-status" class="w-full border-gray-300 rounded">
                    <option value="">Semua</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm">Wilayah
                <select id="f-region" class="w-full border-gray-300 rounded">
                    <option value="">Semua</option>
                    @foreach ($regions as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm">Radius (km)
                <input id="f-radius" type="number" min="0.1" step="0.1" placeholder="mis. 5"
                       class="w-full border-gray-300 rounded">
            </label>

            <div class="text-sm">
                <div class="mb-1">Pusat radius</div>
                <div id="pusat" class="text-gray-500">Klik peta untuk memilih</div>
            </div>

            <div class="flex gap-2">
                <button id="btn-terapkan" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded">Terapkan</button>
                <button id="btn-reset" type="button" class="px-4 py-2 bg-gray-200 rounded">Reset</button>
            </div>
        </div>

        <div id="jumlah" class="text-sm text-gray-600 mb-2"></div>
        <div id="map" style="height: 65vh;" class="rounded-lg shadow"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-6.2088, 106.8456], 11);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const warna = {
            perencanaan: '#6b7280', berjalan: '#2563eb',
            selesai: '#16a34a', ditunda: '#f59e0b', terlambat: '#dc2626'
        };

        let layerProyek = null;   // hasil filter
        let pusat = null;         // {lat, lng}
        let markerPusat = null;
        let lingkaran = null;

        function gambarData(data, fit) {
            if (layerProyek) map.removeLayer(layerProyek);

            layerProyek = L.geoJSON(data, {
                style: f => ({ color: warna[f.properties.status] ?? '#2563eb', weight: 3 }),
                pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                    radius: 9, color: '#fff', weight: 2, fillOpacity: 0.9,
                    fillColor: warna[f.properties.status] ?? '#2563eb'
                }),
                onEachFeature: (f, l) => {
                    const p = f.properties;
                    l.bindPopup(
                        `<strong>${p.code}</strong><br>${p.name}<br>` +
                        `Status: <b>${p.status}</b><br>` +
                        `Progres: ${p.progress}%<br>Deadline: ${p.deadline ?? '-'}`
                    );
                }
            }).addTo(map);

            document.getElementById('jumlah').textContent =
                data.features.length + ' proyek ditampilkan';

            if (fit && data.features.length) {
                map.fitBounds(layerProyek.getBounds(), { padding: [30, 30], maxZoom: 15 });
            }
        }

        function muat(fit = false) {
            const params = new URLSearchParams();
            const status = document.getElementById('f-status').value;
            const region = document.getElementById('f-region').value;
            const radius = document.getElementById('f-radius').value;

            if (status) params.set('status', status);
            if (region) params.set('region_id', region);
            if (pusat && radius) {
                params.set('lat', pusat.lat);
                params.set('lng', pusat.lng);
                params.set('radius', radius);
            }

            fetch('{{ route('map.data') }}?' + params.toString(), {
                headers: { 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    gambarData(data, fit);
                    gambarLingkaran(radius);
                });
        }

        function gambarLingkaran(radiusKm) {
            if (lingkaran) { map.removeLayer(lingkaran); lingkaran = null; }
            if (pusat && radiusKm) {
                lingkaran = L.circle([pusat.lat, pusat.lng], {
                    radius: radiusKm * 1000, color: '#7c3aed', weight: 1, fillOpacity: 0.05
                }).addTo(map);
            }
        }

        map.on('click', e => {
            pusat = { lat: e.latlng.lat, lng: e.latlng.lng };
            if (markerPusat) map.removeLayer(markerPusat);
            markerPusat = L.marker(e.latlng).addTo(map);
            document.getElementById('pusat').textContent =
                pusat.lat.toFixed(5) + ', ' + pusat.lng.toFixed(5);
        });

        document.getElementById('btn-terapkan').addEventListener('click', () => muat(true));

        document.getElementById('btn-reset').addEventListener('click', () => {
            document.getElementById('f-status').value = '';
            document.getElementById('f-region').value = '';
            document.getElementById('f-radius').value = '';
            pusat = null;
            if (markerPusat) { map.removeLayer(markerPusat); markerPusat = null; }
            document.getElementById('pusat').textContent = 'Klik peta untuk memilih';
            muat(true);
        });

        muat(true);
    </script>
</x-app-layout>