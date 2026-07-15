<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Adviser Dashboard</title>
    
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
            <li><a href="thesis-adviser-overview.php" class="active"><img src="../assets/icons/grid-1x2-fill.svg" alt="" class="bi"><span>Overview</span></a></li>
            <li><a href="thesis-adviser-groups.php"><img src="../assets/icons/people-fill.svg" alt="" class="bi"><span>Thesis Groups</span></a></li>
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

        <header class="dashboard-header">
          <div class="greeting">
            <p class="date">Monday, May 24, 2026</p>
            <h2>Good Morning, Dr. Richards</h2>
          </div>
          <div class="header-actions">
            <a class="btn-outline" href="thesis-adviser-submissions.php">Review submissions</a>
            <a class="btn-outline" href="thesis-adviser-consultations.php">Schedule consultation</a>
          </div>
        </header>

        <section class="stat-cards-grid">
          <div class="stat-card">
            <h3>Assigned Groups</h3>
            <p class="stat-number">4</p>
            <p class="stat-label warning">2 Need your attention</p>
          </div>
          
          <div class="stat-card">
            <h3>Pending Reviews</h3>
            <p class="stat-number">2</p>
            <p class="stat-label">Awaiting your action</p>
          </div>
          
          <div class="stat-card">
            <h3>Upcoming consultations</h3>
            <p class="stat-number">3</p>
            <p class="stat-label">This week</p>
          </div>
          
          <div class="stat-card">
            <h3>Unread alerts</h3>
            <p class="stat-number">3</p>
            <p class="stat-label">Notifications</p>
          </div>
        </section>

        <section class="dashboard-panels">
          
          <div class="panel progress-panel">
            <h3>Group progress</h3>
            
            <div class="progress-item">
              <span class="group-name">Group A</span>
              <div class="progress-bar-container">
                <div class="progress-fill" style="width: 72%;"></div>
              </div>
              <span class="progress-text">72%</span>
            </div>
            
            <div class="progress-item">
              <span class="group-name">Group B</span>
              <div class="progress-bar-container">
                <div class="progress-fill" style="width: 45%;"></div>
              </div>
              <span class="progress-text">45%</span>
            </div>

            <div class="progress-item">
              <span class="group-name">Group C</span>
              <div class="progress-bar-container">
                <div class="progress-fill" style="width: 88%;"></div>
              </div>
              <span class="progress-text">88%</span>
            </div>

            <div class="progress-item">
              <span class="group-name">Group D</span>
              <div class="progress-bar-container">
                <div class="progress-fill" style="width: 30%;"></div>
              </div>
              <span class="progress-text">30%</span>
            </div>
          </div>

          <div class="panel activity-panel">
            <h3>Recent Activity</h3>
            <ul class="activity-list">
              <li>New submission from Group B</li>
              <li>Overdue Methodology</li>
              <li>Consultation reminder</li>
              <li>Submission approved</li>
            </ul>
          </div>

        </section>

      </main>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/adviser-interactions.js?v=20260715c"></script>
    </body>
</html>
