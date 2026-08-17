<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\LogSecurityEvents;
use App\Http\Middleware\RequirePrivilegedTwoFactor;
use App\Http\Middleware\VerifyTurnstile;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsurePortalUser;
use App\Http\Middleware\EnsureStaffRole;
use App\Http\Middleware\EnsureStaffPermission;
use App\Http\Middleware\EnsureAdmissionAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'portal' => EnsurePortalUser::class,
            'staff_role' => EnsureStaffRole::class,
            'staff_permission' => EnsureStaffPermission::class,
            'privileged_2fa' => RequirePrivilegedTwoFactor::class,
            'admission.access' => EnsureAdmissionAccess::class,
            'turnstile' => VerifyTurnstile::class,
        ]);

        /*
         * Phase 57 - local reverse-proxy HTTPS awareness.
         *
         * HTTPS tunnel/reverse-proxy agents terminate TLS externally and
         * forward to the local Laravel process over loopback HTTP. Trust only
         * loopback proxy connections so Laravel may honor the standard
         * X-Forwarded-* scheme/host headers without trusting arbitrary
         * Internet clients that try to spoof those headers.
         */
        $middleware->trustProxies(
            at: ['127.0.0.1', '::1'],
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->append(LogSecurityEvents::class);
        $middleware->append(AddSecurityHeaders::class);

        $middleware->redirectGuestsTo(fn (): string => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Keep Laravel's secure production defaults.
    })->create();
