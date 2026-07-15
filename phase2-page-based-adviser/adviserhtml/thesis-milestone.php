<?php
require_once __DIR__ . '/../adviser-data.php';

$groups = adviserGetMilestoneGroups($pdo);

function adviserMilestoneStatusClass(string $status): string
{
    $statusKey = strtolower($status);

    if ($statusKey === 'completed') {
        return 'completed';
    }

    if ($statusKey === 'pending') {
        return 'pending';
    }

    return 'in-progress';
}

function adviserFormatMilestoneDate(?string $date): string
{
    if (empty($date)) {
        return 'Not yet submitted';
    }

    return date('F j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milestone Tracker - Adviser</title>
    
    <script>
      (function() {
        var t = localStorage.getItem('theme');
        var d = t || (window.matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light');
        if (d === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
      })();
    </script>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="../advisercss/adviser-overview.css?v=20260715c">

</head>
<body>
    <script src="../../js/maintenance-check.js"></script>

    <div class="app-layout">
      
      <header class="mobile-header">
        <button class="hamburger-btn" aria-label="Open Menu">&#9776;</button>
        <div class="search-bar-mobile">
          <input type="text" placeholder="Search Groups, Submissions...">
          </div>
        <img src="../assets/utracksis_logo.png" alt="U-Tracksis Logo" class="mobile-logo">
      </header>
      <aside class="sidebar">
        <div class="logo-container">
          <img src="../assets/utracksis_logo.png" alt="U-Tracksis Logo" class="sidebar-logo">
          <div>
            <h6 class="sidebar-title">U-Tracksis</h6>
            <p class="sidebar-subtitle">ADVISER</p>
          </div>
        </div>
        <nav class="workspace-nav">
          <ul>
            <li><a href="thesis-adviser-overview.php"><img src="../assets/icons/grid-1x2-fill.svg" alt="" class="bi"><span>Overview</span></a></li>
            <li><a href="thesis-adviser-groups.php"><img src="../assets/icons/people-fill.svg" alt="" class="bi"><span>Thesis Groups</span></a></li>
            <li><a href="thesis-milestone.php" class="active"><img src="../assets/icons/flag-fill.svg" alt="" class="bi"><span>Milestones</span></a></li>
            <li><a href="thesis-adviser-submissions.php"><img src="../assets/icons/cloud-upload-fill.svg" alt="" class="bi"><span>Submissions</span></a></li>
            <li><a href="thesis-adviser-consultations.php"><img src="../assets/icons/chat-dots-fill.svg" alt="" class="bi"><span>Consultations</span></a></li>
            <li><a href="thesis-group-notifications.php"><img src="../assets/icons/bell-fill.svg" alt="" class="bi"><span>Notifications</span></a></li>
          </ul>
        </nav>
        <div class="sidebar-footer">
          <span>Theme</span>
          <button class="theme-toggle" id="themeToggle" type="button" title="Toggle theme">
            <img src="../assets/icons/moon-stars-fill.svg" alt="" class="bi" id="themeIcon">
          </button>
        </div>
      </aside>

      <main class="main-content">
        
        <div class="top-bar desktop-only">
          <input type="text" placeholder="Search Groups, Submissions, Students">
          <div class="top-bar-actions">
            <button class="btn-outline logout-btn" type="button" data-action="logout">Logout</button>
            <img src="../assets/utracksis_logo.png" alt="Profile" class="top-right-logo">
          </div>
        </div>

        <div class="page-header">
            <h2>Milestone Tracker</h2>
            <p class="subtitle">Cross-group milestone progress from the database</p>
        </div>

        <section class="milestone-tracker-section">
          <?php if (empty($groups)): ?>
            <p class="subtitle">No milestone records found.</p>
          <?php endif; ?>

          <?php foreach ($groups as $group): ?>
            <div class="group-milestone-panel">
              <div class="group-header">
                <h3><?= htmlspecialchars($group['group_name']) ?></h3>
                <p class="project-title"><?= htmlspecialchars($group['thesis_title']) ?></p>
              </div>
              
              <div class="milestone-scroll-container">
                <?php if (empty($group['milestones'])): ?>
                  <p class="subtitle">No milestones assigned yet.</p>
                <?php endif; ?>

                <?php foreach ($group['milestones'] as $milestone): ?>
                  <?php $statusClass = adviserMilestoneStatusClass($milestone['status']); ?>
                  <div
                    class="milestone-card"
                    data-action="view-milestone"
                    data-group="<?= htmlspecialchars($group['group_name']) ?>"
                    data-milestone="<?= htmlspecialchars($milestone['milestone_name']) ?>"
                    data-status="<?= htmlspecialchars($milestone['status']) ?>"
                    data-due-date="<?= htmlspecialchars(adviserFormatMilestoneDate($milestone['due_date'])) ?>"
                    data-submitted="<?= htmlspecialchars(adviserFormatMilestoneDate($milestone['submitted_at'])) ?>"
                    data-comments="<?= htmlspecialchars($milestone['comments'] ?? 'No comments yet.') ?>"
                  >
                    <h4><?= htmlspecialchars($milestone['milestone_name']) ?></h4>
                    <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($milestone['status']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </section>

      </main>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/adviser-interactions.js?v=20260715c"></script>
</body>
</html>
