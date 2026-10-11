<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    /**
     * Send an HTML email through the configured SMTP server.
     * $html is the inner body; it is wrapped in the branded email layout.
     *
     * @return true|string true on success, the error message otherwise
     */
    public static function send(
        string $to,
        string $subject,
        string $html,
        ?string $replyTo = null,
        ?string $replyToName = null,
        string $lang = 'fr',
        array $headers = []
    ): true|string {
        if (!class_exists(PHPMailer::class)) return 'PHPMailer is not installed (run composer install).';
        $c = config('mail');
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $c['host'];
            $mail->Port = (int) $c['port'];
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Timeout = 15;
            if (!empty($c['user']) && !empty($c['pass'])) {
                $mail->SMTPAuth = true;
                $mail->Username = $c['user'];
                $mail->Password = $c['pass'];
            }
            if ($c['secure'] === 'ssl') $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            elseif ($c['secure'] === 'tls') $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            else $mail->SMTPAutoTLS = false;

            $mail->setFrom($c['from'], $c['from_name']);
            $mail->addAddress($to);
            if ($replyTo) $mail->addReplyTo($replyTo, $replyToName ?? '');
            foreach ($headers as $name => $value) $mail->addCustomHeader($name, $value);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = View::render('emails/layout', ['body' => $html, 'subject' => $subject, 'mailLang' => $lang]);
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</h2>'], ["\n", "\n\n", "\n\n"], $html)), ENT_QUOTES, 'UTF-8'));
            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('Mail error: ' . $mail->ErrorInfo);
            return $mail->ErrorInfo ?: $e->getMessage();
        }
    }
}
