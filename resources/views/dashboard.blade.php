<x-app-layout>
    {{-- ====== Gaya khas Gentelella (dimuat lokal, tanpa CDN) ====== --}}
    <style>
        .gtl-wrap {
            --gtl-bg: #f4f6f9;
            --gtl-card: #ffffff;
            --gtl-border: #e6e9ef;
            --gtl-text: #2a3f54;
            --gtl-muted: #73879c;
            --gtl-primary: #1abb9c;
            --gtl-blue: #1a7bb9;
            --gtl-red: #e74c3c;
            --gtl-yellow: #f39c12;
            background: var(--gtl-bg);
            min-height: 100vh;
            padding: 1.5rem 1.25rem 2.5rem;
            color: var(--gtl-text);
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
        .gtl-header {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: .75rem; margin-bottom: 1.5rem;
        }
        .gtl-title { font-size: 1.5rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
        .gtl-sub { font-size: .8rem; color: var(--gtl-muted); margin-top: .15rem; }
        .gtl-badge {
            font-size: .75rem; font-weight: 600; padding: .4rem .8rem;
            background: #fff; border: 1px solid var(--gtl-border);
            border-radius: 999px; color: var(--gtl-muted);
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }

        /* Stat cards */
        .gtl-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        @media (max-width: 1024px) { .gtl-stats { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px)  { .gtl-stats { grid-template-columns: 1fr; } }

        .gtl-stat {
            background: var(--gtl-card); border: 1px solid var(--gtl-border);
            border-radius: 10px; padding: 1.1rem 1.2rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 3px rgba(42,63,84,.04);
            transition: transform .2s ease, box-shadow .2s ease;
            position: relative; overflow: hidden;
        }
        .gtl-stat::before {
            content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--gtl-primary);
        }
        .gtl-stat.is-blue::before  { background: var(--gtl-blue); }
        .gtl-stat.is-yellow::before{ background: var(--gtl-yellow); }
        .gtl-stat.is-red::before   { background: var(--gtl-red); }
        .gtl-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(42,63,84,.08); }

        .gtl-stat .label { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: var(--gtl-muted); font-weight: 600; }
        .gtl-stat .value { font-size: 1.6rem; font-weight: 700; margin-top: .25rem; line-height: 1.1; }
        .gtl-stat .value.small { font-size: 1.15rem; }
        .gtl-stat .value.is-red { color: var(--gtl-red); }
        .gtl-stat .icon {
            width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(26,187,156,.12); color: var(--gtl-primary);
        }
        .gtl-stat.is-blue   .icon { background: rgba(26,123,185,.12); color: var(--gtl-blue); }
        .gtl-stat.is-yellow .icon { background: rgba(243,156,18,.12); color: var(--gtl-yellow); }
        .gtl-stat.is-red    .icon { background: rgba(231,76,60,.12);  color: var(--gtl-red); }
        .gtl-stat .icon svg { width: 22px; height: 22px; }

        /* Grid konten */
        .gtl-grid { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; margin-bottom: 1.5rem; }
        @media (max-width: 1024px) { .gtl-grid { grid-template-columns: 1fr; } }

        .gtl-card {
            background: var(--gtl-card); border: 1px solid var(--gtl-border);
            border-radius: 10px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(42,63,84,.04);
            display: flex; flex-direction: column;
        }
        .gtl-card-head {
            padding: .9rem 1.2rem; border-bottom: 1px solid var(--gtl-border);
            display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        }
        .gtl-card-head h3 { font-size: .95rem; font-weight: 700; margin: 0; }
        .gtl-card-head .tag {
            font-size: .7rem; font-weight: 600; padding: .25rem .6rem; border-radius: 999px;
            background: #eef4f8; color: #5a7b99;
        }
        .gtl-card-head .tag.red { background: #fdecea; color: var(--gtl-red); }
        .gtl-card-body { padding: 1.1rem 1.2rem; }

        /* List item */
        .gtl-list { display: flex; flex-direction: column; gap: .4rem; max-height: 300px; overflow-y: auto; }
        .gtl-list::-webkit-scrollbar { width: 6px; }
        .gtl-list::-webkit-scrollbar-thumb { background: #d6dde6; border-radius: 999px; }
        .gtl-item {
            display: flex; align-items: center; justify-content: space-between; gap: .75rem;
            padding: .65rem .75rem; border-radius: 8px; border: 1px solid transparent;
            text-decoration: none; color: inherit; transition: background .15s, border-color .15s;
        }
        .gtl-item:hover { background: #f7fafd; border-color: var(--gtl-border); }
        .gtl-item .meta { min-width: 0; }
        .gtl-item .code { font-size: .85rem; font-weight: 600; color: var(--gtl-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gtl-item .name { font-size: .75rem; color: var(--gtl-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gtl-item .date {
            flex-shrink: 0; font-size: .72rem; font-weight: 600;
            color: var(--gtl-red); background: #fdecea;
            padding: .25rem .55rem; border-radius: 6px;
        }
        .gtl-empty { padding: 2rem 1rem; text-align: center; color: var(--gtl-muted); font-size: .85rem; }
        .gtl-empty .dot { width: 40px; height: 40px; border-radius: 50%; margin: 0 auto .5rem; display: flex; align-items: center; justify-content: center; background: #eafaf5; color: var(--gtl-primary); }

        /* Laporan terbaru */
        .gtl-report { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .65rem 0; border-bottom: 1px solid #f0f3f7; }
        .gtl-report:last-child { border-bottom: 0; }
        .gtl-report .left { display: flex; align-items: center; gap: .7rem; min-width: 0; }
        .gtl-avatar {
            width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, #1abb9c, #1a7bb9); color: #fff;
            font-weight: 700; font-size: .8rem; display: flex; align-items: center; justify-content: center;
        }
        .gtl-report .date { font-size: .78rem; font-weight: 700; color: var(--gtl-text); }
        .gtl-report .desc { font-size: .76rem; color: var(--gtl-muted); }
        .gtl-report .desc a { color: var(--gtl-blue); font-weight: 600; text-decoration: none; }
        .gtl-report .desc a:hover { text-decoration: underline; }
        .gtl-progress { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; }
        .gtl-progress .bar { width: 70px; height: 6px; background: #eef1f5; border-radius: 999px; overflow: hidden; }
        .gtl-progress .bar span { display: block; height: 100%; background: linear-gradient(90deg, #1abb9c, #1a7bb9); border-radius: 999px; }
        .gtl-progress .pct { font-size: .78rem; font-weight: 700; color: var(--gtl-text); width: 34px; text-align: right; }
        @media (max-width: 560px) { .gtl-progress .bar { display: none; } }
    </style>

    <div class="gtl-wrap">
        {{-- Header --}}
        <div class="gtl-header">
            <div>
                <h2 class="gtl-title">Dashboard</h2>
                <p class="gtl-sub">Ringkasan monitoring proyek infrastruktur</p>
            </div>
            <span class="gtl-badge">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>

        {{-- Stat cards --}}
        <div class="gtl-stats">
            <div class="gtl-stat">
                <div>
                    <div class="label">Total Proyek</div>
                    <div class="value">{{ $total }}</div>
                </div>
                <div class="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
            </div>

            <div class="gtl-stat is-blue">
                <div>
                    <div class="label">Total Anggaran</div>
                    <div class="value small">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
                </div>
                <div class="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="gtl-stat is-yellow">
                <div>
                    <div class="label">Rata-rata Progres</div>
                    <div class="value">{{ $avgProgress }}<span style="font-size:1rem;color:#73879c;">%</span></div>
                </div>
                <div class="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>

            <div class="gtl-stat is-red">
                <div>
                    <div class="label">Proyek Terlambat</div>
                    <div class="value is-red">{{ $counts['terlambat'] }}</div>
                </div>
                <div class="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>

        {{-- Chart + Proyek terlambat --}}
        <div class="gtl-grid">
            <div class="gtl-card">
                <div class="gtl-card-head">
                    <h3>Proyek per Status</h3>
                    <span class="tag">Distribusi</span>
                </div>
                <div class="gtl-card-body">
                    <div style="height: 280px;"><canvas id="chartStatus"></canvas></div>
                </div>
            </div>

            <div class="gtl-card">
                <div class="gtl-card-head">
                    <h3>Proyek Terlambat</h3>
                    <span class="tag red">{{ $counts['terlambat'] }} item</span>
                </div>
                <div class="gtl-card-body">
                    <div class="gtl-list">
                        @forelse ($lateProjects as $p)
                            <a href="{{ route('projects.show', $p) }}" class="gtl-item">
                                <div class="meta">
                                    <div class="code">{{ $p->code }}</div>
                                    <div class="name">{{ $p->name }}</div>
                                </div>
                                <span class="date">{{ $p->deadline->format('d M Y') }}</span>
                            </a>
                        @empty
                            <div class="gtl-empty">
                                <div class="dot">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Tidak ada proyek terlambat.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Laporan terbaru --}}
        <div class="gtl-card">
            <div class="gtl-card-head">
                <h3>Laporan Terbaru</h3>
                <span class="tag">Timeline</span>
            </div>
            <div class="gtl-card-body">
                @forelse ($recentReports as $r)
                    <div class="gtl-report">
                        <div class="left">
                            <div class="gtl-avatar">{{ strtoupper(substr($r->user->name, 0, 1)) }}</div>
                            <div>
                                <div class="date">{{ $r->report_date->format('d M Y') }}</div>
                                <div class="desc">
                                    oleh {{ $r->user->name }} ·
                                    <a href="{{ route('projects.show', $r->project) }}">{{ $r->project->code }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="gtl-progress">
                            <div class="bar"><span style="width: {{ $r->progress_percent }}%"></span></div>
                            <div class="pct">{{ $r->progress_percent }}%</div>
                        </div>
                    </div>
                @empty
                    <div class="gtl-empty">Belum ada laporan.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Chart.js tetap pakai CDN seperti sebelumnya --}}
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
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 14,
                            color: '#2a3f54',
                            font: { size: 12, family: 'Source Sans Pro, Arial, sans-serif' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(42,63,84,.95)',
                        padding: 10,
                        cornerRadius: 6
                    }
                }
            }
        });
    </script>
</x-app-layout>