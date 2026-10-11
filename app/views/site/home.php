<?php
$arrow = is_rtl() ? 'arrow-left' : 'arrow-right';
$heroImg = setting('img_hero');
?>
<section class="hero" id="top" aria-labelledby="hero-title">
  <?php if ($heroImg): ?>
  <img class="hero-bg" src="<?= e(media($heroImg)) ?>" srcset="<?= e(srcset($heroImg)) ?>" sizes="100vw" alt="" fetchpriority="high" data-parallax>
  <?php endif; ?>
  <div class="hero-visual" data-globe aria-hidden="true"></div>
  <div class="container hero-inner">
    <p class="eyebrow eyebrow-light" data-hero-item><?= e(setting('hero_eyebrow')) ?></p>
    <h1 class="hero-title" id="hero-title">
      <span class="hero-brand" data-hero-item><bdi dir="ltr">Heaven of <span class="accent">Web</span></bdi></span>
      <span class="hero-tagline" data-hero-item><?= e(setting('hero_title')) ?></span>
    </h1>
    <p class="hero-sub" data-hero-item><?= e(setting('hero_subtitle')) ?></p>
    <div class="hero-actions" data-hero-item>
      <a class="btn btn-primary btn-lg" href="#contact"><?= e(t('hero.cta')) ?><?= icon($arrow) ?></a>
      <a class="btn btn-outline-light btn-lg" href="#services"><?= e(t('hero.cta_services')) ?></a>
    </div>
  </div>
  <svg class="hero-wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V64C240 112 480 120 760 88S1200 8 1440 0v120z"/></svg>
</section>

<?php if ($highlights): ?>
<section class="highlights" aria-label="<?= e(t('highlights.label')) ?>">
  <ul class="container highlight-list" data-anim="stagger">
    <?php foreach ($highlights as [$ic, $text]): ?>
    <li class="highlight"><span class="highlight-icon"><?= icon($ic ?: 'check') ?></span><span><?= e($text) ?></span></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<section class="section about" id="about" aria-labelledby="about-title" data-track="about">
  <div class="container about-grid">
    <div class="about-text">
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.about'), 'title' => setting('about_title'), 'id' => 'about-title']); ?>
      <div class="prose" data-anim="up"><?= md(setting('about_text')) ?></div>
      <?php if ($stats): ?>
      <dl class="stats" data-anim="stagger">
        <?php foreach ($stats as $s): ?>
        <div class="stat"><dt><?= e($s['label']) ?></dt><dd data-count="<?= e($s['value']) ?>"><?= e($s['value']) ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <?php endif; ?>
      <a class="btn btn-primary" href="#services" data-anim="up"><?= e(t('about.more')) ?><?= icon($arrow) ?></a>
    </div>
    <figure class="about-media" data-anim="reveal">
      <img src="<?= e(media(setting('img_about'), 960)) ?>" srcset="<?= e(srcset(setting('img_about'))) ?>" sizes="(min-width: 900px) 45vw, 90vw" alt="<?= e(t('about.img_alt')) ?>" width="960" height="960" loading="lazy" decoding="async">
      <figcaption class="float-card">
        <span class="float-icon"><?= icon('users') ?></span>
        <span><strong><?= e(t('about.badge_title')) ?></strong><small><?= e(t('about.badge_text')) ?></small></span>
      </figcaption>
    </figure>
  </div>
</section>

<section class="section services tint" id="services" aria-labelledby="services-title" data-track="services">
  <div class="container">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.services'), 'title' => setting('services_title'), 'intro' => setting('services_intro'), 'id' => 'services-title', 'center' => true]); ?>
    <ul class="service-cards" data-anim="stagger">
      <?php foreach ($services as $s): $url = $s['slug'] ? lurl('services/' . $s['slug']) : null; ?>
      <li class="service-card">
        <span class="icon-bubble"><?= icon($s['icon']) ?></span>
        <h3><?php if ($url): ?><a href="<?= $url ?>"><?= e($s['title'] ?? '') ?></a><?php else: ?><?= e($s['title'] ?? '') ?><?php endif; ?></h3>
        <p><?= e($s['text'] ?? '') ?></p>
        <?php if ($url): ?><span class="card-link" aria-hidden="true"><?= e(t('service.more')) ?><?= icon($arrow) ?></span><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php if ($process): ?>
<section class="section" id="process" aria-labelledby="process-title" data-track="process">
  <div class="container">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.process'), 'title' => setting('process_title'), 'intro' => setting('process_intro'), 'id' => 'process-title']); ?>
    <?php partial('partials/process', ['steps' => $process]); ?>
  </div>
</section>
<?php endif; ?>

<section class="section tint" id="why" aria-labelledby="why-title" data-track="why">
  <div class="container">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.why'), 'title' => setting('why_title'), 'intro' => setting('why_intro'), 'id' => 'why-title', 'center' => true]); ?>
    <?php partial('partials/why', ['features' => $features]); ?>
  </div>
</section>

<section class="section projects" id="projects" aria-labelledby="projects-title" data-track="projects">
  <div class="container">
    <div class="section-head-row">
      <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.projects'), 'title' => setting('projects_title'), 'intro' => setting('projects_intro'), 'id' => 'projects-title']); ?>
      <a class="link-arrow" href="<?= lurl('realisations') ?>" data-anim="up"><?= e(t('projects.all_link')) ?><?= icon($arrow) ?></a>
    </div>
    <?php partial('partials/project-carousel', ['projects' => $projects, 'label' => setting('projects_title')]); ?>
  </div>
</section>

<?php if ($reviews): ?>
<section class="section tint reviews" id="reviews" aria-labelledby="reviews-title" data-track="reviews">
  <div class="container">
    <?php partial('partials/section-head', ['eyebrow' => t('eyebrow.reviews'), 'title' => setting('reviews_title'), 'intro' => $rating ? t('reviews.summary', ['rating' => str_replace('.', ',', (string) $rating), 'count' => count($reviews)]) : null, 'id' => 'reviews-title', 'center' => true]); ?>
    <div class="carousel" data-carousel-wrap>
      <ul class="carousel-track review-track" data-carousel tabindex="0" aria-label="<?= e(setting('reviews_title')) ?>" data-anim="stagger">
        <?php foreach ($reviews as $r): ?>
        <li class="review">
          <figure>
            <span class="stars" style="--rating: <?= (int) $r['rating'] ?>" role="img" aria-label="<?= e(t('reviews.stars', ['n' => (int) $r['rating']])) ?>"></span>
            <blockquote><p><?= nl2br(e($r['text'] ?? '')) ?></p></blockquote>
            <figcaption>
              <?php if ($r['avatar']): ?><img src="<?= e(media($r['avatar'])) ?>" alt="" width="48" height="48" loading="lazy">
              <?php else: ?><span class="avatar-initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($r['author'], 0, 1))) ?></span><?php endif; ?>
              <span><strong><?= e($r['author']) ?></strong><?php if ($r['company']): ?><small><?= e($r['company']) ?></small><?php endif; ?></span>
            </figcaption>
          </figure>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="carousel-controls">
        <button class="icon-btn" type="button" data-carousel-prev aria-label="<?= e(t('carousel.prev')) ?>"><?= icon(is_rtl() ? 'chevron-right' : 'chevron-left') ?></button>
        <div class="carousel-dots" data-carousel-dots></div>
        <button class="icon-btn" type="button" data-carousel-next aria-label="<?= e(t('carousel.next')) ?>"><?= icon(is_rtl() ? 'chevron-left' : 'chevron-right') ?></button>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php partial('partials/contact-band', ['services' => $services]); ?>
