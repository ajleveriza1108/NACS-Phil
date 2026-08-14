<?php

namespace App\Console\Commands;

use App\Support\ProductionDataInventory;
use Illuminate\Console\Command;

class NacsDataAudit extends Command
{
    protected $signature = 'nacs:data-audit
                            {--json : Output a machine-readable counts-only report}
                            {--strict : Fail when migration decisions or table classification are incomplete}';

    protected $description = 'Inventory NACS-Phil production-migration data without printing private record values.';

    public function handle(ProductionDataInventory $inventory): int
    {
        $report = $inventory->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 30 - Production Data Migration Readiness');
            $this->line('Read-only inventory. Record contents, names, email addresses, access codes, and document names are not printed.');
            $this->newLine();

            $rows = [];
            foreach ($report['groups'] as $group => $data) {
                $rows[] = [
                    str_replace('_', ' ', (string) $group),
                    count($data['tables']),
                    $data['record_count'],
                ];
            }

            $this->table(['Classification', 'Tables', 'Records'], $rows);
            $this->line('Database driver: '.$report['source_driver']);
            $this->line('Unknown tables: '.count($report['unknown_tables']));
            $this->line('Private storage files: '.$report['private_file_counts']['private_storage']);
            $this->line('Admissions storage files: '.$report['private_file_counts']['admissions_storage']);
            $this->line('Decision record complete: '.($report['decision']['complete'] ? 'YES' : 'NO'));

            if ($report['blockers'] !== []) {
                $this->newLine();
                foreach ($report['blockers'] as $blocker) {
                    $this->warn($blocker);
                }
            }
        }

        if ($this->option('strict') && ! $report['ready_for_controlled_migration']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
