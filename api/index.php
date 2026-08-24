<?php

declare(strict_types=1);

// 1. Ensure required /tmp directories exist for Vercel's read-only serverless filesystem
$tmpDirs = ['/tmp/views', '/tmp/storage/framework/views', '/tmp/storage/framework/sessions', '/tmp/storage/framework/cache', '/tmp/database'];
foreach ($tmpDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// 2. Forward request directly to Laravel public entrypoint
require __DIR__ . '/../public/index.php';
