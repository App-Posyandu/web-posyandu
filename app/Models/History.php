<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory, HasUuids;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'histories';
    public $timestamps = false;

    protected $fillable = ['pengajuan_id', 'action_by_role', 'status', 'pilih_keputusan', 'catatan', 'diubah_oleh', 'created_at'];

    protected $casts = [
    'created_at' => 'datetime',
];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'id');
    }

    public function diubahOleh()
    {
        return $this->belongsTo(User::class, 'diubah_oleh', 'id');
    }
}
