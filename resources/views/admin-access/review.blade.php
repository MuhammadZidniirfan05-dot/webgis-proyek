<x-guest-layout>
    <h2 class="text-lg font-semibold text-gray-800">Tinjau permintaan akses Admin</h2>

    <dl class="mt-3 text-sm space-y-1">
        <div><dt class="inline text-gray-500">Nama:</dt> <dd class="inline">{{ $req->name }}</dd></div>
        <div><dt class="inline text-gray-500">Alasan:</dt> <dd class="inline">{{ $req->reason ?: '-' }}</dd></div>
        <div><dt class="inline text-gray-500">IP:</dt> <dd class="inline">{{ $req->ip_address }}</dd></div>
        <div><dt class="inline text-gray-500">Waktu:</dt> <dd class="inline">{{ $req->created_at->format('d M Y H:i') }}</dd></div>
        <div><dt class="inline text-gray-500">Status:</dt> <dd class="inline">{{ $req->effectiveStatus() }}</dd></div>
    </dl>

    @isset($selesai)
        <div class="mt-4 p-3 bg-green-100 text-green-800 rounded text-sm">{{ $selesai }}</div>
    @elseif ($req->effectiveStatus() === 'pending')
        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-4 flex gap-3">
            @csrf
            <button name="decision" value="approve" class="px-4 py-2 bg-green-600 text-white rounded">Setujui</button>
            <button name="decision" value="deny" class="px-4 py-2 bg-red-600 text-white rounded">Tolak</button>
        </form>
    @else
        <p class="mt-4 text-sm text-gray-600">Permintaan ini sudah diproses atau kedaluwarsa.</p>
    @endisset
</x-guest-layout>