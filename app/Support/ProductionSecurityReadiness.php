<?php

namespace App\Support;

final class ProductionSecurityReadiness
{
    public function report(bool $sourceOnly = false): array
    {
        $checks = $this->sourceChecks();

        if (! $sourceOnly) {
            $checks = array_merge($checks, $this->hostChecks());
        }

        $failures = array_values(array_filter($checks, static fn (array $check): bool => ! $check['passed']));

        return [
            'ready' => $failures === [],
            'source_only' => $sourceOnly,
            'required_failures' => count($failures),
            'checks' => $checks,
        ];
    }

    private function sourceChecks(): array
    {
        $workflow = $this->read(base_path('.github/workflows/quality-gate.yml'));

        return [
            $this->check(
                'phase49_authorization_baseline',
                class_exists(AuthorizationSecurityBaseline::class),
                'Authorization/IDOR source baseline is present.'
            ),
            $this->check(
                'phase50_input_abuse_baseline',
                class_exists(InputAbuseSecurityBaseline::class),
                'Input/upload/abuse source baseline is present.'
            ),
            $this->check(
                'phase51_auth_session_baseline',
                class_exists(AuthSessionSecurityBaseline::class),
                'Authentication/session source baseline is present.'
            ),
            $this->check(
                'phase48_security_baseline_retained',
                class_exists(SecurityBaseline::class) && is_file(config_path('nacs_security.php')),
                'Phase 48 security foundation remains installed.'
            ),
            $this->check(
                'production_evidence_config',
                is_array(config('production_security.host_gates')),
                'Real-host security evidence configuration exists.'
            ),
            $this->check(
                'github_security_roadmap_gate',
                str_contains($workflow, 'php artisan nacs:authorization-baseline --strict')
                    && str_contains($workflow, 'php artisan nacs:input-abuse-baseline --strict')
                    && str_contains($workflow, 'php artisan nacs:auth-session-baseline --strict')
                    && str_contains($workflow, 'php artisan nacs:production-security-readiness --source-only --strict'),
                'GitHub PR CI enforces all source-side Phase 49-52 security gates.'
            ),
            $this->check(
                'production_runbook_present',
                is_file(base_path('PHASE52_PRODUCTION_SECURITY_MONITORING.md'))
                    && is_file(base_path('PRODUCTION_SECURITY_CHECKLIST.md')),
                'Production security monitoring runbook and evidence checklist are tracked.'
            ),
        ];
    }

    private function hostChecks(): array
    {
        $gates = (array) config('production_security.host_gates', []);

        return [
            $this->hostCheck('tls_https_verified', 'Real TLS certificate and HTTPS redirect verified', $gates),
            $this->hostCheck('database_private_verified', 'Production database is not publicly reachable', $gates),
            $this->hostCheck('backup_restore_verified', 'Production backup and restore procedure tested', $gates),
            $this->hostCheck('central_logging_verified', 'Central security-log retention and alert delivery verified', $gates),
            $this->hostCheck('waf_cdn_verified', 'WAF/CDN abuse controls verified where supported', $gates),
            $this->hostCheck('access_review_verified', 'Privileged production-access review completed', $gates),
            $this->hostCheck('vapt_verified', 'Independent production VAPT completed', $gates),
        ];
    }

    private function hostCheck(string $key, string $detail, array $gates): array
    {
        return [
            'key' => $key,
            'passed' => (bool) ($gates[$key] ?? false),
            'scope' => 'real-host-evidence',
            'detail' => $detail,
        ];
    }

    private function check(string $key, bool $passed, string $detail): array
    {
        return [
            'key' => $key,
            'passed' => $passed,
            'scope' => 'source',
            'detail' => $detail,
        ];
    }

    private function read(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
