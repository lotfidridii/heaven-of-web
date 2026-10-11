<?php $key = $status === 404 ? '404' : 'generic'; ?>
<section class="page error-page">
  <div class="container">
    <p class="error-code" aria-hidden="true"><?= (int) $status ?></p>
    <h1 class="page-title"><?= e(t("error.{$key}.title")) ?></h1>
    <p class="lead"><?= e(t("error.{$key}.text")) ?></p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?= lurl() ?>"><?= e(t('error.home')) ?></a>
      <a class="btn btn-ghost" href="<?= lurl() ?>#contact"><?= e(t('nav.contact')) ?></a>
    </div>
  </div>
</section>
