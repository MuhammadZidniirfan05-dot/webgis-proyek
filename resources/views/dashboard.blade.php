@php
    $ringkas = function (float $n): string {
        if ($n >= 1_000_000_000) {
            return 'Rp ' . number_format($n / 1_000_000_000, 1, ',', '.') . ' miliar';
        }
        if ($n >= 1_000_000) {
            return 'Rp ' . number_format($n / 1_000_000, 1, ',', '.') . ' juta';
        }
        return 'Rp ' . number_format($n, 0, ',', '.');
    };

    $kartu = [
        ['Total proyek', $total, 'folder', 'bg-indigo-50 text-indigo-600', null],
        ['Total anggaran', $ringkas($totalBudget), 'banknote', 'bg-green-50 text-green-600', 'Rp ' . number_format($totalBudget, 0, ',', '.')],
        ['Rata-rata progres', $avgProgress . '%', 'trending-up', 'bg-blue-50 text-blue-600', null],
        ['Proyek terlambat', $counts['terlambat'], 'alert-triangle', 'bg-red-50 text-red-600', null],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
        <p class="mt-0.5 text-sm text-slate-500">Ringkasan proyek per {{ now()->format('d M Y') }}</p>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-flash />

        {{-- Angka ringkas --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kartu as [$label, $nilai, $ikon, $warna, $keterangan])
                <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg {{ $warna }}">
                        <x-icon :name="$ikon" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <div class="text-sm text-slate-500">{{ $label }}</div>
                        <div class="mt-0.5 text-2xl font-semibold tabular-nums text-slate-900">{{ $nilai }}</div>
                        @if ($keterangan)
                            <div class="mt-0.5 truncate text-xs text-slate-400">{{ $keterangan }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            {{-- Proyek per status --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
                <h2 class="text-sm font-semibold text-slate-900">Proyek per status</h2>

                @if ($total > 0)
                    <div class="mx-auto mt-4 h-48 max-w-[12rem]"><canvas id="chartStatus"></canvas></div>
                    <ul class="mt-5 space-y-2 text-sm">
                        @foreach ($counts as $status => $jumlah)
                            <li class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $colors[$status] }}"></span>
                                    {{ ucfirst($status) }}
                                </span>
                                <span class="font-medium tabular-nums text-slate-900">{{ $jumlah }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-slate-500">Belum ada proyek.</p>
                @endif
            </section>

            {{-- Proyek terlambat --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-3">
                <h2 class="text-sm font-semibold text-slate-900">Proyek terlambat</h2>

                @forelse ($lateProjects as $p)
                    <a href="{{ route('projects.show', $p) }}"
                       class="-mx-2 mt-1 flex items-center justify-between gap-4 rounded-lg px-2 py-3 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 {{ $loop->first ? '' : 'border-t border-slate-100' }}">
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-slate-900">{{ $p->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $p->code }}</span>
                        </span>
                        <span class="shrink-0 text-right">
                            <span class="block text-sm font-medium text-red-600">{{ (int) abs($p->deadline->diffInDays(today())) }} hari lewat</span>
                            <span class="block text-xs text-slate-500">deadline {{ $p->deadline->format('d M Y') }}</span>
                        </span>
                    </a>
                @empty
                    <p class="mt-4 text-sm text-slate-500">Tidak ada proyek yang melewati deadline.</p>
                @endforelse
            </section>
        </div>

        {{-- Laporan terbaru --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-900">Laporan terbaru</h2>

            @forelse ($recentReports as $r)
                <div class="mt-1 flex items-center justify-between gap-4 py-3 {{ $loop->first ? '' : 'border-t border-slate-100' }}">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                            {{ mb_strtoupper(mb_substr($r->user->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <a href="{{ route('projects.show', $r->project) }}" class="block truncate text-sm font-medium text-slate-900 hover:text-indigo-700">
                                {{ $r->project->code }} &middot; {{ $r->project->name }}
                            </a>
                            <span class="block text-xs text-slate-500">{{ $r->user->name }}, {{ $r->report_date->format('d M Y') }}</span>
                        </div>
                    </div>
                    <x-progress-bar :value="$r->progress_percent" class="w-28 shrink-0 sm:w-40" />
                </div>
            @empty
                <p class="mt-4 text-sm text-slate-500">Belum ada laporan harian.</p>
            @endforelse
        </section>
    </div>

    @if ($total > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            const counts = @json($counts);
            const colors = @json($colors);

            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(counts),
                    datasets: [{
                        data: Object.values(counts),
                        backgroundColor: Object.keys(counts).map(k => colors[k]),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: { cutout: '68%', plugins: { legend: { display: false } } }
            });
        </script>
    @endif
</x-app-layout>