<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardFilterRequest;
use App\Models\BidangPengajuan;
use App\Models\Posyandu;
use App\Models\Pengajuan;
use App\Support\YearParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Hanya menampilkan halaman dashboard (tidak ada data sensitif di sini).
     * Semua data akan di-fetch oleh JS lewat endpoint getData().
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'kader':
                return redirect()->route('ajuan.index');

            case 'operator-desa':
                return redirect()->route('admin.users.index');

            case 'masyarakat':
            case 'admin':
            case 'kabid':
            case 'admin-kabupaten':
            case 'admin-kecamatan':
            case 'ketua-posyandu':
            case 'kades':
            case 'bu-kades':
            case 'ketua-timpembina-posyandu':
                break;

            default:
                abort(403, 'Unauthorized');
        }

        $currentYear = now()->year;

        $dashboardRules = DashboardFilterRequest::filterRules($currentYear);
        unset($dashboardRules['year']);

        $validator = Validator::make($request->query(), $dashboardRules);
        $validator->after(function ($validator) use ($request): void {
            $extra = array_diff(array_keys($request->query()), DashboardFilterRequest::allowedQueryKeys());

            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });

        if ($validator->fails()) {
            return redirect()->route('dashboard')->withErrors($validator)->withInput();
        }

        // Default ke tahun terbaru yang punya data, bukan selalu tahun ini
        $latestDataYear = Pengajuan::selectRaw('YEAR(created_at) as year')
            ->orderByRaw('YEAR(created_at) DESC')
            ->value('year');
        $defaultYear = $request->query('year') ? $currentYear : ($latestDataYear ?? $currentYear);

        $selectedYear = YearParameter::resolveOrFallback($request->query('year'), $defaultYear, 2000, 2100);

        $startYear = 2024;
        $availableYears = array_unique(array_merge(
            range($currentYear, $startYear),
            $latestDataYear ? [$latestDataYear] : []
        ));
        rsort($availableYears);

        $alwaysVerifiedRoles = ['admin', 'kabid', 'admin-kabupaten', 'admin-kecamatan', 'ketua-posyandu', 'kades', 'bu-kades', 'ketua-timpembina-posyandu', 'operator-desa'];
        $isVerified = !is_null($user->verified_at) || in_array($user->role, $alwaysVerifiedRoles);

        $ajuanCounts = [];

        return view('dashboard', compact('availableYears', 'currentYear', 'selectedYear', 'isVerified', 'ajuanCounts'));
    }

    /**
     * Endpoint AJAX — mengembalikan data dashboard.
     * Hanya bisa diakses lewat request AJAX (XMLHttpRequest), bukan buka URL langsung di browser.
     */
    public function getData(DashboardFilterRequest $request)
    {
        abort_unless($request->ajax(), 403);

        $user    = Auth::user();
        $filters = $request->validated();

        $pieChartLabels = [];
        $pieChartValues = [];
        $pieChartColors = [];

        $currentYear = now()->year;

        $selectedYear = YearParameter::resolveOrFallback($request->query('year'), $currentYear, 2000, 2100);
        $showArchived   = isset($filters['archived']) ? filter_var($filters['archived'], FILTER_VALIDATE_BOOLEAN) : false;
        $searchTerm     = $filters['search'] ?? null;
        $statusFilter   = $filters['status'] ?? null;

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
            'masyarakat',
        ];

        $isVerified = !is_null($user->verified_at) || in_array($user->role, $alwaysVerifiedRoles);

        $icons = [
            'kesehatan'         => asset('assets/image/icon/bidang/kesehatan.svg'),
            'pekerjaan-umum'    => asset('assets/image/icon/bidang/pekerjaan-umum.svg'),
            'pendidikan'        => asset('assets/image/icon/bidang/pendidikan.svg'),
            'perumahan-rakyat'  => asset('assets/image/icon/bidang/perumahan-rakyat.svg'),
            'sosial'            => asset('assets/image/icon/bidang/sosial.svg'),
            'trantibumlinmas'   => asset('assets/image/icon/bidang/trantibumlinmas.svg'),
        ];

        if ($user->role === 'masyarakat') {
            return $this->masyarakatData($user, $selectedYear, $showArchived, $searchTerm, $statusFilter, $icons);
        }

        // -------------------------
        // Build queries
        // -------------------------
        $statsQuery = Pengajuan::with(['user', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        $listQuery = Pengajuan::with(['user', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        $desasQuery = Pengajuan::with('user.posyandu')
            ->whereYear('created_at', $selectedYear);

        $actualCountsQuery = Pengajuan::query()
            ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            ->join('users', 'pengajuans.user_id', '=', 'users.id')
            ->whereYear('pengajuans.created_at', $selectedYear)
            ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'));

        $completedStatuses = ['Disetujui', 'Ditolak'];
        $denyAll = function () use ($listQuery, $statsQuery, $actualCountsQuery, $desasQuery): void {
            $listQuery->whereRaw('1 = 0');
            $statsQuery->whereRaw('1 = 0');
            $actualCountsQuery->whereRaw('1 = 0');
            $desasQuery->whereRaw('1 = 0');
        };

        $applyKadesRegionScope = function () use ($user, $listQuery, $statsQuery, $actualCountsQuery, $desasQuery, $denyAll): void {
            if (!$user->posyandu_id && !$user->desa) {
                $denyAll();
                return;
            }

            $scopeUserQuery = function ($q) use ($user): void {
                $q->where(function ($scope) use ($user): void {
                    if ($user->posyandu_id) {
                        $scope->orWhere('posyandu_id', $user->posyandu_id);
                    }

                    if ($user->desa) {
                        $scope->orWhere('desa', 'LIKE', '%' . $user->desa . '%')
                            ->orWhere('alamat', 'LIKE', '%' . $user->desa . '%')
                            ->orWhereHas('posyandu', function ($posyanduQuery) use ($user): void {
                                $posyanduQuery->where('desa', 'LIKE', '%' . $user->desa . '%')
                                    ->orWhere('nama_posyandu', 'LIKE', '%' . $user->desa . '%');
                            });
                    }
                });
            };

            $listQuery->whereHas('user', $scopeUserQuery);
            $statsQuery->whereHas('user', $scopeUserQuery);
            $desasQuery->whereHas('user', $scopeUserQuery);

            $actualCountsQuery->leftJoin('posyandus', 'users.posyandu_id', '=', 'posyandus.id')
                ->where(function ($scope) use ($user): void {
                    if ($user->posyandu_id) {
                        $scope->orWhere('users.posyandu_id', $user->posyandu_id);
                    }

                    if ($user->desa) {
                        $scope->orWhere('users.desa', 'LIKE', '%' . $user->desa . '%')
                            ->orWhere('users.alamat', 'LIKE', '%' . $user->desa . '%')
                            ->orWhere('posyandus.desa', 'LIKE', '%' . $user->desa . '%')
                            ->orWhere('posyandus.nama_posyandu', 'LIKE', '%' . $user->desa . '%');
                    }
                });
        };

        // -------------------------
        // Filter per role
        // -------------------------
        switch ($user->role) {
            case 'admin-kabupaten':
                if ($user->kabupaten) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                } else {
                    $denyAll();
                }
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                    $actualCountsQuery->where('users.kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                } else {
                    $denyAll();
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                } else {
                    $denyAll();
                }
                if ($user->bidang_id) {
                    $listQuery->where('bidang_id', $user->bidang_id);
                    $statsQuery->where('bidang_id', $user->bidang_id);
                    $actualCountsQuery->where('pengajuans.bidang_id', $user->bidang_id);
                } else {
                    $denyAll();
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
                } else {
                    $denyAll();
                }
                break;

            case 'operator-desa':
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
                } else {
                    $denyAll();
                }
                break;

            case 'kader':
                if ($user->posyandu_id && $user->bidang_id) {
                    $listQuery->where('bidang_id', $user->bidang_id)
                        ->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $statsQuery->where('bidang_id', $user->bidang_id)
                        ->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                    $actualCountsQuery->where('pengajuans.bidang_id', $user->bidang_id)
                        ->where('users.posyandu_id', $user->posyandu_id);
                    $desasQuery->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id));
                } else {
                    $denyAll();
                }
                break;

            case 'kades':
            case 'bu-kades':
                $applyKadesRegionScope();
                break;

            case 'ketua-timpembina-posyandu':
                if ($user->kabupaten) {
                    $listQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $statsQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                    $actualCountsQuery->where('users.kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    $desasQuery->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                } else {
                    $denyAll();
                }
                break;

            case 'admin':
                break;
        }

        if ($showArchived) {
            $listQuery->whereIn('status_pengajuan', $completedStatuses);
        } else {
            $listQuery->where('status_pengajuan', 'Diproses');
        }

        // -------------------------
        // Search & status filter
        // -------------------------
        if (!empty($searchTerm)) {
            $listQuery->where(function ($q) use ($searchTerm) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('user', function ($u) use ($searchTerm) {
                        $u->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('alamat', 'like', '%' . $searchTerm . '%')
                            ->orWhere('desa', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('posyandu', function ($p) use ($searchTerm) {
                                $p->where('nama_posyandu', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('desa', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('kecamatan', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('kabupaten', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    ->orWhereHas('bidang', fn($b) => $b->where('nama_bidang', 'like', '%' . $searchTerm . '%'));
            });
        }

        if (!empty($statusFilter)) {
            $listQuery->where('status_pengajuan', $statusFilter);
        }

        // -------------------------
        // Hitung data
        // -------------------------
        $allBidangNames = BidangPengajuan::pluck('nama_bidang');
        $baseCounts     = $allBidangNames->mapWithKeys(fn($nama) => [$nama => 0]);

        if ($isVerified) {
            $actualCountsQuery->groupBy('bidang_pengajuans.nama_bidang');
            $actualCounts = $actualCountsQuery->pluck('total', 'nama_bidang');
            $ajuanCounts  = $baseCounts->merge($actualCounts);
            $semuaAjuan   = $listQuery->latest()->paginate(5)->withQueryString();

            $semuaAjuan->getCollection()->transform(function ($ajuan) {
                $latestRevisionRequest = $ajuan->histories->where('status', 'Revisi Diminta')->first();
                $latestRevisionSubmit  = $ajuan->histories->where('status', 'Direvisi & Diajukan Kembali')->first();

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
            $semuaAjuan  = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        }

        $desas = $desasQuery->get()
            ->map(fn($p) => optional($p->user->posyandu)->desa)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $allPengajuan = $statsQuery->get();

        $colorMap = [
            'Bidang Perumahan Rakyat'  => 'bg-blue-500',
            'Bidang Pendidikan'        => 'bg-orange-500',
            'Bidang Kesehatan'         => 'bg-pink-500',
            'Bidang Sosial'            => 'bg-rose-500',
            'Bidang Pekerjaan Umum'    => 'bg-green-500',
            'Bidang Trantibumlinmas'   => 'bg-yellow-500',
        ];

        $bidangData = $ajuanCounts->map(function ($total, $nama) use ($colorMap, $icons) {
            $icon = $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama))]
                ?? asset('assets/image/icon/bidang/default.svg');
            return [
                'name'  => $nama,
                'total' => $total,
                'color' => $colorMap[$nama] ?? 'bg-gray-500',
                'icon'  => $icon,
            ];
        })->values();

        return response()->json([
            'bidangData'     => $bidangData,
            'statistics'     => [
                'total'      => $allPengajuan->count(),
                'disetujui'  => $allPengajuan->where('status_pengajuan', 'Disetujui')->count(),
                'diproses'   => $allPengajuan->where('status_pengajuan', 'Diproses')->count(),
                'ditolak'    => $allPengajuan->where('status_pengajuan', 'Ditolak')->count(),
            ],
            'paginationInfo' => [
                'from'  => $semuaAjuan->firstItem() ?? 0,
                'to'    => $semuaAjuan->lastItem() ?? 0,
                'total' => $semuaAjuan->total(),
            ],
            'desas'          => $desas,
            'showArchived'   => $showArchived,
            'tableHtml'      => view('ajuan.table', ['semuaAjuan' => $semuaAjuan])->render(),
            'paginationHtml' => $semuaAjuan->links()->render(),
        ]);
    }

    /**
     * Logic khusus role masyarakat — dipanggil dari getData().
     */
    private function masyarakatData(
        $user,
        int $selectedYear,
        bool $showArchived,
        ?string $searchTerm,
        ?string $statusFilter,
        array $icons
    ) {
        $myAjuanQuery = Pengajuan::with(['user', 'bidang'])
            ->where('user_id', $user->id)
            ->whereYear('created_at', $selectedYear);

        $desaAjuanQuery = Pengajuan::query()
            ->join('bidang_pengajuans', 'pengajuans.bidang_id', '=', 'bidang_pengajuans.id')
            ->join('users', 'pengajuans.user_id', '=', 'users.id')
            ->where('pengajuans.user_id', $user->id)
            ->whereYear('pengajuans.created_at', $selectedYear);

        if (!empty($searchTerm)) {
            $myAjuanQuery->where(function ($q) use ($searchTerm) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('bidang', fn($b) => $b->where('nama_bidang', 'like', '%' . $searchTerm . '%'));
            });
        }

        if (!empty($statusFilter)) {
            $myAjuanQuery->where('status_pengajuan', $statusFilter);
        }

        $allBidangNames = BidangPengajuan::pluck('nama_bidang');
        $baseCounts     = $allBidangNames->mapWithKeys(fn($nama) => [$nama => 0]);

        $desaStats   = $desaAjuanQuery
            ->select('bidang_pengajuans.nama_bidang', DB::raw('count(pengajuans.id) as total'))
            ->groupBy('bidang_pengajuans.nama_bidang')
            ->pluck('total', 'nama_bidang');

        $ajuanCounts = $baseCounts->merge($desaStats);
        $myAjuan     = $myAjuanQuery->latest()->paginate(5);

        $myAjuan->getCollection()->transform(function ($ajuan) {
            $latestRevisionRequest = $ajuan->histories->where('status', 'Revisi Diminta')->first();
            $latestRevisionSubmit  = $ajuan->histories->where('status', 'Direvisi & Diajukan Kembali')->first();

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

        $pieChartLabels = [];
        $pieChartValues = [];
        $pieChartColors = [];

        $bidangColorMap = [
            'Perumahan Rakyat' => 'rgb(59, 130, 246)',   // Blue
            'Pendidikan' => 'rgb(251, 146, 60)',          // Orange
            'Kesehatan' => 'rgb(236, 72, 153)',           // Pink
            'Sosial' => 'rgb(251, 113, 133)',             // Rose
            'Pekerjaan Umum' => 'rgb(34, 197, 94)',       // Green
            'Trantibumlinmas' => 'rgb(234, 179, 8)'       // Yellow
        ];

        foreach ($ajuanCounts as $nama => $total) {
            if ($total > 0) {
                $cleanName = str_replace('Bidang ', '', $nama);
                $pieChartLabels[] = $cleanName;
                $pieChartValues[] = $total;
                $pieChartColors[] = $bidangColorMap[$cleanName] ?? 'rgb(209, 213, 219)';
            }
        }

        if (empty($pieChartLabels)) {
            $pieChartLabels = ['Tidak Ada Data'];
            $pieChartValues = [1];
            $pieChartColors = ['rgb(229, 231, 235)'];
        }

        $myStats = [
            'total'     => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->count(),
            'disetujui' => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Disetujui')->count(),
            'diproses'  => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Diproses')->count(),
            'ditolak'   => Pengajuan::where('user_id', $user->id)->whereYear('created_at', $selectedYear)->where('status_pengajuan', 'Ditolak')->count(),
        ];

        $colorMap = [
            'Bidang Perumahan Rakyat'  => 'bg-blue-500',
            'Bidang Pendidikan'        => 'bg-orange-500',
            'Bidang Kesehatan'         => 'bg-pink-500',
            'Bidang Sosial'            => 'bg-rose-500',
            'Bidang Pekerjaan Umum'    => 'bg-green-500',
            'Bidang Trantibumlinmas'   => 'bg-yellow-500',
        ];

        $bidangData = $ajuanCounts->map(function ($total, $nama) use ($colorMap, $icons) {
            $icon = $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama))]
                ?? asset('assets/image/icon/bidang/default.svg');
            return [
                'name'  => $nama,
                'total' => $total,
                'color' => $colorMap[$nama] ?? 'bg-gray-500',
                'icon'  => $icon,
            ];
        })->values();

        $myAjuan->setPath(url('/dashboard'));

        return response()->json([
            'bidangData'     => $bidangData,
            'statistics'     => [
                'total'     => $myStats['total'],
                'disetujui' => $myStats['disetujui'],
                'diproses'  => $myStats['diproses'],
                'ditolak'   => $myStats['ditolak'],
            ],
            'pieChart'       => [
                'labels' => $pieChartLabels,
                'values' => $pieChartValues,
                'colors' => $pieChartColors,
            ],
            'myStats'        => $myStats,
            'tableHtml'      => view('ajuan.table', ['semuaAjuan' => $myAjuan])->render(),
            'paginationHtml' => $myAjuan->links()->render(),
        ]);
    }
}
