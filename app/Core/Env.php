<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env loader (no dependency).
 *
 * Format: one KEY=value per line; blank lines and lines starting with # are ignored;
 * values may be wrapped in "double" or 'single' quotes; an unquoted value ends at " #" (comment).
 * Variables already set in the real environment (e.g. by Docker or the web server) take precedence.
 */
final class Env
{
    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) return;
        foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (str_starts_with($line, 'export ')) $line = trim(substr($line, 7));
            $eq = strpos($line, '=');
            if ($eq === false) continue;
            $key = trim(substr($line, 0, $eq));
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) continue;
            $value = self::parseValue(trim(substr($line, $eq + 1)));
            if (getenv($key) !== false) continue; // real environment wins
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private static function parseValue(string $v): string
    {
        if ($v === '') return '';
        $q = $v[0];
        if (($q === '"' || $q === "'") && ($end = strpos($v, $q, 1)) !== false) {
            $inner = substr($v, 1, $end - 1);
            return $q === '"' ? str_replace(['\\n', '\\"', '\\\\'], ["\n", '"', '\\'], $inner) : $inner;
        }
        $hash = strpos($v, ' #');
        return rtrim($hash === false ? $v : substr($v, 0, $hash));
    }

    /** Typed getter: "true"/"false"/"null"/"" are converted. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $v = getenv($key);
        if ($v === false) return $default;
        return match (strtolower($v)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            default => $v,
        };
    }
}
