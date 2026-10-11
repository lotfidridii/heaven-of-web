<?php
/** @var array $projects @var string $label accessible name */
$arrow = is_rtl() ? 'arrow-left' : 'arrow-right';
?>
<div class="carousel" data-carousel-wrap>
  <ul class="carousel-track" data-carousel tabindex="0" aria-label="<?= e($label) ?>" data-anim="stagger">
    <?php foreach ($projects as $p): ?>
    <li class="project-tile">
      <a href="<?= lurl('realisations/' . $p['slug']) ?>">
        <figure>
          <img src="<?= e(media($p['cover'], 960)) ?>" srcset="<?= e(srcset($p['cover'])) ?>" sizes="(min-width: 1100px) 300px, (min-width: 640px) 45vw, 80vw" alt="" width="960" height="540" loading="lazy" decoding="async">
        </figure>
        <div class="project-tile-meta">
          <span>
            <strong><?= e($p['title']) ?></strong>
            <small><?= e($p['category'] ?? '') ?></small>
          </span>
          <?= icon($arrow) ?>
        </div>
        <?php partial('partials/palette-dots', ['palette' => $p['palette'] ?? null]); ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="carousel-controls">
    <button class="icon-btn" type="button" data-carousel-prev aria-label="<?= e(t('carousel.prev')) ?>"><?= icon(is_rtl() ? 'chevron-right' : 'chevron-left') ?></button>
    <div class="carousel-dots" data-carousel-dots></div>
    <button class="icon-btn" type="button" data-carousel-next aria-label="<?= e(t('carousel.next')) ?>"><?= icon(is_rtl() ? 'chevron-left' : 'chevron-right') ?></button>
  </div>
</div>
