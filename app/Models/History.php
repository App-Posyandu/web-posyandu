<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $table = 'pengajuan_histories';
    public $timestamps = false;

    protected $fillable = ['pengajuan_id', 'status', 'catatan', 'diubah_oleh', 'created_at'];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
