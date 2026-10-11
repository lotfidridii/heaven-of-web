<?php
/**
 * Turns the two flat logo images (white background) into transparent, resolution-independent brand assets.
 *
 * Usage (inside the Docker container, needs potrace + rsvg-convert):
 *   php -d memory_limit=2G app/cli/process-logo.php tools/brand-source/logo-original-dark.webp tools/brand-source/logo-original-light.webp
 *
 * How it works
 * 1. Both images share the same shapes, so one alpha mask is computed from the high-contrast (dark) version:
 *    alpha = ink strength of a pixel / ink strength of the stroke it belongs to (local maximum), which keeps
 *    anti-aliased edges soft without a white halo.
 * 2. The mask is upscaled ×2 and traced to vector paths with potrace → edges are sharp at any size.
 * 3. Each logo's colours are "un-premultiplied" from the white background and spread a few pixels outward,
 *    then clipped by the vector paths. The colours are smooth gradients, so they scale cleanly.
 * 4. Outputs (public_html/assets/img/brand/): logo-{dark,light}.svg/.png/.webp and mark-{dark,light}.* (icon only).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

[$_, $darkPath, $lightPath] = array_pad($argv, 3, null);
if (!$darkPath || !$lightPath) exit("Usage: php app/cli/process-logo.php dark.webp light.webp\n");

$load = static fn(string $p) => match (strtolower(pathinfo($p, PATHINFO_EXTENSION))) {
    'webp' => imagecreatefromwebp($p),
    'png' => imagecreatefrompng($p),
    default => imagecreatefromjpeg($p),
};
$dark = $load($darkPath);
$light = $load($lightPath);
imagepalettetotruecolor($dark);
imagepalettetotruecolor($light);
$W = imagesx($dark);
$H = imagesy($dark);
if (imagesx($light) !== $W || imagesy($light) !== $H) exit("Both images must have the same size.\n");

$tmp = STORAGE_PATH . '/logo-work';
$out = PUBLIC_PATH . '/assets/img/brand';
@mkdir($tmp, 0775, true);
@mkdir($out, 0775, true);
$t0 = microtime(true);
$log = static fn(string $m) => printf("[%5.1fs] %s\n", microtime(true) - $t0, $m);

/* ---------- 1. Alpha mask from the dark logo ---------- */
$ink = new SplFixedArray($W * $H);
for ($y = 0; $y < $H; $y++) {
    for ($x = 0; $x < $W; $x++) {
        $c = imagecolorat($dark, $x, $y);
        $ink[$y * $W + $x] = 255 - min(($c >> 16) & 255, ($c >> 8) & 255, $c & 255);
    }
}
// Local maximum (separable, radius R) = the ink strength of the nearest solid stroke.
$R = 7;
$tmpMax = new SplFixedArray($W * $H);
for ($y = 0; $y < $H; $y++) {
    $row = $y * $W;
    for ($x = 0; $x < $W; $x++) {
        $m = 0;
        for ($k = max(0, $x - $R), $e = min($W - 1, $x + $R); $k <= $e; $k++) if ($ink[$row + $k] > $m) $m = $ink[$row + $k];
        $tmpMax[$row + $x] = $m;
    }
}
$alpha = new SplFixedArray($W * $H);
for ($x = 0; $x < $W; $x++) {
    for ($y = 0; $y < $H; $y++) {
        $m = 0;
        for ($k = max(0, $y - $R), $e = min($H - 1, $y + $R); $k <= $e; $k++) if ($tmpMax[$k * $W + $x] > $m) $m = $tmpMax[$k * $W + $x];
        $i = $y * $W + $x;
        $a = $m < 70 ? 0.0 : $ink[$i] / $m;          // faint specks / background → 0
        $a = $a < 0.06 ? 0.0 : ($a > 0.94 ? 1.0 : $a);
        $alpha[$i] = $a;
    }
}
unset($tmpMax);
$log('alpha mask computed');

// Content bounding box + split between the mark (wings + globe) and the wordmark (largest empty row band).
$rowInk = array_fill(0, $H, 0);
$minX = $W; $maxX = 0; $minY = $H; $maxY = 0;
for ($y = 0; $y < $H; $y++) {
    for ($x = 0; $x < $W; $x++) {
        if ($alpha[$y * $W + $x] > 0.3) {
            $rowInk[$y]++;
            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        }
    }
}
$bestGap = [0, 0];
$start = null;
for ($y = $minY; $y <= $maxY; $y++) {
    if ($rowInk[$y] === 0) { $start ??= $y; continue; }
    if ($start !== null && $y - $start > $bestGap[1] - $bestGap[0]) $bestGap = [$start, $y];
    $start = null;
}
$splitY = intdiv($bestGap[0] + $bestGap[1], 2);
$markMaxY = $bestGap[0];
$markMinX = $W; $markMaxX = 0;
for ($y = $minY; $y < $markMaxY; $y++) for ($x = 0; $x < $W; $x++) if ($alpha[$y * $W + $x] > 0.3) { $markMinX = min($markMinX, $x); $markMaxX = max($markMaxX, $x); }
$log(sprintf('content %d,%d → %d,%d · mark/wordmark split at y=%d', $minX, $minY, $maxX, $maxY, $splitY));

/* ---------- 2. Vector outline with potrace ---------- */
$pgm = "P5\n{$W} {$H}\n255\n";
for ($i = 0, $n = $W * $H; $i < $n; $i++) $pgm .= chr((int) round(255 * (1 - $alpha[$i])));
file_put_contents("{$tmp}/mask.pgm", $pgm);
unset($pgm);
exec(sprintf('mkbitmap -n -s 2 -3 -t 0.5 -o %s %s 2>&1', escapeshellarg("{$tmp}/mask.pbm"), escapeshellarg("{$tmp}/mask.pgm")), $o1, $rc1);
exec(sprintf('potrace -b svg --flat -t 12 -a 1.1 -O 0.3 -o %s %s 2>&1', escapeshellarg("{$tmp}/mask.svg"), escapeshellarg("{$tmp}/mask.pbm")), $o2, $rc2);
if ($rc1 || $rc2) exit("potrace failed: " . implode("\n", array_merge($o1, $o2)) . "\n");
$traced = file_get_contents("{$tmp}/mask.svg");
preg_match('/<g transform="([^"]+)"/', $traced, $tm);
preg_match_all('/<path d="([^"]+)"/s', $traced, $pm);
$transform = $tm[1];
$pathData = implode(' ', array_map(fn($d) => preg_replace('/\s+/', ' ', $d), $pm[1]));
$log('traced ' . count($pm[1]) . ' path(s)');
// The bitmap was traced at 2× → work in a 2W×2H coordinate space.
$S = 2;

/* ---------- 3. Colour layers (un-premultiplied, spread outward, half resolution) ---------- */
$colorLayer = static function (\GdImage $src) use ($W, $H, $alpha): \GdImage {
    $w = intdiv($W, 2);
    $h = intdiv($H, 2);
    $r = array_fill(0, $w * $h, -1);
    $g = $r;
    $b = $r;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $sr = $sg = $sb = $sw = 0.0;
            foreach ([[0, 0], [1, 0], [0, 1], [1, 1]] as [$dx, $dy]) {
                $X = 2 * $x + $dx; $Y = 2 * $y + $dy;
                $a = $alpha[$Y * $W + $X];
                if ($a < 0.35) continue;
                $c = imagecolorat($src, $X, $Y);
                // p = a·c + (1−a)·255  →  c = 255 − (255 − p) / a
                $sr += $a * max(0, min(255, 255 - (255 - (($c >> 16) & 255)) / $a));
                $sg += $a * max(0, min(255, 255 - (255 - (($c >> 8) & 255)) / $a));
                $sb += $a * max(0, min(255, 255 - (255 - ($c & 255)) / $a));
                $sw += $a;
            }
            if ($sw > 0) { $i = $y * $w + $x; $r[$i] = $sr / $sw; $g[$i] = $sg / $sw; $b[$i] = $sb / $sw; }
        }
    }
    // Spread colours 14 px outward so the vector clip never reveals a white fringe.
    for ($it = 0; $it < 14; $it++) {
        $nr = $r; $ng = $g; $nb = $b;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $i = $y * $w + $x;
                if ($r[$i] >= 0) continue;
                $sr = $sg = $sb = 0.0; $n = 0;
                foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as [$dx, $dy]) {
                    $X = $x + $dx; $Y = $y + $dy;
                    if ($X < 0 || $Y < 0 || $X >= $w || $Y >= $h) continue;
                    $j = $Y * $w + $X;
                    if ($r[$j] >= 0) { $sr += $r[$j]; $sg += $g[$j]; $sb += $b[$j]; $n++; }
                }
                if ($n) { $nr[$i] = $sr / $n; $ng[$i] = $sg / $n; $nb[$i] = $sb / $n; }
            }
        }
        $r = $nr; $g = $ng; $b = $nb;
    }
    $img = imagecreatetruecolor($w, $h);
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $i = $y * $w + $x;
            $col = $r[$i] < 0 ? 0x808080 : ((int) $r[$i] << 16) | ((int) $g[$i] << 8) | (int) $b[$i];
            imagesetpixel($img, $x, $y, $col);
        }
    }
    return $img;
};

$layers = [];
foreach (['dark' => $dark, 'light' => $light] as $name => $src) {
    $layer = $colorLayer($src);
    ob_start();
    imagejpeg($layer, null, 92);
    $layers[$name] = base64_encode((string) ob_get_clean());
    $log("colour layer {$name} ready");
}

/* ---------- 4. SVG assembly, PNG/WebP export ---------- */
$svg = static function (string $layer, array $box) use ($W, $H, $S, $transform, $pathData): string {
    [$x0, $y0, $x1, $y1] = $box;
    $pad = 24;
    $vx = ($x0 - $pad) * $S; $vy = ($y0 - $pad) * $S;
    $vw = ($x1 - $x0 + 2 * $pad) * $S; $vh = ($y1 - $y0 + 2 * $pad) * $S;
    return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="' . "{$vx} {$vy} {$vw} {$vh}" . '">'
        . '<title>Heaven of Web</title>'
        . '<defs><clipPath id="how-shape"><path transform="' . $transform . '" d="' . $pathData . '"/></clipPath></defs>'
        . '<image width="' . ($W * $S) . '" height="' . ($H * $S) . '" preserveAspectRatio="none" clip-path="url(#how-shape)" '
        . 'href="data:image/jpeg;base64,' . $layer . '" xlink:href="data:image/jpeg;base64,' . $layer . '"/>'
        . '</svg>';
};
$boxes = [
    'logo' => [$minX, $minY, $maxX, $maxY],
    'mark' => [$markMinX, $minY, $markMaxX, $markMaxY],
];
foreach ($boxes as $kind => $box) {
    foreach ($layers as $tone => $layer) {
        $base = "{$out}/{$kind}-{$tone}";
        file_put_contents("{$base}.svg", $svg($layer, $box));
        $width = $kind === 'logo' ? 1600 : 1024;
        exec(sprintf('rsvg-convert -w %d -o %s %s 2>&1', $width, escapeshellarg("{$base}.png"), escapeshellarg("{$base}.svg")), $o3, $rc3);
        if ($rc3) exit("rsvg-convert failed: " . implode("\n", $o3) . "\n");
        $png = imagecreatefrompng("{$base}.png");
        imagesavealpha($png, true);
        imagewebp($png, "{$base}.webp", 90);
        // Small WebP for the site header / footer.
        $small = imagescale($png, $kind === 'logo' ? 480 : 240, -1, IMG_BICUBIC);
        imagesavealpha($small, true);
        imagewebp($small, "{$base}-sm.webp", 90);
        printf("  %-18s svg %4d KB · png %4d KB · webp %4d KB\n", "{$kind}-{$tone}", filesize("{$base}.svg") / 1024, filesize("{$base}.png") / 1024, filesize("{$base}.webp") / 1024);
    }
}
$log('done → public_html/assets/img/brand/');
