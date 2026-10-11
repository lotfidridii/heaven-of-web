<h1>Vérification en deux étapes</h1>
<p class="muted" data-mfa-hint="app">Ouvrez votre application d’authentification et saisissez le code à 6 chiffres pour Heaven of Web.</p>
<p class="muted" data-mfa-hint="recovery" hidden>Saisissez l’un de vos codes de secours (format ABCD-EFGH). Chaque code ne fonctionne qu’une fois.</p>
<?php if ($error): ?><p class="alert is-error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="/admin/login/verify" class="stack" data-mfa-form>
  <?= csrf_field() ?>
  <div class="field">
    <label for="code" data-mfa-label>Code de vérification</label>
    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}" maxlength="9" required autofocus class="code-input" placeholder="000 000">
  </div>
  <button class="btn btn-primary btn-block" type="submit">Vérifier</button>
</form>
<p class="auth-alt">
  <button class="link-btn" type="button" data-mfa-toggle>Utiliser un code de secours</button>
  · <a href="/admin/login">Annuler</a>
</p>
