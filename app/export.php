<?php
declare(strict_types=1);

function data_export(string $dialect, bool $publicStoriesOnly = false): string {
    if (!in_array($dialect, ['postgresql', 'mysql'], true)) throw new LogicException('Unsupported SQL dialect.');
    $out = $publicStoriesOnly
        ? "-- Published Wales & Webs Journal stories only. No private customer records.\n"
        : "-- Private Wales & Webs database export. Contains customer details: keep it secure.\n";
    $out .= $dialect === 'postgresql' ? "SET standard_conforming_strings = on;\nBEGIN;\n" : "SET NAMES utf8mb4;\nSET sql_mode = 'NO_BACKSLASH_ESCAPES';\nSTART TRANSACTION;\n";
    $quote = fn($value) => "'" . str_replace("'", "''", $value) . "'";
    foreach ($publicStoriesOnly ? ['blog_posts'] : ['blog_posts', 'usabime_requests', 'usabime_proposals', 'contact_inquiries'] as $table) {
        foreach (all_rows($table, $publicStoriesOnly ? 'status = ?' : '', $publicStoriesOnly ? ['published'] : []) as $row) {
            $values = [];
            foreach ($row as $key => $value) {
                if ($value === null) $values[] = 'NULL';
                elseif (is_bool($value)) $values[] = $dialect === 'postgresql' ? ($value ? 'TRUE' : 'FALSE') : ($value ? '1' : '0');
                elseif (is_int($value)) $values[] = (string) $value;
                elseif (is_array($value)) {
                    if ($dialect === 'postgresql' && in_array($key, ['services', 'asset_links', 'website_references'], true)) {
                        $values[] = 'ARRAY[' . implode(',', array_map($quote, $value)) . ']::text[]';
                    } else $values[] = $quote(json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                } else {
                    if ($dialect === 'mysql' && in_array($key, ['created_at', 'updated_at', 'published_at', 'privacy_accepted_at'], true)) $value = gmdate('Y-m-d H:i:s', strtotime($value));
                    $values[] = $quote((string) $value);
                }
            }
            $out .= "INSERT INTO $table (" . implode(',', array_keys($row)) . ')' . ($dialect === 'postgresql' ? ' OVERRIDING SYSTEM VALUE' : '') . ' VALUES (' . implode(',', $values) . ");\n";
        }
        if ($dialect === 'postgresql') $out .= "SELECT setval(pg_get_serial_sequence('$table', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM $table;\n";
    }
    return $out . "COMMIT;\n";
}
function portable_archive(string $file): void {
    if (!class_exists(ZipArchive::class)) throw new RuntimeException('Enable the PHP zip extension to export the site.');
    $root = realpath(dirname(__DIR__));
    $zip = new ZipArchive();
    if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Could not create private archive.');
    db()->beginTransaction();
    try {
        if (driver() === 'pgsql') query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
        $pg = data_export('postgresql');
        $mysql = data_export('mysql');
        db()->commit();
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $entry) {
            $relative = substr($entry->getPathname(), strlen($root) + 1);
            if (!$entry->isFile() || $entry->isLink() || $entry->getFilename() === 'config.local.php' || str_starts_with($relative, 'database/current-data') || str_starts_with($relative, 'vendor/')) continue;
            if (!$zip->addFile($entry->getPathname(), $relative)) throw new RuntimeException('Could not add application file.');
        }
        $zip->addFromString('database/current-data.postgresql.sql', $pg);
        $zip->addFromString('database/current-data.mysql.sql', $mysql);
        if (!$zip->close()) throw new RuntimeException('Could not finish private archive.');
        chmod($file, 0600);
    } catch (Throwable $error) {
        if (db()->inTransaction()) db()->rollBack();
        $zip->close();
        throw $error;
    }
}
function export_page(string $method): void {
    require_admin('/usabime/admin');
    if ($method !== 'POST') { http_response_code(405); header('Allow: POST'); return; }
    $file = tempnam(sys_get_temp_dir(), 'wales-export-');
    if (!$file) throw new RuntimeException('Could not create export.');
    try {
        portable_archive($file);
        session_write_close();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="wales-webs-php-export.zip"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
    } finally { unlink($file); }
}