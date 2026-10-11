<?php
/**
 * One-time helper: turns the images downloaded from the previous site
 * (public_html/uploads/seed/*-orig.webp) into optimised WebP files + srcset variants.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Upload;

$dir = PUBLIC_PATH . '/uploads/seed';
$map = [
    'logo-orig.webp' => ['logo', 512, false],
    'about-img-orig.webp' => ['about', 1200, true],
    'hero-bg-orig.webp' => ['hero', 1920, true],
];
for ($i = 1; $i <= 8; $i++) $map["portfolio-{$i}-orig.webp"] = ["portfolio-{$i}", 1440, true];

foreach ($map as $src => [$name, $max, $variants]) {
    $path = "{$dir}/{$src}";
    if (!is_file($path)) continue;
    $img = imagecreatefromwebp($path);
    $tmp = Upload::store($img, 'seed-tmp', $max, $variants);
    $base = PUBLIC_PATH . '/' . preg_replace('/\.webp$/', '', $tmp);
    rename("{$base}.webp", "{$dir}/{$name}.webp");
    foreach (Upload::WIDTHS as $w) {
        if (is_file("{$base}-{$w}.webp")) rename("{$base}-{$w}.webp", "{$dir}/{$name}-{$w}.webp");
    }
    unlink($path);
    printf("%-12s %6d KB\n", $name, filesize("{$dir}/{$name}.webp") / 1024);
}
@rmdir(PUBLIC_PATH . '/uploads/seed-tmp');

// Open Graph image (1200×630) built from the logo on the brand navy.
if (is_file("{$dir}/logo.webp") && !is_file("{$dir}/og.webp")) {
    $og = imagecreatetruecolor(1200, 630);
    imagefill($og, 0, 0, imagecolorallocate($og, 0x04, 0x1C, 0x32));
    $logo = imagecreatefromwebp("{$dir}/logo.webp");
    $lw = imagesx($logo);
    $lh = imagesy($logo);
    $h = 420;
    $w = (int) round($lw * $h / $lh);
    imagealphablending($og, true);
    imagecopyresampled($og, $logo, (1200 - $w) >> 1, (630 - $h) >> 1, 0, 0, $w, $h, $lw, $lh);
    imagewebp($og, "{$dir}/og.webp", 85);
    imagepng($og, "{$dir}/og.png", 8);
    echo "og image created\n";
}
