<?php
/**
 * Applies the SQL files in app/migrations that have not been applied yet.
 * Usage: php app/cli/migrate.php
 * (Without SSH you can instead import the .sql files in order with phpMyAdmin.)
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit("CLI only\n");
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\DB;

$pdo = DB::pdo();
$tableExists = (bool) $pdo->query("SHOW TABLES LIKE 'migrations'")->fetchColumn();
$applied = $tableExists ? array_column(DB::all('SELECT name FROM migrations'), 'name') : [];

$files = glob(APP_PATH . '/migrations/*.sql');
sort($files);
$count = 0;
foreach ($files as $file) {
    $name = basename($file, '.sql');
    if (in_array($name, $applied, true)) continue;

    echo "Applying {$name}... ";
    $sql = preg_replace('/^--.*$/m', '', (string) file_get_contents($file));
    // Statements end with ";" at the end of a line; string values never contain raw newlines.
    $statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)));
    try {
        foreach ($statements as $statement) $pdo->exec($statement);
    } catch (Throwable $e) {
        echo "FAILED\n" . $e->getMessage() . "\n";
        exit(1);
    }
    echo "ok\n";
    $count++;
}
echo $count ? "{$count} migration(s) applied.\n" : "Database is up to date.\n";
