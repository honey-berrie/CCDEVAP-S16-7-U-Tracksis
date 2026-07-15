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

// Fetch sections for the handled courses to populate the Create Group form
$sections = [];
$advisers = [];
if (!empty($handledCourses)) {
  try {
    $courseIds = array_map(function($c){ return (int)$c['id']; }, $handledCourses);
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $sSql = "SELECT id, course_id, section_code FROM sections WHERE course_id IN ($placeholders) ORDER BY course_id, section_code";
    $sStmt = $pdo->prepare($sSql);
    $sStmt->execute($courseIds);
    $sections = $sStmt->fetchAll();
  } catch (PDOException $e) {
    $sections = [];
  }
}

// Fetch advisers to allow assigning an adviser when creating a group (include coordinators)
try {
  $aStmt = $pdo->query("SELECT id, firstname, lastname, role FROM users WHERE role IN ('adviser','faculty','coordinator') ORDER BY firstname, lastname");
  $advisers = $aStmt->fetchAll();
} catch (PDOException $e) {
  $advisers = [];
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

      // Approve/Reject actions (event delegation)
      (function(){
        async function updateStatus(teamId, status, btn) {
          try {
            const data = new FormData();
            data.append('id', teamId);
            data.append('status', status);
            const res = await fetch('../../api/coordinator/update_team_status.php', { method: 'POST', body: data, credentials: 'same-origin' });
            const text = await res.text();
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch (e) { json = null; }
            if (!res.ok || !json || !json.success) {
              const msg = (json && json.message) ? json.message : (text || res.statusText || 'Server error');
              alert('Error: ' + msg);
              return false;
            }
            // update UI: find card and badge
            const card = document.querySelector('.team-card[data-team-id="' + teamId + '"]');
            if (card) {
              card.dataset.approval = status;
              const badge = card.querySelector('.badge-approval');
              if (badge) {
                // update badge text and classes
                badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                badge.classList.remove('text-bg-success','text-bg-warning','text-bg-danger');
                if (status === 'approved') badge.classList.add('text-bg-success');
                else if (status === 'rejected') badge.classList.add('text-bg-danger');
                else badge.classList.add('text-bg-warning');
              }
            }
            return true;
          } catch (err) {
            console.error(err);
            alert('Network or server error');
            return false;
          }
        }

        document.addEventListener('click', function(e){
          const approve = e.target.closest('.action-approve');
          const reject = e.target.closest('.action-reject');
          const target = approve || reject;
          if (!target) return;
          e.preventDefault();
          const teamId = target.dataset.teamId || (target.closest('.team-card') && target.closest('.team-card').dataset.teamId);
          if (!teamId) { alert('Missing team id'); return; }
          const status = approve ? 'approved' : 'rejected';
          target.disabled = true;
          updateStatus(teamId, status, target).finally(()=>{ target.disabled = false; });
        });
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
                data-bs-toggle="modal"
                data-bs-target="#createGroupModal"
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
                  
                  <input
                    id="GroupSearch"
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
                    <div class="box mb-3 team-card" data-approval="<?php echo htmlspecialchars($team['approval_status'] ?? 'pending'); ?>" data-team-id="<?php echo (int)$team['id']; ?>">
                    <div class="card-body">
                      <div class="d-flex bd-highlight mb-4">
                        <div class="me-auto p-2 bd-highlight">
                          <h5 class="card-title"><?php echo htmlspecialchars($team['group_name']); ?></h5>
                        </div>
                        <div class="p-2 bd-highlight"><h3><span class="badge text-bg-primary"><?php echo htmlspecialchars($team['course_code'] ?? 'Unknown'); ?></span></h1></h3></div>
                        <div class="p-2 bd-highlight"><h3><span class="badge text-bg-secondary"><?php echo htmlspecialchars($team['section_code'] ?? $team['section_id']); ?></span></h1></h3></div>
                        <div class="p-2 bd-highlight"><h3><span class="badge badge-approval <?php echo ($team['approval_status'] === 'approved') ? 'text-bg-success' : 'text-bg-warning'; ?>"><?php echo htmlspecialchars(ucfirst($team['approval_status'] ?? 'pending')); ?></span></h1></h3></div>
                      </div>

                      <h6 class="card-subtitle mb-2 text"><?php echo htmlspecialchars($team['thesis_title'] ?? 'Thesis Title'); ?></h6>
                      <p class="box-label">Submitted on: <?php echo !empty($team['submission_date']) ? htmlspecialchars($team['submission_date']) : '—'; ?></p>
                      <p class="box-label">Defense Date: <?php echo !empty($team['defense_date']) ? htmlspecialchars($team['defense_date']) : '<em>Tentative</em>'; ?></p>
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
                                  <a href="#" class="badge text-bg-success text-decoration-none action-approve" data-team-id="<?php echo (int)$team['id']; ?>">Approve</a>
                                  <a href="#" class="badge text-bg-danger text-decoration-none action-reject" data-team-id="<?php echo (int)$team['id']; ?>">Reject</a>
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

    <!-- Create Group Modal -->
    <div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="createGroupModalLabel">Create Group</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="CreateGroupForm">
              <div class="mb-3">
                <label class="form-label">Group Name</label>
                <input type="text" name="group_name" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Thesis Title</label>
                <input type="text" name="thesis_title" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Section</label>
                <select name="section_id" class="form-select" required>
                  <option value="">-- select section --</option>
                  <?php foreach ($sections as $s): ?>
                    <?php
                      // find course code for section's course_id
                      $courseCode = '';
                      foreach ($handledCourses as $hc) { if ($hc['id'] == $s['course_id']) { $courseCode = $hc['course_code']; break; } }
                    ?>
                    <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars(($courseCode ? $courseCode . ' - ' : '') . $s['section_code']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Abstract</label>
                <textarea name="abstract" class="form-control" rows="4"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Adviser (optional)</label>
                <select name="adviser_id" class="form-select">
                  <option value="">-- none --</option>
                  <?php foreach ($advisers as $a):
                    $fullName = trim(($a['firstname'] ?? '') . ' ' . ($a['lastname'] ?? ''));
                    // exclude the test account named exactly 'Test Coordinator'
                    if (strcasecmp($fullName, 'Test Coordinator') === 0) continue;
                    $display = $fullName;
                    if (!empty($a['role']) && $a['role'] === 'coordinator') {
                      $display .= ' — Coordinator';
                    }
                  ?>
                    <option value="<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($display); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Academic Year (optional)</label>
                <input type="text" name="academic_year" class="form-control" placeholder="2026-2027" />
              </div>
              <div class="mb-3">
                <label class="form-label">Defense Date (optional)</label>
                <input type="text" name="defense_date" class="form-control" placeholder="e.g. 2026-12-15 or Dec 15, 2026" />
                <div class="form-text">Enter a date string; the server will convert it to a MySQL DATE.</div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="CreateGroupSubmit" class="btn btn-primary">Create Group</button>
          </div>
        </div>
      </div>
    </div>


    <script>
      // Group filter & search logic: show/hide team cards by approval status and search query
      (function(){
        const filter = document.getElementById('GroupFilter');
        const search = document.getElementById('GroupSearch');
        if (!filter && !search) return;

        function matchesFilter(card, filterVal) {
          if (!filterVal || filterVal === 'all') return true;
          const ap = (card.dataset.approval || '').toLowerCase();
          return ap === filterVal;
        }

        function matchesSearch(card, query) {
          if (!query) return true;
          const text = (card.innerText || card.textContent || '').toLowerCase();
          return text.indexOf(query) !== -1;
        }

        function updateVisibility() {
          const val = filter ? filter.value : 'all';
          const q = search ? (search.value || '').trim().toLowerCase() : '';
          const cards = Array.from(document.querySelectorAll('.team-card'));
          cards.forEach(card => {
            const okFilter = matchesFilter(card, val);
            const okSearch = matchesSearch(card, q);
            const show = okFilter && okSearch;
            card.style.display = show ? '' : 'none';
          });
        }

        if (filter) {
          filter.addEventListener('change', updateVisibility);
          filter.addEventListener('keyup', updateVisibility);
        }
        if (search) {
          search.addEventListener('input', updateVisibility);
          search.addEventListener('keyup', updateVisibility);
          search.addEventListener('change', updateVisibility);
        }

        // Run once on load to set initial visibility (covers autofill or prefilled queries)
        setTimeout(updateVisibility, 0);
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

      // Create Group form submission
      (function(){
        const btn = document.getElementById('CreateGroupSubmit');
        if (!btn) return;
        btn.addEventListener('click', async () => {
          const form = document.getElementById('CreateGroupForm');
          const data = new FormData(form);
          btn.disabled = true;
          const originalText = btn.textContent;
          btn.textContent = 'Creating...';
          try {
            // use relative path to be robust with different base paths
            const res = await fetch('../../api/coordinator/create_team.php', { method: 'POST', body: data, credentials: 'same-origin' });
            // try to parse JSON when possible
            const text = await res.text();
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch (e) { json = null; }

            if (!res.ok) {
              const msg = (json && json.message) ? json.message : (text || res.statusText || 'Server error');
              alert('Error: ' + msg);
            } else {
              if (json && json.success) {
                const modalEl = document.getElementById('createGroupModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                // append the newly created team to the list without reloading
                if (json.team) {
                  try {
                    appendTeamCard(json.team);
                  } catch (err) {
                    console.warn('Failed to append team card', err);
                  }
                }
              } else {
                const msg = (json && json.message) ? json.message : 'Unable to create group';
                alert('Error: ' + msg);
              }
            }
          } catch (err) {
            console.error('Create group request failed', err);
            alert('Network error: ' + (err && err.message ? err.message : 'request failed'));
          } finally {
            btn.disabled = false;
            btn.textContent = originalText || 'Create Group';
          }
        });
      })();

      // helper to build and insert a team card from the server response
      function appendTeamCard(team) {
        // find the box containing the filter and cards
        const filter = document.getElementById('GroupFilter');
        let box = filter ? filter.closest('.box') : document.querySelector('.box');
        if (!box) return;
        const cardsRow = box.querySelector('.row');
        if (!cardsRow) return;

        const card = document.createElement('div');
        card.className = 'box mb-3 team-card';
        card.setAttribute('data-approval', (team.approval_status || 'pending'));
        const submission = team.submission_date ? team.submission_date : '—';
        const adviserName = team.adviser_name ? team.adviser_name : '';
        const courseCode = team.course_code ? team.course_code : 'Unknown';
        const sectionCode = team.section_code ? team.section_code : team.section_id;
        const defenseHtml = team.defense_date ? escapeHtml(team.defense_date) : '<em>Tentative</em>';
        if (team.id) card.setAttribute('data-team-id', team.id);

        card.innerHTML = `
          <div class="card-body">
            <div class="d-flex bd-highlight mb-4">
              <div class="me-auto p-2 bd-highlight"><h5 class="card-title">${escapeHtml(team.group_name || '')}</h5></div>
              <div class="p-2 bd-highlight"><h3><span class="badge text-bg-primary">${escapeHtml(courseCode)}</span></h1></h3></div>
              <div class="p-2 bd-highlight"><h3><span class="badge text-bg-secondary">${escapeHtml(sectionCode)}</span></h1></h3></div>
              <div class="p-2 bd-highlight"><h3><span class="badge badge-approval text-bg-warning">${escapeHtml((team.approval_status || 'pending'))}</span></h1></h3></div>
            </div>
            <h6 class="card-subtitle mb-2 text">${escapeHtml(team.thesis_title || '')}</h6>
            <p class="box-label">Submitted on: ${escapeHtml(submission)}</p>
            <p class="box-label">Defense Date: ${defenseHtml}</p>
            <div class="d-flex bd-highlight mb-4">
              <div class="p-2 bd-highlight"><span class="text-muted">No members</span></div>
              <div class="ms-auto p-2 bd-highlight">
                <a href="#" class="badge text-bg-success text-decoration-none action-approve" data-team-id="${escapeHtml(team.id || '')}">Approve</a>
                <a href="#" class="badge text-bg-danger text-decoration-none action-reject" data-team-id="${escapeHtml(team.id || '')}">Reject</a>
              </div>
            </div>
          </div>
        `;

        // insert before first existing team card or append
        const first = cardsRow.querySelector('.team-card');
        if (first) cardsRow.insertBefore(card, first);
        else cardsRow.appendChild(card);
      }

      // basic HTML escape helper
      function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, function(c){
          return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
      }
    </script>
  </body>
</html>
