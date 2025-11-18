<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Exports\UsersTemplateExport;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // $currentUser = Auth::user();

        // $query = User::with(['posyandu', 'bidang'])->latest();

        // switch ($currentUser->role) {
        //     case 'kader':
        //         $query->where('role', 'masyarakat')
        //             ->where('posyandu_id', $currentUser->posyandu_id);
        //         break;

        //     case 'ketua-kader':
        //         $query->whereIn('role', ['kader', 'masyarakat'])
        //             ->where('posyandu_id', $currentUser->posyandu_id);
        //         break;

        //     case 'kabid':
        //         $query->whereIn('role', ['ketua-kader', 'kader', 'masyarakat']);
        //         break;
        // }

        // if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
        //     $query->where('posyandu_id', $currentUser->posyandu_id);
        // }

        // if ($request->filled('search')) {
        //     $searchTerm = $request->input('search');
        //     $query->where(function ($q) use ($searchTerm) {
        //         $q->where('name', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('email', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('nik', 'like', '%' . $searchTerm . '%');
        //     });
        // }

        // if ($request->filled('role')) {
        //     $query->where('role', $request->input('role'));
        // }

        // $users = $query->paginate(10)->withQueryString();

        // return view('admin.users.index', compact('users'));
        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();
        return view('admin.users.create', compact('posyandus', 'bidangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $allowedRoles = [];
        switch ($currentUser->role) {
            case 'admin':
                $allowedRoles = ['masyarakat', 'kader', 'ketua-kader', 'kabid', 'admin'];
                break;
            case 'kabid':
                $allowedRoles = ['ketua-kader'];
                break;
            case 'ketua-kader':
                $allowedRoles = ['kader'];
                break;
        }

        if ($currentUser->role === 'kabid') {
            $request->merge(['role' => 'ketua-kader']);
            $allowedRoles = ['ketua-kader'];
        } elseif ($currentUser->role === 'ketua-kader') {
            $request->merge(['role' => 'kader']);
            $allowedRoles = ['kader'];
        } elseif ($currentUser->role === 'admin') {
            $allowedRoles = ['masyarakat', 'kader', 'ketua-kader', 'kabid', 'admin'];
        }

        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            // 'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($allowedRoles)],
            'posyandu_id' => ['nullable', 'uuid', 'exists:posyandus,id'],
            // 'nik' => ['required', 'string', 'digits:16', 'unique:users'],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            // 'ktp' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            // 'kk' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ];

        if ($request->role === 'kader') {
            $validationRules['bidang_id'] = ['required', 'uuid', 'exists:bidang_pengajuans,id'];
        }

        $request->validate($validationRules);

        // // Proses file KTP jika diunggah
        // $ktpBase64 = null;
        // if ($request->hasFile('ktp')) {
        //     $ktpBase64 = 'data:image/' . $request->file('ktp')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($request->file('ktp')->getRealPath()));
        // }

        // // Proses file KK jika diunggah
        // $kkBase64 = null;
        // if ($request->hasFile('kk')) {
        //     $kkBase64 = 'data:image/' . $request->file('kk')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($request->file('kk')->getRealPath()));
        // }

        $isInstantVerified = in_array($request->role, ['admin', 'kabid', 'ketua-kader']);


        // Buat user baru
        $userData = [
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
            'verified_at' => $isInstantVerified ? now() : null,
            'verified_by' => $isInstantVerified ? $currentUser->id : null,
            'bidang_id' => $request->role === 'kader' ? $request->bidang_id : null, // 5. Simpan bidang_id
        ];


        if ($request->role === 'kader' && $request->filled('bidang_id')) {
            $userData['bidang_id'] = $request->bidang_id;
        }

        $user = User::create($userData);

        event(new Registered($user));
        if ($request->input('source') === 'posyandu_create') {
            return redirect()->route('admin.posyandu.create')
                ->with('success', 'User Ketua Kader berhasil dibuat! Silakan refresh halaman dan pilih dari dropdown.');
        }

        return redirect()->route('admin.users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['posyandu', 'bidang']);
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
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        return view('admin.users.edit', compact('user', 'posyandus', 'bidangs'));
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
            'ktp' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            'kk' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ]);

        if ($request->role === 'kader') {
            $validationRules['bidang_id'] = ['required', 'uuid', 'exists:bidang_pengajuans,id'];
        }

        // Ambil semua data yang sudah tervalidasi
        $data = $request->except('password', 'password_confirmation', 'ktp', 'kk');
        $data['no_telepon'] = $request->no_telepon;

        if ($request->role !== 'kader') {
            $data['bidang_id'] = null;
        } elseif ($request->filled('bidang_id')) {
            $data['bidang_id'] = $request->bidang_id;
        }

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

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        return back()->with('success', 'Import berhasil diproses.');
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
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimport!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function exportTemplate()
    {
        try {
            // Ambil semua posyandu yang terdaftar
            $posyandus = Posyandu::select('desa', 'kecamatan', 'kabupaten')
                ->orderBy('kecamatan')
                ->orderBy('desa')
                ->get();

            Log::info('Export User Template', ['total_posyandu' => $posyandus->count()]);

            if ($posyandus->isEmpty()) {
                // Fallback jika tidak ada posyandu
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data posyandu terdaftar. Silakan tambahkan posyandu terlebih dahulu.'
                ], 404);
            }

            $dataRows = [];

            // Loop setiap posyandu untuk generate baris
            foreach ($posyandus as $posyandu) {
                $dataRows[] = [
                    'desa' => $this->cleanDesaName($posyandu->desa),
                    'kecamatan' => $this->cleanKecamatanName($posyandu->kecamatan),
                    'kabupaten' => strtoupper($posyandu->kabupaten)
                ];
            }

            Log::info('Total Rows Generated', ['count' => count($dataRows)]);

            $filename = 'Template_User_Ketua_Kader_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new UsersTemplateExport($dataRows),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Export User Template Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Clean nama desa
     */
    private function cleanDesaName($name)
    {
        $cleaned = str_ireplace(['DESA ', 'KELURAHAN '], '', $name);
        return strtoupper(trim($cleaned));
    }

    /**
     * Helper: Clean nama kecamatan
     */
    private function cleanKecamatanName($name)
    {
        $cleaned = str_ireplace('KECAMATAN ', '', $name);
        return strtoupper(trim($cleaned));
    }
}