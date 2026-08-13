<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user?->is_admin === true
            && $user?->is_active !== false
            && in_array($user->staffRole(), $roles, true),
            403
        );

        return $next($request);
    }
}