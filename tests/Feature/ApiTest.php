<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_login_berhasil_mengeluarkan_token(): void
    {
        $this->postJson('/api/login', ['email' => 'petugas@contoh.test', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'roles']])
            ->assertJsonPath('user.roles.0', 'petugas_lapangan');
    }

    public function test_login_dengan_password_salah_ditolak(): void
    {
        $this->postJson('/api/login', ['email' => 'petugas@contoh.test', 'password' => 'salah'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_endpoint_tanpa_token_mendapat_401(): void
    {
        $p = $this->buatProyek();

        $this->getJson('/api/projects')->assertUnauthorized();
        $this->getJson("/api/projects/{$p->id}")->assertUnauthorized();
        $this->postJson("/api/projects/{$p->id}/reports", [])->assertUnauthorized();
    }

    public function test_token_dari_login_bisa_dipakai_dan_logout_mencabutnya(): void
    {
        $token = $this->postJson('/api/login', ['email' => 'klien@contoh.test', 'password' => 'password'])
            ->json('token');

        $this->withToken($token)->getJson('/api/projects')->assertOk();

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_daftar_proyek_berupa_feature_collection_dengan_filter(): void
    {
        $this->buatProyek(['deadline' => today()->subDay()]);
        $this->buatProyek(['deadline' => today()->addMonth()], self::BANDUNG);
        Sanctum::actingAs($this->akun('klien'));

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(2, 'features');

        $this->getJson('/api/projects?status=terlambat')->assertJsonCount(1, 'features');
        $this->getJson('/api/projects?lat=-6.2088&lng=106.8456&radius=5')->assertJsonCount(1, 'features');
        $this->getJson('/api/projects?status=ngawur')->assertStatus(422);
    }

    public function test_detail_proyek_memuat_geometri_dan_laporan(): void
    {
        $p = $this->buatProyek();
        Sanctum::actingAs($this->akun('klien'));

        $this->getJson("/api/projects/{$p->id}")
            ->assertOk()
            ->assertJsonPath('type', 'Feature')
            ->assertJsonPath('geometry.type', 'Point')
            ->assertJsonPath('properties.id', $p->id)
            ->assertJsonStructure(['properties', 'reports']);
    }

    public function test_petugas_ditugaskan_mengirim_laporan_lewat_api(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();
        $p->officers()->attach($petugas->id);
        Sanctum::actingAs($petugas);

        $this->postJson("/api/projects/{$p->id}/reports", [
            'report_date' => today()->toDateString(),
            'progress_percent' => 45,
            'notes' => 'Lewat API',
        ])
            ->assertCreated()
            ->assertJsonPath('progress_project', 45);

        $this->assertDatabaseHas('daily_reports', ['project_id' => $p->id, 'user_id' => $petugas->id]);
    }

    public function test_klien_dan_petugas_tidak_ditugaskan_ditolak_melapor(): void
    {
        $p = $this->buatProyek();
        $payload = ['report_date' => today()->toDateString(), 'progress_percent' => 50];

        Sanctum::actingAs($this->akun('klien'));
        $this->postJson("/api/projects/{$p->id}/reports", $payload)->assertForbidden();

        Sanctum::actingAs($this->akun('petugas_lapangan'));
        $this->postJson("/api/projects/{$p->id}/reports", $payload)->assertForbidden();

        $this->assertDatabaseCount('daily_reports', 0);
    }

    public function test_login_dibatasi_laju_percobaannya(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => 'petugas@contoh.test', 'password' => 'salah'])
                ->assertStatus(422);
        }

        $this->postJson('/api/login', ['email' => 'petugas@contoh.test', 'password' => 'salah'])
            ->assertStatus(429);
    }
}