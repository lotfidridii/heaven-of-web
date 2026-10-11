<?php
declare(strict_types=1);

namespace App\Core;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

/**
 * Web Push notifications to the admins' installed PWA / browsers.
 * VAPID keys are generated on first use and stored in the settings table.
 */
final class Push
{
    public static function available(): bool
    {
        return class_exists(WebPush::class);
    }

    /** @return array{publicKey:string, privateKey:string} */
    public static function keys(): array
    {
        $public = Settings::get('vapid_public');
        $private = Settings::get('vapid_private');
        if ($public === '' || $private === '') {
            $keys = VAPID::createVapidKeys();
            Settings::set('vapid_public', $keys['publicKey']);
            Settings::set('vapid_private', $keys['privateKey']);
            return $keys;
        }
        return ['publicKey' => $public, 'privateKey' => $private];
    }

    /**
     * Send a notification to every subscribed device (optionally only one admin's).
     * Expired subscriptions are removed. Returns [sent, failed].
     *
     * @param array{title:string, body:string, url?:string, tag?:string} $payload
     */
    public static function send(array $payload, ?int $adminId = null): array
    {
        if (!self::available()) return [0, 0];
        $subs = $adminId
            ? DB::all('SELECT * FROM push_subscriptions WHERE admin_id = ?', [$adminId])
            : DB::all('SELECT * FROM push_subscriptions');
        if (!$subs) return [0, 0];

        $keys = self::keys();
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'mailto:' . config('mail.from'),
                'publicKey' => $keys['publicKey'],
                'privateKey' => $keys['privateKey'],
            ],
        ], ['TTL' => 86400, 'urgency' => 'high'], 10);

        $json = json_encode($payload + ['icon' => '/assets/img/icons/icon-192.png', 'badge' => '/assets/img/icons/badge-72.png'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach ($subs as $s) {
            $webPush->queueNotification(Subscription::create([
                'endpoint' => $s['endpoint'],
                'keys' => ['p256dh' => $s['p256dh'], 'auth' => $s['auth']],
            ]), $json);
        }

        $sent = $failed = 0;
        try {
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getEndpoint();
                if ($report->isSuccess()) {
                    $sent++;
                    DB::run('UPDATE push_subscriptions SET last_used_at = NOW() WHERE endpoint_hash = ?', [hash('sha256', $endpoint)]);
                } else {
                    $failed++;
                    error_log('Push failed: ' . $report->getReason());
                    if ($report->isSubscriptionExpired()) {
                        DB::run('DELETE FROM push_subscriptions WHERE endpoint_hash = ?', [hash('sha256', $endpoint)]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('Push error: ' . $e->getMessage());
        }
        return [$sent, $failed];
    }
}
