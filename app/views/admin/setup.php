<h1>Créer le compte administrateur</h1>
<p class="muted">Première connexion : créez votre compte. Cette page disparaît dès qu’un administrateur existe.</p>
<?php if ($error): ?><p class="alert is-error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="/admin/setup" class="stack">
  <?= csrf_field() ?>
  <div class="field">
    <label for="setup_key">Clé d’installation</label>
    <input id="setup_key" name="setup_key" type="password" required autocomplete="off">
    <p class="help">La valeur <code>setup_key</code> de <code>app/config.php</code>.</p>
  </div>
  <div class="field">
    <label for="name">Votre nom</label>
    <input id="name" name="name" type="text" required autocomplete="name" value="<?= e(old('name')) ?>">
  </div>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autocomplete="username" value="<?= e(old('email')) ?>">
  </div>
  <div class="field">
    <label for="password">Mot de passe</label>
    <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password">
    <p class="help">10 caractères minimum.</p>
  </div>
  <button class="btn btn-primary btn-block" type="submit">Créer le compte</button>
</form>
