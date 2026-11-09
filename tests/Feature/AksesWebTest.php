<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class AksesWebTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        foreach (['/proyek', '/peta', '/dashboard', '/audit-log'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_klien_tidak_boleh_membuka_form_proyek_baru(): void
    {
        $this->actingAs($this->akun('klien'))->get('/proyek/baru')->assertForbidden();
        $this->actingAs($this->akun('petugas_lapangan'))->get('/proyek/baru')->assertForbidden();
    }

    public function test_admin_dan_manajer_boleh_membuka_form_proyek_baru(): void
    {
        $this->actingAs($this->akun('admin'))->get('/proyek/baru')->assertOk();
        $this->actingAs($this->akun('manajer_proyek'))->get('/proyek/baru')->assertOk();
    }

    public function test_manajer_tidak_boleh_mengedit_proyek_orang_lain(): void
    {
        $manajer = $this->akun('manajer_proyek');
        $orangLain = $this->buatProyek(['manager_id' => $this->akun('admin')->id]);
        $milik = $this->buatProyek(['manager_id' => $manajer->id]);

        $this->actingAs($manajer)->get(route('projects.edit', $orangLain))->assertForbidden();
        $this->actingAs($manajer)->get(route('projects.edit', $milik))->assertOk();
        $this->actingAs($manajer)->delete(route('projects.destroy', $orangLain))->assertForbidden();
    }

    public function test_admin_menyimpan_proyek_dengan_geometri(): void
    {
        $this->actingAs($this->akun('admin'))
            ->post(route('projects.store'), [
                'code' => 'PRJ-X1',
                'name' => 'Proyek Baru',
                'status' => 'perencanaan',
                'budget' => 250000000,
                'start_date' => today()->toDateString(),
                'deadline' => today()->addMonths(2)->toDateString(),
                'geojson' => self::JAKARTA,
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', ['code' => 'PRJ-X1', 'manager_id' => $this->akun('admin')->id]);
        $this->assertTrue(DB::table('projects')->where('code', 'PRJ-X1')->whereNotNull('geom')->exists());
    }

    public function test_simpan_proyek_tanpa_lokasi_ditolak(): void
    {
        $this->actingAs($this->akun('admin'))
            ->post(route('projects.store'), [
                'code' => 'PRJ-X2', 'name' => 'Tanpa Lokasi', 'status' => 'perencanaan', 'budget' => 1,
            ])
            ->assertSessionHasErrors('geojson');

        $this->assertDatabaseMissing('projects', ['code' => 'PRJ-X2']);
    }

    public function test_klien_tidak_boleh_menyimpan_proyek(): void
    {
        $this->actingAs($this->akun('klien'))
            ->post(route('projects.store'), [
                'code' => 'PRJ-X3', 'name' => 'Ilegal', 'status' => 'perencanaan', 'budget' => 1,
                'geojson' => self::JAKARTA,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('projects', ['code' => 'PRJ-X3']);
    }

    public function test_penugasan_petugas_hanya_menerima_user_ber_role_petugas(): void
    {
        $p = $this->buatProyek();
        $petugas = $this->akun('petugas_lapangan');
        $klien = $this->akun('klien');

        $this->actingAs($this->akun('admin'))
            ->put(route('projects.update', $p), [
                'code' => $p->code, 'name' => $p->name, 'status' => 'berjalan', 'budget' => 1000,
                'geojson' => self::JAKARTA,
                'officers' => [$petugas->id, $klien->id],
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertSame([$petugas->id], $p->officers()->pluck('users.id')->all());
    }

    public function test_data_peta_memfilter_status_dan_radius(): void
    {
        $terlambat = $this->buatProyek(['deadline' => today()->subDays(3)]);
        $this->buatProyek(['deadline' => today()->addDays(30)], self::BANDUNG);
        $klien = $this->akun('klien');

        $this->actingAs($klien)->getJson('/peta/data')->assertOk()->assertJsonCount(2, 'features');

        $this->actingAs($klien)->getJson('/peta/data?status=terlambat')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.id', $terlambat->id);

        $this->actingAs($klien)->getJson('/peta/data?lat=-6.2088&lng=106.8456&radius=5')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.id', $terlambat->id);
    }

    public function test_filter_peta_menolak_nilai_tidak_valid(): void
    {
        $this->actingAs($this->akun('klien'))
            ->getJson('/peta/data?status=ngawur')->assertStatus(422);
        $this->actingAs($this->akun('klien'))
            ->getJson('/peta/data?lat=999&lng=0&radius=5')->assertStatus(422);
    }

    public function test_dashboard_menampilkan_angka_ringkasan(): void
    {
        $this->buatProyek(['budget' => 100, 'deadline' => today()->subDay()]);
        $this->buatProyek(['budget' => 200]);

        $this->actingAs($this->akun('admin'))->get('/dashboard')
            ->assertOk()
            ->assertViewHas('total', 2)
            ->assertViewHas('totalBudget', 300.0);
    }

    public function test_detail_proyek_terbuka_untuk_semua_role(): void
    {
        $p = $this->buatProyek();

        foreach (['admin', 'manajer_proyek', 'petugas_lapangan', 'klien'] as $role) {
            $this->actingAs($this->akun($role))->get(route('projects.show', $p))->assertOk();
        }
    }

    public function test_export_excel_dan_pdf(): void
    {
        $this->buatProyek();
        $klien = $this->akun('klien');

        $this->actingAs($klien)->get(route('projects.export.excel'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $pdf = $this->actingAs($klien)->get(route('projects.export.pdf'));
        $pdf->assertOk();
        $this->assertStringContainsString('pdf', $pdf->headers->get('content-type'));

        $this->actingAs($klien)->get(route('projects.export.excel', ['status' => 'ngawur']))->assertStatus(422);
    }
}