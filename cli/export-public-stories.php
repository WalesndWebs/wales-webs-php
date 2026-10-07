<?php
declare(strict_types=1);
// Published content only. Safe for the code repository; never exports client records.
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/export.php';
$options = getopt('', ['dialect:', 'output:']);
$dialect = $options['dialect'] ?? 'postgresql';
if (!in_array($dialect, ['postgresql', 'mysql'], true) || empty($options['output'])) {
    fwrite(STDERR, "Usage: php cli/export-public-stories.php --dialect=postgresql|mysql --output=/path/stories.sql\n");
    exit(1);
}
db()->beginTransaction();
try {
    if (driver() === 'pgsql') query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
    $sql = data_export($dialect, true);
    db()->commit();
    if (file_put_contents($options['output'], $sql, LOCK_EX) === false) throw new RuntimeException('Could not write public story export.');
    echo "Published-only Journal SQL written. No private customer data included.\n";
} catch (Throwable $error) {
    if (db()->inTransaction()) db()->rollBack();
    throw $error;
}
