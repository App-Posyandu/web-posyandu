<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    /** @use HasFactory<\Database\Factories\PosyanduFactory> */
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['nama_posyandu', 'kabupaten', 'desa', 'kecamatan'];

    public function bidang()
    {
        return $this->belongsTo(BidangPengajuan::class, 'bidang_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'posyandu_id', 'id');
    }
}
