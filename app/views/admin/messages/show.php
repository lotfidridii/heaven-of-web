<?php
use App\Controllers\Admin\MessageController;

$details = array_filter([
    'Email' => '<a href="mailto:' . e($m['email']) . '">' . e($m['email']) . '</a>',
    'Téléphone' => $m['phone'] ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $m['phone'])) . '">' . e($m['phone']) . '</a>' : null,
    'Entreprise' => $m['company'] ? e($m['company']) : null,
    'Service' => $m['service'] ? e($m['service']) : null,
    'Budget' => $m['budget'] ? e($m['budget']) : null,
    'Langue du site' => ['fr' => 'Français', 'en' => 'Anglais', 'ar' => 'Arabe'][$m['lang']] ?? e($m['lang']),
    'Reçu le' => e(date('d/m/Y à H:i', strtotime($m['created_at']))),
]);
$statusAction = static fn(string $status, string $label, string $ic, string $cls = 'btn-ghost') =>
    '<form method="post" action="/admin/messages/' . $m['id'] . '/status">' . csrf_field()
    . '<input type="hidden" name="status" value="' . $status . '"><button class="btn ' . $cls . ' btn-sm" type="submit">' . icon($ic) . $label . '</button></form>';
?>
<div class="toolbar">
  <a class="back" href="/admin/messages"><?= icon('arrow-left') ?>Messages</a>
  <div class="toolbar-actions">
    <?php if ($neighbours['prev']): ?><a class="icon-btn" href="/admin/messages/<?= (int) $neighbours['prev'] ?>" title="Message plus récent"><?= icon('chevron-left') ?><span class="sr-only">Message plus récent</span></a><?php endif; ?>
    <?php if ($neighbours['next']): ?><a class="icon-btn" href="/admin/messages/<?= (int) $neighbours['next'] ?>" title="Message plus ancien"><?= icon('chevron-right') ?><span class="sr-only">Message plus ancien</span></a><?php endif; ?>
  </div>
</div>

<div class="grid-2 grid-wide-left">
  <div class="stack-lg">
    <article class="card message">
      <header class="message-head">
        <span class="avatar avatar-lg" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($m['name'], 0, 1))) ?></span>
        <div>
          <h2><?= e($m['name']) ?></h2>
          <p class="muted"><?= e($m['email']) ?> · <?= e(time_ago($m['created_at'])) ?></p>
        </div>
        <span class="status status-<?= e($m['status']) ?>"><?= e(MessageController::STATUSES[$m['status']]) ?></span>
      </header>
      <div class="message-body" dir="auto"><?= nl2br(e($m['message'])) ?></div>
    </article>

    <?php foreach ($replies as $r): ?>
    <article class="card reply <?= $r['sent_ok'] ? '' : 'is-failed' ?>">
      <header class="message-head">
        <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($r['admin_name'] ?? 'H', 0, 1))) ?></span>
        <div>
          <h3><?= e($r['subject']) ?></h3>
          <p class="muted">Réponse de <?= e($r['admin_name'] ?? 'un administrateur') ?> · <?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></p>
        </div>
        <?php if (!$r['sent_ok']): ?><span class="status status-spam">Non envoyé</span><?php endif; ?>
      </header>
      <div class="message-body" dir="auto"><?= nl2br(e($r['body'])) ?></div>
      <?php if (!$r['sent_ok'] && $r['error']): ?><p class="alert is-error">Erreur SMTP : <?= e($r['error']) ?></p><?php endif; ?>
    </article>
    <?php endforeach; ?>

    <section class="card" id="reply">
      <h2>Répondre à <?= e($m['name']) ?></h2>
      <form method="post" action="/admin/messages/<?= $m['id'] ?>/reply" class="stack" data-reply-form>
        <?= csrf_field() ?>
        <div class="field">
          <label for="r-subject">Objet</label>
          <input id="r-subject" name="subject" type="text" required maxlength="255" value="<?= e($defaultSubject) ?>">
        </div>
        <div class="field">
          <label for="r-body">Message</label>
          <textarea id="r-body" name="body" rows="10" required dir="auto"><?= e($greeting . $signature) ?></textarea>
          <p class="help">Envoyé à <?= e($m['email']) ?> depuis <?= e(config('mail.from')) ?>. Le message d’origine est cité en dessous.</p>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><?= icon('send') ?>Envoyer la réponse</button>
          <a class="btn btn-ghost" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode($defaultSubject) ?>"><?= icon('mail') ?>Ouvrir dans ma messagerie</a>
        </div>
      </form>
    </section>
  </div>

  <aside class="stack-lg">
    <section class="card">
      <h2>Coordonnées</h2>
      <dl class="details">
        <?php foreach ($details as $label => $value): ?><div><dt><?= e($label) ?></dt><dd><?= $value ?></dd></div><?php endforeach; ?>
      </dl>
    </section>
    <section class="card">
      <h2>Actions</h2>
      <div class="action-list">
        <?= $statusAction('new', 'Marquer comme non lu', 'eye-off') ?>
        <?php if ($m['status'] !== 'archived'): ?><?= $statusAction('archived', 'Archiver', 'archive') ?><?php else: ?><?= $statusAction('read', 'Remettre dans la boîte', 'inbox') ?><?php endif; ?>
        <?php if ($m['status'] !== 'spam'): ?><?= $statusAction('spam', 'Signaler comme spam', 'spam') ?><?php endif; ?>
        <form method="post" action="/admin/messages/<?= $m['id'] ?>/delete" data-confirm="Supprimer définitivement ce message et ses réponses ? Cette action est irréversible.">
          <?= csrf_field() ?>
          <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?>Supprimer définitivement</button>
        </form>
      </div>
    </section>
  </aside>
</div>
