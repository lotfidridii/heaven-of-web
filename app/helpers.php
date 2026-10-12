<?php
declare(strict_types=1);

use App\Core\DB;
use App\Core\I18n;
use App\Core\Settings;
use App\Core\View;

/** Read a config value with dot notation, e.g. config('db.host'). */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['__config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) return $default;
        $value = $value[$part];
    }
    return $value;
}

function is_local(): bool
{
    return config('app.env') === 'local';
}

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function db(): PDO
{
    return DB::pdo();
}

function lang(): string
{
    return I18n::lang();
}

function is_rtl(): bool
{
    return I18n::isRtl();
}

/** Translate an interface string from app/lang/{lang}.php. */
function t(string $key, array $replace = []): string
{
    return I18n::get($key, $replace);
}

/** Site setting (translatable settings fall back to French, then to the untranslated value). */
function setting(string $key, ?string $lang = null, string $default = ''): string
{
    return Settings::get($key, $lang ?? lang(), $default);
}

/** Root-relative URL. */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** URL in the current (or given) language: lurl('realisations/x') → /fr/realisations/x */
function lurl(string $path = '', ?string $lang = null): string
{
    $path = trim($path, '/');
    return '/' . ($lang ?? lang()) . '/' . ($path === '' ? '' : $path);
}

function absolute_url(string $path = ''): string
{
    return config('app.url') . '/' . ltrim($path, '/');
}

/** Public asset URL with a cache-busting version from the file's modification time. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = PUBLIC_PATH . '/assets/' . $path;
    $v = is_file($file) ? '?v=' . base_convert((string) filemtime($file), 10, 36) : '';
    return '/assets/' . $path . $v;
}

/** URL for an uploaded image path stored in the DB (relative to public_html). */
function media(?string $path, ?int $width = null): string
{
    if (!$path) return '';
    if ($width) {
        $variant = preg_replace('/\.webp$/', "-{$width}.webp", $path);
        if ($variant && is_file(PUBLIC_PATH . '/' . $variant)) $path = $variant;
    }
    return '/' . ltrim($path, '/');
}

/** srcset attribute for an uploaded image with generated width variants. */
function srcset(?string $path): string
{
    if (!$path) return '';
    $parts = [];
    foreach (App\Core\Upload::WIDTHS as $w) {
        $variant = preg_replace('/\.webp$/', "-{$w}.webp", $path);
        if ($variant && is_file(PUBLIC_PATH . '/' . $variant)) $parts[] = '/' . $variant . " {$w}w";
    }
    return implode(', ', $parts);
}

function view(string $template, array $data = [], ?string $layout = 'site'): string
{
    return View::render($template, $data, $layout);
}

function partial(string $template, array $data = []): void
{
    echo View::render($template, $data, null);
}

/** Per-request nonce used in the Content-Security-Policy and on inline scripts. */
function csp_nonce(): string
{
    static $nonce = null;
    return $nonce ??= base64_encode(random_bytes(16));
}

function csrf_token(): string
{
    return App\Core\Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function abort(int $code = 404): never
{
    throw new App\Core\HttpException($code);
}

function input(string $key, mixed $default = null): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function wants_json(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

/** Flash messages survive exactly one redirect. */
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['_old'][$key] ?? $default);
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Non-reversible identifier for an IP (we never store raw IPs). */
function ip_hash(?string $ip = null): string
{
    return hash('sha256', config('app.secret') . '|' . ($ip ?? client_ip()));
}

function slugify(string $text): string
{
    $text = transliterator_transliterate_safe($text);
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text) ?? '');
    return trim($text, '-') ?: 'item';
}

function transliterator_transliterate_safe(string $text): string
{
    if (function_exists('transliterator_transliterate')) {
        $r = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        if ($r !== false) return $r;
    }
    $r = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    return $r !== false ? $r : $text;
}

/**
 * Minimal, safe Markdown for admin-edited long texts (legal pages, replies):
 * ## headings, **bold**, *italic*, [links](https://…), "- " lists and paragraphs.
 * Text is escaped first, so no raw HTML ever reaches the page.
 */
function md(string $text): string
{
    $text = str_replace("\r\n", "\n", trim($text));
    $html = [];
    foreach (preg_split('/\n{2,}/', $text) as $block) {
        $block = e($block);
        $inline = static function (string $s): string {
            $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
            $s = preg_replace('/(?<!\*)\*(?!\s)(.+?)\*(?!\*)/s', '<em>$1</em>', $s);
            $s = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^\s)]+|mailto:[^\s)]+|tel:\+?[0-9]+|\/[^\s)]*)\)/', '<a href="$2">$1</a>', $s);
            $s = str_replace('<a href="tel:', '<a dir="ltr" href="tel:', $s); // keep "+216 …" readable in Arabic
            return nl2br($s, false);
        };
        if (preg_match('/^(#{2,4})\s+(.+)$/', $block, $m)) {
            $level = strlen($m[1]);
            $html[] = "<h{$level}>" . $inline($m[2]) . "</h{$level}>";
        } elseif (substr_count($block, "\n") >= 1 && !array_filter(explode("\n", $block), fn($l) => !preg_match('/^(?!- ).{2,60}?\s:\s.+$/u', $l))) {
            // Every line is "Label : value" → a two-column information table.
            $rows = array_map(static function ($l) use ($inline) {
                [$k, $v] = preg_split('/\s:\s/u', $l, 2);
                return '<tr><th scope="row">' . $inline(trim($k)) . '</th><td>' . $inline(trim($v)) . '</td></tr>';
            }, explode("\n", $block));
            $html[] = '<table class="info-table"><tbody>' . implode('', $rows) . '</tbody></table>';
        } elseif (preg_match('/^- /', $block)) {
            $items = array_map(fn($l) => '<li>' . $inline(preg_replace('/^- /', '', $l)) . '</li>', explode("\n", $block));
            $html[] = '<ul>' . implode('', $items) . '</ul>';
        } else {
            $html[] = '<p>' . $inline($block) . '</p>';
        }
    }
    return implode("\n", $html);
}

/** Inline SVG icon from app/views/partials/icons.php (stroke icons, inherit currentColor). */
function icon(string $name, string $class = 'icon'): string
{
    static $icons = null;
    $icons ??= require APP_PATH . '/views/partials/icons.php';
    $body = $icons[$name] ?? $icons['dot'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
}

function format_date(?string $datetime, bool $withTime = false): string
{
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    if (class_exists(IntlDateFormatter::class)) {
        $locale = ['fr' => 'fr_FR', 'en' => 'en_GB', 'ar' => 'ar'][lang()] ?? 'fr_FR';
        $fmt = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, $withTime ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE);
        return (string) $fmt->format($ts);
    }
    return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $ts);
}

/** "il y a 5 min" style relative time for the admin (French). */
function time_ago(?string $datetime): string
{
    if (!$datetime) return '';
    $diff = time() - strtotime($datetime);
    return match (true) {
        $diff < 60 => 'à l’instant',
        $diff < 3600 => 'il y a ' . intdiv($diff, 60) . ' min',
        $diff < 86400 => 'il y a ' . intdiv($diff, 3600) . ' h',
        $diff < 86400 * 7 => 'il y a ' . intdiv($diff, 86400) . ' j',
        default => date('d/m/Y', strtotime($datetime)),
    };
}

function str_excerpt(string $text, int $length = 120): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    return mb_strlen($text) > $length ? rtrim(mb_substr($text, 0, $length - 1)) . '…' : $text;
}

/**
 * Decode a project palette (JSON list of {hex, name}) and add what the display needs:
 * RGB values and a readable text colour (navy or white) for each swatch.
 */
function palette_colors(?string $json): array
{
    $out = [];
    foreach (json_decode((string) $json, true) ?: [] as $c) {
        if (!preg_match('/^#([0-9A-Fa-f]{6})$/', $c['hex'] ?? '', $m)) continue;
        [$r, $g, $b] = array_map('hexdec', str_split($m[1], 2));
        $lin = static fn(int $v) => ($v /= 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        $lum = 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);
        $out[] = [
            'hex' => strtoupper($c['hex']),
            'name' => $c['name'] ?? '',
            'rgb' => "{$r}, {$g}, {$b}",
            'ink' => $lum > 0.36 ? '#041C32' : '#FFFFFF',
        ];
    }
    return $out;
}

/** Non-empty trimmed lines of a multi-line text. */
function text_lines(?string $text): array
{
    return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", (string) $text))), 'strlen'));
}

/** "Titre : description" lines → [['title' => …, 'text' => …], …] (also accepts " — " as separator). */
function parse_steps(?string $text): array
{
    return array_map(static function (string $line) {
        $parts = preg_split('/\s+[:—–]\s+|\s*:\s+/u', $line, 2);
        return ['title' => $parts[0], 'text' => $parts[1] ?? ''];
    }, text_lines($text));
}

/** "a|b" lines → [[a, b], …]; lines without the separator give [line, '']. */
function parse_pairs(?string $text): array
{
    return array_map(static fn(string $l) => array_map('trim', array_pad(explode('|', $l, 2), 2, '')), text_lines($text));
}

/** Escaped text with its last word wrapped in <span class="accent"> (coral highlight in titles). */
function accent_last(string $text): string
{
    $text = trim($text);
    $pos = mb_strrpos($text, ' ');
    if ($pos === false) return '<span class="accent">' . e($text) . '</span>';
    return e(mb_substr($text, 0, $pos)) . ' <span class="accent">' . e(mb_substr($text, $pos + 1)) . '</span>';
}

/** Two-letter monogram: initials for several words, capitals for CamelCase names (WordPress → WP), else first letters. */
function monogram(string $name): string
{
    $words = preg_split('/[\s.\-_\/]+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    if (count($words) > 1) return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
    preg_match_all('/\p{Lu}/u', $name, $caps);
    if (count($caps[0]) >= 2) return implode('', array_slice($caps[0], 0, 2));
    return mb_strtoupper(mb_substr($name, 0, 2));
}

/**
 * Split a long Markdown text into an intro and "## " sections (legal pages).
 * @return array{intro: string, sections: array<int, array{id: string, title: string, html: string}>}
 */
function md_sections(string $text): array
{
    $parts = preg_split('/^##\s+(.+)$/mu', str_replace("\r\n", "\n", $text), -1, PREG_SPLIT_DELIM_CAPTURE);
    $intro = trim((string) array_shift($parts));
    $sections = [];
    for ($i = 0; $i < count($parts); $i += 2) {
        $title = trim($parts[$i]);
        $sections[] = ['id' => 'section-' . ($i / 2 + 1), 'title' => $title, 'html' => md(trim($parts[$i + 1] ?? ''))];
    }
    return ['intro' => $intro !== '' ? md($intro) : '', 'sections' => $sections];
}
