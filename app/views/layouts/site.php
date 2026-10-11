<?php
/** @var string $content */
$lang = lang();
$title = isset($pageTitle) ? $pageTitle . (!empty($fullTitle) ? '' : ' | Heaven of Web') : setting('meta_title');
$description = ($pageDescription ?? '') ?: setting('meta_description');
$canonical = absolute_url(lurl($currentPath ?? ''));
$og = $ogImage ?? (setting('img_og') ? absolute_url(setting('img_og')) : null);
$nonce = csp_nonce();
$socials = array_filter([
    'facebook' => setting('social_facebook'),
    'instagram' => setting('social_instagram'),
    'linkedin' => setting('social_linkedin'),
    'x' => setting('social_x'),
]);
?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php foreach ($alternates ?? [] as $l => $href): ?>
<link rel="alternate" hreflang="<?= $l ?>" href="<?= e(absolute_url($href)) ?>">
<?php endforeach; ?>
<?php if (!empty($alternates)): ?><link rel="alternate" hreflang="x-default" href="<?= e(absolute_url($alternates['fr'])) ?>"><?php endif; ?>

<meta property="og:type" content="website">
<meta property="og:site_name" content="Heaven of Web">
<meta property="og:locale" content="<?= ['fr' => 'fr_FR', 'en' => 'en_GB', 'ar' => 'ar_AR'][$lang] ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($og): ?><meta property="og:image" content="<?= e($og) ?>"><meta name="twitter:image" content="<?= e($og) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">

<meta name="theme-color" content="#041C32">
<meta name="color-scheme" content="light">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/assets/img/icons/icon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/icons/apple-touch-icon.png">

<?php if ($lang === 'ar'): ?>
<link rel="preload" href="/assets/fonts/cairo-arabic-700.woff2" as="font" type="font/woff2" crossorigin>
<?php else: ?>
<link rel="preload" href="/assets/fonts/poppins-latin-700.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/poppins-latin-400.woff2" as="font" type="font/woff2" crossorigin>
<?php endif; ?>
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">

<script nonce="<?= $nonce ?>">
  // JS is on: hide animated elements until they reveal. The loading screen only plays on the first page of a visit.
  (function () {
    var d = document.documentElement;
    d.classList.add('js');
    try { if (sessionStorage.getItem('how-loaded')) d.classList.add('no-preload'); } catch (e) {}
  })();
</script>
<?php if (!empty($isHome)): ?>
<script type="application/ld+json" nonce="<?= $nonce ?>"><?= json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => 'Heaven of Web',
    'url' => absolute_url(lurl()),
    'logo' => absolute_url('assets/img/brand/logo-dark.png'),
    'image' => $og,
    'description' => setting('meta_description'),
    'email' => setting('contact_email') ?: null,
    'telephone' => setting('contact_phone') ?: null,
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => setting('company_city', null, 'Sousse'), 'addressCountry' => 'TN'],
    'areaServed' => [['@type' => 'Country', 'name' => 'Tunisia'], ['@type' => 'Country', 'name' => 'France']],
    'knowsLanguage' => ['fr', 'en', 'ar'],
    'sameAs' => array_values($socials) ?: null,
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endif; ?>
<?php foreach ($jsonLd ?? [] as $ld): ?>
<script type="application/ld+json" nonce="<?= $nonce ?>"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body class="<?= !empty($isHome) ? 'is-home' : 'is-page' ?><?= !empty($hasHero) ? ' has-hero' : '' ?>">
<div class="preloader" data-preloader aria-hidden="true">
  <div class="preloader-mark">
    <svg class="preloader-ring" viewBox="0 0 120 120"><circle cx="60" cy="60" r="54"/></svg>
    <img src="<?= e(media(setting('img_logo_light'))) ?>" alt="" width="60" height="45">
  </div>
  <p class="preloader-name">Heaven <span>of</span> Web</p>
  <span class="preloader-bar"><span></span></span>
</div>
<a class="skip-link" href="#main"><?= e(t('nav.skip')) ?></a>
<?php partial('partials/header', ['isHome' => !empty($isHome), 'alternates' => $alternates ?? []]); ?>
<main id="main" tabindex="-1">
<?= $content ?>
</main>
<?php partial('partials/footer', ['socials' => $socials, 'alternates' => $alternates ?? []]); ?>
<?php partial('partials/floating'); ?>

<script src="<?= asset('vendor/lenis.min.js') ?>" defer></script>
<script src="<?= asset('js/site.js') ?>" defer></script>
<?php if (!empty($isHome)): ?>
<script type="module" src="<?= asset('js/globe.js') ?>"></script>
<?php endif; ?>
</body>
</html>
