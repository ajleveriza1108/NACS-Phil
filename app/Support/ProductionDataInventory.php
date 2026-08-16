<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use Throwable;

final class ProductionDataInventory
{
    /** @var array<string, array<int, string>> */
    private const TABLE_GROUPS = [
        'public_content' => [
            'announcements',
            'school_events',
            'gallery_items',
            'faculty_profiles',
            'academic_calendar_entries',
            'school_documents',
            'facebook_media_items',
            'site_contents',
            'school_settings',
            'seo_settings',
            'media_assets',
        ],
        'staff_accounts' => [
            'users',
        ],
        'private_family_data' => [
            'inquiries',
            'admission_applications',
            'admission_documents',
            'admission_checklist_items',
            'admission_events',
            'students',
            'student_teacher_assignments',
            'student_grades',
            'student_attendances',
            'student_guardians',
            'student_documents',
            'student_financial_entries',
        ],
        'audit_records' => [
            'content_audits',
            'student_record_audits',
        ],
        'runtime_only' => [
            'migrations',
            'password_reset_tokens',
            'registration_invitations',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ],
    ];

    /** @var array<string, string> */
    private const DECISION_CHECKS = [
        'public_content_reviewed' => 'Public CMS content reviewed for transfer.',
        'staff_accounts_reviewed' => 'Staff/admin production account plan reviewed.',
        'private_family_data_reviewed' => 'Inquiry/admissions data handling approved.',
        'private_documents_reviewed' => 'Private admissions document handling approved.',
        'target_database_plan_approved' => 'Target MySQL/MariaDB migration plan approved.',
        'source_database_not_committed' => 'Database exports will not be committed to Git.',
    ];

    /**
     * Read-only inventory. It intentionally reports counts and table names only.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $connection = DB::connection();
        $driver = (string) $connection->getDriverName();
        $tables = array_values(array_map(
            static fn (mixed $table): string => (string) $table,
            $connection->getSchemaBuilder()->getTableListing()
        ));

        sort($tables);

        $groups = [];
        foreach (array_keys(self::TABLE_GROUPS) as $group) {
            $groups[$group] = [
                'tables' => [],
                'record_count' => 0,
            ];
        }

        $unknownTables = [];
        $unavailableCounts = [];

        foreach ($tables as $table) {
            $logicalTable = $this->logicalTableName($table);
            $group = $this->groupFor($logicalTable);
            $count = $this->safeCount($table);

            if ($count === null) {
                $unavailableCounts[] = $logicalTable;
                $count = 0;
            }

            if ($group === null) {
                $unknownTables[] = $logicalTable;
                continue;
            }

            $groups[$group]['tables'][$logicalTable] = $count;
            $groups[$group]['record_count'] += $count;
        }

        $decision = $this->decisionSummary();
        $blockers = [];

        if ($unknownTables !== []) {
            $blockers[] = 'Unclassified database tables require manual review before migration.';
        }

        if ($unavailableCounts !== []) {
            $blockers[] = 'One or more database table counts could not be read safely.';
        }

        if (! $decision['complete']) {
            $blockers[] = 'The local production-data migration decision record is incomplete.';
        }

        return [
            'application' => 'NACS-Phil',
            'phase' => 30,
            'source_driver' => $driver,
            'source_is_sqlite' => $driver === 'sqlite',
            'groups' => $groups,
            'unknown_tables' => $unknownTables,
            'unavailable_counts' => $unavailableCounts,
            'private_file_counts' => [
                'private_storage' => $this->fileCount(storage_path('app/private')),
                'admissions_storage' => $this->fileCount(storage_path('app/admissions')),
                'public_media_storage' => $this->fileCount(storage_path('app/public')),
            ],
            'decision' => $decision,
            'blockers' => $blockers,
            'ready_for_controlled_migration' => $blockers === [],
            'safe_to_export_to_source_control' => false,
            'privacy_note' => 'This report contains counts only. Never commit a database export or private admissions files.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function decisionSummary(): array
    {
        $path = base_path('.nacs-data-migration-decision.json');
        $data = [];

        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $checks = [];
        $missing = [];

        foreach (self::DECISION_CHECKS as $key => $label) {
            $passed = ($data['checks'][$key] ?? false) === true;
            $checks[$key] = [
                'label' => $label,
                'passed' => $passed,
            ];

            if (! $passed) {
                $missing[] = $key;
            }
        }

        return [
            'record_present' => is_file($path),
            'record_path' => '.nacs-data-migration-decision.json',
            'checks' => $checks,
            'missing' => $missing,
            'complete' => $missing === [],
        ];
    }

    private function logicalTableName(string $table): string
    {
        $table = trim($table);

        if (! str_contains($table, '.')) {
            return $table;
        }

        $logical = substr($table, strrpos($table, '.') + 1);

        return $logical === '' ? $table : $logical;
    }

    private function groupFor(string $table): ?string
    {
        foreach (self::TABLE_GROUPS as $group => $tables) {
            if (in_array($table, $tables, true)) {
                return $group;
            }
        }

        return null;
    }

    private function safeCount(string $table): ?int
    {
        try {
            return (int) DB::table($table)->count();
        } catch (Throwable) {
            return null;
        }
    }

    private function fileCount(string $path): int
    {
        if (! is_dir($path)) {
            return 0;
        }

        $count = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && ! $file->isLink()) {
                    $count++;
                }
            }
        } catch (Throwable) {
            return 0;
        }

        return $count;
    }
}
