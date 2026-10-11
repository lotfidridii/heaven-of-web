<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Data available in every template (set by controllers, e.g. current section). */
    public static array $shared = [];

    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        $data = array_merge(self::$shared, $data);
        $content = self::capture(APP_PATH . '/views/' . $template . '.php', $data);
        if ($layout === null) return $content;
        return self::capture(APP_PATH . '/views/layouts/' . $layout . '.php', $data + ['content' => $content]);
    }

    private static function capture(string $__file, array $__data): string
    {
        if (!is_file($__file)) throw new \RuntimeException("View not found: {$__file}");
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            require $__file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
