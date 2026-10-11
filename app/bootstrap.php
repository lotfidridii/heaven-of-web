<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public_html');
define('STORAGE_PATH', BASE_PATH . '/storage');

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        if (str_starts_with($class, 'App\\')) {
            $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (is_file($file)) require $file;
        }
    });
}

$GLOBALS['__config'] = require (is_file(APP_PATH . '/config.php') ? APP_PATH . '/config.php' : APP_PATH . '/config.example.php');
require APP_PATH . '/helpers.php';

date_default_timezone_set(config('app.timezone', 'Europe/Paris'));
mb_internal_encoding('UTF-8');

$isLocal = config('app.env') === 'local';
error_reporting(E_ALL);
ini_set('display_errors', $isLocal ? '1' : '0');
ini_set('log_errors', '1');
if (is_dir(STORAGE_PATH) && is_writable(STORAGE_PATH)) {
    ini_set('error_log', STORAGE_PATH . '/error.log');
}
