<?php

namespace App\Console\Commands;

use App\Support\ProductionReadiness;
use Illuminate\Console\Command;

class NacsProductionCheck extends Command
{
    protected $signature = 'nacs:production-check
                            {--strict : Exit with failure when any required production check fails}';

    protected $description = 'Check NACS-Phil production environment, session, database, build, and storage readiness.';

    public function handle(ProductionReadiness $readiness): int
    {
        $this->newLine();
        $this->info('NACS-Phil Production Readiness');
        $this->line('This command does not print secrets or modify the server.');
        $this->newLine();

        $checks = $readiness->checks();

        $rows = array_map(
            fn (array $check): array => [
                $check['passed'] ? 'PASS' : ($check['required'] ? 'BLOCK' : 'WARN'),
                $check['required'] ? 'Required' : 'Advisory',
                $check['label'],
                $check['detail'],
            ],
            $checks
        );

        $this->table(['Result', 'Level', 'Check', 'Guidance'], $rows);

        $failed = $readiness->requiredFailures($checks);

        if ($failed === []) {
            $this->newLine();
            $this->info('All required automated production checks passed.');
            $this->line('Manual Launch Readiness and live-device checks are still required.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn(count($failed).' required production check(s) are not ready.');

        if ($this->option('strict')) {
            $this->error('Strict production readiness failed.');

            return self::FAILURE;
        }

        $this->line('Non-strict mode reports blockers without failing the command.');

        return self::SUCCESS;
    }
}
