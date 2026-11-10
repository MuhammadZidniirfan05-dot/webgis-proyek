<?php

namespace Tests\Feature;

use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_hanya_admin_boleh_membuka_halaman_wilayah(): void
    {
        $this->actingAs($this->akun('admin'))->get(route('regions.index'))->assertOk();

        foreach (['manajer_proyek', 'petugas_lapangan', 'klien'] as $role) {
            $this->actingAs($this->akun($role))->get(route('regions.index'))->assertForbidden();
        }
    }

    public function test_admin_menambah_dan_mengubah_wilayah(): void
    {
        $admin = $this->akun('admin');

        $this->actingAs($admin)
            ->post(route('regions.store'), ['name' => 'Semarang Utara', 'level' => 'kecamatan'])
            ->assertRedirect(route('regions.index'));
        $this->assertDatabaseHas('regions', ['name' => 'Semarang Utara']);

        $r = Region::where('name', 'Semarang Utara')->firstOrFail();
        $this->actingAs($admin)
            ->put(route('regions.update', $r), ['name' => 'Semarang Selatan', 'level' => 'kecamatan'])
            ->assertRedirect(route('regions.index'));
        $this->assertDatabaseHas('regions', ['id' => $r->id, 'name' => 'Semarang Selatan']);
    }

    public function test_nama_ganda_dan_tingkat_tidak_valid_ditolak(): void
    {
        Region::create(['name' => 'Kota A', 'level' => 'kabupaten']);
        $admin = $this->akun('admin');

        $this->actingAs($admin)->post(route('regions.store'), ['name' => 'Kota A', 'level' => 'kabupaten'])
            ->assertSessionHasErrors('name');
        $this->actingAs($admin)->post(route('regions.store'), ['name' => 'Kota B', 'level' => 'galaksi'])
            ->assertSessionHasErrors('level');
    }

    public function test_wilayah_yang_dipakai_proyek_tidak_bisa_dihapus(): void
    {
        $r = Region::create(['name' => 'Dipakai', 'level' => 'kecamatan']);
        $this->buatProyek(['region_id' => $r->id]);

        $this->actingAs($this->akun('admin'))
            ->delete(route('regions.destroy', $r))
            ->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('regions', ['id' => $r->id]);
    }

    public function test_wilayah_kosong_bisa_dihapus(): void
    {
        $r = Region::create(['name' => 'Kosong', 'level' => 'kecamatan']);

        $this->actingAs($this->akun('admin'))
            ->delete(route('regions.destroy', $r))
            ->assertRedirect(route('regions.index'));

        $this->assertDatabaseMissing('regions', ['id' => $r->id]);
    }

    public function test_menu_wilayah_hanya_terlihat_oleh_admin(): void
    {
        $this->actingAs($this->akun('admin'))->get('/dashboard')
            ->assertSee(route('regions.index'), false);

        $this->actingAs($this->akun('klien'))->get('/dashboard')
            ->assertDontSee(route('regions.index'), false);
    }
}