<?php
$arrow = is_rtl() ? 'arrow-left' : 'arrow-right';
$groups = [
    ['icon' => 'home', 'title' => t('nav.home'), 'links' => [
        [lurl(), t('nav.home')],
        [lurl() . '#about', t('nav.about')],
        [lurl() . '#services', t('nav.services')],
        [lurl() . '#process', t('eyebrow.process')],
        [lurl() . '#why', t('nav.why')],
        [lurl() . '#contact', t('nav.contact')],
    ]],
    ['icon' => 'briefcase', 'title' => t('nav.services'), 'links' => array_map(fn($s) => [lurl('services/' . $s['slug']), $s['title'] ?? ''], $services)],
    ['icon' => 'image', 'title' => t('nav.projects'), 'links' => array_merge(
        [[lurl('realisations'), t('projects.all_link')]],
        array_map(fn($p) => [lurl('realisations/' . $p['slug']), $p['title']], $projects)
    )],
    ['icon' => 'shield', 'title' => t('sitemap.legal'), 'links' => [
        [lurl('mentions-legales'), t('legal.title')],
        [lurl('confidentialite'), t('privacy.title')],
        [lurl('plan-du-site'), t('sitemap.title')],
    ]],
];
?>
<section class="hero hero-page hero-legal" id="top" aria-labelledby="sitemap-title">
  <div class="container hero-inner">
    <nav class="breadcrumb" aria-label="<?= e(t('service.breadcrumb')) ?>" data-hero-item>
      <ol><li><a href="<?= lurl() ?>"><?= e(t('nav.home')) ?></a></li><li aria-current="page"><?= e(t('sitemap.title')) ?></li></ol>
    </nav>
    <h1 class="hero-tagline hero-tagline-lg" id="sitemap-title" data-hero-item><?= e(t('sitemap.title')) ?></h1>
    <p class="hero-sub" data-hero-item><?= e(t('sitemap.sub')) ?></p>
  </div>
</section>

<div class="container sitemap-grid" data-anim="stagger">
  <?php foreach ($groups as $g): if (!$g['links']) continue; ?>
  <section class="sitemap-card" aria-label="<?= e($g['title']) ?>">
    <h2><span class="icon-bubble icon-bubble-sm"><?= icon($g['icon']) ?></span><?= e($g['title']) ?></h2>
    <ul>
      <?php foreach ($g['links'] as [$href, $label]): ?>
      <li><a href="<?= e($href) ?>"><?= icon($arrow) ?><span><?= e($label) ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endforeach; ?>
</div>
