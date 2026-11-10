<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class NavigasiTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    public function test_admin_melihat_menu_audit_log_dan_wilayah(): void
    {
        $this->actingAs($this->akun('admin'))->get('/dashboard')
            ->assertOk()
            ->assertSee(route('map.index'), false)
            ->assertSee(route('projects.index'), false)
            ->assertSee(route('audit.index'), false)
            ->assertSee(route('regions.index'), false);
    }

    public function test_role_lain_tidak_melihat_menu_audit_log_dan_wilayah(): void
    {
        foreach (['manajer_proyek', 'petugas_lapangan', 'klien'] as $role) {
            $this->actingAs($this->akun($role))->get('/dashboard')
                ->assertOk()
                ->assertSee(route('map.index'), false)
                ->assertDontSee(route('audit.index'), false)
                ->assertDontSee(route('regions.index'), false);
        }
    }
}