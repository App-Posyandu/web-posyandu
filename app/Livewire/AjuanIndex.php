<?php

namespace App\Livewire;

use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AjuanIndex extends Component
{
    use WithPagination;

    public $status = '';
    public $search = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    // Reset pagination ketika filter berubah
    public function updatingStatus()
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
        $this->status = '';
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $alwaysVerifiedRoles = ['admin', 'kabid', 'ketua-kader', 'masyarakat'];
        $isVerified = in_array($user->role, $alwaysVerifiedRoles) || !is_null($user->verified_at);

        $query = Pengajuan::with(['user', 'bidang']);

        // Filter berdasarkan role
        if ($user->role === 'masyarakat') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'kader') {
            $query->where('bidang_id', $user->bidang_id)
                ->whereHas('user', function ($q) use ($user) {
                    $q->where('posyandu_id', $user->posyandu_id);
                });
        } elseif ($user->role === 'ketua-kader') {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('posyandu_id', $user->posyandu_id);
            });
        }

        // Filter berdasarkan status
        if ($this->status) {
            $query->where('status_pengajuan', $this->status);
        }

        // Filter berdasarkan search
        if ($this->search) {
            $searchTerm = '%' . strtolower($this->search) . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                })
                ->orWhereRaw('LOWER(deskripsi_pengajuan) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(status_pengajuan) LIKE ?', [$searchTerm])
                ->orWhereHas('bidang', function ($bidangQuery) use ($searchTerm) {
                    $bidangQuery->whereRaw('LOWER(nama_bidang) LIKE ?', [$searchTerm]);
                });
            });
        }

        $semuaAjuan = $query->latest()->paginate(5);

        return view('livewire.ajuan-index', [
            'semuaAjuan' => $semuaAjuan,
            'isVerified' => $isVerified
        ]);
    }
}
