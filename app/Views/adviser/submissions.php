<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $submissions
 */

require_once __DIR__ . '/../../../config/session.php';

// count by status for filter chips
$counts = ['all' => count($submissions)];
foreach (['in-review', 'revision-requested', 'approved', 'rejected'] as $s) {
    $counts[$s] = count(array_filter($submissions, fn($sub) => $sub['status'] === $s));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Submissions | U-Tracksis</title>

  <!-- Apply theme immediately -->
  <script>
    (function() {
      var t = localStorage.getItem('theme');
      var d = t || (window.matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light');
      if (d === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    })();
  </script>

  <!-- bootstrap css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css">

  <!-- local bootstrap icons -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap-icons.css">

  <!-- custom css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/adviser/dashboard.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/adviser/submissions.css">
</head>
<body>
  <script src="<?= BASE_URL ?>/js/maintenance-check.js"></script>

  <div class="dashboard-wrapper">

    <!-- sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="U-Tracksis" class="sidebar-logo">
        <div>
          <h6 class="sidebar-title">U-Tracksis</h6>
          <p class="sidebar-subtitle">ADVISER</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/dashboard">
          <img src="<?= BASE_URL ?>/assets/icons/grid-1x2-fill.svg" alt="" class="bi"> Dashboard
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/groups">
          <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="bi"> Thesis Groups
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/milestones">
          <img src="<?= BASE_URL ?>/assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link active" href="<?= BASE_URL ?>/adviser/submissions">
          <img src="<?= BASE_URL ?>/assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/adviser/consultations">
          <img src="<?= BASE_URL ?>/assets/icons/chat-dots-fill.svg" alt="" class="bi"> Consultations
        </a>
      </nav>

      <div class="sidebar-footer">
        <span>Theme</span>
        <button class="theme-toggle" id="themeToggle" type="button" title="Toggle theme">
          <img src="<?= BASE_URL ?>/assets/icons/moon-stars-fill.svg" alt="" class="bi" id="themeIcon">
        </button>
      </div>
    </aside>

    <!-- mobile overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- main content -->
    <main class="main-content">

      <!-- top bar -->
      <div class="top-bar">
        <button class="mobile-menu-btn" id="menuBtn" type="button">
          <img src="<?= BASE_URL ?>/assets/icons/list.svg" alt="" class="bi">
        </button>

        <div class="topbar-actions">
          <span class="topbar-user" id="topbar-user"><?= htmlspecialchars($userName) ?></span>
          <form action="<?= BASE_URL ?>/logout" method="get">
            <button class="btn-logout" type="submit">
              <img src="<?= BASE_URL ?>/assets/icons/box-arrow-left.svg" alt="" class="bi">
              <span>Logout</span>
            </button>
          </form>
        </div>
      </div>

      <div class="page-header">
        <p class="page-eyebrow">REVIEW</p>
        <h1 class="page-title">Submissions</h1>
        <p class="page-subtitle">Review and act on documents uploaded by your groups.</p>
      </div>

      <!-- filter chips -->
      <div class="submission-filters" id="submissionFilters">
        <button class="filter-chip active" data-filter="all">All (<?= $counts['all'] ?>)</button>
        <button class="filter-chip" data-filter="in-review">Pending (<?= $counts['in-review'] ?>)</button>
        <button class="filter-chip" data-filter="revision-requested">Revision (<?= $counts['revision-requested'] ?>)</button>
        <button class="filter-chip" data-filter="approved">Approved (<?= $counts['approved'] ?>)</button>
        <button class="filter-chip" data-filter="rejected">Rejected (<?= $counts['rejected'] ?>)</button>
      </div>

      <!-- submission list grouped by group -->
      <div class="submission-list" id="submissionList">
        <?php if (empty($submissions)): ?>
          <div class="box">
            <p class="text-muted small mb-0">No submissions found.</p>
          </div>
        <?php endif; ?>

        <?php
          // group submissions by group_name
          $grouped = [];
          foreach ($submissions as $sub) {
              $grouped[$sub['group_name']][] = $sub;
          }
        ?>

        <?php foreach ($grouped as $groupName => $groupSubs): ?>
          <div class="submission-group-block" data-group="<?= htmlspecialchars($groupName) ?>">
            <button class="group-toggle" type="button" data-group="<?= htmlspecialchars($groupName) ?>">
              <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="bi group-toggle-icon">
              <span class="group-toggle-name"><?= htmlspecialchars($groupName) ?></span>
              <span class="group-toggle-count"><?= count($groupSubs) ?> submission<?= count($groupSubs) !== 1 ? 's' : '' ?></span>
              <img src="<?= BASE_URL ?>/assets/icons/chevron-down.svg" alt="" class="bi group-toggle-chevron">
            </button>
            <div class="group-submissions" id="group-<?= htmlspecialchars($groupName) ?>">
              <?php foreach ($groupSubs as $sub): ?>
                <div class="submission-card"
                     data-status="<?= htmlspecialchars($sub['status']) ?>"
                     data-submission-id="<?= (int) $sub['submission_id'] ?>"
                     data-file-name="<?= htmlspecialchars($sub['file_name']) ?>"
                     data-uploader="<?= htmlspecialchars($sub['uploader_name']) ?>"
                     data-date="<?= htmlspecialchars($sub['uploaded_at_formatted']) ?>"
                     data-size="<?= htmlspecialchars($sub['file_size_formatted']) ?>">
                  <div class="submission-avatar">
                    <img src="<?= BASE_URL ?>/assets/icons/file-earmark-text.svg" alt="" class="bi">
                  </div>
                  <div class="submission-main">
                    <div class="submission-title-row">
                      <h3><?= htmlspecialchars($sub['title']) ?></h3>
                      <span class="badge-status <?= $sub['status_class'] ?>"><?= htmlspecialchars($sub['status_label']) ?></span>
                    </div>
                    <p class="submission-group"><?= htmlspecialchars($sub['document_type_label']) ?> &middot; <?= htmlspecialchars($sub['uploaded_at_formatted']) ?></p>
                    <p class="submission-feedback">
                      <strong>Feedback:</strong>
                      <?= htmlspecialchars(!empty($sub['review_notes']) ? $sub['review_notes'] : 'No adviser feedback yet.') ?>
                    </p>
                  </div>
                  <div class="submission-actions">
                    <button class="btn-action-secondary btn-preview" data-action="preview" data-submission-id="<?= (int) $sub['submission_id'] ?>">Preview</button>
                    <button class="btn-action-secondary btn-revise" data-action="revise" data-submission-id="<?= (int) $sub['submission_id'] ?>">Revise</button>
                    <button class="btn-action-primary btn-approve" data-action="approve" data-submission-id="<?= (int) $sub['submission_id'] ?>">Approve</button>
                    <button class="btn-action-danger btn-reject" data-action="reject" data-submission-id="<?= (int) $sub['submission_id'] ?>">Reject</button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </main>
  </div>

  <!-- action modal (revise / reject / preview) -->
  <div class="modal-overlay" id="actionModal">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="modalTitle">Action</h3>
        <button class="modal-close" id="modalCloseBtn" type="button">&times;</button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer" id="modalFooter"></div>
    </div>
  </div>

  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/sidebar.js"></script>
  <script src="<?= BASE_URL ?>/js/adviser/submissions.js"></script>
</body>
</html>