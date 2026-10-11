<?php
$rows = [
    'Nom' => $m['name'],
    'Email' => $m['email'],
    'Téléphone' => $m['phone'],
    'Entreprise' => $m['company'],
    'Service' => $m['service'],
    'Budget' => $m['budget'],
    'Langue' => strtoupper($m['lang']),
];
?>
<h2 style="margin:0 0 16px;font-size:20px;">Nouveau message depuis le site</h2>
<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin-bottom:18px;">
  <?php foreach ($rows as $label => $value): if ($value === '' || $value === null) continue; ?>
  <tr>
    <td style="padding:6px 12px 6px 0;color:#5A6B7C;width:110px;vertical-align:top;"><?= e($label) ?></td>
    <td style="padding:6px 0;font-weight:bold;"><?= e($value) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<div style="background:#F3F5F7;border-radius:10px;padding:16px;white-space:pre-wrap;"><?= e($m['message']) ?></div>
<p style="margin:24px 0 0;">
  <a href="<?= e($adminUrl) ?>" style="display:inline-block;background:#FF4848;color:#041C32;font-weight:bold;text-decoration:none;padding:12px 20px;border-radius:999px;">Ouvrir et répondre dans l’administration</a>
</p>
<p style="margin:16px 0 0;font-size:13px;color:#5A6B7C;">Vous pouvez aussi répondre directement à cet email : la réponse partira vers <?= e($m['email']) ?>.</p>
