<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SecurityEventLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required','email:rfc','max:150'],
            'password' => ['required','string'],
        ]);

        $email = Str::lower($credentials['email']);
        $user = User::query()->where('email', $email)->first();
        $security = app(SecurityEventLogger::class);

        if ($user?->isTemporarilyLocked()) {
            $security->record($request, 'auth.portal.locked', 'warning', [
                'reason' => 'temporary_lock',
                'account_present' => true,
            ]);

            return back()->withErrors([
                'email' => 'This portal account is temporarily locked. Please try again later.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            if ($user) {
                $failures = $user->failed_login_count + 1;
                $user->forceFill([
                    'failed_login_count' => $failures,
                    'locked_until' => $failures >= 5 ? now()->addMinutes(15) : null,
                ])->save();
            }

            $security->record($request, 'auth.portal.failed', 'warning', [
                'reason' => 'invalid_credentials',
                'account_present' => (bool) $user,
            ]);

            return back()->withErrors([
                'email' => 'The email address or password is incorrect.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (
            ! $user
            || $user->is_admin === true
            || $user->is_active === false
            || ! in_array($user->role, ['student', 'parent'], true)
        ) {
            $security->record($request, 'auth.portal.denied', 'warning', [
                'reason' => 'wrong_role_or_inactive',
                'account_present' => (bool) $user,
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This account is not authorized for the student and parent portal.',
            ])->onlyInput('email');
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip_hash' => $security->fingerprint((string) $request->ip()),
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();

        $security->record($request, 'auth.portal.success');

        return $user->force_password_reset
            ? redirect()->route('portal.password.edit')
            : redirect()->route('portal.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        app(SecurityEventLogger::class)->record($request, 'auth.portal.logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
