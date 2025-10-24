<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi semua input dari form
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16', 'unique:users'],
            'alamat' => ['required', 'string'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'nama_posyandu' => ['required', 'string', 'max:255'],
            'desa' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:masyarakat,kader'],
            'ktp' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // Maksimal 2MB
            'kk' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],  // Maksimal 2MB
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users']
        ]);

        // 2. Proses file upload KTP menjadi Base64
        $ktpBase64 = null;
        if ($request->hasFile('ktp')) {
            $ktpPath = $request->file('ktp')->getRealPath();
            $ktpData = file_get_contents($ktpPath);
            $ktpBase64 = 'data:image/' . $request->file('ktp')->getClientOriginalExtension() . ';base64,' . base64_encode($ktpData);
        }

        // 3. Proses file upload KK menjadi Base64
        $kkBase64 = null;
        if ($request->hasFile('kk')) {
            $kkPath = $request->file('kk')->getRealPath();
            $kkData = file_get_contents($kkPath);
            $kkBase64 = 'data:image/' . $request->file('kk')->getClientOriginalExtension() . ';base64,' . base64_encode($kkData);
        }

        // 4. Buat user baru dengan semua data
        $user = User::create([
            'name' => $request->name,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'role' => $request->role,
            'ktp' => $ktpBase64,
            'kk' => $kkBase64,
            'verified_at' => $request->role === 'masyarakat' ? now() : null,
        ]);

        Posyandu::create([
            'nama_posyandu' => $request->nama_posyandu,
            'desa' => $request->desa,
            'kecamatan' => $request->kecamatan,
        ]);

        // 5. Kirim event, login user, dan redirect ke dashboard (bawaan Breeze)
        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
