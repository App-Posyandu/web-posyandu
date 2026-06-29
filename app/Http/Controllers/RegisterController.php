<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{

    public function showGoogleRegisterForm()
    {
        if (!session()->has('google_user_email')) {
            return redirect('/login');
        }
        return view('auth.register-google');
    }

    public function storeGoogleRegisterData(Request $request)
    {
        $request->validate([
            'ktp' => ['required', 'string', 'digits:16', 'unique:users'],
            'kk' => ['required', 'string', 'digits:16', 'unique:users'],
        ]);

        $user = User::create([
            'name' => session('google_user_name'),
            'email' => session('google_user_email'),
            'ktp' => $request->ktp,
            'kk' => $request->kk,
            'password' => Hash::make(Str::random(24)),
            'role' => 'masyarakat',
        ]);

        session()->forget(['google_user_name', 'google_user_email']);

        Auth::login($user);
        
        return redirect('/dashboard');
    }
}
