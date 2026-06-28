<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengajuanRequest;
use App\Http\Requests\UpdatePengajuanRequest;
use App\Models\BidangPengajuan;
use App\Models\History;
use App\Models\Pengajuan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\PengajuanStatusUpdated;
use App\Support\AccessAudit;
use App\Support\YearParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AjuanController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Pengajuan::class);
        $currentYear = now()->year;

        $selectedYear = YearParameter::resolveOrFallback($request->query('year'), $currentYear, 2000, 2100);

        // ✅ Build query with validated year
        $query = Pengajuan::with(['user', 'bidang'])
            ->whereYear('created_at', $selectedYear);

        // Role-based filtering
        $currentUser = Auth::user();

        switch ($currentUser->role) {
            case 'admin':
                // No additional filter
                break;

            case 'ketua-timpembina-posyandu':
            case 'admin-kabupaten':
                if ($currentUser->kabupaten_id) {
                    $query->whereHas('user', function ($q) use ($currentUser) {
                        $q->where(function ($inner) use ($currentUser) {
                            $inner->where('kabupaten_id', $currentUser->kabupaten_id);
                            if ($currentUser->kabupaten) {
                                $inner->orWhere('kabupaten', 'ILIKE', $currentUser->kabupaten);
                            }
                        });
                    })->where('submitted_to_desa', true);
                } elseif ($currentUser->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'ILIKE', $currentUser->kabupaten))
                          ->where('submitted_to_desa', true);
                } else {
                    abort(403, 'Unauthorized');
                }
                break;

            case 'kabid':
                if (!$currentUser->bidang_id) {
                    abort(403, 'Unauthorized');
                }
                $query->where('bidang_id', $currentUser->bidang_id);
                if ($currentUser->kabupaten_id) {
                    $query->whereHas('user', function ($q) use ($currentUser) {
                        $q->where(function ($inner) use ($currentUser) {
                            $inner->where('kabupaten_id', $currentUser->kabupaten_id);
                            if ($currentUser->kabupaten) {
                                $inner->orWhere('kabupaten', 'ILIKE', $currentUser->kabupaten);
                            }
                        });
                    })->where('submitted_to_desa', true);
                } elseif ($currentUser->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'ILIKE', $currentUser->kabupaten))
                          ->where('submitted_to_desa', true);
                } else {
                    abort(403, 'Unauthorized');
                }
                break;

            case 'admin-kecamatan':
                if ($currentUser->kecamatan_id) {
                    $query->whereHas('user', function ($q) use ($currentUser) {
                        $q->where(function ($inner) use ($currentUser) {
                            $inner->where('kecamatan_id', $currentUser->kecamatan_id);
                            if ($currentUser->kecamatan) {
                                $inner->orWhere('kecamatan', 'ILIKE', $currentUser->kecamatan);
                            }
                        });
                        if ($currentUser->kabupaten) {
                            $q->where('kabupaten', 'ILIKE', '%' . $currentUser->kabupaten . '%');
                        }
                    })->where('submitted_to_desa', true);
                } elseif ($currentUser->kecamatan) {
                    $query->whereHas('user', function ($q) use ($currentUser) {
                        $q->where('kecamatan', 'ILIKE', $currentUser->kecamatan);
                        if ($currentUser->kabupaten) {
                            $q->where('kabupaten', 'ILIKE', '%' . $currentUser->kabupaten . '%');
                        }
                    })->where('submitted_to_desa', true);
                } else {
                    abort(403, 'Unauthorized');
                }
                break;

            case 'operator-desa':
            case 'kades':
            case 'bu-kades':
                if ($currentUser->desa) {
                    $query->whereHas('user', function ($q) use ($currentUser) {
                        $q->where('desa', 'ILIKE', $currentUser->desa);
                        if ($currentUser->kecamatan) {
                            $q->where('kecamatan', 'ILIKE', $currentUser->kecamatan);
                        }
                        if ($currentUser->kabupaten) {
                            $q->where('kabupaten', 'ILIKE', $currentUser->kabupaten);
                        }
                    })->where('submitted_to_desa', true);
                } else {
                    abort(403, 'Unauthorized');
                }
                break;

            case 'ketua-posyandu':
                $query->whereHas(
                    'user',
                    fn($q) =>
                    $q->where('posyandu_id', $currentUser->posyandu_id)
                );
                break;

            // ✅ FIXED: Kader only see their bidang
            case 'kader':
                $query->whereHas(
                    'user',
                    fn($q) =>
                    $q->where('posyandu_id', $currentUser->posyandu_id)
                )
                    ->where('bidang_id', $currentUser->bidang_id);
                break;

            case 'masyarakat':
                $query->where('user_id', $currentUser->id);
                break;

            default:
                abort(403, 'Unauthorized');
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $search . '%')
                    ->orWhereHas(
                        'user',
                        fn($userQuery) =>
                        $userQuery->where('name', 'like', '%' . $search . '%')
                    )
                    ->orWhereHas(
                        'bidang',
                        fn($bidangQuery) =>
                        $bidangQuery->where('nama_bidang', 'like', '%' . $search . '%')
                    );
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status_pengajuan', $request->status);
        }

        // Advanced Filter: Tanggal Mulai dan Selesai
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_permohonan', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal_permohonan', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal_permohonan', '<=', $request->end_date . ' 23:59:59');
        }

        // Advanced Filter: Posyandu
        if ($request->filled('posyandu_id')) {
            $posyanduId = $request->posyandu_id;
            $query->whereHas('user', function ($q) use ($posyanduId) {
                $q->where('posyandu_id', $posyanduId);
            });
        }

        $pengajuans = $query->latest()->paginate(10)->withQueryString();

        $availableYears = range($currentYear, 2024);
        
        $posyandus = \App\Models\Posyandu::orderBy('nama_posyandu')->get();

        return view('ajuan.index', compact(
            'pengajuans',
            'selectedYear',
            'currentYear',
            'availableYears',
            'posyandus'
        ));
    }

    private function getTargetUserId()
    {
        if (session()->has('ajuan_on_behalf_of_id') && Auth::user()->role !== 'masyarakat') {
            return session('ajuan_on_behalf_of_id');
        }
        return Auth::id();
    }

    private function getTargetUser(): ?User
    {
        $actor = Auth::user();
        $targetUserId = $this->getTargetUserId();

        if (!$actor || !$targetUserId) {
            return null;
        }

        $target = User::find($targetUserId);

        if (!$target) {
            return null;
        }

        if ($actor->role === 'kader') {
            return $target->role === 'masyarakat'
                && (string) $target->posyandu_id === (string) $actor->posyandu_id
                ? $target
                : null;
        }

        if ($actor->role === 'masyarakat') {
            return (string) $target->id === (string) $actor->id ? $target : null;
        }

        return $target;
    }

    private function historyRole(?string $role): ?string
    {
        return in_array($role, ['kader', 'ketua-posyandu', 'ketua-timpembina-posyandu', 'kades', 'system'], true)
            ? $role
            : null;
    }

    public function requestRevision(Request $request, Pengajuan $ajuan)
    {
        $ajuan = Pengajuan::findOrFail($ajuan->id);

        $autoRejectDays = SystemSetting::get('auto_reject_days', 5);
        $maxRevisionCount = SystemSetting::get('max_revision_count', 3);

        $request->validate([
            'catatan' => 'required|string|max:500'
        ]);

        if ($ajuan->revision_count >= $maxRevisionCount) {
            return redirect()->back()->with('error', "Revisi tidak dapat diminta lagi. Pengajuan sudah mencapai batas maksimal revisi ({$maxRevisionCount}x).");
        }

        $ajuan->update([
            'status_pengajuan' => 'Diproses',
            'revision_requested_at' => now(),
            'revision_count' => $ajuan->revision_count + 1,
        ]);

        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => 'Revisi Diminta',
            'catatan' => $request->catatan,
            'diubah_oleh' => Auth::id(),
            'action_by_role' => $this->historyRole(Auth::user()->role),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Revisi berhasil diminta. User memiliki {$autoRejectDays} hari kerja untuk merevisi.");
    }

    public function pilihLayanan(Request $request)
    {
        $user = Auth::user();
        // Only allow masyarakat and kader to access pilih-layanan
        if (! in_array($user->role, ['masyarakat', 'kader', 'admin'])) {
            abort(403, 'Unauthorized');
        }
        if ($request->has('on_behalf_of')) {
            session(['ajuan_on_behalf_of_id' => $request->query('on_behalf_of')]);
        }
        if ($user->role === 'masyarakat') {
            session()->forget('ajuan_on_behalf_of_id');
        }
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

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        return view('dashboard.partials.pilih-layanan', compact('allBidangs', 'colors', 'icons'));
    }

    public function pilihUser(Request $request)
    {
        $user = Auth::user();

        if ($request->has('user_id')) {
            $masyarakatId = $request->query('user_id');
            session(['ajuan_on_behalf_of_id' => $masyarakatId]);

            if ($user->role === 'kader' && $user->bidang_id) {
                $bidangSlug = $user->bidang->slug;
                return redirect()->route('ajuan.create', $bidangSlug);
            } else {
                // only redirect to pilih-layanan if the current user may access it
                if (in_array($user->role, ['masyarakat', 'kader', 'admin'])) {
                    return redirect()->route('dashboard.partials.pilih-layanan');
                }

                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses untuk memilih layanan.');
            }
        }

        $query = User::with(['posyandu'])
            ->where('role', 'masyarakat')
            ->visibleTo($user)
            ->orderBy('name');

        $masyarakatUsers = $query->get();

        return view('dashboard.partials.pilih-user', compact('masyarakatUsers'));
    }

    public function create($bidang_slug)
    {

        Session::forget('ajuan_data');

        $user = Auth::user();
        if ($user && $user->role === 'kader' && ! $user->bidang) {
            return redirect()->route('dashboard')->with('error', 'Akun kader belum diatur bidangnya. Hubungi administrator.');
        }

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
        $bidang = BidangPengajuan::where('slug', $bidang_slug)->firstOrFail();

        $templateData = $this->getBidangData($bidang->slug);
        if (! $templateData) {
            abort(404, 'Definisi formulir untuk bidang ini tidak ditemukan.');
        }

        session(['ajuan_data' => [
            'bidang_id' => $bidang->id,
            'bidang_slug' => $bidang->slug,
            'bidang_nama' => $bidang->nama_bidang,
            'administrasi_items_template' => $templateData['administrasi_items'],
        ]]);

        return view('components.ajuan.formulir.index', [
            'bidang' => $bidang,
            'allBidangs' => $allBidangs,
            'items' => $templateData['formulir_items'],
        ]);
    }

    public function storePermohonan(StorePengajuanRequest $request)
    {
        $validated = $request->validated();
        $permohonanItems = $validated['permohonan_items'] ?? [];

        $bidangSlug = $validated['bidang_pelayanan'];
        $bidang = BidangPengajuan::where('slug', $bidangSlug)->firstOrFail();

        $templateData = $this->getBidangData($bidangSlug);
        if (!$templateData) {
            return redirect()->back()->with('error', 'Template bidang tidak ditemukan.');
        }

        session()->put('ajuan_data.bidang_id', $bidang->id);
        session()->put('ajuan_data.bidang_slug', $bidang->slug);
        session()->put('ajuan_data.bidang_nama', $bidang->nama_bidang);
        session()->put('ajuan_data.administrasi_items_template', $templateData['administrasi_items']);
        session()->put('ajuan_data.selected_formulir_items', $permohonanItems);
        session()->put('ajuan_data.deskripsi_pengajuan', $validated['deskripsi_pengajuan']);
        session()->put('ajuan_data.tanggal_permohonan', now());
        session()->put('ajuan_data.tindak_lanjut', $validated['deskripsi_pengajuan']);

        if (!empty($validated['lainnya_text'])) {
            session()->put('ajuan_data.lainnya_text', $validated['lainnya_text']);
        }

        return redirect()->route('ajuan.create.administrasi');
    }

    public function createAdministrasi()
    {

        $ajuanData = session('ajuan_data');
        $user = Auth::user();
        $targetUser = $this->getTargetUser();
        if (! $ajuanData) {
            return redirect()->route('dashboard');
        }

        return view('components.ajuan.administrasi-ajuan.index', [
            'items' => $ajuanData['administrasi_items_template'],
            'userKtp' => $targetUser?->ktp ?? $user->ktp,
            'userKk' => $targetUser?->kk ?? $user->kk
        ]);
    }

    public function storeAdministrasi(Request $request)
    {
        $ajuanData = session('ajuan_data');
        $user = Auth::user();

        if (!$ajuanData || !$user) {
            return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
        }

        $targetUser = $this->getTargetUser();
        if (!$targetUser) {
            Session::forget('ajuan_on_behalf_of_id');
            return redirect()->route('dashboard')->with('error', 'User masyarakat yang dipilih tidak valid atau di luar posyandu Anda.');
        }

        $validationRules = [];
        foreach ($ajuanData['administrasi_items_template'] as $key => $item) {
            if ($key === 'ktp') {
                $validationRules[$key] = ['required_if:ktp_mode,upload', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
            } elseif ($key === 'kk') {
                $validationRules[$key] = ['required_if:kk_mode,upload', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
            } elseif ($key === 'kartu_bpjs') {
                $validationRules[$key] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
            } else {
                $validationRules[$key] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
            }
        }
        $validationRules['agreement'] = ['required'];

        $request->validate($validationRules);

        $uploadedFiles = [];
        foreach (array_keys($ajuanData['administrasi_items_template']) as $key) {

            if ($request->hasFile($key)) {
                $path = $this->compressAndStoreImage($request->file($key), 'ajuan_dokumen');
                $uploadedFiles[$key] = $path;
            } elseif ($key === 'ktp' && $request->input('ktp_mode') === 'claimed' && $targetUser->ktp) {
                $uploadedFiles[$key] = $targetUser->ktp;
            } elseif ($key === 'kk' && $request->input('kk_mode') === 'claimed' && $targetUser->kk) {
                $uploadedFiles[$key] = $targetUser->kk;
            }
        }

        $finalChecklistData = $ajuanData['selected_formulir_items'];
        if (isset($ajuanData['lainnya_text']) && in_array('Lainnya...', $finalChecklistData)) {
            $finalChecklistData = array_map(function ($item) use ($ajuanData) {
                return $item === 'Lainnya...' ? 'Lainnya: ' . $ajuanData['lainnya_text'] : $item;
            }, $finalChecklistData);
        }

        $trackingCode = $this->generateTrackingCode();

        $pengajuan = Pengajuan::create([
            'user_id' => $targetUser->id,
            'bidang_id' => $ajuanData['bidang_id'],
            'status_pengajuan' => 'Diproses',
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $uploadedFiles,
            'deskripsi_pengajuan' => $ajuanData['deskripsi_pengajuan'] ?? 'Tidak ada deskripsi.',
            'tanggal_permohonan' => now(),
            'tracking_code' => $trackingCode,
        ]);

        History::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'Diajukan',
            'catatan' => 'Pengajuan baru telah dibuat oleh ' . $user->name . '.',
            'diubah_oleh' => $user->id,
            'created_at' => now(),
        ]);

        Session::forget('ajuan_data');
        Session::forget('ajuan_on_behalf_of_id');

        return redirect()->route('ajuan.index')->with('success', 'Ajuan berhasil dikirim!');
    }

    private function getBidangData($bidang_slug)
    {
        $allData = [
            'pendidikan' => [
                'formulir_items' => [
                    'Pendidikan anak usia dini (0 s.d 6 Tahun)',
                    'Identifikasi ketersediaan dan pengelolaan perpustakaan desa penguatan pemanfaatan literasi',
                    'Identifikasi penyediaan alat peraga edukasi (APE)',
                    'Pembiayaan sekolah',
                    'Perlengkapan sekolah',
                    'Lainnya...',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_pernyataan_tidak_mampu' => 'Surat pernyataan tidak mampu dari RT setempat (untuk pilihan no. 4 dan 5)',
                ],
            ],
            'kesehatan' => [
                'formulir_items' => [
                    'Pemberian makanan tambahan bagi anak usia sekolah',
                    'Pemberian alat/sarpras kesehatan',
                    'Kunjungan Posyandu pada sasaran',
                    'Penyuluhan kesehatan',
                    'Deteksi dini risiko masalah kesehatan pada sasaran',
                    'Rujukan ke unit kesehatan desa/kelurahan atau pusat kesehatan masyarakat',
                    'Pemantauan perilaku kepatuhan keluarga untuk mendapatkan pelayanan kesehatan',
                    'Pemantauan perilaku kepatuhan keluarga untuk melaksanakan pengobatan',
                    'Akses untuk mendapatkan imunisasi, vitamin A, tablet tambah darah',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK'],
            ],
            'pekerjaan-umum' => [
                'formulir_items' => [
                    'Pemenuhan kebutuhan pokok air bersih',
                    'Pengelolaan limbah domestik/rumah tangga',
                    'Penyediaan WC',
                    'Pengelolaan sampah',
                    'Identifikasi/pemeliharaan embung air baku',
                    'Pemeliharaan jaringan air bersih',
                    'Identifikasi/Rehabilitasi sumur air tanah untuk air baku',
                    'Identifikasi kebutuhan pembangunan jalan desa',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'surat_permohonan_dusun' => 'Surat Permohonan RT/RW', 'lokasi_titik_pembangunan' => 'Lokasi titik pembangunan sarana prasarana'],
            ],
            'perumahan-rakyat' => [
                'formulir_items' => [
                    'Penyediaan dan rehabilitasi rumah layak huni',
                    'Komunikasi, informasi dan edukasi perilaku hidup bersih dan sehat',
                    'Pengelolaan pekarangan rumah untuk budidaya tanaman',
                    'Pembuatan biopori',
                    'Pembuatan hidroponik di pekarangan rumah',
                    'Lainnya...',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_pernyataan_belum_pernah_menerima_bantuan' => 'Surat pernyataan Calon Penerima belum pernah menerima bantuan rehabilitasi rumah',
                    'surat_keterangan_penghasilan' => 'Surat keterangan penghasilan dari Desa',
                    'surat_tanah' => 'Surat Tanah atau Sejenisnya',
                    'foto_kondisi_rumah' => 'Foto kondisi rumah calon penerima bantuan 3 sisi',
                ],
            ],
            'sosial' => [
                'formulir_items' => [
                    'Komunikasi, informasi dan edukasi dalam kesetaraan dan keadilan gender',
                    'Komunikasi, informasi dan edukasi dalam disabilitas',
                    'Komunikasi, informasi dan edukasi dalam kesiapsiagaan bencana',
                    'Komunikasi, informasi dan edukasi dalam inklusi sosial',
                    'Identifikasi dan pendataan fakir miskin/masyarakat tidak mampu',
                    'Penyaluran bantuan sosial',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'surat_pernyataan_tindak_lanjut' => 'Surat pernyataan dari Desa/Kelurahan untuk tindak lanjut'],
            ],
            'trantibumlinmas' => [
                'formulir_items' => [
                    'Penyuluhan dan rehabilitasi trauma pasca bencana',
                    'Komunikasi, informasi dan edukasi terhadap kesiapsiagaan bencana',
                    'Deteksi dini dan cegah dini gangguan trantibumlinmas',
                    'Pembinaan dan penyuluhan pelaksanaan patrol pengamanan',
                    'Pemberdayaan perlindungan masyarakat',
                    'Perbaikan poskamling',
                    'Penyediaan APAR',
                    'Penyediaan alat deteksi bencana',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK'],
            ],
        ];
        return $allData[$bidang_slug] ?? null;
    }

    public function show(Pengajuan $ajuan)
    {
        $this->authorize('viewAjuan', $ajuan);
        $user = Auth::user();
        $ajuan = Pengajuan::with([
            'user.posyandu',
            'bidang',
            'histories' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])->findOrFail($ajuan->id);

        if ($ajuan->revision_requested_at && $ajuan->status_pengajuan === 'Diproses') {

            $enableAutoReject = SystemSetting::get('enable_auto_reject', true);
            $debugMode = SystemSetting::get('revision_debug_mode', false);
            $debugMinutes = SystemSetting::get('revision_debug_minutes', 5);
            $productionDays = SystemSetting::get('auto_reject_days', 5);

            if ($enableAutoReject) {
                if ($debugMode) {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addMinutes($debugMinutes);
                } else {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addWeekdays($productionDays);
                }

                $hasRevision = History::where('pengajuan_id', $ajuan->id)
                    ->where('status', 'Revisi Submitted')
                    ->where('created_at', '>', $ajuan->revision_requested_at)
                    ->exists();

                if (now()->greaterThan($revisionDeadline) && !$hasRevision) {
                    $ajuan->update([
                        'status_pengajuan' => 'Ditolak',
                        'auto_rejected' => true,
                    ]);

                    History::create([
                        'pengajuan_id' => $ajuan->id,
                        'status' => 'Auto-Rejected',
                        'catatan' => "Pengajuan otomatis ditolak karena tidak ada revisi dalam {$productionDays} hari kerja.",
                        'diubah_oleh' => null,
                        'action_by_role' => null,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        $ajuan->load(['user', 'bidang', 'histories']);
        $templateData = $this->getBidangData($ajuan->bidang?->slug);
        if (!$templateData) {
            $templateData = ['formulir_items' => [], 'administrasi_items' => []];
        }

        $maxRevisionCount = SystemSetting::get('max_revision_count', 3);
        $canRevise = ($ajuan->revision_count ?? 0) < $maxRevisionCount;

        return view('ajuan.detail', [
            'ajuan'            => $ajuan,
            'templateData'     => $templateData,
            'currentUser'      => $user,
            'canRevise'        => $canRevise,
            'maxRevisionCount' => $maxRevisionCount,
        ]);
    }

    public function edit(Pengajuan $ajuan)
    {
        $this->authorize('update', $ajuan);

        if (!in_array($ajuan->status_pengajuan, ['Diproses', 'Ditolak'])) {
            return redirect()->route('ajuan.show', $ajuan)
                ->with('error', 'Pengajuan ini tidak dapat diedit karena statusnya: ' . $ajuan->status_pengajuan);
        }

        if ($ajuan->revision_requested_at) {

            $enableAutoReject = SystemSetting::get('enable_auto_reject', true);
            $debugMode = SystemSetting::get('revision_debug_mode', false);
            $debugMinutes = SystemSetting::get('revision_debug_minutes', 5);
            $productionDays = SystemSetting::get('auto_reject_days', 5);

            if ($enableAutoReject) {
                if ($debugMode) {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addMinutes($debugMinutes);
                } else {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addWeekdays($productionDays);
                }

                $hasRevision = History::where('pengajuan_id', $ajuan->id)
                    ->where('status', 'Revisi Submitted')
                    ->where('created_at', '>', $ajuan->revision_requested_at)
                    ->exists();

                if (now()->greaterThan($revisionDeadline) && !$hasRevision) {
                    $ajuan->update([
                        'status_pengajuan' => 'Ditolak',
                        'auto_rejected' => true,
                    ]);

                    History::create([
                        'pengajuan_id' => $ajuan->id,
                        'status' => 'Auto-Rejected',
                        'catatan' => "Pengajuan otomatis ditolak karena tidak ada revisi dalam {$productionDays} hari kerja.",
                        'diubah_oleh' => null,
                        'action_by_role' => null,
                        'created_at' => now(),
                    ]);

                    return redirect()->route('ajuan.index')
                        ->with('error', "Pengajuan sudah ditolak karena melewati batas waktu revisi ({$productionDays} hari kerja).");
                }
            }
        }

        $ajuan->load(['bidang', 'histories' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            abort(404, 'Definisi formulir untuk bidang ini tidak ditemukan.');
        }

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        return view('ajuan.edit', [
            'ajuan' => $ajuan,
            'allBidangs' => $allBidangs,
            'templateData' => $templateData,
        ]);
    }

    public function timeline($id)
    {
        $pengajuan = Pengajuan::with([
            'user',
            'bidang',
            'histories' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        $this->authorize('viewAjuan', $pengajuan);

        $revisionDeadline = null;
        if ($pengajuan->revision_requested_at) {
            $debugMode = SystemSetting::get('revision_debug_mode', false);
            $debugMinutes = SystemSetting::get('revision_debug_minutes', 5);
            $productionDays = SystemSetting::get('auto_reject_days', 5);

            if ($debugMode) {
                $revisionDeadline = \Carbon\Carbon::parse($pengajuan->revision_requested_at)
                    ->addMinutes($debugMinutes);
            } else {
                $revisionDeadline = \Carbon\Carbon::parse($pengajuan->revision_requested_at)
                    ->addWeekdays($productionDays);
            }
        }

        return view('ajuan.timeline', [
            'pengajuan' => $pengajuan,
            'revisionDeadline' => $revisionDeadline,
        ]);
    }

    public function update(UpdatePengajuanRequest $request, Pengajuan $ajuan)
    {
        $this->authorize('update', $ajuan);

        $user = Auth::user();
        $validated = $request->validated();

        if ($ajuan->revision_requested_at) {
            $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addWeekdays(5);
            if (now()->greaterThan($revisionDeadline)) {
                return redirect()->route('ajuan.show', $ajuan)
                    ->with('error', 'Masa revisi telah berakhir. Pengajuan ini tidak dapat diedit lagi.');
            }
        }

        $ajuan->load('bidang');

        $finalChecklistData = $validated['permohonan_items'];
        if (in_array('Lainnya...', $finalChecklistData, true) && !empty($validated['lainnya_text'])) {
            $finalChecklistData = array_map(
                fn($item) => $item === 'Lainnya...' ? 'Lainnya: ' . $validated['lainnya_text'] : $item,
                $finalChecklistData
            );
        }

        $dokumenData = $ajuan->administrasi_items ?? [];

        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            return redirect()->back()->with('error', 'Template bidang tidak ditemukan.');
        }

        $administrasiItemsTemplate = $templateData['administrasi_items'] ?? [];

        $validationRules = [];
        foreach (array_keys($administrasiItemsTemplate) as $key) {
            if (!isset($dokumenData[$key])) {
                if (in_array($key, ['ktp', 'kk', 'kartu_bpjs'])) {
                    $validationRules[$key] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
                } else {
                    $validationRules[$key] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
                }
            } else {
                $validationRules[$key] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];
            }
        }

        $request->validate($validationRules);

        foreach (array_keys($administrasiItemsTemplate) as $key) {
            if ($request->hasFile($key)) {
                if (isset($dokumenData[$key]) && !Str::startsWith($dokumenData[$key], 'data:')) {
                    Storage::disk('public')->delete($dokumenData[$key]);
                }

                $path = $this->compressAndStoreImage($request->file($key), 'ajuan_dokumen');
                $dokumenData[$key] = $path;
            }
        }

        $ajuan->update([
            'deskripsi_pengajuan' => $validated['deskripsi_pengajuan'],
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $dokumenData,
            'status_pengajuan' => 'Diproses',
            'revision_requested_at' => null,
            'sudah_verifikasi' => false,
            'kunjungan_lapangan' => false,
            'approved_by_ketua' => false,
        ]);

        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => 'Direvisi & Diajukan Kembali',
            'catatan' => 'Pengguna telah memperbarui pengajuan sesuai permintaan revisi.',
            'diubah_oleh' => $user->id,
            'action_by_role' => null,
            'created_at' => now(),
        ]);

        return redirect()->route('ajuan.show', $ajuan)
            ->with('success', 'Pengajuan berhasil diperbarui dan diajukan kembali. Menunggu verifikasi ulang dari kader.');
    }

    public function downloadDokumen(Pengajuan $ajuan, $key)
    {
        $ajuan->load(['user', 'bidang']);

        $dokumenData = $ajuan->administrasi_items;
        if (!isset($dokumenData) || !isset($dokumenData[$key])) {
            abort(404, 'Dokumen tidak ditemukan di dalam pengajuan ini.');
        }

        $fileData = $dokumenData[$key];

        $userName = Str::slug($ajuan->user->name, '_');
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        $label = $templateData['administrasi_items'][$key] ?? $key;
        $cleanLabel = Str::slug($label, '_');

        if (Str::startsWith($fileData, 'data:')) {
            try {
                list($type, $data) = explode(';', $fileData);
                list(, $data)      = explode(',', $data);

                $fileContents = base64_decode($data);

                $mime = str_replace('data:', '', $type);
                $extension = explode('/', $mime)[1] ?? 'png';

                $filename = "{$cleanLabel}-{$userName}.{$extension}";

                return response()->make($fileContents, 200, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            } catch (\Exception $e) {
                abort(404, 'Gagal memproses data file KTP/KK.');
            }
        } else {
            $path = $fileData;
            if (!Storage::disk('public')->exists($path)) {
                abort(404, 'File tidak ditemukan di storage.');
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = "{$cleanLabel}-{$userName}.{$extension}";

            $fullPath = Storage::disk('public')->path($path);

            return response()->download($fullPath, $filename);
        }
    }

    public function verifyAjuan(Request $request, Pengajuan $ajuan)
    {
        $user = Auth::user();
        $targetUser = $ajuan->user;

        if ($user->role === 'ketua-posyandu') {
            if ($ajuan->user->posyandu_id !== $user->posyandu_id) {
                abort(403, 'Ketua Posyandu hanya bisa takeover pengajuan di Posyandunya sendiri.');
            }
        } else {
            $this->authorize('verify', $ajuan);
        }

        $step = $request->input('verification_step');
        $statusHistory = '';
        $catatanHistory = $request->catatan;

        if (in_array($user->role, ['kader', 'ketua-posyandu', 'admin'])) {
            if ($step == 1) {
                $keputusan = $request->input('keputusan');

                $request->validate([
                    'keputusan' => 'required|in:lanjut,revisi,tolak',
                    'catatan' => 'required|string|min:10',
                ], [
                    'catatan.required' => 'Catatan wajib diisi.',
                    'catatan.min' => 'Catatan minimal 10 karakter.',
                ]);

                if ($keputusan === 'tolak') {
                    $ajuan->update([
                        'status_pengajuan' => 'Ditolak',
                        'sudah_verifikasi' => false,
                        'kunjungan_lapangan' => false,
                        'approved_by_ketua' => false,
                        'verified_formulir_items' => [],
                        'verified_administrasi_items' => [],
                    ]);

                    History::create([
                        'pengajuan_id' => $ajuan->id,
                        'status' => 'Ditolak',
                        'pilih_keputusan' => $keputusan,
                        'catatan' => $request->catatan,
                        'diubah_oleh' => $user->id,
                        'action_by_role' => $this->historyRole($user->role),
                        'created_at' => now(),
                    ]);

                    $targetUser->notify(new PengajuanStatusUpdated($ajuan, 'Ditolak', $request->catatan));

                    return redirect()->route('ajuan.index')->with('success', 'Pengajuan telah ditolak karena posyandu tidak sesuai.');
                }

                if ($keputusan === 'revisi') {
                    $maxRevisionCount = SystemSetting::get('max_revision_count', 3);
                    if ($ajuan->revision_count >= $maxRevisionCount) {
                        return redirect()->back()->with('error', "Revisi tidak dapat diminta lagi. Pengajuan sudah mencapai batas maksimal revisi ({$maxRevisionCount}x).");
                    }

                    $ajuan->update([
                        'status_pengajuan' => 'Diproses',
                        'revision_requested_at' => now(),
                        'revision_count' => $ajuan->revision_count + 1,
                    ]);

                    History::create([
                        'pengajuan_id' => $ajuan->id,
                        'status' => 'Revisi Diminta',
                        'pilih_keputusan' => $keputusan,
                        'catatan' => $request->catatan,
                        'diubah_oleh' => $user->id,
                        'action_by_role' => $this->historyRole($user->role),
                        'created_at' => now(),
                    ]);

                    $targetUser->notify(new PengajuanStatusUpdated($ajuan, 'Revisi Diminta', $request->catatan));

                    return redirect()->route('ajuan.index')->with('success', 'Revisi berhasil diminta. User memiliki 5 hari kerja untuk merevisi.');
                }

                if ($keputusan === 'lanjut') {
                    $submittedItems = $ajuan->formulir_items ?? [];
                    $submittedDocs = $ajuan->administrasi_items ?? [];

                    $request->validate([
                        'verified_formulir_items' => ['required', 'array', 'size:' . count($submittedItems)],
                        'verified_administrasi_items' => ['required', 'array', 'size:' . count($submittedDocs)],
                    ], [
                        'verified_formulir_items.size' => 'Detail permohonan belum lengkap. Centang semua item atau pilih revisi agar pemohon melengkapi.',
                        'verified_administrasi_items.size' => 'Dokumen administrasi belum lengkap. Centang semua dokumen atau pilih revisi agar pemohon melengkapi.',
                    ]);

                    $ajuan->update([
                        'status_pengajuan' => 'Diproses',
                        'sudah_verifikasi' => true,
                        'kunjungan_lapangan' => false,
                        'approved_by_ketua' => false,
                        'verified_formulir_items' => $request->input('verified_formulir_items'),
                        'verified_administrasi_items' => $request->input('verified_administrasi_items'),
                    ]);

                    $statusHistory = 'Menunggu Kunjungan';
                    $catatanHistory = $request->catatan ?? 'Dokumen terverifikasi. Menunggu jadwal kunjungan lapangan.';

                    History::create([
                        'pengajuan_id' => $ajuan->id,
                        'status' => $statusHistory,
                        'pilih_keputusan' => $keputusan,
                        'catatan' => $catatanHistory,
                        'diubah_oleh' => $user->id,
                        'action_by_role' => $this->historyRole($user->role),
                        'created_at' => now()
                    ]);

                    $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

                    return redirect()->route('ajuan.index')->with('success', 'Verifikasi dokumen berhasil. Silakan lakukan kunjungan lapangan.');
                }
            }

            if ($step == 2) {
                $request->validate([
                    'catatan_kunjungan' => 'required|string|min:10',
                    'foto_kunjungan' => 'required|array|min:1',
                    'foto_kunjungan.*' => 'image|mimes:jpeg,png,jpg|max:10240',
                ], [
                    'catatan_kunjungan.required' => 'Catatan kunjungan wajib diisi.',
                    'catatan_kunjungan.min' => 'Catatan minimal 10 karakter.',
                    'foto_kunjungan.required' => 'Foto kunjungan wajib diupload.',
                    'foto_kunjungan.min' => 'Minimal 1 foto kunjungan harus diupload.',
                    'foto_kunjungan.*.image' => 'File harus berupa gambar.',
                    'foto_kunjungan.*.max' => 'Ukuran foto maksimal 10MB.',
                ]);

                $fotoKunjungan = $ajuan->foto_kunjungan ?? [];
                if ($request->hasFile('foto_kunjungan')) {
                    foreach ($request->file('foto_kunjungan') as $foto) {
                        $path = $this->compressAndStoreImage($foto, 'foto_kunjungan');
                        $fotoKunjungan[] = $path;
                    }
                }

                $ajuan->update([
                    'kunjungan_lapangan' => true,
                    'status_pengajuan' => 'Diproses',
                    'foto_kunjungan' => $fotoKunjungan,
                ]);

                $statusHistory = 'Kunjungan Selesai';
                $catatanHistory = $request->catatan_kunjungan;

                History::create([
                    'pengajuan_id' => $ajuan->id,
                    'status' => $statusHistory,
                    'catatan' => $catatanHistory,
                    'diubah_oleh' => $user->id,
                    'action_by_role' => $this->historyRole($user->role),
                    'created_at' => now(),
                ]);

                $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

                return redirect()->route('ajuan.index')->with('success', 'Kunjungan lapangan berhasil dikonfirmasi. Menunggu persetujuan Ketua Posyandu.');
            }
        }

        // Step 3: Ketua Posyandu Approval
        if ($user->role === 'ketua-posyandu') {
            if ($step == 3) {
                $request->validate([
                    'keputusan' => 'required|in:ditindaklanjuti,tidak-ditindaklanjuti',
                    'catatan' => 'nullable|string|min:10',
                ], [
                    'keputusan.required' => 'Keputusan harus dipilih.',
                    'catatan.min' => 'Catatan minimal 10 karakter.',
                ]);

                if ($request->keputusan === 'ditindaklanjuti') {
                    $ajuan->update([
                        'status_pengajuan' => 'Diproses',
                        'approved_by_ketua' => true,
                        'approved_by_ketua_id' => $user->id,
                        'approved_by_ketua_at' => now(),
                    ]);

                    $statusHistory = 'Disetujui Ketua Posyandu';
                    $catatanHistory = $request->catatan ?? 'Pengajuan disetujui Ketua Posyandu dan siap dikirim ke Pemdes.';
                } else {
                    $ajuan->update([
                        'status_pengajuan' => 'Ditolak',
                        'approved_by_ketua' => false,
                        'approved_by_ketua_id' => $user->id,
                        'approved_by_ketua_at' => now(),
                    ]);

                    $statusHistory = 'Ditolak Ketua Posyandu';
                    $catatanHistory = $request->catatan;
                }

                History::create([
                    'pengajuan_id' => $ajuan->id,
                    'status' => $statusHistory,
                    'pilih_keputusan' => $request->keputusan,
                    'catatan' => $catatanHistory,
                    'diubah_oleh' => $user->id,
                    'action_by_role' => 'ketua-posyandu',
                    'created_at' => now(),
                ]);

                $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

                return redirect()->route('ajuan.index')->with('success', 'Keputusan berhasil disimpan.');
            }
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan pada proses verifikasi.');
    }

    public function submitToPemdes(Pengajuan $ajuan)
    {
        $user = Auth::user();

        if ($user->role !== 'ketua-posyandu') {
            AccessAudit::record(request(), $user, 'pengajuan', 'submit_to_pemdes', false, 403, [
                'target_pengajuan_id' => $ajuan->id,
            ]);
            abort(403, 'Akses ditolak.');
        }

        if (!$ajuan->approved_by_ketua || $ajuan->status_pengajuan !== 'Diproses') {
            return redirect()->back()->with('error', 'Pengajuan belum siap dikirim ke Pemdes.');
        }

        if ($ajuan->submitted_to_desa) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah pernah dikirim ke Pemdes.');
        }

        $ajuan->update([
            'submitted_to_desa' => true,
            'submitted_to_desa_at' => now(),
        ]);

        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => 'Diajukan ke Pemdes',
            'catatan' => 'Pengajuan diteruskan ke Kepala Desa untuk persetujuan akhir.',
            'diubah_oleh' => $user->id,
            'action_by_role' => 'ketua-posyandu',
            'created_at' => now(),
        ]);

        return redirect()->route('ajuan.index')->with('success', 'Pengajuan berhasil dikirim ke Pemdes.');
    }

    public function kadesApproval(Request $request, Pengajuan $ajuan)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['kades', 'bu-kades'])) {
            AccessAudit::record(request(), $user, 'pengajuan', 'kades_approval', false, 403, [
                'target_pengajuan_id' => $ajuan->id,
            ]);
            abort(403, 'Akses ditolak.');
        }

        if (!$ajuan->approved_by_ketua || $ajuan->status_pengajuan !== 'Diproses') {
            return redirect()->back()->with('error', 'Pengajuan belum siap ditindaklanjuti oleh Kades.');
        }

        $request->validate([
            'keputusan' => 'required|in:ditindaklanjuti,tidak-ditindaklanjuti',
            'tindak_lanjut' => 'required_if:keputusan,ditindaklanjuti|nullable|string|min:15',
            'catatan' => 'required|string|min:10',
        ], [
            'tindak_lanjut.required_if' => 'Tindak lanjut wajib diisi jika keputusan ditindaklanjuti.',
            'tindak_lanjut.min' => 'Tindak lanjut minimal 15 karakter.',
            'catatan.required' => 'Catatan wajib diisi.',
            'catatan.min' => 'Catatan minimal 10 karakter.',
        ]);

        $desaSubmission = [];
        if (!$ajuan->submitted_to_desa) {
            $desaSubmission = [
                'submitted_to_desa' => true,
                'submitted_to_desa_at' => now(),
            ];
        }

        if ($request->keputusan === 'ditindaklanjuti') {
            $ajuan->update(array_merge($desaSubmission, [
                'status_pengajuan' => 'Disetujui',
                'approved_by_kades' => true,
                'approved_by_kades_id' => $user->id,
                'approved_by_kades_at' => now(),
                'tindak_lanjut' => $request->tindak_lanjut ?? $ajuan->deskripsi_pengajuan,
            ]));

            $status = $user->role === 'kades' ? 'Disetujui Kades' : 'Disetujui Bu Kades';
        } else {
            $ajuan->update(array_merge($desaSubmission, [
                'status_pengajuan' => 'Ditolak',
            ]));

            $status = $user->role === 'kades' ? 'Ditolak Kades' : 'Ditolak Bu Kades';
        }

        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => $status,
            'pilih_keputusan' => $request->keputusan,
            'catatan' => $request->catatan ?? '-',
            'diubah_oleh' => $user->id,
            'action_by_role' => $user->role,
            'created_at' => now(),
        ]);

        return redirect()->route('ajuan.index')->with('success', 'Keputusan berhasil disimpan.');
    }
    public function cetak($id)
    {
        $ajuan = Pengajuan::with(['user', 'bidang', 'histories.diubahOleh', 'latestHistory.diubahOleh'])->findOrFail($id);

        $this->authorize('viewAjuan', $ajuan);

        foreach (['verified_formulir_items', 'verified_administrasi_items', 'formulir_items', 'administrasi_items'] as $key) {
            if (is_string($ajuan->$key)) {
                $ajuan->$key = json_decode($ajuan->$key, true) ?? [];
            }
        }

        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            $templateData = ['administrasi_items' => []];
        }

        $pdf = Pdf::loadView('ajuan.cetak', ['ajuan' => $ajuan, 'templateData' => $templateData]);

        $pdf->setPaper('A4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);
        return $pdf->stream('ajuan_' . $ajuan->id . '.pdf');
    }

    public function streamDokumen(Pengajuan $ajuan, $key)
    {
        $this->authorize('viewAjuan', $ajuan);

        $ajuan->load(['user', 'bidang']);

        $dokumenData = $ajuan->administrasi_items;
        if (!isset($dokumenData[$key])) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $fileData = $dokumenData[$key];
        $disk = Storage::disk('public');

        if (Str::startsWith($fileData, 'data:')) {
            try {
                list($meta, $data) = explode(',', $fileData);
                $mime = str_replace(['data:', ';base64'], '', explode(';', $meta)[0]);
                $fileContents = base64_decode($data);

                return response($fileContents, 200, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline',
                ]);
            } catch (\Exception $e) {
                abort(404, 'Gagal memproses data file.');
            }
        }

        if (!$disk->exists($fileData)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        $mime = mime_content_type(storage_path("app/public/{$fileData}"));
        $stream = $disk->readStream($fileData);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($fileData) . '"',
        ]);
    }

    public function showFotoKunjungan(Pengajuan $ajuan, int $index)
    {
        $this->authorize('viewAjuan', $ajuan);

        $fotoList = $ajuan->foto_kunjungan ?? [];

        if (!isset($fotoList[$index])) {
            abort(404, 'Foto kunjungan tidak ditemukan.');
        }

        $path = $fotoList[$index];

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File foto kunjungan tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($path);

        return response()->file($fullPath);
    }

    public function cetakRingkasan($id)
    {
        $ajuan = Pengajuan::with(['user.posyandu', 'bidang', 'ketuaPosyandu', 'kades'])->findOrFail($id);

        $this->authorize('viewAjuan', $ajuan);

        foreach (['formulir_items', 'administrasi_items', 'verified_formulir_items', 'verified_administrasi_items'] as $key) {
            if (is_string($ajuan->$key)) {
                $ajuan->$key = json_decode($ajuan->$key, true) ?? [];
            }
        }

        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            $templateData = ['administrasi_items' => []];
        }

        $pdf = Pdf::loadView('ajuan.cetak_ringkasan', [
            'ajuan' => $ajuan,
            'templateData' => $templateData
        ]);

        $pdf->setPaper('A4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        return $pdf->stream('ringkasan_ajuan_' . $ajuan->tracking_code . '.pdf');
    }

    public function cetakDokumen($id)
    {
        $ajuan = Pengajuan::with(['user.posyandu', 'bidang'])->findOrFail($id);

        $this->authorize('viewAjuan', $ajuan);

        foreach (['formulir_items', 'administrasi_items'] as $key) {
            if (is_string($ajuan->$key)) {
                $ajuan->$key = json_decode($ajuan->$key, true) ?? [];
            }
        }

        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            $templateData = ['administrasi_items' => []];
        }

        $administrasiItems = $ajuan->administrasi_items ?? [];

        $documentCounter = 1;

        $pdf = Pdf::loadView('ajuan.cetak_dokumen', [
            'ajuan' => $ajuan,
            'templateData' => $templateData,
            'administrasiItems' => $administrasiItems,
            'documentCounter' => $documentCounter,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        return $pdf->stream('dokumen_ajuan_' . $ajuan->tracking_code . '.pdf');
    }

    public function getItemsAjax($slug)
    {
        try {
            $bidang = BidangPengajuan::where('slug', $slug)->firstOrFail();

            $templateData = $this->getBidangData($slug);

            if (!$templateData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template tidak ditemukan untuk bidang ini'
                ], 404);
            }

            $items = $templateData['formulir_items'] ?? [];

            return response()->json([
                'success' => true,
                'items' => $items,
                'bidang_nama' => $bidang->nama_bidang,
                'bidang_slug' => $slug
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateTrackingCode()
    {
        do {
            $code = 'PGJ-' . date('Ym') . '-' . strtoupper(Str::random(5));
        } while (Pengajuan::where('tracking_code', $code)->exists());

        return $code;
    }

    public function showTrackingForm()
    {
        return view('pengajuan.track');
    }

    public function track(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:50',
        ]);

        $trackingCode = strtoupper(trim($request->tracking_code));

        $pengajuan = Pengajuan::with(['user', 'bidang'])
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$pengajuan) {
            return redirect()->route('login')
                ->with('track_error', 'Kode pengajuan tidak ditemukan. Pastikan Anda memasukkan kode yang benar.');
        }

        return view('ajuan.track-result', compact('pengajuan'));
    }

    public function generateQRCode(Pengajuan $pengajuan)
    {
        $this->authorize('viewAjuan', $pengajuan);

        $trackingUrl = route('ajuan.track.show', ['code' => $pengajuan->tracking_code]);

        return response()->json([
            'tracking_code' => $pengajuan->tracking_code,
            'tracking_url' => $trackingUrl,
            'qr_data' => $pengajuan->tracking_code,
        ]);
    }

    public function trackByCode($code)
    {
        $pengajuan = Pengajuan::with(['user', 'bidang'])
            ->where('tracking_code', strtoupper($code))
            ->first();

        if (!$pengajuan) {
            return redirect()->route('login')
                ->with('track_error', 'Kode pengajuan tidak ditemukan.');
        }

        return view('ajuan.track-result', compact('pengajuan'));
    }

    public function printBukti(Pengajuan $pengajuan)
    {
        $this->authorize('viewAjuan', $pengajuan);

        return view('ajuan.print-bukti', [
            'pengajuan' => $pengajuan
        ]);
    }

    public function trackShow(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20'
        ]);

        $trackingCode = strtoupper(trim($request->code));

        $pengajuan = Pengajuan::with(['user', 'bidang', 'histories' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$pengajuan) {
            return redirect()->route('login')
                ->with('tracking_error', 'Kode tracking "' . $trackingCode . '" tidak ditemukan. Pastikan Anda memasukkan kode dengan benar.');
        }

        return view('ajuan.track-result', [
            'pengajuan' => $pengajuan
        ]);
    }

    private function compressAndStoreImage(\Illuminate\Http\UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.jpg';
        $dir = storage_path('app/public/' . $directory);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $storagePath = $dir . '/' . $filename;

        $source = match ($extension) {
            'png'        => imagecreatefrompng($file->getPathname()),
            'jpg', 'jpeg' => imagecreatefromjpeg($file->getPathname()),
            default      => imagecreatefromjpeg($file->getPathname()),
        };

        // Flatten PNG transparency to white background before JPEG conversion
        if ($extension === 'png') {
            $w = imagesx($source);
            $h = imagesy($source);
            $bg = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagecopy($bg, $source, 0, 0, 0, 0, $w, $h);
            imagedestroy($source);
            $source = $bg;
        }

        // Resize if wider than 1920px
        $origW = imagesx($source);
        $origH = imagesy($source);
        if ($origW > 1920) {
            $newW = 1920;
            $newH = (int) round($origH * 1920 / $origW);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($source);
            $source = $resized;
        }

        imagejpeg($source, $storagePath, 80);
        imagedestroy($source);

        return $directory . '/' . $filename;
    }
}
