<!doctype html>
<html lang="fr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($pageTitle ?? 'Connexion') ?> · Heaven of Web</title>
<meta name="theme-color" content="#041C32">
<link rel="manifest" href="/admin.webmanifest">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="apple-touch-icon" href="/assets/img/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<script nonce="<?= csp_nonce() ?>">
  (function () {
    var t = null;
    try { t = localStorage.getItem('how-theme'); } catch (e) {}
    if (t !== 'light' && t !== 'dark') t = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    document.documentElement.dataset.theme = t;
  })();
</script>
</head>
<body class="auth-body">
<main class="auth-card">
  <img class="auth-logo auth-logo-dark" src="<?= e(media(setting('img_logo_full_light'))) ?>" alt="Heaven of Web" width="120" height="86">
  <img class="auth-logo auth-logo-light" src="/assets/img/brand/logo-dark-sm.webp" alt="Heaven of Web" width="120" height="86">
  <?= $content ?>
</main>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
