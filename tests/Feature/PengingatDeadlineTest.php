<?php

namespace Tests\Feature;

use App\Notifications\DeadlineReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\SiapkanData;
use Tests\TestCase;

class PengingatDeadlineTest extends TestCase
{
    use RefreshDatabase, SiapkanData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpData();
        Notification::fake();
    }

    public function test_pengingat_dikirim_pada_h7_h3_h1_dan_hari_h(): void
    {
        foreach ([7, 3, 1, 0] as $hari) {
            $this->buatProyek(['deadline' => today()->addDays($hari)]);
        }

        $this->artisan('proyek:ingatkan-deadline')->assertSuccessful();

        Notification::assertSentToTimes($this->akun('manajer_proyek'), DeadlineReminder::class, 4);
    }

    public function test_tanggal_di_luar_jadwal_tidak_dikirimi(): void
    {
        $this->buatProyek(['deadline' => today()->addDays(5)]);
        $this->buatProyek(['deadline' => today()->subDay()]);

        $this->artisan('proyek:ingatkan-deadline')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_proyek_selesai_atau_tanpa_manajer_tidak_dikirimi(): void
    {
        $this->buatProyek(['status' => 'selesai', 'deadline' => today()->addDays(3)]);
        $this->buatProyek(['manager_id' => null, 'deadline' => today()->addDays(3)]);

        $this->artisan('proyek:ingatkan-deadline')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_isi_pengingat_memuat_sisa_hari(): void
    {
        $this->buatProyek(['deadline' => today()->addDays(3)]);

        $this->artisan('proyek:ingatkan-deadline');

        Notification::assertSentTo(
            $this->akun('manajer_proyek'),
            DeadlineReminder::class,
            fn ($n) => $n->daysLeft === 3
        );
    }
}