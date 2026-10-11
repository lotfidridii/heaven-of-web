<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= (int) $status ?> · Administration</title>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="auth-body">
<main class="auth-card">
  <h1><?= $status === 404 ? 'Page introuvable' : ($status === 419 ? 'Session expirée' : 'Une erreur est survenue') ?></h1>
  <p class="muted"><?= $status === 404 ? 'Cet élément n’existe pas ou a été supprimé.' : ($status === 419 ? 'Rechargez la page précédente puis recommencez.' : 'Réessayez dans un instant.') ?></p>
  <p><a class="btn btn-primary" href="/admin">Retour au tableau de bord</a></p>
</main>
</body>
</html>
