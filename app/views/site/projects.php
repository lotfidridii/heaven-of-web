<?php $arrow = is_rtl() ? 'arrow-left' : 'arrow-right'; ?>
<section class="hero hero-page hero-compact" id="top" aria-labelledby="projects-page-title">
  <?php if ($img = setting('img_hero')): ?><img class="hero-bg" src="<?= e(media($img, 1440)) ?>" alt="" fetchpriority="high" data-parallax><?php endif; ?>
  <div class="container hero-inner">
    <nav class="breadcrumb" aria-label="<?= e(t('service.breadcrumb')) ?>" data-hero-item>
      <ol><li><a href="<?= lurl() ?>"><?= e(t('nav.home')) ?></a></li><li aria-current="page"><?= e(setting('projects_title')) ?></li></ol>
    </nav>
    <p class="eyebrow eyebrow-light" data-hero-item><?= e(t('eyebrow.projects')) ?></p>
    <h1 class="hero-tagline hero-tagline-lg" id="projects-page-title" data-hero-item><?= accent_last(setting('projects_title')) ?></h1>
    <p class="hero-sub" data-hero-item><?= e(setting('projects_intro')) ?></p>
  </div>
  <svg class="hero-wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V64C240 112 480 120 760 88S1200 8 1440 0v120z"/></svg>
</section>

<section class="section">
  <div class="container">
    <?php if (count($categories) > 1): ?>
    <div class="filters" role="group" aria-label="<?= e(t('projects.filter')) ?>" data-filters data-anim="up">
      <button type="button" class="chip" aria-pressed="true" data-filter="*"><?= e(t('projects.all')) ?></button>
      <?php foreach ($categories as $cat): ?><button type="button" class="chip" aria-pressed="false" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <ul class="project-grid" data-project-grid data-anim="stagger">
      <?php foreach ($projects as $p): ?>
      <li class="project-tile" data-category="<?= e($p['category'] ?? '') ?>">
        <a href="<?= lurl('realisations/' . $p['slug']) ?>">
          <figure><img src="<?= e(media($p['cover'], 960)) ?>" srcset="<?= e(srcset($p['cover'])) ?>" sizes="(min-width: 1100px) 380px, (min-width: 640px) 45vw, 92vw" alt="" width="960" height="540" loading="lazy" decoding="async"></figure>
          <div class="project-tile-meta">
            <span><strong><?= e($p['title']) ?></strong><small><?= e($p['category'] ?? '') ?></small></span>
            <?= icon($arrow) ?>
          </div>
          <p class="project-tile-summary"><?= e($p['summary'] ?? '') ?></p>
          <?php partial('partials/palette-dots', ['palette' => $p['palette'] ?? null]); ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
