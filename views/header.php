<?php
$pageTitle = $pageTitle ?? 'Wales & Webs | Digital Agency for Growing Businesses';
$pageDescription = $pageDescription ?? 'Modern websites, smart systems, automation and digital growth for Nigerian founders.';
$page = $page ?? '';
$nav = [
  ['journal', 'Tech Stories', '/journal'],
];
$svc = [
  ['digital-presence', 'Digital Presence'],
  ['business-systems', 'Business Systems'],
  ['automation-ai', 'Automation & AI'],
  ['digital-growth', 'Digital Growth'],
];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="theme-color" content="#060a09">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:type" content="<?= $page === 'story' ? 'article' : 'website' ?>">
<meta property="og:image" content="<?= e(url('/assets/wales_anime_social.png')) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(asset_url('/assets/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset_url('/assets/site.css')) ?>">
<?php if ($page === 'request'): ?><link rel="stylesheet" href="<?= e(asset_url('/assets/admin.css')) ?>"><?php endif; ?>
<script src="<?= e(asset_url('/assets/site.js')) ?>" defer></script>
</head>
<body class="page-<?= e($page) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header" data-header>
  <div class="header-inner">
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="Wales and Webs home">
      <span class="logo" aria-hidden="true">
        <svg viewBox="0 0 48 48" width="46" height="46">
          <polygon class="logo-hex" points="24,3 42,13.5 42,34.5 24,45 6,34.5 6,13.5" fill="#07110d" stroke="#2cff8f" stroke-width="1.4"/>
          <text x="24" y="31" text-anchor="middle" class="logo-w">W</text>
          <g class="orbit"><path class="star" d="M24 0.5l1.1 2.4 2.5.4-1.8 1.8.4 2.5L24 6.3l-2.2 1.3.4-2.5-1.8-1.8 2.5-.4z"/></g>
        </svg>
      </span>
      <span class="brand-text"><strong>Wales &amp; Webs</strong><small>BUILD · AUTOMATE · GROW</small></span>
    </a>
    <nav class="main-nav" id="main-nav" aria-label="Primary">
      <a href="<?= e(url('/journal')) ?>"<?= $page === 'journal' || $page === 'story' ? ' aria-current="page"' : '' ?>>Tech Stories</a>
      <div class="has-menu" data-menu>
        <button type="button" class="menu-toggle" aria-expanded="false" aria-controls="services-menu">Our Services
          <svg viewBox="0 0 12 12" width="10" height="10" aria-hidden="true"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></button>
        <ul class="submenu" id="services-menu">
          <?php foreach ($svc as $s): ?><li><a href="<?= e(url('/#service-' . $s[0])) ?>"><?= e($s[1]) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <a href="<?= e(url('/journal')) ?>">Our Work</a>
      <a href="<?= e(url('/contact')) ?>"<?= $page === 'contact' ? ' aria-current="page"' : '' ?>>Contact Us</a>
      <a class="pill nav-pill" href="<?= e(url('/usabime/request')) ?>">Usabime</a>
    </nav>
    <button type="button" class="nav-burger" aria-expanded="false" aria-controls="main-nav" aria-label="Toggle menu"><span></span><span></span><span></span></button>
  </div>
</header>
