<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $query = User::with('posyandu')->latest();

        switch ($currentUser->role) {
            case 'kader':
                $query->where('role', 'masyarakat');
                break;

            case 'ketua-kader':
                $query->whereIn('role', ['kader', 'masyarakat']);
                break;

            case 'kabid':
                $query->whereIn('role', ['ketua-kader', 'kader', 'masyarakat']);
                break;

            case 'admin':
                break;
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%');
            });
        }


        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();

        return view('admin.users.create', compact('posyandus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:masyarakat,kader,ketua-kader,kabid,admin'],
            'posyandu_id' => ['required', 'exists:posyandus,id'],
            'nik' => ['required', 'string', 'digits:16', 'unique:users'],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'ktp' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'kk' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // Proses file KTP jika diunggah
        $ktpBase64 = null;
        if ($request->hasFile('ktp')) {
            $ktpBase64 = 'data:image/' . $request->file('ktp')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($request->file('ktp')->getRealPath()));
        }

        // Proses file KK jika diunggah
        $kkBase64 = null;
        if ($request->hasFile('kk')) {
            $kkBase64 = 'data:image/' . $request->file('kk')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($request->file('kk')->getRealPath()));
        }

        $isInstantVerified = in_array($request->role, ['admin', 'kabid', 'ketua-kader']);

        $userAuth = Auth::user();

        // Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'posyandu_id' => $request->posyandu_id,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            // 'ktp' => $ktpBase64,
            // 'kk' => $kkBase64,
            'verified_at' => $isInstantVerified ? now() : null,
            'verified_by' => $isInstantVerified ? $userAuth->id : null,
        ]);

        event(new Registered($user));

        return redirect()->route('admin.users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();

        return view('admin.users.edit', compact('user', 'posyandus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Validasi email unik, tapi abaikan user saat ini
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id, 'id')],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:masyarakat,kader,ketua-kader,kabid,admin'],
            'posyandu_id' => ['required', 'exists:posyandus,id'],
            // Validasi NIK unik, tapi abaikan user saat ini
            'nik' => ['required', 'string', 'digits:16', Rule::unique('users')->ignore($user->id, 'id')],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id, 'id')],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'ktp' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'kk' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // Ambil semua data yang sudah tervalidasi
        $data = $request->except('password', 'password_confirmation', 'ktp', 'kk');
        $data['no_telepon'] = $request->no_telepon;

        // Jika ada password baru, hash dan tambahkan ke data
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Jika ada file KTP baru, proses dan tambahkan ke data
        if ($request->hasFile('ktp')) {
            $data['ktp'] = 'data:image/' . $request->file('ktp')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($request->file('ktp')->getRealPath()));
        }

        // Jika ada file KK baru, proses dan tambahkan ke data
        if ($request->hasFile('kk')) {
            $data['kk'] = 'data:image/' . $request->file('kk')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($request->file('kk')->getRealPath()));
        }

        // Update data user di database
        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function verify(User $user)
    {
        $currentUser = Auth::user();

        if (
            ($currentUser->role === 'kader' && $user->role === 'masyarakat') ||
            ($currentUser->role === 'ketua-kader' && $user->role === 'kader') ||
            ($currentUser->role === 'kabid' && $user->role === 'ketua-kader') ||
            ($currentUser->role === 'admin')
        ) {
            $user->update([
                'verified_at' => now(),
                'verified_by' => $currentUser->id,
            ]);
            return redirect()->back()->with('success', 'User berhasil diverifikasi.');
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk memverifikasi user ini.');
    }
}
