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

            case 'operator-desa':
                // ✅ Operator Desa: Hanya lihat KADER di posyandunya
                $query->where('role', 'kader')
                    ->where('posyandu_id', $currentUser->posyandu_id);
                break;
            case 'kades':
                $query->whereIn('role', ['admin-kabupaten', 'kabid', 'admin-kecamatan', 'ketua-kader', 'operator-desa', 'kader', 'masyarakat']);
                if ($currentUser->posyandu_id) {
                    $query->whereHas('user', fn($q) => $q->where('posyandu_id', $currentUser->posyandu_id));
                } elseif ($currentUser->desa) {
                    $query->whereHas('user', fn($q) => $q->where('desa', $currentUser->desa));
                }
                break;
            case 'ketua-kader':
                $query->whereIn('role', ['operator-desa', 'kader', 'masyarakat'])
                    ->where('posyandu_id', $currentUser->posyandu_id);
                break;

            case 'admin-kecamatan':
                $query->whereIn('role', ['ketua-kader', 'operator-desa', 'kader', 'masyarakat']);
                break;

            case 'kabid':
                $query->whereIn('role', ['admin-kecamatan', 'ketua-kader', 'operator-desa', 'kader', 'masyarakat']);
                break;

            case 'admin-kabupaten':
                $query->whereIn('role', ['kabid', 'ketua-posyandu', 'admin-kabupaten', 'admin-kecamatan', 'operator-desa', 'kades']);
                if ($currentUser->kabupaten) {
                    $query->where('kabupaten', 'LIKE', "%{$currentUser->kabupaten}%");
                }
                break;
            case 'ketua-posyandu':
                $query->whereIn('role', ['kabid', 'admin-kecamatan', 'operator-desa']);
                break;
            case 'admin':
                // Admin bisa lihat semua role
                // Tidak perlu filter role
                break;
        }

        // 2. Filter Wilayah (Multi-Tenancy)
        if (in_array($currentUser->role, ['kader'])) {
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
            // Kabid filter berdasarkan kabupaten (opsional)
            // if ($currentUser->kabupaten) {
            //     $query->where('kabupaten', 'LIKE', "%{$currentUser->kabupaten}%");
            // }
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

        // 5. Filter Status (untuk Operator Desa)
        if ($currentUser->role === 'operator-desa' && $request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $currentUser = Auth::user();
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        $kabupatens = Kabupaten::orderBy('jenis')->orderBy('nama_kabupaten')->get();
        $kecamatans = collect();

        // ✅ Filter posyandu berdasarkan role
        if ($currentUser->role === 'ketua-kader') {
            $posyandus = Posyandu::where('id', $currentUser->posyandu_id)->get();
        } elseif ($currentUser->role === 'operator-desa') {
            $posyandus = Posyandu::where('desa', $currentUser->posyandu->desa)
                ->where('kecamatan', $currentUser->posyandu->kecamatan)
                ->orderBy('nama_posyandu')->get();
        } elseif ($currentUser->role === 'admin-kecamatan') {
            $posyandus = Posyandu::where('kecamatan_id', $currentUser->kecamatan_id)
                ->orderBy('nama_posyandu')->get();
        } elseif ($currentUser->role === 'kabid') {
            $posyandus = Posyandu::where('kabupaten_id', $currentUser->kabupaten_id)
                ->orderBy('nama_posyandu')->get();

            $kecamatans = Kecamatan::where('kabupaten_id', $currentUser->kabupaten_id)
                ->orderBy('nama_kecamatan')->get();
        } elseif (in_array($currentUser->role, ['admin'])) {
            $posyandus = Posyandu::orderBy('nama_posyandu')->get();
            $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        }

        // Fetch Kabupaten dan Kota dari API
        $kabupatenList = [];
        $kotaList = [];

        if (in_array($currentUser->role, ['admin', 'kabid', 'ketua-posyandu', 'admin-kabupaten', 'operator-desa', 'admin-kecamatan'])) {
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

        // ✅ Jika dari pilih-user, set default role ke 'masyarakat'
        $defaultRole = null;
        if ($request->get('source') === 'pilih-user') {
            $defaultRole = 'masyarakat';
        }

        return view('admin.users.create', compact(
            'posyandus',
            'bidangs',
            'kabupatens',
            'kecamatans',
            'kabupatenList',
            'kotaList',
            'defaultRole'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        // ✅ Tentukan role yang diperbolehkan berdasarkan role pembuat
        $allowedRoles = $this->getAllowedRoles($currentUser->role);

        // ✅ Auto-assign role untuk beberapa pembuat (SEBELUM validasi!)
        $this->autoAssignRole($request, $currentUser->role);

        // ✅ PASTIKAN role sudah ada sebelum validasi
        $roleToValidate = $request->input('role');
        if (empty($roleToValidate)) {
            return redirect()->back()
                ->withErrors(['role' => 'Role harus dipilih atau otomatis terisi.'])
                ->withInput();
        }

        // ✅ Validasi input
        $validationRules = $this->buildValidationRules($roleToValidate, $allowedRoles, $currentUser);

        if ($request->role === 'masyarakat') {
            $validationRules['rw'] = ['required', 'string', 'regex:/^RW\d{2}$/'];
            $validationRules['rt'] = ['nullable', 'string', 'regex:/^RT\d{3}$/'];
            $validationRules['posyandu_id'] = ['required', 'uuid', 'exists:posyandus,id'];
        }

        $request->validate($validationRules);

        if ($request->role === 'masyarakat' && $request->filled('posyandu_id')) {
            $posyandu = Posyandu::find($request->posyandu_id);

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

        $plainPassword = $request->password;

        // ✅ Tentukan data wilayah & posyandu
        $locationData = $this->determineLocationData($request, $currentUser);

        // ✅ Tentukan apakah langsung terverifikasi
        $isInstantVerified = in_array($request->role, [
            'admin',
            'ketua-posyandu',
            'kabid',
            'kades',
            'admin-kabupaten',
            'admin-kecamatan',
            'ketua-kader',
            'operator-desa'
        ]);

        // ✅ Buat user baru
        $userData = array_merge([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => $request->role,
            'nik' => $request->filled('nik') ? $request->nik : null,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'verified_at' => $isInstantVerified ? now() : null,
            'verified_by' => $isInstantVerified ? $currentUser->id : null,
            'is_active' => true,

            // ✅ TAMBAHAN: RW/RT untuk role masyarakat
            'rw' => $request->role === 'masyarakat' ? $request->rw : null,
            'rt' => $request->role === 'masyarakat' ? $request->rt : null,
        ], $locationData);

        $user = User::create($userData);

        // ✅ Log ke User History
        $this->logUserHistory($user, $currentUser, 'created', 'User baru dibuat');

        // ✅ Event & Notifikasi
        event(new Registered($user));

        // Kirim notifikasi untuk role tertentu
        if ($this->shouldSendNotification($request->role)) {
            $user->notify(new \App\Notifications\UserCreatedNotification(
                $user->toArray(),
                $plainPassword,
                $currentUser->name
            ));

            $this->sendWhatsAppMessage($user, $plainPassword, $currentUser);
        }

        // ✅ Handle redirect
        return $this->handleRedirect($request, $user, $plainPassword, $currentUser);
    }

    private function getAllowedRoles(string $role): array
    {
        $rolesMap = [
            'admin' => [
                'admin-kabupaten',
                'ketua-posyandu',
                'kabid',
                'admin-kecamatan',
                'ketua-kader',
                'kades',
                'operator-desa',
                'kader',
                'masyarakat',
                'admin'
            ],
            'ketua-posyandu' => [
                'kabid',
                'admin-kecamatan',
                'ketua-kader',
                'kades',
                'kader',
                'masyarakat',
            ],
            'admin-kabupaten' => [
                'kabid',
                'ketua-posyandu',
                'admin-kecamatan',
                'kades',
                'operator-desa',
                'admin-kabupaten',
            ],
            'kabid' => [
                'admin-kecamatan',
                'ketua-kader',
                'kader',
            ],
            'admin-kecamatan' => [
                'ketua-kader',
                'kader',
            ],
            'ketua-kader' => [
                'operator-desa',
                'kader',
            ],
            'operator-desa' => [
                'kader'
            ],
            'kader' => [
                'masyarakat',
            ],
        ];

        return $rolesMap[$role] ?? [];
    }

    private function autoAssignRole(Request $request, string $currentRole): void
    {
        $autoAssignMap = [
            'kader' => 'masyarakat',
            'operator-desa' => 'kader',      // ✅ Tambah ini
            'ketua-kader' => 'kader',
        ];

        if (isset($autoAssignMap[$currentRole]) && !$request->filled('role')) {
            $request->merge(['role' => $autoAssignMap[$currentRole]]);
        }
    }

    private function buildValidationRules(?string $role, array $allowedRoles, User $currentUser): array
    {
        if (empty($role)) {
            return [
                'role' => ['required', Rule::in($allowedRoles)],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ];
        }

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($allowedRoles)],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'nik' => ['nullable', 'string', 'max:16', 'unique:users,nik'],
        ];

        // Role-specific validations
        $roleSpecificRules = [
            'ketua-posyandu' => [
                'jenis_wilayah' => ['required', 'in:kabupaten,kota'],
                'kabupaten' => ['required', 'string'],
            ],
            'kabid' => [
                'bidang_id' => ['required', 'uuid', 'exists:bidang_pengajuans,id'],
                'kabupaten' => ['required', 'string'],
            ],
            'kades' => [
                'posyandu_id' => ['required', 'uuid', 'exists:posyandus,id'],
            ],
            'admin-kecamatan' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
            ],
            'ketua-kader' => [
                'posyandu_id' => ['required', 'uuid', 'exists:posyandus,id'],
            ],
            'operator-desa' => [
                'posyandu_id' => ['required', 'uuid', 'exists:posyandus,id'],
            ],
            'kader' => [
                'bidang_id' => ['required', 'uuid', 'exists:bidang_pengajuans,id'],
                'posyandu_id' => ['nullable', 'uuid', 'exists:posyandus,id'],
            ],
        ];

        if (isset($roleSpecificRules[$role])) {
            $baseRules = array_merge($baseRules, $roleSpecificRules[$role]);
        }

        return $baseRules;
    }

    private function determineLocationData(Request $request, User $currentUser): array
    {
        $data = [
            'kabupaten' => null,
            'kecamatan' => null,
            'desa' => null,
            'jenis_wilayah' => null,
            'posyandu_id' => null,
            'bidang_id' => null,
        ];

        switch ($request->role) {
            case 'admin-kabupaten':
                // ✅ Pilih Kabupaten (dari API)
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $data['kabupaten'] = $kabupatenName;
                $data['jenis_wilayah'] = $request->jenis_wilayah ?? 'kabupaten';
                break;
            case 'ketua-posyandu':
                // ✅ Simpan string dari API: "3302_KABUPATEN BANYUMAS"
                // Kita extract nama kabupaten saja
                $kabupatenValue = $request->kabupaten; // Format: "3302_KABUPATEN BANYUMAS"
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $data['kabupaten'] = $kabupatenName; // "KABUPATEN BANYUMAS"
                $data['jenis_wilayah'] = $request->jenis_wilayah;
                break;

            case 'kabid':
                // ✅ Pilih Bidang + Kabupaten (string dari API)
                $data['bidang_id'] = $request->bidang_id;

                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $data['kabupaten'] = $kabupatenName;
                $data['jenis_wilayah'] = $request->jenis_wilayah ?? 'kabupaten';
                break;
            case 'kades':
                // ✅ Kades mirip dengan Ketua Kader
                $posyanduId = $request->posyandu_id;
                $posyandu = Posyandu::find($posyanduId);

                if ($posyandu) {
                    $data['posyandu_id'] = $posyanduId;
                    $data['kabupaten'] = $posyandu->kabupaten;
                    $data['kecamatan'] = $posyandu->kecamatan;
                    $data['desa'] = $posyandu->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah ?? null;
                }
                break;

            case 'admin-kecamatan':
                // ✅ Pilih Kabupaten + Kecamatan (dari API)
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $kecamatanValue = $request->kecamatan;
                $kecamatanName = explode('_', $kecamatanValue)[1] ?? $kecamatanValue;

                $data['kabupaten'] = $kabupatenName;
                $data['kecamatan'] = $kecamatanName;
                $data['jenis_wilayah'] = $request->jenis_wilayah ?? 'kabupaten';
                break;

            case 'ketua-kader':
                // ✅ Ambil dari Posyandu
                $posyanduId = $request->posyandu_id;
                $posyandu = Posyandu::find($posyanduId);

                if ($posyandu) {
                    $data['posyandu_id'] = $posyanduId;
                    $data['kabupaten'] = $posyandu->kabupaten;
                    $data['kecamatan'] = $posyandu->kecamatan;
                    $data['desa'] = $posyandu->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah ?? null;
                }
                break;

            case 'operator-desa':
                // ✅ Inherit dari Posyandu
                $posyanduId = $request->posyandu_id;
                $posyandu = Posyandu::find($posyanduId);

                if ($posyandu) {
                    $data['posyandu_id'] = $posyanduId;
                    $data['kabupaten'] = $posyandu->kabupaten;
                    $data['kecamatan'] = $posyandu->kecamatan;
                    $data['desa'] = $posyandu->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah ?? null;
                }
                break;

            case 'kader':
                $data['bidang_id'] = $request->bidang_id;

                if ($currentUser->role === 'ketua-kader') {
                    // Inherit dari ketua kader
                    $data['posyandu_id'] = $currentUser->posyandu_id;
                    $data['kabupaten'] = $currentUser->kabupaten;
                    $data['kecamatan'] = $currentUser->kecamatan;
                    $data['desa'] = $currentUser->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah;
                }
                // ✅ TAMBAHKAN INI untuk Operator Desa
                elseif ($currentUser->role === 'operator-desa') {
                    $data['posyandu_id'] = $currentUser->posyandu_id;
                    $data['kabupaten'] = $currentUser->kabupaten;
                    $data['kecamatan'] = $currentUser->kecamatan;
                    $data['desa'] = $currentUser->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah;
                } else {
                    // Role lain pilih posyandu manual
                    if ($request->filled('posyandu_id')) {
                        $posyandu = Posyandu::find($request->posyandu_id);
                        if ($posyandu) {
                            $data['posyandu_id'] = $posyandu->id;
                            $data['kabupaten'] = $posyandu->kabupaten;
                            $data['kecamatan'] = $posyandu->kecamatan;
                            $data['desa'] = $posyandu->desa;
                            $data['jenis_wilayah'] = $currentUser->jenis_wilayah;
                        }
                    }
                }
                break;
            case 'masyarakat':
                // ✅ Inherit dari kader
                if ($currentUser->role === 'kader') {
                    $data['posyandu_id'] = $currentUser->posyandu_id;
                    $data['kabupaten'] = $currentUser->kabupaten;
                    $data['kecamatan'] = $currentUser->kecamatan;
                    $data['desa'] = $currentUser->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah;
                }
                break;
        }

        return $data;
    }

    private function shouldSendNotification(string $role): bool
    {
        return in_array($role, [
            'ketua-posyandu',
            'kabid',
            'kades',
            'admin-kecamatan',
            'ketua-kader',
            'operator-desa'
        ]);
    }

    private function logUserHistory(User $user, User $actionBy, string $actionType, string $description, array $oldData = null, array $newData = null): void
    {
        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $actionBy->id,
            'action_type' => $actionType,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }

    private function handleRedirect(Request $request, User $user, string $plainPassword, User $currentUser)
    {
        // ✅ PRIORITAS 1: Redirect khusus dari halaman pilih-user
        if ($request->input('source') === 'pilih-user') {
            return redirect()->route('dashboard.partials.pilih-user')
                ->with('success', 'User masyarakat berhasil dibuat! Silakan pilih dari daftar untuk membuat ajuan.');
        }

        // ✅ PRIORITAS 2: Redirect khusus dari halaman create posyandu
        if ($request->input('source') === 'posyandu_create') {
            return redirect()->route('admin.posyandu.create')
                ->with('success', 'User Ketua Kader berhasil dibuat! Silakan refresh halaman dan pilih dari dropdown.');
        }

        // ✅ PRIORITAS 3: Redirect dengan WhatsApp link untuk role tertentu
        if ($this->shouldSendNotification($request->role)) {
            return redirect()->route('admin.users.index')
                ->with('success', 'User baru berhasil ditambahkan.')
                ->with('whatsapp_link', $this->generateWhatsAppLink($user, $plainPassword, $currentUser));
        }

        // ✅ DEFAULT: Redirect ke user index
        return redirect()->route('admin.users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
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
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
                'role' => 'nullable|string'
            ]);

            $currentUser = Auth::user();
            $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

            if (empty($allowedRoles)) {
                return back()->withErrors(['error' => 'Role Anda tidak memiliki akses untuk import user.']);
            }

            $requestedRole = $request->input('role');
            $roleToCreate = in_array($requestedRole, $allowedRoles, true)
                ? $requestedRole
                : $allowedRoles[0];

            $file = $request->file('file');
            $countBefore = User::count();

            // Import users dari Excel dengan passing user yang melakukan import
            Excel::import(new UsersImport($roleToCreate, $currentUser), $file);

            $countAfter = User::count();
            $imported = $countAfter - $countBefore;

            $message = $imported > 0
                ? "Berhasil import {$imported} user ke database"
                : "Import selesai. Data mungkin sudah ada atau tidak valid.";

            Log::info('User Import Success', [
                'importing_user_id' => $currentUser->id,
                'importing_user_role' => $currentUser->role,
                'role_to_create' => $roleToCreate,
                'total_imported' => $imported
            ]);

            return back()->with('success', $message)->with('imported', $imported);
        } catch (\Exception $e) {
            Log::error('Import Users Error', [
                'error' => $e->getMessage(),
                'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan saat import: ' . $e->getMessage()]);
        }
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
            'file' => 'required|file|mimes:xlsx,xls',
            'role' => 'nullable|string'
        ]);

        $currentUser = Auth::user();
        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        if (empty($allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Role Anda tidak memiliki akses untuk import user.'
            ], 403);
        }

        $requestedRole = $request->input('role');
        $roleToCreate = in_array($requestedRole, $allowedRoles, true)
            ? $requestedRole
            : $allowedRoles[0];

        try {
            Excel::import(new UsersImport($roleToCreate, $currentUser), $request->file('file'));

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
            $currentUser = Auth::user();

            $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);
            if (empty($allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak memiliki akses untuk import user.'
                ], 403);
            }

            $requestedRole = request('role');
            $roleToCreate = in_array($requestedRole, $allowedRoles, true)
                ? $requestedRole
                : $allowedRoles[0];

            // Filter posyandu berdasarkan role user yang login
            $query = Posyandu::select('id', 'nama_posyandu', 'desa', 'kecamatan', 'kabupaten');

            switch ($currentUser->role) {
                case 'kader':
                    // Kader hanya lihat posyandu yang dia pegang
                    $query->where('id', $currentUser->posyandu_id);
                    break;

                case 'ketua-kader':
                    // Ketua-kader hanya lihat posyandu yang dia pegang
                    $query->where('id', $currentUser->posyandu_id);
                    break;

                case 'operator-desa':
                    // Operator-desa hanya lihat posyandu di desa yang sama
                    $posyandus = $currentUser->posyandu;
                    $query->where('desa', $posyandus->desa)
                        ->where('kecamatan', $posyandus->kecamatan);
                    break;

                case 'admin-kecamatan':
                    // Admin-kecamatan lihat semua posyandu di kecamatan
                    $query->where('kecamatan_id', $currentUser->kecamatan_id);
                    break;

                case 'kabid':
                    // Kabid lihat semua posyandu di kabupaten
                    $query->where('kabupaten_id', $currentUser->kabupaten_id);
                    break;

                case 'admin-kabupaten':
                    // Admin-kabupaten lihat semua posyandu di seluruh kabupaten
                    if ($currentUser->kabupaten) {
                        $query->where('kabupaten', 'LIKE', "%{$currentUser->kabupaten}%");
                    }
                    break;

                case 'admin':
                    // Admin bisa lihat semua posyandu
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Role tidak memiliki akses untuk import user.'
                    ], 403);
            }

            $posyandus = $query->orderBy('kecamatan')->orderBy('desa')->get();

            Log::info('Export User Template', [
                'current_user_role' => $currentUser->role,
                'total_posyandu' => $posyandus->count(),
                'role_to_create' => $roleToCreate
            ]);

            if ($posyandus->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data posyandu untuk role Anda.'
                ], 404);
            }

            $dataRows = [];

            // Loop setiap posyandu untuk generate baris
            foreach ($posyandus as $posyandu) {
                // Untuk operator-desa: generate 6 baris per posyandu (1 per bidang SPM)
                if ($roleToCreate === 'kader') {
                    // Ambil 6 bidang (jumlah bidang SPM)
                    $bidangs = BidangPengajuan::all()->take(6);
                    
                    foreach ($bidangs as $bidang) {
                        $dataRows[] = [
                            'desa' => $this->cleanDesaName($posyandu->desa),
                            'kecamatan' => $this->cleanKecamatanName($posyandu->kecamatan),
                            'kabupaten' => strtoupper($posyandu->kabupaten)
                        ];
                    }
                } else {
                    // Untuk role lain: 1 baris per posyandu
                    $dataRows[] = [
                        'desa' => $this->cleanDesaName($posyandu->desa),
                        'kecamatan' => $this->cleanKecamatanName($posyandu->kecamatan),
                        'kabupaten' => strtoupper($posyandu->kabupaten)
                    ];
                }
            }

            Log::info('Total Rows Generated', ['count' => count($dataRows), 'role_to_create' => $roleToCreate]);

            $filename = 'Template_User_' . str_replace('-', '_', $roleToCreate) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new UsersTemplateExport($dataRows, $roleToCreate),
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
        // Handle null atau tipe yang bukan string
        if (!is_string($name)) {
            return '';
        }
        
        $cleaned = str_ireplace(['DESA ', 'KELURAHAN '], '', $name);
        return strtoupper(trim($cleaned));
    }

    /**
     * Helper: Clean nama kecamatan
     */
    private function cleanKecamatanName($name)
    {
        // Handle null atau tipe yang bukan string
        if (!is_string($name)) {
            return '';
        }
        
        $cleaned = str_ireplace('KECAMATAN ', '', $name);
        return strtoupper(trim($cleaned));
    }

    /**
     * Helper: Role target yang boleh dibuat berdasarkan role user
     */
    private function getAllowedRoleTargets(string $role): array
    {
        $roleMap = [
            'kader' => ['masyarakat'],
            'ketua-kader' => ['kader'],
            'operator-desa' => ['ketua-kader', 'kader'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'ketua-kader', 'operator-desa', 'kader', 'masyarakat'],
        ];

        return $roleMap[$role] ?? [];
    }

    public function deactivate(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $currentUser = Auth::user();

        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if ($user->role !== 'kader' || $user->posyandu_id !== $currentUser->posyandu_id) {
            return redirect()->back()->with('error', 'Anda hanya bisa nonaktifkan kader di posyandu Anda');
        }


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

        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if ($user->role !== 'kader' || $user->posyandu_id !== $currentUser->posyandu_id) {
            return redirect()->back()->with('error', 'Anda hanya bisa aktifkan kader di posyandu Anda');
        }

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

    public function resetPasswordKader(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // Validasi: Hanya Operator Desa yang bisa
        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        // Validasi: Hanya Kader di posyandu yang sama
        if ($user->role !== 'kader' || $user->posyandu_id !== $currentUser->posyandu_id) {
            return redirect()->back()->with('error', 'Anda hanya bisa reset password kader di posyandu Anda');
        }

        $request->validate([
            'new_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Log history
        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'updated',
            'description' => "Password direset oleh {$currentUser->name}",
        ]);

        return redirect()->back()->with('success', 'Password kader berhasil direset');
    }

    public function resetPasswordKabid(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'admin-kabupaten') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if (
            !in_array($user->role, ['kabid', 'ketua-kader']) ||
            $user->kabupaten !== $currentUser->kabupaten
        ) {
            return redirect()->back()->with('error', 'Anda hanya bisa reset password user di kabupaten Anda');
        }

        $request->validate([
            'new_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'updated',
            'description' => "Password direset oleh {$currentUser->name}",
        ]);

        return redirect()->back()->with('success', 'Password berhasil direset');
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
    public function deactivateKader(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if ($user->role !== 'kader' || $user->posyandu_id !== $currentUser->posyandu_id) {
            return redirect()->back()->with('error', 'Anda hanya bisa nonaktifkan kader di posyandu Anda');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => $currentUser->id,
            'deactivation_reason' => $request->reason,
        ]);

        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'deactivated',
            'description' => "User dinonaktifkan oleh {$currentUser->name}. Alasan: {$request->reason}",
            'old_data' => json_encode(['is_active' => true]),
            'new_data' => json_encode(['is_active' => false, 'reason' => $request->reason]),
        ]);

        return redirect()->back()->with('success', 'Kader berhasil dinonaktifkan.');
    }

    /**
     * Operator Desa - Reactivate kader
     */
    public function reactivateKader(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if ($user->role !== 'kader' || $user->posyandu_id !== $currentUser->posyandu_id) {
            return redirect()->back()->with('error', 'Anda hanya bisa aktifkan kader di posyandu Anda');
        }

        $user->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
            'deactivation_reason' => null,
        ]);

        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'activated',
            'description' => "User diaktifkan kembali oleh {$currentUser->name}",
            'old_data' => json_encode(['is_active' => false]),
            'new_data' => json_encode(['is_active' => true]),
        ]);

        return redirect()->back()->with('success', 'Kader berhasil diaktifkan kembali.');
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
