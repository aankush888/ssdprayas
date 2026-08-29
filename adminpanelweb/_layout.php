<?php
/** Shared admin chrome. Pages set $admin_title and $admin_active before including. */
require_admin();
$me    = admin_user();
$flash = flash_get();

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
<title><?= e($admin_title ?? 'Admin') ?> | SSD Prayas</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
</head>
<body>

<aside class="sidebar" id="sidebar">
  <a href="index.php" class="side-brand">
    <img src="<?= e(asset(SITE_LOGO)) ?>" alt="<?= e(SITE_NAME) ?>">
    <small>Admin Panel</small>
  </a>

  <nav class="side-nav">
    <?php foreach ($nav as [$key, $href, $icon, $label]): ?>
      <a href="<?= e($href) ?>" class="<?= ($admin_active ?? '') === $key ? 'active' : '' ?>">
        <i class="fas <?= e($icon) ?>"></i> <?= e($label) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="side-foot">
    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> View Website</a>
    <a href="logout.php" class="is-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>

<div class="admin-main">
  <header class="admin-top">
    <button class="side-toggle" id="sideToggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
    <div>
      <h1><?= e($admin_title ?? 'Dashboard') ?></h1>
      <?php if (!empty($admin_sub)): ?><p><?= e($admin_sub) ?></p><?php endif; ?>
    </div>
    <div class="admin-user">
      <span><?= e($me['name']) ?></span>
      <div class="avatar"><?= e(strtoupper(substr($me['name'], 0, 1))) ?></div>
    </div>
  </header>

  <div class="admin-body">
    <?php if ($flash): ?>
      <div class="note note-<?= e($flash['type']) ?>">
        <i class="fas <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
        <span><?= e($flash['msg']) ?></span>
      </div>
    <?php endif; ?>
