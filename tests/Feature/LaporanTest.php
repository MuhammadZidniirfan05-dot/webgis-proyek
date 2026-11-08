<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
        Storage::fake('public');
    }

    private function dataLaporan(array $ubah = []): array
    {
        return $ubah + [
            'report_date' => today()->toDateString(),
            'progress_percent' => 40,
            'notes' => 'Pengecoran selesai',
        ];
    }

    public function test_petugas_yang_ditugaskan_mengirim_laporan_dengan_foto(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();
        $p->officers()->attach($petugas->id);

        $this->actingAs($petugas)
            ->post(route('reports.store', $p), $this->dataLaporan([
                'photos' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
            ]))
            ->assertRedirect(route('projects.show', $p));

        $this->assertDatabaseHas('daily_reports', ['project_id' => $p->id, 'user_id' => $petugas->id, 'progress_percent' => 40]);
        $this->assertSame(40, $p->fresh()->progress_percent);
        $this->assertSame(2, ReportPhoto::count());

        foreach (ReportPhoto::all() as $foto) {
            Storage::disk('public')->assertExists($foto->path);
        }
    }

    public function test_petugas_yang_tidak_ditugaskan_ditolak(): void
    {
        $p = $this->buatProyek();

        $this->actingAs($this->akun('petugas_lapangan'))
            ->post(route('reports.store', $p), $this->dataLaporan())
            ->assertForbidden();

        $this->assertSame(0, DailyReport::count());
    }

    public function test_klien_tidak_boleh_melapor(): void
    {
        $p = $this->buatProyek();

        $this->actingAs($this->akun('klien'))
            ->post(route('reports.store', $p), $this->dataLaporan())
            ->assertForbidden();
    }

    public function test_validasi_tanggal_progres_dan_jumlah_foto(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();
        $p->officers()->attach($petugas->id);
        $this->actingAs($petugas);

        $this->post(route('reports.store', $p), $this->dataLaporan(['report_date' => today()->addDay()->toDateString()]))
            ->assertSessionHasErrors('report_date');

        $this->post(route('reports.store', $p), $this->dataLaporan(['progress_percent' => 101]))
            ->assertSessionHasErrors('progress_percent');

        $enam = array_map(fn ($i) => UploadedFile::fake()->image("f{$i}.jpg"), range(1, 6));
        $this->post(route('reports.store', $p), $this->dataLaporan(['photos' => $enam]))
            ->assertSessionHasErrors('photos');

        $bukanGambar = [UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')];
        $this->post(route('reports.store', $p), $this->dataLaporan(['photos' => $bukanGambar]))
            ->assertSessionHasErrors('photos.0');

        $this->assertSame(0, DailyReport::count());
    }

    public function test_progres_proyek_mengikuti_laporan_terbaru(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();

        $lama = DailyReport::create([
            'project_id' => $p->id, 'user_id' => $petugas->id,
            'report_date' => today()->subDay(), 'progress_percent' => 30,
        ]);
        $baru = DailyReport::create([
            'project_id' => $p->id, 'user_id' => $petugas->id,
            'report_date' => today(), 'progress_percent' => 60,
        ]);
        $p->syncProgress();
        $this->assertSame(60, $p->fresh()->progress_percent);

        $this->actingAs($petugas)
            ->delete(route('reports.destroy', [$p, $baru]))
            ->assertRedirect(route('projects.show', $p));

        $this->assertSame(30, $p->fresh()->progress_percent);
        $this->assertDatabaseMissing('daily_reports', ['id' => $baru->id]);
        $this->assertDatabaseHas('daily_reports', ['id' => $lama->id]);
    }

    public function test_edit_laporan_oleh_pembuat_menghapus_foto_dan_menyinkronkan_progres(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();
        $p->officers()->attach($petugas->id);

        $this->actingAs($petugas)->post(route('reports.store', $p), $this->dataLaporan([
            'photos' => [UploadedFile::fake()->image('a.jpg')],
        ]));

        $laporan = DailyReport::firstOrFail();
        $foto = $laporan->photos()->firstOrFail();

        $this->actingAs($petugas)
            ->put(route('reports.update', [$p, $laporan]), $this->dataLaporan([
                'progress_percent' => 55,
                'remove_photos' => [$foto->id],
            ]))
            ->assertRedirect(route('projects.show', $p));

        $this->assertSame(55, $p->fresh()->progress_percent);
        $this->assertDatabaseMissing('report_photos', ['id' => $foto->id]);
        Storage::disk('public')->assertMissing($foto->path);
    }

    public function test_petugas_lain_tidak_boleh_mengedit_atau_menghapus_laporan_orang(): void
    {
        $pemilik = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();
        $laporan = DailyReport::create([
            'project_id' => $p->id, 'user_id' => $pemilik->id,
            'report_date' => today(), 'progress_percent' => 10,
        ]);

        $lain = User::create(['name' => 'Petugas Dua', 'email' => 'dua@contoh.test', 'password' => 'rahasia123']);
        $lain->assignRole('petugas_lapangan');

        $this->actingAs($lain)->get(route('reports.edit', [$p, $laporan]))->assertForbidden();
        $this->actingAs($lain)->delete(route('reports.destroy', [$p, $laporan]))->assertForbidden();

        $this->actingAs($this->akun('admin'))->get(route('reports.edit', [$p, $laporan]))->assertOk();
    }

    public function test_laporan_dari_proyek_lain_tidak_bisa_diakses_lewat_url_proyek_ini(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $a = $this->buatProyek();
        $b = $this->buatProyek();
        $laporanB = DailyReport::create([
            'project_id' => $b->id, 'user_id' => $petugas->id,
            'report_date' => today(), 'progress_percent' => 10,
        ]);

        $this->actingAs($this->akun('admin'))
            ->get(route('reports.edit', [$a, $laporanB]))
            ->assertNotFound();
    }
}