<?php

namespace App\Notifications;

use App\Models\AdminAccessRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAccessRequested extends Notification
{
    public function __construct(public AdminAccessRequest $request, public string $reviewUrl) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Permintaan akses Admin: ' . $this->request->name)
            ->greeting('Ada permintaan akses Admin')
            ->line("Nama: {$this->request->name}")
            ->line('Alasan: ' . ($this->request->reason ?: '-'))
            ->line("IP: {$this->request->ip_address}")
            ->line('Waktu: ' . $this->request->created_at->format('d M Y H:i'))
            ->action('Tinjau permintaan', $this->reviewUrl)
            ->line('Tautan berlaku 60 menit. Jika Anda tidak mengenal permintaan ini, abaikan atau tolak.');
    }
}