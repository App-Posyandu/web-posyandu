<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'action_by',
        'action_type',
        'description',
        'old_data',
        'new_data',
    ];

    protected $casts = [
        'new_data' => 'array',
        'old_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    // Helper untuk format action type
    public function getActionTypeLabel()
    {
        return match ($this->action_type) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'activated' => 'Diaktifkan',
            'deactivated' => 'Dinonaktifkan',
            'role_changed' => 'Role Diubah',
            'verified' => 'Diverifikasi',
            default => $this->action_type,
        };
    }
}
