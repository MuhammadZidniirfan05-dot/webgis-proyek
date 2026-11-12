<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
    }

    private function dataBaru(array $ubah = []): array
    {
        return $ubah + [
            'name' => 'Petugas Baru',
            'email' => 'baru@contoh.test',
            'role' => 'petugas_lapangan',
            'password' => 'PasswordKuat123',
            'password_confirmation' => 'PasswordKuat123',
        ];
    }

    public function test_hanya_admin_boleh_membuka_halaman_pengguna(): void
    {
        $this->actingAs($this->akun('admin'))->get(route('users.index'))->assertOk();

        foreach (['manajer_proyek', 'petugas_lapangan', 'klien'] as $role) {
            $this->actingAs($this->akun($role))->get(route('users.index'))->assertForbidden();
            $this->actingAs($this->akun($role))->post(route('users.store'), $this->dataBaru())->assertForbidden();
        }

        $this->assertDatabaseMissing('users', ['email' => 'baru@contoh.test']);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('users.index'))->assertRedirect('/login');
    }

    public function test_admin_menambah_pengguna_dengan_role_dan_password_ter_hash(): void
    {
        $this->actingAs($this->akun('admin'))
            ->post(route('users.store'), $this->dataBaru())
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'baru@contoh.test')->firstOrFail();
        $this->assertTrue($user->hasRole('petugas_lapangan'));
        $this->assertNotSame('PasswordKuat123', $user->password);
        $this->assertTrue(Hash::check('PasswordKuat123', $user->password));
    }

    public function test_validasi_email_ganda_role_password_dan_huruf_besar(): void
    {
        $admin = $this->akun('admin');

        $this->actingAs($admin)->post(route('users.store'), $this->dataBaru(['email' => 'admin@contoh.test']))
            ->assertSessionHasErrors('email');
        $this->actingAs($admin)->post(route('users.store'), $this->dataBaru(['email' => 'Baru@Contoh.Test']))
            ->assertSessionHasErrors('email');
        $this->actingAs($admin)->post(route('users.store'), $this->dataBaru(['role' => 'raja']))
            ->assertSessionHasErrors('role');
        $this->actingAs($admin)->post(route('users.store'), $this->dataBaru(['password' => 'abc', 'password_confirmation' => 'abc']))
            ->assertSessionHasErrors('password');
        $this->actingAs($admin)->post(route('users.store'), $this->dataBaru(['password_confirmation' => 'berbeda123']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'baru@contoh.test']);
    }

    public function test_ubah_nama_email_dan_role_tanpa_mengganti_password(): void
    {
        $klien = $this->akun('klien');

        $this->actingAs($this->akun('admin'))
            ->put(route('users.update', $klien), [
                'name' => 'Klien Baru', 'email' => 'klien.baru@contoh.test', 'role' => 'petugas_lapangan',
                'password' => '', 'password_confirmation' => '',
            ])
            ->assertRedirect(route('users.index'));

        $klien->refresh();
        $this->assertSame('Klien Baru', $klien->name);
        $this->assertSame('klien.baru@contoh.test', $klien->email);
        $this->assertSame(['petugas_lapangan'], $klien->getRoleNames()->all());
        $this->assertTrue(Hash::check('password', $klien->password));
    }

    public function test_email_sendiri_tidak_dianggap_ganda_saat_edit(): void
    {
        $klien = $this->akun('klien');

        $this->actingAs($this->akun('admin'))
            ->put(route('users.update', $klien), [
                'name' => $klien->name, 'email' => $klien->email, 'role' => 'klien',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_ganti_password_mencabut_token_api(): void
    {
        $klien = $this->akun('klien');
        $klien->createToken('perangkat');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->actingAs($this->akun('admin'))
            ->put(route('users.update', $klien), [
                'name' => $klien->name, 'email' => $klien->email, 'role' => 'klien',
                'password' => 'PasswordBaru123', 'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertTrue(Hash::check('PasswordBaru123', $klien->fresh()->password));
    }

    public function test_admin_tidak_bisa_menurunkan_role_dirinya_sendiri(): void
    {
        $admin = $this->akun('admin');

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => $admin->name, 'email' => $admin->email, 'role' => 'klien',
            ])
            ->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_admin_tidak_bisa_menghapus_dirinya_sendiri(): void
    {
        $admin = $this->akun('admin');

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_pengguna_dengan_laporan_tidak_bisa_dihapus(): void
    {
        $petugas = $this->akun('petugas_lapangan');
        $p = $this->buatProyek();
        DailyReport::create([
            'project_id' => $p->id, 'user_id' => $petugas->id,
            'report_date' => today(), 'progress_percent' => 10,
        ]);

        $this->actingAs($this->akun('admin'))
            ->delete(route('users.destroy', $petugas))
            ->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('users', ['id' => $petugas->id]);
        $this->assertSame(1, DailyReport::count());
    }

    public function test_manajer_proyek_tidak_bisa_dihapus_selama_masih_mengelola_proyek(): void
    {
        $manajer = $this->akun('manajer_proyek');
        $this->buatProyek(['manager_id' => $manajer->id]);

        $this->actingAs($this->akun('admin'))
            ->delete(route('users.destroy', $manajer))
            ->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('users', ['id' => $manajer->id]);
    }

    public function test_pengguna_tanpa_riwayat_bisa_dihapus(): void
    {
        $user = User::create(['name' => 'Sementara', 'email' => 'sementara@contoh.test', 'password' => 'PasswordKuat123']);
        $user->assignRole('klien');

        $this->actingAs($this->akun('admin'))
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_pencarian_dan_filter_role(): void
    {
        $admin = $this->akun('admin');

        $this->actingAs($admin)->get(route('users.index', ['role' => 'klien']))
            ->assertOk()
            ->assertSee('klien@contoh.test')
            ->assertDontSee('petugas@contoh.test');

        $this->actingAs($admin)->get(route('users.index', ['q' => 'petugas@']))
            ->assertOk()
            ->assertSee('petugas@contoh.test')
            ->assertDontSee('klien@contoh.test');

        $this->actingAs($admin)->get(route('users.index', ['role' => 'raja']))
            ->assertSessionHasErrors('role');
    }

    public function test_menu_pengguna_hanya_terlihat_oleh_admin(): void
    {
        $this->actingAs($this->akun('admin'))->get('/dashboard')
            ->assertSee(route('users.index'), false);

        $this->actingAs($this->akun('klien'))->get('/dashboard')
            ->assertDontSee(route('users.index'), false);
    }
}