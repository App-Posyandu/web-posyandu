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

    /**
     * Kode bidang 2 huruf untuk username kader (mis. kesehatan -> KE).
     */
    public function getKodeAttribute(): string
    {
        $map = [
            'perumahan-rakyat' => 'PR',
            'sosial'           => 'SO',
            'pendidikan'       => 'PE',
            'pekerjaan-umum'   => 'PU',
            'kesehatan'        => 'KE',
            'trantibumlinmas'  => 'TR',
        ];

        $slug = $this->slug
            ?: \Illuminate\Support\Str::slug(str_ireplace('Bidang', '', (string) $this->nama_bidang));

        if (isset($map[$slug])) {
            return $map[$slug];
        }

        // Fallback: 2 huruf pertama dari slug (huruf saja), uppercase.
        $letters = preg_replace('/[^a-zA-Z]/', '', $slug);
        return strtoupper(substr($letters, 0, 2)) ?: 'XX';
    }

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
