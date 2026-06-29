<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public $role = '';
    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'role' => ['except' => ''],
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $currentUser = Auth::user();
        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        $this->search = $this->sanitizeSearch($this->search);
        $this->role = $this->sanitizeRole($this->role, $allowedRoles) ?? '';
    }

    public function updatedRole($value): void
    {
        $currentUser = Auth::user();
        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        $this->role = $this->sanitizeRole($value, $allowedRoles) ?? '';
        $this->resetPage();
    }

    public function updatedSearch($value): void
    {
        $this->search = $this->sanitizeSearch($value);
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->role = '';
        $this->search = '';
        $this->resetPage();
    }

    private function sanitizeSearch(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $value));
        if ($normalized === '') {
            return '';
        }

        return preg_match('/^[\pL\pN\s@\._\-,()]+$/u', $normalized) ? $normalized : '';
    }

    private function sanitizeRole(mixed $value, array $allowedRoles): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        return in_array($normalized, $allowedRoles, true) ? $normalized : null;
    }

    private function getAllowedRoleTargets(string $role): array
    {
        $roleMap = [
            'kader' => ['masyarakat'],
            'ketua-posyandu' => ['kader'],
            'operator-desa' => ['kades', 'bu-kades', 'ketua-posyandu', 'kader'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'bu-kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'bu-kades', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat'],
        ];

        return $roleMap[$role] ?? [];
    }

    public function generateWhatsAppLink($user)
    {
        $currentUser = Auth::user();

        $message = "*Selamat Datang di Sistem Posyandu!*\n\n";
        $message .= "Halo *{$user->name}*,\n\n";
        $message .= "Akun Anda telah berhasil dibuat oleh *{$currentUser->name}*.\n\n";
        $message .= "*Detail Akun Anda:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";

        if ($user->email) {
            $message .= "Email: {$user->email}\n";
        }

        $message .= "Password: Sesuai yang diberikan\n";

        if ($user->posyandu) {
            $message .= "Posyandu: {$user->posyandu->nama_posyandu}\n";
        }

        if ($user->posyandu && $user->posyandu->desa) {
            $message .= "Desa: {$user->posyandu->desa}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "*PENTING:*\n";
        $message .= "• Segera login dengan akun ini\n";
        $message .= "• Simpan informasi ini dengan aman\n";
        $message .= "• Jangan bagikan password kepada siapapun\n\n";
        $message .= "Silakan login di: " . route('login') . "\n\n";
        $message .= "Terima kasih!";

        $phone = preg_replace('/[^0-9]/', '', $user->no_telepon);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function render()
    {
        $currentUser = Auth::user();

        $query = User::with(['posyandu', 'bidang'])->latest();

        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);
        $safeRole = $this->sanitizeRole($this->role, $allowedRoles);
        $safeSearch = $this->sanitizeSearch($this->search);

        if (!empty($allowedRoles)) {
            $query->whereIn('role', $allowedRoles);
        } elseif ($currentUser->role !== 'admin') {
            $query->whereRaw('1 = 0');
        }

        if (in_array($currentUser->role, ['kader', 'ketua-posyandu'])) {
            $query->where('posyandu_id', $currentUser->posyandu_id);
        } elseif ($currentUser->role === 'operator-desa') {
            if ($currentUser->desa) {
                $posyanduIds = \App\Models\Posyandu::where('desa', $currentUser->desa)
                    ->pluck('id')
                    ->toArray();

                $query->where(function ($scope) use ($currentUser, $posyanduIds) {
                    $scope->where(function ($posScope) use ($posyanduIds) {
                        if (!empty($posyanduIds)) {
                            $posScope->whereIn('posyandu_id', $posyanduIds);
                        } else {
                            $posScope->whereRaw('1 = 0');
                        }
                    })->orWhere(function ($desaScope) use ($currentUser) {
                        $desaScope->where('desa', $currentUser->desa)
                            ->whereIn('role', ['kades', 'bu-kades']);
                    });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Search filter
        if (!empty($safeSearch)) {
            $searchTerm = $safeSearch;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('email', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('no_telepon', 'ilike', '%' . $searchTerm . '%');
            });
        }

        // Role filter
        if (!empty($safeRole)) {
            $query->where('role', $safeRole);
        }

        $users = $query->paginate($this->perPage);

        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        return view('livewire.user-index', [
            'users' => $users,
            'currentUser' => $currentUser,
            'allowedRoles' => $allowedRoles
        ]);
    }
}
