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
    <title>Coordinator Adviser Assignments | U-Tracksis</title>

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
    <link rel="stylesheet" href="../coordinator-css/coordinator.css" />
  </head>
  <body>
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
          <a
            class="nav-link active"
            href="coordinator-adviser-assignments.php"
          >
            <img
              src="../../assets/icons/diagram-3-fill.svg"
              alt=""
              class="bi"
            />
            Adviser Assignments
          </a>
          <a class="nav-link" href="coordinator-course-announcements.php">
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
              <path
                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"
              />
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
        
        <div class="page-header">
          <h1 class="page-title">Adviser Assignments</h1>
          <p class="page-subtitle">Good day, <?php echo htmlspecialchars($first_name); ?>.</p>
        </div>

       <!-- Header & Groups Container (Aligned) -->
        <div class="row g-4 mb-4">
          <!-- Groups PHP Query -->
          <?php
          $coordinatorId = $sessUser['id'] ?? null;

          $stmt = $pdo->prepare("
              SELECT
                  t.id AS team_id,
                  t.group_name,
                  c.course_code,
                  t.adviser_id,
                  u.firstname AS adviser_firstname,
                  u.lastname AS adviser_lastname,
                  t.approval_status
              FROM teams t
              JOIN sections s ON t.section_id = s.id
              JOIN courses c ON s.course_id = c.id
              LEFT JOIN users u ON t.adviser_id = u.id
              WHERE c.coordinator_id = ?
              ORDER BY t.group_name
          ");
          $stmt->execute([$coordinatorId]);
          $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $teamCount = count($teams);
          ?>

          <div class="box mb-4">
            <!-- Header Row -->
            <div class="row py-3 border-bottom align-items-center text-center">
              <div class="col-3">
                <p class="text fw-bold small text-uppercase mb-0">Group</p>
              </div>
              <div class="col-3">
                <p class="text fw-bold small text-uppercase mb-0">Course</p>
              </div>
              <div class="col-3">
                <p class="text fw-bold small text-uppercase mb-0">Adviser</p>
              </div>
              <div class="col-3">
                <p class="text fw-bold small text-uppercase mb-0">Action</p>
              </div>
            </div>

            <!-- Data Rows -->
            <?php foreach ($teams as $i => $team): ?>
            <div class="row py-3 align-items-center text-center <?php echo $i < $teamCount - 1 ? 'border-bottom' : ''; ?>">
              <div class="col-3">
                <p class="text fw-bold small text-uppercase mb-0">
                  <?php echo htmlspecialchars($team['group_name']); ?>
                </p>
              </div>
              <div class="col-3">
                <p class="mb-0">
                  <span class="badge text-bg-warning text-white small text-uppercase">
                    <?php echo htmlspecialchars($team['course_code']); ?>
                  </span>
                </p>
              </div>
              <div class="col-3">
                <p class="mb-0">
                  <span class="badge text-bg-primary small text-uppercase">
                    <?php echo $team['adviser_id']
                      ? htmlspecialchars($team['adviser_firstname'] . ' ' . $team['adviser_lastname'])
                      : 'Unassigned'; ?>
                  </span>
                </p>
              </div>
              <div class="col-3">
                <p class="mb-0">
                  <button
                    type="button"
                    class="badge text-bg-danger text-white small text-uppercase mb-0 border-0"
                    onclick="assignAdviser(<?php echo (int)$team['team_id']; ?>, <?php echo $team['adviser_id'] ? (int)$team['adviser_id'] : 'null'; ?>)"
                  >
                    <?php echo $team['adviser_id'] ? 'Reassign' : 'Request'; ?>
                  </button>
                </p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="box mb-4">
            <p class="box-label">Adviser Load</p>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div id="polarchart" style="width: 100%; height: 800px"></div>
              <script
                src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"
                integrity="sha384-o5uz97et3bErHvpKfD4Jz4n0JfhJDWABFuF4NP+iEEDxE1VwMWJ19QGR0lqFZnr6"
                crossorigin="anonymous"
              ></script>
              <script src="../coordinator-back-end/polargrid.js"></script>
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


      async function assignAdviser(teamId, adviserId) {
  try {
    const res = await fetch("../../api/coordinator/adviser_assignment.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ team_id: teamId, adviser_id: adviserId }),
    });
    const data = await res.json();
    if (!data.success) {
      alert(data.error || "Failed to assign adviser");
      return;
    }
    location.reload(); // or update the DOM in place
  } catch (err) {
    console.error("Error assigning adviser", err);
  }
}
    </script>

    
  </body>
</html>
