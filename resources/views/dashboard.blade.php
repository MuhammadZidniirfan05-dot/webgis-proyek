<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white shadow rounded p-4">
                <div class="text-sm text-gray-500">Total proyek</div>
                <div class="text-2xl font-semibold">{{ $total }}</div>
            </div>
            <div class="bg-white shadow rounded p-4">
                <div class="text-sm text-gray-500">Total anggaran</div>
                <div class="text-2xl font-semibold">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white shadow rounded p-4">
                <div class="text-sm text-gray-500">Rata-rata progres</div>
                <div class="text-2xl font-semibold">{{ $avgProgress }}%</div>
            </div>
            <div class="bg-white shadow rounded p-4">
                <div class="text-sm text-gray-500">Proyek terlambat</div>
                <div class="text-2xl font-semibold text-red-600">{{ $counts['terlambat'] }}</div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded p-6">
                <h3 class="font-semibold mb-4">Proyek per status</h3>
                <div style="height: 260px;"><canvas id="chartStatus"></canvas></div>
            </div>

            <div class="bg-white shadow rounded p-6">
                <h3 class="font-semibold mb-4">Proyek terlambat</h3>
                @forelse ($lateProjects as $p)
                    <div class="flex justify-between border-t py-2 text-sm">
                        <a href="{{ route('projects.show', $p) }}" class="text-indigo-600">{{ $p->code }} - {{ $p->name }}</a>
                        <span class="text-red-600">{{ $p->deadline->format('d M Y') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada proyek terlambat.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h3 class="font-semibold mb-4">Laporan terbaru</h3>
            @forelse ($recentReports as $r)
                <div class="flex justify-between border-t py-2 text-sm">
                    <div>
                        <strong>{{ $r->report_date->format('d M Y') }}</strong>
                        <a href="{{ route('projects.show', $r->project) }}" class="text-indigo-600">{{ $r->project->code }}</a>
                        oleh {{ $r->user->name }}
                    </div>
                    <div>{{ $r->progress_percent }}%</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada laporan.</p>
            @endforelse
        </div>
    </div>

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
                    backgroundColor: Object.keys(counts).map(k => colors[k])
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    </script>
</x-app-layout>