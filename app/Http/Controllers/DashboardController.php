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
        $alwaysVerifiedRoles = ['admin', 'kabid', 'admin-kabupaten', 'admin-kecamatan', 'ketua-kader', 'ketua-posyandu', 'operator-desa', 'masyarakat'];
        $isVerified = !is_null($user->verified_at) || in_array($user->role, $alwaysVerifiedRoles);

        // ✅ YEAR FILTER LOGIC
        $currentYear = now()->year;
        $selectedYear = $request->input('year', $currentYear); // Default: tahun berjalan

        // ✅ Generate list tahun (dari tahun awal sistem sampai tahun sekarang)
        $startYear = 2024; // Tahun sistem dimulai
        $availableYears = range($currentYear, $startYear); // [2026, 2025, 2024]

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

        switch ($user->role) {
            case 'masyarakat':
                // Redirect ke pilih layanan
                // session()->forget('ajuan_on_behalf_of_id');
                // return redirect()->route('dashboard.partials.pilih-layanan', compact('allBidangs', 'colors', 'icons'));
                return $this->masyarakatDashboard($request, $user, $selectedYear, $currentYear, $availableYears, $isVerified, $icons);
            case 'kader':
                return redirect()->route('ajuan.index');
            case 'operator-desa':
                // Redirect langsung ke ajuan.index
                return redirect()->route('admin.users.index');
            case 'admin-kabupaten':
            case 'kabid':
            case 'ketua-kader':
            case 'admin-kecamatan':
            case 'ketua-posyandu':
            case 'admin':
                // Tampilkan dashboard dengan chart
                break;

            default:
                abort(403, 'Unauthorized');
        }

        // ✅ UNTUK KETUA KADER, ADMIN KECAMATAN, KABID, DAN ADMIN - TAMPILKAN DASHBOARD
        $ajuanQuery = Pengajuan::query();

        // ✅ Query dengan filter tahun
        $query = Pengajuan::with(['user', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        $desasQuery = Pengajuan::with('user.posyandu')
            ->whereYear('created_at', $selectedYear);

        $actualCountsQuery = Pengajuan::query()
            ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            ->join('users', 'pengajuans.user_id', '=', 'users.id')
            ->whereYear('pengajuans.created_at', $selectedYear)
            ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'));

        // ========================================
        // 🎯 FILTER BERDASARKAN ROLE (HIRARKI)
        // ========================================
        switch ($user->role) {
            // case 'ketua-kader':
            //     // Semua pengajuan di posyandu-nya
            //     $ajuanQuery->whereHas('user', function ($q) use ($user) {
            //         $q->where('posyandu_id', $user->posyandu_id);
            //     });
            //     $query->whereHas('user', function ($q) use ($user) {
            //         $q->where('posyandu_id', $user->posyandu_id);
            //     });
            //     break;
            case 'admin-kabupaten':
                // ✅ Semua pengajuan di kabupatennya
                if ($user->kabupaten) {
                    // Filter Tabel
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                    // Filter Chart
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    // Filter Dropdown Desa
                    $desasQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                }
                break;
            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                    $actualCountsQuery->where('users.kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    $desasQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                }
                break;

            case 'kabid':
                // ✅ Semua pengajuan di kabupatennya
                if ($user->kabupaten) {
                    // Filter Tabel
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                    // Filter Chart
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    // Filter Dropdown Desa
                    $desasQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                }
                // Jika Kabid punya spesifik bidang (misal Kabid Kesehatan)
                if ($user->bidang_id) {
                    $query->where('bidang_id', $user->bidang_id);
                    $actualCountsQuery->where('pengajuans.bidang_id', $user->bidang_id);
                }
                break;

            case 'ketua-kader':
            case 'operator-desa':
            case 'ketua-posyandu':
                if ($user->posyandu_id) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                    $actualCountsQuery->where('users.posyandu_id', $user->posyandu_id);
                    $desasQuery->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                } else {
                    // Fallback jika tidak punya posyandu_id (misal berdasarkan wilayah user)
                    if ($user->desa) {
                        $query->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                        $actualCountsQuery->where('users.desa', $user->desa);
                        $desasQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    }
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
            // $actualCountsQuery = Pengajuan::query()
            //     ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            //     ->join('users', 'pengajuans.user_id', '=', 'users.id')
            //     ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'));
            $actualCountsQuery->groupBy('bidang_pengajuans.nama_bidang');
            $actualCounts = $actualCountsQuery->pluck('total', 'nama_bidang');
            $ajuanCounts = $baseCounts->merge($actualCounts);


            // ✅ Terapkan filter yang sama seperti di atas
            // switch ($user->role) {
            //     case 'ketua-kader':
            //         $actualCountsQuery->where('users.posyandu_id', $user->posyandu_id);
            //         break;

            //     case 'admin-kecamatan':
            //         if ($user->kecamatan) {
            //             $actualCountsQuery->where('users.kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
            //         }
            //         break;

            //     case 'kabid':
            //         if ($user->kabupaten) {
            //             $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
            //         }
            //         break;

            //     case 'admin':
            //         // Tidak ada filter
            //         break;
            // }

            // $actualCountsQuery->groupBy('bidang_pengajuans.nama_bidang');
            // $actualCounts = $actualCountsQuery->pluck('total', 'nama_bidang');

            // $ajuanCounts = $baseCounts->merge($actualCounts);

            // if ($ajuanCounts->isEmpty()) {
            //     $ajuanCounts = $allBidangNames->mapWithKeys(function ($nama) {
            //         return [$nama => 0];
            //     });
            // }

            // Ambil data pengajuan untuk tabel
            $semuaAjuan = $query->latest()->paginate(5)->withQueryString();
        } else {
            $ajuanCounts = $baseCounts;
            $semuaAjuan = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        }

        // Detect current user role
        // $currentUser = Auth::user();

        // // ✅ Ambil daftar desa berdasarkan role

        // switch ($user->role) {
        //     case 'ketua-kader':
        //         $desasQuery->whereHas('user', function ($q) use ($user) {
        //             $q->where('posyandu_id', $user->posyandu_id);
        //         });
        //         break;

        //     case 'admin-kecamatan':
        //         if ($user->kecamatan) {
        //             $desasQuery->whereHas('user', function ($q) use ($user) {
        //                 $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
        //             });
        //         }
        //         break;

        //     case 'kabid':
        //         if ($user->kabupaten) {
        //             $desasQuery->whereHas('user', function ($q) use ($user) {
        //                 $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
        //             });
        //         }
        //         break;
        // }

        $desas = $desasQuery->get()
            ->map(fn($p) => optional($p->user->posyandu)->desa)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $currentUser = Auth::user();

        if ($request->ajax() || $request->input('ajax')) {
            $colorMap = [
                'Bidang Perumahan Rakyat' => 'bg-blue-500',
                'Bidang Pendidikan' => 'bg-orange-500',
                'Bidang Kesehatan' => 'bg-pink-500',
                'Bidang Sosial' => 'bg-rose-500',
                'Bidang Pekerjaan Umum' => 'bg-green-500',
                'Bidang Trantibumlinmas' => 'bg-yellow-500',
            ];

            $bidangData = $ajuanCounts->map(function ($total, $nama) use ($colorMap, $icons) {
                $icon = $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama))]
                    ?? asset('assets/image/icon/bidang/default.svg');
                return [
                    'name' => $nama,
                    'total' => $total,
                    'color' => $colorMap[$nama] ?? 'bg-gray-500',
                    'icon' => $icon,
                ];
            })->values();

            return response()->json([
                'bidangData' => $bidangData,
                'statistics' => [
                    'total' => $ajuanCounts->sum(),
                    'disetujui' => $semuaAjuan->where('status_pengajuan', 'Disetujui')->count(),
                    'diproses' => $semuaAjuan->where('status_pengajuan', 'Diproses')->count(),
                    'ditolak' => $semuaAjuan->where('status_pengajuan', 'Ditolak')->count(),
                ],
                'tableHtml' => view('ajuan.table', ['semuaAjuan' => $semuaAjuan])->render(),
                'paginationHtml' => $semuaAjuan->links()->render(),
            ]);
        }

        return view('dashboard', compact(
            'ajuanCounts',
            'semuaAjuan',
            'colors',
            'isVerified',
            'icons',
            'desas',
            'currentUser',
            'selectedYear',       // ← PASS KE VIEW
            'currentYear',        // ← PASS KE VIEW
            'availableYears'
        ));
    }

    private function masyarakatDashboard($request, $user, $selectedYear, $currentYear, $availableYears, $isVerified, $icons)
    {
        // Query untuk ajuan milik user sendiri
        $myAjuanQuery = Pengajuan::with(['user', 'bidang'])
            ->where('user_id', $user->id)
            ->whereYear('created_at', $selectedYear);

        // Query untuk statistik desa (semua ajuan dari desa yang sama)
        $desaAjuanQuery = Pengajuan::query()
            ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            ->join('users', 'pengajuans.user_id', '=', 'users.id')
            ->whereYear('pengajuans.created_at', $selectedYear);

        // Filter berdasarkan desa user
        if ($user->posyandu_id) {
            $desaAjuanQuery->where('users.posyandu_id', $user->posyandu_id);
        } elseif ($user->desa) {
            $desaAjuanQuery->where('users.desa', $user->desa);
        }

        // Filter Search untuk ajuan sendiri
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $myAjuanQuery->where(function ($q) use ($searchTerm) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('bidang', function ($bidangQuery) use ($searchTerm) {
                        $bidangQuery->where('nama_bidang', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $myAjuanQuery->where('status_pengajuan', $request->status);
        }

        // Hitung statistik desa per bidang
        $allBidangNames = BidangPengajuan::pluck('nama_bidang');
        $baseCounts = $allBidangNames->mapWithKeys(function ($nama) {
            return [$nama => 0];
        });

        $desaStats = $desaAjuanQuery
            ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'))
            ->groupBy('bidang_pengajuans.nama_bidang')
            ->pluck('total', 'nama_bidang');

        $ajuanCounts = $baseCounts->merge($desaStats);

        // Ambil ajuan milik user
        $myAjuan = $myAjuanQuery->latest()->paginate(5)->withQueryString();

        // Statistik ajuan user sendiri
        $myStats = [
            'total' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->count(),
            'disetujui' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Disetujui')->count(),
            'diproses' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Diproses')->count(),
            'ditolak' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Ditolak')->count(),
        ];

        // Untuk AJAX request
        if ($request->ajax() || $request->input('ajax')) {
            $colorMap = [
                'Bidang Perumahan Rakyat' => 'bg-blue-500',
                'Bidang Pendidikan' => 'bg-orange-500',
                'Bidang Kesehatan' => 'bg-pink-500',
                'Bidang Sosial' => 'bg-rose-500',
                'Bidang Pekerjaan Umum' => 'bg-green-500',
                'Bidang Trantibumlinmas' => 'bg-yellow-500',
            ];

            $bidangData = $ajuanCounts->map(function ($total, $nama) use ($colorMap, $icons) {
                $icon = $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama))]
                    ?? asset('assets/image/icon/bidang/default.svg');
                return [
                    'name' => $nama,
                    'total' => $total,
                    'color' => $colorMap[$nama] ?? 'bg-gray-500',
                    'icon' => $icon,
                ];
            })->values();

            return response()->json([
                'bidangData' => $bidangData,
                'statistics' => [
                    'total' => $ajuanCounts->sum(),
                    'disetujui' => Pengajuan::whereHas('user', function ($q) use ($user) {
                        if ($user->posyandu_id) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        } elseif ($user->desa) {
                            $q->where('desa', $user->desa);
                        }
                    })->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Disetujui')->count(),
                    'diproses' => Pengajuan::whereHas('user', function ($q) use ($user) {
                        if ($user->posyandu_id) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        } elseif ($user->desa) {
                            $q->where('desa', $user->desa);
                        }
                    })->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Diproses')->count(),
                    'ditolak' => Pengajuan::whereHas('user', function ($q) use ($user) {
                        if ($user->posyandu_id) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        } elseif ($user->desa) {
                            $q->where('desa', $user->desa);
                        }
                    })->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Ditolak')->count(),
                ],
                'myStats' => $myStats,
                'tableHtml' => view('ajuan.table', ['semuaAjuan' => $myAjuan])->render(),
                'paginationHtml' => (string) $myAjuan->links(),
            ]);
        }

        $colors = [];
        $currentUser = $user;
        $desas = collect([$user->posyandu->desa ?? $user->desa])->filter();

        return view('dashboard', compact(
            'ajuanCounts',
            'myAjuan',
            'myStats',
            'colors',
            'isVerified',
            'icons',
            'desas',
            'currentUser',
            'selectedYear',
            'currentYear',
            'availableYears'
        ));
    }
}
