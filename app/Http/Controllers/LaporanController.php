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
    public function exportExcel()
    {
        //code to export data to excel
        return Excel::download(new UsersExport, 'laporan_pengajuan.xlsx');
    }
}
