<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $announcements
 */

require_once __DIR__ . '/../../../config/session.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Announcements | U-Tracksis</title>

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

  <!-- dashboard css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/dashboard.css">

  <!-- announcements specific css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/announcements.css">
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
        <a class="nav-link" href="<?= BASE_URL ?>/student/milestones">
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
        <a class="nav-link active" href="<?= BASE_URL ?>/student/announcements">
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
          <span class="topbar-user" id="topbar-user"><?= $userName ?></span>
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
        <p class="page-eyebrow">BULLETIN</p>
        <h1 class="page-title">Announcements</h1>
        <p class="page-subtitle">Updates and reminders from your advisers.</p>
      </div>

      <!-- announcements list -->
      <div class="notif-list" id="notifList">
        <!-- cards injected by announcements.js -->
        <?php if (empty($announcements)): ?>
            <p class="text-muted small text-center">No announcements available.</p>
        <?php else: ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="notif-card <?= $announcement['is_read'] ? 'read' : 'unread' ?>" data-id="<?= $announcement['id'] ?>" data-announcement="<?= htmlspecialchars(json_encode($announcement), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="notif-icon"><img src="<?= BASE_URL ?>/assets/icons/megaphone-fill.svg" alt="" class="bi"></div>
                    <div class="notif-body">
                        <p class="notif-title"><?= htmlspecialchars($announcement['title']) ?></p>
                        <p class="notif-meta">From <span><?= htmlspecialchars($announcement['author']) ?></span> . <?= htmlspecialchars($announcement['author_role']) ?></p>
                        <p class="notif-time"><?= $announcement['time_ago'] ?></p>
                    </div>
                    <div class="notif-mark"></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </main>
  </div>

  <!-- announcement read modal -->
  <div class="preview-overlay" id="annOverlay">
    <div class="preview-modal">
      <div class="preview-header">
        <div>
          <p class="preview-eyebrow">ANNOUNCEMENT</p>
          <h3 class="preview-title" id="annTitle">Title</h3>
        </div>
        <button type="button" class="preview-close" id="annClose" aria-label="Close">
          <img src="<?= BASE_URL ?>/assets/icons/x-lg.svg" alt="" class="bi">
        </button>
      </div>

      <div class="preview-body">
        <div class="ann-sender">
          <div class="ann-avatar" id="annAvatar">--</div>
          <div class="ann-sender-info">
            <p class="ann-sender-name" id="annSender">--</p>
            <p class="ann-sender-role" id="annRole">--</p>
          </div>
        </div>

        <div class="ann-message">
          <p id="annMessage">--</p>
        </div>

        <div class="ann-footer-meta">
          <span class="ann-date" id="annDate">--</span>
          <span class="ann-time" id="annTime">--</span>
        </div>
      </div>

      <div class="preview-footer">
        <button type="button" class="preview-btn preview-btn-primary" id="annCloseBtn">Close</button>
      </div>
    </div>
  </div>

  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/student/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/student/sidebar.js"></script>
  <script src="<?= BASE_URL ?>/js/student/announcements.js"></script>

</body>
</html>