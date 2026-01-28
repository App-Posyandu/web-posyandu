<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_posyandu',
        'kabupaten',
        'desa',
        'kecamatan',
        'kabupaten_id',
        'kecamatan_id',
        'rw_list',
        'rt_mapping'
    ];

    protected $casts = [
        'rw_list' => 'array',
        'rt_mapping' => 'array',
    ];

    /**
     * ✅ Validasi apakah RW diperbolehkan di posyandu ini
     */
    public function isRwAllowed($rw)
    {
        if (!$this->rw_list) {
            return true; // Jika belum diset, allow semua
        }

        return in_array($rw, $this->rw_list);
    }

    /**
     * ✅ Get list RT untuk RW tertentu
     */
    public function getRtListForRw($rw)
    {
        $mapping = $this->rt_mapping ?? [];
        return $mapping[$rw] ?? [];
    }

    /**
     * ✅ Validasi apakah RT valid untuk RW yang dipilih
     */
    public function isRtValidForRw($rw, $rt)
    {
        if (!$this->rt_mapping || !isset($this->rt_mapping[$rw])) {
            return true; // Jika belum diset, allow
        }

        return in_array($rt, $this->rt_mapping[$rw]);
    }

    /**
     * ✅ Get semua RW yang tersedia (max 15)
     */
    public function getAvailableRwList()
    {
        return count($this->rw_list ?? []);
    }

    /**
     * ✅ Count total RT di posyandu ini (max 53)
     */
    public function getTotalRtCount()
    {
        if (!$this->rt_mapping) {
            return 0;
        }

        $total = 0;
        foreach ($this->rt_mapping as $rw => $rtList) {
            $total += count($rtList);
        }

        return $total;
    }

    /**
     * ✅ Validasi constraint: max 15 RW, max 53 RT
     */
    public function validateRwRtConstraints()
    {
        $errors = [];

        if ($this->rw_list && count($this->rw_list) > 15) {
            $errors[] = 'Maksimal 15 RW per posyandu';
        }

        if ($this->getTotalRtCount() > 53) {
            $errors[] = 'Maksimal 53 RT per posyandu';
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * ✅ Scope: Filter user berdasarkan RW
     */
    public function scopeByRw($query, string $rw)
    {
        return $query->whereJsonContains('rw_list', $rw);
    }

    // Relations
    public function bidang()
    {
        return $this->belongsTo(BidangPengajuan::class, 'bidang_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'posyandu_id', 'id');
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'posyandu_id', 'id');
    }
}
