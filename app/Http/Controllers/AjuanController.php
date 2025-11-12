<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\History;
use App\Models\Pengajuan;
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
    public function index(User $user, Request $request)
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

    public function pilihLayanan()
    {
        $user = Auth::user();
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

        if ($user->role === 'kader' && $user->bidang_id) {
            // Kader hanya bisa pilih bidangnya sendiri
            $allBidangs = BidangPengajuan::where('id', $user->bidang_id)->get();
        } else {
            // Role lain bisa pilih semua bidang
            $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();
        }

        return view('dashboard.partials.pilih-layanan', compact('allBidangs', 'colors', 'icons'));
    }

    public function pilihUser()
    {
        $user = Auth::user();
        $query = User::where('role', 'masyarakat')
            ->whereNotNull('verified_at')
            ->orderBy('name');

        if (in_array($user->role, ['kader', 'ketua-kader'])) {
            $query->where('posyandu_id', $user->posyandu_id);
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
        // dd($request->all());
        $permohonanItems = $request->input('permohonan_items', []);
        $request->validate([
            'deskripsi_pengajuan' => 'required|string|min:10',
        ]);
        // $lainnyaText = $request->input('lainnya_text');

        // if (in_array('Lainnya...', $permohonanItems) && !empty($lainnyaText)) {
        //     $finalChecklist = array_map(function ($item) use ($lainnyaText) {
        //         return $item === 'Lainnya...' ? 'Lainnya: ' . $lainnyaText : $item;
        //     }, $permohonanItems);
        // } else {
        //     $finalChecklist = $permohonanItems;
        // }

        session()->put('ajuan_data.selected_formulir_items', $permohonanItems);
        session()->put('ajuan_data.deskripsi_pengajuan', $request->input('deskripsi_pengajuan'));

        // 3. Simpan juga teks dari input "Lainnya..." jika ada
        if ($request->has('lainnya_text')) {
            session()->put('ajuan_data.lainnya_text', $request->input('lainnya_text'));
        }

        // session()->put('components.ajuan.selected_formulir.index', $finalChecklist);
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
                $validationRules[$key] = ['required_if:ktp_mode,upload', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
            } elseif ($key === 'kk') {
                $validationRules[$key] = ['required_if:kk_mode,upload', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
            } elseif ($key === 'kartu_bpjs') {
                $validationRules[$key] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
            } else {
                $validationRules[$key] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
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

        $pengajuan = Pengajuan::create([
            'user_id' => $targetUserId,
            'bidang_id' => $ajuanData['bidang_id'],
            'status_pengajuan' => 'Diproses',
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $uploadedFiles,
            'deskripsi_pengajuan' => $ajuanData['deskripsi_pengajuan'] ?? 'Tidak ada deskripsi.',
        ]);

        History::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'Diajukan',
            'catatan' => 'Pengajuan baru telah dibuat oleh ' . $user->name . '.',
            'diubah_oleh' => $user->id,
            'created_at' => now(),
        ]);

        // Session::forget('ajuan_data');
        // session()->put('ajuan_data.uploaded_files', $uploadedFiles);
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
        $ajuan->load(['user', 'bidang', 'histories']);
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            $templateData = ['formulir_items' => [], 'administrasi_items' => []];
        }
        return view('ajuan.detail', [
            'ajuan' => $ajuan,
            'templateData' => $templateData,
        ]);
    }

    public function edit(Pengajuan $ajuan)
    {
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            abort(404, 'Definisi formulir untuk bidang ini tidak ditemukan.');
        }

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        return view('ajuan.edit', [
            'ajuan' => $ajuan, // Data isian lama
            'allBidangs' => $allBidangs, // Untuk dropdown
            'templateData' => $templateData, // TEMPLATE formulir
        ]);
    }

    public function update(Request $request, Pengajuan $ajuan)
    {
        // Terapkan aturan 'update' dari policy.
        // $this->authorize('update', $ajuan);
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'deskripsi_pengajuan' => 'required|string|min:10',
            // Tambahkan validasi lain jika diperlukan
        ]);

        $ajuan->load('bidang');

        // Ambil data checklist yang baru
        $finalChecklistData = $request->input('permohonan_items', []);
        if (in_array('Lainnya...', $finalChecklistData) && $request->filled('lainnya_text')) {
            $finalChecklistData = array_map(fn($item) => $item === 'Lainnya...' ? 'Lainnya: ' . $request->lainnya_text : $item, $finalChecklistData);
        }

        // Ambil data file yang sudah ada
        $dokumenData = $ajuan->administrasi_items;
        // Perbarui file jika ada file baru yang diunggah
        $administrasiItemsTemplate = $ajuan->administrasi_items ?? [];
        // dd($ajuan->bidang);
        foreach (array_keys($administrasiItemsTemplate) as $key) {
            if ($request->hasFile($key)) {
                if (isset($dokumenData[$key])) {
                    Storage::disk('public')->delete($dokumenData[$key]);
                }
                $path = $request->file($key)->store('ajuan_dokumen', 'public');
                $dokumenData[$key] = $path;
            }
        }

        // Update data di database
        $ajuan->update([
            'deskripsi_pengajuan' => $request->deskripsi_pengajuan,
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $dokumenData,
            'status_pengajuan' => 'Diproses',
        ]);

        // Buat catatan history baru
        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => 'Direvisi & Diajukan Kembali',
            'catatan' => 'Pengguna telah memperbarui pengajuan.',
            'diubah_oleh' => $user->id,
            'created_at' => now(),
        ]);

        return redirect()->route('ajuan.show', $ajuan)->with('success', 'Pengajuan berhasil diperbarui dan diajukan kembali.');
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

                // Buat response download manual
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
        $user = Auth::user(); // Kader yang sedang login
        $targetUser = $ajuan->user; // Masyarakat yang punya ajuan

        // Sebaiknya di-aktifkan untuk keamanan
        $this->authorize('verify', $ajuan);

        $step = $request->input('verification_step');
        $statusHistory = '';
        $catatanHistory = $request->catatan;

        // ===============================================
        // == ALUR 1: TAHAP 1 (Verifikasi Dokumen)
        // ===============================================
        if ($step == 1) {

            // --- Jika Kader mengklik "Tolak Langsung" ---
            if ($request->input('tolak_langsung') === 'Ditolak') {
                $request->validate(
                    ['catatan' => 'required'],
                    ['catatan.required' => 'Catatan wajib diisi jika menolak.']
                );

                // <-- PERBAIKAN: Definisikan status & catatan di sini
                $statusDitolak = 'Ditolak';
                $catatanDitolak = $request->catatan;
                $ajuan->update([
                    'status_pengajuan' => $statusDitolak,
                    'sudah_verifikasi' => true, // Selesai verifikasi (ditolak)
                    'kunjungan_lapangan' => false, // Tidak jadi kunjungan
                    'ttd_kader' => true,
                ]);

                // <-- PERBAIKAN: Tambahkan History::create() yang hilang
                History::create([
                    'pengajuan_id' => $ajuan->id,
                    'status' => $statusDitolak,
                    'catatan' => $catatanDitolak,
                    'diubah_oleh' => $user->id,
                    'created_at' => now(),
                ]);

                // <-- PERBAIKAN: Kirim notifikasi DENGAN data yang benar
                $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusDitolak, $catatanDitolak));

                return redirect()->route('ajuan.index')->with('success', 'Pengajuan telah ditolak.');
            }

            // --- Jika Kader mengklik "Setujui Verifikasi" ---
            $submittedItems = $ajuan->formulir_items ?? [];
            $submittedDocs = $ajuan->administrasi_items ?? [];

            $request->validate([
                'verified_formulir_items' => ['required', 'array', 'size:' . count($submittedItems)],
                'verified_administrasi_items' => ['required', 'array', 'size:' . count($submittedDocs)],
            ], [
                'verified_formulir_items.size' => 'Semua item permohonan harus dicentang untuk lanjut.',
                'verified_administrasi_items.size' => 'Semua dokumen administrasi harus dicentang untuk lanjut.',
            ]);

            // PERBAIKAN: Set kunjungan_lapangan ke true secara otomatis
            $ajuan->update([
                'status_pengajuan' => 'Diproses',
                'sudah_verifikasi' => true,
                'kunjungan_lapangan' => true, // <-- Otomatis wajib kunjungan
            ]);

            $statusHistory = 'Menunggu Kunjungan';
            $catatanHistory = 'Dokumen terverifikasi. Menunggu jadwal kunjungan lapangan.';

            History::create([
                'pengajuan_id' => $ajuan->id,
                'status' => $statusHistory,
                'catatan' => $catatanHistory,
                'diubah_oleh' => $user->id,
                'created_at'=>now()
            ]);

            $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

            // Kembali ke halaman detail (sekarang akan menampilkan Tahap 2)
            return redirect()->route('ajuan.show', $ajuan)->with('success', 'Verifikasi dokumen berhasil. Silakan lakukan kunjungan lapangan dan update hasilnya.');
        }

        // ===============================================
        // == ALUR 2: TAHAP 2 (Keputusan Pasca Kunjungan)
        // ===============================================
        elseif ($step == 2) {
            $request->validate([
                'status' => ['required', 'in:Disetujui,Ditolak'], // Status akhir wajib
                'catatan' => ['nullable', 'string', 'required_if:status,Ditolak'],
                'ttd_kader' => ['required'], // TTD Kader wajib
            ]);

            $statusAkhir = $request->status; // 'Disetujui' atau 'Ditolak'
            $statusHistory = $statusAkhir;
            $catatanHistory = $request->catatan ?? "Pengajuan $statusAkhir setelah kunjungan lapangan.";

            $ajuan->update([
                'status_pengajuan' => $statusAkhir,
                'kunjungan_lapangan' => true, // Tetap true (karena sudah dilakukan)
                'ttd_kader' => $request->has('ttd_kader'),
            ]);

            History::create([
                'pengajuan_id' => $ajuan->id,
                'status' => $statusHistory,
                'catatan' => $catatanHistory,
                'diubah_oleh' => $user->id,
                'created_at' => now(),
            ]);

            $targetUser->notify(new PengajuanStatusUpdated($ajuan, $statusHistory, $catatanHistory));

            return redirect()->route('ajuan.index')->with('success', 'Keputusan akhir berhasil disimpan.');
        }

        // Fallback
        return redirect()->back()->with('error', 'Terjadi kesalahan.');
    }

    //cetak detail ajuan
    public function cetak($id)
    {
        $ajuan = Pengajuan::with(['user', 'bidang', 'histories.diubahOleh', 'latestHistory.diubahOleh'])->findOrFail($id);

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
}
