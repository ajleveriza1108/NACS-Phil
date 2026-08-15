<?php

namespace App\Console\Commands;

use App\Support\GoLiveReadiness;
use Illuminate\Console\Command;

class NacsGoLiveCheck extends Command
{
    protected $signature = 'nacs:go-live-check
                            {--json : Output a machine-readable report}
                            {--source-only : Check tracked source readiness without requiring a real host}
                            {--strict : Exit with failure while the requested readiness scope has blockers}';

    protected $description = 'Aggregate final NACS-Phil source and live-cutover readiness without changing deployment, DNS, credentials, or acceptance records.';

    public function handle(GoLiveReadiness $readiness): int
    {
        $sourceOnly = $this->option('source-only');
        $report = $sourceOnly ? $readiness->sourceSummary() : $readiness->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($sourceOnly) {
            $this->newLine();
            $this->info('NACS-Phil Phase 42 - Go-Live Source Readiness');
            $this->line('This check validates tracked source contracts only. It does not perform manual or host acceptance.');
            $this->newLine();

            $rows = array_map(
                static fn (array $check): array => [
                    $check['passed'] ? 'PASS' : 'BLOCK',
                    $check['label'],
                    $check['detail'],
                ],
                $report['checks']
            );

            $this->table(['Result', 'Source gate', 'Guidance'], $rows);
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 42 - Go-Live Readiness');
            $this->line('Read-only. This command never changes DNS, deployment, credentials, acceptance records, or backups.');
            $this->newLine();

            $sourceRows = array_map(
                static fn (array $check): array => [
                    $check['passed'] ? 'PASS' : 'BLOCK',
                    $check['label'],
                    $check['detail'],
                ],
                $report['source']['checks']
            );

            $this->table(['Result', 'Source gate', 'Guidance'], $sourceRows);
            $this->newLine();

            $externalRows = array_map(
                static fn (array $check): array => [
                    $check['passed'] ? 'PASS' : 'BLOCK',
                    $check['label'],
                    $check['detail'],
                ],
                $report['external_cutover']['checks']
            );

            $this->table(['Result', 'External cutover gate', 'Required command/action'], $externalRows);
            $this->newLine();
            $this->line('Source ready: '.($report['source_ready'] ? 'YES' : 'NO'));
            $this->line('External cutover ready: '.($report['external_ready'] ? 'YES' : 'NO'));
            $this->line('Ready for live production: '.($report['ready_for_live'] ? 'YES' : 'NO'));
        }

        if (! $this->option('strict')) {
            return self::SUCCESS;
        }

        $ready = $sourceOnly
            ? $report['source_ready'] === true
            : $report['ready_for_live'] === true;

        if (! $ready) {
            $this->error(
                $sourceOnly
                    ? 'Strict source readiness failed.'
                    : 'Strict go-live readiness failed. Complete the remaining manual and real-host gates.'
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
