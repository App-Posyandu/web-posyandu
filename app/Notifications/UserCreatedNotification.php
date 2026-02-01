<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification
{
    use Queueable;

    public $userData;
    public $plainPassword;
    public $createdBy;
    public function __construct($userData, $plainPassword, $createdBy)
    {
        $this->userData = $userData;
        $this->plainPassword = $plainPassword;
        $this->createdBy = $createdBy;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $roleNames = [
            'admin' => 'Administrator',
            'kabid' => 'Kepala Bidang',
            'ketua-posyandu' => 'Ketua Posyandu',
            'kader' => 'Kader',
            'masyarakat' => 'Masyarakat'
        ];

        return [
            'title' => 'Akun Anda Telah Dibuat',
            'message' => sprintf(
                'Selamat datang! Akun Anda sebagai %s telah dibuat oleh %s.',
                $roleNames[$this->userData['role']] ?? $this->userData['role'],
                $this->createdBy
            ),
            'type' => 'user_created',
            'user_id' => $this->userData['id'],
            'role' => $this->userData['role'],
        ];
    }


    public function toArray(object $notifiable): array
    {
        return [];
    }
}
