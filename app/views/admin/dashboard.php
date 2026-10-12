<?php
$delta = static function (int $now, int $prev): string {
    if ($prev === 0) return $now > 0 ? '<span class="delta is-up">nouveau</span>' : '';
    $pct = (int) round(($now - $prev) / $prev * 100);
    $cls = $pct > 0 ? 'is-up' : ($pct < 0 ? 'is-down' : '');
    return '<span class="delta ' . $cls . '">' . ($pct > 0 ? '+' : '') . $pct . ' %</span>';
};
$bars = static function (array $rows, string $empty, ?callable $label = null): string {
    if (!$rows) return '<p class="empty">' . e($empty) . '</p>';
    $max = max(array_column($rows, 'n')) ?: 1;
    $html = '<ul class="barlist">';
    foreach ($rows as $r) {
        $text = $label ? $label($r['label']) : $r['label'];
        $html .= '<li><span class="barlist-label" title="' . e($text) . '">' . e($text) . '</span><span class="barlist-value">' . (int) $r['n'] . '</span>'
            . '<span class="barlist-bar" style="--w:' . round($r['n'] / $max * 100, 1) . '%"></span></li>';
    }
    return $html . '</ul>';
};
$sectionNames = ['about' => 'À propos', 'services' => 'Services', 'process' => 'Processus', 'why' => 'Engagements', 'projects' => 'Réalisations', 'reviews' => 'Avis', 'contact' => 'Contact'];
$langNames = ['fr' => 'Français', 'en' => 'Anglais', 'ar' => 'Arabe', '—' => 'Inconnue'];
?>
<div class="toolbar">
  <p class="muted">Visiteurs uniques comptés sans cookie, par jour.</p>
  <div class="segmented" role="group" aria-label="Période">
    <?php foreach ([7 => '7 jours', 30 => '30 jours', 90 => '3 mois', 365 => '12 mois'] as $d => $label): ?>
    <a href="/admin?range=<?= $d ?>" <?= $days === $d ? 'aria-current="true"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
  </div>
</div>

<section class="kpis" aria-label="Chiffres clés">
  <div class="kpi">
    <p class="kpi-label">Visiteurs</p>
    <p class="kpi-value"><?= number_format($stats['visitors'], 0, ',', ' ') ?></p>
    <p class="kpi-foot"><?= $delta($stats['visitors'], $stats['prevVisitors']) ?> vs période précédente</p>
  </div>
  <div class="kpi">
    <p class="kpi-label">Pages vues</p>
    <p class="kpi-value"><?= number_format($stats['views'], 0, ',', ' ') ?></p>
    <p class="kpi-foot"><?= $delta($stats['views'], $stats['prevViews']) ?> vs période précédente</p>
  </div>
  <div class="kpi">
    <p class="kpi-label">Demandes reçues</p>
    <p class="kpi-value"><?= $stats['contacts'] ?></p>
    <p class="kpi-foot"><a href="/admin/messages"><?= $unreadCount ?> non lue<?= $unreadCount > 1 ? 's' : '' ?></a></p>
  </div>
  <div class="kpi">
    <p class="kpi-label">Taux de contact</p>
    <p class="kpi-value"><?= str_replace('.', ',', (string) $stats['conversion']) ?> %</p>
    <p class="kpi-foot">demandes / visiteurs</p>
  </div>
  <div class="kpi">
    <p class="kpi-label">Aujourd’hui</p>
    <p class="kpi-value"><?= (int) $today['visitors'] ?></p>
    <p class="kpi-foot"><?= (int) $today['views'] ?> pages vues</p>
  </div>
</section>

<section class="card">
  <header class="card-head">
    <h2>Fréquentation</h2>
    <details class="table-toggle"><summary>Voir en tableau</summary>
      <div class="table-scroll"><table class="table table-compact">
        <thead><tr><th>Jour</th><th>Visiteurs</th><th>Pages vues</th></tr></thead>
        <tbody><?php foreach (array_reverse($stats['series']) as $s): ?><tr><td><?= date('d/m/Y', strtotime($s['day'])) ?></td><td><?= $s['visitors'] ?></td><td><?= $s['views'] ?></td></tr><?php endforeach; ?></tbody>
      </table></div>
    </details>
  </header>
  <div class="chart-wrap"><canvas id="traffic-chart" aria-label="Visiteurs et pages vues par jour" role="img"></canvas></div>
  <script type="application/json" id="traffic-data"><?= json_encode($stats['series'], JSON_HEX_TAG) ?></script>
</section>

<div class="grid-2">
  <section class="card">
    <h2>Pages les plus vues</h2>
    <?= $bars($stats['pages'], 'Pas encore de visite sur la période.') ?>
  </section>
  <section class="card">
    <h2>Sections lues sur l’accueil</h2>
    <?= $bars($stats['sections'], 'Pas encore de données.', fn($l) => $sectionNames[$l] ?? $l) ?>
  </section>
  <section class="card">
    <h2>Provenance</h2>
    <?= $bars($stats['referrers'], 'Aucun site référent : les visites viennent d’un accès direct ou de favoris.') ?>
  </section>
  <section class="card">
    <h2>Langue de visite</h2>
    <?= $bars($stats['langs'], 'Pas encore de données.', fn($l) => $langNames[$l] ?? $l) ?>
  </section>
  <section class="card">
    <h2>Appareils</h2>
    <?= $bars($stats['devices'], 'Pas encore de données.') ?>
  </section>
  <section class="card">
    <h2>Navigateurs</h2>
    <?= $bars($stats['browsers'], 'Pas encore de données.') ?>
  </section>
</div>

<?php
$countryName = static function (string $code): string {
    if (class_exists(Locale::class)) {
        $n = Locale::getDisplayRegion('-' . $code, 'fr');
        if ($n !== '' && $n !== $code) return $n;
    }
    return $code;
};
$known = max(1, (int) $stats['countryKnown']);
?>
<section class="card countries-card">
  <header class="card-head">
    <h2>Pays des visiteurs</h2>
    <span class="muted small-note"><?= (int) $stats['countryKnown'] ?> visiteur<?= $stats['countryKnown'] > 1 ? 's' : '' ?> localisé<?= $stats['countryKnown'] > 1 ? 's' : '' ?> sur <?= (int) $stats['visitors'] ?></span>
  </header>
  <?php if ($stats['countries']): ?>
  <ol class="country-list">
    <?php foreach ($stats['countries'] as $i => $c): $pct = round($c['n'] / $known * 100); ?>
    <li>
      <span class="country-rank"><?= $i + 1 ?></span>
      <span class="country-code"><?= e($c['label']) ?></span>
      <span class="country-name"><?= e($countryName($c['label'])) ?></span>
      <span class="country-bar" aria-hidden="true"><span style="--w: <?= $pct ?>%"></span></span>
      <span class="country-num"><strong><?= (int) $c['n'] ?></strong><small><?= $pct ?> %</small></span>
    </li>
    <?php endforeach; ?>
  </ol>
  <?php elseif (!App\Core\GeoIp::available()): ?>
  <p class="empty">La base de géolocalisation n’est pas installée. Lancez <code>php app/cli/update-geoip.php</code> sur le serveur (une fois, puis chaque mois).</p>
  <?php else: ?>
  <p class="empty">Pas encore de visite localisée sur la période. Les pays apparaissent dès les prochaines visites.</p>
  <?php endif; ?>
  <p class="geo-credit">Pays déduit de l’adresse IP au moment de la visite (l’adresse n’est pas conservée) · <a href="https://db-ip.com" target="_blank" rel="noopener">IP Geolocation by DB-IP</a></p>
</section>

<div class="grid-2 grid-wide-left">
  <section class="card">
    <header class="card-head"><h2>Derniers messages</h2><a class="link" href="/admin/messages">Tous les messages</a></header>
    <?php if (!$latest): ?>
      <p class="empty">Aucun message pour l’instant. Les demandes envoyées depuis le formulaire de contact apparaîtront ici.</p>
    <?php else: ?>
    <ul class="msg-list">
      <?php foreach ($latest as $m): ?>
      <li class="<?= $m['status'] === 'new' ? 'is-unread' : '' ?>">
        <a href="/admin/messages/<?= $m['id'] ?>">
          <span class="msg-from"><?= e($m['name']) ?><?php if ($m['service']): ?><small><?= e($m['service']) ?></small><?php endif; ?></span>
          <span class="msg-excerpt"><?= e(str_excerpt($m['message'], 90)) ?></span>
          <time datetime="<?= e($m['created_at']) ?>"><?= e(time_ago($m['created_at'])) ?></time>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
  <section class="card">
    <h2>Sur le site</h2>
    <ul class="mini-stats">
      <li><a href="/admin/projects"><strong><?= $counts['projects'] ?></strong> réalisations publiées</a></li>
      <li><a href="/admin/reviews"><strong><?= $counts['reviews'] ?></strong> avis publiés<?= $counts['rating'] ? ' · ' . str_replace('.', ',', $counts['rating']) . '/5' : '' ?></a></li>
      <li><a href="/admin/notifications"><strong><?= icon('bell') ?></strong> Notifications sur ce téléphone</a></li>
    </ul>
  </section>
</div>
