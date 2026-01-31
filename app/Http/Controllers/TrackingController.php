<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Tampilkan halaman landing dengan form tracking
     */
    public function landing()
    {
        return view('landing');
    }

    /**
     * Tracking ajuan berdasarkan kode unik
     */
    public function track(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $request->input('code');

        // Cari ajuan berdasarkan tracking_code (uuid)
        $ajuan = Pengajuan::where('tracking_code', $code)->first();

        if (!$ajuan) {
            return redirect()->route('landing')
                ->with('track_error', 'Kode ajuan tidak ditemukan. Pastikan kode yang Anda masukkan benar.');
        }

        // Redirect ke halaman detail tracking
        return view('tracking.show', compact('ajuan'));
    }

    /**
     * Tracking menggunakan QR Code scan
     */
    public function trackByQR($code)
    {
        $ajuan = Pengajuan::where('tracking_code', $code)->first();

        if (!$ajuan) {
            return redirect()->route('landing')
                ->with('track_error', 'Kode ajuan tidak ditemukan.');
        }

        return view('tracking.show', compact('ajuan'));
    }
}
