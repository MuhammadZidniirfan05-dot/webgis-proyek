<x-guest-layout>
    <h2 class="text-lg font-semibold text-gray-800">Minta akses Admin</h2>
    <p class="mt-1 text-sm text-gray-600">
        Akses Admin perlu izin pemilik aplikasi. Permintaanmu dikirim lewat email, dan kamu akan masuk otomatis begitu disetujui.
    </p>

    <form method="POST" action="{{ route('admin-access.store') }}" class="mt-4">
        @csrf
        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" class="block mt-1 w-full" name="name" :value="old('name')" required autofocus maxlength="80" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="reason" value="Alasan (opsional)" />
            <textarea id="reason" name="reason" rows="3" maxlength="300"
                      class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('reason') }}</textarea>
            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
        </div>
        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 underline">Kembali</a>
            <x-primary-button>Kirim permintaan</x-primary-button>
        </div>
    </form>
</x-guest-layout>