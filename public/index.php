<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/handlers.php';
require __DIR__ . '/../app/export.php';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$base = rtrim(setting('base_path', '/'), '/');
if ($base && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
$path = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 131072) { http_response_code(413); render('error', ['pageTitle' => 'Form too large', 'message' => 'Please shorten your message and try again.']); exit; }
    if ($method === 'POST') check_csrf();
    if ($method !== 'GET' && $method !== 'POST' && $method !== 'HEAD') { http_response_code(405); header('Allow: GET, POST, HEAD'); exit; }
    switch (true) {
        case $path === '/api/healthz': header('Content-Type: application/json'); echo json_encode(['status' => 'ok', 'runtime' => 'PHP']); break;
        case $path === '/api/blog/posts':
            header('Content-Type: application/json'); echo json_encode(published_posts(), JSON_THROW_ON_ERROR); break;
        case $path === '/':
            render('home', ['posts' => published_posts(), 'pageTitle' => 'Wales & Webs — Build. Automate. Grow.']); break;
        case $path === '/contact': contact_page($method); break;
        case $path === '/usabime/request': request_page($method); break;
        case $path === '/admin/login': login_page($method); break;
        case $path === '/admin/logout':
            if ($method !== 'POST') { http_response_code(405); exit; }
            $_SESSION = []; session_regenerate_id(true); redirect('/'); break;
        case $path === '/admin/contact': contact_admin_page($method); break;
        case $path === '/admin/export': export_page($method); break;
        case $path === '/usabime/admin': request_admin_page($method); break;
        case $path === '/journal/studio': blog_admin_page($method); break;
        case $path === '/journal':
            $posts = published_posts();
            $search = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 200) : '';
            $category = is_string($_GET['category'] ?? null) ? mb_substr($_GET['category'], 0, 120) : '';
            $categories = array_values(array_unique(array_column($posts, 'category')));
            if ($search || $category) $posts = array_values(array_filter($posts, fn($p) => (!$category || $p['category'] === $category) && (!$search || mb_stripos($p['title'] . ' ' . $p['client_name'] . ' ' . $p['excerpt'] . ' ' . $p['body'], $search) !== false)));
            render('journal', ['posts' => $posts, 'categories' => $categories, 'search' => $search, 'category' => $category, 'pageTitle' => 'Tech Stories & Our Work | Wales & Webs']); break;
        case preg_match('~^/journal/([a-z0-9-]+)$~', $path, $match) === 1:
            $post = all_rows('blog_posts', 'slug = ? AND status = ?', [$match[1], 'published'])[0] ?? null;
            if (!$post) { http_response_code(404); render('error', ['pageTitle' => 'Story not found', 'message' => 'This story is not available. Browse the Journal for published stories.']); break; }
            render('story', ['post' => $post, 'pageTitle' => $post['title'] . ' | Wales & Webs', 'pageDescription' => $post['excerpt']]); break;
        default: http_response_code(404); render('error', ['pageTitle' => 'Page not found', 'message' => 'The page you requested does not exist.']); break;
    }
} catch (Throwable $error) {
    // Never expose connection strings, queries, credentials or request details.
    error_log('Wales & Webs request failed: ' . get_class($error) . ' at ' . basename($error->getFile()) . ':' . $error->getLine());
    if (db_in_transaction()) db()->rollBack();
    http_response_code(503);
    render('error', ['pageTitle' => 'Temporarily unavailable', 'message' => 'We could not complete this request. Please try again shortly. Your browser form can be recovered with Back.']);
}
function db_in_transaction(): bool {
    try { return db()->inTransaction(); } catch (Throwable) { return false; }
}