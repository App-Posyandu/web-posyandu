<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SyncDirective extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kabupaten',
        'is_active',
        'message',
        'activated_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    /**
     * Apakah seorang user (operator-desa) sudah menyelesaikan sinkronisasi untuk versi directive ini.
     */
    public function isDoneBy(User $user): bool
    {
        return $this->activated_at
            && $user->sync_done_at
            && $user->sync_done_at->gte($this->activated_at);
    }
}
