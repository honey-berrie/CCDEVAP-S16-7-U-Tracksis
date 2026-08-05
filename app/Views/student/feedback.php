<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $feedbacks
 */

require_once __DIR__ . '/../../../config/session.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Feedback | U-Tracksis</title>

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

  <!-- feedback specific css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/feedback.css">
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
        <a class="nav-link active" href="<?= BASE_URL ?>/student/feedback">
          <img src="<?= BASE_URL ?>/assets/icons/chat-left-text-fill.svg" alt="" class="bi"> Feedbacks
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/consultations">
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

      <!-- page header -->
      <div class="page-header">
        <p class="page-eyebrow">CHAPTER</p>
        <h1 class="page-title">Feedbacks</h1>
        <p class="page-subtitle">Comments and revisions from your adviser and panel members. Click a chapter to view all feedback.</p>
      </div>

      <!-- chapter list -->
      <div class="chapter-list">

        <?php if (empty($feedbacks)): ?>
          <p class="text-muted small text-center">No feedbacks found.</p>
        <?php else : ?>
          <?php foreach ($feedbacks as $feedback): ?>
            <div class="chapter-card" data-chapter="<?= htmlspecialchars($feedback['document_type_label']) ?>" data-id="<?= htmlspecialchars($feedback['id']) ?>" data-document-type="<?= htmlspecialchars($feedback['document_type_label']) ?>" data-feedbacks="<?= htmlspecialchars(json_encode($feedback['messages'])) ?>">
              <div class="chapter-icon">
                <img src="<?= BASE_URL ?>/assets/icons/chat-left-text-fill.svg" alt="" class="bi">
              </div>
              <div class="chapter-info">
                <p class="chapter-title"><?= htmlspecialchars($feedback['document_type_label']) ?></p>
                <p class="chapter-meta"><span><?= $feedback['count'] ?></span> feedback . Last updated <?= htmlspecialchars($feedback['last_updated']) ?></p>
              </div>
              <div class="chapter-side">
                <span class="chapter-count"><?= $feedback['count'] ?></span>
                <img src="<?= BASE_URL ?>/assets/icons/chevron-right.svg" alt="" class="bi chapter-chevron">
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

    </main>
  </div>

  <!-- feedback modal -->
  <div class="preview-overlay" id="fbOverlay">
    <div class="preview-modal">
      <div class="preview-header">
        <div>
          <p class="preview-eyebrow">FEEDBACK</p>
          <h3 class="preview-title" id="fbChapter">Chapter</h3>
        </div>
        <button type="button" class="preview-close" id="fbClose" aria-label="Close">
          <img src="<?= BASE_URL ?>/assets/icons/x-lg.svg" alt="" class="bi">
        </button>
      </div>

      <div class="preview-body" id="fbBody">
        <!-- feedback items injected here -->
      </div>

      <div class="preview-footer">
        <button type="button" class="preview-btn preview-btn-secondary" id="fbPreview" style="display:none;">
          <img src="<?= BASE_URL ?>/assets/icons/eye.svg" alt="" class="bi">
          View Submission
        </button>
        <button type="button" class="preview-btn preview-btn-primary" id="fbCloseBtn">Close</button>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/student/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/student/sidebar.js"></script>
  <script src="<?= BASE_URL ?>/js/student/feedback.js"></script>
  
</body>
</html>
