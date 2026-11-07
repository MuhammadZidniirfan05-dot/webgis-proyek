<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $warna = [
            'perencanaan' => '#6b7280', 'berjalan' => '#2563eb',
            'selesai' => '#16a34a', 'ditunda' => '#f59e0b', 'terlambat' => '#dc2626',
        ];

        $counts = [];
        foreach (array_keys($warna) as $s) {
            $counts[$s] = Project::withStatus($s)->count();
        }

        return view('dashboard', [
            'counts' => $counts,
            'colors' => $warna,
            'total' => Project::count(),
            'totalBudget' => (float) Project::sum('budget'),
            'avgProgress' => (int) round(Project::avg('progress_percent') ?? 0),
            'lateProjects' => Project::late()->orderBy('deadline')->limit(5)->get(),
            'recentReports' => DailyReport::with(['project', 'user'])
                ->orderByDesc('report_date')->orderByDesc('id')->limit(5)->get(),
        ]);
    }
}