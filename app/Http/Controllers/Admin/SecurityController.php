<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SecurityEventLogger;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SecurityController extends Controller
{
    // Phase 51: rotate CSRF tokens after security-state transitions.
    public function index(Request $request): View
    {
        return view('admin.security.index', [
            'user' => $request->user(),
            'pendingSecret' => $request->session()->get('two_factor_setup_secret'),
            'provisioningUri' => $request->session()->has('two_factor_setup_secret')
                ? Totp::provisioningUri(
                    $request->session()->get('two_factor_setup_secret'),
                    $request->user()->email
                )
                : null,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required','current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $request->user()->forceFill([
            'password' => $data['password'],
            'password_changed_at' => now(),
            'force_password_reset' => false,
        ])->save();

        $this->revokeOtherDatabaseSessions($request);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        app(SecurityEventLogger::class)->record($request, 'auth.password.changed', 'notice', [
            'action' => 'admin_password_change',
        ]);

        return back()->with('success', 'Password updated. Other database-backed sessions were revoked.');
    }

    public function beginTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required','current_password']]);

        $request->session()->put('two_factor_setup_secret', Totp::generateSecret());

        app(SecurityEventLogger::class)->record($request, 'auth.2fa.setup_started', 'notice');

        return back()->with('success', 'Two-factor setup started. Add the secret to your authenticator, then confirm a code.');
    }

    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required','digits:6']]);
        $secret = (string) $request->session()->get('two_factor_setup_secret');

        if ($secret === '' || ! Totp::verify($secret, $data['code'])) {
            app(SecurityEventLogger::class)->record($request, 'auth.2fa.confirm_failed', 'warning', [
                'reason' => 'invalid_code',
            ]);

            return back()->withErrors(['code' => 'The authenticator code is not valid.']);
        }

        $plainRecoveryCodes = [];
        $hashedRecoveryCodes = [];

        for ($index = 0; $index < 8; $index++) {
            $plain = strtoupper(bin2hex(random_bytes(5)));
            $plainRecoveryCodes[] = $plain;
            $hashedRecoveryCodes[] = Hash::make($plain);
        }

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled_at' => now(),
            'two_factor_recovery_codes' => $hashedRecoveryCodes,
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        app(SecurityEventLogger::class)->record($request, 'auth.2fa.enabled', 'notice');

        return back()
            ->with('success', 'Two-factor authentication enabled. Save the recovery codes now.')
            ->with('new_recovery_codes', $plainRecoveryCodes);
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required','current_password'],
            'code' => ['required','string','max:32'],
        ]);

        $user = $request->user();
        $code = strtoupper(trim($request->string('code')->toString()));
        $verified = $user->twoFactorEnabled() && Totp::verify($user->two_factor_secret, $code);

        if (! $verified && $user->twoFactorEnabled()) {
            $hashes = $user->two_factor_recovery_codes ?? [];

            foreach ($hashes as $index => $hash) {
                if (Hash::check($code, $hash)) {
                    unset($hashes[$index]);
                    $user->forceFill(['two_factor_recovery_codes' => array_values($hashes)])->save();
                    $verified = true;
                    break;
                }
            }
        }

        if (! $verified) {
            app(SecurityEventLogger::class)->record($request, 'auth.2fa.disable_failed', 'warning', [
                'reason' => 'invalid_code',
            ]);

            return back()->withErrors(['code' => 'The authenticator or recovery code is not valid.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        app(SecurityEventLogger::class)->record($request, 'auth.2fa.disabled', 'warning');

        return back()->with('success', 'Two-factor authentication disabled.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required','current_password']]);

        $count = $this->revokeOtherDatabaseSessions($request);

        app(SecurityEventLogger::class)->record($request, 'auth.sessions.revoked', 'notice', [
            'count' => $count,
        ]);

        return back()->with('success', 'Other database-backed sessions were revoked.');
    }

    public function challenge(Request $request): View
    {
        abort_unless($request->session()->has('admin_2fa_pending_user_id'), 403);

        return view('admin.auth.two-factor');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required','string','max:32']]);
        $user = User::query()->find($request->session()->get('admin_2fa_pending_user_id'));

        if (! $user || ! $user->twoFactorEnabled()) {
            app(SecurityEventLogger::class)->record($request, 'auth.2fa.challenge_failed', 'warning', [
                'reason' => 'pending_account_invalid',
                'account_present' => (bool) $user,
            ]);

            return back()->withErrors(['code' => 'The authentication code is not valid.']);
        }

        $code = strtoupper(trim($data['code']));
        $verified = Totp::verify($user->two_factor_secret, $code);

        if (! $verified) {
            $hashes = $user->two_factor_recovery_codes ?? [];

            foreach ($hashes as $index => $hash) {
                if (Hash::check($code, $hash)) {
                    unset($hashes[$index]);
                    $user->forceFill(['two_factor_recovery_codes' => array_values($hashes)])->save();
                    $verified = true;
                    break;
                }
            }
        }

        if (! $verified) {
            app(SecurityEventLogger::class)->record($request, 'auth.2fa.challenge_failed', 'warning', [
                'reason' => 'invalid_code',
                'account_present' => true,
            ]);

            return back()->withErrors(['code' => 'The authenticator or recovery code is not valid.']);
        }

        auth()->login($user, false);
        $request->session()->forget('admin_2fa_pending_user_id');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        app(SecurityEventLogger::class)->record($request, 'auth.2fa.challenge_success', 'info');

        return redirect()->route('admin.dashboard');
    }

    private function revokeOtherDatabaseSessions(Request $request): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        return DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
    }
}
