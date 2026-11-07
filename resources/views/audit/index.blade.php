<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Log</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="bg-white shadow rounded p-4 flex flex-wrap gap-3 items-end text-sm">
            <label class="block">Aksi
                <select name="action" class="border-gray-300 rounded">
                    <option value="">Semua</option>
                    @foreach (['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus'] as $k => $v)
                        <option value="{{ $k }}" @selected(request('action') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">Data
                <select name="model" class="border-gray-300 rounded">
                    <option value="">Semua</option>
                    @foreach (['Project' => 'Proyek', 'DailyReport' => 'Laporan', 'ReportPhoto' => 'Foto'] as $k => $v)
                        <option value="{{ $k }}" @selected(request('model') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </label>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Filter</button>
            <a href="{{ route('audit.index') }}" class="px-4 py-2 bg-gray-200 rounded">Reset</a>
        </form>

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Pengguna</th>
                        <th class="p-3">Aksi</th>
                        <th class="p-3">Data</th>
                        <th class="p-3">Perubahan</th>
                        <th class="p-3">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t align-top">
                            <td class="p-3 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="p-3">{{ $log->user?->name ?? 'sistem' }}</td>
                            <td class="p-3">
                                <span class="{{ match($log->action) {
                                    'created' => 'text-green-700',
                                    'deleted' => 'text-red-600',
                                    default => 'text-amber-600',
                                } }}">{{ $log->action }}</span>
                            </td>
                            <td class="p-3">
                                {{ $log->model_name }} #{{ $log->auditable_id }}
                                @if ($log->label)<div class="text-gray-500">{{ $log->label }}</div>@endif
                            </td>
                            <td class="p-3">
                                @foreach (($log->new_values ?? $log->old_values ?? []) as $kolom => $nilai)
                                    <div>
                                        <span class="text-gray-500">{{ $kolom }}:</span>
                                        @if ($log->action === 'updated' && isset($log->old_values[$kolom]))
                                            <span class="line-through text-gray-400">{{ is_scalar($log->old_values[$kolom]) ? $log->old_values[$kolom] : json_encode($log->old_values[$kolom]) }}</span>
                                            &rarr;
                                        @endif
                                        {{ is_scalar($nilai) || is_null($nilai) ? $nilai : json_encode($nilai) }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="p-3 whitespace-nowrap">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="6">Belum ada catatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>