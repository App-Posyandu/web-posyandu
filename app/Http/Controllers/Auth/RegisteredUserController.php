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
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],

            // ✅ TAMBAHAN BARU: RW/RT
            'rw' => ['required', 'string', 'regex:/^RW\d{2}$/'],  // Format: RW01, RW02, dst
            'rt' => ['nullable', 'string', 'regex:/^RT\d{3}$/'],  // Format: RT001, RT002, dst
        ];

        $posyanduId = $request->posyandu_id;

        // ✅ Validasi: Jika posyandu sudah UUID, cek apakah RW valid
        if (\Illuminate\Support\Str::isUuid($posyanduId)) {
            $posyandu = Posyandu::find($posyanduId);

            if ($posyandu && !$posyandu->isRwAllowed($request->rw)) {
                return redirect()->back()
                    ->withErrors(['rw' => 'RW yang Anda pilih tidak dilayani oleh Posyandu ini.'])
                    ->withInput();
            }

            // ✅ Validasi RT jika diisi
            if ($request->filled('rt') && $posyandu) {
                if (!$posyandu->isRtValidForRw($request->rw, $request->rt)) {
                    return redirect()->back()
                        ->withErrors(['rt' => 'RT yang Anda pilih tidak tersedia di RW ini.'])
                        ->withInput();
                }
            }
        }

        // ✅ Jika posyandu belum ada (user ketik manual), buat posyandu baru
        if (!\Illuminate\Support\Str::isUuid($posyanduId)) {
            $newPosyandu = Posyandu::create([
                'nama_posyandu' => $posyanduId,
                'kabupaten' => explode('_', $request->kabupaten)[1] ?? $request->kabupaten,
                'kecamatan' => explode('_', $request->kecamatan)[1] ?? $request->kecamatan,
                'desa' => explode('_', $request->desa)[1] ?? $request->desa,
                // ✅ Posyandu baru belum punya mapping RW/RT, admin perlu setup nanti
            ]);
            $posyanduId = $newPosyandu->id;
        }

        if ($request->role === 'kader') {
            $validationRules['bidang_id'] = ['required', 'uuid', 'exists:bidang_pengajuans,id'];
        }

        $request->validate($validationRules);

        // ✅ Buat user baru dengan RW/RT
        $userData = [
            'name' => $request->name,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'posyandu_id' => $posyanduId,

            // ✅ TAMBAHAN BARU
            'rw' => $request->rw,
            'rt' => $request->rt,

            'verified_at' => $request->role === 'masyarakat' ? now() : null,
            'verified_by' => null,
        ];

        $user = User::create($userData);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
