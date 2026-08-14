<?php

namespace App\Console\Commands;

use App\Support\BrowserAcceptanceGate;
use Illuminate\Console\Command;

class NacsAcceptanceCheck extends Command
{
    protected $signature = 'nacs:acceptance-check
                            {--json : Output a machine-readable acceptance report}
                            {--strict : Fail until automated and manual acceptance gates are complete}';

    protected $description = 'Report NACS-Phil Phase 32 browser/device acceptance without pretending manual checks were performed.';

    public function handle(BrowserAcceptanceGate $gate): int
    {
        $report = $gate->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 32 - Browser and Device Acceptance');
            $this->line('Manual checks remain BLOCK until a real tester marks the ignored local acceptance record.');
            $this->newLine();

            $rows = [];
            foreach ($report['manual_checks'] as $check) {
                $rows[] = [
                    $check['passed'] ? 'PASS' : 'BLOCK',
                    $check['label'],
                ];
            }

            $this->table(['Result', 'Manual acceptance item'], $rows);
            $this->line('Automated functional-surface failures: '.$report['automated_required_failures']);
        }

        if ($this->option('strict') && ! $report['ready_for_cutover']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
