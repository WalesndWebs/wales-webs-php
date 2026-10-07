<?php
// Development router. Production hosting uses public/index.php with Apache/nginx.
$public = __DIR__ . '/public';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$base = rtrim(getenv('BASE_PATH') ?: '', '/');
if ($base && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
$file = realpath($public . $path);
if ($file && str_starts_with($file, $public . DIRECTORY_SEPARATOR) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
    return false;
}
require $public . '/index.php';