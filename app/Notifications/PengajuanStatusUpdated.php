<?php

namespace App\Notifications;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanStatusUpdated extends Notification
{
    use Queueable;

    // Variabel untuk menyimpan data yang akan kita kirim
    public Pengajuan $ajuan;
    public string $statusHistory;
    public string $catatanHistory;

    /**
     * Buat instance notifikasi baru.
     */
    public function __construct(Pengajuan $ajuan, string $statusHistory, string $catatanHistory)
    {
        $this->ajuan = $ajuan;
        $this->statusHistory = $statusHistory;
        $this->catatanHistory = $catatanHistory;
    }

    /**
     * Tentukan channel pengiriman (kita pakai 'database')
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Akan disimpan di tabel 'notifications'
    }

    /**
     * Ubah notifikasi menjadi array untuk disimpan di database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ajuan_id' => $this->ajuan->id,
            'title' => 'Status Pengajuan Anda Diperbarui',
            'message' => "Pengajuan Anda untuk '{$this->ajuan->bidang->nama_bidang}' kini berstatus: {$this->statusHistory}.",
            'catatan' => $this->catatanHistory,
            'url' => route('ajuan.show', $this->ajuan->id), // Link saat notif diklik
        ];
    }
}
