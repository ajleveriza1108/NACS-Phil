<?php

namespace App\Support;

final class AuthSessionSecurityBaseline
{
    public function summary(): array
    {
        $web = $this->read(base_path('routes/web.php'));
        $bootstrap = $this->read(base_path('bootstrap/app.php'));
        $adminAuth = $this->read(app_path('Http/Controllers/Admin/AuthController.php'));
        $adminSecurity = $this->read(app_path('Http/Controllers/Admin/SecurityController.php'));
        $portalPassword = $this->read(app_path('Http/Controllers/Portal/PasswordController.php'));

        $checks = [
            $this->check(
                'admin_session_rotation',
                str_contains($adminAuth, '$request->session()->regenerate();')
                    && str_contains($adminAuth, '$request->session()->regenerateToken();'),
                'Successful staff authentication rotates both session identifier and CSRF token.'
            ),
            $this->check(
                'security_state_token_rotation',
                substr_count($adminSecurity, '$request->session()->regenerateToken();') >= 4,
                'Password and 2FA state transitions rotate CSRF tokens.'
            ),
            $this->check(
                'portal_password_token_rotation',
                str_contains($portalPassword, '$request->session()->regenerateToken();'),
                'Portal password changes rotate the CSRF token after session rotation.'
            ),
            $this->check(
                'privileged_2fa_middleware_registered',
                str_contains($bootstrap, "'privileged_2fa' => RequirePrivilegedTwoFactor::class")
                    && str_contains($web, "middleware(['auth', 'admin', 'privileged_2fa'])"),
                'Privileged 2FA enforcement is wired into the authenticated admin boundary.'
            ),
            $this->check(
                'privileged_2fa_safe_default',
                config('nacs_security.privileged_2fa.required') === false,
                'Privileged 2FA remains staged/off until recovery and administrator training are ready.'
            ),
            $this->check(
                'privileged_2fa_roles',
                config('nacs_security.privileged_2fa.roles') === ['super_admin', 'principal'],
                'Staged mandatory 2FA targets only the configured privileged leadership roles.'
            ),
            $this->check(
                'strong_password_contract',
                str_contains($adminSecurity, 'Password::min(12)->letters()->mixedCase()->numbers()->symbols()')
                    && str_contains($portalPassword, 'Password::min(12)->letters()->mixedCase()->numbers()->symbols()'),
                'Staff and portal password changes share the strong symbol-bearing password contract.'
            ),
            $this->check(
                'recovery_codes_hashed',
                str_contains($adminSecurity, 'Hash::make($plain)'),
                '2FA recovery codes remain hashed at rest.'
            ),
            $this->check(
                'two_factor_challenge_throttled',
                str_contains($web, "middleware('throttle:10,1')")
                    && str_contains($web, "name('admin.two-factor.verify')"),
                '2FA challenge verification remains rate limited.'
            ),
        ];

        $failures = array_values(array_filter($checks, static fn (array $check): bool => ! $check['passed']));

        return [
            'ready' => $failures === [],
            'required_failures' => count($failures),
            'checks' => $checks,
        ];
    }

    private function read(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function check(string $key, bool $passed, string $detail): array
    {
        return compact('key', 'passed', 'detail');
    }
}
