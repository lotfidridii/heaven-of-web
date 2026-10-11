<?php
declare(strict_types=1);

namespace App\Core;

final class App
{
    public static function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

        self::securityHeaders();
        try {
            $router = new Router();
            (require APP_PATH . '/routes.php')($router);
            $router->dispatch($method, $path);
        } catch (HttpException $e) {
            self::error($e->status, $path);
        } catch (\Throwable $e) {
            error_log((string) $e);
            if (is_local()) {
                http_response_code(500);
                header('Content-Type: text/plain; charset=utf-8');
                echo $e;
                return;
            }
            self::error(500, $path);
        }
    }

    private static function securityHeaders(): void
    {
        $nonce = csp_nonce();
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'; connect-src 'self'; worker-src 'self'; manifest-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'; upgrade-insecure-requests");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
        if (!is_local()) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    private static function error(int $status, string $path): void
    {
        http_response_code($status);
        if (wants_json()) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => $status]);
            return;
        }
        if (str_starts_with($path, '/admin')) {
            echo View::render('admin/error', ['status' => $status], null);
            return;
        }
        if (preg_match('#^/(fr|en|ar)(/|$)#', $path, $m)) I18n::set($m[1]);
        try {
            echo view('site/error', ['status' => $status, 'pageTitle' => t('error.' . ($status === 404 ? '404' : 'generic') . '.title')]);
        } catch (\Throwable $e) {
            error_log((string) $e);
            echo $status === 404 ? 'Page introuvable' : 'Erreur serveur';
        }
    }
}
