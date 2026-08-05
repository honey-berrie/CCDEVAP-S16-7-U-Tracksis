<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $assignedGroups
 * @var int         $pendingReviews
 * @var int         $upcomingConsultations
 * @var int         $unreadAlerts
 * @var array       $groupProgress
 * @var array       $recentActivities
 */

require_once __DIR__ . '/../../../config/session.php';

$currentDate = date('l, F j, Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adviser Dashboard | U-Tracksis</title>

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
        <a class="nav-link active" href="<?= BASE_URL ?>/adviser/dashboard">
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
        <p class="page-eyebrow"><?= htmlspecialchars($currentDate) ?></p>
        <h1 class="page-title">Good day, <?= htmlspecialchars($firstname) ?></h1>
        <p class="page-subtitle">Overview of your assigned thesis groups and pending tasks.</p>
      </div>

      <!-- stat cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="box stat-box">
            <p class="box-label">ASSIGNED GROUPS</p>
            <div class="stat-value"><?= count($assignedGroups) ?></div>
            <?php
              $needsAttention = 0;
              foreach ($assignedGroups as $g) {
                  if ($g['progress_status'] === 'falling behind') $needsAttention++;
              }
            ?>
            <?php if ($needsAttention > 0): ?>
              <p class="stat-label-sm text-warning"><?= $needsAttention ?> need your attention</p>
            <?php else: ?>
              <p class="stat-label-sm">All on track</p>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="box stat-box">
            <p class="box-label">PENDING REVIEWS</p>
            <div class="stat-value"><?= $pendingReviews ?></div>
            <p class="stat-label-sm">Awaiting your action</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="box stat-box">
            <p class="box-label">UPCOMING CONSULTATIONS</p>
            <div class="stat-value"><?= $upcomingConsultations ?></div>
            <p class="stat-label-sm">This week</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="box stat-box">
            <p class="box-label">UNREAD ALERTS</p>
            <div class="stat-value"><?= $unreadAlerts ?></div>
            <p class="stat-label-sm">Notifications</p>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <!-- group progress panel -->
        <div class="col-lg-8">
          <div class="box">
            <p class="box-label">GROUP PROGRESS</p>
            <?php if (empty($groupProgress)): ?>
              <p class="text-muted small">No groups assigned yet.</p>
            <?php else: ?>
              <?php foreach ($groupProgress as $gp): ?>
                <div class="progress-item">
                  <span class="progress-group-name"><?= htmlspecialchars($gp['group_name']) ?></span>
                  <div class="progress flex-grow-1 mx-3" style="height: 10px;">
                    <div class="progress-bar" style="width: <?= $gp['progress'] ?>%;"></div>
                  </div>
                  <span class="progress-text"><?= $gp['progress'] ?>%</span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- recent activity panel -->
        <div class="col-lg-4">
          <div class="box">
            <p class="box-label">RECENT ACTIVITY</p>
            <?php if (empty($recentActivities)): ?>
              <p class="text-muted small">No recent activity.</p>
            <?php else: ?>
              <?php foreach ($recentActivities as $activity): ?>
                <div class="activity-item">
                  <img src="<?= BASE_URL ?>/assets/icons/<?= $activity['icon'] ?? 'question-circle' ?>.svg" alt="" class="activity-avatar">
                  <div>
                    <span class="activity-desc"><?= htmlspecialchars($activity['description']) ?></span>
                    <span class="activity-group"><?= htmlspecialchars($activity['group_name'] ?? '') ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/sidebar.js"></script>
</body>
</html>