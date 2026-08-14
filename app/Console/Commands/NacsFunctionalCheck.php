<?php

namespace App\Console\Commands;

use App\Support\FunctionalSurfaceReport;
use Illuminate\Console\Command;

class NacsFunctionalCheck extends Command
{
    protected $signature = 'nacs:functional-check
                            {--strict : Exit with failure when a required route/view/asset is missing}
                            {--json : Output a machine-readable report}';

    protected $description = 'Check the NACS-Phil functional route, view, and interaction-asset surface without modifying data.';

    public function handle(FunctionalSurfaceReport $report): int
    {
        $summary = $report->summary();
        $checks = $summary['checks'];
        $failures = $report->requiredFailures($checks);

        if ($this->option('json')) {
            $this->line((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $this->option('strict') && $failures !== []
                ? self::FAILURE
                : self::SUCCESS;
        }

        $this->newLine();
        $this->info('NACS-Phil Functional Surface Check');
        $this->line('Read-only check: routes, key views, and interaction assets. No records or server settings are changed.');
        $this->newLine();

        $rows = array_map(
            fn (array $check): array => [
                $check['passed'] ? 'PASS' : 'BLOCK',
                $check['label'],
                $check['detail'],
            ],
            $checks
        );

        $this->table(['Result', 'Functional Surface', 'Detail'], $rows);

        if ($failures === []) {
            $this->newLine();
            $this->info('All required functional-surface checks passed.');
            $this->line('HTTP/CRUD integration and browser interaction checks remain separate release gates.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn(count($failures).' required functional-surface check(s) failed.');

        if ($this->option('strict')) {
            $this->error('Strict functional surface check failed.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
