<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    /** Returned by attempt() when the password is right but a second factor is still needed. */
    public const MFA_REQUIRED = '__mfa__';
    private const MFA_TTL = 300;

    private static ?array $user = null;

    public static function user(): ?array
    {
        Session::start();
        if (self::$user === null && !empty($_SESSION['admin_id'])) {
            self::$user = DB::one('SELECT id, name, email, mfa_enabled_at FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
            if (!self::$user) unset($_SESSION['admin_id']);
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return isset(self::user()['id']) ? (int) self::user()['id'] : null;
    }

    /**
     * Check credentials. Limited to 5 failed attempts per IP+email per 15 minutes,
     * and 20 per IP overall, to slow down password guessing.
     */
    public static function attempt(string $email, string $password): string|true
    {
        $ipKey = 'login-ip|' . client_ip();
        $key = 'login|' . client_ip() . '|' . strtolower($email);
        if (!RateLimit::hit($ipKey, 20, 900) || !RateLimit::hit($key, 5, 900)) {
            return 'Trop de tentatives. Réessayez dans 15 minutes.';
        }
        $admin = DB::one('SELECT id, password_hash, mfa_enabled_at FROM admins WHERE email = ?', [strtolower($email)]);
        // Verify against a dummy hash when the email is unknown so timing doesn't reveal valid accounts.
        $hash = $admin['password_hash'] ?? password_hash(random_bytes(8), PASSWORD_DEFAULT);
        if (!password_verify($password, $hash) || !$admin) {
            return 'Email ou mot de passe incorrect.';
        }
        RateLimit::clear($key);
        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            DB::run('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
        }
        if (!empty($admin['mfa_enabled_at'])) {
            Session::start();
            session_regenerate_id(true);
            $_SESSION['mfa_pending'] = ['id' => (int) $admin['id'], 'at' => time()];
            return self::MFA_REQUIRED;
        }
        self::login((int) $admin['id']);
        return true;
    }

    /** Admin id waiting for its second factor (valid 5 minutes), or null. */
    public static function pendingMfa(): ?int
    {
        Session::start();
        $p = $_SESSION['mfa_pending'] ?? null;
        if (!$p || time() - $p['at'] > self::MFA_TTL) {
            unset($_SESSION['mfa_pending']);
            return null;
        }
        return (int) $p['id'];
    }

    public static function clearPendingMfa(): void
    {
        unset($_SESSION['mfa_pending']);
    }

    public static function login(int $id): void
    {
        Session::start();
        unset($_SESSION['mfa_pending']);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $id;
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        DB::run('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$id]);
        self::$user = null;
    }

    public static function logout(): void
    {
        Session::start();
        $_SESSION = [];
        session_regenerate_id(true);
        self::$user = null;
    }

    /** Guard for admin pages: redirect to login (or 401 for JSON requests). */
    public static function require(): array
    {
        $user = self::user();
        if ($user) return $user;
        if (wants_json()) json_response(['ok' => false, 'error' => 'auth'], 401);
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/admin';
        redirect('/admin/login');
    }
}
