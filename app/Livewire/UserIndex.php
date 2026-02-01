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

        $allowedRoles = $this->getAllowedRoleTargets($currentUser->role);

        if (!empty($allowedRoles)) {
            $query->whereIn('role', $allowedRoles);
        } elseif ($currentUser->role !== 'admin') {
            $query->whereRaw('1 = 0');
        }

        if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
            $query->where('posyandu_id', $currentUser->posyandu_id);
        } elseif ($currentUser->role === 'operator-desa') {
            if ($currentUser->kecamatan) {
                $kecamatanName = explode('_', $currentUser->kecamatan)[1] ?? $currentUser->kecamatan;
                $query->where('kecamatan', 'LIKE', "%{$kecamatanName}%");
            }
        }

        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%');
            });
        }

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
