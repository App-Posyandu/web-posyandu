<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 2. Ambil semua bidang dari database, urutkan berdasarkan nama
        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        // 3. Siapkan array warna Tailwind CSS untuk tombol-tombol
        $colors = [
            'bg-blue-500 hover:bg-blue-600',
            'bg-pink-500 hover:bg-pink-600',
            'bg-orange-500 hover:bg-orange-600',
            'bg-green-500 hover:bg-green-600',
            'bg-fuchsia-500 hover:bg-fuchsia-600',
            'bg-yellow-500 hover:bg-yellow-600',
        ];

        // 4. Kirim kedua data (bidang dan warna) ke view
        return view('dashboard', [
            'allBidangs' => $allBidangs,
            'colors' => $colors,
        ]);
    }
}
