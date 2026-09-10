<?php
/** Shared admin chrome. Pages set $admin_title and $admin_active before including. */
require_admin();
$me    = admin_user();
$flash = flash_get();

$nav_counts = [
    'enquiries'    => (int)scalar($pdo, "SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
    'applications' => (int)scalar($pdo, "SELECT COUNT(*) FROM job_applications WHERE status = 'new'"),
];

$nav = [
    ['dashboard',    'index.php',        'fa-gauge-high',        'Dashboard'],
    ['partners',     'partners.php',     'fa-handshake',         'Partners'],
    ['educators',    'educators.php',    'fa-chalkboard-user',   'Educators'],
    ['students',     'students.php',     'fa-user-graduate',     'Students'],
    ['batches',      'batches.php',      'fa-layer-group',       'Batches'],
    ['applications', 'applications.php', 'fa-file-lines',        'Applications'],
    ['enquiries',    'enquiries.php',    'fa-inbox',             'Enquiries'],
    ['blogs',        'blogs.php',        'fa-pen-nib',           'Blogs'],
    ['settings',     'settings.php',     'fa-gear',              'Settings'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($admin_title ?? 'Admin') ?> | SSD Prayas Admin Console</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
</head>
<body>

<!-- Sidebar Navigation -->
<aside class="sidebar" id="sidebar">
  <div class="side-header">
    <a href="index.php" class="side-logo-link" title="SSD Prayas Admin">
      <img src="<?= e(asset(SITE_LOGO)) ?>" alt="<?= e(SITE_NAME) ?>" class="side-logo-img">
    </a>
    <div class="side-console-badge">
      <span class="scb-pulse"></span>
      <span class="scb-text">Control Console</span>
      <span class="scb-ver">v2.4</span>
    </div>
  </div>

  <nav class="side-nav">
    <div class="side-nav-title">Navigation</div>
    <?php foreach ($nav as [$key, $href, $icon, $label]): ?>
      <a href="<?= e($href) ?>" class="<?= ($admin_active ?? '') === $key ? 'active' : '' ?>">
        <span class="nav-icon-wrap"><i class="fas <?= e($icon) ?>"></i></span>
        <span class="nav-label"><?= e($label) ?></span>
        <?php if (!empty($nav_counts[$key])): ?>
          <span class="nav-badge"><?= $nav_counts[$key] ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="side-foot">
    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="foot-link">
      <span class="foot-icon"><i class="fas fa-arrow-up-right-from-square"></i></span>
      <span>View Public Site</span>
    </a>
    <a href="logout.php" class="foot-link is-logout">
      <span class="foot-icon"><i class="fas fa-right-from-bracket"></i></span>
      <span>Sign Out</span>
    </a>
  </div>
</aside>

<!-- Main Admin Layout -->
<div class="admin-main">
  <header class="admin-top">
    <!-- Top Left: Title & Breadcrumbs -->
    <div class="admin-top-left">
      <button class="side-toggle" id="sideToggle" aria-label="Toggle Menu"><i class="fas fa-bars"></i></button>
      <div class="page-title-box">
        <div class="admin-breadcrumbs">
          <a href="index.php"><i class="fas fa-house-chimney"></i> Admin Console</a>
          <i class="fas fa-chevron-right bc-sep"></i>
          <span class="bc-active"><?= e($admin_title ?? 'Dashboard') ?></span>
        </div>
        <h1 class="page-heading"><?= e($admin_title ?? 'Dashboard') ?></h1>
      </div>
    </div>

    <!-- Top Center: Global Quick Search -->
    <div class="admin-top-center">
      <div class="top-search-bar" id="topSearchBar">
        <i class="fas fa-magnifying-glass search-ico"></i>
        <input type="text" placeholder="Quick search modules, enquiries, partners..." id="topSearchInput">
        <span class="search-shortcut"><kbd>Ctrl</kbd> <kbd>K</kbd></span>
      </div>
    </div>

    <!-- Top Right: Status, Notifications & User Profile -->
    <div class="admin-top-right">
      <!-- Live Status -->
      <div class="top-status-indicator" title="System online and syncing with database">
        <span class="status-ping"></span>
        <span class="status-text">Live System</span>
      </div>

      <!-- Quick Action Bell -->
      <a href="enquiries.php" class="top-action-btn" title="Pending Enquiries">
        <i class="far fa-bell"></i>
        <?php if (!empty($nav_counts['enquiries']) || !empty($nav_counts['applications'])): ?>
          <span class="top-bell-badge"><?= ($nav_counts['enquiries'] + $nav_counts['applications']) ?></span>
        <?php endif; ?>
      </a>

      <!-- Quick Link to Website -->
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="top-site-btn" title="Visit Public Portal">
        <i class="fas fa-external-link-alt"></i>
        <span>Live Site</span>
      </a>

      <!-- User Profile Card -->
      <div class="admin-user-card">
        <div class="user-avatar-box">
          <div class="avatar"><?= e(strtoupper(substr($me['name'] ?? 'A', 0, 1))) ?></div>
          <span class="online-indicator"></span>
        </div>
        <div class="user-info-text">
          <span class="user-name"><?= e($me['name']) ?></span>
          <span class="user-role-tag"><?= e(ucfirst($me['role'] ?? 'Superadmin')) ?></span>
        </div>
      </div>
    </div>
  </header>

  <div class="admin-body">
    <?php if ($flash): ?>
      <div class="note note-<?= e($flash['type']) ?>">
        <i class="fas <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
        <span><?= e($flash['msg']) ?></span>
      </div>
    <?php endif; ?>
