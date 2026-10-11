<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Key/value site settings. Translatable settings have one row per language;
 * plain settings use lang = ''. Lookups fall back: requested lang → French → ''.
 */
final class Settings
{
    private static ?array $cache = null;

    private static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (DB::all('SELECT skey, lang, value FROM settings') as $row) {
                self::$cache[$row['skey']][$row['lang']] = $row['value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, string $lang = '', string $default = ''): string
    {
        $values = self::all()[$key] ?? [];
        foreach ([$lang, I18n::DEFAULT, ''] as $l) {
            if (isset($values[$l]) && $values[$l] !== '') return $values[$l];
        }
        return $default;
    }

    /** Exact stored value for one language, without fallback (used by admin forms). */
    public static function raw(string $key, string $lang = ''): string
    {
        return self::all()[$key][$lang] ?? '';
    }

    public static function set(string $key, ?string $value, string $lang = ''): void
    {
        DB::run(
            'INSERT INTO settings (skey, lang, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [$key, $lang, $value ?? '']
        );
        self::$cache[$key][$lang] = $value ?? '';
    }
}
