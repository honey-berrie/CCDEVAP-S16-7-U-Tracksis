<?php
// 1. Include the PDO database connection
require_once '..\..\api\db.php'; 

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
// Fetch handled courses for this coordinator (for display under Group Registry)
$handledCount = 0;
$handledCourses = [];
$userId = (int)($_SESSION['user']['id'] ?? 0);
if ($userId) {
  try {
    $stmt = $pdo->prepare("SELECT id, course_code, course_name FROM courses WHERE coordinator_id = ? ORDER BY course_code");
    $stmt->execute([$userId]);
    $handledCourses = $stmt->fetchAll();
    $handledCount = count($handledCourses);
  } catch (PDOException $e) {
    $handledCourses = [];
    $handledCount = 0;
  }
}
// Fetch teams to display — only from courses handled by this coordinator
$teams = [];
try {
  if (!empty($handledCourses)) {
    // build a list of course ids for the IN clause
    $courseIds = array_map(function($c){ return (int)$c['id']; }, $handledCourses);
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $sql = "SELECT t.*, s.section_code, c.course_code, CONCAT(u.firstname, ' ', u.lastname) AS adviser_name
        FROM teams t
        LEFT JOIN sections s ON t.section_id = s.id
        LEFT JOIN courses c ON s.course_id = c.id
        LEFT JOIN users u ON t.adviser_id = u.id
        WHERE c.id IN ($placeholders)
        ORDER BY t.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($courseIds);
    $teams = $stmt->fetchAll();
  } else {
    // Coordinator has no assigned courses, so no teams to show
    $teams = [];
  }
} catch (PDOException $e) {
  $teams = [];
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coordinator Group Formations | U-Tracksis</title>

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
          <a class="nav-link active" href="coordinator-group-formations.php">
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
          <h1 class="page-title">Group Formations</h1>
          <p class="page-subtitle">Good day, <?php echo htmlspecialchars($first_name); ?>.</p>
        </div>

        <!-- Group Creation and Searching -->
        <div class="row g-4 mb-4">
          <!-- Groups -->
          <div class="box mb-4">
            <div class="d-flex justify-content-between mb-4">
              <div class="box-label">Group Registry</div>
              <button
                class="btn-new-submission"
                type="button"
                onclick="
                  window.location.href = 'coordinator-course-announcements.html'
                "
              >
                <img src="../../assets/icons/plus-lg.svg" alt="" class="bi" />
                <span>Create Group</span>
              </button>
            </div>
            <div class="row align-items-center">
              <div class="col-lg-8">
                <p class="text medium mb-0">
                  <?php if ($handledCount > 0): ?>
                    Courses handled (<?php echo (int)$handledCount; ?>):
                    <?php
                      $names = array_map(function($c){
                        return htmlspecialchars($c['course_code']);
                      }, $handledCourses);
                      echo implode(', ', $names);
                    ?>
                  <?php else: ?>
                    <span class="text-muted">No courses assigned</span>
                  <?php endif; ?>
                </p>
              </div>
              <div class="col-lg-4">
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
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06q.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                  </svg>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Search Group"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Group List and Group Filter -->
          <div class="box">
            <div class="row">
              <div class="col-lg-4 md-6 sm-12 mb-4">
                <div class="form-group">
                  <label for="GroupFilter">Group Filter</label>
                  <select class="form-control" id="GroupFilter">
                    <option value="all">All</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>

              <!-- One box per group -->
              <?php if (!empty($teams)): ?>
                <?php foreach ($teams as $team): ?>
                  <div class="box mb-3 team-card" data-approval="<?php echo htmlspecialchars($team['approval_status'] ?? 'pending'); ?>">
                    <div class="card-body">
                      <div class="d-flex bd-highlight mb-4">
                        <div class="me-auto p-2 bd-highlight">
                          <h5 class="card-title"><?php echo htmlspecialchars($team['group_name']); ?></h5>
                        </div>
                        <div class="p-2 bd-highlight"><h3><span class="badge text-bg-secondary"><?php echo htmlspecialchars($team['section_code'] ?? $team['section_id']); ?></span></h1></h3></div>
                        <div class="p-2 bd-highlight"><h3><span class="badge <?php echo ($team['approval_status'] === 'approved') ? 'text-bg-success' : 'text-bg-warning'; ?>"><?php echo htmlspecialchars(ucfirst($team['approval_status'] ?? 'pending')); ?></span></h1></h3></div>
                      </div>

                      <h6 class="card-subtitle mb-2 text"><?php echo htmlspecialchars($team['thesis_title'] ?? 'Thesis Title'); ?></h6>
                      <p class="box-label">Submitted on: <?php echo !empty($team['submission_date']) ? htmlspecialchars($team['submission_date']) : '—'; ?></p>
                      <div class="d-flex bd-highlight mb-4">
                        <?php
                          // fetch members for this team
                          $mStmt = $pdo->prepare("SELECT u.firstname, u.lastname FROM group_members gm JOIN users u ON gm.user_id = u.id WHERE gm.group_id = ? LIMIT 10");
                          $mStmt->execute([(int)$team['id']]);
                          $members = $mStmt->fetchAll();
                          if (!empty($members)) {
                            foreach ($members as $m) {
                              echo '<div class="p-2 bd-highlight"><span class="badge text-bg-primary">' . htmlspecialchars($m['firstname'] . ' ' . $m['lastname']) . '</span></div>';
                            }
                          } else {
                            echo '<div class="p-2 bd-highlight"><span class="text-muted">No members</span></div>';
                          }
                        ?>
                        <div class="ms-auto p-2 bd-highlight">
                          <a href="#" class="badge text-bg-success text-decoration-none" onclick="alert('Placeholder: Approve Group')">Approve</a>
                          <a href="#" class="badge text-bg-danger text-decoration-none" onclick="alert('Placeholder: Reject Group')">Reject</a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted">No teams yet</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/student/theme.js"></script>
    <script src="../coordinator-back-end/coordinator-logout.js"></script>

    <script>
      // Group filter logic: show/hide team cards by approval status
      (function(){
        const filter = document.getElementById('GroupFilter');
        if (!filter) return;
        filter.addEventListener('change', () => {
          const val = filter.value;
          const cards = document.querySelectorAll('.team-card');
          cards.forEach(card => {
            if (val === 'all') {
              card.style.display = '';
            } else {
              const ap = (card.dataset.approval || '').toLowerCase();
              card.style.display = (ap === val) ? '' : 'none';
            }
          });
        });
      })();

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
