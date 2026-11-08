<?php

namespace Tests\Concerns;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;

trait SiapkanData
{
    protected const JAKARTA = '{"type":"Point","coordinates":[106.8456,-6.2088]}';
    protected const BANDUNG = '{"type":"Point","coordinates":[107.6,-6.91]}';

    protected function setUpData(): void
    {
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    protected function akun(string $role): User
    {
        $email = [
            'admin' => 'admin@contoh.test',
            'manajer_proyek' => 'manajer@contoh.test',
            'petugas_lapangan' => 'petugas@contoh.test',
            'klien' => 'klien@contoh.test',
        ][$role];

        return User::where('email', $email)->firstOrFail();
    }

    protected function buatProyek(array $attrs = [], ?string $geojson = self::JAKARTA): Project
    {
        $project = Project::create($attrs + [
            'code' => 'TES-' . Str::upper(Str::random(6)),
            'name' => 'Proyek Uji',
            'status' => 'berjalan',
            'budget' => 1000000,
            'start_date' => today()->subMonth(),
            'deadline' => today()->addMonth(),
            'manager_id' => $this->akun('manajer_proyek')->id,
        ]);

        if ($geojson) {
            $project->setGeometryFromGeoJson($geojson);
        }

        return $project->fresh();
    }
}