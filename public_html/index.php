<?php
declare(strict_types=1);

// app/ normally sits next to public_html (recommended on Hostinger). If it had to be
// uploaded inside public_html, the second path is used; app/.htaccess then denies web access.
require is_file(__DIR__ . '/../app/bootstrap.php')
    ? __DIR__ . '/../app/bootstrap.php'
    : __DIR__ . '/app/bootstrap.php';

App\Core\App::run();
