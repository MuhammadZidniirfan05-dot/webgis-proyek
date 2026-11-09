<?php

namespace Tests\Feature;

use App\Models\AdminAccessRequest;
use App\Notifications\AdminAccessRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
        config([
            'app.show_demo_accounts' => true,
            'app.admin_request_enabled' => true,
            'app.admin_approver_email' => 'pemilik@contoh.test',
        ]);
    }

    private function buatRequest(string $status = 'pending'): AdminAccessRequest
    {
        return AdminAccessRequest::create([
            'name' => 'Penilai', 'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
    }

    private function urlTinjau(AdminAccessRequest $r): string
    {
        return URL::temporarySignedRoute('admin-access.review', now()->addHour(), ['accessRequest' => $r->id]);
    }

    public function test_permintaan_mengirim_email_ke_pemilik(): void
    {
        Notification::fake();

        $this->post(route('admin-access.store'), ['name' => 'Penilai', 'reason' => 'Ingin mencoba'])
            ->assertRedirect(route('admin-access.waiting'));

        $this->assertDatabaseHas('admin_access_requests', ['name' => 'Penilai', 'status' => 'pending']);
        Notification::assertSentOnDemand(AdminAccessRequested::class);
    }

    public function test_login_sekali_klik_tidak_pernah_menerima_admin(): void
    {
        config(['app.demo_roles' => ['admin', 'klien']]);

        $this->post(route('demo.login', 'admin'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_tautan_tinjau_tanpa_tanda_tangan_ditolak(): void
    {
        $r = $this->buatRequest();

        $this->get(route('admin-access.review', $r))->assertForbidden();
        $this->post(route('admin-access.review', $r), ['decision' => 'approve'])->assertForbidden();
        $this->assertSame('pending', $r->fresh()->status);
    }

    public function test_pemilik_menyetujui_lalu_pengunjung_masuk_sebagai_admin(): void
    {
        $r = $this->buatRequest();

        $this->post($this->urlTinjau($r), ['decision' => 'approve'])->assertOk();
        $this->assertSame('approved', $r->fresh()->status);

        $this->withSession(['admin_request_id' => $r->id])
            ->post(route('admin-access.claim'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->akun('admin'));
        $this->assertSame('used', $r->fresh()->status);
    }

    public function test_klaim_hanya_bisa_sekali(): void
    {
        $r = $this->buatRequest('approved');

        $this->withSession(['admin_request_id' => $r->id])->post(route('admin-access.claim'));
        auth()->logout();

        $this->withSession(['admin_request_id' => $r->id])
            ->post(route('admin-access.claim'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_klaim_tanpa_persetujuan_atau_setelah_ditolak_gagal(): void
    {
        foreach (['pending', 'denied'] as $status) {
            $r = $this->buatRequest($status);

            $this->withSession(['admin_request_id' => $r->id])
                ->post(route('admin-access.claim'))
                ->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }

    public function test_persetujuan_kedaluwarsa_setelah_15_menit(): void
    {
        $r = $this->buatRequest('approved');
        $r->update(['approved_at' => now()->subMinutes(16)]);

        $this->withSession(['admin_request_id' => $r->id])
            ->post(route('admin-access.claim'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_fitur_mati_menolak_semua(): void
    {
        config(['app.admin_request_enabled' => false]);

        $this->get(route('admin-access.form'))->assertNotFound();
        $this->post(route('admin-access.store'), ['name' => 'X'])->assertNotFound();
    }
}