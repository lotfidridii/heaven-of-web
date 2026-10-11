<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Analytics;
use App\Core\RateLimit;

final class ApiController
{
    /** Analytics beacon (navigator.sendBeacon). Always answers 204. */
    public function hit(): void
    {
        $raw = file_get_contents('php://input', false, null, 0, 2048) ?: '';
        $data = json_decode($raw, true);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
        $sameOrigin = $origin === '' || parse_url($origin, PHP_URL_HOST) === $host;

        if (is_array($data) && $sameOrigin && RateLimit::hit('hit|' . client_ip(), 120, 600)) {
            try {
                Analytics::record([
                    'type' => (string) ($data['t'] ?? 'pageview'),
                    'path' => (string) ($data['p'] ?? '/'),
                    'label' => isset($data['l']) ? (string) $data['l'] : null,
                    'lang' => (string) ($data['lang'] ?? ''),
                    'ref' => (string) ($data['r'] ?? ''),
                    'w' => (int) ($data['w'] ?? 0),
                ]);
            } catch (\Throwable $e) {
                error_log('Analytics: ' . $e->getMessage());
            }
        }
        http_response_code(204);
    }
}
