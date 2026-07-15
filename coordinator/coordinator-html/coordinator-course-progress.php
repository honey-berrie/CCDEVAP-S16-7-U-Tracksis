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

// Fetch dynamic group progress data for the bullet chart
$bulletData = [];

// Summary card values
$averageProgress = 0;
$onTrackCount = 0;
$fallingBehindCount = 0;

if (!empty($sessUser['id'])) {
    try {
        // Query teams connected to the coordinator's courses and compute:
        //  - measure: average actual progress across the group's milestones
        //  - target:  average *expected* progress across the group's milestones,
        //             where each milestone contributes 100 if its due_date has
        //             already passed (or is today) and 0 if it's still upcoming
        $sql = "
            SELECT 
                t.id AS team_id,
                t.group_name AS name,
                COALESCE(AVG(m.progress), 0) AS measure,
                COALESCE(AVG(
                    CASE 
                        WHEN m.due_date IS NOT NULL AND m.due_date <= CURDATE() THEN 100
                        ELSE 0
                    END
                ), 0) AS target
            FROM teams t
            JOIN sections s ON t.section_id = s.id
            JOIN courses c ON s.course_id = c.id
            LEFT JOIN milestones m ON t.id = m.group_id
            WHERE c.coordinator_id = :coord_id
            GROUP BY t.id, t.group_name
            ORDER BY t.group_name ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['coord_id' => $sessUser['id']]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepared statement to persist the computed status back to each team
        $updateStatusStmt = $pdo->prepare(
            "UPDATE teams SET progress_status = :status WHERE id = :team_id"
        );

        $totalMeasure = 0;

        // Map data to the format expected by ECharts, tally On Track / Falling Behind,
        // and sync each team's progress_status column to reflect the computed status
        foreach ($groups as $group) {
            $measure = round((float)$group['measure']);
            $targetValue = round((float)$group['target']);
            $totalMeasure += $measure;

            if ($measure >= $targetValue) {
                $status = 'on track';
                $onTrackCount++;
            } else {
                $status = 'falling behind';
                $fallingBehindCount++;
            }

            // Auto-update the group's progress_status in the database
            $updateStatusStmt->execute([
                'status' => $status,
                'team_id' => $group['team_id'],
            ]);

            $bulletData[] = [
                'name' => $group['name'],
                'ranges' => [40, 70, 100],
                'measure' => $measure,
                'target' => $targetValue,
            ];
        }

        $groupCount = count($groups);
        if ($groupCount > 0) {
            $averageProgress = round($totalMeasure / $groupCount);
        }
    } catch (PDOException $e) {
        // Fallback to empty/zero values if query fails
        $bulletData = [];
        $averageProgress = 0;
        $onTrackCount = 0;
        $fallingBehindCount = 0;
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coordinator Course Progress | U-Tracksis</title>

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
          <a class="nav-link" href="coordinator-course-announcements.php">
            <img
              src="../../assets/icons/megaphone-fill.svg"
              alt=""
              class="bi"
            />
            Announcements
          </a>
          <a class="nav-link active" href="coordinator-course-progress.php">
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
        <script src="../../js/logout.js"></script>

        <div class="page-header">
          <h1 class="page-title">Course Progress</h1>
          <p class="page-subtitle">Good day, <?php echo htmlspecialchars($first_name); ?>.</p>
        </div>

        <!-- 3x1 display for Average Progress, On Track, and Falling Behind -->
        <!-- Average Progress -->
        <div class="row g-4 mb-4">
          <div class="col-lg-4 col-md-4">
            <div class="box mb-4 h-100">
              <div class="box-label">Average Progress</div>
              <div class="box-value"><?php echo htmlspecialchars($averageProgress); ?>%</div>
              <div class="progress mt-3 rounded-pill" style="height: 10px">
                <div
                  class="progress-bar bg-warning"
                  role="progressbar"
                  style="width: <?php echo htmlspecialchars($averageProgress); ?>%"
                  aria-valuenow="<?php echo htmlspecialchars($averageProgress); ?>"
                  aria-valuemin="0"
                  aria-valuemax="100"
                ></div>
              </div>
            </div>
          </div>

          <!-- On Track -->
          <div class="col-lg-4 col-md-4">
            <div class="box mb-4 h-100">
              <div class="box-label">On Track</div>
              <div class="box-value"><?php echo htmlspecialchars($onTrackCount); ?></div>
            </div>
          </div>

          <!-- Falling Behind -->
          <div class="col-lg-4 col-md-4">
            <div class="box mb-4 h-100">
              <div class="box-label">Falling Behind</div>
              <div class="box-value"><?php echo htmlspecialchars($fallingBehindCount); ?></div>
            </div>
          </div>
        </div>

        <div class="box mb-4 h-100">
          <p class="box-label">Group Submission Progress</p>
          <div class="box">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">
                  Groups
                </h5>
              </div>

              <!-- BULLET CHART: chart container. Give it a unique id so bulletchart.js can target it -->
              <!-- BULLET CHART: chart container. Give it a unique id so bulletchart.js can target it -->
              <div id="bulletchart" style="width: 100%; height: 300px"></div>
              
              <!-- Load ECharts -->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js" integrity="sha384-o5uz97et3bErHvpKfD4Jz4n0JfhJDWABFuF4NP+iEEDxE1VwMWJ19QGR0lqFZnr6" crossorigin="anonymous"></script>
              
              <!-- Inline dynamic initialization -->
              <script>
                const bulletChart = echarts.init(document.getElementById("bulletchart"));

                // Inject PHP data directly into JS
                const bulletData = <?php echo json_encode($bulletData); ?>;

                if (bulletData.length > 0) {
                    const categories = bulletData.map((d) => d.name);
                    const axisMax = Math.max(...bulletData.map((d) => d.ranges[d.ranges.length - 1]));

                    const bulletOption = {
                      title: {
                        text: "Submission Progress",
                        left: "center",
                        textStyle: { fontSize: 16, color: "#FFFF" },
                      },
                      tooltip: {
                        trigger: "axis",
                        axisPointer: { type: "shadow" },
                        formatter: (params) => {
                          const d = bulletData[params[0].dataIndex];
                          return `${d.name}<br/>Progress: ${d.measure}%<br/>Target: ${d.target}%`;
                        },
                      },
                      grid: { left: 90, right: 30, top: 60, bottom: 30 },
                      xAxis: {
                        max: axisMax,
                        splitLine: { show: false },
                      },
                      yAxis: {
                        type: "category",
                        data: categories,
                        axisTick: { show: false },
                      },
                      series: [
                        {
                          name: "High range",
                          type: "bar",
                          barWidth: 22,
                          data: bulletData.map((d) => d.ranges[2]),
                          itemStyle: { color: "#eee" },
                          barGap: "-100%",
                          z: 1,
                          silent: true,
                        },
                        {
                          name: "Medium range",
                          type: "bar",
                          barWidth: 22,
                          data: bulletData.map((d) => d.ranges[1]),
                          itemStyle: { color: "#ddd" },
                          barGap: "-100%",
                          z: 2,
                          silent: true,
                        },
                        {
                          name: "Low range",
                          type: "bar",
                          barWidth: 22,
                          data: bulletData.map((d) => d.ranges[0]),
                          itemStyle: { color: "#ccc" },
                          barGap: "-100%",
                          z: 3,
                          silent: true,
                        },
                        {
                          name: "Progress",
                          type: "bar",
                          barWidth: 8,
                          data: bulletData.map((d) => d.measure),
                          itemStyle: { color: "#5B8FF9" },
                          barGap: "-100%",
                          z: 4,
                        },
                        {
                          name: "Target",
                          type: "scatter",
                          symbol: "rect",
                          symbolSize: [3, 24],
                          data: bulletData.map((d, i) => [d.target, i]),
                          itemStyle: { color: "#333" },
                          z: 5,
                        },
                      ],
                    };

                    bulletChart.setOption(bulletOption);
                    window.addEventListener("resize", () => bulletChart.resize());
                } else {
                    document.getElementById("bulletchart").innerHTML = "<p class='text-center text-muted mt-5'>No group data available.</p>";
                }
              </script>
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