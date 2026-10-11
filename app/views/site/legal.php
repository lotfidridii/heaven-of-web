<section class="hero hero-page hero-legal" id="top" aria-labelledby="legal-title">
  <div class="container hero-inner">
    <nav class="breadcrumb" aria-label="<?= e(t('service.breadcrumb')) ?>" data-hero-item>
      <ol><li><a href="<?= lurl() ?>"><?= e(t('nav.home')) ?></a></li><li aria-current="page"><?= e($title) ?></li></ol>
    </nav>
    <h1 class="hero-tagline hero-tagline-lg" id="legal-title" data-hero-item><?= e($title) ?></h1>
    <p class="hero-sub" data-hero-item><?= e($subtitle) ?></p>
    <ul class="chips" data-hero-item>
      <?php if ($updated): ?><li><?= icon('clock') ?><?= e(t('legal.updated', ['date' => $updated])) ?></li><?php endif; ?>
      <li><?= icon('shield') ?>Heaven of Web · <?= e(setting('company_city', null, 'Sousse')) ?></li>
    </ul>
  </div>
</section>

<div class="container legal-layout">
  <?php if (count($doc['sections']) > 1): ?>
  <aside class="legal-toc" aria-labelledby="toc-title">
    <nav>
      <p class="legal-toc-title" id="toc-title"><?= e(t('legal.toc')) ?></p>
      <ol>
        <?php foreach ($doc['sections'] as $i => $s): ?>
        <li><a href="#<?= $s['id'] ?>" data-toc-link><span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($s['title']) ?></a></li>
        <?php endforeach; ?>
      </ol>
    </nav>
  </aside>
  <?php endif; ?>

  <div class="legal-content">
    <?php if ($doc['intro'] !== ''): ?><div class="legal-intro prose" data-anim="up"><?= $doc['intro'] ?></div><?php endif; ?>
    <?php foreach ($doc['sections'] as $i => $s): ?>
    <section class="legal-card" id="<?= $s['id'] ?>" aria-labelledby="<?= $s['id'] ?>-title" data-anim="up" data-toc-section>
      <h2 id="<?= $s['id'] ?>-title"><span class="legal-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($s['title']) ?></h2>
      <div class="prose"><?= $s['html'] ?></div>
    </section>
    <?php endforeach; ?>
  </div>
</div>
