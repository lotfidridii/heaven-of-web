<?php $rtl = ($mailLang ?? 'fr') === 'ar'; ?>
<!doctype html>
<html lang="<?= e($mailLang ?? 'fr') ?>" dir="<?= $rtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($subject) ?></title>
</head>
<body style="margin:0;padding:0;background:#EEF1F4;font-family:Arial,Helvetica,sans-serif;color:#041C32;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F4;padding:24px 12px;">
  <tr><td align="center">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border-radius:14px;overflow:hidden;">
      <tr><td style="background:#041C32;padding:22px 28px;">
        <span style="font-size:20px;font-weight:bold;color:#FFFFFF;letter-spacing:.3px;">Heaven <span style="color:#FF4848;">of</span> Web</span>
      </td></tr>
      <tr><td style="padding:28px;font-size:15px;line-height:1.6;text-align:<?= $rtl ? 'right' : 'left' ?>;">
        <?= $body ?>
      </td></tr>
      <tr><td style="padding:18px 28px;border-top:1px solid #E3E7EB;font-size:12px;color:#5A6B7C;text-align:<?= $rtl ? 'right' : 'left' ?>;">
        Heaven of Web · <a href="<?= e(config('app.url')) ?>" style="color:#5A6B7C;"><?= e(preg_replace('#^https?://#', '', config('app.url'))) ?></a>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
