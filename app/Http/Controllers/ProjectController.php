<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Region;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['region', 'manager'])->latest()->paginate(10);
        return view('projects.index', compact('projects'));
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
    public function edit(Project $project)
{
    $this->authorize('update', $project);

    $geojson = Project::withGeoJson()->whereKey($project->id)->value('geojson');

    return view('projects.edit', [
        'project' => $project,
        'geojson' => $geojson,
        'regions' => Region::orderBy('name')->get(),
    ]);
}

public function update(Request $request, Project $project)
{
    $this->authorize('update', $project);

    $request->validate(['geojson' => ['required', 'json']],
        ['geojson.required' => 'Lokasi proyek tidak boleh kosong.']);

    $data = $request->validate([
        'code' => ['required', 'string', 'max:50', 'unique:projects,code,' . $project->id],
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'status' => ['required', 'in:' . implode(',', Project::STATUSES)],
        'budget' => ['required', 'numeric', 'min:0'],
        'start_date' => ['nullable', 'date'],
        'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
        'region_id' => ['nullable', 'exists:regions,id'],
    ]);

    $project->update($data);
    $project->setGeometryFromGeoJson($request->input('geojson'));

    return redirect()->route('projects.index')->with('status', 'Proyek diperbarui.');
}

public function destroy(Project $project)
{
    $this->authorize('delete', $project);
    $project->delete();

    return redirect()->route('projects.index')->with('status', 'Proyek dihapus.');
}
}