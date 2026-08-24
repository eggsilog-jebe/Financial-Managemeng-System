<?php

declare(strict_types=1);

// 1. Ensure required /tmp directories exist for Vercel's read-only serverless filesystem
$tmpDirs = ['/tmp/views', '/tmp/storage/framework/views', '/tmp/storage/framework/sessions', '/tmp/storage/framework/cache', '/tmp/database'];
foreach ($tmpDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// 2. Automatically initialize and seed /tmp/database.sqlite on serverless cold starts if not using remote MySQL
$sqlitePath = '/tmp/database/database.sqlite';
if (! file_exists($sqlitePath) && env('DB_CONNECTION') !== 'mysql' && env('DB_CONNECTION') !== 'pgsql') {
    touch($sqlitePath);
    putenv("DB_CONNECTION=sqlite");
    putenv("DB_DATABASE={$sqlitePath}");
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_ENV['DB_DATABASE'] = $sqlitePath;

    // Bootstrap Laravel Console Kernel to migrate and seed demo data automatically
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->call('migrate:fresh', ['--force' => true]);
    $kernel->call('db:seed', ['--class' => 'PhilippineHealthcareChartOfAccountsSeeder', '--force' => true]);
    $kernel->call('db:seed', ['--class' => 'FinancialRolesUserSeeder', '--force' => true]);
    $kernel->call('db:seed', ['--class' => 'HospitalFinancialMockSeeder', '--force' => true]);
}

// 3. Forward request to Laravel public entrypoint
require __DIR__ . '/../public/index.php';
