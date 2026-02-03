<?php

namespace App\Notifications;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanReadyForKetuaApproval extends Notification
{
    use Queueable;
    protected $pengajuan;
    public function __construct(Pengajuan $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pengajuan_id' => $this->pengajuan->id,
            'user_name' => $this->pengajuan->user->name,
            'bidang' => $this->pengajuan->bidang->nama_bidang,
            'message' => 'Pengajuan menunggu persetujuan Ketua Posyandu',
        ];
    }
}
