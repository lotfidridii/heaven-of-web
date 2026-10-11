<?php
$title = $s['title'] ?? '';
$arrow = is_rtl() ? 'arrow-left' : 'arrow-right';
$heroImg = $s['image'] ?: setting('img_hero');
$visual = $s['image'] ?: ($related[0]['cover'] ?? null);
$city = setting('company_city', null, 'Sousse');
$region = setting('service_region') ?: setting('company_country', null, 'Tunisie');
?>
<section class="hero hero-page" id="top" aria-labelledby="service-title">
  <?php if ($heroImg): ?><img class="hero-bg" src="<?= e(media($heroImg, 1440)) ?>" srcset="<?= e(srcset($heroImg)) ?>" sizes="100vw" alt="" fetchpriority="high" data-parallax><?php endif; ?>
  <div class="container hero-inner">
    <nav class="breadcrumb" aria-label="<?= e(t('service.breadcrumb')) ?>" data-hero-item>
      <ol>
        <li><a href="<?= lurl() ?>"><?= e(t('nav.home')) ?></a></li>
        <li><a href="<?= lurl() ?>#services"><?= e(t('nav.services')) ?></a></li>
        <li aria-current="page"><?= e($title) ?></li>
      </ol>
    </nav>
    <p class="eyebrow eyebrow-light" data-hero-item><?= e($title) ?></p>
    <h1 class="hero-tagline hero-tagline-lg" id="service-title" data-hero-item><?= accent_last(($s['meta_title'] ?? '') ? preg_replace('/\s+[—–-]\s+.*$/u', '', $s['meta_title']) : $title) ?></h1>
    <p class="hero-sub" data-hero-item><?= e(($s['intro'] ?? '') ?: ($s['text'] ?? '')) ?></p>
    <div class="hero-actions" data-hero-item>
      <a class="btn btn-primary btn-lg" href="#contact"><?= e(t('service.start')) ?><?= icon($arrow) ?></a>
      <a class="btn btn-outline-light btn-lg" href="<?= $related ? '#related' : lurl('realisations') ?>"><?= e(t('hero.cta2')) ?></a>
    </div>
  </div>
  <svg class="hero-wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V64C240 112 480 120 760 88S1200 8 1440 0v120z"/></svg>
</section>

<section class="section" aria-labelledby="intro-title">
  <div class="container service-intro">
    <div>
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.service'), 'title' => '', 'titleHtml' => accent_last(t('service.intro_title')), 'id' => 'intro-title']); ?>
      <div class="prose service-prose" data-anim="up"><?= !empty($s['body']) ? md($s['body']) : '<p>' . e($s['text'] ?? '') . '</p>' ?></div>
    </div>
    <aside class="service-aside" data-anim="right">
      <?php if ($visual): ?>
      <figure class="service-visual"><img src="<?= e(media($visual, 960)) ?>" srcset="<?= e(srcset($visual)) ?>" sizes="(min-width: 900px) 40vw, 90vw" alt="" loading="lazy"></figure>
      <?php endif; ?>
      <?php if ($included): ?>
      <div class="included-card">
        <h2><?= e(t('service.included')) ?></h2>
        <ul class="checklist">
          <?php foreach ($included as $item): ?><li><?= icon('check') ?><span><?= e($item) ?></span></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</section>

<?php if ($features): ?>
<section class="section tint" aria-labelledby="why-title">
  <div class="container">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.why'), 'title' => setting('why_title'), 'intro' => setting('why_intro'), 'id' => 'why-title']); ?>
    <?php partial('partials/why', ['features' => $features]); ?>
  </div>
</section>
<?php endif; ?>

<?php if ($steps): ?>
<section class="section" aria-labelledby="steps-title">
  <div class="container">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.process'), 'title' => t('service.process'), 'id' => 'steps-title']); ?>
    <?php partial('partials/process', ['steps' => $steps]); ?>
  </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="section tint" id="related" aria-labelledby="related-title">
  <div class="container">
    <div class="section-head-row">
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.projects'), 'title' => t('service.related'), 'id' => 'related-title']); ?>
      <a class="link-arrow" href="<?= lurl('realisations') ?>" data-anim="up"><?= e(t('projects.all_link')) ?><?= icon($arrow) ?></a>
    </div>
    <?php partial('partials/project-carousel', ['projects' => $related, 'label' => t('service.related')]); ?>
  </div>
</section>
<?php endif; ?>

<?php if ($faq): ?>
<section class="section" aria-labelledby="faq-title">
  <div class="container faq">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.faq'), 'title' => t('service.faq'), 'id' => 'faq-title']); ?>
    <div class="faq-list" data-anim="stagger">
      <?php foreach ($faq as $i => $item): ?>
      <details <?= $i === 0 ? 'open' : '' ?>>
        <summary><span><?= e($item['q']) ?></span><?= icon('plus') ?></summary>
        <div class="faq-answer"><p><?= nl2br(e($item['a'])) ?></p></div>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-tight" aria-labelledby="area-title">
  <div class="container">
    <div class="service-area" data-anim="up">
      <div>
        <p class="eyebrow eyebrow-light"><?= e(t('eyebrow.area')) ?></p>
        <h2 class="section-title section-title-sm" id="area-title"><?= e(t('service.area_title')) ?></h2>
        <p class="lead"><?= e(t('service.area_text', ['city' => $city, 'region' => $region])) ?></p>
      </div>
      <?php if ($areas): ?>
      <ul class="area-list">
        <?php foreach ($areas as $a): ?><li><?= icon('map-pin') ?><?= e($a) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($others): ?>
<nav class="section section-tight" aria-labelledby="others-title">
  <div class="container">
    <h2 class="section-title section-title-sm" id="others-title" data-anim="up"><?= e(t('service.others')) ?></h2>
    <ul class="other-services" data-anim="stagger">
      <?php foreach ($others as $o): if (!$o['slug']) continue; ?>
      <li>
        <a href="<?= lurl('services/' . $o['slug']) ?>">
          <span class="icon-bubble"><?= icon($o['icon']) ?></span>
          <span><strong><?= e($o['title'] ?? '') ?></strong><small><?= e(str_excerpt($o['text'] ?? '', 90)) ?></small></span>
          <?= icon($arrow) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>
<?php endif; ?>

<?php partial('partials/contact-band', ['services' => $services, 'preselect' => $title, 'title' => t('service.contact_title')]); ?>
