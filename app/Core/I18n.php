<?php
declare(strict_types=1);

namespace App\Core;

final class I18n
{
    public const LANGS = ['fr', 'en', 'ar'];
    public const DEFAULT = 'fr';
    public const NAMES = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];

    private static string $lang = self::DEFAULT;
    private static array $strings = [];

    public static function set(string $lang): void
    {
        self::$lang = in_array($lang, self::LANGS, true) ? $lang : self::DEFAULT;
    }

    public static function lang(): string
    {
        return self::$lang;
    }

    public static function isRtl(?string $lang = null): bool
    {
        return ($lang ?? self::$lang) === 'ar';
    }

    public static function get(string $key, array $replace = [], ?string $lang = null): string
    {
        $lang ??= self::$lang;
        $value = self::load($lang)[$key] ?? self::load(self::DEFAULT)[$key] ?? $key;
        foreach ($replace as $k => $v) $value = str_replace('{' . $k . '}', (string) $v, $value);
        return $value;
    }

    private static function load(string $lang): array
    {
        return self::$strings[$lang] ??= (require APP_PATH . "/lang/{$lang}.php");
    }

    /** Best supported language from the Accept-Language header (for the bare "/" URL). */
    public static function detect(): string
    {
        $best = self::DEFAULT;
        $bestQ = 0.0;
        foreach (explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')) as $part) {
            [$tag, $q] = array_pad(explode(';q=', trim($part)), 2, '1');
            $code = substr($tag, 0, 2);
            if (in_array($code, self::LANGS, true) && (float) $q > $bestQ) {
                $best = $code;
                $bestQ = (float) $q;
            }
        }
        return $best;
    }
}
