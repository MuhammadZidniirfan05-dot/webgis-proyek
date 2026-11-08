<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_perubahan_proyek_tercatat_dengan_nilai_lama_dan_baru(): void
    {
        $admin = $this->akun('admin');
        $p = $this->buatProyek(['name' => 'Nama Lama']);

        $this->actingAs($admin);
        $p->update(['name' => 'Nama Baru']);

        $log = AuditLog::where('auditable_type', Project::class)
            ->where('auditable_id', $p->id)
            ->where('action', 'updated')
            ->where('user_id', $admin->id)
            ->latest('id')->firstOrFail();

        $this->assertSame('Nama Lama', $log->old_values['name']);
        $this->assertSame('Nama Baru', $log->new_values['name']);
        $this->assertArrayNotHasKey('updated_at', $log->new_values);
    }

    public function test_pembuatan_dan_penghapusan_tercatat(): void
    {
        $this->actingAs($this->akun('admin'));
        $p = $this->buatProyek();

        $this->assertDatabaseHas('audit_logs', ['auditable_id' => $p->id, 'auditable_type' => Project::class, 'action' => 'created']);

        $p->delete();

        $this->assertDatabaseHas('audit_logs', ['auditable_id' => $p->id, 'auditable_type' => Project::class, 'action' => 'deleted']);
    }

    public function test_perubahan_geometri_dicatat_tanpa_menyimpan_data_biner(): void
    {
        $p = $this->buatProyek();
        $sebelum = AuditLog::count();

        $p->setGeometryFromGeoJson(self::BANDUNG);

        $this->assertSame($sebelum + 1, AuditLog::count());
        $log = AuditLog::latest('id')->first();
        $this->assertSame('diubah', $log->new_values['geom']);
    }

    public function test_simpan_tanpa_perubahan_tidak_membuat_catatan(): void
    {
        $p = $this->buatProyek();
        $sebelum = AuditLog::count();

        $p->save();

        $this->assertSame($sebelum, AuditLog::count());
    }

    public function test_halaman_audit_log_hanya_untuk_admin(): void
    {
        $this->actingAs($this->akun('admin'))->get('/audit-log')->assertOk();

        foreach (['manajer_proyek', 'petugas_lapangan', 'klien'] as $role) {
            $this->actingAs($this->akun($role))->get('/audit-log')->assertForbidden();
        }
    }

    public function test_filter_audit_log_menolak_nilai_tidak_valid(): void
    {
        $this->actingAs($this->akun('admin'))
            ->get('/audit-log?action=ngawur')
            ->assertSessionHasErrors('action');
    }
}