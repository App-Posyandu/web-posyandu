<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],

            'nik' => ['nullable', 'string', 'digits:16', Rule::unique(User::class)->ignore($user->id)],
            'alamat' => ['nullable', 'string'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string'],
            'no_telepon' => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($user->id)],
            'ktp' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            'kk' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar, gunakan email lain.',
            'nik.digits'         => 'NIK harus tepat 16 digit angka.',
            'nik.unique'         => 'NIK sudah terdaftar, gunakan NIK yang benar.',
            'no_telepon.max'     => 'Nomor WhatsApp maksimal 20 digit.',
            'no_telepon.unique'  => 'Nomor WhatsApp sudah terdaftar, gunakan nomor lain.',
            'ktp.mimes'          => 'File KTP harus berformat jpeg, png, jpg, atau pdf.',
            'ktp.max'            => 'Ukuran file KTP maksimal 2MB.',
            'kk.mimes'           => 'File KK harus berformat jpeg, png, jpg, atau pdf.',
            'kk.max'             => 'Ukuran file KK maksimal 2MB.',
        ]);

        $user->fill($request->except(['ktp', 'kk']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('ktp')) {
            $ktpFile = $request->file('ktp');
            $ktpBase64 = 'data:' . $ktpFile->getMimeType() . ';base64,' . base64_encode(file_get_contents($ktpFile->getRealPath()));
            $user->ktp = $ktpBase64;
        }

        if ($request->hasFile('kk')) {
            $kkFile = $request->file('kk');
            $kkBase64 = 'data:' . $kkFile->getMimeType() . ';base64,' . base64_encode(file_get_contents($kkFile->getRealPath()));
            $user->kk = $kkBase64;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
