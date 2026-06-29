<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        // Google mengembalikan error (misal redirect_uri_mismatch, access_denied, dll)
        if ($request->has('error')) {
            $desc = $request->input('error_description', $request->input('error'));
            return redirect('/login')->with('error', 'Google login gagal: ' . $desc);
        }

        // Kode tidak ada — kemungkinan state-less redirect atau session hilang
        if (! $request->has('code')) {
            return redirect('/login')->with('error', 'Kode otorisasi Google tidak diterima. Silakan coba lagi.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                Auth::login($user, true);
                return redirect('/dashboard');
            }

            session(['google_user_name' => $googleUser->getName(), 'google_user_email' => $googleUser->getEmail()]);

            return redirect()->route('register');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login menggunakan Google. ' . $e->getMessage());
        }
    }
}
