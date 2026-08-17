<?php

use App\Support\SecurityBaseline;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command(
    'nacs:security-baseline {--production : Include host/runtime production checks} {--json : Emit JSON} {--strict : Exit non-zero when required checks fail}',
    function (SecurityBaseline $baseline): int {
        $report = $baseline->summary((bool) $this->option('production'));

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('NACS-Phil Security Baseline');
            $this->line('Scope: '.$report['scope']);

            foreach ($report['checks'] as $check) {
                $label = $check['passed'] ? 'PASS' : 'FAIL';
                $this->line(sprintf('[%s] %s - %s', $label, $check['key'], $check['detail']));
            }

            if ($report['manual_production_gates'] !== []) {
                $this->newLine();
                $this->comment('Manual production gates remain host-specific:');
                foreach ($report['manual_production_gates'] as $gate) {
                    $this->line(' - '.$gate);
                }
            }
        }

        if ($this->option('strict') && $report['required_failures'] > 0) {
            return 1;
        }

        return 0;
    }
)->purpose('Audit NACS-Phil source security controls and optional production-runtime gates');

// Phase 49-52 security roadmap source gates.
Artisan::command('nacs:authorization-baseline {--strict}', function (): int {
    $report = app(\App\Support\AuthorizationSecurityBaseline::class)->summary();
    $this->info('NACS-Phil Phase 49 Authorization / IDOR Baseline');

    foreach ($report['checks'] as $check) {
        $this->line(sprintf('[%s] %s - %s', $check['passed'] ? 'PASS' : 'FAIL', $check['key'], $check['detail']));
    }

    return $this->option('strict') && ! $report['ready'] ? 1 : 0;
})->purpose('Audit authorization and object-level access boundaries');

Artisan::command('nacs:input-abuse-baseline {--strict}', function (): int {
    $report = app(\App\Support\InputAbuseSecurityBaseline::class)->summary();
    $this->info('NACS-Phil Phase 50 Input / Upload / Abuse Baseline');

    foreach ($report['checks'] as $check) {
        $this->line(sprintf('[%s] %s - %s', $check['passed'] ? 'PASS' : 'FAIL', $check['key'], $check['detail']));
    }

    return $this->option('strict') && ! $report['ready'] ? 1 : 0;
})->purpose('Audit validation, upload allowlists, and risk-based throttles');

Artisan::command('nacs:auth-session-baseline {--strict}', function (): int {
    $report = app(\App\Support\AuthSessionSecurityBaseline::class)->summary();
    $this->info('NACS-Phil Phase 51 Authentication / Session Baseline');

    foreach ($report['checks'] as $check) {
        $this->line(sprintf('[%s] %s - %s', $check['passed'] ? 'PASS' : 'FAIL', $check['key'], $check['detail']));
    }

    return $this->option('strict') && ! $report['ready'] ? 1 : 0;
})->purpose('Audit authentication-state rotation and staged privileged 2FA');

Artisan::command('nacs:production-security-readiness {--source-only} {--strict}', function (): int {
    $sourceOnly = (bool) $this->option('source-only');
    $report = app(\App\Support\ProductionSecurityReadiness::class)->report($sourceOnly);

    $this->info('NACS-Phil Phase 52 Production Security Readiness');
    $this->line('Scope: '.($sourceOnly ? 'source-only' : 'source + real-host evidence'));

    foreach ($report['checks'] as $check) {
        $label = $check['passed'] ? 'PASS' : ($check['scope'] === 'real-host-evidence' ? 'PENDING' : 'FAIL');
        $this->line(sprintf('[%s] %s - %s', $label, $check['key'], $check['detail']));
    }

    if ($sourceOnly) {
        $this->line('Source-only readiness: '.($report['ready'] ? 'PASS' : 'FAIL'));
    } else {
        $this->line('Production-security readiness: '.($report['ready'] ? 'PASS' : 'PENDING'));
    }

    return $this->option('strict') && ! $report['ready'] ? 1 : 0;
})->purpose('Report source and real-host production security readiness without exposing secrets');
