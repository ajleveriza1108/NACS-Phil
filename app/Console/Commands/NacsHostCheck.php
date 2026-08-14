<?php

namespace App\Console\Commands;

use App\Support\HostCapabilityReport;
use Illuminate\Console\Command;

class NacsHostCheck extends Command
{
    protected $signature = 'nacs:host-check
                            {--strict : Exit with failure when a required host capability is missing}
                            {--json : Output a machine-readable report without secrets}';

    protected $description = 'Check whether the current PHP host can run NACS-Phil safely before live cutover.';

    public function handle(HostCapabilityReport $report): int
    {
        $summary = $report->summary();
        $checks = $summary['checks'];
        $failures = $report->requiredFailures($checks);

        if ($this->option('json')) {
            $this->line((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if ($this->option('strict') && $failures !== []) {
                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('NACS-Phil Host Capability Preflight');
        $this->line('This command reports capabilities only. It does not print environment secrets or change server settings.');
        $this->newLine();

        $rows = array_map(
            fn (array $check): array => [
                $check['passed'] ? 'PASS' : ($check['required'] ? 'BLOCK' : 'WARN'),
                $check['required'] ? 'Required' : 'Advisory',
                $check['label'],
                $check['detail'],
            ],
            $checks
        );

        $this->table(['Result', 'Level', 'Capability', 'Detail'], $rows);

        $this->newLine();

        if ($failures === []) {
            $this->info('All required host-capability checks passed.');
            $this->line('Run php artisan nacs:production-check --strict after the real production .env is configured.');

            return self::SUCCESS;
        }

        $this->warn(count($failures).' required host-capability check(s) are missing.');

        if ($this->option('strict')) {
            $this->error('Strict host capability preflight failed.');

            return self::FAILURE;
        }

        $this->line('Non-strict mode reports blockers without failing the command.');

        return self::SUCCESS;
    }
}
