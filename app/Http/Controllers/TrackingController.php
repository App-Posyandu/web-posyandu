<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function track(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $request->input('code');

        $ajuan = Pengajuan::where('tracking_code', $code)->first();

        if (!$ajuan) {
            return redirect()->route('landing')
                ->with('track_error', 'Kode ajuan tidak ditemukan. Pastikan kode yang Anda masukkan benar.');
        }

        return view('tracking.show', compact('ajuan'));
    }

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
