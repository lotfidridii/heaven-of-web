<?php
/**
 * Generates the PWA / favicon icons and the social sharing image from the brand logos
 * (public_html/assets/img/brand/, produced by app/cli/process-logo.php).
 * Usage: php app/cli/make-icons.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

$brand = PUBLIC_PATH . '/assets/img/brand';
$out = PUBLIC_PATH . '/assets/img/icons';
if (!is_dir($out)) mkdir($out, 0755, true);

// Light version of the mark (wings + globe), transparent background: icons sit on navy.
$mark = imagecreatefrompng("{$brand}/mark-light.png");
imagesavealpha($mark, true);

/** Square icon: navy background, logo mark centred with $padding (0..0.5) margin. */
function icon_png(GdImage $mark, int $size, float $padding, string $file, bool $rounded = false): void
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    imagealphablending($img, false);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);
    $navy = imagecolorallocate($img, 0x04, 0x1C, 0x32);
    if ($rounded) {
        $r = (int) ($size * 0.22);
        imagefilledrectangle($img, $r, 0, $size - $r - 1, $size - 1, $navy);
        imagefilledrectangle($img, 0, $r, $size - 1, $size - $r - 1, $navy);
        foreach ([[$r, $r], [$size - $r - 1, $r], [$r, $size - $r - 1], [$size - $r - 1, $size - $r - 1]] as [$cx, $cy]) {
            imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $navy);
        }
    } else {
        imagefilledrectangle($img, 0, 0, $size - 1, $size - 1, $navy);
    }
    $box = (int) round($size * (1 - 2 * $padding));
    $mw = imagesx($mark);
    $mh = imagesy($mark);
    $scale = min($box / $mw, $box / $mh);
    $w = (int) round($mw * $scale);
    $h = (int) round($mh * $scale);
    imagecopyresampled($img, $mark, (int) (($size - $w) / 2), (int) (($size - $h) / 2), 0, 0, $w, $h, $mw, $mh);
    imagepng($img, $file, 9);
    echo basename($file) . "\n";
}

icon_png($mark, 192, 0.12, "{$out}/icon-192.png", true);
icon_png($mark, 512, 0.12, "{$out}/icon-512.png", true);
icon_png($mark, 512, 0.22, "{$out}/maskable-512.png");   // safe zone for Android adaptive icons
icon_png($mark, 180, 0.14, "{$out}/apple-touch-icon.png");
icon_png($mark, 32, 0.06, "{$out}/favicon-32.png", true);

// Monochrome badge for Android notifications: white silhouette from the logo's alpha channel.
$size = 96;
$badge = imagecreatetruecolor($size, $size);
imagesavealpha($badge, true);
imagealphablending($badge, false);
imagefill($badge, 0, 0, imagecolorallocatealpha($badge, 0, 0, 0, 127));
$tmp = imagecreatetruecolor($size, $size);
imagesavealpha($tmp, true);
imagealphablending($tmp, false);
imagefill($tmp, 0, 0, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
$mw = imagesx($mark);
$mh = imagesy($mark);
$scale = min(($size - 8) / $mw, ($size - 8) / $mh);
$w = (int) ($mw * $scale);
$h = (int) ($mh * $scale);
imagecopyresampled($tmp, $mark, (int) (($size - $w) / 2), (int) (($size - $h) / 2), 0, 0, $w, $h, $mw, $mh);
for ($x = 0; $x < $size; $x++) {
    for ($y = 0; $y < $size; $y++) {
        $a = (imagecolorat($tmp, $x, $y) >> 24) & 0x7F;
        imagesetpixel($badge, $x, $y, imagecolorallocatealpha($badge, 255, 255, 255, $a));
    }
}
imagepng($badge, "{$out}/badge-72.png", 9);
echo "badge-72.png\n";

// favicon.ico (PNG-in-ICO, 32×32)
$png = file_get_contents("{$out}/favicon-32.png");
$ico = pack('vvv', 0, 1, 1) . pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($png), 22) . $png;
file_put_contents(PUBLIC_PATH . '/favicon.ico', $ico);
echo "favicon.ico\n";

// Social sharing image (1200×630): full light logo on the brand navy.
$og = imagecreatetruecolor(1200, 630);
imagefill($og, 0, 0, imagecolorallocate($og, 0x04, 0x1C, 0x32));
imagealphablending($og, true);
$logo = imagecreatefrompng("{$brand}/logo-light.png");
$lw = imagesx($logo);
$lh = imagesy($logo);
$h = 430;
$w = (int) round($lw * $h / $lh);
imagecopyresampled($og, $logo, (1200 - $w) >> 1, (630 - $h) >> 1, 0, 0, $w, $h, $lw, $lh);
imagepng($og, "{$brand}/og.png", 8);
echo "og.png
";
