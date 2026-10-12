<?php
declare(strict_types=1);

namespace App\Core;

use MaxMind\Db\Reader;

/**
 * IP → country lookup with the free "DB-IP IP to Country Lite" database (CC BY 4.0, https://db-ip.com).
 * The database file lives in storage/geo/ and is refreshed by app/cli/update-geoip.php (monthly).
 * Only the 2-letter country code is ever stored by the analytics; the IP itself is not kept.
 */
final class GeoIp
{
    public const FILE = STORAGE_PATH . '/geo/dbip-country-lite.mmdb';
    private static Reader|false|null $reader = null;

    public static function available(): bool
    {
        return class_exists(Reader::class) && is_file(self::FILE);
    }

    /** ISO 3166-1 alpha-2 code (e.g. "TN"), or null for private/unknown addresses. */
    public static function country(string $ip): ?string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return null;
        if (self::$reader === null) {
            try {
                self::$reader = self::available() ? new Reader(self::FILE) : false;
            } catch (\Throwable $e) {
                error_log('GeoIP: ' . $e->getMessage());
                self::$reader = false;
            }
        }
        if (!self::$reader) return null;
        try {
            $record = self::$reader->get($ip);
        } catch (\Throwable) {
            return null;
        }
        $code = strtoupper((string) ($record['country']['iso_code'] ?? ''));
        return preg_match('/^[A-Z]{2}$/', $code) ? $code : null;
    }

    /** Date of the installed database (file modification time), or null. */
    public static function updatedAt(): ?string
    {
        return is_file(self::FILE) ? date('Y-m-d', (int) filemtime(self::FILE)) : null;
    }
}
