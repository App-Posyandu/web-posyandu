<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    /** @use HasFactory<\Database\Factories\PosyanduFactory> */
    use HasFactory;

    protected $fillable = ['nama_posyandu', 'desa', 'kecamatan'];

    public function users()
    {
        return $this->hasMany(User::class, 'posyandu_id', 'id');
    }
}
