<?php

namespace App\Console\Commands;

use App\Support\StagingReadiness;
use Illuminate\Console\Command;

class NacsStagingCheck extends Command
{
    protected $signature = 'nacs:staging-check
                            {--json : Output a machine-readable report}
                            {--strict : Exit with failure while any staging blocker remains}';

    protected $description = 'Check NACS-Phil staging configuration without printing secrets or modifying the host.';

    public function handle(StagingReadiness $readiness): int
    {
        $report = $readiness->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 31 - Staging Readiness');
            $this->line('Read-only staging configuration and host gate.');
            $this->newLine();

            $rows = array_map(
                static fn (array $check): array => [
                    $check['passed'] ? 'PASS' : 'BLOCK',
                    $check['label'],
                    $check['detail'],
                ],
                $report['checks']
            );

            $this->table(['Result', 'Check', 'Guidance'], $rows);
        }

        if ($this->option('strict') && ! $report['ready_for_staging_acceptance']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
