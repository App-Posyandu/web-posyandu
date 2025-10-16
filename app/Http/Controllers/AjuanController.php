<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\History;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AjuanController extends Controller
{
    public function index()
    {
        // $semuaAjuan = collect([
        //     (object)[
        //         'id' => 1,
        //         'bidang' => 'Bidang Perumahan Rakyat',
        //         'deskripsi' => 'Lorem ipsum',
        //         'tindak_lanjut' => 'Sudah Verifikasi',
        //         'status' => 'Disetujui'
        //     ],
        //     (object)[
        //         'id' => 2,
        //         'bidang' => 'Bidang Pendidikan',
        //         'deskripsi' => 'Lorem ipsum',
        //         'tindak_lanjut' => 'Inactive',
        //         'status' => 'Ditolak'
        //     ],
        //     (object)[
        //         'id' => 3,
        //         'bidang' => 'Bidang Kesehatan',
        //         'deskripsi' => 'Lorem ipsum',
        //         'tindak_lanjut' => 'Inactive',
        //         'status' => 'Ditolak'
        //     ],
        //     (object)[
        //         'id' => 4,
        //         'bidang' => 'Bidang Sosial',
        //         'deskripsi' => 'Lorem ipsum',
        //         'tindak_lanjut' => 'Inactive',
        //         'status' => 'Ditolak'
        //     ],
        //     (object)[
        //         'id' => 5,
        //         'bidang' => 'Bidang Trantibumlinmas',
        //         'deskripsi' => 'Lorem ipsum',
        //         'tindak_lanjut' => 'Inactive',
        //         'status' => 'Ditolak'
        //     ],
        // ]);

        $semuaAjuan = Pengajuan::with(['user', 'bidang'])->latest()->paginate(5);

        return view('ajuan.index', [
            'semuaAjuan' => $semuaAjuan
        ]);
    }
    public function create($bidang_slug)
    {
        Session::forget('ajuan_data');

        $allBidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        $bidang = BidangPengajuan::where('slug', $bidang_slug)->firstOrFail();

        $data = $this->getBidangData($bidang);
        if (!$data) {
            abort(404, 'Bidang Layanan tidak ditemukan');
        }

        session(['ajuan_data' => [
            'bidang_id' => $bidang->id,
            'bidang_slug' => $bidang->slug,
            'bidang_nama' => $bidang->nama_bidang,
            'formulir_items' => $data['formulir_items'],
            'administrasi_items' => $data['administrasi_items']
        ]]);

        return view('components.ajuan.formulir.index', [
            'bidang' => $bidang, // Untuk @selected
            'allBidangs' => $allBidangs, // Untuk perulangan <option>
            'items' => $data['formulir_items']
        ]);
    }

    public function storePermohonan(Request $request)
    {
        session()->put('components.ajuan.selected_formulir.index', $request->input('permohonan_items', []));
        return redirect()->route('ajuan.create.administrasi');
    }

    public function createAdministrasi(Request $request)
    {
        $ajuanData = session('ajuan_data');
        if (!$ajuanData) {
            return redirect()->route('dashboard');
        }

        return view('components.ajuan.administrasi-ajuan.index', [
            'items' => $ajuanData['administrasi_items']
        ]);
    }

    public function storeAdministrasi(Request $request)
    {
        $ajuanData = session('ajuan_data');
        $validationRules = [];
        foreach ($ajuanData['administrasi_items'] as $key => $item) {
            $validationRules[$key] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
        }

        $request->validate($validationRules);

        $uploadedFiles = [];
        foreach (array_keys($ajuanData['administrasi_items']) as $key) {
            if ($request->hasFile($key)) {
                $uploadedFiles[$key] = $request->file($key)->getClientOriginalName();
            }
        }

        session()->put('ajuan_data.uploaded_files', $uploadedFiles);

        return redirect()->route('ajuan.verifikasi');
    }

    public function showVerifikasi()
    {
        $ajuanData = session('ajuan_data');
        if (!$ajuanData) {
            return redirect()->route('dashboard');
        }

        return view('components.ajuan.verifikasi.index', ['data' => $ajuanData]);
    }

    public function storeFinal(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $ajuanData = session('ajuan_data');
        if (!$ajuanData) {
            return redirect()->route('dashboard')->with('error', 'Sesi ajuan telah habis.');
        }

        $pengajuan = Pengajuan::create([
            'user_id' => $user->uuid ?? $user->id,
            'bidang_id' => $ajuanData['bidang_id'],
            'detail_permohonan' => $ajuanData['selected_formulir_items'] ?? null,
            'dokumen_administrasi' => $ajuanData['uploaded_files'] ?? null,
            'status' => 'Diproses',
        ]);

        History::create([
            'pengajuan_id' => $pengajuan->id,
            'status' => 'Diajukan',
            'catatan' => 'Pengajuan baru telah dibuat oleh pengguna.',
            'diubah_oleh' => $user->uuid ?? $user->id,
            'created_at' => now(),
        ]);
        Session::forget('ajuan_data');

        return redirect()->route('ajuan.index')->with('success', 'Ajuan berhasil dikirim!');
    }

    public function getBidangData($bidang)
    {
        $allData = [
            'pekerjaanUmum' => [
                'formulir_items' => [
                    'Bantuan pembangunan infrastruktur desa',
                    'Bantuan perbaikan jalan desa',
                    'Bantuan penyediaan air bersih',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_permohonan' => 'Surat permohonan Kepala Dusun/RT',
                    'surat_keterangan' => 'Surat keterangan penghasilan dari Desa',
                ],
            ],
            'sosial' => [
                'formulir_items' => [
                    'Bantuan sosial untuk lansia',
                    'Program keluarga harapan (PKH)',
                    'Bantuan pangan non-tunai (BPNT)',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_tidak_mampu' => 'Surat pernyataan tidak mampu',
                    'surat_permohonan' => 'Surat permohonan Kepala Dusun/RT',
                ],
            ],
            'trantibumlinmas' => [
                'formulir_items' => [
                    'Bantuan keamanan lingkungan',
                    'Bantuan penanganan bencana',
                    'Bantuan pengelolaan lalu lintas',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_permohonan' => 'Surat permohonan Kepala Dusun/RT',
                    'surat_keterangan' => 'Surat keterangan penghasilan dari Desa',
                ],
            ],
            'perumahanrakyat' => [
                'formulir_items' => [
                    'Bantuan stimulan perumahan swadaya (BSPS)',
                    'Bantuan rumah tidak layak huni (RTLH)',
                    'Bantuan renovasi rumah',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_permohonan' => 'Surat permohonan Kepala Dusun/RT',
                    'surat_keterangan' => 'Surat keterangan penghasilan dari Desa',
                ],
            ],
            'pendidikan' => [
                'formulir_items' => [
                    'Bantuan pendidikan untuk anak usia sekolah',
                    'Beasiswa pendidikan',
                    'Bantuan perlengkapan sekolah',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_permohonan' => 'Surat permohonan Kepala Dusun/RT',
                    'surat_keterangan' => 'Surat keterangan penghasilan dari Desa',
                ],
            ],
            'kesehatan' => [
                'formulir_items' => [
                    'Bantuan iuran BPJS Kesehatan',
                    'Bantuan obat-obatan',
                    'Bantuan perawatan kesehatan',
                ],
                'administrasi_items' => [
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kk' => 'Kartu Keluarga (KK)',
                    'surat_permohonan' => 'Surat permohonan Kepala Dusun/RT',
                    'surat_keterangan' => 'Surat keterangan penghasilan dari Desa',
                ],
            ],
        ];

        return $allData[$bidang] ?? null;
    }
}
