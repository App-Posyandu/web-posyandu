<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosyanduLookupResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'nama_posyandu' => $this->nama_posyandu,
            'desa' => $this->when(!is_null($this->desa), $this->desa),
            'kecamatan' => $this->when(!is_null($this->kecamatan), $this->kecamatan),
            'kecamatan_id' => $this->when(!is_null($this->kecamatan_id), $this->kecamatan_id),
            'kabupaten' => $this->when(!is_null($this->kabupaten), $this->kabupaten),
            'kabupaten_id' => $this->when(!is_null($this->kabupaten_id), $this->kabupaten_id),
            'rw_list' => $this->when(isset($this->rw_list), $this->rw_list ?? []),
            'rt_mapping' => $this->when(isset($this->rt_mapping), $this->rt_mapping ?? []),
        ];
    }
}
