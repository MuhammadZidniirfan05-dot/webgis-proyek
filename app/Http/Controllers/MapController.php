<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        return view('map.index', [
            'regions' => Region::orderBy('name')->get(),
            'statuses' => array_merge(Project::STATUSES, ['terlambat']),
        ]);
    }

    // Data proyek sebagai GeoJSON FeatureCollection, dengan filter
    public function projects(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:' . implode(',', array_merge(Project::STATUSES, ['terlambat']))],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:5000'], // km
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
                (float) $request->input('radius') * 1000 // km ke meter
            );
        }

        $features = $query->get()->map(fn ($p) => [
            'type' => 'Feature',
            'geometry' => json_decode($p->geojson),
            'properties' => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'status' => $p->display_status,
                'budget' => (float) $p->budget,
                'deadline' => $p->deadline?->format('d M Y'),
                'progress' => $p->progress_percent,
            ],
        ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}