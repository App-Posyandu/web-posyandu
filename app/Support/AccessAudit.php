<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AccessAudit
{
    public static function record(
        Request $request,
        ?User $user,
        string $resource,
        string $action,
        bool $allowed,
        int $status,
        array $context = []
    ): void {
        $payload = array_merge([
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user?->id,
            'role' => $user?->role,
            'resource' => $resource,
            'action' => $action,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'status' => $status,
            'allowed' => $allowed,
        ], $context);

        Log::channel('daily')->info('access.audit', $payload);

        if (!$allowed && $user) {
            $key = sprintf('idor:%s:%s:%s', $user->id, $resource, $action);
            if (!Cache::has($key)) {
                Cache::put($key, 0, now()->addMinutes(5));
            }

            $failedAttempts = Cache::increment($key);
            $payload['failed_attempts_last_5m'] = $failedAttempts;

            if ($failedAttempts > 10) {
                Log::channel('daily')->alert('access.suspicious_activity', $payload);
            }
        }
    }
}
