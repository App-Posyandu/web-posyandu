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
        $user = Auth::user();
        $isVerified = !is_null($user->verified_at) || $user->role === 'admin';

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
        $colors = [
            '#4D73FD',
            '#EC4899',
            '#F2993F',
            '#7CD75A',
            '#E655A0',
            '#EAB308',
        ];
        if ($user->role === 'masyarakat') {
            $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
            $colors = ['#4D73FD', '#EC4899', '#F2993F', '#7CD75A', '#E655A0', '#EAB308'];

            return view('dashboard', [
                'allBidangs' => $allBidangs,
                'colors' => $colors,
            ]);
        } else {
            $query = Pengajuan::with(['user', 'bidang']);

            if ($request->has('search') && $request->input('search') != '') {
                $searchTerm = $request->input('search');

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

            $allBidangNames = BidangPengajuan::pluck('nama_bidang');

            $baseCounts = $allBidangNames->mapWithKeys(function ($nama) {
                return [$nama => 0];
            });

            if ($isVerified) {
                $ajuanCounts = Pengajuan::query()
                    ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
                    ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'))
                    ->groupBy('bidang_pengajuans.nama_bidang')
                    ->pluck('total', 'nama_bidang');

                if ($ajuanCounts->isEmpty()) {
                    $ajuanCounts = $allBidangNames->mapWithKeys(function ($nama) {
                        return [$nama => 0];
                    });
                }
                $semuaAjuan = Pengajuan::with(['user', 'bidang'])->latest()->paginate(5);
            } else {
                $ajuanCounts = $allBidangNames->mapWithKeys(function ($nama) {
                    return [$nama => 0];
                });

                $semuaAjuan = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
            }

            $colors = ['#4D73FD', '#EC4899', '#F2993F', '#7CD75A', '#E655A0', '#EAB308'];
            return view('dashboard', compact('ajuanCounts', 'semuaAjuan', 'colors', 'isVerified'));
        }
    }
}
