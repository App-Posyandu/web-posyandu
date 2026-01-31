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

    // Reset pagination ketika filter berubah
    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Method untuk reset filter
    public function resetFilters()
    {
        $this->role = '';
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Get allowed role targets based on current user role (same as UserController)
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

    public function render()
    {
        $currentUser = Auth::user();

        $query = User::with(['posyandu', 'bidang'])->latest();

        // 1. Filter Role berdasarkan role map (hanya tampilkan role yang bisa dibuat oleh current user)
        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);
        
        if (!empty($allowedRoles)) {
            $query->whereIn('role', $allowedRoles);
        } elseif ($currentUser->role !== 'admin') {
            // Jika tidak ada allowed roles dan bukan admin, jangan tampilkan user manapun
            $query->whereRaw('1 = 0'); // Query yang selalu false
        }

        // 2. Filter Wilayah (Multi-Tenancy)
        if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
            // Filter berdasarkan posyandu
            $query->where('posyandu_id', $currentUser->posyandu_id);
        } elseif ($currentUser->role === 'operator-desa') {
            // Filter berdasarkan kecamatan untuk operator desa
            if ($currentUser->kecamatan) {
                $kecamatanName = explode('_', $currentUser->kecamatan)[1] ?? $currentUser->kecamatan;
                $query->where('kecamatan', 'LIKE', "%{$kecamatanName}%");
            }
        }
        // Admin, admin-kabupaten, admin-kecamatan, kabid tidak perlu filter wilayah

        // 3. Filter berdasarkan search
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%');
            });
        }

        // 4. Filter berdasarkan role (dari dropdown filter)
        if ($this->role && $this->role !== '') {
            $query->where('role', $this->role);
        }

        $users = $query->paginate(10);

        // Get allowed roles for filter dropdown
        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        return view('livewire.user-index', [
            'users' => $users,
            'currentUser' => $currentUser,
            'allowedRoles' => $allowedRoles
        ]);
    }
}
