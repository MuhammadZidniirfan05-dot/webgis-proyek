<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengguna</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
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

        <div class="flex flex-wrap gap-3 items-end justify-between">
            <form method="GET" class="flex flex-wrap gap-3 items-end text-sm">
                <label class="block">Cari
                    <input name="q" value="{{ request('q') }}" placeholder="nama atau email"
                           class="border-gray-300 rounded">
                </label>
                <label class="block">Role
                    <select name="role" class="border-gray-300 rounded">
                        <option value="">Semua</option>
                        @foreach ($roles as $kode => $nama)
                            <option value="{{ $kode }}" @selected(request('role') === $kode)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Filter</button>
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 rounded">Reset</a>
            </form>

            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">+ Pengguna Baru</a>
        </div>

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Role</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr class="border-t">
                            <td class="p-3">
                                {{ $u->name }}
                                @if ($u->is(auth()->user()))
                                    <span class="text-xs text-indigo-600">(kamu)</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $u->email }}</td>
                            <td class="p-3">{{ $roles[$u->roles->first()?->name] ?? 'Tanpa role' }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a href="{{ route('users.edit', $u) }}" class="text-indigo-600">Edit</a>
                                @unless ($u->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline"
                                          onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 ml-2">Hapus</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="4">Tidak ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>