<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_kabupaten',
        'jenis',
    ];

    public function kecamatans()
    {
        return $this->hasMany(Kecamatan::class);
    }

    public function getNamaLengkapAttribute()
    {
        return ucfirst($this->jenis) . ' ' . $this->nama_kabupaten;
    }
}
