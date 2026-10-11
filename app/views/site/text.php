<article class="page text-page">
  <header class="container page-head">
    <a class="back-link" href="<?= lurl() ?>"><?= icon(is_rtl() ? 'arrow-right' : 'arrow-left') ?><?= e(t('nav.home')) ?></a>
    <h1 class="page-title"><?= e($title) ?></h1>
  </header>
  <div class="container prose prose-narrow"><?= md($body) ?></div>
</article>
