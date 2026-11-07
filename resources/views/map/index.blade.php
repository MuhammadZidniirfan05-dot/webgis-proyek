<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peta Proyek</h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div id="map" style="height: 70vh;" class="rounded-lg shadow"></div>
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

        fetch('{{ route('map.data') }}')
            .then(r => r.json())
            .then(data => {
                const layer = L.geoJSON(data, {
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
                if (data.features.length) map.fitBounds(layer.getBounds(), { padding: [30, 30] });
            });
    </script>
</x-app-layout>