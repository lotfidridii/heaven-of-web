<?php
$colors = $palette ? (json_decode($palette, true) ?: []) : [];
if (!$colors) return;
?>
<span class="palette-dots" aria-hidden="true"><?php foreach (array_slice($colors, 0, 6) as $c): ?><span style="background: <?= e($c['hex']) ?>"></span><?php endforeach; ?></span>
