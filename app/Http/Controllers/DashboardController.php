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

        $alwaysVerifiedRoles = [
            'admin',
            'kabid',
            'admin-kabupaten',
            'admin-kecamatan',
            'ketua-posyandu',
            'kades',
            'bu-kades',
            'ketua-timpembina-posyandu',
            'operator-desa',
            'masyarakat'
        ];

        $isVerified = !is_null($user->verified_at) || in_array($user->role, $alwaysVerifiedRoles);

        $currentYear = now()->year;
        $selectedYear = $request->input('year', $currentYear);
        $startYear = 2024;
        $availableYears = range($currentYear, $startYear);
        $showArchived = $request->boolean('archived', 0);

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
                return $this->masyarakatDashboard($request, $user, $selectedYear, $currentYear, $availableYears, $isVerified, $icons);

            case 'kader':
                return redirect()->route('ajuan.index');

            case 'operator-desa':
                return redirect()->route('admin.users.index');

            case 'admin-kabupaten':
            case 'kabid':
            case 'ketua-posyandu':
            case 'kades':
            case 'bu-kades':
            case 'admin-kecamatan':
            case 'ketua-timpembina-posyandu':
            case 'admin':
                break;

            default:
                abort(403, 'Unauthorized');
        }

        $statsQuery = Pengajuan::with(['user', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        $listQuery = Pengajuan::with(['user', 'bidang',])
            ->whereYear('created_at', $selectedYear);

        $query = Pengajuan::with(['user', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        $desasQuery = Pengajuan::with('user.posyandu')
            ->whereYear('created_at', $selectedYear);

        $actualCountsQuery = Pengajuan::query()
            ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            ->join('users', 'pengajuans.user_id', '=', 'users.id')
            ->whereYear('pengajuans.created_at', $selectedYear)
            ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'));

        switch ($user->role) {
            case 'admin-kabupaten':
                if ($user->kabupaten) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                }
                if ($showArchived) {
                    $listQuery->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                }
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                    $actualCountsQuery->where('users.kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                }
                if ($showArchived) {
                    $listQuery->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                }
                if ($user->bidang_id) {
                    $listQuery->where('bidang_id', $user->bidang_id);
                    $statsQuery->where('bidang_id', $user->bidang_id);
                    $actualCountsQuery->where('pengajuans.bidang_id', $user->bidang_id);
                }
                if ($showArchived) {
                    $listQuery->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                }
                break;

            case 'ketua-posyandu':
                if ($user->posyandu_id) {
                    $listQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $statsQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $actualCountsQuery->where('users.posyandu_id', $user->posyandu_id);
                    $desasQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                } elseif ($user->desa) {
                    $listQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    $statsQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    $actualCountsQuery->where('users.desa', $user->desa);
                    $desasQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                }

                if (!$showArchived) {
                    $listQuery->where(function ($q) {
                        $q->where(function ($subQ) {
                            $subQ->where('kunjungan_lapangan', true)
                                ->where('approved_by_ketua', false)
                                ->where('status_pengajuan', 'Diproses');
                        })->orWhere('status_pengajuan', 'Sesuai');
                    });
                } else {
                    $listQuery->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                }
                break;

            case 'kades':
            case 'bu-kades':
                if ($user->posyandu_id) {
                    $listQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $statsQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $actualCountsQuery->where('users.posyandu_id', $user->posyandu_id);
                    $desasQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                } elseif ($user->desa) {
                    $listQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    $statsQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    $actualCountsQuery->where('users.desa', $user->desa);
                    $desasQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                }

                if (!$showArchived) {
                    $listQuery->where('status_pengajuan', 'Diajukan ke Desa');
                } else {
                    $listQuery->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                }
                break;

            case 'ketua-timpembina-posyandu':
                // Ketua Tim Pembina Posyandu tidak lagi di alur tahap 3
                if ($user->posyandu_id) {
                    $listQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $statsQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $actualCountsQuery->where('users.posyandu_id', $user->posyandu_id);
                    $desasQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                } elseif ($user->desa) {
                    $listQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    $statsQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                    $actualCountsQuery->where('users.desa', $user->desa);
                    $desasQuery->whereHas('user', fn($q) => $q->where('desa', $user->desa));
                }

                // Tidak ada pengajuan yang perlu ditindaklanjuti oleh Ketua Tim Pembina
                $listQuery->whereRaw('1 = 0');
                break;

            case 'admin':
                if ($showArchived) {
                    $listQuery->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                }
                break;
        }

        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $listQuery->where(function ($q) use ($searchTerm) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('user', fn($userQuery) => $userQuery->where('name', 'like', '%' . $searchTerm . '%'))
                    ->orWhereHas('bidang', fn($bidangQuery) => $bidangQuery->where('nama_bidang', 'like', '%' . $searchTerm . '%'));
            });
        }

        if ($request->filled('status')) {
            $listQuery->where('status_pengajuan', $request->status);
        }

        $allBidangNames = BidangPengajuan::pluck('nama_bidang');
        $baseCounts = $allBidangNames->mapWithKeys(fn($nama) => [$nama => 0]);

        if ($isVerified) {
            $actualCountsQuery->groupBy('bidang_pengajuans.nama_bidang');
            $actualCounts = $actualCountsQuery->pluck('total', 'nama_bidang');
            $ajuanCounts = $baseCounts->merge($actualCounts);
            $semuaAjuan = $listQuery->latest()->paginate(5)->withQueryString();

            $semuaAjuan->getCollection()->transform(function ($ajuan) {
                $latestRevisionRequest = $ajuan->histories
                    ->where('status', 'Revisi Diminta')
                    ->first();

                $latestRevisionSubmit = $ajuan->histories
                    ->where('status', 'Direvisi & Diajukan Kembali')
                    ->first();

                if ($latestRevisionRequest && $latestRevisionSubmit) {
                    $requestDate = $latestRevisionRequest->created_at instanceof \Carbon\Carbon
                        ? $latestRevisionRequest->created_at
                        : \Carbon\Carbon::parse($latestRevisionRequest->created_at);

                    $submitDate = $latestRevisionSubmit->created_at instanceof \Carbon\Carbon
                        ? $latestRevisionSubmit->created_at
                        : \Carbon\Carbon::parse($latestRevisionSubmit->created_at);

                    $ajuan->has_been_revised_by_user = $submitDate->greaterThan($requestDate);
                } else {
                    $ajuan->has_been_revised_by_user = false;
                }

                $ajuan->is_waiting_revision = $latestRevisionRequest && !$ajuan->has_been_revised_by_user;

                return $ajuan;
            });
        } else {
            $ajuanCounts = $baseCounts;
            $semuaAjuan = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        }

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
                $icon = $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama))] ?? asset('assets/image/icon/bidang/default.svg');
                return [
                    'name' => $nama,
                    'total' => $total,
                    'color' => $colorMap[$nama] ?? 'bg-gray-500',
                    'icon' => $icon,
                ];
            })->values();

            $allPengajuan = $statsQuery->get();

            return response()->json([
                'bidangData' => $bidangData,
                'statistics' => [
                    'total' => $allPengajuan->count(),
                    'disetujui' => $allPengajuan->where('status_pengajuan', 'Disetujui')->count(),
                    'diproses' => $allPengajuan->where('status_pengajuan', 'Diproses')->count(),
                    'ditolak' => $allPengajuan->where('status_pengajuan', 'Ditolak')->count(),
                ],
                'paginationInfo' => [
                    'from' => $semuaAjuan->firstItem() ?? 0,
                    'to' => $semuaAjuan->lastItem() ?? 0,
                    'total' => $semuaAjuan->total(),
                ],
                'showArchived' => $showArchived,
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
            'selectedYear',
            'currentYear',
            'availableYears',
            'showArchived'
        ));
    }
    public function debugDashboard(Request $request)
    {
        $user = Auth::user();
        $selectedYear = $request->input('year', now()->year);

        $query = Pengajuan::with(['user.posyandu', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        $semuaDataTanpaFilter = (clone $query)->get();

        if (in_array($user->role, ['ketua-posyandu', 'ketua-timpembina-posyandu']) && $user->posyandu_id) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('posyandu_id', $user->posyandu_id);
            });
        } elseif (in_array($user->role, ['kades', 'bu-kades'])) {
            $desaName = $user->posyandu->desa ?? $user->desa;
            $query->whereHas('user.posyandu', function ($q) use ($desaName) {
                $q->where('desa', $desaName);
            });
        }

        $allFiltered = $query->get();

        return response()->json([
            'info_login' => [
                'role' => $user->role,
                'my_posyandu_id' => $user->posyandu_id,
                'my_desa' => $user->posyandu->desa ?? $user->desa,
            ],
            'hasil_tanpa_filter_wilayah' => [
                'total' => $semuaDataTanpaFilter->count(),
                'bidang_found' => $semuaDataTanpaFilter->groupBy('bidang.nama_bidang')->map->count(),
            ],
            'hasil_dengan_filter_wilayah' => [
                'total' => $allFiltered->count(),
                'status_breakdown' => $allFiltered->groupBy('status_pengajuan')->map->count(),
            ],
            'list_id_dan_bidang_di_db' => $semuaDataTanpaFilter->map(function ($p) {
                return [
                    'id' => $p->id,
                    'bidang' => $p->bidang->nama_bidang,
                    'posyandu_user' => $p->user->posyandu_id,
                    'desa_user' => $p->user->posyandu->desa ?? 'N/A'
                ];
            })
        ]);
    }

    private function masyarakatDashboard($request, $user, $selectedYear, $currentYear, $availableYears, $isVerified, $icons)
    {
        $myAjuanQuery = Pengajuan::with(['user', 'bidang'])
            ->where('user_id', $user->id)
            ->whereYear('created_at', $selectedYear);

        $desaAjuanQuery = Pengajuan::query()
            ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            ->join('users', 'pengajuans.user_id', '=', 'users.id')
            ->whereYear('pengajuans.created_at', $selectedYear);

        if ($user->posyandu_id) {
            $desaAjuanQuery->where('users.posyandu_id', $user->posyandu_id);
        } elseif ($user->desa) {
            $desaAjuanQuery->where('users.desa', $user->desa);
        }

        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $myAjuanQuery->where(function ($q) use ($searchTerm) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('bidang', fn($bidangQuery) => $bidangQuery->where('nama_bidang', 'like', '%' . $searchTerm . '%'));
            });
        }

        if ($request->filled('status')) {
            $myAjuanQuery->where('status_pengajuan', $request->status);
        }

        $allBidangNames = BidangPengajuan::pluck('nama_bidang');
        $baseCounts = $allBidangNames->mapWithKeys(fn($nama) => [$nama => 0]);

        $desaStats = $desaAjuanQuery
            ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'))
            ->groupBy('bidang_pengajuans.nama_bidang')
            ->pluck('total', 'nama_bidang');

        $ajuanCounts = $baseCounts->merge($desaStats);
        $myAjuan = $myAjuanQuery->latest()->paginate(5)->withQueryString();

        $myAjuan->getCollection()->transform(function ($ajuan) {
            $latestRevisionRequest = $ajuan->histories
                ->where('status', 'Revisi Diminta')
                ->first();

            $latestRevisionSubmit = $ajuan->histories
                ->where('status', 'Direvisi & Diajukan Kembali')
                ->first();

            if ($latestRevisionRequest && $latestRevisionSubmit) {
                $requestDate = $latestRevisionRequest->created_at instanceof \Carbon\Carbon
                    ? $latestRevisionRequest->created_at
                    : \Carbon\Carbon::parse($latestRevisionRequest->created_at);

                $submitDate = $latestRevisionSubmit->created_at instanceof \Carbon\Carbon
                    ? $latestRevisionSubmit->created_at
                    : \Carbon\Carbon::parse($latestRevisionSubmit->created_at);

                $ajuan->has_been_revised_by_user = $submitDate->greaterThan($requestDate);
            } else {
                $ajuan->has_been_revised_by_user = false;
            }

            $ajuan->is_waiting_revision = $latestRevisionRequest && !$ajuan->has_been_revised_by_user;

            return $ajuan;
        });

        $myStats = [
            'total' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->count(),
            'disetujui' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Disetujui')->count(),
            'diproses' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Diproses')->count(),
            'ditolak' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Ditolak')->count(),
        ];

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
                $icon = $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama))] ?? asset('assets/image/icon/bidang/default.svg');
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
                        if ($user->posyandu_id) $q->where('posyandu_id', $user->posyandu_id);
                        elseif ($user->desa) $q->where('desa', $user->desa);
                    })->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Disetujui')->count(),
                    'diproses' => Pengajuan::whereHas('user', function ($q) use ($user) {
                        if ($user->posyandu_id) $q->where('posyandu_id', $user->posyandu_id);
                        elseif ($user->desa) $q->where('desa', $user->desa);
                    })->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Diproses')->count(),
                    'ditolak' => Pengajuan::whereHas('user', function ($q) use ($user) {
                        if ($user->posyandu_id) $q->where('posyandu_id', $user->posyandu_id);
                        elseif ($user->desa) $q->where('desa', $user->desa);
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