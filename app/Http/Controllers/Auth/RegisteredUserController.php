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
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    const PROVINCE_ID = 33;
    public function create(): View
    {
        $kabupatens = Http::get(env('API_WILAYAH_URL') . 'regencies/' . self::PROVINCE_ID . '.json')->json();
        return view('auth.register', compact('kabupatens'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request): RedirectResponse
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            // 'nik' => ['nullable', 'string', 'digits:16', 'unique:users'],
            // 'alamat' => ['nullable', 'string'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'desa' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'posyandu_id' => ['required', 'string'],
            'bidang_id' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:masyarakat,kader'],
            // 'ktp' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            // 'kk' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
        ];

        $posyanduId = $request->posyandu_id;

        if (!\Illuminate\Support\Str::isUuid($posyanduId)) {
            $newPosyandu = Posyandu::create([
                'nama_posyandu' => $posyanduId,
                'kabupaten' => explode('_', $request->kabupaten)[1] ?? $request->kabupaten,
                'kecamatan' => explode('_', $request->kecamatan)[1] ?? $request->kecamatan,
                'desa' => explode('_', $request->desa)[1] ?? $request->desa,
            ]);
            $posyanduId = $newPosyandu->id;
        }

        if ($request->role === 'kader') {
            $validationRules['bidang_id'] = ['required', 'uuid', 'exists:bidang_pengajuans,id'];
        }

        $request->validate($validationRules);

        // $ktpBase64 = null;
        // if ($request->hasFile('ktp')) {
        //     $ktpPath = $request->file('ktp')->getRealPath();
        //     $ktpData = file_get_contents($ktpPath);
        //     $ktpBase64 = 'data:image/' . $request->file('ktp')->getClientOriginalExtension() . ';base64,' . base64_encode($ktpData);
        // }

        // $kkBase64 = null;
        // if ($request->hasFile('kk')) {
        //     $kkPath = $request->file('kk')->getRealPath();
        //     $kkData = file_get_contents($kkPath);
        //     $kkBase64 = 'data:image/' . $request->file('kk')->getClientOriginalExtension() . ';base64,' . base64_encode($kkData);
        // }

        // $posyandu = Posyandu::firstOrCreate(
        //     [
        //         'nama_posyandu' => $request->nama_posyandu,
        //         'desa' => $request->desa,
        //     ],
        //     [
        //         'kecamatan' => $request->kecamatan,
        //         'kabupaten' => $request->kabupaten,
        //     ]
        // );

        // 4. Buat user baru dengan semua data
        $userData = [
            'name' => $request->name,
            // 'nik' => $request->nik,
            // 'alamat' => $request->alamat,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'role' => $request->role,
            // 'ktp' => $ktpBase64,
            // 'kk' => $kkBase64,
            'posyandu_id' => $posyanduId,
            'verified_at' => $request->role === 'masyarakat' ? now() : null,
            'verified_by' => null,
        ];

        if ($request->role === 'kader' && $request->filled('bidang_id')) {
            $userData['bidang_id'] = $request->bidang_id;
        }

        $user = User::create($userData);

        // Posyandu::create([
        //     'nama_posyandu' => $request->nama_posyandu,
        //     'desa' => $request->desa,
        //     'kecamatan' => $request->kecamatan,
        // ]);

        // 5. Kirim event, login user, dan redirect ke dashboard (bawaan Breeze)
        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
