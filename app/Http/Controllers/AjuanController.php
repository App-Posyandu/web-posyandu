<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\History;
use App\Models\Pengajuan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\PengajuanStatusUpdated;
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
    public function index()
    {
        return view('ajuan.index');
    }

    private function getTargetUserId()
    {
        if (session()->has('ajuan_on_behalf_of_id') && Auth::user()->role !== 'masyarakat') {
            return session('ajuan_on_behalf_of_id');
        }
        return Auth::id();
    }

    public function requestRevision(Request $request, Pengajuan $ajuan)
    {
        $ajuan = Pengajuan::findOrFail($ajuan->id);

        // ✅ GET dari SystemSetting
        $autoRejectDays = SystemSetting::get('auto_reject_days', 5);

        $request->validate([
            'catatan' => 'required|string|max:500'
        ]);

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
            // ✅ FIX: enum constraint histories hanya allow: kader, ketua-posyandu, kades, ketua-kader
            'action_by_role' => in_array(Auth::user()->role, ['kader', 'ketua-posyandu', 'kades', 'ketua-kader'])
                ? Auth::user()->role : null,
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Revisi berhasil diminta. User memiliki {$autoRejectDays} hari kerja untuk merevisi.");
    }

    public function pilihLayanan(Request $request)
    {
        $user = Auth::user();
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

        // ✅ Handle user yang sudah dipilih
        if ($request->has('user_id')) {
            $masyarakatId = $request->query('user_id');
            session(['ajuan_on_behalf_of_id' => $masyarakatId]);

            if ($user->role === 'kader' && $user->bidang_id) {
                $bidangSlug = $user->bidang->slug;
                return redirect()->route('ajuan.create', $bidangSlug);
            } else {
                return redirect()->route('dashboard.partials.pilih-layanan');
            }
        }

        // ✅ Query dengan eager loading
        $query = User::with(['posyandu']) // ← TAMBAHKAN INI!
            ->where('role', 'masyarakat')
            ->whereNotNull('verified_at')
            ->orderBy('name');

        // ✅ Filter berdasarkan role
        if (in_array($user->role, ['kader', 'ketua-kader'])) {
            $query->where('posyandu_id', $user->posyandu_id);
        } elseif ($user->role === 'admin-kecamatan') {
            // Admin kecamatan hanya lihat masyarakat di kecamatannya
            $query->where('kecamatan_id', $user->kecamatan_id);
        } elseif ($user->role === 'kabid') {
            // Kabid hanya lihat masyarakat di kabupatennya
            $query->where('kabupaten_id', $user->kabupaten_id);
        }

        $masyarakatUsers = $query->get();

        return view('dashboard.partials.pilih-user', compact('masyarakatUsers'));
    }

    public function create($bidang_slug)
    {

        Session::forget('ajuan_data');

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

    public function storePermohonan(Request $request)
    {
        $permohonanItems = $request->input('permohonan_items', []);

        $request->validate([
            'bidang_pelayanan' => 'required|string|exists:bidang_pengajuans,slug',
            'deskripsi_pengajuan' => 'required|string|min:10',
        ]);

        $bidangSlug = $request->input('bidang_pelayanan');
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
        session()->put('ajuan_data.deskripsi_pengajuan', $request->input('deskripsi_pengajuan'));
        session()->put('ajuan_data.tanggal_permohonan', now());
        session()->put('ajuan_data.tindak_lanjut', $request->input('deskripsi_pengajuan')); // Sama dengan deskripsi

        if ($request->has('lainnya_text') && !empty($request->input('lainnya_text'))) {
            session()->put('ajuan_data.lainnya_text', $request->input('lainnya_text'));
        }

        return redirect()->route('ajuan.create.administrasi');
    }

    public function createAdministrasi()
    {

        $ajuanData = session('ajuan_data');
        $user = Auth::user();
        if (! $ajuanData) {
            return redirect()->route('dashboard');
        }

        return view('components.ajuan.administrasi-ajuan.index', [
            'items' => $ajuanData['administrasi_items_template'],
            'userKtp' => $user->ktp,
            'userKk' => $user->kk
        ]);
    }

    public function storeAdministrasi(Request $request)
    {
        $ajuanData = session('ajuan_data');
        $user = Auth::user();

        $targetUserId = $this->getTargetUserId();
        if (!$targetUserId) {
            return redirect()->route('dashboard')->with('error', 'User target tidak ditemukan.');
        }

        if (!$ajuanData || !$user) {
            return redirect()->route('dashboard')->with('error', 'Sesi tidak valid.');
        }

        $validationRules = [];
        foreach ($ajuanData['administrasi_items_template'] as $key => $item) {
            if ($key === 'ktp') {
                $validationRules[$key] = ['required_if:ktp_mode,upload', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
            } elseif ($key === 'kk') {
                $validationRules[$key] = ['required_if:kk_mode,upload', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
            } elseif ($key === 'kartu_bpjs') {
                $validationRules[$key] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
            } else {
                $validationRules[$key] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
            }
        }
        $validationRules['agreement'] = ['required'];

        $request->validate($validationRules);

        $uploadedFiles = [];
        foreach (array_keys($ajuanData['administrasi_items_template']) as $key) {

            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('ajuan_dokumen', 'public');
                $uploadedFiles[$key] = $path;
            } elseif ($key === 'ktp' && $request->input('ktp_mode') === 'claimed' && $user->ktp) {
                $uploadedFiles[$key] = $user->ktp;
            } elseif ($key === 'kk' && $request->input('kk_mode') === 'claimed' && $user->kk) {
                $uploadedFiles[$key] = $user->kk;
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
            'user_id' => $targetUserId,
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
        if (!Gate::forUser(Auth::user())->check('viewAjuan', $ajuan)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat pengajuan ini.');
        }
        $user = Auth::user();
        $ajuan = Pengajuan::with([
            'user.posyandu',
            'bidang',
            'histories' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])->findOrFail($ajuan->id);

        // ✅ AUTO-REJECT jika expired (sama seperti di edit)
        if ($ajuan->revision_requested_at && $ajuan->status_pengajuan === 'Diproses') {

            // Get settings from database
            $enableAutoReject = SystemSetting::get('enable_auto_reject', true);
            $debugMode = SystemSetting::get('revision_debug_mode', false);
            $debugMinutes = SystemSetting::get('revision_debug_minutes', 5);
            $productionDays = SystemSetting::get('auto_reject_days', 5);

            if ($enableAutoReject) {
                // Calculate deadline based on mode
                if ($debugMode) {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addMinutes($debugMinutes);
                } else {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addWeekdays($productionDays);
                }

                // Check if deadline passed and no revision submitted
                $hasRevision = History::where('pengajuan_id', $ajuan->id)
                    ->where('status', 'Revisi Submitted')
                    ->where('created_at', '>', $ajuan->revision_requested_at)
                    ->exists();

                if (now()->greaterThan($revisionDeadline) && !$hasRevision) {
                    // Auto-reject
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
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            $templateData = ['formulir_items' => [], 'administrasi_items' => []];
        }
        return view('ajuan.detail', [
            'ajuan' => $ajuan,
            'templateData' => $templateData,
            'currentUser' => $user,
        ]);
    }

    public function edit(Pengajuan $ajuan)
    {
        // ✅ Cek apakah user berhak mengedit
        if (Auth::user()->id !== $ajuan->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pengajuan ini.');
        }

        // ✅ Cek apakah pengajuan masih bisa diedit
        if (!in_array($ajuan->status_pengajuan, ['Diproses', 'Ditolak'])) {
            return redirect()->route('ajuan.show', $ajuan)
                ->with('error', 'Pengajuan ini tidak dapat diedit karena statusnya: ' . $ajuan->status_pengajuan);
        }

        // ✅✅ AUTO-REJECT jika masa revisi sudah expired
        if ($ajuan->revision_requested_at) {

            // Get settings from database
            $enableAutoReject = SystemSetting::get('enable_auto_reject', true);
            $debugMode = SystemSetting::get('revision_debug_mode', false);
            $debugMinutes = SystemSetting::get('revision_debug_minutes', 5);
            $productionDays = SystemSetting::get('auto_reject_days', 5);

            if ($enableAutoReject) {
                // Calculate deadline
                if ($debugMode) {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addMinutes($debugMinutes);
                } else {
                    $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addWeekdays($productionDays);
                }

                // Check if deadline passed
                $hasRevision = History::where('pengajuan_id', $ajuan->id)
                    ->where('status', 'Revisi Submitted')
                    ->where('created_at', '>', $ajuan->revision_requested_at)
                    ->exists();

                if (now()->greaterThan($revisionDeadline) && !$hasRevision) {
                    // Auto-reject
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

        // ✅ Load relasi yang diperlukan
        $ajuan->load(['bidang', 'histories' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // ✅ Ambil template data berdasarkan bidang pengajuan
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            abort(404, 'Definisi formulir untuk bidang ini tidak ditemukan.');
        }

        // ✅ Ambil semua bidang (untuk future enhancement jika diperlukan)
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

        // ✅ Calculate revision deadline if applicable
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

    public function update(Request $request, Pengajuan $ajuan)
    {
        $user = Auth::user();

        // ✅ Cek apakah user berhak mengupdate
        if ($user->id !== $ajuan->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pengajuan ini.');
        }

        // ✅ Cek apakah masa revisi sudah expired
        if ($ajuan->revision_requested_at) {
            $revisionDeadline = \Carbon\Carbon::parse($ajuan->revision_requested_at)->addWeekdays(5);
            if (now()->greaterThan($revisionDeadline)) {
                return redirect()->route('ajuan.show', $ajuan)
                    ->with('error', 'Masa revisi telah berakhir. Pengajuan ini tidak dapat diedit lagi.');
            }
        }

        // ✅ Validasi input
        $request->validate([
            'deskripsi_pengajuan' => 'required|string|min:10',
            'permohonan_items' => 'required|array|min:1',
            'permohonan_items.*' => 'string',
            'lainnya_text' => 'nullable|string|max:500',
        ], [
            'deskripsi_pengajuan.required' => 'Deskripsi pengajuan wajib diisi.',
            'deskripsi_pengajuan.min' => 'Deskripsi pengajuan minimal 10 karakter.',
            'permohonan_items.required' => 'Pilih minimal 1 item permohonan.',
            'permohonan_items.min' => 'Pilih minimal 1 item permohonan.',
        ]);

        $ajuan->load('bidang');

        // ✅ Proses item permohonan dengan "Lainnya"
        $finalChecklistData = $request->input('permohonan_items', []);
        if (in_array('Lainnya...', $finalChecklistData) && $request->filled('lainnya_text')) {
            $finalChecklistData = array_map(
                fn($item) => $item === 'Lainnya...' ? 'Lainnya: ' . $request->lainnya_text : $item,
                $finalChecklistData
            );
        }

        // ✅ Ambil data dokumen yang sudah ada
        $dokumenData = $ajuan->administrasi_items ?? [];

        // ✅ Ambil template administrasi untuk validasi
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            return redirect()->back()->with('error', 'Template bidang tidak ditemukan.');
        }

        $administrasiItemsTemplate = $templateData['administrasi_items'] ?? [];

        // ✅ Validasi file yang di-upload (jika ada)
        $validationRules = [];
        foreach (array_keys($administrasiItemsTemplate) as $key) {
            // File bersifat opsional saat update (kecuali belum pernah diupload)
            if (!isset($dokumenData[$key])) {
                // Jika dokumen belum ada, wajib upload (kecuali KTP/KK/BPJS yang opsional)
                if (in_array($key, ['ktp', 'kk', 'kartu_bpjs'])) {
                    $validationRules[$key] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
                } else {
                    $validationRules[$key] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
                }
            } else {
                // Jika dokumen sudah ada, upload baru bersifat opsional
                $validationRules[$key] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
            }
        }

        $request->validate($validationRules);

        // ✅ Update dokumen jika ada file baru yang diupload
        foreach (array_keys($administrasiItemsTemplate) as $key) {
            if ($request->hasFile($key)) {
                // Hapus file lama jika ada dan bukan base64
                if (isset($dokumenData[$key]) && !Str::startsWith($dokumenData[$key], 'data:')) {
                    Storage::disk('public')->delete($dokumenData[$key]);
                }

                // Upload file baru
                $path = $request->file($key)->store('ajuan_dokumen', 'public');
                $dokumenData[$key] = $path;
            }
        }

        // ✅ Update pengajuan
        $ajuan->update([
            'deskripsi_pengajuan' => $request->deskripsi_pengajuan,
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $dokumenData,
            'status_pengajuan' => 'Diproses',
            // 'revision_requested_at' => null, // Reset revision request
            'sudah_verifikasi' => false, // Reset verifikasi
            'kunjungan_lapangan' => false, // Reset kunjungan
            'approved_by_ketua' => false, // Reset approval
        ]);

        // ✅ Tambah history
        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => 'Direvisi & Diajukan Kembali',
            'catatan' => 'Pengguna telah memperbarui pengajuan sesuai permintaan revisi.',
            'diubah_oleh' => $user->id,
            'action_by_role' => null, // ✅ FIX: 'masyarakat' tidak ada di enum constraint histories
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

        $this->authorize('verify', $ajuan);

        $step = $request->input('verification_step');
        $statusHistory = '';
        $catatanHistory = $request->catatan;

        // ===============================================
        // == KADER: STEP 1 & 2
        // ===============================================
        if ($user->role === 'kader') {

            // ===============================================
            // == STEP 1: Verifikasi Dokumen
            // ===============================================
            if ($step == 1) {
                $keputusan = $request->input('keputusan');

                // Validasi keputusan
                $request->validate([
                    'keputusan' => 'required|in:lanjut,revisi,tolak',
                ]);

                // Validasi catatan: required jika revisi atau tolak
                if (in_array($keputusan, ['revisi', 'tolak'])) {
                    $request->validate([
                        'catatan' => 'required|string|min:10',
                    ], [
                        'catatan.required' => 'Catatan wajib diisi jika memilih revisi atau tolak.',
                        'catatan.min' => 'Catatan minimal 10 karakter.',
                    ]);
                }

                // === TOLAK ===
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
                        'status' => 'Ditolak - Posyandu Salah',
                        'catatan' => $request->catatan,
                        'diubah_oleh' => $user->id,
                        'action_by_role' => 'kader',
                        'created_at' => now(),
                    ]);

                    $targetUser->notify(new PengajuanStatusUpdated($ajuan, 'Ditolak', $request->catatan));

                    return redirect()->route('ajuan.index')->with('success', 'Pengajuan telah ditolak karena posyandu tidak sesuai.');
                }

                // === REVISI ===
                if ($keputusan === 'revisi') {
                    $ajuan->update([
                        'status_pengajuan' => 'Diproses',
                        'revision_requested_at' => now(),
                        'revision_count' => $ajuan->revision_count + 1,
                    ]);

                    History::create([
                        'pengajuan_id' => $ajuan->id,
                        'status' => 'Revisi Diminta',
                        'catatan' => $request->catatan,
                        'diubah_oleh' => $user->id,
                        'action_by_role' => 'kader',
                        'created_at' => now(),
                    ]);

                    $targetUser->notify(new PengajuanStatusUpdated($ajuan, 'Revisi Diminta', $request->catatan));

                    return redirect()->route('ajuan.index')->with('success', 'Revisi berhasil diminta. User memiliki 5 hari kerja untuk merevisi.');
                }

                // === LANJUT KE KUNJUNGAN ===
                if ($keputusan === 'lanjut') {
                    $submittedItems = $ajuan->formulir_items ?? [];
                    $submittedDocs = $ajuan->administrasi_items ?? [];

                    $request->validate([
                        'verified_formulir_items' => ['required', 'array', 'size:' . count($submittedItems)],
                        'verified_administrasi_items' => ['required', 'array', 'size:' . count($submittedDocs)],
                    ], [
                        'verified_formulir_items.size' => 'Semua item permohonan harus dicentang untuk lanjut.',
                        'verified_administrasi_items.size' => 'Semua dokumen administrasi harus dicentang untuk lanjut.',
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
                        'catatan' => $catatanHistory,
                        'diubah_oleh' => $user->id,
                        'action_by_role' => 'kader',
                        'created_at' => now()
                    ]);

                    $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

                    return redirect()->route('ajuan.index')->with('success', 'Verifikasi dokumen berhasil. Silakan lakukan kunjungan lapangan.');
                }
            }

            // ===============================================
            // == STEP 2: Kunjungan Lapangan
            // ===============================================
            if ($step == 2) {
                $request->validate([
                    'catatan_kunjungan' => 'required|string|min:10',
                    'foto_kunjungan.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                ], [
                    'catatan_kunjungan.required' => 'Catatan kunjungan wajib diisi.',
                    'catatan_kunjungan.min' => 'Catatan minimal 10 karakter.',
                    'foto_kunjungan.*.image' => 'File harus berupa gambar.',
                    'foto_kunjungan.*.max' => 'Ukuran foto maksimal 2MB.',
                ]);

                // Upload foto jika ada
                $fotoKunjungan = $ajuan->foto_kunjungan ?? [];
                if ($request->hasFile('foto_kunjungan')) {
                    foreach ($request->file('foto_kunjungan') as $foto) {
                        $path = $foto->store('foto_kunjungan', 'public');
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
                    'action_by_role' => 'kader',
                    'created_at' => now(),
                ]);

                $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

                return redirect()->route('ajuan.index')->with('success', 'Kunjungan lapangan berhasil dikonfirmasi. Menunggu persetujuan Ketua Posyandu.');
            }
        }

        // ===============================================
        // == KETUA POSYANDU: STEP 3
        // ===============================================
        if ($user->role === 'ketua-posyandu') {
            if ($step == 3) {
                $request->validate([
                    'keputusan' => 'required|in:ditindaklanjuti,tidak-ditindaklanjuti',
                    'catatan' => 'nullable|string|min:15|required_if:keputusan,tidak-ditindaklanjuti',
                ], [
                    'keputusan.required' => 'Keputusan harus dipilih.',
                    'catatan.required_if' => 'Catatan wajib diisi jika menolak.',
                ]);

                if ($request->keputusan === 'ditindaklanjuti') {
                    $ajuan->update([
                        'status_pengajuan' => 'Sesuai',
                        'approved_by_ketua' => true,
                        'approved_by_ketua_id' => $user->id,
                        'approved_by_ketua_at' => now(),
                    ]);

                    $statusHistory = 'Disetujui Ketua Posyandu';
                    $catatanHistory = $request->catatan ?? 'Pengajuan disetujui dan siap diajukan ke Pemdes.';
                } else {
                    $ajuan->update([
                        'status_pengajuan' => 'Ditolak',
                    ]);

                    $statusHistory = 'Ditolak Ketua Posyandu';
                    $catatanHistory = $request->catatan;
                }

                History::create([
                    'pengajuan_id' => $ajuan->id,
                    'status' => $statusHistory,
                    'catatan' => $catatanHistory,
                    'diubah_oleh' => $user->id,
                    'action_by_role' => 'ketua-posyandu',
                    'created_at' => now(),
                ]);

                $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

                return redirect()->route('ajuan.index')->with('success', 'Keputusan berhasil disimpan.');
            }
        }

        // Fallback
        return redirect()->back()->with('error', 'Terjadi kesalahan pada proses verifikasi.');
    }

    public function submitToPemdes(Pengajuan $ajuan)
    {
        $user = Auth::user();

        if ($user->role !== 'ketua-posyandu') {
            abort(403);
        }

        if ($ajuan->status_pengajuan !== 'Sesuai') {
            return redirect()->back()->with('error', 'Pengajuan belum siap dikirim ke Pemdes.');
        }

        $ajuan->update([
            'status_pengajuan' => 'Diajukan ke Desa',
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

        if ($user->role !== 'kades') {
            abort(403);
        }

        $request->validate([
            'keputusan' => 'required|in:diajukan,tidak-diajukan',
            'tindak_lanjut' => 'nullable|string',
            'catatan' => 'nullable|string|min:15|required_if:keputusan,tidak-diajukan',
        ], [
            'catatan.required_if' => 'Catatan wajib diisi jika menolak.',
        ]);

        if ($request->keputusan === 'diajukan') {
            $ajuan->update([
                'status_pengajuan' => 'Disetujui',
                'approved_by_kades' => true,
                'approved_by_kades_id' => $user->id,
                'approved_by_kades_at' => now(),
                'tindak_lanjut' => $request->tindak_lanjut ?? $ajuan->deskripsi_pengajuan,
            ]);

            $status = 'Disetujui Kades';
        } else {
            $ajuan->update([
                'status_pengajuan' => 'Ditolak',
            ]);

            $status = 'Ditolak Kades';
        }

        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => $status,
            'catatan' => $request->catatan ?? '-',
            'diubah_oleh' => $user->id,
            'action_by_role' => 'kades',
            'created_at' => now(),
        ]);

        return redirect()->route('ajuan.index')->with('success', 'Keputusan berhasil disimpan.');
    }
    //cetak detail ajuan
    public function cetak($id)
    {
        $ajuan = Pengajuan::with(['user', 'bidang', 'histories.diubahOleh', 'latestHistory.diubahOleh'])->findOrFail($id);

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
        $ajuan->load(['user', 'bidang']);

        $dokumenData = $ajuan->administrasi_items;
        if (!isset($dokumenData[$key])) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $fileData = $dokumenData[$key];
        $disk = Storage::disk('public');

        // Jika data base64
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

        // Jika file path di storage
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

    /**
     * Cetak Ringkasan Pengajuan (1 halaman) - PDF
     */
    public function cetakRingkasan($id)
    {
        $ajuan = Pengajuan::with(['user.posyandu', 'bidang', 'ketuaPosyandu', 'kades'])->findOrFail($id);

        // Decode JSON fields
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

    /**
     * Cetak Dokumen Administrasi (1 halaman per dokumen) - PDF
     */
    public function cetakDokumen($id)
    {
        $ajuan = Pengajuan::with(['user.posyandu', 'bidang'])->findOrFail($id);

        // Decode JSON fields
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
            'administrasiItems' => $administrasiItems,  // ← TAMBAHKAN
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

    /**
     * ✅ Generate unique tracking code untuk pengajuan
     * Format: PGJ-YYYYMM-XXXXX
     * Example: PGJ-202501-A1B2C
     */
    private function generateTrackingCode()
    {
        do {
            // Format: PGJ-202501-AB123
            $code = 'PGJ-' . date('Ym') . '-' . strtoupper(Str::random(5));
        } while (Pengajuan::where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * ✅ Show tracking form (public, no auth required)
     */
    public function showTrackingForm()
    {
        return view('pengajuan.track');
    }

    /**
     * ✅ Track pengajuan by tracking code (public, no auth required)
     */
    public function track(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:50',
        ]);

        // Normalize tracking code (remove spaces, convert to uppercase)
        $trackingCode = strtoupper(trim($request->tracking_code));

        // Find pengajuan by tracking code
        $pengajuan = Pengajuan::with(['user', 'bidang'])
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$pengajuan) {
            return redirect()->route('login')
                ->with('track_error', 'Kode pengajuan tidak ditemukan. Pastikan Anda memasukkan kode yang benar.');
        }

        // Show tracking result
        return view('ajuan.track-result', compact('pengajuan'));
    }

    /**
     * ✅ Generate QR Code untuk tracking
     * Dapat digunakan untuk cetak bukti pengajuan
     */
    public function generateQRCode(Pengajuan $pengajuan)
    {
        // URL untuk tracking
        $trackingUrl = route('ajuan.track.show', ['code' => $pengajuan->tracking_code]);

        // Generate QR Code menggunakan library (misal: simplesoftwareio/simple-qrcode)
        // return QrCode::size(200)->generate($trackingUrl);

        // Atau return data untuk generate di frontend
        return response()->json([
            'tracking_code' => $pengajuan->tracking_code,
            'tracking_url' => $trackingUrl,
            'qr_data' => $pengajuan->tracking_code,
        ]);
    }

    /**
     * ✅ Track by direct URL (untuk QR Code scan)
     */
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

    /**
     * Print bukti pengajuan dengan tracking code
     */
    public function printBukti(Pengajuan $pengajuan)
    {
        $user = Auth::user();
        // Authorization check
        if (
            $user->id !== $pengajuan->user_id &&
            !in_array($user->role, ['admin', 'kader', 'operator-desa', 'ketua-kader', 'ketua-posyandu'])
        ) {
            abort(403, 'Unauthorized');
        }

        return view('ajuan.print-bukti', [
            'pengajuan' => $pengajuan
        ]);
    }

    /**
     * Show tracking result
     */
    public function trackShow(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20'
        ]);

        $trackingCode = strtoupper(trim($request->code));

        // Find pengajuan by tracking code
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
}
