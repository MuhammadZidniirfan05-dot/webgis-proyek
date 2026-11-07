<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['region', 'manager'])->latest()->paginate(10);
        return view('projects.index', compact('projects'));
    }

       public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['region', 'manager', 'officers']);

        $reports = $project->reports()
            ->with(['user', 'photos'])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->get();

        $geojson = Project::withGeoJson()->whereKey($project->id)->value('geojson');
        $sCurve = $this->sCurve($project, $reports);

        return view('projects.show', compact('project', 'reports', 'geojson', 'sCurve'));
    }

    // Kurva S: rencana linear (mulai ke deadline) vs aktual (dari laporan harian)
    private function sCurve(Project $project, $reports): ?array
    {
        if (! $project->start_date || ! $project->deadline || $project->deadline->lte($project->start_date)) {
            return null;
        }

        $start = $project->start_date->copy()->startOfDay();
        $plannedDays = (int) $start->diffInDays($project->deadline->copy()->startOfDay());

        $end = $project->deadline->copy()->startOfDay();
        $lastReport = $reports->max('report_date');
        if ($lastReport && $lastReport->gt($end)) {
            $end = $lastReport->copy()->startOfDay();
        }
        $totalDays = (int) $start->diffInDays($end);
        $step = max(1, (int) ceil($totalDays / 60)); // maksimal sekitar 60 titik

        // progres terakhir per tanggal
        $perDay = $reports
            ->sortBy([['report_date', 'asc'], ['id', 'asc']])
            ->groupBy(fn ($r) => $r->report_date->format('Y-m-d'))
            ->map(fn ($g) => $g->last()->progress_percent);

        // laporan sebelum tanggal mulai dianggap nilai awal
        $carry = null;
        foreach ($perDay as $tgl => $nilai) {
            if ($tgl < $start->format('Y-m-d')) {
                $carry = $nilai;
            }
        }

        $labels = $planned = $actual = [];
        $today = today();

        for ($i = 0; $i <= $totalDays; $i++) {
            $d = $start->copy()->addDays($i);
            $key = $d->format('Y-m-d');

            if ($perDay->has($key)) {
                $carry = $perDay[$key];
            }

            if ($i % $step === 0 || $i === $totalDays) {
                $labels[] = $d->format('d M');
                $planned[] = round(min(100, $i / $plannedDays * 100), 1);
                $actual[] = $d->gt($today) ? null : $carry;
            }
        }

        return compact('labels', 'planned', 'actual');
    }

    public function create()
    {
        return view('projects.create', [
            'project' => new Project(),
            'regions' => Region::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $project = Project::create($data + ['manager_id' => $request->user()->id]);
        $project->setGeometryFromGeoJson($request->input('geojson'));

        return redirect()->route('projects.index')->with('status', 'Proyek disimpan.');
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $geojson = Project::withGeoJson()->whereKey($project->id)->value('geojson');

        return view('projects.edit', [
            'project' => $project,
            'geojson' => $geojson,
            'regions' => Region::orderBy('name')->get(),
            'officers' => User::role('petugas_lapangan')->orderBy('name')->get(),
            'assignedIds' => $project->officers()->pluck('users.id')->all(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate(
            ['geojson' => ['required', 'json']],
            ['geojson.required' => 'Lokasi proyek tidak boleh kosong.']
        );

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:projects,code,' . $project->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', Project::STATUSES)],
            'budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'officers' => ['nullable', 'array'],
            'officers.*' => ['integer', 'exists:users,id'],
        ]);

        $project->update(collect($data)->except('officers')->all());
        $project->setGeometryFromGeoJson($request->input('geojson'));

        // Hanya user ber-role petugas_lapangan yang boleh ditugaskan
        $petugasIds = User::role('petugas_lapangan')
            ->whereIn('id', $request->input('officers', []))
            ->pluck('id');
        $project->officers()->sync($petugasIds);

        return redirect()->route('projects.index')->with('status', 'Proyek diperbarui.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Proyek dihapus.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'geojson' => ['required', 'json'],
        ], ['geojson.required' => 'Gambar lokasi proyek di peta dulu.']);

        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:projects,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', Project::STATUSES)],
            'budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'region_id' => ['nullable', 'exists:regions,id'],
        ]);
    }
}