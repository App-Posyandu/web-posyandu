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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Str;

class RegisteredUserController extends Controller
{
    const PROVINCE_ID = 33;

    public function create(): View
    {
        $kabupatens = $this->fetchKabupatenList();

        return view('auth.register', compact('kabupatens'));
    }

    private function fetchKabupatenList(): array
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get(env('API_WILAYAH_URL') . 'regencies/' . self::PROVINCE_ID . '.json');

            if ($response->successful()) {
                $payload = $response->json();

                if (is_array($payload) && isset($payload['data']) && is_array($payload['data'])) {
                    return $payload;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('register.wilayah_api_unavailable', [
                'message' => $e->getMessage(),
            ]);
        }

        return ['data' => []];
    }

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

            'rw' => ['required', 'string', 'regex:/^RW\d{2}$/'],
            'rt' => ['nullable', 'string', 'regex:/^RT\d{3}$/'],
        ];

        $posyanduId = $request->posyandu_id;

        if (\Illuminate\Support\Str::isUuid($posyanduId)) {
            $posyandu = Posyandu::find($posyanduId);

            if ($posyandu && !$posyandu->isRwAllowed($request->rw)) {
                return redirect()->back()
                    ->withErrors(['rw' => 'RW yang Anda pilih tidak dilayani oleh Posyandu ini.'])
                    ->withInput();
            }

            if ($request->filled('rt') && $posyandu) {
                if (!$posyandu->isRtValidForRw($request->rw, $request->rt)) {
                    return redirect()->back()
                        ->withErrors(['rt' => 'RT yang Anda pilih tidak tersedia di RW ini.'])
                        ->withInput();
                }
            }
        }

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

        $request->validate($validationRules, [
            'name.required'          => 'Nama lengkap wajib diisi.',
            'name.max'               => 'Nama maksimal 255 karakter.',
            'tempat_lahir.required'  => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date'     => 'Format tanggal lahir tidak valid.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'desa.required'          => 'Desa wajib dipilih.',
            'kecamatan.required'     => 'Kecamatan wajib dipilih.',
            'kabupaten.required'     => 'Kabupaten wajib dipilih.',
            'posyandu_id.required'   => 'Posyandu wajib dipilih.',
            'email.required'         => 'Email wajib diisi.',
            'email.email'            => 'Format email tidak valid (contoh: nama@email.com).',
            'email.unique'           => 'Email sudah terdaftar, gunakan email lain.',
            'password.required'      => 'Password wajib diisi.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'password.min'           => 'Password minimal 8 karakter.',
            'no_telepon.required'    => 'Nomor WhatsApp wajib diisi.',
            'no_telepon.max'         => 'Nomor WhatsApp maksimal 20 digit.',
            'no_telepon.unique'      => 'Nomor WhatsApp sudah terdaftar, gunakan nomor lain.',
            'nik.unique'             => 'NIK sudah terdaftar, gunakan NIK yang benar.',
            'nik.max'                => 'NIK maksimal 16 digit.',
            'rw.required'            => 'RW wajib dipilih.',
            'rw.regex'               => 'Format RW tidak valid (contoh: RW01, RW02).',
            'rt.regex'               => 'Format RT tidak valid (contoh: RT001, RT002).',
            'bidang_id.required'     => 'Bidang tugas wajib dipilih.',
        ]);

        $userData = [
            'name' => $request->name,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'posyandu_id' => $posyanduId,

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
