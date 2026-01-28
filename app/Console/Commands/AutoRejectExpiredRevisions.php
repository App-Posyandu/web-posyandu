<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pengajuan;
use App\Models\History;
use Carbon\Carbon;

class AutoRejectExpiredRevisions extends Command
{
    protected $signature = 'revisions:auto-reject {--force : Force reject all pending revisions}';
    protected $description = 'Auto-reject pengajuan yang masa revisinya sudah habis';

    public function handle()
    {
        $now = now();
        $forceMode = $this->option('force');

        // ✅ Ambil config durasi dari config file
        $debugMode = config('revision.debug_mode', false);
        $debugMinutes = config('revision.deadline.debug_minutes');
        $productionDays = config('revision.deadline.days', 5);

        if ($forceMode) {
            $this->warn("⚠️  FORCE MODE ENABLED - Will reject ALL pending revisions!");
        }

        if ($debugMode && $debugMinutes) {
            $this->warn("🐛 DEBUG MODE: Using {$debugMinutes} minutes deadline");
        } else {
            $this->info("✅ PRODUCTION MODE: Using {$productionDays} days deadline");
        }

        $expiredPengajuans = Pengajuan::where('status_pengajuan', 'Diproses')
            ->whereNotNull('revision_requested_at')
            ->get()
            ->filter(function ($ajuan) use ($now, $forceMode, $debugMode, $debugMinutes, $productionDays) {
                // ✅ Calculate deadline based on config
                if ($debugMode && $debugMinutes) {
                    $deadline = Carbon::parse($ajuan->revision_requested_at)->addMinutes($debugMinutes);
                } else {
                    $deadline = Carbon::parse($ajuan->revision_requested_at)->addDays($productionDays);
                }

                $this->line("📋 Pengajuan #{$ajuan->id} - {$ajuan->user->name}:");
                $this->line("   Request: {$ajuan->revision_requested_at}");
                $this->line("   Deadline: {$deadline}");
                $this->line("   Now: {$now}");

                $isExpired = $now->greaterThan($deadline);
                $this->line("   Expired: " . ($isExpired ? 'YES ✅' : 'NO ❌'));

                $hasBeenRevised = $ajuan->histories()
                    ->where('status', 'Direvisi & Diajukan Kembali')
                    ->where('action_by_role', 'masyarakat')
                    ->where('created_at', '>', $ajuan->revision_requested_at)
                    ->exists();

                $this->line("   Revised: " . ($hasBeenRevised ? 'YES' : 'NO'));

                if ($forceMode) {
                    $shouldReject = !$hasBeenRevised;
                    $this->line("   Action: " . ($shouldReject ? 'WILL REJECT 🔴' : 'SKIP (already revised)'));
                } else {
                    $shouldReject = !$hasBeenRevised && $isExpired;
                    $this->line("   Action: " . ($shouldReject ? 'WILL REJECT 🔴' : 'SKIP'));
                }

                $this->line("");
                return $shouldReject;
            });

        $count = 0;

        foreach ($expiredPengajuans as $ajuan) {
            $ajuan->update([
                'status_pengajuan' => 'Ditolak',
                'sudah_verifikasi' => false,
                'kunjungan_lapangan' => false,
                'approved_by_ketua' => false,
            ]);

            History::create([
                'pengajuan_id' => $ajuan->id,
                'status' => 'Ditolak - Masa Revisi Habis',
                'catatan' => 'Pengajuan otomatis ditolak karena tidak direvisi dalam waktu yang ditentukan.',
                'diubah_oleh' => null,
                'action_by_role' => 'system',
                'created_at' => now(),
            ]);

            $count++;

            $this->info("✅ Rejected: Pengajuan #{$ajuan->id} - {$ajuan->user->name}");
        }

        if ($count > 0) {
            $this->info("🎯 Total auto-rejected: {$count} pengajuan");
        } else {
            $this->info("✨ Tidak ada pengajuan yang perlu di-reject");
        }

        return 0;
    }
}
