<?php

namespace App\Support;

final class HostCapabilityReport
{
    private const REQUIRED_EXTENSIONS = [
        'ctype',
        'fileinfo',
        'filter',
        'mbstring',
        'openssl',
        'pdo',
        'session',
        'tokenizer',
    ];

    /**
     * @return array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}>
     */
    public function checks(): array
    {
        $checks = [];

        $checks[] = $this->check(
            'php_version',
            'PHP 8.3 or newer',
            version_compare(PHP_VERSION, '8.3.0', '>='),
            true,
            'Detected PHP '.PHP_VERSION.'. NACS-Phil composer.json requires PHP ^8.3.'
        );

        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            $checks[] = $this->check(
                'extension_'.$extension,
                'PHP extension: '.$extension,
                extension_loaded($extension),
                true,
                extension_loaded($extension)
                    ? 'Extension is loaded.'
                    : 'Enable the '.$extension.' extension for the PHP runtime serving Laravel.'
            );
        }

        $database = (string) config('database.default');
        $pdoDrivers = class_exists(\PDO::class) ? \PDO::getAvailableDrivers() : [];
        $requiredDriver = $this->pdoDriverForConnection($database);

        if ($requiredDriver !== null) {
            $checks[] = $this->check(
                'pdo_driver',
                'PDO driver for '.$database,
                in_array($requiredDriver, $pdoDrivers, true),
                true,
                'Required PDO driver: '.$requiredDriver.'. Available: '.($pdoDrivers === [] ? 'none' : implode(', ', $pdoDrivers)).'.'
            );
        } else {
            $checks[] = $this->check(
                'pdo_driver',
                'PDO database driver',
                $pdoDrivers !== [],
                true,
                'Configured database connection is '.$database.'. Available PDO drivers: '.($pdoDrivers === [] ? 'none' : implode(', ', $pdoDrivers)).'.'
            );
        }

        $uploadBytes = $this->iniBytes((string) ini_get('upload_max_filesize'));
        $postBytes = $this->iniBytes((string) ini_get('post_max_size'));
        $requiredUploadBytes = 5 * 1024 * 1024;

        $checks[] = $this->check(
            'upload_limit',
            'PHP upload limit supports 5 MB admissions documents',
            $uploadBytes === -1 || $uploadBytes >= $requiredUploadBytes,
            true,
            'upload_max_filesize='.((string) ini_get('upload_max_filesize') ?: 'unknown').'; NACS-Phil accepts requested admissions files up to 5 MB.'
        );

        $checks[] = $this->check(
            'post_limit',
            'PHP POST limit supports admissions uploads',
            $postBytes === -1 || $postBytes >= ($requiredUploadBytes + 1024 * 1024),
            true,
            'post_max_size='.((string) ini_get('post_max_size') ?: 'unknown').'; at least 6 MB is recommended for a 5 MB file plus form overhead.'
        );

        $memoryBytes = $this->iniBytes((string) ini_get('memory_limit'));
        $checks[] = $this->check(
            'memory_limit',
            'PHP memory limit',
            $memoryBytes === -1 || $memoryBytes >= 128 * 1024 * 1024,
            false,
            'memory_limit='.((string) ini_get('memory_limit') ?: 'unknown').'; 128 MB or more is recommended.'
        );

        foreach ([
            'storage' => storage_path(),
            'bootstrap_cache' => base_path('bootstrap/cache'),
            'private_storage' => storage_path('app/private'),
            'public_storage' => storage_path('app/public'),
        ] as $key => $path) {
            $checks[] = $this->check(
                'writable_'.$key,
                'Writable '.str_replace('_', ' ', $key),
                is_dir($path) && is_writable($path),
                true,
                $path
            );
        }

        $checks[] = $this->check(
            'public_index',
            'Laravel public entry point exists',
            is_file(public_path('index.php')),
            true,
            public_path('index.php')
        );

        $checks[] = $this->check(
            'build_manifest',
            'Production frontend build exists',
            is_file(public_path('build/manifest.json')),
            true,
            public_path('build/manifest.json')
        );

        $publicStorage = public_path('storage');
        $checks[] = $this->check(
            'public_storage_link',
            'Public storage link or host equivalent',
            is_link($publicStorage) || is_dir($publicStorage),
            false,
            'Expected public media path: '.$publicStorage.'. Run php artisan storage:link when supported.'
        );

        $checks[] = $this->check(
            'shell_exec',
            'Deployment shell access',
            function_exists('proc_open') || function_exists('shell_exec'),
            false,
            'Advisory only. If the host blocks shell functions, deploy Composer/build artifacts from a trusted build machine and use the host control panel for deployment tasks.'
        );

        return $checks;
    }

    /**
     * @param array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}> $checks
     * @return array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}>
     */
    public function requiredFailures(array $checks): array
    {
        return array_values(array_filter(
            $checks,
            fn (array $check): bool => $check['required'] && ! $check['passed']
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $checks = $this->checks();
        $failures = $this->requiredFailures($checks);

        return [
            'application' => 'NACS-Phil',
            'php_version' => PHP_VERSION,
            'database_connection' => (string) config('database.default'),
            'required_failures' => count($failures),
            'checks' => $checks,
        ];
    }

    private function pdoDriverForConnection(string $connection): ?string
    {
        return match ($connection) {
            'mysql', 'mariadb' => 'mysql',
            'pgsql' => 'pgsql',
            'sqlite' => 'sqlite',
            'sqlsrv' => 'sqlsrv',
            default => null,
        };
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) substr($value, 0, -1);

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $value,
        };
    }

    /**
     * @return array{key:string,label:string,passed:bool,required:bool,detail:string}
     */
    private function check(
        string $key,
        string $label,
        bool $passed,
        bool $required,
        string $detail
    ): array {
        return compact('key', 'label', 'passed', 'required', 'detail');
    }
}
