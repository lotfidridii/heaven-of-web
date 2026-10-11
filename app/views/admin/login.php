<h1>Administration</h1>
<p class="muted">Connectez-vous pour gérer le site et répondre aux messages.</p>
<?php if ($error): ?><p class="alert is-error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="/admin/login" class="stack">
  <?= csrf_field() ?>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($email ?? '') ?>" autofocus>
  </div>
  <div class="field">
    <label for="password">Mot de passe</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
  </div>
  <button class="btn btn-primary btn-block" type="submit">Se connecter</button>
</form>
