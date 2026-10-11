<?php
/**
 * Emergency: turns off two-factor authentication for one admin (lost phone and recovery codes).
 * Usage: php app/cli/reset-mfa.php email@example.com
 * Also needed if app.secret changes, since the stored authenticator secrets can then no longer be decrypted.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\DB;
use App\Core\Mfa;

$email = strtolower(trim((string) ($argv[1] ?? '')));
$admin = $email !== '' ? DB::one('SELECT id, email FROM admins WHERE email = ?', [$email]) : null;
if (!$admin) exit("Usage: php app/cli/reset-mfa.php email@example.com (admin not found)\n");

Mfa::disable((int) $admin['id']);
echo "Two-factor authentication disabled for {$admin['email']}. They can sign in with their password and set it up again.\n";
if (setting('mfa_required') === '1') echo "Note: MFA is required for all admins, so they will be asked to set it up right after signing in.\n";
