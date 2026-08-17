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
