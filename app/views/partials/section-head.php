<?php
/** @var string $eyebrow @var string $title @var ?string $intro @var string $id @var bool $center @var ?string $titleHtml */
?>
<header class="section-head<?= !empty($center) ? ' is-center' : '' ?>" data-anim="up">
  <?php if (!empty($eyebrow)): ?><p class="eyebrow"><?= e($eyebrow) ?></p><?php endif; ?>
  <h2 class="section-title" id="<?= e($id) ?>"><?= $titleHtml ?? e($title) ?></h2>
  <?php if (!empty($intro)): ?><p class="section-intro"><?= e($intro) ?></p><?php endif; ?>
</header>
