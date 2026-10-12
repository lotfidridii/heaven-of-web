<?php
/**
 * Application configuration. Values come from environment variables, normally set in the
 * .env file at the project root (copy .env.example to .env and fill it in).
 * No secrets in this file: it is committed to git.
 */
use App\Core\Env;

return [
    'app' => [
        'env'       => Env::get('APP_ENV', 'production'),        // "local" shows detailed errors
        'url'       => rtrim((string) Env::get('APP_URL', 'https://heavenofweb.fr'), '/'),
        'name'      => Env::get('APP_NAME', 'Heaven of Web'),
        'secret'    => Env::get('APP_SECRET', 'CHANGE-ME'),        // never change once MFA is in use
        'setup_key' => Env::get('SETUP_KEY', 'CHANGE-ME'),         // creates the first admin at /admin/setup
        'timezone'  => Env::get('APP_TIMEZONE', 'Africa/Tunis'),
    ],
    'db' => [
        'host'    => Env::get('DB_HOST', 'localhost'),
        'port'    => (int) Env::get('DB_PORT', 3306),
        'name'    => Env::get('DB_NAME', ''),
        'user'    => Env::get('DB_USER', ''),
        'pass'    => (string) Env::get('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'host'      => Env::get('MAIL_HOST', 'smtp.hostinger.com'),
        'port'      => (int) Env::get('MAIL_PORT', 465),
        'secure'    => (string) Env::get('MAIL_SECURE', 'ssl'),    // "ssl", "tls" or empty
        'user'      => (string) Env::get('MAIL_USER', ''),
        'pass'      => (string) Env::get('MAIL_PASS', ''),
        'from'      => Env::get('MAIL_FROM', 'contact@heavenofweb.fr'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'Heaven of Web'),
    ],
];
