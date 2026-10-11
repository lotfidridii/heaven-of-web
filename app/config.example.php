<?php
/**
 * Configuration. On Hostinger: copy this file to app/config.php and fill in the values
 * (hPanel → Databases for DB credentials, hPanel → Emails for the SMTP mailbox).
 * In Docker the values come from the environment variables in docker-compose.yml.
 */
$env = static fn(string $key, $default = null) => ($v = getenv($key)) !== false ? $v : $default;

return [
    'app' => [
        'env'       => $env('APP_ENV', 'production'),        // 'local' shows detailed errors
        'url'       => rtrim($env('APP_URL', 'https://heavenofweb.fr'), '/'),
        'name'      => 'Heaven of Web',
        // Long random string; used to salt visitor hashes and tokens. Generate with: php -r "echo bin2hex(random_bytes(32));"
        'secret'    => $env('APP_SECRET', 'CHANGE-ME'),
        // Needed once, to create the first admin account at /admin/setup.
        'setup_key' => $env('SETUP_KEY', 'CHANGE-ME'),
        'timezone'  => 'Europe/Paris',
    ],
    'db' => [
        'host'    => $env('DB_HOST', 'localhost'),
        'name'    => $env('DB_NAME', ''),
        'user'    => $env('DB_USER', ''),
        'pass'    => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        // Hostinger: smtp.hostinger.com, port 465, secure 'ssl', user = full mailbox address
        'host'      => $env('MAIL_HOST', 'smtp.hostinger.com'),
        'port'      => (int) $env('MAIL_PORT', 465),
        'secure'    => $env('MAIL_SECURE', 'ssl'),            // 'ssl', 'tls' or '' (none)
        'user'      => $env('MAIL_USER', 'contact@heavenofweb.fr'),
        'pass'      => $env('MAIL_PASS', ''),
        'from'      => $env('MAIL_FROM', 'contact@heavenofweb.fr'),
        'from_name' => 'Heaven of Web',
    ],
];
