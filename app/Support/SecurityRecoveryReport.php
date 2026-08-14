<?php

namespace App\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use Throwable;

final class SecurityRecoveryReport
{
    /** @var array<string, string> */
    private const RECOVERY_CHECKS = [
        'database_backup_created' => 'Production database backup exists.',
        'database_restore_tested' => 'Database restore was tested.',
        'private_files_backup_created' => 'Private admissions files are included in backup.',
        'private_files_restore_tested' => 'Private admissions file restore was tested.',
        'public_media_restore_tested' => 'Public media restore was tested.',
        'offsite_copy_confirmed' => 'At least one protected copy exists outside the live account.',
    ];

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $leaks = $this->publicLeakPaths();
        $middleware = (string) @file_get_contents(app_path('Http/Middleware/AddSecurityHeaders.php'));
        $recovery = $this->recoverySummary();

        $publicRoot = $this->normalizedRealPath(public_path());
        $privateRoot = $this->normalizedRealPath(storage_path('app/private'));
        $admissionsRoot = $this->normalizedRealPath(storage_path('app/admissions'));

        $checks = [
            $this->check(
                'no_sensitive_public_files',
                'No sensitive backup/database/key files under public',
                $leaks === [],
                $leaks === [] ? 'No forbidden public artifacts detected.' : count($leaks).' forbidden public artifact(s) detected.'
            ),
            $this->check(
                'private_storage_outside_public',
                'Private storage remains outside public',
                $this->outsidePublic($privateRoot, $publicRoot),
                'storage/app/private must not resolve inside public.'
            ),
            $this->check(
                'admissions_storage_outside_public',
                'Admissions storage remains outside public',
                $this->outsidePublic($admissionsRoot, $publicRoot),
                'storage/app/admissions must not resolve inside public.'
            ),
            $this->check(
                'strict_csp',
                'Restrictive CSP includes required external origins',
                str_contains($middleware, "'https://challenges.cloudflare.com'")
                    && str_contains($middleware, "'script-src '.implode(' ', \$scriptSources)")
                    && str_contains($middleware, "'connect-src '.implode(' ', \$connectSources)")
                    && str_contains($middleware, "frame-src 'self' https://challenges.cloudflare.com https://www.facebook.com")
                    && str_contains($middleware, "object-src 'none'")
                    && str_contains($middleware, "frame-ancestors 'self'"),
                'CSP must permit Turnstile and the approved Facebook video iframe while blocking unrelated origins.'
            ),
            $this->check(
                'recovery_verified',
                'Backup and restore verification recorded',
                $recovery['complete'],
                $recovery['complete']
                    ? 'All required backup/restore checks are recorded.'
                    : 'Complete the ignored local .nacs-recovery-verification.json record.'
            ),
        ];

        $failed = array_values(array_filter(
            $checks,
            static fn (array $check): bool => ! $check['passed']
        ));

        return [
            'application' => 'NACS-Phil',
            'phase' => 33,
            'checks' => $checks,
            'public_leak_paths' => $leaks,
            'recovery' => $recovery,
            'required_failures' => count($failed),
            'ready_for_cutover' => $failed === [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function recoverySummary(): array
    {
        $path = base_path('.nacs-recovery-verification.json');
        $data = [];

        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $checks = [];
        $missing = [];

        foreach (self::RECOVERY_CHECKS as $key => $label) {
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
            'record_path' => '.nacs-recovery-verification.json',
            'checks' => $checks,
            'missing' => $missing,
            'complete' => $missing === [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function publicLeakPaths(): array
    {
        $root = public_path();

        if (! is_dir($root)) {
            return ['public-directory-missing'];
        }

        $leaks = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->isLink()) {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                $rootNormalized = rtrim(str_replace('\\', '/', $root), '/').'/';
                $relative = str_starts_with($path, $rootNormalized)
                    ? substr($path, strlen($rootNormalized))
                    : $path;

                if ($this->isSensitivePublicFile($relative)) {
                    $leaks[] = $relative;
                }
            }
        } catch (Throwable) {
            return ['public-scan-unavailable'];
        }

        sort($leaks);

        return $leaks;
    }

    private function isSensitivePublicFile(string $relative): bool
    {
        $lower = strtolower(str_replace('\\', '/', $relative));
        $base = strtolower(basename($lower));
        $extension = strtolower(pathinfo($base, PATHINFO_EXTENSION));

        if (in_array($base, [
            '.env',
            '.env.production',
            'database.sqlite',
            'id_rsa',
            'id_ed25519',
        ], true)) {
            return true;
        }

        if (in_array($extension, [
            'sql',
            'sqlite',
            'sqlite3',
            'bak',
            'pem',
            'key',
            'p12',
            'pfx',
            'log',
            'zip',
            '7z',
            'tar',
            'gz',
        ], true)) {
            return true;
        }

        return str_starts_with($lower, 'admissions/')
            || str_starts_with($lower, 'private/')
            || str_starts_with($lower, 'storage/admissions/')
            || str_starts_with($lower, 'storage/private/');
    }

    private function normalizedRealPath(string $path): string
    {
        $real = realpath($path);

        return rtrim(str_replace('\\', '/', $real !== false ? $real : $path), '/');
    }

    private function outsidePublic(string $candidate, string $publicRoot): bool
    {
        if ($candidate === '' || $publicRoot === '') {
            return false;
        }

        return $candidate !== $publicRoot
            && ! str_starts_with($candidate.'/', $publicRoot.'/');
    }

    /**
     * @return array{key:string,label:string,passed:bool,detail:string}
     */
    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }
}
