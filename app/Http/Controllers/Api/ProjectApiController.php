<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\ReportPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectApiController extends Controller
{
    private function properties(Project $p): array
    {
        return [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'status' => $p->display_status,
            'budget' => (float) $p->budget,
            'start_date' => $p->start_date?->toDateString(),
            'deadline' => $p->deadline?->toDateString(),
            'progress' => $p->progress_percent,
        ];
    }

    // GET /api/projects?status=&region_id=&lat=&lng=&radius=(km)
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:' . implode(',', array_merge(Project::STATUSES, ['terlambat']))],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
        ]);

        $query = Project::withGeoJson()
            ->whereNotNull('geom')
            ->withStatus($request->input('status'));

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->filled(['lat', 'lng', 'radius'])) {
            $query->withinRadius(
                (float) $request->input('lat'),
                (float) $request->input('lng'),
                (float) $request->input('radius') * 1000
            );
        }

        $features = $query->get()->map(fn ($p) => [
            'type' => 'Feature',
            'geometry' => json_decode($p->geojson),
            'properties' => $this->properties($p),
        ]);

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }

    // GET /api/projects/{project}
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['region', 'manager', 'officers']);
        $geojson = Project::withGeoJson()->whereKey($project->id)->value('geojson');

        $reports = $project->reports()
            ->with(['user', 'photos'])
            ->orderByDesc('report_date')->orderByDesc('id')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'date' => $r->report_date->toDateString(),
                'progress' => $r->progress_percent,
                'notes' => $r->notes,
                'by' => $r->user->name,
                'photos' => $r->photos->map(fn ($ph) => asset('storage/' . $ph->path)),
            ]);

        return response()->json([
            'type' => 'Feature',
            'geometry' => $geojson ? json_decode($geojson) : null,
            'properties' => $this->properties($project) + [
                'description' => $project->description,
                'region' => $project->region?->name,
                'manager' => $project->manager?->name,
                'officers' => $project->officers->pluck('name'),
            ],
            'reports' => $reports,
        ]);
    }

    // POST /api/projects/{project}/reports  (multipart/form-data bila ada foto)
    public function storeReport(Request $request, Project $project): JsonResponse
    {
        $this->authorize('report', $project);

        $data = $request->validate([
            'report_date' => ['required', 'date', 'before_or_equal:today'],
            'progress_percent' => ['required', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $report = DB::transaction(function () use ($request, $project, $data) {
            $report = DailyReport::create([
                'project_id' => $project->id,
                'user_id' => $request->user()->id,
                'report_date' => $data['report_date'],
                'progress_percent' => $data['progress_percent'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($request->file('photos', []) as $file) {
                ReportPhoto::create([
                    'daily_report_id' => $report->id,
                    'path' => $file->store('laporan/' . $project->id, 'public'),
                ]);
            }

            $project->syncProgress();

            return $report;
        });

        return response()->json([
            'id' => $report->id,
            'progress_project' => $project->fresh()->progress_percent,
        ], 201);
    }
}