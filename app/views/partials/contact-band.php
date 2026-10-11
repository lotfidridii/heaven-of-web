<?php
/** @var array $services @var ?string $preselect service title to select @var ?string $title @var ?string $intro */
$email = setting('contact_email');
$phone = setting('contact_phone');
$whatsapp = setting('contact_whatsapp');
$socials = array_filter([
    'facebook' => setting('social_facebook'),
    'instagram' => setting('social_instagram'),
    'linkedin' => setting('social_linkedin'),
    'x' => setting('social_x'),
]);
$contactSuccess = flash('contact_success');
$contactError = flash('contact_error');
$selected = old('service') ?: ($preselect ?? (string) ($_GET['service'] ?? ''));
$bg = setting('img_hero');
?>
<section class="contact-band" id="contact" aria-labelledby="contact-title" data-track="contact">
  <?php if ($bg): ?><img class="contact-bg" src="<?= e(media($bg, 1440)) ?>" alt="" loading="lazy" aria-hidden="true"><?php endif; ?>
  <div class="container contact-grid">
    <div class="contact-text" data-anim="left">
      <p class="eyebrow eyebrow-light"><?= e(t('eyebrow.contact')) ?></p>
      <h2 class="section-title" id="contact-title"><?= e($title ?? setting('contact_title')) ?></h2>
      <p class="lead"><?= e($intro ?? setting('contact_intro')) ?></p>
      <ul class="contact-direct">
        <?php if ($phone): ?><li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= icon('phone') ?><bdi dir="ltr"><?= e($phone) ?></bdi></a></li><?php endif; ?>
        <?php if ($whatsapp): ?><li><a href="https://wa.me/<?= e(preg_replace('/\D/', '', $whatsapp)) ?>" rel="noopener" target="_blank"><?= icon('whatsapp') ?><bdi dir="ltr"><?= e($whatsapp) ?></bdi></a></li><?php endif; ?>
        <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= icon('mail') ?><?= e($email) ?></a></li><?php endif; ?>
        <li><span><?= icon('map-pin') ?><?= e(setting('company_city', null, 'Sousse')) ?>, <?= e(setting('company_country', null, 'Tunisie')) ?></span></li>
      </ul>
      <?php if ($socials): ?>
      <ul class="socials" aria-label="<?= e(t('footer.follow')) ?>">
        <?php foreach ($socials as $network => $url): ?><li><a href="<?= e($url) ?>" rel="noopener" target="_blank" aria-label="<?= e(ucfirst($network)) ?>"><?= icon($network) ?></a></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>

    <form class="contact-form" method="post" action="<?= lurl('contact') ?>" novalidate data-contact-form data-anim="right"
          data-msg-sending="<?= e(t('contact.sending')) ?>" data-msg-network="<?= e(t('contact.error.network')) ?>"
          data-msg-required="<?= e(t('contact.error.required')) ?>" data-msg-email="<?= e(t('contact.error.email')) ?>"
          data-msg-message="<?= e(t('contact.error.message_short')) ?>" data-msg-consent="<?= e(t('contact.error.consent')) ?>">
      <?= csrf_field() ?>
      <?php /* Anti-spam trap: invisible to people, filled by bots. Neutral name and label so browser autofill ignores it. */ ?>
      <div class="hp" aria-hidden="true"><label for="hp-<?= e(substr(csrf_token(), 0, 6)) ?>">Ne pas remplir</label><input id="hp-<?= e(substr(csrf_token(), 0, 6)) ?>" type="text" name="hp_trap" value="" tabindex="-1" autocomplete="new-password" data-lpignore="true" data-1p-ignore data-bwignore></div>
      <p class="form-title"><?= e(t('contact.form_title')) ?></p>

      <?php if ($contactSuccess): ?><p class="form-alert is-success" role="status"><?= e($contactSuccess) ?></p><?php endif; ?>
      <?php if ($contactError): ?><p class="form-alert is-error" role="alert"><?= e($contactError) ?></p><?php endif; ?>
      <p class="form-alert" role="status" aria-live="polite" data-form-status hidden></p>

      <div class="field-row">
        <div class="field">
          <label for="f-name"><?= e(t('contact.name')) ?></label>
          <input id="f-name" name="name" type="text" autocomplete="name" required maxlength="120" value="<?= e(old('name')) ?>">
          <p class="field-error" data-error-for="name"></p>
        </div>
        <div class="field">
          <label for="f-email"><?= e(t('contact.email')) ?></label>
          <input id="f-email" name="email" type="email" autocomplete="email" required maxlength="190" dir="ltr" value="<?= e(old('email')) ?>">
          <p class="field-error" data-error-for="email"></p>
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <label for="f-phone"><?= e(t('contact.phone')) ?> <span class="optional"><?= e(t('contact.optional')) ?></span></label>
          <input id="f-phone" name="phone" type="tel" autocomplete="tel" maxlength="40" dir="ltr" value="<?= e(old('phone')) ?>">
          <p class="field-error" data-error-for="phone"></p>
        </div>
        <div class="field">
          <label for="f-service"><?= e(t('contact.service')) ?></label>
          <select id="f-service" name="service" data-select>
            <option value=""><?= e(t('contact.service_pick')) ?></option>
            <?php foreach ($services as $s): $st = $s['title'] ?? ''; ?>
            <option <?= $selected === $st ? 'selected' : '' ?>><?= e($st) ?></option>
            <?php endforeach; ?>
            <option value="<?= e(t('contact.service_other')) ?>"><?= e(t('contact.service_other')) ?></option>
          </select>
        </div>
      </div>
      <div class="field">
        <label for="f-message"><?= e(t('contact.message')) ?></label>
        <textarea id="f-message" name="message" rows="4" required minlength="10" maxlength="5000" placeholder="<?= e(t('contact.message_ph')) ?>"><?= e(old('message')) ?></textarea>
        <p class="field-error" data-error-for="message"></p>
      </div>
      <div class="field field-check">
        <input id="f-consent" name="consent" type="checkbox" value="1" required>
        <label for="f-consent"><?= t('contact.consent', ['url' => e(lurl('confidentialite'))]) ?></label>
        <p class="field-error" data-error-for="consent"></p>
      </div>
      <button class="btn btn-primary btn-submit" type="submit" data-submit>
        <span data-submit-label><?= e(t('contact.submit')) ?></span><?= icon(is_rtl() ? 'arrow-left' : 'arrow-right') ?>
      </button>
      <p class="form-note"><?= e(t('contact.response')) ?></p>
    </form>
  </div>
</section>
