<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $region = Region::firstOrCreate(['name' => 'Wilayah Contoh'], ['level' => 'kecamatan']);
        $manager = User::where('email', 'manajer@contoh.test')->first();
        $petugas = User::where('email', 'petugas@contoh.test')->first();

        $data = [
            ['PRJ-001', 'Jembatan Contoh', 'berjalan', '2026-10-01', '2026-12-31',
                '{"type":"Point","coordinates":[106.8456,-6.2088]}'],
            ['PRJ-002', 'Perbaikan Jalan Contoh', 'berjalan', '2026-08-01', '2026-10-15',
                '{"type":"Polygon","coordinates":[[[106.80,-6.20],[106.82,-6.20],[106.82,-6.22],[106.80,-6.22],[106.80,-6.20]]]}'],
            ['PRJ-003', 'Drainase Jauh', 'perencanaan', '2026-11-01', '2027-03-01',
                '{"type":"Point","coordinates":[107.60,-6.91]}'],
        ];

        foreach ($data as [$code, $name, $status, $start, $deadline, $geojson]) {
            $project = Project::updateOrCreate(['code' => $code], [
                'name' => $name,
                'status' => $status,
                'budget' => 500000000,
                'start_date' => $start,
                'deadline' => $deadline,
                'region_id' => $region->id,
                'manager_id' => $manager?->id,
            ]);
            $project->setGeometryFromGeoJson($geojson);
            if ($petugas) {
                $project->officers()->syncWithoutDetaching([$petugas->id]);
            }
        }
    }
}