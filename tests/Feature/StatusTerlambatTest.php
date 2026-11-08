<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class StatusTerlambatTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_lewat_deadline_dan_belum_selesai_berstatus_terlambat(): void
    {
        $p = $this->buatProyek(['deadline' => today()->subDay()]);

        $this->assertTrue($p->is_late);
        $this->assertSame('terlambat', $p->display_status);
    }

    public function test_deadline_hari_ini_belum_terlambat(): void
    {
        $p = $this->buatProyek(['deadline' => today()]);

        $this->assertFalse($p->is_late);
        $this->assertSame('berjalan', $p->display_status);
    }

    public function test_proyek_selesai_tidak_pernah_terlambat(): void
    {
        $p = $this->buatProyek(['status' => 'selesai', 'deadline' => today()->subMonth()]);

        $this->assertFalse($p->is_late);
        $this->assertSame('selesai', $p->display_status);
    }

    public function test_proyek_tanpa_deadline_tidak_terlambat(): void
    {
        $p = $this->buatProyek(['deadline' => null]);

        $this->assertFalse($p->is_late);
    }

    public function test_scope_late_dan_with_status_konsisten(): void
    {
        $terlambat = $this->buatProyek(['deadline' => today()->subDays(5)]);
        $normal = $this->buatProyek(['deadline' => today()->addDays(5)]);
        $selesai = $this->buatProyek(['status' => 'selesai', 'deadline' => today()->subDays(5)]);

        $late = Project::late()->pluck('id');
        $this->assertTrue($late->contains($terlambat->id));
        $this->assertFalse($late->contains($normal->id));
        $this->assertFalse($late->contains($selesai->id));

        $berjalan = Project::withStatus('berjalan')->pluck('id');
        $this->assertTrue($berjalan->contains($normal->id));
        $this->assertFalse($berjalan->contains($terlambat->id));

        $this->assertSame([$terlambat->id], Project::withStatus('terlambat')->pluck('id')->all());
        $this->assertSame([$selesai->id], Project::withStatus('selesai')->pluck('id')->all());
    }
}