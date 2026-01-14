<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class LaporanController extends Controller
{
    public function index()
    {
        return view('dashboard.partials.laporan');
    }
    //export data pengajuan ke excel
    public function exportExcelAll($desa)
    {
        // Export semua bidang tapi difilter berdasarkan desa
        return Excel::download(new UsersExport('all', $desa), 'Laporan_Data_Pengajuan_Recap_All_' . strtoupper($desa) . '.xlsx');
    }
    //export dari semmua data bidang dan semua data desa
    public function exportExcelAllBidangDanDesa()
    {
        return Excel::download(new UsersExport(), 'Laporan_Data_Pengajuan_Recap_All.xlsx');
    }


    public function exportExcelBidang($bidang, $desa)
    {
        // Export berdasarkan bidang tertentu dan desa
        return Excel::download(new UsersExport($bidang, $desa), 'Laporan_Data_Pengajuan_Recap_' . strtoupper($bidang) . '_' . strtoupper($desa) . '.xlsx');
    }
    public function exportBidangAllDesa($bidang)
    {
        return Excel::download(new UsersExport($bidang, 'all'), 'Laporan_Data_Pengajuan_Recap_' . strtoupper($bidang) . '_AllDesa' . '.xlsx');
    }
}
