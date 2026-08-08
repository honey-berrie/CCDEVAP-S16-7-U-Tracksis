<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array      $milestones
 * @var int         $total_count
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
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/dashboard.css">

  <!-- milestones specific css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/milestones.css">
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
          <p class="sidebar-subtitle">STUDENT</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="<?= BASE_URL ?>/student/dashboard">
          <img src="<?= BASE_URL ?>/assets/icons/grid-1x2-fill.svg" alt="" class="bi"> Dashboard
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/group-profile">
          <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="bi"> Group Profile
        </a>
        <a class="nav-link active" href="<?= BASE_URL ?>/student/milestones">
          <img src="<?= BASE_URL ?>/assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/submissions">
          <img src="<?= BASE_URL ?>/assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/feedback">
          <img src="<?= BASE_URL ?>/assets/icons/chat-left-text-fill.svg" alt="" class="bi"> Feedbacks
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/consultations">
          <img src="<?= BASE_URL ?>/assets/icons/chat-dots-fill.svg" alt="" class="bi"> Consultations
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/announcements">
  <img src="<?= BASE_URL ?>/assets/icons/megaphone-fill.svg" alt="" class="bi"> Announcements
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

      <!-- page header -->
      <div class="page-header">
        <p class="page-eyebrow">TIMELINE</p>
        <h1 class="page-title">Milestones &amp; Deadlines</h1>
        <p class="page-subtitle">A complete timeline for every chapter and defense, synced to the academic year.</p>
      </div>

      <!-- timeline box -->
      <div class="box" id="timeline-box">
            <?php if (empty($milestones)) : ?>
                <p class="text-muted small text-center">No milestones found.</p>
            <?php else : ?>
                <!-- Render milestones here -->
                <?php foreach ($milestones as $milestone) : ?>
                    <div class="timeline-item">
                        <div class="timeline-number"><?= $milestone['display_order'] ?></div>
                        <div class="timeline-body">
                            <p class="timeline-name"><?= htmlspecialchars($milestone['name']) ?> <span><?= htmlspecialchars($milestone['description']) ?></span></p>
                            <p class="timeline-date">due <?= htmlspecialchars($milestone['due_date_formatted']) ?></p>
                            <div class="progress timeline-progress">
                                <div class="progress-bar" style="width: <?= $milestone['progress'] ?>%"></div>
                            </div>
                        </div>
                        <div class="timeline-side">
                            <span class="badge-status <?= $milestone['status_class'] ?>"><?= htmlspecialchars($milestone['status_label']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
      </div>

    </main>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/student/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/student/sidebar.js"></script>

</body>
</html>
