<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Project $project, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $kapan = $this->daysLeft === 0
            ? 'hari ini'
            : "{$this->daysLeft} hari lagi";

        return (new MailMessage)
            ->subject("Deadline proyek {$this->project->code}: {$kapan}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Proyek {$this->project->code} - {$this->project->name} berakhir {$kapan} ({$this->project->deadline->format('d M Y')}).")
            ->line("Progres saat ini: {$this->project->progress_percent}%.")
            ->action('Buka proyek', route('projects.show', $this->project))
            ->line('Email ini dikirim otomatis oleh sistem monitoring proyek.');
    }
}