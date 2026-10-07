<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#060a09">
<link rel="icon" href="<?= e(asset_url('/assets/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset_url('/assets/site.css')) ?>"><link rel="stylesheet" href="<?= e(asset_url('/assets/admin.css')) ?>">
<script src="<?= e(asset_url('/assets/admin.js')) ?>" defer></script></head>
<body class="private-workspace"<?= !empty($printNow) ? ' data-print-on-load="1"' : '' ?>>
<header class="desk-header">
  <a class="brand" href="<?= e(url('/')) ?>"><span class="logo" aria-hidden="true"><svg viewBox="0 0 48 48" width="46" height="46"><polygon class="logo-hex" points="24,3 42,13.5 42,34.5 24,45 6,34.5 6,13.5" fill="#07110d" stroke="#2cff8f" stroke-width="1.4"/><text x="24" y="31" text-anchor="middle" class="logo-w">W</text><g class="orbit"><path class="star" d="M24 0.5l1.1 2.4 2.5.4-1.8 1.8.4 2.5L24 6.3l-2.2 1.3.4-2.5-1.8-1.8 2.5-.4z"/></g></svg></span><span class="brand-text"><strong>Wales &amp; Webs</strong><small>PRIVATE WORKSPACE</small></span></a>
  <?php if (admin_signed_in()): ?>
  <nav class="desk-nav" aria-label="Private workspace">
    <a href="<?= e(url('/usabime/admin')) ?>"<?= $page === 'request-admin' ? ' aria-current="page"' : '' ?>>Usabime requests</a>
    <a href="<?= e(url('/admin/contact')) ?>"<?= $page === 'contact-admin' ? ' aria-current="page"' : '' ?>>Contact inbox</a>
    <a href="<?= e(url('/journal/studio')) ?>"<?= $page === 'blog-admin' ? ' aria-current="page"' : '' ?>>Journal studio</a>
    <form method="post" action="<?= e(url('/admin/export')) ?>"><?= csrf_field() ?><button type="submit" class="text-button" title="Includes private customer records and SQL. Keep the archive secure.">Export PHP + SQL</button></form>
    <form method="post" action="<?= e(url('/admin/logout')) ?>"><?= csrf_field() ?><button type="submit" class="text-button">Sign out</button></form>
  </nav>
  <?php else: ?><a class="muted" href="<?= e(url('/')) ?>">Public site →</a><?php endif; ?>
</header>