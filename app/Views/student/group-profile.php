<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var object      $teamInfo
 * @var array       $members
 * @var object       $adviser
 * @var string|null  $flash
 */

require_once __DIR__ . '/../../../config/session.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Group Profile | U-Tracksis</title>

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
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/dashboard.css">

  <!-- group profile specific css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/group-profile.css">
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
          <p class="sidebar-subtitle">STUDENT</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="<?= BASE_URL ?>/student/dashboard">
          <img src="<?= BASE_URL ?>/assets/icons/grid-1x2-fill.svg" alt="" class="bi"> Dashboard
        </a>
        <a class="nav-link active" href="<?= BASE_URL ?>/student/group-profile">
          <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="bi"> Group Profile
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/milestones">
          <img src="<?= BASE_URL ?>/assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/submissions">
          <img src="<?= BASE_URL ?>/assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/feedback">
          <img src="<?= BASE_URL ?>/assets/icons/chat-left-text-fill.svg" alt="" class="bi"> Feedbacks
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/consultations">
          <img src="<?= BASE_URL ?>/assets/icons/chat-dots-fill.svg" alt="" class="bi"> Consultations
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/announcements">
  <img src="<?= BASE_URL ?>/assets/icons/megaphone-fill.svg" alt="" class="bi"> Announcements
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
          <span class="topbar-user" id="topbar-user"><?= $userName ?></span>
          <form action="<?= BASE_URL ?>/logout" method="get">
            <button class="btn-logout" type="submit">
              <img src="<?= BASE_URL ?>/assets/icons/box-arrow-left.svg" alt="" class="bi">
              <span>Logout</span>
            </button>
          </form>
        </div>
      </div>

       <!-- page header -->
        <div class="page-header">
            <p class="page-eyebrow">PROFILE</p>
            <h1 class="page-title">Group Profile</h1>
            <p class="page-subtitle">Manage your thesis group profile, members, and research scope</p>
        </div>

      <!-- If no group is found -->
        <?php if (!$teamInfo): ?>
            <div class="mb-3" id="noGroupMessage">
                <p class="alert alert-warning text-center mb-0">You are not part of any group yet.</p>
            </div>
        <?php else: ?>
            <!-- group details and members row -->
            <div class="row g-3 mb-3" id="groupProfileContent" style="display: flex;">
                <!-- group details box -->
                <div class="col-lg-8">
                <div class="box h-100">
                    <p class="box-label">Group Details</p>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($_SESSION['error_message']) ?>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php elseif (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($_SESSION['success_message']) ?>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/student/group-profile-update" method="post" id="groupDetailsForm">
                        <div class="mb-3">
                            <label class="form-label-sm">Group Name</label>
                            <input type="text" class="form-control" name="team_name" id="groupName" placeholder="Enter group name" value="<?= $teamInfo['group_name'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-sm">Thesis Title</label>
                            <input type="text" class="form-control" name="thesis_title" id="thesisTitle" placeholder="Enter thesis title" value="<?= $teamInfo['thesis_title'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-sm">Abstract</label>
                            <textarea class="form-control abstract-textarea" name="abstract" id="abstract" rows="6" placeholder="Write your abstract here..."><?= $teamInfo['abstract'] ?></textarea>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn-action-primary">Update Group</button>
                        </div>
                    </form>
                </div>
                </div>

                <!-- members and adviser column -->
                <div class="col-lg-4">
                <div class="d-flex flex-column gap-3 h-100">
                    <!-- members box -->
                    <div class="box" id="membersBox">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="box-label mb-0">Members</p>
                        </div>
                        <?php foreach ($members as $member): ?>
                            <div class="member-item">
                                <div class="member-avatar"><?= $member['initials'] ?></div>
                                <div class="member-info">
                                    <p class="member-name"><?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?></p>
                                    <p class="member-role">Member</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- adviser box -->
                    <div class="box" id="adviserBox">
                    <p class="box-label">Adviser</p>
                    <div class="member-item">
                        <div class="member-avatar"><?= $adviser['initials'] ?></div>
                        <div class="member-info">
                            <p class="member-name" id="adviserName"><?= htmlspecialchars($adviser['firstname'] . ' ' . $adviser['lastname']) ?></p>
                            <p class="member-role">Main Adviser</p>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>

        <?php endif; ?>

    </main>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/student/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/student/sidebar.js"></script>
</body>
</html>
