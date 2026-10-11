<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Analytics;
use App\Core\DB;
use App\Core\Push;

final class PushController extends BaseController
{
    public function index(): void
    {
        $this->render('notifications', [
            'pageTitle' => 'Notifications',
            'available' => Push::available(),
            'publicKey' => Push::available() ? Push::keys()['publicKey'] : '',
            'devices' => DB::all('SELECT id, device, created_at, last_used_at FROM push_subscriptions WHERE admin_id = ? ORDER BY created_at DESC', [$this->user['id']]),
        ]);
    }

    /** Store this browser's push subscription (JSON from PushManager.subscribe()). */
    public function subscribe(): void
    {
        $sub = json_decode(file_get_contents('php://input') ?: '', true);
        $endpoint = (string) ($sub['endpoint'] ?? '');
        $p256dh = (string) ($sub['keys']['p256dh'] ?? '');
        $auth = (string) ($sub['keys']['auth'] ?? '');
        if (!filter_var($endpoint, FILTER_VALIDATE_URL) || !str_starts_with($endpoint, 'https://') || $p256dh === '' || $auth === '') {
            json_response(['ok' => false, 'error' => 'Abonnement invalide.'], 422);
        }
        [$browser, $os, $device] = Analytics::parseUa($_SERVER['HTTP_USER_AGENT'] ?? '');
        DB::run(
            'INSERT INTO push_subscriptions (admin_id, endpoint, endpoint_hash, p256dh, auth, device) VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE admin_id = VALUES(admin_id), p256dh = VALUES(p256dh), auth = VALUES(auth), device = VALUES(device)',
            [$this->user['id'], $endpoint, hash('sha256', $endpoint), $p256dh, $auth, "{$browser} · {$os} · {$device}"]
        );
        json_response(['ok' => true]);
    }

    public function unsubscribe(): void
    {
        $endpoint = (string) (json_decode(file_get_contents('php://input') ?: '', true)['endpoint'] ?? '');
        DB::run('DELETE FROM push_subscriptions WHERE endpoint_hash = ? AND admin_id = ?', [hash('sha256', $endpoint), $this->user['id']]);
        json_response(['ok' => true]);
    }

    public function test(): void
    {
        [$sent, $failed] = Push::send([
            'title' => 'Notifications activées',
            'body' => 'Vous serez prévenu ici à chaque nouveau message reçu sur le site.',
            'url' => '/admin/notifications',
            'tag' => 'test',
        ], (int) $this->user['id']);
        if (wants_json()) json_response(['ok' => $sent > 0, 'sent' => $sent, 'failed' => $failed]);
        $this->back('/admin/notifications', $sent ? "Notification de test envoyée à {$sent} appareil(s)." : 'Aucun appareil n’a reçu la notification.', $sent ? 'success' : 'error');
    }

    public function delete(string $id): void
    {
        DB::run('DELETE FROM push_subscriptions WHERE id = ? AND admin_id = ?', [(int) $id, $this->user['id']]);
        $this->back('/admin/notifications', 'Appareil retiré.');
    }
}
