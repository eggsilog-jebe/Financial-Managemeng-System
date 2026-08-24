<?php

declare(strict_types=1);

// Ensure temporary cache directories exist for Vercel's read-only serverless filesystem
if (! is_dir('/tmp/views')) {
    @mkdir('/tmp/views', 0777, true);
}

// Forward Vercel serverless request directly to Laravel's public entrypoint
require __DIR__ . '/../public/index.php';
