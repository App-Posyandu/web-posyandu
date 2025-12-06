<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function headingRow(): int
    {
        return 6; // Header di baris 6
    }

    public function model(array $row)
    {
        Log::info('Excel Row Raw', ['row' => $row]);

        // Ambil data dari Excel
        $nama = $row['nama'] ?? null;
        $nomorTelepon = $row['nomor_telepon'] ?? null;
        $desa = $row['desa'] ?? null;
        $kecamatan = $row['kecamatan'] ?? null;
        $kabupaten = $row['kabupaten'] ?? null;

        Log::info('Excel Parsed', [
            'nama' => $nama,
            'nomor_telepon' => $nomorTelepon,
            'desa' => $desa,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten
        ]);

        // Validasi: Nama dan Nomor Telepon wajib diisi
        if (empty($nama) || empty($nomorTelepon)) {
            Log::warning("Row dilewati karena NAMA atau NOMOR TELEPON kosong!", [
                'nama' => $nama,
                'nomor_telepon' => $nomorTelepon
            ]);
            return null;
        }

        // Validasi: Desa harus ada (dari template auto-fill)
        if (empty($desa)) {
            Log::warning("Row dilewati karena DESA kosong!", ['desa' => $desa]);
            return null;
        }

        // Cari posyandu berdasarkan desa dan kecamatan
        $posyandu = Posyandu::where('desa', 'LIKE', '%' . $desa . '%')
            ->where('kecamatan', 'LIKE', '%' . $kecamatan . '%')
            ->first();

        if (!$posyandu) {
            Log::warning("Row dilewati karena Posyandu tidak ditemukan!", [
                'desa' => $desa,
                'kecamatan' => $kecamatan
            ]);
            return null;
        }

        // Cek apakah user dengan nomor telepon sudah ada
        $existingUser = User::where('no_telepon', $nomorTelepon)->first();
        if ($existingUser) {
            Log::warning("Row dilewati karena nomor telepon sudah terdaftar!", [
                'nomor_telepon' => $nomorTelepon
            ]);
            return null;
        }

        // Format nama desa dan kecamatan
        $desaFormatted = strtoupper($desa);
        if (stripos($desaFormatted, 'DESA') === false && stripos($desaFormatted, 'KELURAHAN') === false) {
            $desaFormatted = 'DESA ' . $desaFormatted;
        }

        $kecamatanFormatted = strtoupper($kecamatan);
        if (stripos($kecamatanFormatted, 'KECAMATAN') === false) {
            $kecamatanFormatted = 'KECAMATAN ' . $kecamatanFormatted;
        }

        // Data user yang akan disimpan
        $userData = [
            'name' => $nama,
            'no_telepon' => $nomorTelepon,
            'posyandu_id' => $posyandu->id,
            'role' => 'ketua-kader',
            'password' => Hash::make('password123'), // Default password
            'alamat' => $desaFormatted . ', ' . $kecamatanFormatted . ', ' . strtoupper($kabupaten),
            'verified_at' => now(), // Auto-verify
            'verified_by' => auth()->id() // User yang melakukan import
        ];

        Log::info('User Data Ready', $userData);

        return User::create($userData);
    }
}