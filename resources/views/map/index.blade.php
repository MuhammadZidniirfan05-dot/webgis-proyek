<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-slate-900">Peta proyek</h1>
        <p id="jumlah" class="mt-0.5 text-sm text-slate-500">Memuat data...</p>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Filter --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <label class="block text-sm text-slate-700">Status
                    <select id="f-status" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm text-slate-700">Wilayah
                    <select id="f-region" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($regions as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm text-slate-700">Radius (km)
                    <input id="f-radius" type="number" min="0.1" step="0.1" placeholder="mis. 5"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>

                <div class="text-sm text-slate-700">
                    <div>Pusat radius</div>
                    <div id="pusat" class="mt-2 text-slate-500">Klik peta untuk memilih</div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button id="btn-terapkan" type="button"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">Terapkan</button>
                <button id="btn-lokasi" type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">Lokasi saya</button>
                <button id="btn-reset" type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">Reset</button>
            </div>
        </section>

        {{-- Legenda --}}
        <ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-600" aria-label="Legenda warna status">
            @foreach (['perencanaan' => '#6b7280', 'berjalan' => '#2563eb', 'selesai' => '#16a34a', 'ditunda' => '#f59e0b', 'terlambat' => '#dc2626'] as $nama => $warna)
                <li class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full" style="background: {{ $warna }}"></span>{{ ucfirst($nama) }}
                </li>
            @endforeach
        </ul>

        <div id="map" class="isolate h-[55vh] rounded-xl border border-slate-200 md:h-[65vh]"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-6.2088, 106.8456], 11);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const warna = {
            perencanaan: '#6b7280', berjalan: '#2563eb',
            selesai: '#16a34a', ditunda: '#f59e0b', terlambat: '#dc2626'
        };
        const dataUrl = @json(route('map.data'));
        const detailBase = @json(url('/proyek'));
        const rupiah = new Intl.NumberFormat('id-ID');

        // Saring teks dari data pengguna sebelum masuk ke HTML popup
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
        ));

        let layerProyek = null;
        let pusat = null;
        let markerPusat = null;
        let lingkaran = null;

        function popup(p) {
            return `<div style="min-width:190px">
                <div style="font-weight:600">${esc(p.name)}</div>
                <div style="color:#64748b;font-size:12px">${esc(p.code)}</div>
                <div style="margin-top:6px;font-size:13px;line-height:1.5">
                    Status: <b>${esc(p.status)}</b><br>
                    Progres: ${esc(p.progress)}%<br>
                    Anggaran: Rp ${rupiah.format(p.budget || 0)}<br>
                    Deadline: ${esc(p.deadline ?? '-')}
                </div>
                <a href="${detailBase}/${encodeURIComponent(p.id)}"
                   style="display:inline-block;margin-top:8px;color:#4338ca;font-weight:600">Buka detail</a>
            </div>`;
        }

        function gambarData(data, fit) {
            if (layerProyek) map.removeLayer(layerProyek);

            layerProyek = L.geoJSON(data, {
                style: f => ({ color: warna[f.properties.status] ?? '#2563eb', weight: 3 }),
                pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                    radius: 9, color: '#fff', weight: 2, fillOpacity: 0.95,
                    fillColor: warna[f.properties.status] ?? '#2563eb'
                }),
                onEachFeature: (f, l) => l.bindPopup(popup(f.properties))
            }).addTo(map);

            document.getElementById('jumlah').textContent = data.features.length + ' proyek ditampilkan';

            if (fit && data.features.length) {
                map.fitBounds(layerProyek.getBounds(), { padding: [30, 30], maxZoom: 15 });
            }
        }

        function gambarLingkaran(radiusKm) {
            if (lingkaran) { map.removeLayer(lingkaran); lingkaran = null; }
            if (pusat && radiusKm) {
                lingkaran = L.circle([pusat.lat, pusat.lng], {
                    radius: radiusKm * 1000, color: '#4338ca', weight: 1, fillOpacity: 0.06
                }).addTo(map);
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

            fetch(dataUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => { gambarData(data, fit); gambarLingkaran(radius); });
        }

        function setPusat(latlng) {
            pusat = { lat: latlng.lat, lng: latlng.lng };
            if (markerPusat) map.removeLayer(markerPusat);
            markerPusat = L.marker(latlng).addTo(map);
            document.getElementById('pusat').textContent = pusat.lat.toFixed(5) + ', ' + pusat.lng.toFixed(5);
        }

        map.on('click', e => setPusat(e.latlng));

        document.getElementById('btn-terapkan').addEventListener('click', () => muat(true));

        document.getElementById('btn-lokasi').addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('Peramban ini tidak mendukung pencarian lokasi.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const titik = L.latLng(pos.coords.latitude, pos.coords.longitude);
                    setPusat(titik);
                    map.setView(titik, 13);
                },
                () => alert('Lokasi tidak bisa diambil. Izinkan akses lokasi di peramban lalu coba lagi.')
            );
        });

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