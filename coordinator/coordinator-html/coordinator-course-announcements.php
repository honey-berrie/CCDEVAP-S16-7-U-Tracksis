<?php
// 1. Include the PDO database connection
require_once '../../api/db.php';

// 2. Fetch coordinator name safely using PDO
try {
    // Use logged-in user from session when available
  // `api/auth/login.php` sets `$_SESSION['user']` on successful login.
  $first_name = "Coordinator";

  if (!empty($_SESSION['user'])) {
    $sessUser = $_SESSION['user'];
    // ensure only coordinators access this page
    if (!empty($sessUser['role']) && $sessUser['role'] !== 'coordinator') {
      header('Location: ../../pages/auth/auth.html');
      exit;
    }

    // Prefer session firstname to avoid an extra DB query
    $first_name = $sessUser['firstname'] ?? ($sessUser['email'] ?? 'Coordinator');
  } else {
    // Not logged in — redirect to login
    header('Location: ../../pages/auth/auth.html');
    exit;
  }
} catch (PDOException $e) {
    // Fallback if the database table doesn't exist yet
    $first_name = "Coordinator"; 
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coordinator Course Announcements | U-Tracksis</title>

    <!-- Apply theme immediately -->
    <script>
      (function () {
        var t = localStorage.getItem("theme");
        var d =
          t ||
          (window.matchMedia("(prefers-color-scheme:dark)").matches
            ? "dark"
            : "light");
        if (d === "dark")
          document.documentElement.setAttribute("data-theme", "dark");
      })();
    </script>

    <!-- bootstrap css -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css" />

    <!-- local bootstrap icons -->
    <link rel="stylesheet" href="../../css/bootstrap-icons.css" />

    <!-- custom css -->
    <link
      rel="stylesheet"
      href="../../css/coordinator/coordinator-dashboard.css"
    />
  </head>
  <body>
  <script src="../../js/maintenance-check.js"></script>
    <div class="dashboard-wrapper">
      <!-- sidebar -->
      <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
          <img
            src="../../assets/images/logo.png"
            alt="U-Tracksis"
            class="sidebar-logo"
          />
          <div>
            <h6 class="sidebar-title">U-Tracksis</h6>
            <p class="sidebar-subtitle">COORDINATOR</p>
          </div>
        </div>

        <nav class="sidebar-nav">
          <a class="nav-link" href="coordinator-overview.php">
            <img
              src="../../assets/icons/binoculars-fill.svg"
              alt=""
              class="bi"
            />
            Overview
          </a>
          <a class="nav-link" href="coordinator-group-formations.php">
            <img src="../../assets/icons/people-fill.svg" alt="" class="bi" />
            Group Formations
          </a>
          <a class="nav-link" href="coordinator-adviser-assignments.php">
            <img
              src="../../assets/icons/diagram-3-fill.svg"
              alt=""
              class="bi"
            />
            Adviser Assignments
          </a>
          <a
            class="nav-link active"
            href="coordinator-course-announcements.php"
          >
            <img
              src="../../assets/icons/megaphone-fill.svg"
              alt=""
              class="bi"
            />
            Announcements
          </a>
          <a class="nav-link" href="coordinator-course-progress.php">
            <img src="../../assets/icons/flag-fill.svg" alt="" class="bi" />
            Course Progress
          </a>
        </nav>

        <div class="sidebar-footer">
          <span>Theme</span>
          <button
            class="theme-toggle"
            id="themeToggle"
            type="button"
            title="Toggle theme"
          >
            <img
              src="../../assets/icons/moon-stars-fill.svg"
              alt=""
              class="bi"
              id="themeIcon"
            />
          </button>
        </div>
      </aside>

      <!-- mobile overlay -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- main content -->
      <main class="main-content">
        <!-- top bar with search and actions -->
        <div class="top-bar">
          <button class="mobile-menu-btn" id="menuBtn" type="button">
            <img src="../../assets/icons/list.svg" alt="" class="bi" />
          </button>

          <div class="topbar-search">
            <svg
              class="bi search-icon"
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              viewBox="0 0 16 16"
              aria-hidden="true"
            >
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
            </svg>
            <input type="text" class="form-control" placeholder="Search..." />
          </div>

          <div class="topbar-actions">
            <button
              class="btn-new-submission"
              type="button"
              onclick="
                window.location.href = 'coordinator-course-announcements.html'
              "
            >
              <img src="../../assets/icons/plus-lg.svg" alt="" class="bi" />
              <span>New Announcement</span>
            </button>

            <button
              class="btn-logout"
              type="button"
              onclick="logout()"
            >
              <img
                src="../../assets/icons/box-arrow-left.svg"
                alt=""
                class="bi"
              />
              <span>Logout</span>
            </button>
          </div>
        </div>
        <script src="../../js/logout.js"></script>

        <div class="page-header">
          <h1 class="page-title">Adviser Assignments</h1>
          <p class="page-subtitle">Good day, <?php echo htmlspecialchars($first_name); ?>.</p>
        </div>

        <div class="row g-4 mb-4">

        <!-- Course Announcement Form -->
        <div class="col-lg-6 col-md-12">
          <div class="box mb-4 h-100">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="box-label">Course Announcements</p>
              </div>
              <form>
                <!-- Course Selection -->
                <div class="form-group">
                  <label for="CourseSelect">Course</label>
                  <select class="form-control" id="CourseSelect">
                    <option>Thesis Writing 1</option>
                    <option>Thesis Writing 2</option>
                    <option>Thesis Writing 3</option>
                    <option>Thesis Writing 4</option>
                    <option>Thesis Writing 5</option>
                  </select>
                </div>
                <div class="form-group p-1">
                  <label for="exampleInputPassword1">Title</label>
                  <input type="text" class="form-control" id="AnnouncementTitle" placeholder="Enter your Announcement Title">
                </div>
                <div class="form-group p-1">
                  <label for="exampleFormControlTextarea1">Message</label>
                  <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" placeholder="Enter your announcement message"></textarea>
                </div> 

                <div class="d-flex justify-content-between align-items-center">
                  <button type="submit" class="btn btn-primary my-1" onclick="alert('Placeholder: Publish announcement')">Publish</button>
                  <div class="form-check mb-2 mr-sm-2">
                  <input class="form-check-input" type="checkbox" id="inlineFormCheck">
                  <label class="form-check-label" for="inlineFormCheck">
                    Pin announcement to the top
                  </label>
                  </div>
                </div>
              </form>
           
          </div>
        </div>

        <!-- Published Announcements -->
        <div class="col-lg-6 col-md-12">
          <div class="box mb-4 h-100">
              <p class="box-label">Published Announcements</p>
              <div class="box">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Title Proposal Defense Week</h5>
                    <h3><span class="badge text-bg-secondary">S11</span></h1></h3>
                  </div>
                  <p class="box-label">Sept. 10, 2027</p>
                  <p class="card-text">Good afternoon, class! The title Proposal Defense will be held on September 18, 2027 at...</p>
                  <div class="d-flex justify-content-end align-items-center gap-2">
                    <a href="#" class="badge text-bg-primary text-decoration-none" onclick="alert('Placeholder: Edit announcement')">Edit</a>
                    <a href="#" class="badge text-bg-danger text-decoration-none" onclick="alert('Placeholder: Delete announcement')">Delete</a>
                  </div>
                </div>
              </div>
          </div>
        </div>

      </div>
        
      </main>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/student/theme.js"></script>
    <script src="../coordinator-back-end/coordinator-logout.js"></script>

    <script>
      /* sidebar toggle */
      const sidebar = document.getElementById("sidebar");
      const menuBtn = document.getElementById("menuBtn");
      const overlay = document.getElementById("sidebarOverlay");

      function toggleSidebar() {
        sidebar.classList.toggle("show");
        overlay.classList.toggle("show");
      }

      menuBtn.addEventListener("click", toggleSidebar);
      overlay.addEventListener("click", toggleSidebar);

      /* close sidebar on link click for mobile */
      document.querySelectorAll(".sidebar-nav .nav-link").forEach((link) => {
        link.addEventListener("click", () => {
          if (window.innerWidth < 992) toggleSidebar();
        });
      });
    </script>
  </body>
</html>
