<div class="grid-2 grid-wide-left">
  <section class="card" data-push data-vapid="<?= e($publicKey) ?>">
    <h2>Notifications sur cet appareil</h2>
    <p class="muted">Recevez une alerte dès qu’un message arrive par le formulaire de contact, même quand l’application est fermée.</p>

    <?php if (!$available): ?>
      <p class="alert is-error">La bibliothèque d’envoi n’est pas installée sur le serveur (dossier <code>vendor/</code> manquant). Lancez <code>composer install</code> puis téléversez le dossier <code>vendor/</code>.</p>
    <?php else: ?>
    <div class="push-state" data-push-state>
      <p class="push-status" data-push-status>Vérification…</p>
      <div class="form-actions">
        <button class="btn btn-primary" type="button" data-push-enable hidden><?= icon('bell') ?>Activer les notifications</button>
        <button class="btn btn-ghost" type="button" data-push-test hidden><?= icon('send') ?>Envoyer une notification de test</button>
        <button class="btn btn-ghost" type="button" data-push-disable hidden>Désactiver sur cet appareil</button>
      </div>
    </div>

    <details class="install-help">
      <summary>Installer l’administration comme une application</summary>
      <ul>
        <li><strong>Android (Chrome)</strong> : menu ⋮ → « Installer l’application » ou « Ajouter à l’écran d’accueil ».</li>
        <li><strong>iPhone / iPad (Safari, iOS 16.4 ou plus)</strong> : bouton Partager → « Sur l’écran d’accueil ». Ouvrez ensuite l’app depuis l’écran d’accueil et activez les notifications ici : sur iPhone, elles ne fonctionnent que dans l’app installée.</li>
        <li><strong>Ordinateur (Chrome, Edge)</strong> : icône d’installation dans la barre d’adresse.</li>
      </ul>
      <button class="btn btn-ghost btn-sm" type="button" data-install hidden><?= icon('download') ?>Installer maintenant</button>
    </details>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Mes appareils abonnés</h2>
    <?php if (!$devices): ?>
      <p class="empty">Aucun appareil pour l’instant. Activez les notifications sur chaque téléphone ou ordinateur où vous voulez être prévenu.</p>
    <?php else: ?>
    <ul class="device-list">
      <?php foreach ($devices as $d): ?>
      <li>
        <span class="thumb thumb-icon"><?= icon(str_contains((string) $d['device'], 'Mobile') ? 'smartphone' : 'browser') ?></span>
        <span class="row-main"><strong><?= e($d['device'] ?: 'Appareil') ?></strong><small>Ajouté le <?= e(date('d/m/Y', strtotime($d['created_at']))) ?><?= $d['last_used_at'] ? ' · dernière notification ' . e(time_ago($d['last_used_at'])) : '' ?></small></span>
        <form method="post" action="/admin/push/<?= (int) $d['id'] ?>/delete" data-confirm="Ne plus envoyer de notifications à cet appareil ?">
          <?= csrf_field() ?>
          <button class="icon-btn icon-btn-danger" type="submit" title="Retirer"><?= icon('trash') ?><span class="sr-only">Retirer</span></button>
        </form>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <p class="help">Un email est aussi envoyé à chaque nouveau message, à l’adresse définie dans <a href="/admin/settings?tab=contact">Coordonnées</a>.</p>
  </section>
</div>
