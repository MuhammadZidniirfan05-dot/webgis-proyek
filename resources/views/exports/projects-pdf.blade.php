<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Proyek</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .late { color: #dc2626; font-weight: bold; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Proyek Infrastruktur</h1>
    <div class="meta">
        Dicetak: {{ $tanggal->format('d M Y H:i') }}
        @if ($status) | Filter status: {{ $status }} @endif
        | Jumlah: {{ $projects->count() }} proyek
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Wilayah</th>
                <th>Manajer</th>
                <th class="right">Anggaran (Rp)</th>
                <th>Deadline</th>
                <th class="right">Progres</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $p)
                <tr>
                    <td>{{ $p->code }}</td>
                    <td>{{ $p->name }}</td>
                    <td class="{{ $p->is_late ? 'late' : '' }}">{{ $p->display_status }}</td>
                    <td>{{ $p->region?->name ?? '-' }}</td>
                    <td>{{ $p->manager?->name ?? '-' }}</td>
                    <td class="right">{{ number_format($p->budget, 0, ',', '.') }}</td>
                    <td>{{ $p->deadline?->format('d M Y') ?? '-' }}</td>
                    <td class="right">{{ $p->progress_percent }}%</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="right">Total anggaran</th>
                <th class="right">{{ number_format($projects->sum('budget'), 0, ',', '.') }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>