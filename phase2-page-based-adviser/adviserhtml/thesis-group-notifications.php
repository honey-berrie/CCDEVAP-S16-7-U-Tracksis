<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Adviser</title>
    
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
        <img src="../assets/utracksis_logo.png" alt="U-Tracksis Logo" class="top-right-logo">
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
            <li><a href="thesis-milestone.php"><img src="../assets/icons/flag-fill.svg" alt="" class="bi"><span>Milestones</span></a></li>
            <li><a href="thesis-adviser-submissions.php"><img src="../assets/icons/cloud-upload-fill.svg" alt="" class="bi"><span>Submissions</span></a></li>
            <li><a href="thesis-adviser-consultations.php"><img src="../assets/icons/chat-dots-fill.svg" alt="" class="bi"><span>Consultations</span></a></li>
            <li><a href="thesis-group-notifications.php" class="active"><img src="../assets/icons/bell-fill.svg" alt="" class="bi"><span>Notifications</span></a></li>
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
            <h2>Notifications</h2>
            <p class="subtitle">3 Unread, 3 pending</p>
        </div>

        <div class="notifications-controls">
            <button class="filter-pill active">All (6)</button>
            <button class="btn-text">Mark all as read</button>
        </div>

        <section class="notifications-list">
            
            <div class="notification-card unread">
                <div class="avatar-placeholder"></div>
                <div class="notification-content">
                    <div class="notification-title">
                        <strong>Chapter 4</strong> <span class="sub-text">Results & Discussion</span>
                    </div>
                    <div class="notification-group">Group 3</div>
                </div>
                <div class="notification-time">2 hours ago</div>
            </div>

            <div class="notification-card unread">
                <div class="avatar-placeholder"></div>
                <div class="notification-content">
                    <div class="notification-title">
                        <strong>Methodology Revision</strong>
                    </div>
                    <div class="notification-group">Group 1</div>
                </div>
                <div class="notification-time">2 hours ago</div>
            </div>

            <div class="notification-card">
                <div class="avatar-placeholder"></div>
                <div class="notification-content">
                    <div class="notification-title">
                        <strong>User testing & Reporting</strong>
                    </div>
                    <div class="notification-group">Group 2</div>
                </div>
                <div class="notification-time">5 hours ago</div>
            </div>

        </section>

      </main>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/adviser-interactions.js?v=20260715c"></script>
</body>
</html>
