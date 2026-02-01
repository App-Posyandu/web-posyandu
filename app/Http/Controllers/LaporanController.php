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

    private function authorizeExport($bidang = null, $desa = null)
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'ketua-posyandu', 'admin-kabupaten'])) {
            return true;
        }

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

        if ($user->role === 'admin-kecamatan') {
            if ($desa !== 'all' && $desa) {
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

        if ($user->role === 'kades') {
            if ($desa && $desa !== 'all' && $desa !== $user->desa) {
                abort(403, 'Anda hanya dapat export data desa: ' . $user->desa);
            }
            if (!$desa || $desa === 'all') {
                $desa = $user->desa;
            }
            return true;
        }

        if ($user->role === 'ketua-kader') {
            $userPosyandu = $user->posyandu;
            if (!$userPosyandu) {
                abort(403, 'Data posyandu tidak ditemukan.');
            }
            if ($desa && $desa !== 'all' && $desa !== $userPosyandu->desa) {
                abort(403, 'Anda hanya dapat export data posyandu Anda di desa: ' . $userPosyandu->desa);
            }
            return true;
        }

        abort(403, 'Anda tidak memiliki izin untuk export data.');
    }

    public function exportExcelAll($desa)
    {
        $this->authorizeExport(null, $desa);
        
        $user = Auth::user();
        
        if ($user->role === 'kades') {
            $desa = $user->desa;
        }

        return Excel::download(new UsersExport('all', $desa), 'Laporan_Data_Pengajuan_Recap_All_' . strtoupper($desa) . '.xlsx');
    }

    public function exportExcelAllBidangDanDesa()
    {
        $this->authorizeExport();

        return Excel::download(new UsersExport(), 'Laporan_Data_Pengajuan_Recap_All.xlsx');
    }

    public function exportExcelBidang($bidang, $desa)
    {
        $this->authorizeExport($bidang, $desa);

        $user = Auth::user();

        if ($user->role === 'ketua-kader') {
            $desa = $user->posyandu->desa;
        }
        
        if ($user->role === 'kades') {
            $desa = $user->desa;
        }

        return Excel::download(
            new UsersExport($bidang, $desa),
            'Laporan_Data_Pengajuan_Recap_' . strtoupper($bidang) . '_' . strtoupper($desa) . '.xlsx'
        );
    }

    public function exportBidangAllDesa($bidang)
    {
        $this->authorizeExport($bidang);

        $user = Auth::user();

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
