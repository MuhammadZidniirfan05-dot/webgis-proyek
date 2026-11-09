<x-guest-layout>
    <h2 class="text-lg font-semibold text-gray-800">Menunggu persetujuan</h2>
    <p id="pesan" class="mt-2 text-sm text-gray-600">
        Permintaan dari <strong>{{ $req->name }}</strong> sudah dikirim ke pemilik aplikasi. Halaman ini memeriksa jawabannya otomatis. Jangan tutup halaman ini.
    </p>

    <form id="form-masuk" method="POST" action="{{ route('admin-access.claim') }}" class="hidden">
        @csrf
    </form>

    <a href="{{ route('login') }}" class="mt-4 inline-block text-sm text-gray-600 underline">Batalkan dan kembali</a>

    <script>
        const pesan = document.getElementById('pesan');
        const timer = setInterval(function () {
            fetch('{{ route('admin-access.status') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(function (d) {
                    if (d.status === 'approved') {
                        clearInterval(timer);
                        pesan.textContent = 'Disetujui. Masuk...';
                        document.getElementById('form-masuk').submit();
                    } else if (['denied', 'expired', 'used', 'none'].includes(d.status)) {
                        clearInterval(timer);
                        pesan.textContent = d.status === 'denied'
                            ? 'Permintaan ditolak.'
                            : 'Permintaan sudah tidak berlaku. Silakan ajukan lagi.';
                    }
                });
        }, 4000);
    </script>
</x-guest-layout>