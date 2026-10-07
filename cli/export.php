<?php
declare(strict_types=1);
// Private, explicit CLI export. Never accessible from public/.
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/export.php';
$options = getopt('', ['dialect:', 'output:']);
$dialect = $options['dialect'] ?? 'postgresql';
if (!in_array($dialect, ['postgresql', 'mysql'], true) || empty($options['output'])) {
    fwrite(STDERR, "Usage: php cli/export.php --dialect=postgresql|mysql --output=/private/path/data.sql\n"); exit(1);
}
db()->beginTransaction();
try {
    if (driver() === 'pgsql') query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
    $out = data_export($dialect); db()->commit();
    if (file_put_contents($options['output'], $out, LOCK_EX) === false) throw new RuntimeException('Could not write export.');
    chmod($options['output'], 0600);
    echo "Private SQL export written. No credentials included.\n";
} catch (Throwable $error) {
    if (db()->inTransaction()) db()->rollBack();
    throw $error;
}