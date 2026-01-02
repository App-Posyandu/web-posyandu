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

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->status = '';
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $alwaysVerifiedRoles = ['admin','kader', 'kabid', 'admin-kecamatan', 'ketua-kader', 'masyarakat'];
        $isVerified = in_array($user->role, $alwaysVerifiedRoles) || !is_null($user->verified_at);

        $query = Pengajuan::with(['user.posyandu', 'bidang', 'histories']);

        // ========================================
        // 🎯 FILTER BERDASARKAN ROLE (HIERARKI)
        // ========================================
        switch ($user->role) {
            case 'masyarakat':
                // Masyarakat: Hanya lihat pengajuan miliknya sendiri
                $query->where('user_id', $user->id);
                break;

            case 'kader':
                // Kader: Lihat pengajuan di bidangnya di posyandunya
                if ($user->bidang_id && $user->posyandu_id) {
                    $query->where('bidang_id', $user->bidang_id)
                        ->whereHas('user', function ($q) use ($user) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        });
                } else {
                    // Jika kader belum punya bidang/posyandu, return empty
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'operator-desa':
                // Operator Desa: Sama seperti ketua kader (lihat semua di posyandunya)
                if ($user->posyandu_id) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'ketua-kader':
                // Ketua Kader: Lihat semua pengajuan di posyandunya (semua bidang)
                if ($user->posyandu_id) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'admin-kecamatan':
                // Admin Kecamatan: Lihat semua pengajuan di kecamatannya
                if ($user->kecamatan) {
                    $query->whereHas('user', function ($q) use ($user) {
                        // Gunakan LIKE untuk mencocokkan kecamatan
                        $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%');
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'kabid':
                // Kabid: Lihat pengajuan di kabupatennya, tapi hanya 1 bidang
                if ($user->kabupaten && $user->bidang_id) {
                    $query->where('bidang_id', $user->bidang_id)
                        ->whereHas('user', function ($q) use ($user) {
                            $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                        });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'ketua-posyandu':
                // Ketua Posyandu: Lihat semua pengajuan di kabupaten/kota-nya (semua bidang)
                if ($user->kabupaten) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%');
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'admin':
                // Admin: Lihat semua tanpa filter
                break;

            default:
                // Role tidak dikenali, return empty
                $query->whereRaw('1 = 0');
                break;
        }

        // ========================================
        // 🔍 FILTER STATUS
        // ========================================
        if (!empty($this->status)) {
            $query->where('status_pengajuan', $this->status);
        }

        // ========================================
        // 🔍 FILTER SEARCH
        // ========================================
        if (!empty($this->search)) {
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

        // ========================================
        // 📊 AMBIL DATA & PAGINATION
        // ========================================
        $semuaAjuan = $query->latest()->paginate(10)->withQueryString();

        // ========================================
        // 📈 STATISTIK UNTUK HEADER (OPTIONAL)
        // ========================================
        $totalDiproses = (clone $query)->where('status_pengajuan', 'Diproses')->count();
        $totalDisetujui = (clone $query)->where('status_pengajuan', 'Disetujui')->count();
        $totalDitolak = (clone $query)->where('status_pengajuan', 'Ditolak')->count();

        return view('livewire.ajuan-index', compact(
            'semuaAjuan',
            'isVerified',
            'totalDiproses',
            'totalDisetujui',
            'totalDitolak'
        ));
    }
}