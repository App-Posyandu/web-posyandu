<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AjuanController extends Controller
{
    public function index()
    {
        // $semuaAjuan = Ajuan::latest()->paginate(5);
        $semuaAjuan = collect([
            (object)[
                'id' => 1,
                'bidang' => 'Bidang Perumahan Rakyat',
                'deskripsi' => 'Lorem ipsum',
                'tindak_lanjut' => 'Sudah Verifikasi',
                'status' => 'Disetujui'
            ],
            (object)[
                'id' => 2,
                'bidang' => 'Bidang Pendidikan',
                'deskripsi' => 'Lorem ipsum',
                'tindak_lanjut' => 'Inactive',
                'status' => 'Ditolak'
            ],
            (object)[
                'id' => 3,
                'bidang' => 'Bidang Kesehatan',
                'deskripsi' => 'Lorem ipsum',
                'tindak_lanjut' => 'Inactive',
                'status' => 'Ditolak'
            ],
            (object)[
                'id' => 4,
                'bidang' => 'Bidang Sosial',
                'deskripsi' => 'Lorem ipsum',
                'tindak_lanjut' => 'Inactive',
                'status' => 'Ditolak'
            ],
            (object)[
                'id' => 5,
                'bidang' => 'Bidang Trantibumlinmas',
                'deskripsi' => 'Lorem ipsum',
                'tindak_lanjut' => 'Inactive',
                'status' => 'Ditolak'
            ],
        ]);

        // Kirim data ke view
        return view('ajuan.index', [
            'semuaAjuan' => $semuaAjuan
        ]);
    }
    public function create($bidang)
    {
        Session::forget('ajuan_data');

        $data = $this->getBidangData($bidang);
        if (!$data) {
            abort(404, 'Bidang Layanan tidak ditemukan');
        }

        session(['ajuan_data' => [
            'bidang' => $bidang,
            'formulir_items' => $data['formulir_items'],
            'administrasi_items' => $data['administrasi_items']
        ]]);

        return view('components.ajuan.formulir.index', [
            'bidang' => $bidang,
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
        $ajuanData = session('ajuan_data');
        // Ajuan::create([
        //     'user_id' => auth()->id(),
        //     'bidang' => $ajuanData['bidang'],
        //     'detail_permohonan' => json_encode($ajuanData['selected_formulir_items']),
        //     'dokumen_administrasi' => json_encode($ajuanData['uploaded_files']),
        // ]);

        Session::forget('ajuan_data');

        return redirect()->route('ajuan.index')->with('success', 'Ajuan berhasil dikirim!');
    }

    public function getBidangData($bidang)
    {
        $allData = [
            'pekerjaanUmum' => [
                'formulir_items' => [
                    'Pendidikan anak usia dini (0 s.d 6 Tahun)',
                    'Identifikasi ketersediaan dan pengelolaan perpustakaan desa',
                    'Penguatan pemanfaatan literasi',
                    'Identifikasi penyediaan alat peraga edukasi (APE)',
                    'Pembiayaan sekolah',
                    'Perlengkapan sekolah',
                    'Pemberian makanan tambahan bagi anak usia sekolah',
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
        ];

        return $allData[$bidang] ?? null;
    }
}