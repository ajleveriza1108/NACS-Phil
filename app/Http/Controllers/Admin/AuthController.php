<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required','email:rfc','max:150'],
            'password' => ['required','string'],
        ]);

        $user = User::query()->where('email', Str::lower($credentials['email']))->first();

        if ($user?->isTemporarilyLocked()) {
            return back()->withErrors([
                'email' => 'This staff account is temporarily locked after repeated failed sign-in attempts. Please try again later.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($user) {
                $failures = $user->failed_login_count + 1;
                $user->forceFill([
                    'failed_login_count' => $failures,
                    'locked_until' => $failures >= 5 ? now()->addMinutes(15) : null,
                ])->save();
            }

            return back()->withErrors([
                'email' => 'The email address or password is incorrect.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if ($user?->is_admin !== true || $user?->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This staff account is not currently authorized for school administration.',
            ])->onlyInput('email');
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip_hash' => hash('sha256', (string) $request->ip()),
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();

        if ($user->twoFactorEnabled()) {
            $request->session()->put('admin_2fa_pending_user_id', $user->id);
            Auth::logout();

            return redirect()->route('admin.two-factor.challenge');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
