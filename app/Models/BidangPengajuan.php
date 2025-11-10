<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidangPengajuan extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'bidang_pengajuans';
    protected $fillable = ['nama_bidang', 'slug'];

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'bidang_id', 'id');
    }
    
    public function kaders()
    {
        return $this->hasMany(User::class, 'bidang_id', 'id')
            ->where('role', 'kader');
    }
}