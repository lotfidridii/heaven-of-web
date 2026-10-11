<div class="grid-2">
  <section class="card">
    <h2>Mon profil</h2>
    <form method="post" action="/admin/account" class="stack">
      <?= csrf_field() ?>
      <div class="field">
        <label for="a-name">Nom</label>
        <input id="a-name" name="name" type="text" required value="<?= e($adminUser['name']) ?>" autocomplete="name">
        <p class="help">Utilisé dans la signature des réponses.</p>
      </div>
      <div class="field">
        <label for="a-email">Email de connexion</label>
        <input id="a-email" name="email" type="email" required value="<?= e($adminUser['email']) ?>" autocomplete="username">
      </div>
      <fieldset class="fieldset">
        <legend>Changer de mot de passe <span class="optional">(facultatif)</span></legend>
        <div class="field">
          <label for="a-current">Mot de passe actuel</label>
          <input id="a-current" name="current_password" type="password" autocomplete="current-password">
        </div>
        <div class="field">
          <label for="a-new">Nouveau mot de passe</label>
          <input id="a-new" name="new_password_self" type="password" minlength="10" autocomplete="new-password">
          <p class="help">10 caractères minimum.</p>
        </div>
      </fieldset>
      <div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer</button></div>
    </form>
  </section>

  <section class="card">
    <h2>Administrateurs</h2>
    <ul class="device-list">
      <?php foreach ($admins as $a): ?>
      <li>
        <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($a['name'], 0, 1))) ?></span>
        <span class="row-main"><strong><?= e($a['name']) ?><?= (int) $a['id'] === (int) $adminUser['id'] ? ' (vous)' : '' ?></strong><small><?= e($a['email']) ?><?= $a['last_login_at'] ? ' · connecté ' . e(time_ago($a['last_login_at'])) : '' ?></small></span>
        <span class="status <?= $a['mfa_enabled_at'] ? 'status-replied' : 'status-spam' ?>" title="Double authentification"><?= $a['mfa_enabled_at'] ? 'MFA' : 'Sans MFA' ?></span>
        <?php if ((int) $a['id'] !== (int) $adminUser['id']): ?>
        <form method="post" action="/admin/account" data-confirm="Retirer l’accès de <?= e($a['name']) ?> ?">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete_admin"><input type="hidden" name="admin_id" value="<?= (int) $a['id'] ?>">
          <button class="icon-btn icon-btn-danger" type="submit" title="Retirer"><?= icon('trash') ?><span class="sr-only">Retirer</span></button>
        </form>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <details class="install-help">
      <summary>Ajouter un administrateur</summary>
      <form method="post" action="/admin/account" class="stack">
        <?= csrf_field() ?><input type="hidden" name="action" value="add_admin">
        <div class="field"><label for="n-name">Nom</label><input id="n-name" name="new_name" type="text" required></div>
        <div class="field"><label for="n-email">Email</label><input id="n-email" name="new_email" type="email" required autocomplete="off"></div>
        <div class="field"><label for="n-pass">Mot de passe provisoire</label><input id="n-pass" name="new_password" type="password" required minlength="10" autocomplete="new-password"><p class="help">Communiquez-le de vive voix ; la personne pourra le changer dans « Compte ».</p></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('plus') ?>Ajouter</button></div>
      </form>
    </details>
  </section>
</div>

<section class="card mfa-card" id="mfa" aria-labelledby="mfa-title">
  <header class="card-head">
    <h2 id="mfa-title"><?= icon('shield', 'icon icon-inline') ?> Double authentification (MFA)</h2>
    <span class="status <?= $mfaEnabled ? 'status-replied' : 'status-spam' ?>"><?= $mfaEnabled ? 'Activée' : 'Désactivée' ?></span>
  </header>

  <?php if ($recoveryCodes): ?>
  <div class="recovery-box" role="region" aria-label="Codes de secours">
    <p><strong>Vos codes de secours.</strong> Ils ne seront plus affichés. Chacun permet de se connecter une fois si vous n’avez pas votre téléphone : imprimez-les ou rangez-les dans votre gestionnaire de mots de passe.</p>
    <ol class="recovery-codes" data-recovery-codes><?php foreach ($recoveryCodes as $c): ?><li><code><?= e($c) ?></code></li><?php endforeach; ?></ol>
    <div class="form-actions">
      <button class="btn btn-ghost btn-sm" type="button" data-copy-codes><?= icon('check') ?>Copier</button>
      <button class="btn btn-ghost btn-sm" type="button" data-download-codes><?= icon('download') ?>Télécharger (.txt)</button>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($mfaSetup): ?>
  <div class="mfa-setup">
    <ol class="mfa-steps">
      <li>Installez une application d’authentification : Google Authenticator, Microsoft Authenticator, 1Password…</li>
      <li>Scannez ce QR code avec l’application (ou saisissez la clé manuellement).</li>
      <li>Saisissez le code à 6 chiffres affiché pour confirmer.</li>
    </ol>
    <div class="mfa-pair">
      <div class="mfa-qr-wrap"><?= $mfaSetup['qr'] ?></div>
      <div>
        <p class="help">Clé à saisir manuellement :</p>
        <p class="mfa-key"><code dir="ltr"><?= e($mfaSetup['key']) ?></code></p>
        <form method="post" action="/admin/account/mfa/confirm" class="stack">
          <?= csrf_field() ?>
          <div class="field">
            <label for="mfa-code">Code de l’application</label>
            <input id="mfa-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}" maxlength="7" required class="code-input" placeholder="000 000">
          </div>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= icon('check') ?>Activer</button>
          </div>
        </form>
        <form method="post" action="/admin/account/mfa/cancel"><?= csrf_field() ?><button class="link-btn muted" type="submit">Annuler</button></form>
      </div>
    </div>
  </div>
  <?php elseif (!$mfaEnabled): ?>
  <p class="muted">Protégez l’accès à l’administration : en plus du mot de passe, un code à 6 chiffres généré par votre téléphone sera demandé à chaque connexion. Même si votre mot de passe est découvert, personne ne pourra se connecter sans votre téléphone.</p>
  <form method="post" action="/admin/account/mfa/start" class="form-actions"><?= csrf_field() ?><button class="btn btn-primary" type="submit"><?= icon('shield') ?>Activer la double authentification</button></form>
  <?php else: ?>
  <p class="muted">Activée le <?= e(date('d/m/Y', strtotime($mfaSince))) ?>. Codes de secours restants : <strong><?= (int) $mfaRemaining ?></strong> / 10.</p>
  <div class="grid-2 mfa-actions">
    <form method="post" action="/admin/account/mfa/recovery" class="stack">
      <?= csrf_field() ?>
      <h3>Nouveaux codes de secours</h3>
      <div class="field"><label for="rc-code">Code de l’application</label><input id="rc-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required class="code-input" placeholder="000 000"></div>
      <div class="form-actions"><button class="btn btn-ghost btn-sm" type="submit"><?= icon('reply') ?>Générer 10 nouveaux codes</button></div>
    </form>
    <?php if (!$mfaRequired): ?>
    <form method="post" action="/admin/account/mfa/disable" class="stack" data-confirm="Désactiver la double authentification ? Votre compte ne sera plus protégé que par le mot de passe.">
      <?= csrf_field() ?>
      <h3>Désactiver</h3>
      <div class="field"><label for="ds-pass">Mot de passe</label><input id="ds-pass" name="password" type="password" autocomplete="current-password" required></div>
      <div class="field"><label for="ds-code">Code de l’application ou code de secours</label><input id="ds-code" name="code" type="text" autocomplete="one-time-code" required></div>
      <div class="form-actions"><button class="btn btn-danger btn-sm" type="submit">Désactiver la MFA</button></div>
    </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <form method="post" action="/admin/account/mfa/require" class="mfa-require">
    <?= csrf_field() ?>
    <input type="hidden" name="required" value="<?= $mfaRequired ? '0' : '1' ?>">
    <span><strong>Obligatoire pour tous les administrateurs</strong><small><?= $mfaRequired ? 'Activé : un administrateur sans MFA doit la configurer avant d’accéder au reste de l’administration.' : 'Désactivé : chaque administrateur choisit d’activer ou non la MFA.' ?></small></span>
    <button class="switch" type="submit" role="switch" aria-checked="<?= $mfaRequired ? 'true' : 'false' ?>" <?= !$mfaEnabled && !$mfaRequired ? 'disabled title="Activez d’abord la MFA sur votre compte"' : '' ?>><span class="sr-only">Rendre obligatoire</span></button>
  </form>
</section>
