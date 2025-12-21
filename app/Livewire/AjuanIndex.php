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
        $alwaysVerifiedRoles = ['admin', 'kabid', 'admin-kecamatan', 'ketua-kader', 'masyarakat'];
        $isVerified = in_array($user->role, $alwaysVerifiedRoles) || !is_null($user->verified_at);

        $query = Pengajuan::with(['user', 'bidang']);

        // ✅ Filter berdasarkan role
        switch ($user->role) {
            case 'masyarakat':
                // Hanya pengajuan milik user sendiri
                $query->where('user_id', $user->id);
                break;

            case 'kader':
                // Pengajuan di posyandu-nya untuk bidang yang dikelola
                $query->where('bidang_id', $user->bidang_id)
                    ->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                break;

            case 'ketua-kader':
                // Semua pengajuan di posyandu-nya (semua bidang)
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('posyandu_id', $user->posyandu_id);
                });
                break;

            case 'admin-kecamatan':
                // ✅ Semua pengajuan dari user yang berada di kecamatan yang sama
                if ($user->kecamatan) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                }
                break;

            case 'kabid':
                // ✅ Semua pengajuan di kabupaten yang dikelola kabid
                if ($user->kabupaten) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                }
                break;

            case 'admin':
                // Admin bisa lihat semua (tidak ada filter)
                break;
        }

        // ✅ Filter berdasarkan status
        if ($this->status) {
            $query->where('status_pengajuan', $this->status);
        }

        // ✅ Filter berdasarkan search
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

        $semuaAjuan = $query->latest()->paginate(10);

        return view('livewire.ajuan-index', [
            'semuaAjuan' => $semuaAjuan,
            'isVerified' => $isVerified
        ]);
    }
}
