<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\Posyandu;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $alwaysVerifiedRoles = ['admin', 'kabid', 'ketua-kader', 'masyarakat'];
        $isVerified = !is_null($user->verified_at) || in_array($user->role, $alwaysVerifiedRoles);

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
        $colors = [
            '#4D73FD',
            '#f43f5e',
            '#F2993F',
            '#7CD75A',
            '#E655A0',
            '#EAB308',
        ];
        $icons = [
            'kesehatan' => asset('assets/image/icon/bidang/kesehatan.svg'),
            'pekerjaan-umum' => asset('assets/image/icon/bidang/pekerjaan-umum.svg'),
            'pendidikan' => asset('assets/image/icon/bidang/pendidikan.svg'),
            'perumahan-rakyat' => asset('assets/image/icon/bidang/perumahan-rakyat.svg'),
            'sosial' => asset('assets/image/icon/bidang/sosial.svg'),
            'trantibumlinmas' => asset('assets/image/icon/bidang/trantibumlinmas.svg'),
        ];

        // ✅ REDIRECT MASYARAKAT KE PILIH LAYANAN
        if ($user->role === 'masyarakat') {
            session()->forget('ajuan_on_behalf_of_id');
            return redirect()->route('dashboard.partials.pilih-layanan', compact('allBidangs', 'colors', 'icons'));
        }

        // ✅ REDIRECT KADER LANGSUNG KE AJUAN.INDEX
        if ($user->role === 'kader') {
            return redirect()->route('ajuan.index');
        }

        // ✅ UNTUK KETUA KADER, KABID, DAN ADMIN - TAMPILKAN DASHBOARD
        $ajuanQuery = Pengajuan::query();
        $query = Pengajuan::with(['user', 'bidang']);

        // Filter Search
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('bidang', function ($bidangQuery) use ($searchTerm) {
                        $bidangQuery->where('nama_bidang', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status_pengajuan', $request->status);
        }

        // Filter berdasarkan Posyandu untuk Ketua Kader
        if ($user->role === 'ketua-kader') {
            $posyanduId = $user->posyandu_id;
            $ajuanQuery->whereHas('user', function ($q) use ($posyanduId) {
                $q->where('posyandu_id', $posyanduId);
            });
            $query->whereHas('user', function ($q) use ($posyanduId) {
                $q->where('posyandu_id', $posyanduId);
            });
        }

        $allBidangNames = BidangPengajuan::pluck('nama_bidang');

        $baseCounts = $allBidangNames->mapWithKeys(function ($nama) {
            return [$nama => 0];
        });

        if ($isVerified) {
            // Hitung jumlah pengajuan per bidang
            $actualCountsQuery = Pengajuan::query()
                ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
                ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'))
                ->groupBy('bidang_pengajuans.nama_bidang');

            // Filter untuk Ketua Kader
            if ($user->role === 'ketua-kader') {
                $actualCountsQuery->join('users', 'pengajuans.user_id', '=', 'users.id')
                    ->where('users.posyandu_id', $user->posyandu_id);
            }

            $actualCounts = $actualCountsQuery->pluck('total', 'nama_bidang');

            $ajuanCounts = $baseCounts->merge($actualCounts);

            if ($ajuanCounts->isEmpty()) {
                $ajuanCounts = $allBidangNames->mapWithKeys(function ($nama) {
                    return [$nama => 0];
                });
            }

            // Ambil data pengajuan untuk tabel
            $semuaAjuan = $query->latest()->paginate(5)->withQueryString();
        } else {
            $ajuanCounts = $baseCounts;
            $semuaAjuan = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        }

        //detect current user role
        $currentUser = Auth::user();

        $desas = Pengajuan::with('user.posyandu')
            ->get()
            ->map(fn($p) => optional($p->user->posyandu)->desa)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('dashboard', compact('ajuanCounts', 'semuaAjuan', 'colors', 'isVerified', 'icons', 'desas', 'currentUser'));
    }
}
