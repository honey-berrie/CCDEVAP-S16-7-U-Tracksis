<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $upcoming
 * @var array       $history
 */

require_once __DIR__ . '/../../../config/session.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Consultations | U-Tracksis</title>

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
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/adviser/consultations.css">
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
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/milestones">
          <img src="<?= BASE_URL ?>/assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/submissions">
          <img src="<?= BASE_URL ?>/assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link active" href="<?= BASE_URL ?>/adviser/consultations">
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
        <p class="page-eyebrow">SCHEDULE</p>
        <h1 class="page-title">Consultations</h1>
        <p class="page-subtitle">View upcoming consultation requests and review history.</p>
      </div>

      <!-- upcoming consultations -->
      <div class="consultation-section">
        <p class="box-label">UPCOMING</p>
        <?php if (empty($upcoming)): ?>
          <div class="box">
            <p class="text-muted small mb-0">No upcoming consultations.</p>
          </div>
        <?php endif; ?>

        <?php foreach ($upcoming as $c): ?>
          <div class="consultation-card">
            <div class="consultation-date">
              <span class="cons-month"><?= htmlspecialchars($c['month']) ?></span>
              <strong class="cons-day"><?= htmlspecialchars($c['day']) ?></strong>
            </div>
            <div class="consultation-details">
              <h4><?= htmlspecialchars($c['topic'] ?: 'Consultation') ?></h4>
              <p class="cons-group"><?= htmlspecialchars($c['group_name']) ?></p>
              <p class="cons-time"><?= htmlspecialchars($c['time_range']) ?></p>
              <?php if ($c['meeting_link']): ?>
                <p class="cons-link">Via: <?= htmlspecialchars($c['meeting_link']) ?></p>
              <?php endif; ?>
              <?php if ($c['agenda']): ?>
                <p class="cons-agenda"><?= htmlspecialchars($c['agenda']) ?></p>
              <?php endif; ?>
            </div>
            <div class="consultation-side">
              <span class="badge-status <?= $c['status_class'] ?>"><?= htmlspecialchars($c['status_label']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- consultation history -->
      <div class="consultation-section mt-4">
        <p class="box-label">HISTORY</p>
        <?php if (empty($history)): ?>
          <div class="box">
            <p class="text-muted small mb-0">No completed consultations.</p>
          </div>
        <?php endif; ?>

        <?php foreach ($history as $c): ?>
          <div class="history-card">
            <div class="history-avatar">
              <img src="<?= BASE_URL ?>/assets/icons/chat-dots-fill.svg" alt="" class="bi">
            </div>
            <div class="history-meta">
              <h4><?= htmlspecialchars($c['topic'] ?: 'Consultation') ?></h4>
              <p><?= htmlspecialchars($c['group_name']) ?> &middot; <?= htmlspecialchars($c['time_range']) ?></p>
              <?php if ($c['requester_name']): ?>
                <p class="history-requester">Requested by: <?= htmlspecialchars($c['requester_name']) ?></p>
              <?php endif; ?>
            </div>
            <div class="history-notes">
              <span class="history-notes-label">Notes</span>
              <p><?= htmlspecialchars($c['adviser_notes'] ?: $c['agenda'] ?: 'No notes recorded.') ?></p>
            </div>
            <div class="history-side">
              <span class="badge-status <?= $c['status_class'] ?>"><?= htmlspecialchars($c['status_label']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </main>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/sidebar.js"></script>
</body>
</html>