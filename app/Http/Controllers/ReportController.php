<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Project;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('report', $project);

        $data = $request->validate([
            'report_date' => ['required', 'date', 'before_or_equal:today'],
            'progress_percent' => ['required', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], // 4 MB
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $project, $data) {
            $report = DailyReport::create([
                'project_id' => $project->id,
                'user_id' => $request->user()->id,
                'report_date' => $data['report_date'],
                'progress_percent' => $data['progress_percent'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($request->file('photos', []) as $i => $file) {
                $path = $file->store('laporan/' . $project->id, 'public');
                ReportPhoto::create([
                    'daily_report_id' => $report->id,
                    'path' => $path,
                    'caption' => $request->input("captions.$i"),
                ]);
            }

            $project->syncProgress();
        });

        return redirect()->route('projects.show', $project)->with('status', 'Laporan disimpan.');
    }

    public function destroy(Request $request, Project $project, DailyReport $report)
    {
        abort_unless($report->project_id === $project->id, 404);

        // Hanya admin, atau pembuat laporan itu sendiri
        abort_unless(
            $request->user()->hasRole('admin') || $report->user_id === $request->user()->id,
            403
        );

        foreach ($report->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $report->delete();
        $project->syncProgress();

        return redirect()->route('projects.show', $project)->with('status', 'Laporan dihapus.');
    }
        public function edit(Request $request, Project $project, DailyReport $report)
    {
        abort_unless($report->project_id === $project->id, 404);
        $this->authorizeReport($request, $report);

        $report->load('photos');

        return view('reports.edit', compact('project', 'report'));
    }

    public function update(Request $request, Project $project, DailyReport $report)
    {
        abort_unless($report->project_id === $project->id, 404);
        $this->authorizeReport($request, $report);

        $data = $request->validate([
            'report_date' => ['required', 'date', 'before_or_equal:today'],
            'progress_percent' => ['required', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
        ]);

        $sisa = $report->photos()->count() - count($request->input('remove_photos', []));
        $baru = count($request->file('photos', []));
        if ($sisa + $baru > 5) {
            return back()->withErrors(['photos' => 'Maksimal 5 foto per laporan.'])->withInput();
        }

        DB::transaction(function () use ($request, $project, $report, $data) {
            $report->update([
                'report_date' => $data['report_date'],
                'progress_percent' => $data['progress_percent'],
                'notes' => $data['notes'] ?? null,
            ]);

            $hapus = $report->photos()->whereIn('id', $request->input('remove_photos', []))->get();
            foreach ($hapus as $photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }

            foreach ($request->file('photos', []) as $file) {
                $path = $file->store('laporan/' . $project->id, 'public');
                ReportPhoto::create(['daily_report_id' => $report->id, 'path' => $path]);
            }

            $project->syncProgress();
        });

        return redirect()->route('projects.show', $project)->with('status', 'Laporan diperbarui.');
    }

    private function authorizeReport(Request $request, DailyReport $report): void
    {
        abort_unless(
            $request->user()->hasRole('admin') || $report->user_id === $request->user()->id,
            403
        );
    }
}