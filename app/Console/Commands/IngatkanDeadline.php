<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Notifications\DeadlineReminder;
use Illuminate\Console\Command;

class IngatkanDeadline extends Command
{
    protected $signature = 'proyek:ingatkan-deadline';

    protected $description = 'Kirim email pengingat deadline (H-7, H-3, H-1, hari H) ke manajer proyek';

    public function handle(): int
    {
        $hitung = 0;

        foreach ([7, 3, 1, 0] as $hari) {
            $tanggal = today()->addDays($hari)->toDateString();

            $projects = Project::with('manager')
                ->whereDate('deadline', $tanggal)
                ->where('status', '!=', 'selesai')
                ->whereNotNull('manager_id')
                ->get();

            foreach ($projects as $project) {
                $project->manager->notify(new DeadlineReminder($project, $hari));
                $hitung++;
            }
        }

        $this->info("{$hitung} pengingat dimasukkan ke antrean.");

        return self::SUCCESS;
    }
}