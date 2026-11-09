<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_klik_role_langsung_login_bila_demo_aktif(): void
    {
        config(['app.show_demo_accounts' => true, 'app.demo_roles' => ['klien']]);

        $this->post(route('demo.login', 'klien'))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->akun('klien'));
    }

    public function test_role_di_luar_daftar_ditolak(): void
    {
        config(['app.show_demo_accounts' => true, 'app.demo_roles' => ['klien']]);

        $this->post(route('demo.login', 'petugas_lapangan'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_saklar_mati_menolak_semua(): void
    {
        config(['app.show_demo_accounts' => false]);

        $this->post(route('demo.login', 'klien'))->assertNotFound();
        $this->assertGuest();
    }
}