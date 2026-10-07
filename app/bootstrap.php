<?php
declare(strict_types=1);

date_default_timezone_set('UTC');
$localConfig = is_file(__DIR__ . '/../config.local.php') ? require __DIR__ . '/../config.local.php' : [];
if (!is_array($localConfig)) throw new RuntimeException('config.local.php must return an array.');
function setting(string $key, string $default = ''): string {
    global $localConfig;
    return (string) ($localConfig[$key] ?? (getenv(strtoupper($key)) ?: $default));
}
function e(mixed $value): string { return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = '/'): string {
    $base = rtrim(setting('base_path', '/'), '/');
    return $base . '/' . ltrim($path, '/');
}
function asset_url(string $path): string {
    $file = __DIR__ . '/../public/' . ltrim($path, '/');
    if (!is_file($file)) throw new RuntimeException('Missing public asset.');
    return url($path) . '?v=' . substr(hash_file('sha256', $file), 0, 12);
}
function redirect(string $path): never { header('Location: ' . url($path), true, 303); exit; }
function db(): PDO {
    static $connection;
    if ($connection) return $connection;
    $dsn = setting('db_dsn');
    $user = setting('db_user'); $password = setting('db_password');
    if (!$dsn) {
        $parts = parse_url(setting('database_url'));
        if (!$parts || !in_array($parts['scheme'] ?? '', ['postgres', 'postgresql'], true)) {
            throw new RuntimeException('Configure DATABASE_URL (PostgreSQL) or DB_DSN, DB_USER and DB_PASSWORD.');
        }
        parse_str($parts['query'] ?? '', $options);
        $dsn = 'pgsql:host=' . ($parts['host'] ?? 'localhost') . ';port=' . (int) ($parts['port'] ?? 5432) .
            ';dbname=' . rawurldecode(ltrim($parts['path'] ?? '', '/')) .
            ';sslmode=' . ($options['sslmode'] ?? 'prefer');
        $user = rawurldecode($parts['user'] ?? ''); $password = rawurldecode($parts['pass'] ?? '');
    }
    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    if (!in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), ['pgsql', 'mysql'], true)) {
        throw new RuntimeException('Use PostgreSQL or MySQL with the supplied schema.');
    }
    return $connection;
}
function driver(): string { return db()->getAttribute(PDO::ATTR_DRIVER_NAME); }
function query(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(is_int($key) ? $key + 1 : ':' . $key, $value, is_bool($value) ? PDO::PARAM_BOOL : (is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR)));
    }
    $stmt->execute();
    return $stmt;
}
function array_value(array $values): string {
    if (driver() === 'mysql') return json_encode(array_values($values), JSON_THROW_ON_ERROR);
    return '{' . implode(',', array_map(fn($v) => '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $v) . '"', $values)) . '}';
}
function select_columns(string $table): string {
    if (driver() !== 'pgsql') return '*';
    return match ($table) {
        'blog_posts' => '*, array_to_json(services) AS services',
        'usabime_requests' => '*, array_to_json(asset_links) AS asset_links, array_to_json(website_references) AS website_references',
        default => '*',
    };
}
function normalize(?array $row): ?array {
    if (!$row) return null;
    foreach (['services', 'asset_links', 'website_references', 'line_items'] as $field) {
        if (isset($row[$field]) && is_string($row[$field])) $row[$field] = json_decode($row[$field], true, 512, JSON_THROW_ON_ERROR);
    }
    foreach (['featured', 'privacy_accepted'] as $field) {
        if (isset($row[$field])) $row[$field] = in_array($row[$field], [true, 1, '1', 't', 'true'], true);
    }
    return $row;
}
function all_rows(string $table, string $where = '', array $params = [], string $order = 'created_at DESC'): array {
    if (!in_array($table, ['blog_posts', 'usabime_requests', 'usabime_proposals', 'contact_inquiries'], true)) throw new LogicException('Unknown table.');
    return array_map('normalize', query('SELECT ' . select_columns($table) . " FROM $table " . ($where ? "WHERE $where " : '') . "ORDER BY $order", $params)->fetchAll());
}
function find_row(string $table, int $id): ?array {
    return all_rows($table, 'id = ?', [$id])[0] ?? null;
}
function save_row(string $table, array $values, ?int $id = null): int {
    if (!in_array($table, ['blog_posts', 'usabime_requests', 'usabime_proposals', 'contact_inquiries'], true)) throw new LogicException('Unknown table.');
    foreach (array_keys($values) as $column) if (!preg_match('/^[a-z_]+$/', $column)) throw new LogicException('Invalid column.');
    foreach (['services', 'asset_links', 'website_references'] as $field) {
        if (array_key_exists($field, $values)) $values[$field] = array_value($values[$field]);
    }
    if (isset($values['line_items'])) $values['line_items'] = json_encode($values['line_items'], JSON_THROW_ON_ERROR);
    if ($id !== null) {
        $values['updated_at'] = gmdate('Y-m-d H:i:s');
        query("UPDATE $table SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($values))) . ' WHERE id = ?', [...array_values($values), $id]);
        return $id;
    }
    $sql = "INSERT INTO $table (" . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')';
    $stmt = query($sql . (driver() === 'pgsql' ? ' RETURNING id' : ''), array_values($values));
    return driver() === 'pgsql' ? (int) $stmt->fetchColumn() : (int) db()->lastInsertId();
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-ancestors 'self' https://*.replit.dev https://*.replit.com https://replit.com; form-action 'self'; base-uri 'self'");
header('Cache-Control: no-store');
ini_set('session.use_strict_mode', '1');
session_name('WALES_WEBS_SESSION');
$secureCookie = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_set_cookie_params(['httponly' => true, 'samesite' => $secureCookie ? 'None' : 'Lax', 'secure' => $secureCookie, 'path' => url('/')]);
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e($_SESSION['csrf']) . '">'; }
function check_csrf(): void {
    if (!is_string($_POST['_csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['_csrf'])) {
        http_response_code(403); render('error', ['pageTitle' => 'Request expired', 'message' => 'Your form session expired. Reload the page and try again.']); exit;
    }
}
function admin_signed_in(): bool {
    $expected = setting('blog_admin_password');
    return $expected !== '' && isset($_SESSION['admin_fingerprint'], $_SESSION['admin_seen']) &&
        time() - (int) $_SESSION['admin_seen'] < 1800 &&
        hash_equals(hash('sha256', $expected), (string) $_SESSION['admin_fingerprint']);
}
function require_admin(string $next): void {
    header('X-Robots-Tag: noindex, nofollow');
    if (!admin_signed_in()) { render('login', ['admin' => true, 'pageTitle' => 'Private sign in', 'next' => $next, 'errors' => []]); exit; }
    $_SESSION['admin_seen'] = time();
}
function safe_next(string $next): string {
    return preg_match('~^/(usabime/admin|admin/contact|journal/studio)(\?id=[1-9][0-9]*)?$~', $next) ? $next : '/usabime/admin';
}
function client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (in_array($ip, ['127.0.0.1', '::1'], true)) {
        $forwarded = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0]);
        if (filter_var($forwarded, FILTER_VALIDATE_IP)) return $forwarded;
    }
    return $ip;
}
function limited(string $bucket, int $limit, int $seconds = 900): bool {
    $key = hash('sha256', $bucket . ':' . client_ip()); $now = time(); $cutoff = $now - $seconds;
    if (driver() === 'pgsql') {
        $attempts = query('INSERT INTO security_limits (key, window_start, attempts) VALUES (?, ?, 1) ON CONFLICT (key) DO UPDATE SET attempts = CASE WHEN security_limits.window_start < ? THEN 1 ELSE security_limits.attempts + 1 END, window_start = CASE WHEN security_limits.window_start < ? THEN ? ELSE security_limits.window_start END RETURNING attempts', [$key, $now, $cutoff, $cutoff, $now])->fetchColumn();
    } else {
        db()->beginTransaction();
        try {
            query('INSERT INTO security_limits (`key`, window_start, attempts) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE attempts = IF(window_start < ?, 1, attempts + 1), window_start = IF(window_start < ?, ?, window_start)', [$key, $now, $cutoff, $cutoff, $now]);
            $attempts = query('SELECT attempts FROM security_limits WHERE `key` = ? FOR UPDATE', [$key])->fetchColumn();
            db()->commit();
        } catch (Throwable $error) { db()->rollBack(); throw $error; }
    }
    return (int) $attempts > $limit;
}
function text_input(array $source, string $key, int $min, int $max, array &$errors, string $label): string {
    $value = is_string($source[$key] ?? null) ? trim($source[$key]) : '';
    if (mb_strlen($value) < $min || mb_strlen($value) > $max) $errors[] = "$label must contain $min to $max characters.";
    return $value;
}
function date_input(mixed $value, array &$errors, string $label): ?string {
    if ($value === null || $value === '') return null;
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || !($date = DateTimeImmutable::createFromFormat('!Y-m-d', $value)) || $date->format('Y-m-d') !== $value) {
        $errors[] = "$label must be a valid calendar date."; return null;
    }
    return $value;
}
function links_input(mixed $input, array &$errors, string $label): array {
    $links = is_string($input) ? preg_split('/\r?\n/', $input) : (is_array($input) ? $input : []);
    $links = array_values(array_filter(array_map(fn($v) => is_string($v) ? trim($v) : '', $links)));
    if (count($links) > 10) $errors[] = "$label: share no more than 10 links.";
    foreach ($links as $link) {
        if (strlen($link) > 2000 || !filter_var($link, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($link, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) $errors[] = "$label must use valid http or https URLs.";
    }
    return $links;
}
function cents(string $price): int {
    $parts = explode('.', $price);
    return (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
}
function format_money(int $cents): string {
    return preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', (string) intdiv($cents, 100)) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}
function render(string $view, array $data = []): void {
    $pageTitle = 'Wales & Webs'; $pageDescription = 'Build, automate and grow with Wales & Webs.'; $page = $view;
    $errors = []; $values = []; $receipt = null; $admin = false;
    extract($data, EXTR_SKIP);
    // Defaults are deliberately assigned before extracting explicit page data.
    foreach ($data as $key => $value) { if (in_array($key, ['pageTitle', 'pageDescription', 'page', 'errors', 'values', 'receipt', 'admin'], true)) $$key = $value; }
    if ($admin) require __DIR__ . '/../views/admin-header.php'; else require __DIR__ . '/../views/header.php';
    require __DIR__ . '/../views/' . $view . '.php';
    if ($admin) require __DIR__ . '/../views/admin-footer.php'; else require __DIR__ . '/../views/footer.php';
}