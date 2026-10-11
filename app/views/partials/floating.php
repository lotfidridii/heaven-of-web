<?php
$wa = preg_replace('/\D/', '', setting('contact_whatsapp'));
$waText = rawurlencode(t('whatsapp.message'));
?>
<div class="fab-stack" aria-label="<?= e(t('fab.label')) ?>">
  <?php if ($wa !== ''): ?>
  <a class="fab fab-wa" href="https://wa.me/<?= e($wa) ?>?text=<?= $waText ?>" target="_blank" rel="noopener" aria-label="<?= e(t('whatsapp.label')) ?>">
    <?= icon('whatsapp') ?>
    <span class="fab-tip" aria-hidden="true"><?= e(t('whatsapp.label')) ?></span>
  </a>
  <?php endif; ?>
  <button class="fab fab-top" type="button" data-to-top aria-label="<?= e(t('fab.top')) ?>">
    <svg class="fab-ring" viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="22" pathLength="100"/></svg>
    <?= icon('arrow-up') ?>
  </button>
</div>

<div class="cookie" data-cookie hidden role="dialog" aria-modal="false" aria-labelledby="cookie-title" aria-describedby="cookie-text">
  <div class="cookie-card">
    <div class="cookie-head">
      <span class="cookie-icon" aria-hidden="true"><?= icon('cookie') ?></span>
      <div>
        <p class="cookie-title" id="cookie-title"><?= e(t('cookie.title')) ?></p>
        <p class="cookie-text" id="cookie-text"><?= e(t('cookie.text')) ?> <a href="<?= lurl('confidentialite') ?>"><?= e(t('cookie.more')) ?></a></p>
      </div>
    </div>
    <div class="cookie-prefs" data-cookie-prefs hidden>
      <label class="cookie-opt">
        <span><strong><?= e(t('cookie.essential')) ?></strong><small><?= e(t('cookie.essential_text')) ?></small></span>
        <input type="checkbox" role="switch" checked disabled>
      </label>
      <label class="cookie-opt">
        <span><strong><?= e(t('cookie.stats')) ?></strong><small><?= e(t('cookie.stats_text')) ?></small></span>
        <input type="checkbox" role="switch" checked data-cookie-stats>
      </label>
    </div>
    <div class="cookie-actions">
      <button class="btn btn-ghost btn-sm" type="button" data-cookie-customize><?= e(t('cookie.customize')) ?></button>
      <button class="btn btn-ghost btn-sm" type="button" data-cookie-refuse><?= e(t('cookie.refuse')) ?></button>
      <button class="btn btn-primary btn-sm" type="button" data-cookie-accept><?= e(t('cookie.accept')) ?></button>
      <button class="btn btn-primary btn-sm" type="button" data-cookie-save hidden><?= e(t('cookie.save')) ?></button>
    </div>
  </div>
</div>
