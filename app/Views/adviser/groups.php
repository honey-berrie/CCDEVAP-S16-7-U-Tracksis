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
  <title>Thesis Groups | U-Tracksis</title>

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
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/adviser/groups.css">
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
        <a class="nav-link active" href="<?= BASE_URL ?>/adviser/groups">
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
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/notifications">
          <img src="<?= BASE_URL ?>/assets/icons/bell-fill.svg" alt="" class="bi"> Notifications
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
        <p class="page-eyebrow">THESIS</p>
        <h1 class="page-title">Thesis Groups</h1>
        <p class="page-subtitle">Assigned groups and their milestone progress.</p>
      </div>

      <!-- group cards -->
      <div class="group-cards-grid">
        <?php if (empty($groups)): ?>
          <p class="text-muted">No thesis groups assigned yet.</p>
        <?php endif; ?>

        <?php foreach ($groups as $group): ?>
          <?php
            $progress = (int) $group['progress_percent'];
            if ($group['progress_status'] === 'falling behind') {
                $statusLabel = 'Behind';
                $statusClass = 'behind';
            } elseif ($progress < 50) {
                $statusLabel = 'At Risk';
                $statusClass = 'at-risk';
            } else {
                $statusLabel = 'On Track';
                $statusClass = 'on-track';
            }
          ?>
          <div class="group-card">
            <div class="card-header">
              <h3><?= htmlspecialchars($group['group_name']) ?></h3>
              <span class="badge-status badge-<?= $statusClass ?>"><?= $statusLabel ?></span>
            </div>
            <p class="project-title"><?= htmlspecialchars($group['thesis_title']) ?></p>
            <p class="program"><?= htmlspecialchars($group['member_count']) ?> members</p>

            <div class="card-progress">
              <div class="progress flex-grow-1" style="height: 8px;">
                <div class="progress-bar <?= $statusClass === 'behind' ? 'progress-behind' : '' ?>" style="width: <?= $progress ?>%;"></div>
              </div>
              <span class="progress-text"><?= $progress ?>%</span>
            </div>

            <div class="card-footer">
              <span class="milestone-info"><?= htmlspecialchars((string) $group['completed_milestones']) ?> of <?= htmlspecialchars((string) $group['total_milestones']) ?> milestones done</span>
              <span class="next-milestone">Next: <?= htmlspecialchars($group['next_milestone']) ?></span>
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