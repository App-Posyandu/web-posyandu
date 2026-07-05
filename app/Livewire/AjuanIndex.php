<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;

class AjuanIndex extends Component
{
    use WithPagination;

    private const ALLOWED_STATUSES = ['Diproses', 'Disetujui', 'Ditolak'];

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

    public function mount(): void
    {
        $this->search = $this->sanitizeSearch($this->search);
        $this->status = $this->sanitizeStatus($this->status) ?? '';
        $this->statusFilter = $this->sanitizeStatus($this->statusFilter) ?? '';
    }

    public function toggleArchive()
    {
        $this->showArchived = !$this->showArchived;
        $this->resetPage();
    }

    public function updatedSearch($value): void
    {
        $this->search = $this->sanitizeSearch($value);
        $this->resetPage();
    }

    public function updatedStatus($value): void
    {
        $this->status = $this->sanitizeStatus($value) ?? '';
        $this->resetPage();
    }

    public function updatedStatusFilter($value): void
    {
        $this->statusFilter = $this->sanitizeStatus($value) ?? '';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->status = '';
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    private function sanitizeSearch(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $value));
        if ($normalized === '') {
            return '';
        }

        return preg_match('/^[\pL\pN\s@\._\-,()]+$/u', $normalized) ? $normalized : '';
    }

    private function sanitizeStatus(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        return in_array($normalized, self::ALLOWED_STATUSES, true) ? $normalized : null;
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

        $completedStatuses = ['Disetujui', 'Ditolak'];

        switch ($user->role) {
            case 'masyarakat':
                $query->where('user_id', $user->id);
                break;

            case 'kader':
                if ($user->bidang_id && $user->posyandu_id) {
                    $query->where('bidang_id', $user->bidang_id)
                        ->whereHas('user', function ($q) use ($user) {
                            $q->where('posyandu_id', $user->posyandu_id);
                        });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'operator-desa':
                if ($user->posyandu_id) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                } elseif ($user->desa) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('desa', $user->desa);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'ketua-posyandu':
                if ($user->posyandu_id) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('posyandu_id', $user->posyandu_id);
                    });
                } elseif ($user->desa) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('desa', $user->desa);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'kades':
            case 'bu-kades':
                if ($user->posyandu_id || $user->desa) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where(function ($scope) use ($user) {
                            if ($user->posyandu_id) {
                                $scope->orWhere('posyandu_id', $user->posyandu_id);
                            }

                            if ($user->desa) {
                                $scope->orWhere('desa', 'ilike', '%' . $user->desa . '%')
                                    ->orWhere('alamat', 'ilike', '%' . $user->desa . '%')
                                    ->orWhereHas('posyandu', function ($posyanduQuery) use ($user) {
                                        $posyanduQuery->where('desa', 'ilike', '%' . $user->desa . '%')
                                            ->orWhere('nama_posyandu', 'ilike', '%' . $user->desa . '%');
                                    });
                            }
                        });
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'ketua-timpembina-posyandu':
                if ($user->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'ilike', '%' . $user->kabupaten . '%'));
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $query->whereHas('user', fn($q) => $q->where('kecamatan', 'ilike', '%' . $user->kecamatan . '%'));
                    if ($user->kabupaten) {
                        $query->whereHas('user', fn($q) => $q->where('kabupaten', 'ilike', '%' . $user->kabupaten . '%'));
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'ilike', '%' . $user->kabupaten . '%'));
                } else {
                    $query->whereRaw('1 = 0');
                }
                if ($user->bidang_id) {
                    $query->where('bidang_id', $user->bidang_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'admin-kabupaten':
                if ($user->kabupaten) {
                    $query->whereHas('user', fn($q) => $q->where('kabupaten', 'ilike', '%' . $user->kabupaten . '%'));
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'admin':
                break;

            default:
                $query->whereRaw('1 = 0');
                break;
        }

        if ($user->role === 'kader') {
            if ($this->showArchived) {
                // ARSIP KADER: Kunjungan sudah selesai ATAU status sudah final (Disetujui/Ditolak)
                $query->where(function($q) use ($completedStatuses) {
                    $q->where('kunjungan_lapangan', true)
                      ->orWhereIn('status_pengajuan', $completedStatuses);
                });
            } else {
                // AKTIF KADER: Status masih diproses DAN kunjungan lapangan belum selesai
                $query->where('status_pengajuan', 'Diproses')
                      ->where('kunjungan_lapangan', false);
            }
        } elseif ($user->role === 'ketua-posyandu') {
            if ($this->showArchived) {
                // ARSIP KETUA: Sudah dikirim ke desa ATAU status akhir Disetujui/Ditolak
                $query->where(function($q) use ($completedStatuses) {
                    $q->where('submitted_to_desa', true)
                      ->orWhereIn('status_pengajuan', $completedStatuses);
                });
            } else {
                // AKTIF KETUA: Status diproses, Kunjungan selesai, TAPI belum dikirim ke desa
                $query->where('status_pengajuan', 'Diproses')
                      ->where('kunjungan_lapangan', true)
                      ->where('submitted_to_desa', false);
            }
        } else {
            if ($this->showArchived) {
                $query->whereIn('status_pengajuan', $completedStatuses);
            } else {
                $query->where('status_pengajuan', 'Diproses');
            }
        }

        $safeStatus = $this->sanitizeStatus($this->status);
        if (!empty($safeStatus)) {
            $query->where('status_pengajuan', $safeStatus);
        }

        $safeSearch = $this->sanitizeSearch($this->search);
        if (!empty($safeSearch)) {
            $query->where(function ($q) use ($safeSearch) {
                $q->where('deskripsi_pengajuan', 'ilike', '%' . $safeSearch . '%')
                    ->orWhere('status_pengajuan', 'ilike', '%' . $safeSearch . '%')
                    ->orWhereHas('user', function ($userQuery) use ($safeSearch) {
                        $userQuery->where('name', 'ilike', '%' . $safeSearch . '%')
                            ->orWhere('alamat', 'ilike', '%' . $safeSearch . '%')
                            ->orWhere('desa', 'ilike', '%' . $safeSearch . '%')
                            ->orWhereHas('posyandu', function ($posyanduQuery) use ($safeSearch) {
                                $posyanduQuery->where('nama_posyandu', 'ilike', '%' . $safeSearch . '%')
                                    ->orWhere('desa', 'ilike', '%' . $safeSearch . '%')
                                    ->orWhere('kecamatan', 'ilike', '%' . $safeSearch . '%')
                                    ->orWhere('kabupaten', 'ilike', '%' . $safeSearch . '%');
                            });
                    })
                    ->orWhereHas('bidang', fn($bidangQuery) =>
                    $bidangQuery->where('nama_bidang', 'ilike', '%' . $safeSearch . '%'));
            });
        }

        $safeStatusFilter = $this->sanitizeStatus($this->statusFilter);
        if (!empty($safeStatusFilter)) {
            $query->where('status_pengajuan', $safeStatusFilter);
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
