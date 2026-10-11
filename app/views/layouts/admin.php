<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);
$is = static fn(string $prefix) => $prefix === '/admin' ? $path === '/admin' : str_starts_with($path, $prefix);
$nav = [
    ['/admin', 'Tableau de bord', 'chart'],
    ['/admin/messages', 'Messages', 'inbox'],
    ['/admin/projects', 'Réalisations', 'layers'],
    ['/admin/reviews', 'Avis clients', 'quote'],
    ['/admin/services', 'Services', 'browser'],
    ['/admin/features', 'Engagements', 'award'],
    ['/admin/settings', 'Contenus & images', 'text'],
    ['/admin/notifications', 'Notifications', 'bell'],
    ['/admin/account', 'Compte', 'user'],
];
$toast = flash('toast');
$toastType = flash('toast_type') ?? 'success';
$nonce = csp_nonce();
?>
<!doctype html>
<html lang="fr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($pageTitle ?? 'Administration') ?> · Heaven of Web</title>
<meta name="theme-color" content="#041C32">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="manifest" href="/admin.webmanifest">
<link rel="icon" href="/favicon.ico?v=2" sizes="32x32">
<link rel="icon" href="<?= asset('img/icons/favicon-32.png') ?>" type="image/png" sizes="32x32">
<link rel="icon" href="<?= asset('img/icons/icon-192.png') ?>" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="<?= asset('img/icons/apple-touch-icon.png') ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="HoW Admin">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<script nonce="<?= $nonce ?>">
  (function () {
    var t = null;
    try { t = localStorage.getItem('how-theme'); } catch (e) {}
    if (t !== 'light' && t !== 'dark') t = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    document.documentElement.dataset.theme = t;
  })();
</script>
</head>
<body>
<a class="skip-link" href="#admin-main">Aller au contenu</a>
<div class="admin-shell">
  <aside class="sidebar" id="sidebar" data-sidebar>
    <a class="sidebar-brand" href="/admin">
      <img src="<?= e(media(setting('img_logo_light'))) ?>" alt="" width="44" height="33">
      <span>Heaven <em>of</em> Web</span>
    </a>
    <nav aria-label="Administration">
      <ul>
        <?php foreach ($nav as [$href, $label, $ic]): ?>
        <li>
          <a href="<?= $href ?>" <?= $is($href) ? 'aria-current="page"' : '' ?>>
            <?= icon($ic) ?><span><?= e($label) ?></span>
            <?php if ($href === '/admin/messages'): ?><span class="badge" data-unread <?= $unreadCount ? '' : 'hidden' ?>><?= (int) $unreadCount ?></span><?php endif; ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="sidebar-foot">
      <a class="sidebar-link" href="/fr/" target="_blank" rel="noopener"><?= icon('external') ?><span>Voir le site</span></a>
      <form method="post" action="/admin/logout"><?= csrf_field() ?><button class="sidebar-link" type="submit"><?= icon('logout') ?><span>Se déconnecter</span></button></form>
    </div>
  </aside>
  <div class="sidebar-scrim" data-sidebar-close></div>

  <div class="admin-main">
    <header class="topbar">
      <button class="icon-btn menu-btn" type="button" data-sidebar-open aria-controls="sidebar" aria-expanded="false"><?= icon('menu') ?><span class="sr-only">Menu</span></button>
      <h1 class="topbar-title"><?= e($pageTitle ?? '') ?></h1>
      <div class="topbar-tools">
        <button class="icon-btn" type="button" data-theme-toggle aria-label="Basculer le thème clair ou sombre">
          <span class="theme-icon theme-icon-dark"><?= icon('moon') ?></span><span class="theme-icon theme-icon-light"><?= icon('sun') ?></span>
        </button>
        <span class="avatar" title="<?= e($adminUser['name']) ?>"><?= e(mb_strtoupper(mb_substr($adminUser['name'], 0, 1))) ?></span>
      </div>
    </header>
    <main id="admin-main" class="admin-content" tabindex="-1">
      <?= $content ?>
    </main>
  </div>

  <nav class="tabbar" aria-label="Navigation rapide">
    <a href="/admin" <?= $is('/admin') ? 'aria-current="page"' : '' ?>><?= icon('chart') ?><span>Stats</span></a>
    <a href="/admin/messages" <?= $is('/admin/messages') ? 'aria-current="page"' : '' ?>><?= icon('inbox') ?><span>Messages</span><span class="badge" data-unread <?= $unreadCount ? '' : 'hidden' ?>><?= (int) $unreadCount ?></span></a>
    <a href="/admin/projects" <?= $is('/admin/projects') ? 'aria-current="page"' : '' ?>><?= icon('layers') ?><span>Projets</span></a>
    <a href="/admin/reviews" <?= $is('/admin/reviews') ? 'aria-current="page"' : '' ?>><?= icon('quote') ?><span>Avis</span></a>
    <button type="button" data-sidebar-open><?= icon('menu') ?><span>Plus</span></button>
  </nav>
</div>

<?php if ($toast): ?>
<div class="toast is-<?= e($toastType) ?>" role="status" data-toast><?= nl2br(e($toast)) ?></div>
<?php endif; ?>
<div class="toast" role="status" data-live-toast hidden></div>

<?php if (!empty($loadCharts)): ?><script src="<?= asset('vendor/chart.umd.min.js') ?>" defer></script><?php endif; ?>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
