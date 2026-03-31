<?php
if (!defined('SITE_NAME')) require_once __DIR__ . '/config.php';

$page_title      = $page_title      ?? SITE_NAME;
$page_desc       = $page_desc       ?? 'Browse professionally managed rental units — find your perfect space.';
$body_class      = $body_class      ?? '';
$active_nav      = $active_nav      ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta property="og:type" content="website">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Inline the API base so JS can use it regardless of nesting -->
  <script>window.__CODEX_API_BASE__ = 'api';</script>
</head>
<body class="<?= htmlspecialchars($body_class) ?>">

<nav class="site-nav" id="site-nav">
  <div class="container nav-inner">
    <a href="index.php" class="nav-brand" aria-label="<?= SITE_NAME ?> home">
      <div class="nav-brand-icon">P</div>
      <span class="nav-brand-name"><?= htmlspecialchars(SITE_NAME) ?></span>
    </a>

    <ul class="nav-links" id="nav-links">
      <li><a href="index.php"    class="<?= $active_nav === 'home'     ? 'active' : '' ?>">Home</a></li>
      <li><a href="listings.php" class="<?= $active_nav === 'listings' ? 'active' : '' ?>">Listings</a></li>
    </ul>

    <div class="nav-actions">
      <a href="<?= APP_URL ?>" class="btn-nav-app" target="_blank" rel="noopener">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Owner Login
      </a>
      <button class="nav-mobile-toggle" id="mobile-toggle" aria-label="Menu" aria-expanded="false">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="4" y1="7"  x2="20" y2="7"/>
          <line x1="4" y1="12" x2="20" y2="12"/>
          <line x1="4" y1="17" x2="20" y2="17"/>
        </svg>
      </button>
    </div>
  </div>
</nav>

<script>
  (function(){
    const toggle = document.getElementById('mobile-toggle');
    const links  = document.getElementById('nav-links');
    if (!toggle || !links) return;
    toggle.addEventListener('click', function(){
      const open = links.style.display === 'flex';
      links.style.display = open ? '' : 'flex';
      links.style.flexDirection = 'column';
      links.style.position = 'absolute';
      links.style.top = '68px';
      links.style.left = '0';
      links.style.right = '0';
      links.style.background = 'rgba(29,51,84,.98)';
      links.style.padding = '16px 24px';
      toggle.setAttribute('aria-expanded', !open);
      if(open){ links.removeAttribute('style'); }
    });
  })();
</script>
