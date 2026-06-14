<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Posyandu extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_posyandu',
        'kabupaten',
        'desa',
        'kecamatan',
        'kabupaten_id',
        'kecamatan_id',
        'rw_list',
        'rt_mapping'
    ];

    protected $casts = [
        'rw_list' => 'array',
        'rt_mapping' => 'array',
    ];

    public function isRwAllowed($rw)
    {
        if (!$this->rw_list) {
            return true;
        }

        return in_array($rw, $this->rw_list);
    }

    public function getRtListForRw($rw)
    {
        $mapping = $this->rt_mapping ?? [];
        return $mapping[$rw] ?? [];
    }

    public function isRtValidForRw($rw, $rt)
    {
        if (!$this->rt_mapping || !isset($this->rt_mapping[$rw])) {
            return true;
        }

        return in_array($rt, $this->rt_mapping[$rw]);
    }

    public function getAvailableRwList()
    {
        return count($this->rw_list ?? []);
    }

    public function getTotalRtCount()
    {
        if (!$this->rt_mapping) {
            return 0;
        }

        $total = 0;
        foreach ($this->rt_mapping as $rw => $rtList) {
            $total += count($rtList);
        }

        return $total;
    }

    public function validateRwRtConstraints()
    {
        $errors = [];

        if ($this->rw_list && count($this->rw_list) > 15) {
            $errors[] = 'Maksimal 15 RW per posyandu';
        }

        if ($this->getTotalRtCount() > 53) {
            $errors[] = 'Maksimal 53 RT per posyandu';
        }

        return empty($errors) ? true : $errors;
    }

    public function scopeByRw($query, string $rw)
    {
        return $query->whereJsonContains('rw_list', $rw);
    }

    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        if ($actor->role === 'admin' || $actor->role === 'ketua-timpembina-posyandu') {
            return $query;
        }

        return match ($actor->role) {
            'admin-kabupaten', 'kabid' => $query->where(function (Builder $q) use ($actor) {
                if ($actor->kabupaten_id) {
                    $q->where('kabupaten_id', $actor->kabupaten_id)
                      ->orWhere('kabupaten', $actor->kabupaten);
                } else {
                    $q->where('kabupaten', $actor->kabupaten);
                }
            }),
            'admin-kecamatan' => $query->where(function (Builder $q) use ($actor) {
                if ($actor->kecamatan_id) {
                    $q->where('kecamatan_id', $actor->kecamatan_id)
                      ->orWhere('kecamatan', $actor->kecamatan);
                } else {
                    $q->where('kecamatan', $actor->kecamatan);
                }
                // Batasi juga per kabupaten agar desa/kecamatan yang sama nama di kabupaten lain tidak ikut
                if ($actor->kabupaten_id) {
                    $q->where(function (Builder $inner) use ($actor) {
                        $inner->where('kabupaten_id', $actor->kabupaten_id)
                              ->orWhere('kabupaten', $actor->kabupaten);
                    });
                } elseif ($actor->kabupaten) {
                    $q->where('kabupaten', $actor->kabupaten);
                }
            }),
            'kades', 'bu-kades', 'operator-desa' => $query->where('desa', $actor->desa)
                ->where(function (Builder $q) use ($actor) {
                    if ($actor->kecamatan_id) {
                        $q->where('kecamatan_id', $actor->kecamatan_id)
                          ->orWhere('kecamatan', $actor->kecamatan);
                    } elseif ($actor->kecamatan) {
                        $q->where('kecamatan', $actor->kecamatan);
                    }
                }),
            'ketua-posyandu' => $query->where('id', $actor->posyandu_id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public function bidang()
    {
        return $this->belongsTo(BidangPengajuan::class, 'bidang_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'posyandu_id', 'id');
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'posyandu_id', 'id');
    }
}
