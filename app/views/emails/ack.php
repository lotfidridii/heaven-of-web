<h2 style="margin:0 0 16px;font-size:20px;"><?= e(t('mail.ack.title', ['name' => $m['name']])) ?></h2>
<p style="margin:0 0 14px;"><?= e(t('mail.ack.text')) ?></p>
<p style="margin:0 0 8px;color:#5A6B7C;font-size:13px;"><?= e(t('mail.ack.copy')) ?></p>
<div style="background:#F3F5F7;border-radius:10px;padding:16px;white-space:pre-wrap;"><?= e($m['message']) ?></div>
<p style="margin:22px 0 0;"><?= e(t('mail.ack.signature')) ?><br><strong>Heaven of Web</strong></p>
