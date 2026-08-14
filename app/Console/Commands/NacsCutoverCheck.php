<?php

namespace App\Console\Commands;

use App\Support\LiveCutoverReadiness;
use Illuminate\Console\Command;

class NacsCutoverCheck extends Command
{
    protected $signature = 'nacs:cutover-check
                            {--json : Output a machine-readable cutover gate}
                            {--strict : Fail until every production cutover gate passes}';

    protected $description = 'Aggregate NACS-Phil production, host, data, browser, security, and recovery gates before DNS cutover.';

    public function handle(LiveCutoverReadiness $readiness): int
    {
        $report = $readiness->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 34 - Live Cutover Gate');
            $this->line('Read-only. DNS and production credentials are never changed by this command.');
            $this->newLine();

            $rows = array_map(
                static fn (array $check): array => [
                    $check['passed'] ? 'PASS' : 'BLOCK',
                    $check['label'],
                    $check['detail'],
                ],
                $report['checks']
            );

            $this->table(['Result', 'Gate', 'Required command/action'], $rows);
        }

        if ($this->option('strict') && ! $report['ready_for_live_cutover']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
