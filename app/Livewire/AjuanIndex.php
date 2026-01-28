<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;

class AjuanIndex extends Component
{
    use WithPagination;

    public $status = '';
    public $search = '';
    public $statusFilter = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->status = '';
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $alwaysVerifiedRoles = [
            'admin',
            'kabid',
            'admin-kabupaten',
            'admin-kecamatan',
            'ketua-kader',
            'kades',
            'ketua-posyandu',
            'operator-desa',
            'masyarakat'
        ];
        $isVerified = in_array($user->role, $alwaysVerifiedRoles) || !is_null($user->verified_at);

        // Build query based on role
        $query = Pengajuan::with(['user.posyandu', 'bidang', 'histories' => function ($q) {
            // ✅ Eager load semua history untuk cek revisi
            $q->whereIn('status', ['Revisi Diminta', 'Direvisi & Diajukan Kembali'])
                ->orderBy('created_at', 'desc');
        }]);

        // Filter by role
        switch ($user->role) {
            case 'masyarakat':
                $query->where('user_id', $user->id);
                break;

            case 'kader':
                if ($user->bidang_id && $user->posyandu_id) {
                    $query->where('bidang_id', $user->bidang_id)
                        ->whereHas('user', function ($q) use ($user) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        })
                        // ✅ Kader hanya lihat pengajuan yang statusnya "Diproses"
                        ->where('status_pengajuan', 'Diproses');
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'operator-desa':
                if ($user->posyandu_id) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'ketua-posyandu':
                if ($user->posyandu_id) {
                    $query->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id))
                        ->where(function ($q) {
                            // Yang sudah kunjungan tapi belum diapprove ketua
                            $q->where(function ($subQ) {
                                $subQ->where('kunjungan_lapangan', true)
                                    ->where('approved_by_ketua', false)
                                    ->where('status_pengajuan', 'Diproses');
                            })
                                // Atau yang sudah disetujui ketua (status "Sesuai")
                                ->orWhere('status_pengajuan', 'Sesuai');
                        });
                } elseif ($user->desa) {
                    $query->whereHas('user', fn($q) => $q->where('desa', $user->desa))
                        ->where(function ($q) {
                            $q->where(function ($subQ) {
                                $subQ->where('kunjungan_lapangan', true)
                                    ->where('approved_by_ketua', false)
                                    ->where('status_pengajuan', 'Diproses');
                            })
                                ->orWhere('status_pengajuan', 'Sesuai');
                        });
                }
                break;
            case 'ketua-kader':
            case 'kades':
                if ($user->posyandu_id) {
                    $query->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id))
                        ->where('status_pengajuan', 'Diajukan ke Desa');
                } elseif ($user->desa) {
                    $query->whereHas('user', fn($q) => $q->where('desa', $user->desa))
                        ->where('status_pengajuan', 'Diajukan ke Desa');
                }
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $query->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $user->kecamatan . '%'));
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                }
                if ($user->bidang_id) {
                    $query->where('bidang_id', $user->bidang_id);
                }
                break;

            case 'admin-kabupaten':
                if ($user->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'LIKE', '%' . $user->kabupaten . '%'));
                }
                break;

            case 'admin':
                // Admin sees all
                break;
        }

        if (!empty($this->status)) {
            $query->where('status_pengajuan', $this->status);
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('deskripsi_pengajuan', 'like', '%' . $this->search . '%')
                    ->orWhere('status_pengajuan', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', fn($userQuery) =>
                    $userQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('bidang', fn($bidangQuery) =>
                    $bidangQuery->where('nama_bidang', 'like', '%' . $this->search . '%'));
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status_pengajuan', $this->statusFilter);
        }

        $semuaAjuan = $query->latest()->paginate(10);

        // ✅ Tambahkan computed attributes untuk setiap pengajuan
        $semuaAjuan->getCollection()->transform(function ($ajuan) {
            // Ambil history "Revisi Diminta" terakhir
            $latestRevisionRequest = $ajuan->histories
                ->where('status', 'Revisi Diminta')
                ->first(); // Sudah sorted by created_at desc

            // Ambil history "Direvisi & Diajukan Kembali" terakhir
            // ✅ TIDAK perlu cek action_by_role karena statusnya sudah spesifik
            $latestRevisionSubmit = $ajuan->histories
                ->where('status', 'Direvisi & Diajukan Kembali')
                ->first();

            // Logic: Sudah direvisi jika ada submit revision SETELAH request revision terakhir
            if ($latestRevisionRequest && $latestRevisionSubmit) {
                // ✅ Parse ke Carbon jika masih string
                $requestDate = $latestRevisionRequest->created_at instanceof \Carbon\Carbon
                    ? $latestRevisionRequest->created_at
                    : \Carbon\Carbon::parse($latestRevisionRequest->created_at);

                $submitDate = $latestRevisionSubmit->created_at instanceof \Carbon\Carbon
                    ? $latestRevisionSubmit->created_at
                    : \Carbon\Carbon::parse($latestRevisionSubmit->created_at);

                $ajuan->has_been_revised_by_user = $submitDate->greaterThan($requestDate);
            } else {
                $ajuan->has_been_revised_by_user = false;
            }

            // Logic: Menunggu revisi jika ada request tapi belum ada submit setelahnya
            $ajuan->is_waiting_revision = $latestRevisionRequest && !$ajuan->has_been_revised_by_user;

            return $ajuan;
        });

        return view('livewire.ajuan-index', [
            'semuaAjuan' => $semuaAjuan,
            'isVerified' => $isVerified,
            'status' => $this->status
        ]);
    }
}
