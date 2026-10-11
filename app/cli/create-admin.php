<?php
/**
 * Creates an admin account, or resets the password of an existing one.
 * Usage: php app/cli/create-admin.php "Name" email@example.com "password"
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\DB;

[$_, $name, $email, $password] = array_pad($argv, 4, null);
// --if-none: only create the account when no admin exists yet (used by the local Docker setup).
if (in_array('--if-none', $argv, true) && (int) DB::value('SELECT COUNT(*) FROM admins') > 0) exit(0);
if (!$name || !$email || !$password) {
    exit("Usage: php app/cli/create-admin.php \"Name\" email@example.com \"password\"\n");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit("Invalid email.\n");
if (strlen($password) < 10) exit("Password must be at least 10 characters.\n");

$email = strtolower($email);
$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = DB::one('SELECT id FROM admins WHERE email = ?', [$email]);
if ($existing) {
    DB::run('UPDATE admins SET name = ?, password_hash = ? WHERE id = ?', [$name, $hash, $existing['id']]);
    echo "Password updated for {$email}.\n";
} else {
    DB::insert('admins', ['name' => $name, 'email' => $email, 'password_hash' => $hash]);
    echo "Admin {$email} created.\n";
}
