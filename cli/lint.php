<?php
declare(strict_types=1);
$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$count = 0;
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php' || $file->getFilename() === 'config.local.php') continue;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $code);
    if ($code !== 0) { echo implode("\n", $output) . "\n"; exit(1); }
    $output = []; $count++;
}
echo "PHP syntax passed for $count files.\n";