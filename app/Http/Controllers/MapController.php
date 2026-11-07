<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    public function index()
    {
        return view('map.index');
    }

    // Data proyek sebagai GeoJSON FeatureCollection untuk Leaflet
    public function projects(): JsonResponse
    {
        $features = Project::withGeoJson()
            ->whereNotNull('geom')
            ->get()
            ->map(fn ($p) => [
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