<?php

namespace App\Http\Controllers;

use App\Models\BidangPengajuan;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\UserHistory;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Imports\UsersImport;
use App\Imports\PosyanduImport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersTemplateExport;
use App\Models\Kabupaten;
use App\Models\Kecamatan;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;

    const PROVINCE_ID = 33; // ✅ TAMBAHKAN
    const API_TIMEOUT = 10; // ✅ TAMBAHKAN
    const CACHE_TTL = 3600; // ✅ TAMBAHKAN

    // ✅ TAMBAHKAN METHOD HELPER INI
    private function fetchWilayahData($endpoint, $cacheKey)
    {
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($endpoint) {
            try {
                $response = Http::timeout(self::API_TIMEOUT)
                    ->retry(2, 100)
                    ->get(env('API_WILAYAH_URL') . $endpoint);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['data' => []];
            } catch (\Exception $e) {
                Log::error("Wilayah API Error: {$endpoint}", ['error' => $e->getMessage()]);
                return ['data' => []];
            }
        });
    }

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $query = User::with(['posyandu', 'bidang'])->latest();

        // 1. Filter Hirarki Role
        switch ($currentUser->role) {
            case 'kader':
                $query->where('role', 'masyarakat');
                break;
            case 'ketua-kader':
                $query->whereIn('role', ['kader', 'masyarakat']);
                break;
            case 'operator-desa':
                $query->whereIn('role', ['ketua-kader', 'kader'])
                    ->where('kecamatan', $currentUser->kecamatan)
                    ->where('desa', $currentUser->desa);
                break;
            case 'admin-kecamatan':
                $query->whereIn('role', ['ketua-kader', 'kader', 'masyarakat']);
                break;
            case 'kabid':
                $query->whereIn('role', ['admin-kecamatan', 'ketua-kader', 'kader', 'masyarakat']);
                break;
        }

        // 2. Filter Wilayah (Multi-Tenancy)
        if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
            $query->where('posyandu_id', $currentUser->posyandu_id);
        } elseif ($currentUser->role === 'admin-kecamatan') {
            $kecamatanName = explode('_', $currentUser->kecamatan)[1] ?? $currentUser->kecamatan;
            $query->where(function ($q) use ($kecamatanName, $currentUser) {
                $q->where('kecamatan', 'LIKE', "%{$kecamatanName}%")
                    ->orWhere(function ($subQ) use ($currentUser) {
                        $subQ->where('role', 'admin-kecamatan')
                            ->where('kecamatan', $currentUser->kecamatan);
                    });
            });
        } elseif ($currentUser->role === 'kabid') {
            // ✅ TAMBAHKAN INI!
            // Kabid bisa lihat semua user, ATAU filter berdasarkan bidang jika diperlukan
            // if ($currentUser->kabupaten) {
            //     $query->where(function ($q) use ($currentUser) {
            //         $q->where('kabupaten', $currentUser->kabupaten)
            //             ->orWhereNull('kabupaten'); // User yang belum set kabupaten
            //     });
            // }
            // Jika bidang_id NULL, kabid bisa lihat SEMUA (tidak ada filter tambahan)
        }

        // 3. Search
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%');
            });
        }

        // 4. Filter Role
        if ($request->filled('role') && $request->input('role') !== '') {
            $query->where('role', $request->input('role'));
        }

        // if ($currentUser->role === 'kabid') {
        //     dd([
        //         'current_user' => [
        //             'name' => $currentUser->name,
        //             'role' => $currentUser->role,
        //             'bidang_id' => $currentUser->bidang_id,
        //             'kabupaten' => $currentUser->kabupaten,
        //         ],
        //         'sql' => $query->toSql(),
        //         'bindings' => $query->getBindings(),
        //         'total_users' => User::count(),
        //         'total_admin_kecamatan' => User::where('role', 'admin-kecamatan')->count(),
        //         'query_count' => $query->count(),
        //         'first_5_results' => $query->limit(5)->get(['id', 'name', 'role', 'bidang_id'])
        //     ]);
        // }

        $users = $query->paginate(10)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentUser = Auth::user();
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        $kabupatens = Kabupaten::orderBy('jenis')->orderBy('nama_kabupaten')->get();
        $kecamatans = collect();

        if ($currentUser->role === 'ketua-kader') {
            $posyandus = Posyandu::where('id', $currentUser->posyandu_id)->get();
        } elseif ($currentUser->role === 'operator-desa') {
            $posyandus = Posyandu::where('desa', $currentUser->desa)
                ->where('kecamatan', $currentUser->kecamatan)
                ->orderBy('nama_posyandu')->get();
        } elseif ($currentUser->role === 'admin-kecamatan') {
            // Admin Kecamatan hanya lihat posyandu di kecamatannya
            $posyandus = Posyandu::where('kecamatan_id', $currentUser->kecamatan_id)
                ->orderBy('nama_posyandu')->get();
        } elseif ($currentUser->role === 'kabid') {
            // Kabid lihat semua posyandu di kabupatennya
            $posyandus = Posyandu::where('kabupaten_id', $currentUser->kabupaten_id)
                ->orderBy('nama_posyandu')->get();

            // Ambil kecamatan di kabupaten Kabid
            $kecamatans = Kecamatan::where('kabupaten_id', $currentUser->kabupaten_id)
                ->orderBy('nama_kecamatan')->get();
        } elseif (in_array($currentUser->role, ['admin'])) {
            $posyandus = Posyandu::orderBy('nama_posyandu')->get();
            $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        }

        // Fetch Kabupaten dan Kota dari API
        $kabupatenList = [];
        $kotaList = [];

        if (in_array($currentUser->role, ['admin', 'kabid'])) {
            $wilayahData = $this->fetchWilayahData(
                'regencies/' . self::PROVINCE_ID . '.json',
                'kabupatens_jateng'
            );

            foreach (($wilayahData['data'] ?? []) as $wilayah) {
                if (stripos($wilayah['name'], 'Kota ') === 0) {
                    $kotaList[] = $wilayah;
                } elseif (stripos($wilayah['name'], 'Kabupaten ') === 0) {
                    $kabupatenList[] = $wilayah;
                }
            }
        }

        // return view('admin.users.create', compact('posyandus', 'bidangs', 'kabupatenList', 'kotaList'));
        return view('admin.users.create', compact('posyandus', 'bidangs', 'kabupatens', 'kecamatans', 'kabupatenList', 'kotaList'));
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
                $allowedRoles = ['masyarakat', 'kader', 'ketua-kader', 'admin-kecamatan', 'kabid', 'admin'];
                break;
            case 'kabid':
                $allowedRoles = ['admin-kecamatan', 'ketua-kader'];
                break;
            case 'admin-kecamatan':
                $allowedRoles = ['ketua-kader'];
                $request->merge(['role' => 'ketua-kader']);
                break;
            case 'operator-desa':
                $allowedRoles = ['ketua-kader'];
                $request->merge(['role' => 'ketua-kader']);
                break;
            case 'ketua-kader':
                $allowedRoles = ['kader'];
                $request->merge(['role' => 'kader']);
                break;
            case 'kader':
                $allowedRoles = ['masyarakat'];
                $request->merge(['role' => 'masyarakat']);
                break;
        }

        // ✅ Base validation rules
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($allowedRoles)],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'nik' => ['nullable', 'string', 'max:16', 'unique:users,nik'],
        ];

        // ✅ Validation untuk Admin Kecamatan
        if ($request->role === 'admin-kecamatan') {
            $validationRules['kecamatan'] = ['required', 'string'];
            // Kabupaten diambil dari Kabid, tidak perlu validasi
        }

        if ($request->role === 'operator-desa') {
            $validationRules['desa'] = ['required', 'string']; // Harus pilih desa
        }

        // ✅ Validation untuk Ketua Kader
        if ($request->role === 'ketua-kader') {
            $validationRules['posyandu_id'] = ['required', 'uuid', 'exists:posyandus,id'];
        }

        // ✅ Validation untuk Kader
        if ($request->role === 'kader') {
            $validationRules['bidang_id'] = ['required', 'uuid', 'exists:bidang_pengajuans,id'];
            // Posyandu otomatis dari Ketua Kader yang buat
        }

        // ✅ Validation untuk Kabid
        if ($request->role === 'kabid') {
            $validationRules['jenis_wilayah'] = ['required', 'in:kabupaten,kota'];
            $validationRules['kabupaten'] = ['required', 'string'];
        }

        $request->validate($validationRules);

        // Tentukan apakah langsung terverifikasi
        $isInstantVerified = in_array($request->role, ['admin', 'kabid', 'admin-kecamatan', 'operator-desa', 'ketua-kader']);

        // Simpan password asli untuk notifikasi
        $plainPassword = $request->password;

        // ✅ LOGIKA PENENTUAN WILAYAH & POSYANDU
        $kabupatenName = null;
        $kecamatanName = null;
        $jenisWilayah = null;
        $posyanduId = null;

        // 1. Jika membuat KABID baru (hanya Admin yang bisa)
        if ($request->role === 'kabid' && $request->filled('kabupaten')) {
            $kabupatenName = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
            $jenisWilayah = $request->jenis_wilayah;
        }

        if ($request->role === 'admin-kecamatan' && $currentUser->role === 'operator-desa') {
            $kabupatenName = $currentUser->kabupaten;
            $kecamatanName = $currentUser->kecamatan;
            $desaName = $currentUser->desa;
            // Posyandu ID dari request
        }
        // 2. Jika KABID membuat Admin Kecamatan
        if ($request->role === 'admin-kecamatan' && $currentUser->role === 'kabid') {
            $kabupatenName = $currentUser->kabupaten; // Inherit kabupaten dari Kabid
            $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;
        }

        // 3. Jika KABID atau ADMIN KECAMATAN membuat Ketua Kader
        if ($request->role === 'ketua-kader') {
            $posyanduId = $request->posyandu_id; // User pilih posyandu

            // Ambil data posyandu untuk get kabupaten & kecamatan
            $posyandu = \App\Models\Posyandu::find($posyanduId);
            if ($posyandu) {
                $kabupatenName = $posyandu->kabupaten;
                $kecamatanName = $posyandu->kecamatan;
            }
        }

        // 4. Jika KETUA KADER membuat Kader
        if ($request->role === 'kader' && $currentUser->role === 'ketua-kader') {
            // Otomatis inherit semua dari Ketua Kader
            $posyanduId = $currentUser->posyandu_id;
            $kabupatenName = $currentUser->kabupaten;
            $kecamatanName = $currentUser->kecamatan;
        }

        // ✅ Buat user baru
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => $request->role,
            'posyandu_id' => $posyanduId,
            'nik' => $request->filled('nik') ? $request->nik : null, // ✅ NULL jika kosong
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'verified_at' => $isInstantVerified ? now() : null,
            'verified_by' => $isInstantVerified ? $currentUser->id : null,
            'bidang_id' => $request->role === 'kader' ? $request->bidang_id : null,
            'kabupaten' => $kabupatenName,
            'kecamatan' => $kecamatanName,
            'jenis_wilayah' => $jenisWilayah,
            'is_active' => true,
        ];

        $user = User::create($userData);

        event(new Registered($user));

        // Kirim notifikasi
        if (in_array($request->role, ['kabid', 'admin-kecamatan', 'operator-desa', 'ketua-kader'])) {
            $user->notify(new \App\Notifications\UserCreatedNotification($user->toArray(), $plainPassword, $currentUser->name));
        }

        // Generate pesan WhatsApp
        if (in_array($request->role, ['kabid', 'admin-kecamatan', 'operator-desa', 'ketua-kader'])) {
            $this->sendWhatsAppMessage($user, $plainPassword, $currentUser);
        }

        // Redirect berdasarkan source
        if ($request->input('source') === 'posyandu_create') {
            return redirect()->route('admin.posyandu.create')
                ->with('success', 'User Ketua Kader berhasil dibuat! Silakan refresh halaman dan pilih dari dropdown.');
        }

        // Tambahkan info WhatsApp ke flash message
        if (in_array($request->role, ['kabid', 'ketua-kader', 'admin-kecamatan'])) {
            return redirect()->route('admin.users.index')
                ->with('success', 'User baru berhasil ditambahkan.')
                ->with('whatsapp_link', $this->generateWhatsAppLink($user, $plainPassword, $currentUser));
        }

        return redirect()->route('admin.users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    private function generateWhatsAppLink($user, $plainPassword, $createdBy)
    {
        $roleNames = [
            'kabid' => 'Kepala Bidang',
            'ketua-kader' => 'Ketua Kader',
            'admin-kecamatan' => 'Admin Kecamatan',
        ];

        $roleName = $roleNames[$user->role] ?? $user->role;

        // Ambil informasi tambahan
        $posyandu = $user->posyandu ? $user->posyandu->nama_posyandu : '-';
        $desa = $user->posyandu && $user->posyandu->desa ? $user->posyandu->desa : '-';
        $bidang = $user->bidang ? $user->bidang->nama_bidang : '-';

        $message = "🎉 *Selamat Datang di Sistem Posyandu!*\n\n";
        $message .= "Halo *{$user->name}*,\n\n";
        $message .= "Akun Anda telah berhasil dibuat oleh *{$createdBy->name}*.\n\n";
        $message .= "📋 *Detail Akun Anda:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";

        if ($user->email) {
            $message .= "📧 Email: {$user->email}\n";
        }

        $message .= "🔐 Password: `{$plainPassword}`\n";
        $message .= "👤 Role: {$roleName}\n";

        if ($user->role === 'kader' && $bidang !== '-') {
            $message .= "📁 Bidang: {$bidang}\n";
        }

        if ($posyandu !== '-') {
            $message .= "🏥 Posyandu: {$posyandu}\n";
        }

        if ($desa !== '-') {
            $message .= "📍 Desa: {$desa}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "⚠️ *PENTING:*\n";
        $message .= "• Segera login dan ganti password Anda\n";
        $message .= "• Simpan informasi login ini dengan aman\n";
        $message .= "• Jangan bagikan password kepada siapapun\n\n";
        $message .= "🔗 Silakan login di: " . route('login') . "\n\n";
        $message .= "Terima kasih! 🙏";

        // Format nomor telepon (hapus karakter non-digit, tambahkan 62 jika dimulai dengan 0)
        $phone = preg_replace('/[^0-9]/', '', $user->no_telepon);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    private function sendWhatsAppMessage($user, $plainPassword, $createdBy)
    {
        return $this->generateWhatsAppLink($user, $plainPassword, $createdBy);
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
        // Validasi input status
        $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $currentUser = Auth::user();
        $newStatus = $request->has('is_active') ? 1 : 0;
        $oldStatus = $user->is_active;

        // Cek apakah status berubah
        if ($newStatus !== $oldStatus) {
            $actionType = $newStatus ? 'activated' : 'deactivated';
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            // 1. Update User
            $user->update([
                'is_active' => $newStatus,
                'deactivated_at' => $newStatus ? null : now(),
                'deactivated_by' => $newStatus ? null : $currentUser->id,
                'deactivation_reason' => $newStatus ? null : $request->reason,
            ]);

            // 2. Catat History
            UserHistory::create([
                'user_id' => $user->id,
                'action_by' => $currentUser->id,
                'action_type' => $actionType,
                'description' => "User {$statusText} oleh {$currentUser->name}. Alasan: " . ($request->reason ?? '-'),
                'old_data' => json_encode(['is_active' => $oldStatus]),
                'new_data' => json_encode(['is_active' => $newStatus]),
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Status user {$user->name} berhasil {$statusText}.");
        }

        return redirect()
            ->back()
            ->with('info', 'Tidak ada perubahan status yang dilakukan.');
    }

    public function importPage()
    {
        return view('admin.users.import');
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

    public function deactivate(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $currentUser = Auth::user();

        $user->update([
            'status' => 'inactive',
            'deactivated_at' => now(),
            'deactivated_by' => $currentUser->id,
            'deactivation_reason' => $request->reason,
        ]);

        // ✅ LOGGING HISTORY
        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'deactivated',
            'description' => "User dinonaktifkan oleh {$currentUser->name}. Alasan: {$request->reason}",
            'old_data' => ['status' => 'active'],
            'new_data' => [
                'status' => 'inactive',
                'reason' => $request->reason,
            ],
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'User berhasil dinonaktifkan.');
    }

    public function activate(User $user)
    {
        $currentUser = Auth::user();

        $user->update([
            'status' => 'active',
            'deactivated_at' => null,
            'deactivated_by' => null,
            'deactivation_reason' => null,
        ]);

        // ✅ LOGGING HISTORY
        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'activated',
            'description' => "User diaktifkan kembali oleh {$currentUser->name}",
            'old_data' => ['status' => 'inactive'],
            'new_data' => ['status' => 'active'],
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'User berhasil diaktifkan kembali.');
    }

    // ========================================
    // OPERATOR DESA: Manage Kaders
    // ========================================

    /**
     * Operator Desa - List kaders di desanya
     */
    public function kaderIndex(Request $request)
    {
        $operator = Auth::user();

        if ($operator->role !== 'operator-desa') {
            abort(403);
        }

        $query = User::with(['posyandu', 'bidang'])
            ->where('role', 'kader')
            ->where('kecamatan', $operator->kecamatan);

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $kaders = $query->paginate(15);

        return view('admin.users.kader-manage', compact('kaders'));
    }

    /**
     * Operator Desa - Deactivate kader
     */
    public function deactivateKader(Request $request, User $kader)
    {
        if (Auth::user()->role !== 'operator-desa' || $kader->kecamatan !== Auth::user()->kecamatan) {
            abort(403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $kader->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => Auth::id(),
            'deactivation_reason' => $request->reason,
        ]);

        UserHistory::create([
            'user_id' => $kader->id,
            'action_by' => Auth::id(),
            'action_type' => 'deactivated',
            'description' => "Dinonaktifkan: {$request->reason}",
        ]);

        return back()->with('success', 'Kader berhasil dinonaktifkan.');
    }

    /**
     * Operator Desa - Reactivate kader
     */
    public function reactivateKader(User $kader)
    {
        if (Auth::user()->role !== 'operator-desa' || $kader->kecamatan !== Auth::user()->kecamatan) {
            abort(403);
        }

        $kader->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
            'deactivation_reason' => null,
        ]);

        UserHistory::create([
            'user_id' => $kader->id,
            'action_by' => Auth::id(),
            'action_type' => 'activated',
            'description' => "Diaktifkan kembali oleh " . Auth::user()->name,
        ]);

        return back()->with('success', 'Kader berhasil diaktifkan kembali.');
    }

    // ========================================
    // KETUA KADER: Takeover Kader
    // ========================================

    /**
     * Ketua Kader - List kaders yang bisa diambil alih
     */
    public function takeoverIndex()
    {
        $ketuaKader = Auth::user();

        if ($ketuaKader->role !== 'ketua-kader') {
            abort(403);
        }

        $kaders = User::with(['bidang'])
            ->where('role', 'kader')
            ->where('posyandu_id', $ketuaKader->posyandu_id)
            ->get();

        return view('admin.users.takeover', compact('kaders'));
    }

    /**
     * Ketua Kader - Reset password kader (non-aktif)
     */
    public function takeoverResetPassword(Request $request, User $kader)
    {
        $ketuaKader = Auth::user();

        if (
            $ketuaKader->role !== 'ketua-kader' ||
            $kader->posyandu_id !== $ketuaKader->posyandu_id ||
            $kader->is_active
        ) {
            abort(403, 'Hanya bisa reset password kader non-aktif di posyandu Anda.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
            'reason' => 'required|string|max:500',
        ]);

        $kader->update([
            'password' => Hash::make($request->new_password),
            'is_active' => true,
            'deactivated_at' => null,
        ]);

        UserHistory::create([
            'user_id' => $kader->id,
            'action_by' => Auth::id(),
            'action_type' => 'updated',
            'description' => "Password direset & diaktifkan kembali: {$request->reason}",
        ]);

        return back()->with('success', 'Password kader berhasil direset.')
            ->with('new_password', $request->new_password);
    }
}