<?php
declare(strict_types=1);

namespace App\Core;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Two-factor authentication for admins: TOTP (RFC 6238, compatible with Google Authenticator,
 * Microsoft Authenticator, 1Password…) plus single-use recovery codes.
 * The TOTP secret is stored encrypted (AES-256-GCM, key derived from app.secret).
 */
final class Mfa
{
    private const PERIOD = 30;
    private const DIGITS = 6;
    private const WINDOW = 1; // accept the previous/next 30 s step for clock drift
    private const B32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /* ---------- TOTP ---------- */

    public static function newSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    public static function code(string $secret, int $step): string
    {
        $hash = hash_hmac('sha1', pack('J', $step), self::base32Decode($secret), true);
        $offset = ord($hash[19]) & 0x0F;
        $bin = ((ord($hash[$offset]) & 0x7F) << 24) | (ord($hash[$offset + 1]) << 16) | (ord($hash[$offset + 2]) << 8) | ord($hash[$offset + 3]);
        return str_pad((string) ($bin % 10 ** self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Check a 6-digit code. Returns the matching time step, or null.
     * Steps at or before $lastStep are refused so a code can't be replayed.
     */
    public static function verify(string $secret, string $code, ?int $lastStep = null): ?int
    {
        $code = preg_replace('/\D/', '', $code) ?? '';
        if (strlen($code) !== self::DIGITS) return null;
        $now = intdiv(time(), self::PERIOD);
        for ($s = $now - self::WINDOW; $s <= $now + self::WINDOW; $s++) {
            if ($lastStep !== null && $s <= $lastStep) continue;
            if (hash_equals(self::code($secret, $s), $code)) return $s;
        }
        return null;
    }

    public static function uri(string $secret, string $account): string
    {
        $issuer = 'Heaven of Web';
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    /** QR code as inline SVG markup (generated on the server: the secret never leaves it). */
    public static function qrSvg(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'connectPaths' => true,
            'drawLightModules' => false,
            'svgAddXmlHeader' => false,
            'markupDark' => '#041C32',
            'cssClass' => 'mfa-qr',
        ]);
        return (new QRCode($options))->render($data);
    }

    /** "ABCD EFGH IJKL …" for manual entry. */
    public static function formatSecret(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    /* ---------- Secret storage ---------- */

    private static function key(): string
    {
        return hash('sha256', 'mfa|' . config('app.secret'), true);
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return 'v1:' . base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(?string $stored): ?string
    {
        if (!$stored || !str_starts_with($stored, 'v1:')) return null;
        $raw = base64_decode(substr($stored, 3), true);
        if ($raw === false || strlen($raw) < 29) return null;
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? null : $plain;
    }

    /* ---------- Per-admin helpers ---------- */

    public static function enabled(array $admin): bool
    {
        return !empty($admin['mfa_enabled_at']) && !empty($admin['mfa_secret']);
    }

    /** Verify a TOTP code or a recovery code for an admin; records the step / marks the code used. */
    public static function check(array $admin, string $input): bool
    {
        $input = trim($input);
        if (preg_match('/^\d[\d\s]{5,8}$/', $input)) {
            $secret = self::decrypt($admin['mfa_secret'] ?? null);
            if ($secret === null) return false;
            $step = self::verify($secret, $input, isset($admin['mfa_last_step']) ? (int) $admin['mfa_last_step'] : null);
            if ($step === null) return false;
            DB::run('UPDATE admins SET mfa_last_step = ? WHERE id = ?', [$step, $admin['id']]);
            return true;
        }
        return self::useRecoveryCode((int) $admin['id'], $input);
    }

    /** @return string[] 10 new codes (shown once); previous codes are invalidated. */
    public static function newRecoveryCodes(int $adminId): array
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I
        DB::run('DELETE FROM admin_recovery_codes WHERE admin_id = ?', [$adminId]);
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $c = '';
            for ($j = 0; $j < 8; $j++) $c .= $alphabet[random_int(0, 31)];
            $code = substr($c, 0, 4) . '-' . substr($c, 4);
            $codes[] = $code;
            DB::insert('admin_recovery_codes', ['admin_id' => $adminId, 'code_hash' => password_hash($code, PASSWORD_DEFAULT)]);
        }
        return $codes;
    }

    public static function remainingRecoveryCodes(int $adminId): int
    {
        return (int) DB::value('SELECT COUNT(*) FROM admin_recovery_codes WHERE admin_id = ? AND used_at IS NULL', [$adminId]);
    }

    private static function useRecoveryCode(int $adminId, string $input): bool
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $input) ?? '');
        if (strlen($code) !== 8) return false;
        $code = substr($code, 0, 4) . '-' . substr($code, 4);
        foreach (DB::all('SELECT id, code_hash FROM admin_recovery_codes WHERE admin_id = ? AND used_at IS NULL', [$adminId]) as $row) {
            if (password_verify($code, $row['code_hash'])) {
                DB::run('UPDATE admin_recovery_codes SET used_at = NOW() WHERE id = ?', [$row['id']]);
                return true;
            }
        }
        return false;
    }

    public static function disable(int $adminId): void
    {
        DB::run('UPDATE admins SET mfa_secret = NULL, mfa_enabled_at = NULL, mfa_last_step = NULL WHERE id = ?', [$adminId]);
        DB::run('DELETE FROM admin_recovery_codes WHERE admin_id = ?', [$adminId]);
    }

    /* ---------- Base32 (RFC 4648) ---------- */

    private static function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $b) $bits .= str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT);
        $out = '';
        foreach (str_split($bits, 5) as $chunk) $out .= self::B32[bindec(str_pad($chunk, 5, '0'))];
        return $out;
    }

    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32) ?? '');
        $bits = '';
        foreach (str_split($b32) as $c) $bits .= str_pad(decbin(strpos(self::B32, $c)), 5, '0', STR_PAD_LEFT);
        $out = '';
        foreach (str_split($bits, 8) as $byte) if (strlen($byte) === 8) $out .= chr(bindec($byte));
        return $out;
    }
}
