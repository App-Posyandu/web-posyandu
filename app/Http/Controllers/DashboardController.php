<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userRole = Auth::user()->role;

        // JIKA user adalah 'masyarakat', siapkan data untuk halaman "Pilih Layanan"
        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
        $colors = [
            '#4D73FD',
            '#EC4899',
            '#F2993F',
            '#7CD75A',
            '#E655A0',
            '#EAB308',
        ];
        if ($userRole === 'masyarakat') {

            // Kirim data ke view yang sama, yaitu 'dashboard'
            return view('dashboard', [
                'allBidangs' => $allBidangs,
                'colors' => $colors,
            ]);
        } else {
            $query = Pengajuan::with(['user', 'bidang']);

            // Jika ada input 'search' dari URL (misal: ?search=pendidikan)
            if ($request->has('search') && $request->input('search') != '') {
                $searchTerm = $request->input('search');

                // Lakukan pencarian di beberapa kolom
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                        ->orWhere('status', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhereHas('bidang', function ($bidangQuery) use ($searchTerm) {
                            $bidangQuery->where('nama_bidang', 'like', '%' . $searchTerm . '%');
                        });
                });
            }
            // Ambil data untuk kartu statistik dan pie chart
            $ajuanCounts = Pengajuan::query()
                ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
                ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'))
                ->groupBy('bidang_pengajuans.nama_bidang')
                ->pluck('total', 'nama_bidang');

            // Ambil data untuk tabel list pengajuan
            $semuaAjuan = Pengajuan::with(['user', 'bidang'])->latest()->paginate(5);
            $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
            // Kirim data ke view yang sama, yaitu 'dashboard'
            return view('dashboard', [
                'ajuanCounts' => $ajuanCounts,
                'semuaAjuan' => $semuaAjuan,
                'colors' => $colors
            ]);
        }
    }
}
