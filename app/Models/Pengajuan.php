<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengajuan extends Model
{
    use HasFactory, HasUuids;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $foreignKey = 'bidang_id';

    protected $fillable = [
        'user_id',
        'bidang_id',
        'deskripsi_pengajuan',
        'status_pengajuan',
        'formulir_items',
        'administrasi_items',
        'sudah_verifikasi',
        'kunjungan_lapangan',
        'verified_formulir_items',
        'verified_administrasi_items',
        'ttd_kader',
        'tanggal_permohonan',        // ← BARU
        'tindak_lanjut',             // ← BARU
        'approved_by_ketua',         // ← BARU
        'approved_by_ketua_id',      // ← BARU
        'approved_by_ketua_at',      // ← BARU
        'approved_by_kades',         // ← BARU
        'approved_by_kades_id',      // ← BARU
        'approved_by_kades_at',      // ← BARU
        'foto_kunjungan',            // ← BARU
        'revision_requested_at',     // ← BARU
        'revision_count',            // ← BARU
        'auto_rejected',
        'tracking_code',
    ];

    protected $casts = [
        'formulir_items' => 'array',
        'administrasi_items' => 'array',
        'sudah_verifikasi' => 'boolean',
        'kunjungan_lapangan' => 'boolean',
        'verified_formulir_items',
        'verified_administrasi_items',
        'ttd_kader' => 'boolean',
        'foto_kunjungan' => 'array',           // ← BARU
        'approved_by_ketua' => 'boolean',      // ← BARU
        'approved_by_kades' => 'boolean',      // ← BARU
        'tanggal_permohonan' => 'datetime',    // ← BARU
        'approved_by_ketua_at' => 'datetime',  // ← BARU
        'approved_by_kades_at' => 'datetime',  // ← BARU
        'revision_requested_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pengajuan) {
            // Jika tracking_code belum di-set, generate otomatis
            if (empty($pengajuan->tracking_code)) {
                $pengajuan->tracking_code = self::generateTrackingCode();
            }
        });
    }

    /**
     * Generate unique tracking code
     * Format: PGJ-YYYYMM-XXXXX
     */
    private static function generateTrackingCode()
    {
        do {
            // Format: PGJ-202501-AB123
            $code = 'PGJ-' . date('Ym') . '-' . strtoupper(Str::random(5));
        } while (self::where('tracking_code', $code)->exists());

        return $code;
    }

    public function ketuaPosyandu()
    {
        return $this->belongsTo(User::class, 'approved_by_ketua_id');
    }


    public function kades()
    {
        return $this->belongsTo(User::class, 'approved_by_kades_id');
    }

    public function isRevisionExpired()
    {
        if (!$this->revision_requested_at) return false;

        $workDays = $this->calculateWorkDays($this->revision_requested_at, now());
        return $workDays > 5;
    }

    private function calculateWorkDays($start, $end)
    {
        $workDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $workDays++;
            }
            $current->addDay();
        }

        return $workDays;
    }

    public function scopeArchived($query)
    {
        // Pengajuan dianggap arsip jika statusnya sudah final (Disetujui/Ditolak)
        return $query->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
    }

    public function scopeActive($query)
    {
        // Pengajuan masih aktif jika statusnya selain Disetujui atau Ditolak
        return $query->whereNotIn('status_pengajuan', ['Disetujui', 'Ditolak']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bidang()
    {
        return $this->belongsTo(BidangPengajuan::class, 'bidang_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(History::class, 'pengajuan_id', 'id');
    }

    public function latestHistory()
    {
        return $this->hasOne(History::class, 'pengajuan_id', 'id')->latest('created_at');
    }
}
