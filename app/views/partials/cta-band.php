<?php
$email = setting('contact_email');
$phone = setting('contact_phone');
$bg = setting('img_hero');
?>
<section class="contact-band cta-band" aria-labelledby="cta-title">
  <?php if ($bg): ?><img class="contact-bg" src="<?= e(media($bg, 1440)) ?>" alt="" loading="lazy" aria-hidden="true"><?php endif; ?>
  <div class="container cta-grid">
    <div data-anim="left">
      <p class="eyebrow eyebrow-light"><?= e(t('cta.eyebrow')) ?></p>
      <h2 class="section-title" id="cta-title"><?= e(t('cta.title')) ?></h2>
      <p class="lead"><?= e(t('cta.text')) ?></p>
      <a class="btn btn-primary btn-lg" href="<?= lurl() ?>#contact"><?= e(t('cta.button')) ?><?= icon(is_rtl() ? 'arrow-left' : 'arrow-right') ?></a>
    </div>
    <ul class="contact-direct" data-anim="right">
      <?php if ($phone): ?><li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= icon('phone') ?><bdi dir="ltr"><?= e($phone) ?></bdi></a></li><?php endif; ?>
      <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= icon('mail') ?><?= e($email) ?></a></li><?php endif; ?>
      <li><span><?= icon('map-pin') ?><?= e(setting('company_city', null, 'Sousse')) ?>, <?= e(setting('company_country', null, 'Tunisie')) ?></span></li>
    </ul>
  </div>
</section>
