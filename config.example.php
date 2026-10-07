<?php
// Copy to config.local.php on hosts that do not support environment variables.
// Keep this file OUTSIDE the public document root. Do not commit real secrets.
return [
    'base_path' => '/',
    // PostgreSQL: use database_url OR the PDO DSN/user/password below.
    'database_url' => '',
    'db_dsn' => 'pgsql:host=localhost;port=5432;dbname=wales_webs',
    // For MySQL: mysql:host=localhost;dbname=wales_webs;charset=utf8mb4
    'db_user' => 'your_database_user',
    'db_password' => '',
    'blog_admin_password' => '',
];