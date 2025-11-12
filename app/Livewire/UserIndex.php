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

        // Filter berdasarkan role user yang login
        switch ($currentUser->role) {
            case 'kader':
                $query->where('role', 'masyarakat')
                    ->where('posyandu_id', $currentUser->posyandu_id);
                break;

            case 'ketua-kader':
                $query->whereIn('role', ['kader', 'masyarakat'])
                    ->where('posyandu_id', $currentUser->posyandu_id);
                break;

            case 'kabid':
                $query->whereIn('role', ['ketua-kader', 'kader', 'masyarakat']);
                break;
        }

        // Filter tambahan untuk kader dan ketua-kader
        if (in_array($currentUser->role, ['kader', 'ketua-kader'])) {
            $query->where('posyandu_id', $currentUser->posyandu_id);
        }

        // Filter berdasarkan search
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nik', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter berdasarkan role
        if ($this->role) {
            $query->where('role', $this->role);
        }

        $users = $query->paginate(10);

        return view('livewire.user-index', [
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }
}
