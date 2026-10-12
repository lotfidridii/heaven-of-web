<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        Session::start();
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }

    /** Accepts the token from the _csrf form field or the X-CSRF-Token header. */
    public static function check(): bool
    {
        Session::start();
        $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($sent) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $sent);
    }

    public static function verify(): void
    {
        if (!self::check()) {
            if (wants_json()) json_response(['ok' => false, 'error' => 'csrf'], 403);
            throw new HttpException(419);
        }
    }
}
