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
    public function exportExcelAll()
    {
        //code to export data to excel
        return Excel::download(new UsersExport, 'Laporan Data Pengajuan_Recap_All.xlsx');
    }
    public function exportExcelBidang($bidang)
{
    return Excel::download(new UsersExport($bidang), 'Laporan Data Pengajuan_Recap_' . $bidang . '.xlsx');
}
}
