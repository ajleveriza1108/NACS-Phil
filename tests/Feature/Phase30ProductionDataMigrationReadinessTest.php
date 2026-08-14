<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Support\ProductionDataInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase30ProductionDataMigrationReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_only_data_audit_does_not_expose_private_record_values(): void
    {
        $privateEmail = 'phase30-private-family@example.test';

        Inquiry::create([
            'guardian_name' => 'Private Phase Thirty Guardian',
            'email' => $privateEmail,
            'phone' => '09171234567',
            'student_name' => 'Private Student',
            'level_interested' => 'Grade 1',
            'message' => 'Private migration audit test message.',
            'status' => 'new',
            'privacy_consent_at' => now(),
        ]);

        Artisan::call('nacs:data-audit', ['--json' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('"source_driver"', $output);
        $this->assertStringContainsString('"private_family_data"', $output);
        $this->assertStringNotContainsString($privateEmail, $output);
        $this->assertStringNotContainsString('Private Phase Thirty Guardian', $output);
        $this->assertStringNotContainsString('Private Student', $output);
        $this->assertStringNotContainsString('Private migration audit test message.', $output);
    }

    public function test_sqlite_schema_prefixes_are_normalized_and_known_tables_are_classified(): void
    {
        $report = app(ProductionDataInventory::class)->summary();

        $this->assertSame([], $report['unknown_tables']);
        $this->assertArrayHasKey('announcements', $report['groups']['public_content']['tables']);
        $this->assertArrayHasKey('seo_settings', $report['groups']['public_content']['tables']);
        $this->assertArrayHasKey('inquiries', $report['groups']['private_family_data']['tables']);
        $this->assertArrayHasKey('users', $report['groups']['staff_accounts']['tables']);
    }

    public function test_inventory_never_marks_database_exports_safe_for_source_control(): void
    {
        $report = app(ProductionDataInventory::class)->summary();

        $this->assertFalse($report['safe_to_export_to_source_control']);
        $this->assertArrayHasKey('decision', $report);
        $this->assertArrayHasKey('unknown_tables', $report);
        $this->assertArrayHasKey('private_file_counts', $report);
    }

    public function test_phase_thirty_decision_template_and_guide_exist(): void
    {
        $this->assertFileExists(base_path('PRODUCTION_DATA_MIGRATION_DECISION.example.json'));
        $this->assertFileExists(base_path('PHASE30_PRODUCTION_DATA_MIGRATION.md'));

        $template = json_decode(
            (string) file_get_contents(base_path('PRODUCTION_DATA_MIGRATION_DECISION.example.json')),
            true
        );

        $this->assertIsArray($template);
        $this->assertSame(30, $template['phase']);
        $this->assertFalse($template['checks']['private_family_data_reviewed']);
        $this->assertFalse($template['checks']['source_database_not_committed']);
    }
}
