<?php
require_once __DIR__ . '/../adviser-data.php';

$groups = adviserGetGroupSummaries($pdo);

function adviserGroupStatusLabel(array $group): string
{
    if (strtolower($group['status']) === 'revision') {
        return 'Behind';
    }

    if ((int) $group['progress_percent'] < 50) {
        return 'At risk';
    }

    return 'On Track';
}

function adviserGroupStatusClass(string $statusLabel): string
{
    $statusKey = strtolower($statusLabel);

    if ($statusKey === 'at risk') {
        return 'at-risk';
    }

    if ($statusKey === 'behind') {
        return 'behind';
    }

    return 'on-track';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Groups - Adviser</title>
    
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
            <li><a href="thesis-adviser-groups.php" class="active"><img src="../assets/icons/people-fill.svg" alt="" class="bi"><span>Thesis Groups</span></a></li>
            <li><a href="thesis-milestone.php"><img src="../assets/icons/flag-fill.svg" alt="" class="bi"><span>Milestones</span></a></li>
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
          <h2>Thesis Groups</h2>
          <p class="subtitle">Assigned groups and milestone progress from the database</p>
        </div>

        <section class="group-cards-grid">
          <?php if (empty($groups)): ?>
            <p class="subtitle">No thesis groups found.</p>
          <?php endif; ?>

          <?php foreach ($groups as $group): ?>
            <?php
              $statusLabel = adviserGroupStatusLabel($group);
              $statusClass = adviserGroupStatusClass($statusLabel);
              $progress = (int) $group['progress_percent'];
            ?>
            <div class="group-card">
              <div class="card-header">
                <h3><?= htmlspecialchars($group['group_name']) ?></h3>
                <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusLabel) ?></span>
              </div>
              <p class="project-title"><?= htmlspecialchars($group['thesis_title']) ?></p>
              <p class="program">BS-Information Technology</p>
              
              <div class="card-progress">
                <div class="progress-bar-container">
                  <div class="progress-fill <?= $statusClass === 'behind' ? 'behind-fill' : '' ?>" style="width: <?= htmlspecialchars((string) $progress) ?>%;"></div>
                </div>
                <span class="progress-text"><?= htmlspecialchars((string) $progress) ?>%</span>
              </div>

              <div class="card-footer">
                <span class="messages"><?= htmlspecialchars((string) $group['completed_milestones']) ?> of <?= htmlspecialchars((string) $group['total_milestones']) ?> milestones done</span>
                <span class="next-milestone">Next milestone: <?= htmlspecialchars($group['next_milestone']) ?></span>
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
