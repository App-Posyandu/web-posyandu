<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        return view('dashboard.partials.laporan');
    }

    /**
     * ✅ Authorization check untuk export
     * Memvalidasi bahwa user hanya bisa export sesuai dengan scope mereka
     */
    private function authorizeExport($bidang = null, $desa = null)
    {
        $user = Auth::user();

        // ✅ Roles yang bisa akses semua data
        if (in_array($user->role, ['admin', 'ketua-posyandu', 'admin-kabupaten'])) {
            return true;
        }

        // ✅ KABID: Hanya bisa export bidangnya saja (untuk semua desa & kecamatan)
        if ($user->role === 'kabid') {
            if ($bidang === 'all' || !$bidang) {
                abort(403, 'KABID hanya bisa export untuk bidang tertentu, bukan semua bidang.');
            }
            
            $userBidang = $user->bidang?->nama_bidang;
            if (!$userBidang || $bidang !== $userBidang) {
                abort(403, 'Anda hanya dapat export bidang: ' . $userBidang);
            }
            return true;
        }

        // ✅ ADMIN KECAMATAN: Hanya bisa export untuk desa di kecamatan mereka (semua bidang)
        if ($user->role === 'admin-kecamatan') {
            if ($desa !== 'all' && $desa) {
                // Validasi bahwa desa tersebut ada di kecamatan user
                $desaBelongsToKecamatan = \DB::table('desa')
                    ->where('nama_desa', $desa)
                    ->where('nama_kecamatan', $user->kecamatan)
                    ->exists();

                if (!$desaBelongsToKecamatan) {
                    abort(403, 'Desa tersebut tidak termasuk dalam kecamatan Anda.');
                }
            }
            return true;
        }

        // ✅ KADES: Hanya bisa export untuk desanya sendiri (semua bidang)
        if ($user->role === 'kades') {
            if ($desa && $desa !== 'all' && $desa !== $user->desa) {
                abort(403, 'Anda hanya dapat export data desa: ' . $user->desa);
            }
            // Jika user kades, force desa filter
            if (!$desa || $desa === 'all') {
                $desa = $user->desa;
            }
            return true;
        }

        // ✅ KETUA KADER: Hanya bisa export posyandu-nya sendiri
        if ($user->role === 'ketua-kader') {
            $userPosyandu = $user->posyandu;
            if (!$userPosyandu) {
                abort(403, 'Data posyandu tidak ditemukan.');
            }
            // Validasi bahwa desa sesuai dengan posyandu-nya
            if ($desa && $desa !== 'all' && $desa !== $userPosyandu->desa) {
                abort(403, 'Anda hanya dapat export data posyandu Anda di desa: ' . $userPosyandu->desa);
            }
            return true;
        }

        abort(403, 'Anda tidak memiliki izin untuk export data.');
    }

    //export data pengajuan ke excel
    public function exportExcelAll($desa)
    {
        // ✅ Authorization untuk KADES dan ADMIN KECAMATAN
        $this->authorizeExport(null, $desa);
        
        $user = Auth::user();
        
        // ✅ KADES: Force export untuk desa mereka saja
        if ($user->role === 'kades') {
            $desa = $user->desa;
        }

        // Export semua bidang tapi difilter berdasarkan desa
        return Excel::download(new UsersExport('all', $desa), 'Laporan_Data_Pengajuan_Recap_All_' . strtoupper($desa) . '.xlsx');
    }

    //export dari semmua data bidang dan semua data desa
    public function exportExcelAllBidangDanDesa()
    {
        // ✅ Authorization: Hanya admin, ketua-posyandu, admin-kabupaten
        $this->authorizeExport();

        return Excel::download(new UsersExport(), 'Laporan_Data_Pengajuan_Recap_All.xlsx');
    }

    /**
     * ✅ Export berdasarkan bidang tertentu dan desa tertentu
     * GET /admin/export/{bidang}/{desa}
     */
    public function exportExcelBidang($bidang, $desa)
    {
        // ✅ Authorization check
        $this->authorizeExport($bidang, $desa);

        $user = Auth::user();

        // ✅ KETUA KADER: Force desa dari posyandu mereka
        if ($user->role === 'ketua-kader') {
            $desa = $user->posyandu->desa;
        }
        
        // ✅ KADES: Force desa dari profile mereka
        if ($user->role === 'kades') {
            $desa = $user->desa;
        }

        // Export berdasarkan bidang tertentu dan desa
        return Excel::download(
            new UsersExport($bidang, $desa),
            'Laporan_Data_Pengajuan_Recap_' . strtoupper($bidang) . '_' . strtoupper($desa) . '.xlsx'
        );
    }

    /**
     * ✅ Export berdasarkan bidang tertentu untuk semua desa
     * GET /admin/export/{bidang}
     */
    public function exportBidangAllDesa($bidang)
    {
        // ✅ Authorization check
        $this->authorizeExport($bidang);

        $user = Auth::user();

        // ✅ KABID: Hanya bisa export bidangnya
        if ($user->role === 'kabid') {
            $userBidang = $user->bidang?->nama_bidang;
            if ($bidang !== $userBidang) {
                abort(403, 'Anda hanya dapat export bidang: ' . $userBidang);
            }
        }

        return Excel::download(
            new UsersExport($bidang, 'all'),
            'Laporan_Data_Pengajuan_Recap_' . strtoupper($bidang) . '_AllDesa' . '.xlsx'
        );
    }
}
