<?php
// Usage: $pageTitle, $pageDesc, $active (nav key), $appLayout (bool), $bodyClass
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'StudySync — Timetable & Study Planner Generator';
$pageDesc  = $pageDesc ?? 'Auto-generate a balanced weekly study timetable, then drag blocks to fine-tune it.';
$appLayout = $appLayout ?? false;
$active    = $active ?? '';
$bodyClass = $bodyClass ?? '';
$u         = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <meta name="base-url" content="<?= e(BASE_URL) ?>">
  <link rel="icon" href="<?= e(url('assets/img/favicon.ico')) ?>">
  
  <!-- Immediate theme application to prevent flash -->
  <script>
    (function() {
      try {
        var t = localStorage.getItem('studysync_theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<?php if ($appLayout): $nav = ['dashboard' => 'Dashboard', 'timetable' => 'Timetable', 'insights' => 'Insights', 'settings' => 'Settings']; ?>
<div class="app">
  <header class="topbar">
    <div class="container topbar-inner">
      <a href="<?= e(url('dashboard.php')) ?>" class="brand">
        <span class="brand-spark">&#x2728;</span> Study<span>Sync</span>
      </a>
      <nav class="nav" aria-label="Main">
        <?php foreach ($nav as $key => $label): ?>
          <a href="<?= e(url($key . '.php')) ?>" class="nav-link<?= $active === $key ? ' active' : '' ?>">
            <?= e($label) ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <div class="topbar-actions">
        <button type="button" class="theme-toggle-btn btn btn-ghost btn-sm btn-icon" title="Toggle dark/light mode">
          <span class="theme-icon">&#x1F319;</span>
        </button>
        <?php if ($u): ?>
          <span class="user-avatar" title="<?= e($u['email']) ?>">
            <?= strtoupper(substr($u['display_name'] ?: $u['email'], 0, 1)) ?>
          </span>
        <?php endif; ?>
        <a href="<?= e(url('logout.php')) ?>" class="btn btn-ghost btn-sm signout" title="Sign out of account">Sign out</a>
      </div>
    </div>
  </header>
  <main class="container main">
<?php endif; ?>
