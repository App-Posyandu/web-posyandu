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
    use AuthorizesRequests;

    const PROVINCE_ID = 33;
    const API_TIMEOUT = 10;
    const CACHE_TTL = 3600;

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

        switch ($currentUser->role) {
            case 'kader':
                $query->where('role', 'masyarakat');
                break;

            case 'operator-desa':
                $query->whereIn('role', ['ketua-posyandu', 'kader'])
                    ->where('posyandu_id', $currentUser->posyandu_id);
                break;
            case 'kades':
                $query->whereIn('role', ['admin-kabupaten', 'kabid', 'admin-kecamatan', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat']);
                if ($currentUser->posyandu_id) {
                    $query->whereHas('user', fn($q) => $q->where('posyandu_id', $currentUser->posyandu_id));
                } elseif ($currentUser->desa) {
                    $query->whereHas('user', fn($q) => $q->where('desa', $currentUser->desa));
                }
                break;
            case 'ketua-posyandu':
                $query->whereIn('role', ['operator-desa', 'kader', 'masyarakat'])
                    ->where('posyandu_id', $currentUser->posyandu_id);
                break;

            case 'admin-kecamatan':
                $query->whereIn('role', ['ketua-posyandu', 'operator-desa', 'kader', 'masyarakat']);
                break;

            case 'kabid':
                $query->whereIn('role', ['admin-kecamatan', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat']);
                break;

            case 'admin-kabupaten':
                $query->whereIn('role', ['kabid', 'ketua-timpembina-posyandu', 'admin-kabupaten', 'admin-kecamatan', 'operator-desa', 'kades']);
                if ($currentUser->kabupaten) {
                    $query->where('kabupaten', 'LIKE', "%{$currentUser->kabupaten}%");
                }
                break;
            case 'ketua-timpembina-posyandu':
                $query->whereIn('role', ['kabid', 'admin-kecamatan', 'operator-desa']);
                break;
            case 'admin':
                break;
        }

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
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('role') && $request->input('role') !== '') {
            $query->where('role', $request->input('role'));
        }

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

    public function create(Request $request)
    {
        $currentUser = Auth::user();
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        $kabupatens = Kabupaten::orderBy('jenis')->orderBy('nama_kabupaten')->get();
        $kecamatans = collect();

        if ($currentUser->role === 'ketua-posyandu') {
            $posyandus = Posyandu::where('id', $currentUser->posyandu_id)->get();
        } elseif ($currentUser->role === 'operator-desa') {
            $posyandus = Posyandu::where('desa', $currentUser->desa)
                ->where('kecamatan', $currentUser->kecamatan)
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

        $kabupatenList = [];
        $kotaList = [];

        if (in_array($currentUser->role, ['admin', 'kabid', 'ketua-timpembina-posyandu', 'admin-kabupaten', 'operator-desa', 'admin-kecamatan'])) {
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

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $allowedRoles = $this->getAllowedRoles($currentUser->role);

        $this->autoAssignRole($request, $currentUser->role);

        $roleToValidate = $request->input('role');
        if (empty($roleToValidate)) {
            return redirect()->back()
                ->withErrors(['role' => 'Role harus dipilih atau otomatis terisi.'])
                ->withInput();
        }

        $validationRules = $this->buildValidationRules($roleToValidate, $allowedRoles, $currentUser);

        if ($request->role === 'masyarakat') {
            $validationRules['rw'] = ['required', 'string'];
            $validationRules['rt'] = ['required', 'string'];

            $validationRules['kabupaten'] = ['required', 'string'];
            $validationRules['kecamatan'] = ['required', 'string'];
            $validationRules['desa'] = ['required', 'string'];
            $validationRules['posyandu_id'] = ['required', 'uuid', 'exists:posyandus,id'];
        }

        $request->validate($validationRules);
        if ($request->role === 'ketua-posyandu' && $request->filled('posyandu_id')) {
            $existingActiveKetua = User::where('role', 'ketua-posyandu')
                ->where('posyandu_id', $request->posyandu_id)
                ->where('is_active', true)
                ->first();

            if ($existingActiveKetua) {
                $posyanduName = Posyandu::find($request->posyandu_id)?->nama_posyandu ?? 'Posyandu ini';
                return redirect()->back()
                    ->with('swal_error', [
                        'title' => 'Ketua Posyandu Sudah Ada',
                        'text' => "Tidak dapat membuat Ketua Posyandu baru karena sudah ada Ketua Posyandu aktif ({$existingActiveKetua->name}) di {$posyanduName}. Nonaktifkan terlebih dahulu Ketua Posyandu yang aktif sebelum menambahkan yang baru."
                    ])
                    ->withInput();
            }
        }

        // Check for existing active kader in the same bidang and posyandu
        if ($request->role === 'kader' && $request->filled('bidang_id') && $request->filled('posyandu_id')) {
            $existingActiveKader = User::where('role', 'kader')
                ->where('posyandu_id', $request->posyandu_id)
                ->where('bidang_id', $request->bidang_id)
                ->where('is_active', true)
                ->first();

            if ($existingActiveKader) {
                $bidang = \App\Models\BidangPengajuan::find($request->bidang_id);
                $bidangName = $bidang?->nama_bidang ?? 'bidang ini';
                $posyanduName = Posyandu::find($request->posyandu_id)?->nama_posyandu ?? 'posyandu ini';
                return redirect()->back()
                    ->with('swal_error', [
                        'title' => 'Kader Bidang Sudah Ada',
                        'text' => "Tidak dapat membuat Kader baru karena sudah ada Kader aktif ({$existingActiveKader->name}) di bidang {$bidangName} pada {$posyanduName}. Nonaktifkan terlebih dahulu Kader yang aktif di bidang tersebut sebelum menambahkan yang baru."
                    ])
                    ->withInput();
            }
        }

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

        $locationData = $this->determineLocationData($request, $currentUser);

        $isInstantVerified = in_array($request->role, [
            'admin',
            'ketua-timpembina-posyandu',
            'kabid',
            'kades',
            'admin-kabupaten',
            'admin-kecamatan',
            'ketua-posyandu',
            'operator-desa'
        ]);

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
            'verified_at' => now(),
            'verified_by' => $currentUser->id,
            'is_active' => true,

            'rw' => $request->role === 'masyarakat' ? $request->rw : null,
            'rt' => $request->role === 'masyarakat' ? $request->rt : null,
        ], $locationData);

        $user = User::create($userData);

        $this->logUserHistory($user, $currentUser, 'created', 'User baru dibuat');

        event(new Registered($user));

        if ($this->shouldSendNotification($request->role)) {
            $user->notify(new \App\Notifications\UserCreatedNotification(
                $user->toArray(),
                $plainPassword,
                $currentUser->name
            ));

            $this->sendWhatsAppMessage($user, $plainPassword, $currentUser);
        }

        return $this->handleRedirect($request, $user, $plainPassword, $currentUser);
    }

    private function getAllowedRoles(string $role): array
    {
        $rolesMap = [
            'admin' => [
                'admin-kabupaten',
                'ketua-timpembina-posyandu',
                'kabid',
                'admin-kecamatan',
                'ketua-posyandu',
                'kades',
                'operator-desa',
                'kader',
                'masyarakat',
                'admin'
            ],
            'ketua-timpembina-posyandu' => [
                'kabid',
                'admin-kecamatan',
                'ketua-posyandu',
                'kades',
                'kader',
                'masyarakat',
            ],
            'admin-kabupaten' => [
                'kabid',
                'ketua-timpembina-posyandu',
                'admin-kecamatan',
                'kades',
                'operator-desa',
                'admin-kabupaten',
            ],
            'kabid' => [
                'admin-kecamatan',
                'ketua-posyandu',
                'kader',
            ],
            'admin-kecamatan' => [
                'ketua-posyandu',
                'kader',
            ],
            'ketua-posyandu' => [
                'operator-desa',
                'kader',
            ],
            'operator-desa' => [
                'ketua-posyandu',
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
            'operator-desa' => 'kader',
            'ketua-posyandu' => 'kader',
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

        $roleSpecificRules = [
            'ketua-timpembina-posyandu' => [
                'jenis_wilayah' => ['required', 'in:kabupaten,kota'],
                'kabupaten' => ['required', 'string'],
            ],
            'kabid' => [
                'bidang_id' => ['required', 'uuid', 'exists:bidang_pengajuans,id'],
                'kabupaten' => ['required', 'string'],
            ],
            'kades' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
                'desa' => ['required', 'string'],
            ],
            'bu-kades' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
                'desa' => ['required', 'string'],
            ],
            'admin-kecamatan' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
            ],
            'ketua-posyandu' => [
                'posyandu_id' => ['required', 'uuid', 'exists:posyandus,id'],
            ],
            'operator-desa' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
                'desa' => ['required', 'string'],
            ],
            'kader' => [
                'bidang_id' => ['required', 'uuid', 'exists:bidang_pengajuans,id'],
                'posyandu_id' => $currentUser->role === 'ketua-posyandu' 
                    ? ['nullable', 'uuid', 'exists:posyandus,id']
                    : ['required', 'uuid', 'exists:posyandus,id'],
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
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $data['kabupaten'] = $kabupatenName;
                $data['jenis_wilayah'] = $request->jenis_wilayah ?? 'kabupaten';
                break;
            case 'ketua-timpembina-posyandu':
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $data['kabupaten'] = $kabupatenName;
                $data['jenis_wilayah'] = $request->jenis_wilayah;
                break;

            case 'kabid':
                $data['bidang_id'] = $request->bidang_id;

                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $data['kabupaten'] = $kabupatenName;
                $data['jenis_wilayah'] = $request->jenis_wilayah ?? 'kabupaten';
                break;
            case 'kades':
            case 'bu-kades':
                // Get kabupaten from hidden field (admin-kabupaten) or form input
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $kecamatanValue = $request->kecamatan;
                $kecamatanName = explode('_', $kecamatanValue)[1] ?? $kecamatanValue;

                $desaValue = $request->desa;
                $desaName = explode('_', $desaValue)[1] ?? $desaValue;

                $data['kabupaten'] = $kabupatenName;
                $data['kecamatan'] = $kecamatanName;
                $data['desa'] = $desaName;
                $data['jenis_wilayah'] = $currentUser->jenis_wilayah ?? 'kabupaten';
                break;

            case 'admin-kecamatan':
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $kecamatanValue = $request->kecamatan;
                $kecamatanName = explode('_', $kecamatanValue)[1] ?? $kecamatanValue;

                $data['kabupaten'] = $kabupatenName;
                $data['kecamatan'] = $kecamatanName;
                $data['jenis_wilayah'] = $request->jenis_wilayah ?? 'kabupaten';
                break;

            case 'ketua-posyandu':
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
                $kabupatenValue = $request->kabupaten;
                $kabupatenName = explode('_', $kabupatenValue)[1] ?? $kabupatenValue;

                $kecamatanValue = $request->kecamatan;
                $kecamatanName = explode('_', $kecamatanValue)[1] ?? $kecamatanValue;

                $desaValue = $request->desa;
                $desaName = explode('_', $desaValue)[1] ?? $desaValue;

                $data['kabupaten'] = $kabupatenName;
                $data['kecamatan'] = $kecamatanName;
                $data['desa'] = $desaName;
                break;

            case 'kader':
                $data['bidang_id'] = $request->bidang_id;

                if ($currentUser->role === 'ketua-posyandu') {
                    $data['posyandu_id'] = $currentUser->posyandu_id;
                    $data['kabupaten'] = $currentUser->kabupaten;
                    $data['kecamatan'] = $currentUser->kecamatan;
                    $data['desa'] = $currentUser->desa;
                    $data['jenis_wilayah'] = $currentUser->jenis_wilayah;
                } elseif ($currentUser->role === 'operator-desa') {
                    // Operator-desa selects posyandu from dropdown
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
                } else {
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
                if ($currentUser->role === 'kader') {
                    $data['posyandu_id'] = $currentUser->posyandu_id;
                    $data['kabupaten'] = $currentUser->kabupaten;
                    $data['kecamatan'] = $currentUser->kecamatan;
                    $data['desa'] = $currentUser->desa;
                }
                break;
        }

        return $data;
    }

    private function shouldSendNotification(string $role): bool
    {
        return in_array($role, [
            'ketua-timpembina-posyandu',
            'kabid',
            'kades',
            'admin-kecamatan',
            'ketua-posyandu',
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
        if ($request->input('source') === 'pilih-user') {
            return redirect()->route('dashboard.partials.pilih-user')
                ->with('success', 'User masyarakat berhasil dibuat! Silakan pilih dari daftar untuk membuat ajuan.');
        }

        if ($request->input('source') === 'posyandu_create') {
            return redirect()->route('admin.posyandu.create')
                ->with('success', 'User Ketua Posyandu berhasil dibuat! Silakan refresh halaman dan pilih dari dropdown.');
        }

        if ($this->shouldSendNotification($request->role)) {
            return redirect()->route('admin.users.index')
                ->with('success', 'User baru berhasil ditambahkan.')
                ->with('whatsapp_link', $this->generateWhatsAppLink($user, $plainPassword, $currentUser));
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    private function generateWhatsAppLink($user, $plainPassword, $createdBy)
    {
        $roleNames = [
            'kabid' => 'Kepala Bidang',
            'ketua-posyandu' => 'Ketua Posyandu',
            'admin-kecamatan' => 'Admin Kecamatan',
        ];

        $roleName = $roleNames[$user->role] ?? $user->role;

        $posyandu = $user->posyandu ? $user->posyandu->nama_posyandu : '-';
        $desa = $user->posyandu && $user->posyandu->desa ? $user->posyandu->desa : '-';
        $bidang = $user->bidang ? $user->bidang->nama_bidang : '-';

        $message = "*Selamat Datang di Sistem Posyandu!*\n\n";
        $message .= "Halo *{$user->name}*,\n\n";
        $message .= "Akun Anda telah berhasil dibuat oleh *{$createdBy->name}*.\n\n";
        $message .= "*Detail Akun Anda:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";

        if ($user->email) {
            $message .= "📧 Email: {$user->email}\n";
        }

        $message .= "Password: `{$plainPassword}`\n";
        $message .= "Role: {$roleName}\n";

        if ($user->role === 'kader' && $bidang !== '-') {
            $message .= "Bidang: {$bidang}\n";
        }

        if ($posyandu !== '-') {
            $message .= "Posyandu: {$posyandu}\n";
        }

        if ($desa !== '-') {
            $message .= "Desa: {$desa}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "*PENTING:*\n";
        $message .= "• Segera login dan ganti password Anda\n";
        $message .= "• Simpan informasi login ini dengan aman\n";
        $message .= "• Jangan bagikan password kepada siapapun\n\n";
        $message .= "Silakan login di: " . route('login') . "\n\n";
        $message .= "Terima kasih!";

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

    public function show(User $user)
    {
        $user->load(['posyandu', 'bidang']);
        return view('admin.users.show', [
            'user' => $user
        ]);
    }

    public function edit(User $user)
    {
        $posyandus = Posyandu::orderBy('nama_posyandu')->get();
        $bidangs = BidangPengajuan::orderBy('nama_bidang')->get();

        return view('admin.users.edit', compact('user', 'posyandus', 'bidangs'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'digits:16', Rule::unique(User::class)->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'alamat' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $currentUser = Auth::user();
        $newStatus = $request->has('is_active') ? 1 : 0;
        $oldStatus = $user->is_active;

        $data = [
            'name' => $validated['name'],
            'nik' => $validated['nik'] ?? null,
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'is_active' => $newStatus,
        ];

        if ($newStatus === 0) {
            $data['deactivated_at'] = now();
            $data['deactivated_by'] = $currentUser->id;
            $data['deactivation_reason'] = $request->input('reason', '-');
        } else {
            $data['deactivated_at'] = null;
            $data['deactivated_by'] = null;
            $data['deactivation_reason'] = null;
        }

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        // Catat history jika status berubah
        if ($newStatus !== $oldStatus) {
            $actionType = $newStatus ? 'activated' : 'deactivated';
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            UserHistory::create([
                'user_id' => $user->id,
                'action_by' => $currentUser->id,
                'action_type' => $actionType,
                'description' => "User {$statusText} oleh {$currentUser->name}.",
                'old_data' => json_encode(['is_active' => $oldStatus]),
                'new_data' => json_encode(['is_active' => $newStatus]),
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data user {$user->name} berhasil diperbarui.");
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

    public function destroy(User $user)
    {
        // Hanya admin yang bisa menghapus user
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized: Hanya admin yang dapat menghapus user.');
        }

        // Cegah admin menghapus dirinya sendiri
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun diri sendiri.');
        }

        // Hapus user
        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' berhasil dihapus.");
    }

    public function verify(User $user)
    {
        $currentUser = Auth::user();

        if (
            ($currentUser->role === 'kader' && $user->role === 'masyarakat') ||
            ($currentUser->role === 'ketua-posyandu' && $user->role === 'kader') ||
            ($currentUser->role === 'kabid' && $user->role === 'ketua-posyandu') ||
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

            $query = Posyandu::select('id', 'nama_posyandu', 'desa', 'kecamatan', 'kabupaten');

            switch ($currentUser->role) {
                case 'kader':
                    $query->where('id', $currentUser->posyandu_id);
                    break;

                case 'ketua-posyandu':
                    $query->where('id', $currentUser->posyandu_id);
                    break;

                case 'operator-desa':
                    $posyandus = $currentUser->posyandu;
                    $query->where('desa', $posyandus->desa)
                        ->where('kecamatan', $posyandus->kecamatan);
                    break;

                case 'admin-kecamatan':
                    $query->where('kecamatan_id', $currentUser->kecamatan_id);
                    break;

                case 'kabid':
                    $query->where('kabupaten_id', $currentUser->kabupaten_id);
                    break;

                case 'admin-kabupaten':
                    if ($currentUser->kabupaten) {
                        $query->where('kabupaten', 'LIKE', "%{$currentUser->kabupaten}%");
                    }
                    break;

                case 'admin':
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

            foreach ($posyandus as $posyandu) {
                if ($roleToCreate === 'kader') {
                    $bidangs = BidangPengajuan::all()->take(6);

                    foreach ($bidangs as $bidang) {
                        $dataRows[] = [
                            'desa' => $this->cleanDesaName($posyandu->desa),
                            'kecamatan' => $this->cleanKecamatanName($posyandu->kecamatan),
                            'kabupaten' => strtoupper($posyandu->kabupaten)
                        ];
                    }
                } else {
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

    private function cleanDesaName($name)
    {
        if (!is_string($name)) {
            return '';
        }

        $cleaned = str_ireplace(['DESA ', 'KELURAHAN '], '', $name);
        return strtoupper(trim($cleaned));
    }

    private function cleanKecamatanName($name)
    {
        if (!is_string($name)) {
            return '';
        }

        $cleaned = str_ireplace('KECAMATAN ', '', $name);
        return strtoupper(trim($cleaned));
    }

    private function getAllowedRoleTargets(string $role): array
    {
        $roleMap = [
            'kader' => ['masyarakat'],
            'ketua-posyandu' => ['kader'],
            'operator-desa' => ['ketua-posyandu', 'kader'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat'],
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


        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $allowedRoles = ['kader', 'ketua-posyandu'];
        if (!in_array($user->role, $allowedRoles) || (string)$user->posyandu_id !== (string)$currentUser->posyandu_id) {
            return redirect()->back()->with('error', 'Anda hanya bisa reset password kader atau ketua di posyandu Anda');
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

        return redirect()->back()->with('success', 'Password kader berhasil direset');
    }

    public function resetPasswordKabid(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'admin-kabupaten') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if (
            !in_array($user->role, ['kabid', 'ketua-posyandu']) ||
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

    public function deactivateUserKabid(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $currentUser = Auth::user();

        if ($currentUser->role !== 'admin-kabupaten') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $allowedRoles = ['ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'];

        if (!in_array($user->role, $allowedRoles)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menonaktifkan user ini');
        }

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
            'old_data' => ['is_active' => true],
            'new_data' => [
                'is_active' => false,
                'reason' => $request->reason,
            ],
        ]);

        return redirect()->back()->with('success', 'User berhasil dinonaktifkan.');
    }

    public function reactivateUserKabid(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'admin-kabupaten') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $allowedRoles = ['ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'];

        if (!in_array($user->role, $allowedRoles)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengaktifkan user ini');
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
            'old_data' => ['is_active' => false],
            'new_data' => ['is_active' => true],
        ]);

        return redirect()->back()->with('success', 'User berhasil diaktifkan kembali.');
    }

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

    public function deactivateKader(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('swal_error', [
                'title' => 'Tidak Diizinkan',
                'text' => 'Anda tidak memiliki akses untuk melakukan aksi ini.'
            ]);
        }

        $allowedRoles = ['kader', 'ketua-posyandu'];
        if (!in_array($user->role, $allowedRoles)) {
            return redirect()->back()->with('swal_error', [
                'title' => 'Role Tidak Valid',
                'text' => 'Anda hanya bisa menonaktifkan kader atau ketua posyandu.'
            ]);
        }
        if ($user->desa !== $currentUser->desa || $user->kecamatan !== $currentUser->kecamatan) {
            return redirect()->back()->with('swal_error', [
                'title' => 'Lokasi Tidak Valid',
                'text' => 'Anda hanya bisa menonaktifkan user di desa Anda.'
            ]);
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

        $roleLabel = $user->role === 'ketua-posyandu' ? 'Ketua Posyandu' : 'Kader';

        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'deactivated',
            'description' => "User dinonaktifkan oleh {$currentUser->name}. Alasan: {$request->reason}",
            'old_data' => json_encode(['is_active' => true]),
            'new_data' => json_encode(['is_active' => false, 'reason' => $request->reason]),
        ]);

        return redirect()->back()->with('swal_success', [
            'title' => 'Berhasil Dinonaktifkan',
            'text' => "{$roleLabel} {$user->name} berhasil dinonaktifkan."
        ]);
    }

    public function reactivateKader(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'operator-desa') {
            return redirect()->back()->with('swal_error', [
                'title' => 'Tidak Diizinkan',
                'text' => 'Anda tidak memiliki akses untuk melakukan aksi ini.'
            ]);
        }

        $allowedRoles = ['kader', 'ketua-posyandu'];
        if (!in_array($user->role, $allowedRoles)) {
            return redirect()->back()->with('swal_error', [
                'title' => 'Role Tidak Valid',
                'text' => 'Anda hanya bisa mengaktifkan kader atau ketua posyandu.'
            ]);
        }

        if ($user->desa !== $currentUser->desa || $user->kecamatan !== $currentUser->kecamatan) {
            return redirect()->back()->with('swal_error', [
                'title' => 'Lokasi Tidak Valid',
                'text' => 'Anda hanya bisa mengaktifkan user di desa Anda.'
            ]);
        }

        // Check for existing active ketua-posyandu
        if ($user->role === 'ketua-posyandu') {
            if ($user->posyandu_id) {
                $existingActiveKetua = User::where('role', 'ketua-posyandu')
                    ->where('posyandu_id', $user->posyandu_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->first();


                if ($existingActiveKetua) {
                    $posyanduName = $user->posyandu?->nama_posyandu ?? 'posyandu ini';
                    return redirect()->back()->with('swal_error', [
                        'title' => 'Ketua Posyandu Sudah Ada',
                        'text' => "Tidak dapat mengaktifkan Ketua Posyandu karena sudah ada Ketua Posyandu aktif ({$existingActiveKetua->name}) di {$posyanduName}. Nonaktifkan terlebih dahulu Ketua Posyandu yang aktif."
                    ]);
                }
            } else {
                \Log::warning('Ketua posyandu has no posyandu_id', ['user_id' => $user->id]);
            }
        }

        // Check for existing active kader in the same bidang and posyandu
        if ($user->role === 'kader' && $user->bidang_id && $user->posyandu_id) {
            $existingActiveKader = User::where('role', 'kader')
                ->where('posyandu_id', $user->posyandu_id)
                ->where('bidang_id', $user->bidang_id)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingActiveKader) {
                $bidangName = $user->bidang?->nama_bidang ?? 'bidang ini';
                $posyanduName = $user->posyandu?->nama_posyandu ?? 'posyandu ini';
                return redirect()->back()->with('swal_error', [
                    'title' => 'Kader Bidang Sudah Ada',
                    'text' => "Tidak dapat mengaktifkan Kader karena sudah ada Kader aktif ({$existingActiveKader->name}) di bidang {$bidangName} pada {$posyanduName}. Nonaktifkan terlebih dahulu Kader yang aktif di bidang tersebut."
                ]);
            }
        }

        $user->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
            'deactivation_reason' => null,
        ]);

        $roleLabel = $user->role === 'ketua-posyandu' ? 'Ketua Posyandu' : 'Kader';

        UserHistory::create([
            'user_id' => $user->id,
            'action_by' => $currentUser->id,
            'action_type' => 'activated',
            'description' => "User diaktifkan kembali oleh {$currentUser->name}",
            'old_data' => json_encode(['is_active' => false]),
            'new_data' => json_encode(['is_active' => true]),
        ]);

        return redirect()->back()->with('swal_success', [
            'title' => 'Berhasil Diaktifkan',
            'text' => "{$roleLabel} {$user->name} berhasil diaktifkan kembali."
        ]);
    }

    public function takeoverIndex()
    {
        $ketuaKader = Auth::user();

        if ($ketuaKader->role !== 'ketua-posyandu') {
            abort(403);
        }

        $kaders = User::with(['bidang'])
            ->where('role', 'kader')
            ->where('posyandu_id', $ketuaKader->posyandu_id)
            ->get();

        return view('admin.users.takeover', compact('kaders'));
    }

    public function takeoverResetPassword(Request $request, User $kader)
    {
        $ketuaKader = Auth::user();

        if (
            $ketuaKader->role !== 'ketua-posyandu' ||
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
