<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\History;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class AjuanController extends Controller
{
    use AuthorizesRequests;
    public function index(User $user, Pengajuan $ajuan)
    {
        $user = Auth::user();
        $query = Pengajuan::with(['user', 'bidang']);

        if ($user->role === 'masyarakat') {
            $query->where('user_id', $user->id);
        }
        $semuaAjuan = Pengajuan::with(['user', 'bidang'])->latest()->paginate(5);

        return view('ajuan.index', [
            'semuaAjuan' => $semuaAjuan,
        ]);
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
            'user_id' => $user->id,
            'bidang_id' => $ajuanData['bidang_id'],
            'status_pengajuan' => 'Diproses',
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $uploadedFiles,
            'deskripsi_pengajuan' => $ajuanData['deskripsi_pengajuan'] ?? 'Tidak ada deskripsi.',
        ]);

        History::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'Diajukan',
            'catatan' => 'Pengajuan baru telah dibuat oleh pengguna.',
            'diubah_oleh' => $user->id,
            'created_at' => now(),
        ]);

        Session::forget('ajuan_data');
        session()->put('ajuan_data.uploaded_files', $uploadedFiles);

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
        // $this->authorize('view', $ajuan);
        $ajuan->load(['user', 'bidang', 'histories']);
        $templateData = $this->getBidangData($ajuan->bidang->slug);
        if (!$templateData) {
            // Tambahkan fallback jika template tidak ditemukan
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

    public function downloadDokumen(Request $request)
    {
        $path = $request->query('path');

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($fullPath);
    }

    public function verifyAjuan(Request $request, Pengajuan $ajuan)
    {
        $user = Auth::user();
        // $this->authorize('verify', $ajuan);

        $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan' => ['required', 'string'],
            // 'sudah_verifikasi' => ['sometimes', 'boolean'],
            // 'kunjungan_lapangan' => ['sometimes', 'boolean'],
            'sudah_verifikasi' => ['required', 'boolean'],
            'kunjungan_lapangan' => ['required', 'boolean'],
            'verified_formulir_items' => ['nullable', 'array'],
            'verified_administrasi_items' => ['nullable', 'array'],
        ]);

        $ajuan->update([
            'status_pengajuan' => $request->status,
            // 'sudah_verifikasi' => $request->has('sudah_verifikasi'),
            // 'kunjungan_lapangan' => $request->has('kunjungan_lapangan'),
            'sudah_verifikasi' => $request->sudah_verifikasi,
            'kunjungan_lapangan' => $request->kunjungan_lapangan,
        ]);

        History::create([
            'pengajuan_id' => $ajuan->id,
            'status' => $request->status,
            'catatan' => $request->catatan,
            'diubah_oleh' => $user->id,
            'created_at' => now(),
        ]);

        return redirect()->route('ajuan.index')->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    //cetak detail ajuan
    public function cetak($id)
    {
        $ajuan = Pengajuan::with(['user', 'bidang', 'histories'])->findOrFail($id);

        $pdf = Pdf::loadView('ajuan.cetak', ['ajuan' => $ajuan]);

        $pdf->setPaper('A4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);
        return $pdf->stream('ajuan_' . $ajuan->id . '.pdf');
    }
}
