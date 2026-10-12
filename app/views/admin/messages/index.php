<?php
use App\Controllers\Admin\MessageController;

$tabs = [
    'inbox' => ['Boîte de réception', ($counts['new'] ?? 0) + ($counts['read'] ?? 0) + ($counts['replied'] ?? 0)],
    'new' => ['Non lus', $counts['new'] ?? 0],
    'replied' => ['Répondus', $counts['replied'] ?? 0],
    'archived' => ['Archivés', $counts['archived'] ?? 0],
    'spam' => ['Spam', $counts['spam'] ?? 0],
];
$qs = static fn(array $p) => '/admin/messages?' . http_build_query(array_filter($p + ['status' => $status, 'q' => $q], fn($v) => $v !== '' && $v !== null));
?>
<div class="toolbar">
  <nav class="tabs" aria-label="Dossiers">
    <?php foreach ($tabs as $key => [$label, $n]): ?>
    <a href="<?= e($qs(['status' => $key, 'page' => null])) ?>" <?= $status === $key ? 'aria-current="page"' : '' ?>><?= $label ?> <span class="count"><?= (int) $n ?></span></a>
    <?php endforeach; ?>
  </nav>
  <div class="toolbar-actions">
    <form class="search" method="get" action="/admin/messages" role="search">
      <input type="hidden" name="status" value="<?= e($status) ?>">
      <label class="sr-only" for="msg-q">Rechercher</label>
      <?= icon('search') ?>
      <input id="msg-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, email, texte…">
    </form>
    <a class="btn btn-ghost btn-sm" href="<?= e('/admin/messages/export?' . http_build_query(['status' => $status, 'q' => $q])) ?>"><?= icon('download') ?>Exporter CSV</a>
  </div>
</div>

<section class="card card-flush">
  <?php if (!$messages): ?>
    <p class="empty empty-lg">
      <?= $q !== '' ? 'Aucun message ne correspond à « ' . e($q) . ' ».' : ($status === 'inbox' ? 'Aucun message pour l’instant. Les demandes du formulaire de contact arriveront ici, et une notification sera envoyée sur vos appareils abonnés.' : 'Ce dossier est vide.') ?>
    </p>
  <?php else:
    $inTrash = in_array($status, ['archived', 'spam'], true); ?>
  <form method="post" action="/admin/messages/bulk" data-bulk>
    <?= csrf_field() ?>
    <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/admin/messages') ?>">
    <div class="bulk-bar" data-bulk-bar>
      <label class="bulk-check" title="Tout sélectionner">
        <input type="checkbox" data-bulk-all aria-label="Tout sélectionner">
      </label>
      <span class="bulk-count" data-bulk-count aria-live="polite">Tout sélectionner</span>
      <div class="bulk-actions" data-bulk-actions>
        <button class="bulk-btn" type="submit" name="action" value="read"><?= icon('eye') ?><span>Lu</span></button>
        <button class="bulk-btn" type="submit" name="action" value="new"><?= icon('mail') ?><span>Non lu</span></button>
        <?php if ($inTrash): ?>
        <button class="bulk-btn" type="submit" name="action" value="read"><?= icon('inbox') ?><span>Restaurer</span></button>
        <?php else: ?>
        <button class="bulk-btn" type="submit" name="action" value="archived"><?= icon('archive') ?><span>Archiver</span></button>
        <button class="bulk-btn" type="submit" name="action" value="spam"><?= icon('spam') ?><span>Spam</span></button>
        <?php endif; ?>
        <span class="bulk-sep" aria-hidden="true"></span>
        <button class="bulk-btn bulk-danger" type="submit" name="action" value="delete" data-bulk-confirm="Supprimer définitivement les messages sélectionnés ? Cette action est irréversible."><?= icon('trash') ?><span>Supprimer</span></button>
        <button class="bulk-close" type="button" data-bulk-clear aria-label="Annuler la sélection"><?= icon('close') ?></button>
      </div>
    </div>
    <ul class="msg-list msg-list-full msg-list-select">
      <?php foreach ($messages as $m): ?>
      <li class="<?= $m['status'] === 'new' ? 'is-unread' : '' ?>" data-bulk-row>
        <label class="row-check"><input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" data-bulk-item aria-label="Sélectionner le message de <?= e($m['name']) ?>"></label>
        <a href="/admin/messages/<?= $m['id'] ?>">
          <span class="msg-from">
            <?= e($m['name']) ?>
            <small><?= e($m['company'] ?: $m['email']) ?></small>
          </span>
          <span class="msg-excerpt">
            <?php if ($m['service']): ?><span class="tag"><?= e($m['service']) ?></span><?php endif; ?>
            <?= e(str_excerpt($m['message'], 140)) ?>
          </span>
          <span class="msg-meta">
            <?php if ($m['status'] !== 'new' && $m['status'] !== 'read'): ?><span class="status status-<?= e($m['status']) ?>"><?= e(MessageController::STATUSES[$m['status']]) ?></span><?php endif; ?>
            <time datetime="<?= e($m['created_at']) ?>" title="<?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?>"><?= e(time_ago($m['created_at'])) ?></time>
          </span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </form>
  <?php endif; ?>
</section>

<?php if ($pages > 1): ?>
<nav class="pagination" aria-label="Pages">
  <?php if ($page > 1): ?><a class="btn btn-ghost btn-sm" href="<?= e($qs(['page' => $page - 1])) ?>"><?= icon('chevron-left') ?>Plus récents</a><?php endif; ?>
  <span class="muted">Page <?= $page ?> sur <?= $pages ?> · <?= $total ?> messages</span>
  <?php if ($page < $pages): ?><a class="btn btn-ghost btn-sm" href="<?= e($qs(['page' => $page + 1])) ?>">Plus anciens<?= icon('chevron-right') ?></a><?php endif; ?>
</nav>
<?php endif; ?>
