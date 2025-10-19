<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\History;
use App\Models\Pengajuan;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PDF;

class AjuanController extends Controller
{
    public function index()
    {
        $semuaAjuan = Pengajuan::with(['user', 'bidang'])->latest()->paginate(5);

        return view('ajuan.index', [
            'semuaAjuan' => $semuaAjuan,
        ]);
    }

    public function create($bidang_slug)
    {
        // if (Auth::user()->status != 'verified') {
        //     return redirect()->back()->with('error', 'Akun Anda belum terverifikasi oleh kader. Mohon tunggu.');
        // }

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
        if (! $ajuanData) {
            return redirect()->route('dashboard');
        }

        return view('components.ajuan.administrasi-ajuan.index', [
            'items' => $ajuanData['administrasi_items_template'],
        ]);
    }

    public function storeAdministrasi(Request $request)
    {
        $ajuanData = session('ajuan_data');
        $validationRules = [];
        foreach ($ajuanData['administrasi_items_template'] as $key => $item) {
            if ($key === 'kartu_bpjs') {
                $validationRules[$key] = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
            } else {
                $validationRules[$key] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
            }
        }

        $request->validate($validationRules);

        $uploadedFiles = [];
        foreach (array_keys($ajuanData['administrasi_items_template']) as $key) {
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('ajuan_dokumen', 'public');
                $uploadedFiles[$key] = $path;
            }
        }

        session()->put('ajuan_data.uploaded_files', $uploadedFiles);

        return redirect()->route('ajuan.verifikasi');
    }

    public function showVerifikasi()
    {
        $ajuanData = session('ajuan_data');
        if (! $ajuanData) {
            return redirect()->route('dashboard');
        }

        $verifikasiData = [
            'bidang_nama' => $ajuanData['bidang_nama'],
            'checklist_items' => $ajuanData['selected_formulir_items'],
            'dokumen_items' => $ajuanData['administrasi_items_template'],
            'uploaded_files' => $ajuanData['uploaded_files'],
        ];

        return view('components.ajuan.verifikasi.index', ['data' => $verifikasiData]);
    }

    public function storeFinal(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $ajuanData = session('ajuan_data');
        if (! $ajuanData) {
            return redirect()->route('dashboard')->with('error', 'Sesi ajuan telah habis.');
        }

        $finalChecklistData = $ajuanData['selected_formulir_items'];
        if (isset($ajuanData['lainnya_text']) && in_array('Lainnya...', $finalChecklistData)) {
            $finalChecklistData = array_map(function ($item) use ($ajuanData) {
                return $item === 'Lainnya...' ? 'Lainnya: '.$ajuanData['lainnya_text'] : $item;
            }, $finalChecklistData);
        }

        $pengajuan = Pengajuan::create([
            'user_id' => $user->id,
            'bidang_id' => $ajuanData['bidang_id'],
            'status' => 'Diproses',
            'formulir_items' => $finalChecklistData,
            'administrasi_items' => $ajuanData['uploaded_files'],
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
                    'dokumen_lainnya' => 'Lainnya...',
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

    // show detail ajuan
    public function show($id)
    {
        $ajuan = Pengajuan::with(['user', 'bidang', 'histories'])->findOrFail($id);

        return view('ajuan.detail', [
            'ajuan' => $ajuan,
        ]);
    }

    // cetak detail ajuan
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
        return $pdf->stream('ajuan_'.$ajuan->id.'.pdf');
    }
}
