<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $primaryKey = 'id';
    protected $foreignKey = 'posyandu_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'ktp',
        'kk',
        'kabupaten',
        'kecamatan',
        'desa',
        'kabupaten_id',
        'kecamatan_id',
        'jenis_wilayah',

        'rt',
        'rw',

        'nik',
        'alamat',
        'no_telepon',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'verified_at',
        'verified_by',
        'posyandu_id',
        'bidang_id',
        'is_active',
        'deactivated_at',
        'deactivated_by',
        'deactivation_reason',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function validatePosyanduRw()
    {
        if ($this->posyandu && $this->rw) {
            return $this->posyandu->isRwAllowed($this->rw);
        }
        return true;
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'user_id');
    }

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'posyandu_id', 'id');
    }
    public function bukuSakus()
    {
        return $this->hasMany(BukuSaku::class, 'user_id');
    }

    public function bidang()
    {
        return $this->belongsTo(BidangPengajuan::class, 'bidang_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(UserHistory::class, 'user_id')->latest();
    }

    public function kabupatenRelation()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function kecamatanRelation()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function getWilayahLengkapAttribute()
    {
        $parts = [];

        if ($this->posyandu && $this->posyandu->desa) {
            $parts[] = $this->posyandu->desa;
        }

        if ($this->kecamatanRelation) {
            $parts[] = 'Kec. ' . $this->kecamatanRelation->nama_kecamatan;
        } elseif ($this->kecamatan) {
            $parts[] = 'Kec. ' . str_replace('KECAMATAN ', '', $this->kecamatan);
        }

        if ($this->kabupatenRelation) {
            $parts[] = $this->kabupatenRelation->nama_lengkap;
        } elseif ($this->kabupaten) {
            $parts[] = $this->kabupaten;
        }

        return implode(', ', $parts) ?: '-';
    }

    public function getKabupatenNameAttribute()
    {
        if ($this->kabupatenRelation) {
            return $this->kabupatenRelation->nama_lengkap;
        }
        return $this->kabupaten ?? '-';
    }

    public function getKecamatanNameAttribute()
    {
        if ($this->kecamatanRelation) {
            return $this->kecamatanRelation->nama_kecamatan;
        }
        return $this->kecamatan ? str_replace('KECAMATAN ', '', $this->kecamatan) : '-';
    }

    public function deactivatedBy()
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function setNikAttribute($value)
    {
        $this->attributes['nik'] = empty($value) ? null : $value;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }
}
