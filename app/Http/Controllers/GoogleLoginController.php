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

    public function handleGoogleCallback()
    {
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
            dd($e->getMessage());
            return redirect('/login')->with('error', 'Gagal login menggunakan Google');
        }
    }
}