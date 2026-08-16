<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\VerifyTurnstile;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsurePortalUser;
use App\Http\Middleware\EnsureStaffRole;
use App\Http\Middleware\EnsureStaffPermission;
use App\Http\Middleware\EnsureAdmissionAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
            'admission.access' => EnsureAdmissionAccess::class,
            'turnstile' => VerifyTurnstile::class,
        ]);

        $middleware->append(AddSecurityHeaders::class);

        $middleware->redirectGuestsTo(fn (): string => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Keep Laravel's secure production defaults.
    })->create();
