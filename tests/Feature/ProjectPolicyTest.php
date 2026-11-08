<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_admin_boleh_mengubah_dan_menghapus_semua_proyek(): void
    {
        $admin = $this->akun('admin');
        $p = $this->buatProyek();

        $this->assertTrue($admin->can('update', $p));
        $this->assertTrue($admin->can('delete', $p));
    }

    public function test_manajer_hanya_boleh_mengubah_proyek_miliknya(): void
    {
        $manajer = $this->akun('manajer_proyek');

        $milik = $this->buatProyek(['manager_id' => $manajer->id]);
        $orangLain = $this->buatProyek(['manager_id' => $this->akun('admin')->id]);

        $this->assertTrue($manajer->can('update', $milik));
        $this->assertTrue($manajer->can('delete', $milik));
        $this->assertFalse($manajer->can('update', $orangLain));
        $this->assertFalse($manajer->can('delete', $orangLain));
    }

    public function test_petugas_dan_klien_tidak_boleh_mengubah_proyek(): void
    {
        $p = $this->buatProyek();

        $this->assertFalse($this->akun('petugas_lapangan')->can('update', $p));
        $this->assertFalse($this->akun('klien')->can('update', $p));
        $this->assertFalse($this->akun('klien')->can('delete', $p));
    }

    public function test_hanya_petugas_yang_ditugaskan_boleh_melapor(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();

        $this->assertFalse($petugas->can('report', $p));

        $p->officers()->attach($petugas->id);

        $this->assertTrue($petugas->can('report', $p));
    }

    public function test_aturan_melapor_untuk_admin_manajer_dan_klien(): void
    {
        $manajer = $this->akun('manajer_proyek');
        $milik = $this->buatProyek(['manager_id' => $manajer->id]);
        $orangLain = $this->buatProyek(['manager_id' => $this->akun('admin')->id]);

        $this->assertTrue($this->akun('admin')->can('report', $orangLain));
        $this->assertTrue($manajer->can('report', $milik));
        $this->assertFalse($manajer->can('report', $orangLain));
        $this->assertFalse($this->akun('klien')->can('report', $milik));
    }

    public function test_semua_pengguna_login_boleh_melihat_proyek(): void
    {
        $p = $this->buatProyek();

        foreach (['admin', 'manajer_proyek', 'petugas_lapangan', 'klien'] as $role) {
            $this->assertTrue($this->akun($role)->can('view', $p), $role);
        }
    }
}