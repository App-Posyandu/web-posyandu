<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    protected $roleToCreate;
    protected $importingUser;
    protected $allowedPosyanduIds;

    /**
     * Constructor untuk menerima role yang akan dibuat dan user yang melakukan import
     */
    public function __construct($roleToCreate = null, $importingUser = null)
    {
        $this->importingUser = $importingUser ?? Auth::user();
        $this->roleToCreate = $roleToCreate;
        $this->allowedPosyanduIds = [];

        // Tentukan posyandu yang diizinkan berdasarkan role
        if ($this->importingUser) {
            $this->setAllowedPosyandus();
            $this->validateRoleTarget();
        }

        Log::info('UsersImport Initialized', [
            'role_to_create' => $this->roleToCreate,
            'importing_user_role' => $this->importingUser?->role,
            'allowed_posyandu_count' => count($this->allowedPosyanduIds)
        ]);
    }

    /**
     * Validasi role target sesuai role user yang melakukan import
     */
    private function validateRoleTarget(): void
    {
        $allowedRoles = $this->getAllowedRoleTargets($this->importingUser->role);

        if (!in_array($this->roleToCreate, $allowedRoles, true)) {
            Log::error('Role target tidak diizinkan untuk import', [
                'importing_user_role' => $this->importingUser->role,
                'role_to_create' => $this->roleToCreate,
                'allowed_roles' => $allowedRoles
            ]);

            throw new \RuntimeException('Role target tidak diizinkan untuk import user.');
        }
    }

    /**
     * Role target yang boleh dibuat berdasarkan role user
     */
    private function getAllowedRoleTargets(string $role): array
    {
        $roleMap = [
            'kader' => ['masyarakat'],
            'ketua-kader' => ['kader'],
            'operator-desa' => ['ketua-kader', 'kader'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'ketua-kader', 'operator-desa', 'kader', 'masyarakat'],
        ];

        return $roleMap[$role] ?? [];
    }

    /**
     * Tentukan posyandu mana saja yang diizinkan berdasarkan role user yang melakukan import
     */
    private function setAllowedPosyandus()
    {
        $currentUserRole = $this->importingUser->role;

        switch ($currentUserRole) {
            case 'kader':
                // Kader hanya bisa import ke posyandu yang dia pegang
                $this->allowedPosyanduIds = [$this->importingUser->posyandu_id];
                $this->roleToCreate = $this->roleToCreate ?? 'masyarakat';
                break;

            case 'ketua-kader':
                // Ketua-kader hanya bisa import ke posyandu yang dia pegang
                $this->allowedPosyanduIds = [$this->importingUser->posyandu_id];
                $this->roleToCreate = $this->roleToCreate ?? 'kader';
                break;

            case 'operator-desa':
                // Operator-desa bisa import ke semua posyandu di desa yang sama
                $desa = $this->importingUser->posyandu->desa;
                $kecamatan = $this->importingUser->posyandu->kecamatan;
                $this->allowedPosyanduIds = Posyandu::where('desa', $desa)
                    ->where('kecamatan', $kecamatan)
                    ->pluck('id')
                    ->toArray();
                $this->roleToCreate = $this->roleToCreate ?? 'ketua-kader';
                break;

            case 'admin-kecamatan':
                // Admin-kecamatan bisa import ke semua posyandu di kecamatan
                $this->allowedPosyanduIds = Posyandu::where('kecamatan_id', $this->importingUser->kecamatan_id)
                    ->pluck('id')
                    ->toArray();
                $this->roleToCreate = $this->roleToCreate ?? 'operator-desa';
                break;

            case 'kabid':
                // Kabid bisa import ke semua posyandu di kabupaten
                $this->allowedPosyanduIds = Posyandu::where('kabupaten_id', $this->importingUser->kabupaten_id)
                    ->pluck('id')
                    ->toArray();
                $this->roleToCreate = $this->roleToCreate ?? 'admin-kecamatan';
                break;

            case 'admin-kabupaten':
                // Admin-kabupaten bisa import ke semua posyandu di kabupaten
                $this->allowedPosyanduIds = Posyandu::where('kabupaten_id', $this->importingUser->kabupaten_id)
                    ->pluck('id')
                    ->toArray();
                $this->roleToCreate = $this->roleToCreate ?? 'ketua-kader';
                break;

            case 'admin':
                // Admin bisa import ke semua posyandu
                $this->allowedPosyanduIds = Posyandu::pluck('id')->toArray();
                $this->roleToCreate = $this->roleToCreate ?? 'ketua-kader';
                break;

            default:
                $this->roleToCreate = $this->roleToCreate ?? 'kader';
        }
    }

    public function headingRow(): int
    {
        return 6; // Header di baris 6
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row) || (count(array_filter($row, fn($v) => !empty($v))) === 0)) {
            return null;
        }

        Log::info('Excel Row Raw', ['row' => $row]);

        // Handle berbagai variasi nama kolom (dengan spasi, underscore, atau titik)
        $nama = $row['nama'] ?? $row['NAMA'] ?? null;
        $nomorTelepon = $row['nomor_telepon'] ?? $row['nomor telepon'] ?? $row['NOMOR TELEPON'] ?? $row['NOMOR_TELEPON'] ?? null;
        $desa = $row['desa'] ?? $row['DESA'] ?? null;
        $kecamatan = $row['kecamatan'] ?? $row['KECAMATAN'] ?? null;
        $kabupaten = $row['kabupaten'] ?? $row['KABUPATEN'] ?? 'KEBUMEN';

        // Trim dan normalize values
        // Convert ke string jika numeric, jangan langsung null
        $nama = !empty($nama) ? trim((string)$nama) : null;
        $nomorTelepon = !empty($nomorTelepon) ? trim((string)$nomorTelepon) : null;
        $desa = !empty($desa) ? trim((string)$desa) : null;
        $kecamatan = !empty($kecamatan) ? trim((string)$kecamatan) : null;
        $kabupaten = !empty($kabupaten) ? trim((string)$kabupaten) : 'KEBUMEN';

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
                'nomor_telepon' => $nomorTelepon,
                'raw_row' => $row
            ]);
            return null;
        }

        if (empty($desa)) {
            Log::warning("Row dilewati karena DESA kosong!", ['desa' => $desa]);
            return null;
        }

        // Case-insensitive search dengan normalize
        $desaUpper = strtoupper($desa);
        $kecamatanUpper = strtoupper($kecamatan);

        $posyandu = Posyandu::whereRaw('UPPER(desa) LIKE ?', ['%' . $desaUpper . '%'])
            ->whereRaw('UPPER(kecamatan) LIKE ?', ['%' . $kecamatanUpper . '%'])
            ->first();

        if (!$posyandu) {
            Log::warning("Row dilewati karena Posyandu tidak ditemukan!", [
                'desa' => $desa,
                'kecamatan' => $kecamatan,
                'desa_upper' => $desaUpper,
                'kecamatan_upper' => $kecamatanUpper
            ]);
            return null;
        }

        // Validasi: Posyandu harus berada dalam daftar posyandu yang diizinkan
        if (!in_array($posyandu->id, $this->allowedPosyanduIds)) {
            Log::warning("Row dilewati karena Posyandu tidak dalam scope akses user!", [
                'posyandu_id' => $posyandu->id,
                'posyandu_nama' => $posyandu->nama_posyandu,
                'allowed_posyandu_ids' => $this->allowedPosyanduIds
            ]);
            return null;
        }

        $existingUser = User::where('no_telepon', $nomorTelepon)->first();
        if ($existingUser) {
            Log::warning("Row dilewati karena nomor telepon sudah terdaftar!", [
                'nomor_telepon' => $nomorTelepon
            ]);
            return null;
        }

        $desaFormatted = strtoupper($desa);
        if (stripos($desaFormatted, 'DESA') === false && stripos($desaFormatted, 'KELURAHAN') === false) {
            $desaFormatted = 'DESA ' . $desaFormatted;
        }

        $kecamatanFormatted = strtoupper($kecamatan);
        if (stripos($kecamatanFormatted, 'KECAMATAN') === false) {
            $kecamatanFormatted = 'KECAMATAN ' . $kecamatanFormatted;
        }

        $userData = [
            'name' => $nama,
            'no_telepon' => $nomorTelepon,
            'posyandu_id' => $posyandu->id,
            'role' => $this->roleToCreate,
            'password' => Hash::make('password123'), // Default password
            'alamat' => $desaFormatted . ', ' . $kecamatanFormatted . ', ' . strtoupper($kabupaten),
            'verified_at' => now(), // Auto-verify
            'verified_by' => $this->importingUser->id // User yang melakukan import
        ];

        Log::info('User Data Ready', [
            'name' => $userData['name'],
            'role' => $userData['role'],
            'posyandu_id' => $userData['posyandu_id']
        ]);

        try {
            $user = User::create($userData);
            Log::info('User Created Successfully', [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ]);
            return $user;
        } catch (\Exception $e) {
            Log::error('Error Creating User', [
                'error' => $e->getMessage(),
                'user_data' => $userData
            ]);
            throw $e;
        }
    }}