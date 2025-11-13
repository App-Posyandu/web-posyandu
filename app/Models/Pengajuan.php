<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'ttd_kader'
    ];

    protected $casts = [
        'formulir_items' => 'array',
        'administrasi_items' => 'array',
        'sudah_verifikasi' => 'boolean',
        'kunjungan_lapangan' => 'boolean',
        'verified_formulir_items',
        'verified_administrasi_items',
    ];

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
