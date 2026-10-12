<?php
/**
 * Downloads (or refreshes) the free DB-IP "IP to Country Lite" database used to show visitor countries.
 * Licence: CC BY 4.0 — "IP Geolocation by DB-IP" (https://db-ip.com), credited on the admin dashboard.
 *
 * Usage: php app/cli/update-geoip.php
 * Hostinger: run it once over SSH, then monthly with a cron job (hPanel → Advanced → Cron Jobs), e.g.
 *   0 4 3 * *  /usr/bin/php /home/USER/domains/heavenofweb.fr/app/cli/update-geoip.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\GeoIp;

$dir = dirname(GeoIp::FILE);
if (!is_dir($dir) && !mkdir($dir, 0755, true)) exit("Cannot create {$dir}\n");

// The file for the current month is published at the start of the month; fall back to last month.
$months = [date('Y-m'), date('Y-m', strtotime('first day of last month'))];
foreach ($months as $month) {
    $url = "https://download.db-ip.com/free/dbip-country-lite-{$month}.mmdb.gz";
    echo "Downloading {$url} … ";
    $ctx = stream_context_create(['http' => ['timeout' => 60, 'user_agent' => 'HeavenOfWeb-GeoIP/1.0', 'ignore_errors' => true]]);
    $gz = @file_get_contents($url, false, $ctx);
    $status = isset($http_response_header[0]) ? (int) explode(' ', $http_response_header[0])[1] : 0;
    if ($gz === false || $status !== 200 || strlen($gz) < 100000) {
        echo "not available (HTTP {$status})\n";
        continue;
    }
    $data = gzdecode($gz);
    if ($data === false || !str_contains(substr($data, -200000), "\xAB\xCD\xEFMaxMind.com")) {
        echo "invalid file\n";
        continue;
    }
    $tmp = GeoIp::FILE . '.tmp';
    file_put_contents($tmp, $data);
    rename($tmp, GeoIp::FILE); // atomic replace
    printf("ok (%.1f MB)\n", strlen($data) / 1048576);
    exit(0);
}
echo "Could not download the database. Visitor countries will stay empty until it is installed.\n";
exit(1);
