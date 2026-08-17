<?php

namespace App\Support;

class SecurityBaseline
{
    public function summary(bool $production = false): array
    {
        $checks = $this->sourceChecks();

        if ($production) {
            $checks = array_merge($checks, $this->productionChecks());
        }

        $failures = array_values(array_filter(
            $checks,
            static fn (array $check): bool => $check['required'] && ! $check['passed']
        ));

        return [
            'application' => 'NACS-Phil',
            'scope' => $production ? 'source+production-runtime' : 'source',
            'source_ready' => ! collect($this->sourceChecks())->contains(
                static fn (array $check): bool => $check['required'] && ! $check['passed']
            ),
            'production_requested' => $production,
            'required_failures' => count($failures),
            'checks' => $checks,
            'manual_production_gates' => config('nacs_security.manual_production_gates', []),
            'future_surfaces' => SecuritySurfaceInventory::future(),
        ];
    }

    private function sourceChecks(): array
    {
        $web = (string) @file_get_contents(base_path('routes/web.php'));
        $portal = (string) @file_get_contents(base_path('routes/student_portal.php'));

        return [
            $this->check(
                'security_log_channel',
                config('logging.channels.security.driver') === 'daily',
                'Dedicated daily security log channel exists.'
            ),
            $this->check(
                'security_event_middleware',
                class_exists(\App\Http\Middleware\LogSecurityEvents::class),
                'Security response logging middleware exists.'
            ),
            $this->check(
                'session_http_only',
                config('session.http_only') === true,
                'Session cookie is HttpOnly.'
            ),
            $this->check(
                'session_json_serialization',
                config('session.serialization') === 'json',
                'Session serialization is JSON.'
            ),
            $this->check(
                'session_same_site',
                in_array(config('session.same_site'), ['lax', 'strict'], true),
                'Session SameSite policy is Lax or Strict.'
            ),
            $this->check(
                'admin_login_abuse_controls',
                str_contains($web, "throttle:5,1") && str_contains($web, 'turnstile:admin_login'),
                'Admin login is rate limited and Turnstile protected.'
            ),
            $this->check(
                'public_form_abuse_controls',
                str_contains($web, 'turnstile:inquiry')
                    && str_contains($web, 'turnstile:admissions_apply')
                    && str_contains($web, 'turnstile:admissions_track'),
                'Public write surfaces keep Turnstile protection.'
            ),
            $this->check(
                'student_sensitive_throttles',
                str_contains($portal, "students/{student}/photo")
                    && str_contains($portal, 'throttle:10,10')
                    && str_contains($portal, 'throttle:30,1'),
                'Student photo and academic-record sensitive surfaces are rate limited.'
            ),
            $this->check(
                'private_student_photo_disk',
                config('student_portal.profile_photo.disk', 'local') !== 'public',
                'Student profile photos are not stored on the public disk.'
            ),
            $this->check(
                'future_surfaces_not_activated',
                collect(SecuritySurfaceInventory::future())->every(
                    static fn (array $surface): bool => $surface['status'] === 'future_only'
                ),
                'Mobile API, live payments, and AI generation remain future-only.'
            ),
        ];
    }

    private function productionChecks(): array
    {
        return [
            $this->check(
                'production_environment',
                app()->environment('production'),
                'APP_ENV is production.'
            ),
            $this->check(
                'debug_disabled',
                config('app.debug') === false,
                'APP_DEBUG is disabled.'
            ),
            $this->check(
                'https_app_url',
                str_starts_with((string) config('app.url'), 'https://'),
                'APP_URL uses HTTPS.'
            ),
            $this->check(
                'secure_session_cookie',
                config('session.secure') === true,
                'Session cookie is HTTPS-only.'
            ),
            $this->check(
                'encrypted_sessions',
                config('session.encrypt') === true,
                'Session payload encryption is enabled.'
            ),
            $this->check(
                'application_key_present',
                filled(config('app.key')),
                'APP_KEY is configured.'
            ),
            $this->check(
                'turnstile_enabled',
                (bool) config('services.turnstile.enabled', false),
                'Turnstile is enabled for production public forms.'
            ),
        ];
    }

    private function check(string $key, bool $passed, string $detail, bool $required = true): array
    {
        return compact('key', 'passed', 'detail', 'required');
    }
}
