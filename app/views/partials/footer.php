<?php
$email = setting('contact_email');
$phone = setting('contact_phone');
$footerServices = App\Core\Translations::attach('service', App\Core\DB::all('SELECT id, slug FROM services WHERE published = 1 AND slug IS NOT NULL ORDER BY sort, id'));
?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a class="footer-logo" href="<?= lurl() ?>">
        <img src="<?= e(media(setting('img_logo_full_light'))) ?>" alt="Heaven of Web" width="160" height="114" loading="lazy">
      </a>
      <p><?= e(t('footer.tagline', ['city' => setting('company_city', null, 'Sousse')])) ?></p>
    </div>

    <nav class="footer-col" aria-label="<?= e(t('footer.sitemap')) ?>">
      <h2><?= e(t('footer.sitemap')) ?></h2>
      <ul>
        <li><a href="<?= lurl() ?>#services"><?= e(t('nav.services')) ?></a></li>
        <li><a href="<?= lurl() ?>#projects"><?= e(t('nav.projects')) ?></a></li>
        <li><a href="<?= lurl() ?>#about"><?= e(t('nav.about')) ?></a></li>
        <li><a href="<?= lurl() ?>#contact"><?= e(t('nav.contact')) ?></a></li>
      </ul>
    </nav>

    <?php if ($footerServices): ?>
    <nav class="footer-col" aria-label="<?= e(t('nav.services')) ?>">
      <h2><?= e(t('nav.services')) ?></h2>
      <ul>
        <?php foreach ($footerServices as $fs): ?><li><a href="<?= lurl('services/' . $fs['slug']) ?>"><?= e($fs['title'] ?? '') ?></a></li><?php endforeach; ?>
      </ul>
    </nav>
    <?php endif; ?>

    <div class="footer-col">
      <h2><?= e(t('footer.contact')) ?></h2>
      <ul>
        <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= icon('mail') ?><?= e($email) ?></a></li><?php endif; ?>
        <?php if ($phone): ?><li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" dir="ltr"><?= icon('phone') ?><?= e($phone) ?></a></li><?php endif; ?>
        <li><span><?= icon('map-pin') ?><?= e(setting('company_city', null, 'Sousse')) ?>, <?= e(setting('company_country', null, 'Tunisie')) ?></span></li>
      </ul>
      <?php if ($socials): ?>
      <ul class="socials" aria-label="<?= e(t('footer.follow')) ?>">
        <?php foreach ($socials as $network => $href): ?>
        <li><a href="<?= e($href) ?>" rel="noopener" target="_blank" aria-label="<?= e(ucfirst($network)) ?>"><?= icon($network) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>© <?= date('Y') ?> Heaven of Web. <?= e(t('footer.rights')) ?></p>
    <ul>
      <li><a href="<?= lurl('mentions-legales') ?>"><?= e(t('legal.title')) ?></a></li>
      <li><a href="<?= lurl('confidentialite') ?>"><?= e(t('privacy.title')) ?></a></li>
      <li><button class="link-btn" type="button" data-cookie-open><?= e(t('cookie.manage')) ?></button></li>
      <li><a href="<?= lurl('plan-du-site') ?>"><?= e(t('sitemap.title')) ?></a></li>
    </ul>
  </div>
</footer>
