<?php
use App\Core\I18n;

$base = $isHome ? '' : lurl();
$path = $currentPath ?? '';
$links = [
    'top' => t('nav.home'),
    'about' => t('nav.about'),
    'services' => t('nav.services'),
    'projects' => t('nav.projects'),
    'contact' => t('nav.contact'),
];
// On inner pages, highlight the section the page belongs to.
$pageSection = match (true) {
    str_starts_with($path, 'realisations') => 'projects',
    str_starts_with($path, 'services/') => 'services',
    default => null,
};
$href = static fn(string $id) => $id === 'projects' && !$isHome ? lurl('realisations') : $base . '#' . $id;
$arrow = is_rtl() ? 'arrow-left' : 'arrow-right';
?>
<header class="site-header" data-header>
  <div class="container header-inner">
    <a class="brand" href="<?= lurl() ?>" aria-label="Heaven of Web — <?= e(t('nav.home')) ?>">
      <img class="logo-on-light" src="<?= e(media(setting('img_logo'))) ?>" alt="" width="48" height="36">
      <img class="logo-on-dark" src="<?= e(media(setting('img_logo_light'))) ?>" alt="" width="48" height="36">
      <span class="brand-name">Heaven <span>of</span> Web</span>
    </a>

    <nav class="main-nav" aria-label="<?= e(t('nav.label')) ?>">
      <ul>
        <?php foreach ($links as $id => $label): ?>
        <li><a href="<?= e($href($id)) ?>" data-nav-link="<?= $id ?>"<?= $pageSection === $id ? ' class="is-active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="header-tools">
      <details class="lang-menu" data-lang-menu>
        <summary aria-label="<?= e(t('lang.label')) ?>"><?= icon('globe') ?><span><?= strtoupper(lang()) ?></span></summary>
        <ul>
          <?php foreach (I18n::LANGS as $l): ?>
          <li><a href="<?= e($alternates[$l] ?? lurl('', $l)) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>" <?= $l === lang() ? 'aria-current="true"' : '' ?>><?= e(I18n::NAMES[$l]) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </details>
      <a class="btn btn-primary btn-sm header-cta" href="<?= $base ?>#contact"><?= e(t('nav.cta')) ?><?= icon($arrow) ?></a>
      <button class="menu-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false" data-menu-open>
        <?= icon('grid') ?><span class="sr-only"><?= e(t('nav.menu')) ?></span>
      </button>
    </div>
  </div>
</header>

<?php
// Mobile menu: a bottom sheet with icon tiles (shown below 1024px).
$phone = setting('contact_phone');
$wa = preg_replace('/\D/', '', setting('contact_whatsapp'));
$hasReviews = (int) App\Core\DB::value('SELECT COUNT(*) FROM reviews WHERE published = 1') > 0;
$tiles = [
    ['top', t('nav.home'), 'home', $href('top')],
    ['about', t('nav.about'), 'user', $href('about')],
    ['services', t('nav.services'), 'briefcase', $href('services')],
    ['process', t('menu.process'), 'list', $base . '#process'],
    ['projects', t('nav.tab_projects'), 'image', $href('projects')],
    ['why', t('menu.why'), 'award', $base . '#why'],
];
$tiles[] = $hasReviews ? ['reviews', t('menu.reviews'), 'star', $base . '#reviews'] : ['sitemap', t('sitemap.title'), 'layers', lurl('plan-du-site')];
$tiles[] = ['contact', t('nav.contact'), 'send', $href('contact')];
if ($phone) $tiles[] = ['call', t('menu.call'), 'phone', 'tel:' . preg_replace('/[^0-9+]/', '', $phone)];
elseif ($wa !== '') $tiles[] = ['whatsapp', 'WhatsApp', 'whatsapp', 'https://wa.me/' . $wa];
else $tiles[] = ['email', t('menu.email'), 'mail', 'mailto:' . setting('contact_email')];
?>
<div class="menu-sheet" id="mobile-menu" role="dialog" aria-modal="true" aria-label="<?= e(t('nav.label')) ?>" data-menu hidden>
  <div class="menu-backdrop" data-menu-close></div>
  <div class="menu-panel" data-menu-panel>
    <span class="menu-handle" aria-hidden="true" data-menu-handle></span>
    <div class="menu-head">
      <a class="brand" href="<?= lurl() ?>">
        <img src="<?= e(media(setting('img_logo_light'))) ?>" alt="" width="44" height="33">
        <span class="brand-name">Heaven <span>of</span> Web</span>
      </a>
      <button class="menu-close" type="button" data-menu-close aria-label="<?= e(t('menu.close')) ?>"><?= icon('close') ?></button>
    </div>
    <nav aria-label="<?= e(t('nav.label')) ?>">
      <ul class="menu-grid">
        <?php foreach ($tiles as $i => [$id, $label, $ic, $url]): ?>
        <li style="--i: <?= $i ?>">
          <a href="<?= e($url) ?>" data-nav-link="<?= e($id) ?>"<?= $pageSection === $id ? ' class="is-active" aria-current="page"' : '' ?><?= str_starts_with($url, 'https://') ? ' target="_blank" rel="noopener"' : '' ?>>
            <?= icon($ic) ?><span><?= e($label) ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <ul class="menu-langs" aria-label="<?= e(t('lang.label')) ?>">
      <?php foreach (App\Core\I18n::LANGS as $l): ?>
      <li><a href="<?= e($alternates[$l] ?? lurl('', $l)) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>" <?= $l === lang() ? 'aria-current="true"' : '' ?>><?= e(App\Core\I18n::NAMES[$l]) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <a class="menu-cta" href="<?= $base ?>#contact" data-menu-link><?= e(t('menu.cta')) ?><?= icon($arrow) ?></a>
  </div>
</div>
