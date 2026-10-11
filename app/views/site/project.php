<?php
$arrow = is_rtl() ? 'arrow-left' : 'arrow-right';
$back = is_rtl() ? 'arrow-right' : 'arrow-left';
$heroImg = setting('img_hero');
$imgOverview = $images[0]['path'] ?? $project['cover'];
$imgTech = $images[1]['path'] ?? null;
$imgFeatures = $images[2]['path'] ?? $project['cover'];
$moreImages = array_slice($images, 3);
$colors = palette_colors($project['palette'] ?? null);
$host = $project['url'] ? preg_replace('#^www\.#', '', (string) parse_url($project['url'], PHP_URL_HOST)) : null;
/** Monogram colour for a technology tile, stable per name. */
$techColor = static fn(string $name) => ['#041C32', '#D9363A', '#133A5E', '#0F7B6C', '#6B46C1', '#B45309'][crc32(strtolower($name)) % 6];
?>
<section class="hero hero-page hero-project" id="top" aria-labelledby="project-title">
  <?php if ($heroImg): ?><img class="hero-bg" src="<?= e(media($heroImg, 1440)) ?>" alt="" data-parallax><?php endif; ?>
  <div class="container hero-project-grid">
    <div class="hero-inner">
      <a class="back-link back-link-light" href="<?= lurl('realisations') ?>" data-hero-item><?= icon($back) ?><?= e(t('projects.back')) ?></a>
      <?php if (!empty($project['category'])): ?><p class="pill" data-hero-item><?= e($project['category']) ?></p><?php endif; ?>
      <h1 class="hero-tagline hero-tagline-lg" id="project-title" data-hero-item><?= e($project['title']) ?></h1>
      <?php if (!empty($project['tagline'])): ?><p class="hero-lead" data-hero-item><?= e($project['tagline']) ?></p><?php endif; ?>
      <?php if (!empty($project['summary'])): ?><p class="hero-sub" data-hero-item><?= e($project['summary']) ?></p><?php endif; ?>
      <ul class="hero-meta" data-hero-item>
        <?php if ($date): ?><li><?= icon('clock') ?><span><small><?= e(t('project.date')) ?></small><strong><?= e($date) ?></strong></span></li><?php endif; ?>
        <?php if ($service): ?><li><?= icon($service['icon']) ?><span><small><?= e(t('projects.service')) ?></small><strong><a href="<?= lurl('services/' . $service['slug']) ?>"><?= e($service['title'] ?? '') ?></a></strong></span></li><?php endif; ?>
        <?php if ($host): ?><li><?= icon('globe') ?><span><small><?= e(t('project.link')) ?></small><strong><a href="<?= e($project['url']) ?>" target="_blank" rel="noopener"><?= e($host) ?> <?= icon('arrow-up-right', 'icon icon-xs') ?></a></strong></span></li><?php endif; ?>
      </ul>
    </div>
    <?php if ($project['cover']): ?>
    <figure class="hero-project-visual" data-hero-item>
      <img src="<?= e(media($project['cover'])) ?>" srcset="<?= e(srcset($project['cover'])) ?>" sizes="(min-width: 1024px) 50vw, 92vw" alt="<?= e($project['title']) ?>" width="1440" height="810" fetchpriority="high">
    </figure>
    <?php endif; ?>
  </div>
  <svg class="hero-wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V64C240 112 480 120 760 88S1200 8 1440 0v120z"/></svg>
</section>

<section class="section" aria-labelledby="overview-title">
  <div class="container split">
    <div>
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.overview'), 'title' => t('project.overview_heading', ['client' => $project['client']]), 'id' => 'overview-title']); ?>
      <div class="prose" data-anim="up"><?= !empty($project['body']) ? md($project['body']) : '<p>' . e($project['summary'] ?? '') . '</p>' ?></div>
      <?php if ($highlights): ?>
      <ul class="mini-features" data-anim="stagger">
        <?php foreach ($highlights as [$ic, $text]): ?><li><span class="icon-bubble icon-bubble-sm"><?= icon($ic ?: 'check') ?></span><span><?= e($text) ?></span></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <?php if ($imgOverview): ?>
    <figure class="split-visual" data-anim="reveal">
      <img src="<?= e(media($imgOverview, 960)) ?>" srcset="<?= e(srcset($imgOverview)) ?>" sizes="(min-width: 900px) 45vw, 92vw" alt="" loading="lazy">
    </figure>
    <?php endif; ?>
  </div>
</section>

<?php if ($technologies): ?>
<section class="section section-tight" aria-labelledby="tech-title">
  <div class="container">
    <div class="tech-panel<?= $imgTech ? ' has-visual' : '' ?>">
      <div class="tech-body">
        <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.tech'), 'title' => t('project.tech_title'), 'intro' => t('project.tech_intro'), 'id' => 'tech-title']); ?>
        <ul class="tech-list" data-anim="stagger">
          <?php foreach ($technologies as [$name, $role]): ?>
          <li>
            <span class="tech-logo" style="--c: <?= $techColor($name) ?>" aria-hidden="true"><?= e(monogram($name)) ?></span>
            <strong><?= e($name) ?></strong>
            <?php if ($role !== ''): ?><small><?= e($role) ?></small><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php if ($imgTech): ?><figure class="tech-visual"><img src="<?= e(media($imgTech, 960)) ?>" alt="" loading="lazy"></figure><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($features): ?>
<section class="section" aria-labelledby="features-title">
  <div class="container split split-reverse">
    <figure class="feature-visual" data-anim="left">
      <img src="<?= e(media($imgFeatures, 960)) ?>" srcset="<?= e(srcset($imgFeatures)) ?>" sizes="(min-width: 900px) 45vw, 92vw" alt="" loading="lazy">
      <?php if ($imageStats): ?>
      <figcaption class="stat-overlay">
        <?php foreach (array_slice($imageStats, 0, 3) as [$value, $label]): ?><span><strong><?= e($value) ?></strong><small><?= e($label) ?></small></span><?php endforeach; ?>
      </figcaption>
      <?php endif; ?>
    </figure>
    <div>
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.details'), 'title' => t('project.features'), 'id' => 'features-title']); ?>
      <ul class="feature-list" data-anim="stagger">
        <?php foreach ($features as $f): ?><li><?= icon('check') ?><span><?= e($f) ?></span></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($colors): ?>
<section class="section section-tight tint" aria-labelledby="palette-title">
  <div class="container">
    <div class="palette-head" data-anim="up">
      <div><p class="eyebrow"><?= e(t('eyebrow.identity')) ?></p><h2 class="section-title section-title-sm" id="palette-title"><?= e(t('projects.palette')) ?></h2></div>
      <p class="muted-note"><?= e(t('projects.palette_hint')) ?></p>
    </div>
    <div class="palette-strip" aria-hidden="true"><?php foreach ($colors as $c): ?><span style="background: <?= e($c['hex']) ?>"></span><?php endforeach; ?></div>
    <ul class="palette-grid" data-anim="stagger">
      <?php foreach ($colors as $c): ?>
      <li>
        <button type="button" class="swatch" style="--swatch: <?= e($c['hex']) ?>; --ink: <?= e($c['ink']) ?>" data-copy="<?= e($c['hex']) ?>" aria-label="<?= e(t('projects.copy') . ' ' . $c['hex']) ?>">
          <span class="swatch-color"><span class="swatch-copied"><?= icon('check') ?><?= e(t('projects.copied')) ?></span></span>
          <span class="swatch-info">
            <?php if ($c['name'] !== ''): ?><strong><?= e($c['name']) ?></strong><?php endif; ?>
            <code dir="ltr"><?= e($c['hex']) ?></code>
            <small dir="ltr">RGB <?= e($c['rgb']) ?></small>
          </span>
        </button>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($moreImages): ?>
<section class="section section-tight" aria-label="<?= e(t('project.gallery')) ?>">
  <div class="container gallery" data-anim="stagger">
    <?php foreach ($moreImages as $img): ?>
    <figure><img src="<?= e(media($img['path'], 960)) ?>" srcset="<?= e(srcset($img['path'])) ?>" sizes="(min-width: 900px) 50vw, 94vw" alt="" loading="lazy" decoding="async"></figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="similar-title">
  <div class="container results-split<?= $results ? '' : ' no-results' ?>">
    <?php if ($results): ?>
    <div class="results">
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.results'), 'title' => t('project.results'), 'intro' => $project['results_text'] ?? null, 'id' => 'results-title']); ?>
      <dl class="result-grid" data-anim="stagger">
        <?php foreach ($results as $r): ?>
        <div><dt><span class="icon-bubble icon-bubble-coral"><?= icon($r['icon']) ?></span><?= e($r['label']) ?></dt><dd data-count="<?= e($r['value']) ?>"><?= e($r['value']) ?></dd></div>
        <?php endforeach; ?>
      </dl>
    </div>
    <?php endif; ?>
    <?php if ($others): ?>
    <div class="similar">
      <div class="section-head-row">
        <h2 class="section-title section-title-sm" id="similar-title" data-anim="up"><?= e(t('project.similar')) ?></h2>
        <a class="link-arrow" href="<?= lurl('realisations') ?>"><?= e(t('projects.all_link')) ?><?= icon($arrow) ?></a>
      </div>
      <ul class="similar-grid" data-anim="stagger">
        <?php foreach ($others as $p): ?>
        <li class="project-tile">
          <a href="<?= lurl('realisations/' . $p['slug']) ?>">
            <figure><img src="<?= e(media($p['cover'], 480)) ?>" srcset="<?= e(srcset($p['cover'])) ?>" sizes="(min-width: 1100px) 300px, 80vw" alt="" width="480" height="270" loading="lazy"></figure>
            <div class="project-tile-meta">
              <span><?php if (!empty($p['category'])): ?><span class="pill pill-sm"><?= e($p['category']) ?></span><?php endif; ?><strong><?= e($p['title']) ?></strong></span>
            </div>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php partial('partials/cta-band'); ?>
