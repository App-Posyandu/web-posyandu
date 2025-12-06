<?php

namespace App\Imports;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PosyanduImport implements ToModel, WithHeadingRow
{
    public function headingRow(): int
    {
        return 6; 
    }

    public function model(array $row)
    {
        Log::info("Excel Posyandu Row Raw", ['row' => $row]);

        $posyandu = $row['nama_posyandu'] ?? null;
        $desa = $row['desa'] ?? null;
        $kecamatan = $row['kecamatan'] ?? null;
        $kabupaten = $row['kabupaten'] ?? null;

        Log::info("Row Parsed", [
            'nama_posyandu' => $posyandu,
            'desa' => $desa,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
        ]);

        if (empty($posyandu)) {
            Log::warning("Row dilewati (nama posyandu kosong)");
            return null;
        }

        $existing = Posyandu::where('nama_posyandu', $posyandu)
            ->where('desa', $desa)
            ->first();

        if ($existing) {
            Log::warning("Row dilewati (duplikat)", [
                'nama_posyandu' => $posyandu,
                'desa' => $desa
            ]);
            return null;
        }

        $data = [
            'nama_posyandu' => $posyandu,
            'desa' => $desa,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
        ];

        Log::info("Posyandu Created:", $data);

        return Posyandu::create($data);
    }
}
