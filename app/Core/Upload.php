<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Image uploads. Every image is decoded and re-encoded to WebP with GD, which strips
 * metadata and guarantees that what lands on disk is really an image (no disguised scripts).
 * Width variants are generated for srcset.
 */
final class Upload
{
    public const WIDTHS = [480, 960, 1440];
    private const MAX_BYTES = 10 * 1024 * 1024;
    private const MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/x-ms-bmp'];

    /** Accepted formats; AVIF only when this server's GD can decode it. Everything is stored as WebP. */
    private static function accepted(): array
    {
        $avif = function_exists('imagecreatefromavif') && !empty(gd_info()['AVIF Support']);
        return $avif ? [...self::MIME, 'image/avif'] : self::MIME;
    }

    /**
     * @param array $file one entry of $_FILES
     * @return string path relative to public_html, e.g. "uploads/projects/3f9a1c.webp"
     * @throws \RuntimeException with a user-facing French message
     */
    public static function image(array $file, string $dir, int $maxWidth = 1920, bool $variants = true): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(match ($file['error'] ?? 0) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image trop lourde (10 Mo maximum).',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier reçu.',
                default => 'Le téléversement a échoué, réessayez.',
            });
        }
        if ($file['size'] > self::MAX_BYTES) throw new \RuntimeException('Image trop lourde (10 Mo maximum).');
        if (!is_uploaded_file($file['tmp_name']) && PHP_SAPI !== 'cli') throw new \RuntimeException('Fichier invalide.');

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::accepted(), true)) {
            throw new \RuntimeException('Format non accepté : utilisez JPG, PNG, WebP, GIF ou BMP (converti automatiquement en WebP).');
        }
        $img = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
        if (!$img) throw new \RuntimeException('Image illisible ou corrompue.');

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            $img = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => imagerotate($img, 180, 0),
                6 => imagerotate($img, -90, 0),
                8 => imagerotate($img, 90, 0),
                default => $img,
            };
        }
        return self::store($img, $dir, $maxWidth, $variants);
    }

    /** Re-encode a GD image to WebP (+ width variants) under public_html/uploads/{dir}. */
    public static function store(\GdImage $img, string $dir, int $maxWidth = 1920, bool $variants = true): string
    {
        $dir = trim(preg_replace('/[^a-z0-9_-]/', '', strtolower($dir)) ?: 'misc', '/');
        $absDir = PUBLIC_PATH . '/uploads/' . $dir;
        if (!is_dir($absDir) && !mkdir($absDir, 0755, true)) throw new \RuntimeException('Dossier d’upload non accessible en écriture.');

        $name = bin2hex(random_bytes(8));
        $width = imagesx($img);
        self::saveWebp($img, "{$absDir}/{$name}.webp", min($width, $maxWidth));
        if ($variants) {
            foreach (self::WIDTHS as $w) {
                if ($w < $width && $w < $maxWidth) self::saveWebp($img, "{$absDir}/{$name}-{$w}.webp", $w);
            }
        }
        imagedestroy($img);
        return "uploads/{$dir}/{$name}.webp";
    }

    private static function saveWebp(\GdImage $src, string $path, int $width): void
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $out = $src;
        if ($width < $w) {
            $height = (int) round($h * $width / $w);
            $out = imagecreatetruecolor($width, $height);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
            imagecopyresampled($out, $src, 0, 0, 0, 0, $width, $height, $w, $h);
        } else {
            imagepalettetotruecolor($out);
            imagealphablending($out, false);
            imagesavealpha($out, true);
        }
        if (!imagewebp($out, $path, 82)) throw new \RuntimeException('Impossible d’enregistrer l’image.');
        if ($out !== $src) imagedestroy($out);
    }

    /** Delete an uploaded image and its variants. Only touches files under public_html/uploads. */
    public static function delete(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'uploads/') || str_contains($path, '..')) return;
        $base = PUBLIC_PATH . '/' . preg_replace('/\.(webp|png)$/', '', $path);
        // .png only exists for the social sharing image (kept next to its WebP version).
        foreach ([$base . '.webp', $base . '.png', ...array_map(fn($w) => "{$base}-{$w}.webp", self::WIDTHS)] as $f) {
            if (is_file($f)) @unlink($f);
        }
    }

    /** Normalise the $_FILES structure of a multiple file input into a list. */
    public static function list(string $field): array
    {
        $f = $_FILES[$field] ?? null;
        if (!$f || !is_array($f['name'])) return $f && $f['error'] !== UPLOAD_ERR_NO_FILE ? [$f] : [];
        $out = [];
        foreach ($f['name'] as $i => $_) {
            if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $out[] = ['name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
        }
        return $out;
    }
}
