<?php
declare(strict_types=1);

namespace App\Core;

/** Fixed-window rate limiter stored in MySQL (no Redis on shared hosting). */
final class RateLimit
{
    /** Records a hit and returns false when the key exceeded $max hits within $seconds. */
    public static function hit(string $key, int $max, int $seconds): bool
    {
        $key = substr(hash('sha256', $key), 0, 64);
        $row = DB::one('SELECT hits, reset_at FROM rate_limits WHERE rkey = ?', [$key]);
        $now = time();
        if (!$row || strtotime($row['reset_at']) <= $now) {
            DB::run(
                'REPLACE INTO rate_limits (rkey, hits, reset_at) VALUES (?, 1, ?)',
                [$key, date('Y-m-d H:i:s', $now + $seconds)]
            );
            if (random_int(1, 50) === 1) DB::run('DELETE FROM rate_limits WHERE reset_at < NOW()');
            return true;
        }
        DB::run('UPDATE rate_limits SET hits = hits + 1 WHERE rkey = ?', [$key]);
        return (int) $row['hits'] < $max;
    }

    public static function clear(string $key): void
    {
        DB::run('DELETE FROM rate_limits WHERE rkey = ?', [substr(hash('sha256', $key), 0, 64)]);
    }
}
