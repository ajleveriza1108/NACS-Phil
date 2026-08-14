<?php

namespace App\Console\Commands;

use App\Support\SecurityRecoveryReport;
use Illuminate\Console\Command;

class NacsRecoveryCheck extends Command
{
    protected $signature = 'nacs:recovery-check
                            {--json : Output a machine-readable security/recovery report}
                            {--strict : Fail while a security or restore-verification blocker remains}';

    protected $description = 'Check NACS-Phil public-file exposure, CSP hardening, private storage, and restore verification.';

    public function handle(SecurityRecoveryReport $reporter): int
    {
        $report = $reporter->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 33 - Security and Recovery');
            $this->line('This check does not create, delete, upload, or restore backups.');
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

        if ($this->option('strict') && ! $report['ready_for_cutover']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
