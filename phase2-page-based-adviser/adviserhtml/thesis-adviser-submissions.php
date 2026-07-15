<?php
require_once __DIR__ . '/../adviser-data.php';

adviserHandlePost($pdo, 'thesis-adviser-submissions.php');

$submissions = adviserGetSubmissions($pdo);
$successMessage = $_GET['saved'] ?? null;

function adviserSubmissionStatusKey(string $status): string
{
    return strtolower($status);
}

function adviserSubmissionBadgeClass(string $status): string
{
    $statusKey = adviserSubmissionStatusKey($status);

    if ($statusKey === 'pending') {
        return 'pending-review';
    }

    return $statusKey;
}

function adviserSubmissionCount(array $submissions, string $status): int
{
    if ($status === 'all') {
        return count($submissions);
    }

    return count(array_filter($submissions, function ($submission) use ($status) {
        return adviserSubmissionStatusKey($submission['status']) === $status;
    }));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions - Adviser</title>
    
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
            <li><a href="thesis-adviser-groups.php"><img src="../assets/icons/people-fill.svg" alt="" class="bi"><span>Thesis Groups</span></a></li>
            <li><a href="thesis-milestone.php"><img src="../assets/icons/flag-fill.svg" alt="" class="bi"><span>Milestones</span></a></li>
            <li><a href="thesis-adviser-submissions.php" class="active"><img src="../assets/icons/cloud-upload-fill.svg" alt="" class="bi"><span>Submissions</span></a></li>
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

        <header class="page-header submissions-header">
          <h2>Submissions</h2>
          <p class="subtitle">Review and act on documents uploaded by your groups</p>
        </header>

        <?php if ($successMessage === 'status'): ?>
          <div class="alert alert-success" role="alert">
            Submission status saved successfully.
          </div>
        <?php endif; ?>

        <section class="submission-filters" aria-label="Submission filters">
          <button class="filter-chip active" data-action="filter-submissions" data-filter="all">All (<?= adviserSubmissionCount($submissions, 'all') ?>)</button>
          <button class="filter-chip" data-action="filter-submissions" data-filter="revision">Revision (<?= adviserSubmissionCount($submissions, 'revision') ?>)</button>
          <button class="filter-chip" data-action="filter-submissions" data-filter="pending">Pending (<?= adviserSubmissionCount($submissions, 'pending') ?>)</button>
          <button class="filter-chip" data-action="filter-submissions" data-filter="approved">Approved (<?= adviserSubmissionCount($submissions, 'approved') ?>)</button>
          <button class="filter-chip" data-action="filter-submissions" data-filter="rejected">Rejected (<?= adviserSubmissionCount($submissions, 'rejected') ?>)</button>
        </section>

        <section class="submission-list">
          <?php if (empty($submissions)): ?>
            <p class="subtitle">No submissions found.</p>
          <?php endif; ?>

          <?php foreach ($submissions as $submission): ?>
            <?php
              $statusKey = adviserSubmissionStatusKey($submission['status']);
              $badgeClass = adviserSubmissionBadgeClass($submission['status']);
            ?>
            <article
              class="submission-card"
              data-status="<?= htmlspecialchars($statusKey) ?>"
              data-submission-id="<?= htmlspecialchars((string) $submission['submission_id']) ?>"
              data-file-name="<?= htmlspecialchars($submission['file_name']) ?>"
              data-feedback="<?= htmlspecialchars($submission['adviser_feedback'] ?? '') ?>"
            >
              <div class="avatar-placeholder"></div>
              <div class="submission-main">
                <div class="submission-title-row">
                  <h3><?= htmlspecialchars($submission['title']) ?></h3>
                  <span class="status-badge <?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($submission['status']) ?></span>
                </div>
                <p><?= htmlspecialchars($submission['group_name']) ?></p>
                <p class="submission-feedback">
                  <strong>Feedback:</strong>
                  <?= htmlspecialchars(!empty($submission['adviser_feedback']) ? $submission['adviser_feedback'] : 'No adviser feedback yet.') ?>
                </p>
              </div>
              <div class="submission-actions">
                <button class="btn-secondary small" data-action="preview-submission">Preview</button>
                <button
                  class="btn-secondary small"
                  data-action="revise-submission"
                  data-submission-id="<?= htmlspecialchars((string) $submission['submission_id']) ?>"
                >Revise</button>

                <form class="submission-action-form" method="post" action="thesis-adviser-submissions.php">
                  <input type="hidden" name="action" value="update-submission-status">
                  <input type="hidden" name="submission_id" value="<?= htmlspecialchars((string) $submission['submission_id']) ?>">
                  <input type="hidden" name="status" value="Approved">
                  <input type="hidden" name="feedback" value="Approved by adviser.">
                  <button class="btn-secondary small" type="submit">Approve</button>
                </form>

                <form class="submission-action-form" method="post" action="thesis-adviser-submissions.php">
                  <input type="hidden" name="action" value="update-submission-status">
                  <input type="hidden" name="submission_id" value="<?= htmlspecialchars((string) $submission['submission_id']) ?>">
                  <input type="hidden" name="status" value="Rejected">
                  <input type="hidden" name="feedback" value="Rejected by adviser.">
                  <button class="btn-secondary small danger" type="submit">Reject</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </section>
      </main>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/adviser-interactions.js?v=20260715c"></script>
</body>
</html>
