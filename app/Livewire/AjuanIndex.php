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
    public $showArchived = false;

    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'showArchived' => ['except' => false]
    ];

    public function toggleArchive()
    {
        $this->showArchived = !$this->showArchived;
        $this->resetPage();
    }

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
            'ketua-posyandu',
            'kades',
            'bu-kades',
            'ketua-timpembina-posyandu',
            'operator-desa',
            'masyarakat'
        ];
        $isVerified = in_array($user->role, $alwaysVerifiedRoles) || !is_null($user->verified_at);

        $query = Pengajuan::with(['user.posyandu', 'bidang', 'histories' => function ($q) {
            $q->whereIn('status', ['Revisi Diminta', 'Direvisi & Diajukan Kembali'])
                ->orderBy('created_at', 'desc');
        }]);

        if ($this->showArchived) {
            switch ($user->role) {
                case 'ketua-posyandu':
                    $query->whereIn('status_pengajuan', ['Sesuai', 'Diajukan ke Desa', 'Disetujui', 'Ditolak'])
                        ->where('approved_by_ketua', true);
                    break;
                case 'kades':
                    $query->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                    break;
                case 'kader':
                    $query->where('sudah_verifikasi', true)->where('kunjungan_lapangan', true);
                    break;
                default:
                    $query->whereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
            }
        } else {
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

                case 'ketua-timpembina-posyandu':
                    // Ketua Tim Pembina Posyandu tidak lagi di alur tahap 3
                    $query->whereRaw('1 = 0');
                    break;
                case 'ketua-posyandu':
                    if ($user->posyandu_id) {
                        $query->whereHas('user', fn($q) => $q->where('posyandu_id', $user->posyandu_id))
                            ->where(function ($q) {
                                $q->where(function ($subQ) {
                                    $subQ->where('kunjungan_lapangan', true)
                                        ->where('approved_by_ketua', false)
                                        ->where('status_pengajuan', 'Diproses');
                                })
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
<<<<<<< HEAD
=======
                case 'ketua-kader':
                    if ($user->posyandu_id) {
                        $query->whereHas('user', function ($q) use ($user) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        })
                            ->where(function ($q) {
                                $q->where('status_pengajuan', 'Diproses')
                                    ->orWhere('status_pengajuan', 'Diajukan ke Desa');
                            });
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                    break;
>>>>>>> cc1c49af8c215191eb7e881f308dd4656871174f
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
                    break;
            }
        }

        if (!empty($this->status)) {
            $query->where('status_pengajuan', $this->status);
        }

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

        if ($this->statusFilter) {
            $query->where('status_pengajuan', $this->statusFilter);
        }

        $semuaAjuan = $query->latest()->paginate(10);

        $semuaAjuan->getCollection()->transform(function ($ajuan) {
            $latestRevisionRequest = $ajuan->histories
                ->where('status', 'Revisi Diminta')
                ->first();

            $latestRevisionSubmit = $ajuan->histories
                ->where('status', 'Direvisi & Diajukan Kembali')
                ->first();

            if ($latestRevisionRequest && $latestRevisionSubmit) {
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
