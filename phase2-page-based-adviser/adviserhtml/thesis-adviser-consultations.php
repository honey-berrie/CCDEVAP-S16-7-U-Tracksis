<?php
require_once __DIR__ . '/../adviser-data.php';

adviserHandlePost($pdo, 'thesis-adviser-consultations.php');

$upcomingConsultations = adviserGetConsultations($pdo, 'Scheduled');
$consultationHistory = adviserGetConsultations($pdo, 'Completed');
$thesisGroups = adviserGetThesisGroups($pdo);
$successMessage = $_GET['saved'] ?? null;

function adviserFormatMonth(string $dateTime): string
{
    return date('M', strtotime($dateTime));
}

function adviserFormatDay(string $dateTime): string
{
    return date('d', strtotime($dateTime));
}

function adviserFormatTimeRange(string $dateTime, ?string $endDateTime = null): string
{
    $start = date('g:i A', strtotime($dateTime));

    if (empty($endDateTime)) {
        return $start;
    }

    return $start . ' - ' . date('g:i A', strtotime($endDateTime));
}

function adviserFormatInputDate(string $dateTime): string
{
    return date('Y-m-d', strtotime($dateTime));
}

function adviserFormatInputTime(string $dateTime): string
{
    return date('H:i', strtotime($dateTime));
}

function adviserFormatInputEndTime(?string $dateTime, string $fallbackDateTime): string
{
    return adviserFormatInputTime(!empty($dateTime) ? $dateTime : date('Y-m-d H:i:s', strtotime($fallbackDateTime . ' +1 hour')));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - Adviser</title>
    
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
            <li><a href="thesis-adviser-submissions.php"><img src="../assets/icons/cloud-upload-fill.svg" alt="" class="bi"><span>Submissions</span></a></li>
            <li><a href="thesis-adviser-consultations.php" class="active"><img src="../assets/icons/chat-dots-fill.svg" alt="" class="bi"><span>Consultations</span></a></li>
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

        <header class="page-header consultation-header">
          <div>
            <h2>Consultations</h2>
            <p class="subtitle">Schedule sessions and review consultation history</p>
          </div>
          <button class="btn-outline" data-action="schedule-consultation">+ Schedule consultation</button>
        </header>

        <?php if ($successMessage === 'comment'): ?>
          <div class="alert alert-success" role="alert">
            Consultation comment saved successfully.
          </div>
        <?php endif; ?>

        <?php if ($successMessage === 'schedule'): ?>
          <div class="alert alert-success" role="alert">
            Consultation schedule saved successfully.
          </div>
        <?php endif; ?>

        <?php if ($successMessage === 'created'): ?>
          <div class="alert alert-success" role="alert">
            Consultation scheduled successfully.
          </div>
        <?php endif; ?>

        <section class="consultation-section">
          <h3>Upcoming</h3>

          <div class="consultation-list">
            <?php if (empty($upcomingConsultations)): ?>
              <p class="subtitle">No upcoming consultations found.</p>
            <?php endif; ?>

            <?php foreach ($upcomingConsultations as $consultation): ?>
              <article class="consultation-card upcoming-card">
                <div class="consultation-date">
                  <span><?= htmlspecialchars(adviserFormatMonth($consultation['consultation_date'])) ?></span>
                  <strong><?= htmlspecialchars(adviserFormatDay($consultation['consultation_date'])) ?></strong>
                </div>
                <div class="consultation-details">
                  <h4><?= htmlspecialchars($consultation['notes']) ?></h4>
                  <p><?= htmlspecialchars($consultation['group_name']) ?></p>
                  <p><?= htmlspecialchars(adviserFormatTimeRange($consultation['consultation_date'], $consultation['consultation_end'] ?? null)) ?></p>
                  <p><?= htmlspecialchars($consultation['meeting_link'] ? 'Via Zoom' : 'No meeting link') ?></p>
                  <?php if (!empty($consultation['reschedule_reason'])): ?>
                    <p><strong>Reason:</strong> <?= htmlspecialchars($consultation['reschedule_reason']) ?></p>
                  <?php endif; ?>
                </div>
                <div class="consultation-actions">
                  <button
                    class="btn-secondary"
                    data-action="reschedule-consultation"
                    data-consultation-id="<?= htmlspecialchars((string) $consultation['consultation_id']) ?>"
                    data-current-date="<?= htmlspecialchars(adviserFormatInputDate($consultation['consultation_date'])) ?>"
                    data-current-time="<?= htmlspecialchars(adviserFormatInputTime($consultation['consultation_date'])) ?>"
                    data-current-end-time="<?= htmlspecialchars(adviserFormatInputEndTime($consultation['consultation_end'] ?? null, $consultation['consultation_date'])) ?>"
                  >Reschedule</button>
                  <button class="btn-outline compact" data-action="join-consultation" data-meeting-link="<?= htmlspecialchars($consultation['meeting_link']) ?>">Join</button>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="consultation-section">
          <h3>History</h3>

          <div class="history-panel">
            <?php if (empty($consultationHistory)): ?>
              <p class="subtitle">No completed consultations found.</p>
            <?php endif; ?>

            <?php foreach ($consultationHistory as $consultation): ?>
              <article class="history-card">
                <div class="avatar-placeholder"></div>
                <div class="history-meta">
                  <h4>Consultation</h4>
                  <p><?= htmlspecialchars($consultation['group_name']) ?></p>
                  <p><?= htmlspecialchars(adviserFormatTimeRange($consultation['consultation_date'], $consultation['consultation_end'] ?? null)) ?></p>
                  <p><?= htmlspecialchars($consultation['meeting_link'] ? 'Via Zoom' : 'No meeting link') ?></p>
                </div>
                <div class="comment-box">
                  <span>Comments</span>
                  <p><?= htmlspecialchars($consultation['notes']) ?></p>
                </div>
                <button
                  class="btn-secondary"
                  data-action="edit-comment"
                  data-consultation-id="<?= htmlspecialchars((string) $consultation['consultation_id']) ?>"
                >Edit comment</button>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

      </main>
    </div>
    <script>
      window.thesisGroups = <?= json_encode($thesisGroups, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/adviser-interactions.js?v=20260715c"></script>
</body>
</html>
