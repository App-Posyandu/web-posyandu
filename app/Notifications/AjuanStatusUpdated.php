<?php

namespace App\Notifications;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AjuanStatusUpdated extends Notification
{
    use Queueable;

    protected $ajuan;
    protected $status;
    protected $catatan;
    public function __construct(Pengajuan $ajuan, $status, $catatan)
    {
        $this->ajuan = $ajuan;
        $this->status = $status;
        $this->catatan = $catatan;
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
        $message = "Status pengajuan Anda telah diubah menjadi '{$this->status}'.";

        if (!empty($this->catatan)) {
            $message .= " Catatan dari Kader: \"{$this->catatan}\"";
        }

        return [
            'title' => 'Update Status Pengajuan',
            'message' => $message,
            'ajuan_id' => $this->ajuan->id,
            'url' => route('ajuan.show', $this->ajuan->id),
        ];
    }
}
