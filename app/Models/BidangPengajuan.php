<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidangPengajuan extends Model
{
    use HasFactory;
    protected $fillable = ['nama_bidang', 'slug'];
    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }
}
