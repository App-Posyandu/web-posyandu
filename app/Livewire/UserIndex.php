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

    protected $queryString = [
        'role' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->role = '';
        $this->search = '';
        $this->resetPage();
    }

    private function getAllowedRoleTargets(string $role): array
    {
        $roleMap = [
            'kader' => ['masyarakat'],
            'ketua-kader' => ['kader'],
            'operator-desa' => ['ketua-kader', 'kader', 'masyarakat'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'ketua-kader', 'operator-desa', 'kader', 'masyarakat'],
        ];

        return $roleMap[$role] ?? [];
    }

    public function getWhatsAppLink($userId)
    {
        $user = User::with(['posyandu', 'bidang'])->find($userId);
        if (!$user) return '#';

        $createdBy = Auth::user();

        // Logika pesan yang sama dengan UserController
        $roleNames = [
            'kabid' => 'Kepala Bidang',
            'ketua-kader' => 'Ketua Kader',
            'admin-kecamatan' => 'Admin Kecamatan',
        ];

        $roleName = $roleNames[$user->role] ?? $user->role;
        $posyandu = $user->posyandu ? $user->posyandu->nama_posyandu : '-';
        $bidang = $user->bidang ? $user->bidang->nama_bidang : '-';

        $message = "*Sistem Posyandu - Detail Login*\n\n";
        $message .= "Halo *{$user->name}*,\n\n";
        $message .= "Berikut adalah informasi akun Anda:\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        if ($user->email) $message .= "📧 Email: {$user->email}\n";
        $message .= "Role: {$roleName}\n";
        if ($user->role === 'kader' && $bidang !== '-') $message .= "Bidang: {$bidang}\n";
        if ($posyandu !== '-') $message .= "Posyandu: {$posyandu}\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Silakan login di: " . route('login') . "\n";
        $message .= "Jika lupa password, silakan hubungi admin.";

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

        if (!empty($allowedRoles)) {
            $query->whereIn('role', $allowedRoles);
        } elseif ($currentUser->role !== 'admin') {
            $query->whereRaw('1 = 0');
        }

        if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
            $query->where('posyandu_id', $currentUser->posyandu_id);
        } elseif ($currentUser->role === 'operator-desa') {
            if ($currentUser->desa) {
                // Get all posyandu IDs di desa ini
                $posyanduIds = \App\Models\Posyandu::where('desa', $currentUser->desa)
                    ->pluck('id')
                    ->toArray();

                // Filter users yang ada di posyandu-posyandu tersebut
                $query->whereIn('posyandu_id', $posyanduIds);
            }
        }

        // Search filter
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%')
                    ->orWhere('no_telepon', 'like', '%' . $searchTerm . '%');
            });
        }

        // Role filter
        if ($this->role && $this->role !== '') {
            $query->where('role', $this->role);
        }

        $users = $query->paginate(10);

        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        return view('livewire.user-index', [
            'users' => $users,
            'currentUser' => $currentUser,
            'allowedRoles' => $allowedRoles
        ]);
    }
}
