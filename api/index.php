<?php

// Forward Vercel serverless requests to Laravel's public entrypoint
$viewPath = '/tmp/views';
if (!is_dir($viewPath)) {
    @mkdir($viewPath, 0755, true);
}
putenv('VIEW_COMPILED_PATH=' . $viewPath);
$_ENV['VIEW_COMPILED_PATH'] = $viewPath;

require __DIR__ . '/../public/index.php';
