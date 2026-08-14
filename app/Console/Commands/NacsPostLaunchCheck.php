<?php

namespace App\Console\Commands;

use App\Support\PostLaunchHealth;
use Illuminate\Console\Command;

class NacsPostLaunchCheck extends Command
{
    protected $signature = 'nacs:post-launch-check
                            {--live-http : Make read-only GET requests to the configured final APP_URL}
                            {--json : Output a machine-readable report}
                            {--strict : Exit with failure while any requested post-launch gate fails}';

    protected $description = 'Verify the NACS-Phil production baseline and optionally perform live public HTTPS smoke checks.';

    public function handle(PostLaunchHealth $health): int
    {
        $report = $health->summary((bool) $this->option('live-http'));

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->newLine();
            $this->info('NACS-Phil Phase 35 - Post-Launch Verification');
            $this->line($report['note']);
            $this->newLine();
            $this->line('Production blockers: '.$report['production_required_failures']);
            $this->line('Cutover gate: '.($report['cutover_ready'] ? 'PASS' : 'BLOCK'));
            $this->line('Live HTTP performed: '.($report['live_http']['performed'] ? 'YES' : 'NO'));
            $this->line('Live HTTP failures: '.$report['live_http']['failures']);
        }

        if ($this->option('strict') && ! $report['ready_for_post_launch_baseline']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
