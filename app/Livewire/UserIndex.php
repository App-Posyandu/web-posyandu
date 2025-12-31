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

    public function render()
    {
        $currentUser = Auth::user();

        $query = User::with(['posyandu', 'bidang'])->latest();

        // 1. Filter Hirarki Role berdasarkan role user yang login
        switch ($currentUser->role) {
            case 'kader':
                $query->where('role', 'masyarakat');
                break;

            case 'ketua-kader':
                $query->whereIn('role', ['kader', 'masyarakat']);
                break;

            case 'admin-kecamatan':
                $query->whereIn('role', ['ketua-kader', 'kader', 'masyarakat']);
                break;

            case 'operator-desa':
                $query->where('role', 'kader')
                    ->where('kecamatan', $currentUser->kecamatan);
                break;

            case 'kabid':
                // PERBAIKAN: Tambahkan admin-kecamatan!
                $query->whereIn('role', ['admin-kecamatan', 'ketua-kader', 'kader', 'masyarakat']);
                break;

            case 'admin':
                // Admin bisa lihat semua role
                // Tidak perlu filter role
                break;
        }

        // 2. Filter Wilayah (Multi-Tenancy)
        if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
            // Filter berdasarkan posyandu
            $query->where('posyandu_id', $currentUser->posyandu_id);
        } elseif ($currentUser->role === 'admin-kecamatan') {
            // Filter berdasarkan kecamatan
            if ($currentUser->kecamatan) {
                $kecamatanName = explode('_', $currentUser->kecamatan)[1] ?? $currentUser->kecamatan;
                $query->where('kecamatan', 'LIKE', "%{$kecamatanName}%");
            }
        } elseif ($currentUser->role === 'kabid') {
            // Filter berdasarkan bidang (jika ada)
            // if ($currentUser->bidang_id) {
            //     $query->where(function ($q) use ($currentUser) {
            //         $q->where('bidang_id', $currentUser->bidang_id)
            //             ->orWhereNull('bidang_id'); // User yang belum punya bidang
            //     });
            // }
            // Jika tidak ada bidang_id, kabid bisa lihat semua
        }
        // Admin tidak perlu filter wilayah

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

        return view('livewire.user-index', [
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }
}