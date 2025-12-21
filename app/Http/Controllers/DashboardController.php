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
        $alwaysVerifiedRoles = ['admin', 'kabid', 'admin-kecamatan', 'ketua-kader', 'masyarakat'];
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

        // ✅ UNTUK KETUA KADER, ADMIN KECAMATAN, KABID, DAN ADMIN - TAMPILKAN DASHBOARD
        $ajuanQuery = Pengajuan::query();
        $query = Pengajuan::with(['user', 'bidang']);

        // ========================================
        // 🎯 FILTER BERDASARKAN ROLE (HIRARKI)
        // ========================================
        switch ($user->role) {
            case 'ketua-kader':
                // Semua pengajuan di posyandu-nya
                $ajuanQuery->whereHas('user', function ($q) use ($user) {
                    $q->where('posyandu_id', $user->posyandu_id);
                });
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('posyandu_id', $user->posyandu_id);
                });
                break;

            case 'admin-kecamatan':
                // ✅ Semua pengajuan di kecamatannya
                if ($user->kecamatan) {
                    $ajuanQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                }
                break;

            case 'kabid':
                // ✅ Semua pengajuan di kabupatennya
                if ($user->kabupaten) {
                    $ajuanQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                }
                break;

            case 'admin':
                // Admin bisa lihat semua (tidak ada filter)
                break;
        }

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

        $allBidangNames = BidangPengajuan::pluck('nama_bidang');

        $baseCounts = $allBidangNames->mapWithKeys(function ($nama) {
            return [$nama => 0];
        });

        if ($isVerified) {
            // ✅ Hitung jumlah pengajuan per bidang DENGAN FILTER ROLE
            $actualCountsQuery = Pengajuan::query()
                ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
                ->join('users', 'pengajuans.user_id', '=', 'users.id')
                ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'));

            // ✅ Terapkan filter yang sama seperti di atas
            switch ($user->role) {
                case 'ketua-kader':
                    $actualCountsQuery->where('users.posyandu_id', $user->posyandu_id);
                    break;

                case 'admin-kecamatan':
                    if ($user->kecamatan) {
                        $actualCountsQuery->where('users.kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    }
                    break;

                case 'kabid':
                    if ($user->kabupaten) {
                        $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    }
                    break;

                case 'admin':
                    // Tidak ada filter
                    break;
            }

            $actualCountsQuery->groupBy('bidang_pengajuans.nama_bidang');
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

        // Detect current user role
        $currentUser = Auth::user();

        // ✅ Ambil daftar desa berdasarkan role
        $desasQuery = Pengajuan::with('user.posyandu');

        switch ($user->role) {
            case 'ketua-kader':
                $desasQuery->whereHas('user', function ($q) use ($user) {
                    $q->where('posyandu_id', $user->posyandu_id);
                });
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $desasQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $desasQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                }
                break;
        }

        $desas = $desasQuery->get()
            ->map(fn($p) => optional($p->user->posyandu)->desa)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('dashboard', compact(
            'ajuanCounts',
            'semuaAjuan',
            'colors',
            'isVerified',
            'icons',
            'desas',
            'currentUser'
        ));
    }
}
