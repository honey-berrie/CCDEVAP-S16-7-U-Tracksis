<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $groups
 */

require_once __DIR__ . '/../../../config/session.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Milestones | U-Tracksis</title>

  <!-- Apply theme immediately -->
  <script>
    (function() {
      var t = localStorage.getItem('theme');
      var d = t || (window.matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light');
      if (d === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    })();
  </script>

  <!-- bootstrap css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css">

  <!-- local bootstrap icons -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap-icons.css">

  <!-- custom css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/adviser/dashboard.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/adviser/milestones.css">
</head>
<body>
  <script src="<?= BASE_URL ?>/js/maintenance-check.js"></script>

  <div class="dashboard-wrapper">

    <!-- sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="U-Tracksis" class="sidebar-logo">
        <div>
          <h6 class="sidebar-title">U-Tracksis</h6>
          <p class="sidebar-subtitle">ADVISER</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/dashboard">
          <img src="<?= BASE_URL ?>/assets/icons/grid-1x2-fill.svg" alt="" class="bi"> Dashboard
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/groups">
          <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="bi"> Thesis Groups
        </a>
        <a class="nav-link active" href="<?= BASE_URL ?>/adviser/milestones">
          <img src="<?= BASE_URL ?>/assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/submissions">
          <img src="<?= BASE_URL ?>/assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/consultations">
          <img src="<?= BASE_URL ?>/assets/icons/chat-dots-fill.svg" alt="" class="bi"> Consultations
        </a>
      </nav>

      <div class="sidebar-footer">
        <span>Theme</span>
        <button class="theme-toggle" id="themeToggle" type="button" title="Toggle theme">
          <img src="<?= BASE_URL ?>/assets/icons/moon-stars-fill.svg" alt="" class="bi" id="themeIcon">
        </button>
      </div>
    </aside>

    <!-- mobile overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- main content -->
    <main class="main-content">

      <!-- top bar -->
      <div class="top-bar">
        <button class="mobile-menu-btn" id="menuBtn" type="button">
          <img src="<?= BASE_URL ?>/assets/icons/list.svg" alt="" class="bi">
        </button>

        <div class="topbar-actions">
          <span class="topbar-user" id="topbar-user"><?= htmlspecialchars($userName) ?></span>
          <form action="<?= BASE_URL ?>/logout" method="get">
            <button class="btn-logout" type="submit">
              <img src="<?= BASE_URL ?>/assets/icons/box-arrow-left.svg" alt="" class="bi">
              <span>Logout</span>
            </button>
          </form>
        </div>
      </div>

      <div class="page-header">
        <p class="page-eyebrow">TRACKER</p>
        <h1 class="page-title">Milestone Tracker</h1>
        <p class="page-subtitle">Cross-group milestone progress overview.</p>
      </div>

      <!-- milestone groups -->
      <?php if (empty($groups)): ?>
        <div class="box">
          <p class="text-muted small mb-0">No milestone records found.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($groups as $group): ?>
        <div class="milestone-panel">
          <div class="milestone-panel-header">
            <h3><?= htmlspecialchars($group['group_name']) ?></h3>
            <p class="milestone-panel-subtitle"><?= htmlspecialchars($group['thesis_title']) ?></p>
          </div>

          <div class="milestone-scroll">
            <?php if (empty($group['milestones'])): ?>
              <p class="text-muted small">No milestones assigned yet.</p>
            <?php endif; ?>

            <?php foreach ($group['milestones'] as $milestone): ?>
              <div class="milestone-card milestone-<?= $milestone['status_class'] ?>">
                <h4><?= htmlspecialchars($milestone['milestone_name']) ?></h4>
                <span class="badge-status <?= $milestone['status_class'] ?>"><?= htmlspecialchars($milestone['status_label']) ?></span>
                <?php if ($milestone['due_date_formatted']): ?>
                  <p class="milestone-due">Due: <?= htmlspecialchars($milestone['due_date_formatted']) ?></p>
                <?php endif; ?>
                <?php if ($milestone['submitted_at_formatted']): ?>
                  <p class="milestone-submitted">Submitted: <?= htmlspecialchars($milestone['submitted_at_formatted']) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

    </main>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/sidebar.js"></script>
</body>
</html>