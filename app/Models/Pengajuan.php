<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'tanggal_permohonan',
        'tindak_lanjut',
        // Workflow: Ketua Posyandu
        'approved_by_ketua',
        'approved_by_ketua_id',
        'approved_by_ketua_at',
        // Workflow: Desa (Kades)
        'submitted_to_desa',
        'submitted_to_desa_at',
        'approved_by_kades',
        'approved_by_kades_id',
        'approved_by_kades_at',
        'foto_kunjungan',
        'revision_requested_at',
        'revision_count',
        'auto_rejected',
        'tracking_code',
    ];

    protected $casts = [
        'formulir_items' => 'array',
        'administrasi_items' => 'array',
        'sudah_verifikasi' => 'boolean',
        'kunjungan_lapangan' => 'boolean',
        'verified_formulir_items' => 'array',
        'verified_administrasi_items' => 'array',
        'ttd_kader' => 'boolean',
        'foto_kunjungan' => 'array',
        // Workflow: Ketua Posyandu
        'approved_by_ketua' => 'boolean',
        'approved_by_ketua_at' => 'datetime',
        // Workflow: Desa (Kades)
        'submitted_to_desa' => 'boolean',
        'submitted_to_desa_at' => 'datetime',
        'approved_by_kades' => 'boolean',
        'approved_by_kades_at' => 'datetime',
        'tanggal_permohonan' => 'datetime',
        'revision_requested_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pengajuan) {
            if (empty($pengajuan->tracking_code)) {
                $pengajuan->tracking_code = self::generateTrackingCode();
            }
        });
    }

    private static function generateTrackingCode()
    {
        do {
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
        return $query->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status_pengajuan', ['Disetujui', 'Ditolak']);
    }

    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        if ($actor->role === 'admin' || $actor->role === 'ketua-timpembina-posyandu') {
            return $query;
        }

        $kabupatenScope = function (Builder $userQuery) use ($actor): void {
            if ($actor->kabupaten_id) {
                $userQuery->where(function (Builder $q) use ($actor) {
                    $q->where('kabupaten_id', $actor->kabupaten_id);
                    if ($actor->kabupaten) {
                        $q->orWhere('kabupaten', $actor->kabupaten);
                    }
                });
            } elseif ($actor->kabupaten) {
                $userQuery->where('kabupaten', $actor->kabupaten);
            } else {
                $userQuery->whereRaw('1 = 0');
            }
        };

        return match ($actor->role) {
            'admin-kabupaten' => $query->whereHas('user', $kabupatenScope),
            'kabid' => $actor->bidang_id
                ? $query->where('bidang_id', $actor->bidang_id)
                    ->whereHas('user', $kabupatenScope)
                : $query->whereRaw('1 = 0'),
            'admin-kecamatan' => $query->whereHas('user', function (Builder $userQuery) use ($actor) {
                if ($actor->kecamatan_id) {
                    $userQuery->where(function (Builder $q) use ($actor) {
                        $q->where('kecamatan_id', $actor->kecamatan_id);
                        if ($actor->kecamatan) {
                            $q->orWhere('kecamatan', $actor->kecamatan);
                        }
                    });
                } elseif ($actor->kecamatan) {
                    $userQuery->where('kecamatan', $actor->kecamatan);
                } else {
                    $userQuery->whereRaw('1 = 0');
                }
            }),
            'kades', 'bu-kades' => $query->whereHas('user', function (Builder $userQuery) use ($actor) {
                $userQuery->where('desa', $actor->desa);
            }),
            'operator-desa', 'ketua-posyandu' => $query->whereHas('user', function (Builder $userQuery) use ($actor) {
                $userQuery->where('posyandu_id', $actor->posyandu_id);
            }),
            'kader' => $actor->bidang_id
                ? $query->where('bidang_id', $actor->bidang_id)
                    ->whereHas('user', function (Builder $userQuery) use ($actor) {
                        $userQuery->where('posyandu_id', $actor->posyandu_id);
                    })
                : $query->whereRaw('1 = 0'),
            'masyarakat' => $query->where('user_id', $actor->id),
            default => $query->whereRaw('1 = 0'),
        };
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
