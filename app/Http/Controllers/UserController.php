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
        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        // ✅ Fetch Kabupaten dan Kota dari API
        $wilayahData = $this->fetchWilayahData(
            'regencies/' . self::PROVINCE_ID . '.json',
            'kabupatens_jateng'
        );

        // ✅ Pisahkan berdasarkan prefix "Kabupaten" dan "Kota"
        $kabupatenList = [];
        $kotaList = [];

        foreach (($wilayahData['data'] ?? []) as $wilayah) {
            // Cek apakah nama diawali dengan "Kota"
            if (stripos($wilayah['name'], 'Kota ') === 0) {
                $kotaList[] = $wilayah;
            }
            // Cek apakah nama diawali dengan "Kabupaten"
            elseif (stripos($wilayah['name'], 'Kabupaten ') === 0) {
                $kabupatenList[] = $wilayah;
            }
        }

        return view('admin.users.create', compact('posyandus', 'bidangs', 'kabupatenList', 'kotaList'));
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
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($allowedRoles)],
            'posyandu_id' => ['nullable', 'uuid', 'exists:posyandus,id'],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
        ];

        if ($request->role === 'kabid') {
            $validationRules['jenis_wilayah'] = ['required', 'in:kabupaten,kota'];
            $validationRules['kabupaten'] = ['required', 'string'];
        }

        if ($request->role === 'kader') {
            $validationRules['bidang_id'] = ['required', 'uuid', 'exists:bidang_pengajuans,id'];
        }

        $request->validate($validationRules);

        $isInstantVerified = in_array($request->role, ['admin', 'kabid', 'ketua-kader']);

        // Simpan password asli sebelum di-hash
        $plainPassword = $request->password;

        $kabupatenName = null;
        $jenisWilayah = null;

        if ($request->filled('kabupaten') && $request->role === 'kabid') {
            $kabupatenName = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        }

        if ($request->filled('jenis_wilayah') && $request->role === 'kabid') {
            $jenisWilayah = $request->jenis_wilayah;
        }

        // Buat user baru
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => $request->role,
            'posyandu_id' => $request->posyandu_id,
            'nik' => $request->nik,
            'status' => 'active',
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'verified_at' => $isInstantVerified ? now() : null,
            'verified_by' => $isInstantVerified ? $currentUser->id : null,
            'bidang_id' => $request->role === 'kader' ? $request->bidang_id : null,
            'kabupaten' => $kabupatenName,
            'jenis_wilayah' => $jenisWilayah,
        ];

        if ($request->role === 'kader' && $request->filled('bidang_id')) {
            $userData['bidang_id'] = $request->bidang_id;
        }

        $user = User::create($userData);

        event(new Registered($user));

        // Import notification class di bagian atas controller
        // use App\Notifications\UserCreatedNotification;

        // Kirim notifikasi ke user yang baru dibuat
        if (in_array($request->role, ['kabid', 'ketua-kader'])) {
            $user->notify(new \App\Notifications\UserCreatedNotification($user->toArray(), $plainPassword, $currentUser->name));
        }

        // Generate pesan WhatsApp
        if (in_array($request->role, ['kabid', 'ketua-kader'])) {
            $this->sendWhatsAppMessage($user, $plainPassword, $currentUser);
        }

        if ($request->input('source') === 'posyandu_create') {
            return redirect()->route('admin.posyandu.create')
                ->with('success', 'User Ketua Kader berhasil dibuat! Silakan refresh halaman dan pilih dari dropdown.');
        }

        // Tambahkan info WhatsApp ke flash message
        if (in_array($request->role, ['kabid', 'ketua-kader'])) {
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
            // Tambahkan validasi alasan (opsional tapi disarankan)
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
}
