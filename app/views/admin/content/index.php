<?php
$imageField = array_key_first($c['images']);
$titleOf = static fn(array $r) => match ($type) {
    'projects' => ($r['title'] ?? '') ?: $r['client'],
    'reviews' => $r['author'],
    default => $r['title'] ?? '(sans titre)',
};
$subOf = static fn(array $r) => match ($type) {
    'projects' => trim(($r['category'] ?? '') . ($r['featured'] ? ' · mis en avant' : '')),
    'reviews' => trim(($r['company'] ? $r['company'] . ' · ' : '') . str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating'])),
    default => str_excerpt($r['text'] ?? '', 90),
};
?>
<div class="toolbar">
  <p class="muted"><?= count($rows) ?> élément<?= count($rows) > 1 ? 's' : '' ?> · glissez <?= icon('grip', 'icon icon-inline') ?> pour changer l’ordre d’affichage sur le site.</p>
  <a class="btn btn-primary" href="/admin/<?= $type ?>/new"><?= icon('plus') ?><?= e($c['new']) ?></a>
</div>

<section class="card card-flush">
  <?php if (!$rows): ?>
    <p class="empty empty-lg">
      <?= $type === 'reviews' ? 'Aucun avis pour l’instant. Ajoutez les retours de vos clients : la section « Avis » apparaîtra sur le site dès le premier avis publié.' : 'Rien ici pour l’instant.' ?>
      <br><a class="btn btn-primary" href="/admin/<?= $type ?>/new"><?= icon('plus') ?><?= e($c['new']) ?></a>
    </p>
  <?php else: ?>
  <ul class="sortable" data-sortable="/admin/<?= $type ?>/reorder">
    <?php foreach ($rows as $r): ?>
    <li class="row <?= $r['published'] ? '' : 'is-draft' ?>" data-id="<?= (int) $r['id'] ?>" draggable="true">
      <span class="handle" aria-hidden="true"><?= icon('grip') ?></span>
      <?php if ($imageField): ?>
        <?php if (!empty($r[$imageField])): ?>
          <img class="thumb <?= $imageField === 'avatar' ? 'thumb-round' : '' ?>" src="<?= e(media($r[$imageField], 480)) ?>" alt="" loading="lazy">
        <?php else: ?>
          <span class="thumb thumb-empty <?= $imageField === 'avatar' ? 'thumb-round' : '' ?>"><?= e(mb_strtoupper(mb_substr($titleOf($r), 0, 1))) ?></span>
        <?php endif; ?>
      <?php else: ?>
        <span class="thumb thumb-icon"><?= icon($r['icon'] ?? 'dot') ?></span>
      <?php endif; ?>
      <a class="row-main" href="/admin/<?= $type ?>/<?= (int) $r['id'] ?>">
        <strong><?= e($titleOf($r)) ?></strong>
        <small><?= e($subOf($r)) ?></small>
      </a>
      <form method="post" action="/admin/<?= $type ?>/<?= (int) $r['id'] ?>/toggle" data-toggle-form>
        <?= csrf_field() ?>
        <button class="switch" type="submit" role="switch" aria-checked="<?= $r['published'] ? 'true' : 'false' ?>" title="<?= $r['published'] ? 'Publié — cliquer pour masquer' : 'Masqué — cliquer pour publier' ?>">
          <span class="sr-only">Publié</span>
        </button>
      </form>
      <a class="icon-btn" href="/admin/<?= $type ?>/<?= (int) $r['id'] ?>" title="Modifier"><?= icon('edit') ?><span class="sr-only">Modifier</span></a>
      <form method="post" action="/admin/<?= $type ?>/<?= (int) $r['id'] ?>/delete" data-confirm="Supprimer « <?= e($titleOf($r)) ?> » ? Cette action est irréversible.">
        <?= csrf_field() ?>
        <button class="icon-btn icon-btn-danger" type="submit" title="Supprimer"><?= icon('trash') ?><span class="sr-only">Supprimer</span></button>
      </form>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
