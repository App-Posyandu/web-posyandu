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
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],

            // Aturan baru (buat opsional dengan 'nullable')
            'nik' => ['nullable', 'string', 'digits:16', Rule::unique(User::class)->ignore($user->id)],
            'alamat' => ['nullable', 'string'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string'],
            'no_telepon' => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($user->id)],
            'ktp' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            'kk' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $user->fill($request->except(['ktp', 'kk']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('ktp')) {
            $ktpBase64 = 'data:' . $request->file('ktp')->getMimeType() . ';base64,' . base64_encode(file_get_contents($request->file('ktp')->getRealPath()));
            $user->ktp = $ktpBase64;
        }

        if ($request->hasFile('kk')) {
            $kkBase64 = 'data:' . $request->file('kk')->getMimeType() . ';base64,' . base64_encode(file_get_contents($request->file('kk')->getRealPath()));
            $user->kk = $kkBase64;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
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
