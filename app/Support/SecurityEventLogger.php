<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityEventLogger
{
    private const ALLOWED_EXTRA_KEYS = [
        'status',
        'reason',
        'account_present',
        'two_factor',
        'resource_type',
        'action',
        'count',
    ];

    public function record(
        Request $request,
        string $event,
        string $level = 'info',
        array $extra = []
    ): void {
        Log::channel((string) config('nacs_security.logging.channel', 'security'))
            ->log($level, $event, $this->context($request, $extra));
    }

    public function context(Request $request, array $extra = []): array
    {
        $user = $request->user();
        $route = $request->route();

        $context = [
            'user_id' => $user?->getAuthIdentifier(),
            'role' => $user instanceof User ? ($user->staffRole() ?? $user->role) : null,
            'route_name' => is_object($route) && method_exists($route, 'getName')
                ? $route->getName()
                : null,
            'route_template' => is_object($route) && method_exists($route, 'uri')
                ? $route->uri()
                : null,
            'method' => strtoupper($request->method()),
            'ip_hash' => $this->fingerprint((string) $request->ip()),
            'user_agent_hash' => $this->fingerprint((string) $request->userAgent()),
        ];

        foreach (self::ALLOWED_EXTRA_KEYS as $key) {
            if (array_key_exists($key, $extra)) {
                $context[$key] = $extra[$key];
            }
        }

        return array_filter(
            $context,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        );
    }

    public function fingerprint(string $value): ?string
    {
        $value = trim($value);
        $key = (string) config('app.key');

        if ($value === '' || $key === '') {
            return null;
        }

        return hash_hmac('sha256', $value, $key);
    }
}
