<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class SpasialTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_geojson_titik_tersimpan_dan_terbaca_kembali(): void
    {
        $p = $this->buatProyek();

        $geo = json_decode(Project::withGeoJson()->find($p->id)->geojson, true);

        $this->assertSame('Point', $geo['type']);
        $this->assertEqualsWithDelta(106.8456, $geo['coordinates'][0], 0.00001);
        $this->assertEqualsWithDelta(-6.2088, $geo['coordinates'][1], 0.00001);
    }

    public function test_geojson_polygon_dan_feature_wrapper_diterima(): void
    {
        $polygon = '{"type":"Polygon","coordinates":[[[106.80,-6.20],[106.82,-6.20],[106.82,-6.22],[106.80,-6.22],[106.80,-6.20]]]}';
        $p = $this->buatProyek([], null);

        $p->setGeometryFromGeoJson('{"type":"Feature","properties":{},"geometry":' . $polygon . '}');

        $geo = json_decode(Project::withGeoJson()->find($p->id)->geojson, true);
        $this->assertSame('Polygon', $geo['type']);
    }

    public function test_radius_5_km_hanya_memuat_proyek_yang_dekat(): void
    {
        $jakarta = $this->buatProyek(['name' => 'Jakarta']);
        $bandung = $this->buatProyek(['name' => 'Bandung'], self::BANDUNG);

        $ids = Project::withinRadius(-6.2088, 106.8456, 5000)->pluck('id');

        $this->assertTrue($ids->contains($jakarta->id));
        $this->assertFalse($ids->contains($bandung->id));
    }

    public function test_radius_dihitung_dalam_meter(): void
    {
        $p = $this->buatProyek();

        $this->assertTrue(Project::withinRadius(-6.1988, 106.8456, 2000)->pluck('id')->contains($p->id));
        $this->assertFalse(Project::withinRadius(-6.1988, 106.8456, 500)->pluck('id')->contains($p->id));
    }

    public function test_polygon_yang_melingkupi_pusat_masuk_radius_kecil(): void
    {
        $polygon = '{"type":"Polygon","coordinates":[[[106.80,-6.20],[106.82,-6.20],[106.82,-6.22],[106.80,-6.22],[106.80,-6.20]]]}';
        $p = $this->buatProyek([], $polygon);

        $this->assertTrue(Project::withinRadius(-6.21, 106.81, 100)->pluck('id')->contains($p->id));
    }
}