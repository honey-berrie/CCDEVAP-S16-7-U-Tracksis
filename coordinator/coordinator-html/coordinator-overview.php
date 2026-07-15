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

// Fetch handled courses for this coordinator
$handledCount = 0;
$handledCourses = [];
$userId = (int)($_SESSION['user']['id'] ?? 0);
if ($userId) {
  try {
    // courses has no progress column of its own — derive each course's
    // progress from the average milestone progress across its teams.
    $sql = "
      SELECT 
        c.id,
        c.course_code,
        c.course_name,
        COALESCE(AVG(m.progress), 0) AS progress
      FROM courses c
      LEFT JOIN sections s ON s.course_id = c.id
      LEFT JOIN teams t ON t.section_id = s.id
      LEFT JOIN milestones m ON m.group_id = t.id
      WHERE c.coordinator_id = ?
      GROUP BY c.id, c.course_code, c.course_name
      ORDER BY c.course_code
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $handledCourses = $stmt->fetchAll();
    $handledCount = count($handledCourses);
  } catch (PDOException $e) {
    // leave empty on error
    $handledCourses = [];
    $handledCount = 0;
  }
}
// Fetch group counts (total groups, pending, and off-track groups for coordinator's courses)
$totalGroups = 0;
$pendingGroups = 0;
$offTrackGroups = 0;
$attentionGroups = []; // New array to store groups requiring attention

if ($userId) {
  try {
    // 1. Total Groups
    $sql = "SELECT COUNT(*) FROM teams t
            LEFT JOIN sections s ON t.section_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            WHERE c.coordinator_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $totalGroups = (int)$stmt->fetchColumn();

    // 2. Pending Groups
    $sql = "SELECT COUNT(*) FROM teams t
            LEFT JOIN sections s ON t.section_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            WHERE c.coordinator_id = ? AND (t.approval_status = 'pending' OR t.approval_status IS NULL)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $pendingGroups = (int)$stmt->fetchColumn();

    // 3. Off-Track Groups (progress_status = 'falling behind')
    $sql = "SELECT COUNT(*) FROM teams t
            LEFT JOIN sections s ON t.section_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            WHERE c.coordinator_id = ? AND t.progress_status = 'falling behind'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $offTrackGroups = (int)$stmt->fetchColumn();

    // 4. Fetch the specific groups for the Attention Scrollable Container
    $sql = "SELECT t.id AS team_id, t.group_name, c.course_code, t.approval_status, t.progress_status,
                   u.firstname AS adviser_firstname, u.lastname AS adviser_lastname
            FROM teams t
            LEFT JOIN sections s ON t.section_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN users u ON t.adviser_id = u.id
            WHERE c.coordinator_id = ? 
              AND ((t.approval_status = 'pending' OR t.approval_status IS NULL) 
                   OR t.progress_status = 'falling behind')
            ORDER BY t.group_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $attentionGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
    $totalGroups = 0;
    $pendingGroups = 0;
    $offTrackGroups = 0;
    $attentionGroups = [];
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coordinator Overview | U-Tracksis</title>

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
          <a class="nav-link active" href="coordinator-overview.php">
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
          <h1 class="page-title">Overview</h1>
          <p class="page-subtitle">Good day, <?php echo htmlspecialchars($first_name); ?>.</p>

        <!-- 4 cards layout, In a row when Big, 2x2 grid when small -->
        <div class="row g-4 mb-4">
          <!-- Handled Courses -->
          <div class="col-lg-3 col-md-6">
            <div class="box mb-4 h-100">
              <p class="box-label">Handled Courses</p>
              <h3 class="fw-bold mb-3"><?php echo (int)$handledCount; ?></h3>
              <?php if (!empty($handledCourses)): ?>
                <ul class="list-unstyled small mb-0">
                  <?php foreach ($handledCourses as $c): ?>
                    <li><?php echo htmlspecialchars($c['course_code'] . ' — ' . $c['course_name']); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="small text-muted mb-0">No courses assigned</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Active Groups -->
          <div class="col-lg-3 col-md-6">
            <div class="box mb-4 h-100">
              <p class="box-label">Active Groups</p>
              <h3 class="fw-bold mb-3"><?php echo (int)$totalGroups; ?></h3>
            </div>
          </div>

          <!-- Pending Formations -->
          <div class="col-lg-3 col-md-6">
            <div class="box mb-4 h-100">
              <p class="box-label">Pending Formations</p>
              <h3 class="fw-bold mb-3"><?php echo (int)$pendingGroups; ?></h3>
            </div>
          </div>

          <!-- Off-Track Groups -->
          <div class="col-lg-3 col-md-6">
            <div class="box mb-4 h-100">
              <p class="box-label">Off-Track Groups</p>
              <h3 class="fw-bold mb-3"><?php echo (int)$offTrackGroups; ?></h3>
            </div>
          </div>

          <!-- container for side-by-side cards -->
          <div class="row g-4 mb-4">
            <div class="col-lg-6 col-md-12">
              <div class="box mb-4 h-100">
                <div
                  class="d-flex justify-content-between align-items-center mb-2"
                >
                  <p class="box-label">Handled Courses</p>
                </div>
                

                <div
                  id="chart"
                  style="width: 100%; height: 300px; margin-top: 20px"
                ></div>

                <!-- Scripts -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js" integrity="sha384-o5uz97et3bErHvpKfD4Jz4n0JfhJDWABFuF4NP+iEEDxE1VwMWJ19QGR0lqFZnr6" crossorigin="anonymous"></script>
                <?php if (!empty($handledCourses)): ?>
                  <script>
                    window.COORDINATOR_COURSE_CHART = <?php
                      echo json_encode(array_map(function($c){
                        return [
                          'name'  => $c['course_code'],
                          'value' => isset($c['progress']) ? (int)round((float)$c['progress']) : 0,
                        ];
                      }, $handledCourses));
                    ?>;
                  </script>
                <?php endif; ?>
                <script src="../coordinator-back-end/pictogram.js"></script>
                <script src="../../js/logout.js"></script>
              </div>
            </div>

           <div class="col-lg-6 col-md-12">
              <div class="box mb-4 h-100 d-flex flex-column">
                <p class="box-label">Needs your attention!</p>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                  <h2 class="fw-bold mb-0" style="font-size: 1.5rem;">Group formations to review</h2>
                  <h3 class="fw-bold mb-0"><?php echo (int)($pendingGroups + $offTrackGroups); ?></h3>
                </div>
                
                <!-- Scrollable Attention Container -->
                <div class="attention-list flex-grow-1" style="height: 0; overflow-y: auto; padding-right: 8px;">
                  <?php if (!empty($attentionGroups)): ?>
                    <?php foreach ($attentionGroups as $g): ?>
                      <!-- Switched to .row class to match your border-bottom css perfectly -->
                      <div class="row py-2 border-bottom align-items-center">
                        <div class="col-8">
                          <!-- Removed 'text-white' to inherit light/dark mode colors -->
                          <p class="fw-bold mb-0" style="font-size: 0.95rem;">
                            <?php echo htmlspecialchars($g['group_name']); ?>
                          </p>
                          <p class="text-muted mb-0" style="font-size: 0.8rem;">
                            Course: <span class="fw-bold text-warning"><?php echo htmlspecialchars($g['course_code']); ?></span> | 
                            Adviser: <span class="fw-bold"><?php echo $g['adviser_firstname'] ? htmlspecialchars($g['adviser_firstname'] . ' ' . $g['adviser_lastname']) : 'Unassigned'; ?></span>
                          </p>
                        </div>
                        <div class="col-4 text-end">
                          <?php
                          if ($g['approval_status'] === 'pending' || is_null($g['approval_status'])) {
                              echo '<span class="badge text-bg-warning text-white small text-uppercase me-1">Pending</span>';
                          }
                          if ($g['progress_status'] === 'falling behind') {
                              echo '<span class="badge text-bg-danger text-white small text-uppercase">Off-Track</span>';
                          }
                          ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-muted small mt-3 mb-0">No groups currently require attention.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="box mb-4">
          <p class="box-label">Recent Announcements</p>
          <div class="d-flex justify-content-between align-items-center">
            <p class="fw-bold mb-0">
              Title Proposal Defense Week
              <span class="badge text-bg-primary ms-2 fw-normal">Pinned</span>
            </p>
            <p class="text small mb-2">Due on July 28, 2027</p>
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