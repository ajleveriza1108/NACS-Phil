<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequirePrivilegedTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('nacs_security.privileged_2fa.required', false)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || $user->is_admin !== true || $user->is_active === false) {
            return $next($request);
        }

        $roles = (array) config('nacs_security.privileged_2fa.roles', ['super_admin', 'principal']);

        if (! in_array($user->staffRole(), $roles, true) || $user->twoFactorEnabled()) {
            return $next($request);
        }

        if ($request->routeIs('admin.security.*', 'admin.logout')) {
            return $next($request);
        }

        return redirect()
            ->route('admin.security.index')
            ->with('warning', 'Two-factor authentication is required for this privileged account before continuing.');
    }
}
