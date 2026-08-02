<?php

if (!isset($userName, $firstname, $lastname, $overall_progress, $done_count, $total_count, $next_deadline_days, $defense_date, $days_to_defense, $latestFeedback, $recentActivities)) {
    throw new \RuntimeException('Missing required variables for dashboard view.');
}

$nextText = $next_deadline_days !== null
        ? ' next deadline in ' . $next_deadline_days . ' day' . ($next_deadline_days !== 1 ? 's' : '')
        : '';

$turnAroundText = $next_deadline_days !== null
        ? $next_deadline_days . ' day' . ($next_deadline_days !== 1 ? 's' : '')
        : 'No upcoming deadlines';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard | U-Tracksis</title>

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
</head>
<body>
  <script src="<?= BASE_URL ?>/js/maintenance-check.js"></script>

  <div class="dashboard-wrapper">

    <!-- sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <img src="../../assets/images/logo.png" alt="U-Tracksis" class="sidebar-logo">
        <div>
          <h6 class="sidebar-title">U-Tracksis</h6>
          <p class="sidebar-subtitle">STUDENT</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link active" href="dashboard.html">
          <img src="../../assets/icons/grid-1x2-fill.svg" alt="" class="bi"> Dashboard
        </a>
        <a class="nav-link" href="group-profile.html">
          <img src="../../assets/icons/people-fill.svg" alt="" class="bi"> Group Profile
        </a>
        <a class="nav-link" href="milestones.html">
          <img src="../../assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link" href="submissions.html">
          <img src="../../assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link" href="feedback.html">
          <img src="../../assets/icons/chat-left-text-fill.svg" alt="" class="bi"> Feedbacks
        </a>
        <a class="nav-link" href="consultations.html">
          <img src="../../assets/icons/chat-dots-fill.svg" alt="" class="bi"> Consultations
        </a>
        <a class="nav-link" href="announcements.html">
          <img src="../../assets/icons/megaphone-fill.svg" alt="" class="bi"> Announcements
        </a>
      </nav>

      <div class="sidebar-footer">
        <span>Theme</span>
        <button class="theme-toggle" id="themeToggle" type="button" title="Toggle theme">
          <img src="../../assets/icons/moon-stars-fill.svg" alt="" class="bi" id="themeIcon">
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
          <img src="../../assets/icons/list.svg" alt="" class="bi">
        </button>

        <div class="topbar-actions">
          <span class="topbar-user" id="topbar-user"><?= $userName ?></span>
          <form action="<?= BASE_URL ?>/logout" method="get">
            <button class="btn-logout" type="submit">
              <img src="../../assets/icons/box-arrow-left.svg" alt="" class="bi">
              <span>Logout</span>
            </button>
          </form>
        </div>
      </div>

      <div class="page-header">
        <h1 class="page-title">Student Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?= $firstname ?>.</p>
      </div>

      <div class="box mb-4">
        <p class="box-label">OVERALL THESIS PROGRESS</p>
        <h2 class="display-5 fw-bold mb-0" id="dashProgressPercent"><?= $overall_progress ?>%</h2>
        <div class="progress mt-2" style="height: 10px;">
          <div class="progress-bar" id="dashProgressBar" style="width: <?= $overall_progress ?>%;"></div>
        </div>
        <p class="stat-label-sm mt-2" id="dashProgressSummary"><?= htmlspecialchars($done_count . ' of ' . $total_count . ' milestones done' . $nextText) ?></p>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="box">
            <div class="d-flex justify-content-between align-items-start">
              <p class="box-label mb-0">DAYS TO DEFENSE</p>
              <img src="../../assets/icons/calendar-event.svg" alt="" class="bi text-muted">
            </div>
            <div class="stat-value" id="dashDefenseDays"><?= $days_to_defense !== null ? $days_to_defense : '--'; ?></div>
            <p class="stat-label-sm" id="dashDefenseDate"><?= $defense_date !== null ? $defense_date : 'No defense scheduled' ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box">
            <div class="d-flex justify-content-between align-items-start">
              <p class="box-label mb-0">EXPECTED TURNAROUND</p>
              <img src="../../assets/icons/hourglass-split.svg" alt="" class="bi text-muted">
            </div>
            <div class="stat-value" style="font-size: 1.5rem;" id="dashTurnaround"><?= $turnAroundText ?></div>
            <p class="stat-label-sm">Next Deadline</p>
          </div>
        </div>
      </div>

      <div class="box mb-4" id="dashMilestoneList">
        <p class="box-label">MILESTONES</p>
        <?php if (empty($milestones)) : ?>
          <p class="text-muted small">No recent activity.</p>
        <?php else : ?>
          <?php foreach ($milestones as $m) : ?>
            <div class="milestone-item">
              <p class="milestone-name"><?= htmlspecialchars($m['name']) ?></p>
              <div class="d-flex justify-content-between align-items-center">
                <p class="milestone-date"><?= $m['due_date_formatted'] ?? 'No due date' ?></p>
                <span class="badge-status <?= htmlspecialchars($m['status_class']) ?>"><?= htmlspecialchars($m['status_label']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="box mb-4" id="recentActivitiesBox">
        <p class="box-label">RECENT ACTIVITY</p>
        <?php if (empty($recentActivities)) : ?>
          <p class="text-muted small">No recent activity.</p>
        <?php else : ?>
          <?php foreach ($recentActivities as $activity) : ?>
              <div class="activity-item">
                <img src="<?= BASE_URL ?>/assets/icons/<?= $activity['icon'] ?? 'question-circle' ?>.svg" alt="" class="activity-avatar">
                <span><?= htmlspecialchars($activity['description']) ?></span>
              </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="box" id="dashFeedbackBox">
        <p class="box-label">LATEST ADVISER FEEDBACK</p>
        <?php if ($latestFeedback) : ?>
          <div class="feedback-card">
            <div class="feedback-avatar">
              <?= htmlspecialchars($latestFeedback['initials']) ?>
            </div>
            <div class="feedback-content">
              <p class="feedback-meta"><?= htmlspecialchars($latestFeedback['author']) ?></p>
              <p class="feedback-date"><?= htmlspecialchars($latestFeedback['date_formatted']) ?></p>
              <p class="feedback-text"><?= htmlspecialchars('"' . $latestFeedback['message'] . '"') ?></p>
            </div>
          </div>
        <?php endif; ?>
      </div>

    </main>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/student/theme.js"></script>
  

  <script>
    /* sidebar toggle */
    const sidebar = document.getElementById('sidebar');
    const menuBtn = document.getElementById('menuBtn');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    }

    menuBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    /* close sidebar on link click for mobile */
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992) toggleSidebar();
      });
    });
  </script>
</body>
</html>
