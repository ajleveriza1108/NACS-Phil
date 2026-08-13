<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$required = [
    'artisan',
    'bootstrap/app.php',
    'bootstrap/providers.php',
    'app/Http/Middleware/EnsureUserIsAdmin.php',
    'app/Http/Controllers/Admin/AuthController.php',
    'app/Http/Controllers/Admin/DashboardController.php',
    'app/Models/Announcement.php',
    'app/Models/SchoolEvent.php',
    'app/Models/GalleryItem.php',
    'app/Models/Inquiry.php',
    'resources/views/admin/auth/login.blade.php',
    'resources/views/admin/dashboard.blade.php',
    'resources/views/home.blade.php',
    'routes/web.php',
    'public/images/nacs-development-mark.svg',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = "Missing required source file: {$relative}";
    }
}

$forbidden = ['.env', 'database/database.sqlite'];
$gitMetadataPath = $root . DIRECTORY_SEPARATOR . '.git';
if (file_exists($gitMetadataPath)) {
    foreach ($forbidden as $relative) {
        $output = [];
        $status = 0;
        $command = 'git -C ' . escapeshellarg($root) . ' ls-files --cached -- ' . escapeshellarg($relative);
        exec($command, $output, $status);

        if ($status !== 0) {
            $errors[] = "Unable to verify Git tracking safety for: {$relative}";
            continue;
        }

        $tracked = array_map(
            static fn (string $line): string => str_replace('\\', '/', trim($line)),
            $output,
        );

        if (in_array($relative, $tracked, true)) {
            $errors[] = "Sensitive local file is tracked by Git: {$relative}";
        }
    }
}

$filamentPaths = [
    $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Filament',
    $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . 'Filament',
];
foreach ($filamentPaths as $path) {
    if (is_dir($path)) {
        $errors[] = "Obsolete Filament source remains: {$path}";
    }
}

$providers = file_get_contents($root . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'providers.php') ?: '';
if (str_contains($providers, 'Filament')) {
    $errors[] = 'bootstrap/providers.php still registers a Filament provider.';
}

$composerJson = file_get_contents($root . DIRECTORY_SEPARATOR . 'composer.json') ?: '';
if (str_contains($composerJson, 'filament/filament')) {
    $errors[] = 'composer.json still requires filament/filament.';
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, "[FAIL] {$error}\n");
    }
    exit(1);
}

echo "[PASS] NACS-Phil native-admin source safety validation passed.\n";
