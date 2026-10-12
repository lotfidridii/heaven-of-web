<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cookieless, self-hosted analytics.
 * A visitor is identified per day by sha256(daily salt + IP + user agent): the raw IP is
 * never stored and the hash cannot be linked across days, so no consent banner is needed.
 */
final class Analytics
{
    private const BOTS = '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|headless|lighthouse|pagespeed|gtmetrix|curl|wget|python|java\/|go-http|axios|node-fetch|monitor|uptime|preview/i';

    public static function isBot(string $ua): bool
    {
        return $ua === '' || (bool) preg_match(self::BOTS, $ua);
    }

    public static function visitorHash(): string
    {
        $salt = hash('sha256', config('app.secret') . '|' . date('Y-m-d'));
        return hash('sha256', $salt . '|' . client_ip() . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }

    /** @param array{type?:string, path?:string, label?:string, lang?:string, ref?:string, w?:int} $hit */
    public static function record(array $hit): void
    {
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 400);
        if (self::isBot($ua)) return;

        $type = in_array($hit['type'] ?? '', ['pageview', 'section', 'contact', 'outbound'], true) ? $hit['type'] : 'pageview';
        $path = substr(parse_url((string) ($hit['path'] ?? '/'), PHP_URL_PATH) ?: '/', 0, 255);
        if (str_starts_with($path, '/admin')) return;

        $refHost = null;
        if (!empty($hit['ref'])) {
            $host = parse_url((string) $hit['ref'], PHP_URL_HOST);
            $own = parse_url(config('app.url'), PHP_URL_HOST);
            if ($host && $host !== $own && $host !== ($_SERVER['HTTP_HOST'] ?? '')) $refHost = substr(preg_replace('/^www\./', '', $host), 0, 120);
        }
        [$browser, $os, $device] = self::parseUa($ua, (int) ($hit['w'] ?? 0));

        DB::insert('visits', [
            'type' => $type,
            'day' => date('Y-m-d'),
            'visited_at' => date('Y-m-d H:i:s'),
            'path' => $path,
            'label' => isset($hit['label']) ? substr((string) $hit['label'], 0, 80) : null,
            'lang' => in_array($hit['lang'] ?? '', I18n::LANGS, true) ? $hit['lang'] : null,
            'visitor' => self::visitorHash(),
            'referrer' => $refHost,
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
            'country' => self::country(),
        ]);
    }

    /**
     * Visitor country: from a CDN header when the host provides one (Cloudflare…), otherwise from the
     * local DB-IP database. Only the country code is stored; the IP address is never saved.
     */
    private static function country(): ?string
    {
        foreach (['HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'HTTP_X_GEO_COUNTRY'] as $h) {
            $c = strtoupper(substr((string) ($_SERVER[$h] ?? ''), 0, 2));
            if (preg_match('/^[A-Z]{2}$/', $c) && $c !== 'XX' && $c !== 'T1') return $c;
        }
        return GeoIp::country(client_ip());
    }

    /** @return array{0:string,1:string,2:string} browser, os, device */
    public static function parseUa(string $ua, int $screenWidth = 0): array
    {
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') || str_contains($ua, 'CriOS') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Autre',
        };
        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Mac OS X') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Autre',
        };
        $device = match (true) {
            str_contains($ua, 'iPad') || (str_contains($ua, 'Android') && !str_contains($ua, 'Mobile')) => 'Tablette',
            str_contains($ua, 'Mobi') || str_contains($ua, 'iPhone') => 'Mobile',
            $screenWidth > 0 && $screenWidth < 768 => 'Mobile',
            default => 'Ordinateur',
        };
        return [$browser, $os, $device];
    }

    /** Aggregated numbers for the dashboard over the last $days days. */
    public static function stats(int $days): array
    {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $prevFrom = date('Y-m-d', strtotime('-' . (2 * $days - 1) . ' days'));
        $pv = "type = 'pageview'";

        $totals = DB::one(
            "SELECT COUNT(*) views, COUNT(DISTINCT day, visitor) visitors FROM visits WHERE {$pv} AND day >= ?",
            [$from]
        );
        $prev = DB::one(
            "SELECT COUNT(*) views, COUNT(DISTINCT day, visitor) visitors FROM visits WHERE {$pv} AND day >= ? AND day < ?",
            [$prevFrom, $from]
        );
        $contacts = (int) DB::value('SELECT COUNT(*) FROM messages WHERE created_at >= ? AND status <> ?', [$from, 'spam']);

        // Daily series with zero-filled gaps.
        $rows = DB::all(
            "SELECT day, COUNT(*) views, COUNT(DISTINCT visitor) visitors FROM visits WHERE {$pv} AND day >= ? GROUP BY day",
            [$from]
        );
        $byDay = array_column($rows, null, 'day');
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = ['day' => $d, 'views' => (int) ($byDay[$d]['views'] ?? 0), 'visitors' => (int) ($byDay[$d]['visitors'] ?? 0)];
        }

        $top = static fn(string $col, string $where = '', int $limit = 6) => DB::all(
            "SELECT COALESCE({$col}, '—') label, COUNT(DISTINCT day, visitor) n FROM visits
             WHERE day >= ? " . ($where ?: "AND {$pv}") . " GROUP BY label ORDER BY n DESC LIMIT {$limit}",
            [$from]
        );

        return [
            'views' => (int) $totals['views'],
            'visitors' => (int) $totals['visitors'],
            'prevViews' => (int) $prev['views'],
            'prevVisitors' => (int) $prev['visitors'],
            'contacts' => $contacts,
            'conversion' => $totals['visitors'] > 0 ? min(100, round($contacts / $totals['visitors'] * 100, 1)) : 0,
            'series' => $series,
            'pages' => $top('path', '', 8),
            'sections' => $top('label', "AND type = 'section'", 8),
            'referrers' => $top('referrer', "AND {$pv} AND referrer IS NOT NULL", 8),
            'devices' => $top('device'),
            'browsers' => $top('browser'),
            'os' => $top('os'),
            'langs' => $top('lang'),
            'countries' => $top('country', "AND {$pv} AND country IS NOT NULL", 15),
            'countryKnown' => (int) DB::value("SELECT COUNT(DISTINCT day, visitor) FROM visits WHERE {$pv} AND day >= ? AND country IS NOT NULL", [$from]),
        ];
    }

    /** Keep 13 months of raw hits. Called occasionally from the dashboard. */
    public static function prune(): void
    {
        DB::run('DELETE FROM visits WHERE day < ?', [date('Y-m-d', strtotime('-13 months'))]);
    }
}
