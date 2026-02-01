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
            'ketua-posyandu' => ['kader'],
            'operator-desa' => ['ketua-posyandu', 'kader'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'bu-kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'bu-kades', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat'],
        ];

        return $roleMap[$role] ?? [];
    }

    public function generateWhatsAppLink($user)
    {
        $currentUser = Auth::user();
        
        $message = "*Selamat Datang di Sistem Posyandu!*\n\n";
        $message .= "Halo *{$user->name}*,\n\n";
        $message .= "Akun Anda telah berhasil dibuat oleh *{$currentUser->name}*.\n\n";
        $message .= "*Detail Akun Anda:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";

        if ($user->email) {
            $message .= "Email: {$user->email}\n";
        }

        $message .= "Password: Sesuai yang diberikan\n";

        if ($user->posyandu) {
            $message .= "Posyandu: {$user->posyandu->nama_posyandu}\n";
        }

        if ($user->posyandu && $user->posyandu->desa) {
            $message .= "Desa: {$user->posyandu->desa}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "*PENTING:*\n";
        $message .= "• Segera login dengan akun ini\n";
        $message .= "• Simpan informasi ini dengan aman\n";
        $message .= "• Jangan bagikan password kepada siapapun\n\n";
        $message .= "Silakan login di: " . route('login') . "\n\n";
        $message .= "Terima kasih!";

        $phone = preg_replace('/[^0-9]/', '', $user->no_telepon);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
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

        if (in_array($currentUser->role, ['kader', 'ketua-posyandu'])) {
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
