<?php
/**
 * @var string      $userName
 * @var string      $firstname
 * @var string      $lastname
 * @var array       $submissions
 */

require_once __DIR__ . '/../../../config/session.php';

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

  <!-- dashboard css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/dashboard.css">

  <!-- submissions specific css -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/student/submissions.css">
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
        <a class="nav-link" href="<?= BASE_URL ?>/student/group-profile">
          <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="bi"> Group Profile
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/milestones">
          <img src="<?= BASE_URL ?>/assets/icons/flag-fill.svg" alt="" class="bi"> Milestones
        </a>
        <a class="nav-link active" href="<?= BASE_URL ?>/student/submissions">
          <img src="<?= BASE_URL ?>/assets/icons/cloud-upload-fill.svg" alt="" class="bi"> Submissions
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/feedback">
          <img src="<?= BASE_URL ?>/assets/icons/chat-left-text-fill.svg" alt="" class="bi"> Feedbacks
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>/student/consultations">
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

      <!-- page header -->
      <div class="page-header">
        <p class="page-eyebrow">DOCUMENTS</p>
        <h1 class="page-title">Submissions</h1>
        <p class="page-subtitle">Upload thesis documents, view recent activity, and access your full archive.</p>
      </div>

      <!-- document type select -->
        <form action="<?= BASE_URL ?>/student/submissions-upload" method="post" enctype="multipart/form-data" id="uploadForm">
            <div class="doc-type-wrap">
                <label for="docType" class="doc-type-label">Document Type</label>
                <div class="doc-type-select-wrap">
                    <select id="docType" class="doc-type-select" name="docType" required>
                        <option value="" disabled selected>Select document type</option>
                        <option value="title-proposal">Title Proposal</option>
                        <option value="chapter-1">Chapter 1</option>
                        <option value="chapter-2">Chapter 2</option>
                        <option value="chapter-3">Chapter 3</option>
                        <option value="chapter-4">Chapter 4</option>
                        <option value="chapter-5">Chapter 5</option>
                        <option value="final-thesis">Final Thesis</option>
                        <option value="revision">Revision</option>
                    </select>
                    <span class="doc-type-chevron" aria-hidden="true">&#9662;</span>
                </div>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $_SESSION['error_message'] ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php elseif (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['success_message'] ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <!-- upload dropzone -->
            <div class="upload-dropzone" id="uploadDropzone">
                <input type="file" id="fileInput" class="upload-input" accept=".pdf" name="submissionFile">
                <div class="upload-icon">
                    <img src="<?= BASE_URL ?>/assets/icons/cloud-upload.svg" alt="" class="bi">
                </div>
                <p class="upload-title" id="uploadTitle">Drop PDF or click to choose</p>
                <p class="upload-helper" id="uploadHelper">PDF up to 25MB</p>
                <button type="submit" class="btn-upload" id="submitBtn" disabled>
                    <span id="submitBtnLabel">Submit</span>
                </button>
            </div>
        </form>
      


      <!-- recent submissions box -->
      <div class="box mt-3" id="recentSubmissionsBox">
        <p class="box-label">Recent Submissions</p>

        <!-- limit to 3 recent submissions -->
        <?php if (empty($submissions)) : ?>
            <p class="text-muted small text-center">No recent submissions found.</p>
        <?php else : ?>
            <?php $recentSubmissions = array_slice($submissions, 0, 3); ?>
            <?php foreach ($recentSubmissions as $submission) : ?>
                <div class="submission-item" data-id="<?= $submission['id'] ?>" data-title="<?= htmlspecialchars($submission['title']) ?>" data-date="<?= htmlspecialchars(date('F j, Y', strtotime($submission['uploaded_at']))) ?>" data-size="<?= $submission['file_size'] ?>" data-status="<?= htmlspecialchars(ucwords(str_replace('-', ' ', $submission['status']))) ?>" data-status-class="<?= htmlspecialchars($submission['status_class']) ?>" data-uploader="<?= htmlspecialchars($submission['uploader_name']) ?>">
                    <div class="submission-thumb">
                        <img src="<?= BASE_URL ?>/assets/icons/file-earmark-text.svg" alt="" class="bi">
                    </div>
                    <div class="submission-info">
                        <p class="submission-name"><?= htmlspecialchars($submission['document_type_label']) ?></p>
                        <p class="submission-meta"><?= htmlspecialchars(date('F j, Y', strtotime($submission['uploaded_at']))) ?> . <?= $submission['file_size'] ?></p>
                    </div>
                    <div class="submission-side">
                        <span class="badge-status <?= htmlspecialchars($submission['status_class']) ?>"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $submission['status']))) ?></span>
                        <button type="button" class="submission-view btn-preview">View</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

      </div>

      <!-- submission history -->
      <p class="box-label mt-3">Submission History</p>
      <div class="history-list" id="historyList">

        <?php if (empty($submissions)) : ?>
            <p class="text-muted small text-center">No submissions found.</p>
        <?php else : ?>
            <?php foreach ($submissions as $submission) : ?>
                <div class="history-row" data-id="<?= $submission['id'] ?>" data-title="<?= htmlspecialchars($submission['title']) ?>" data-date="<?= htmlspecialchars(date('F j, Y', strtotime($submission['uploaded_at']))) ?>" data-size="<?= $submission['file_size'] ?>" data-status="<?= htmlspecialchars(ucwords(str_replace('-', ' ', $submission['status']))) ?>" data-status-class="<?= htmlspecialchars($submission['status_class']) ?>" data-uploader="<?= htmlspecialchars($submission['uploader_name']) ?>">
                    <div class="history-bullet"></div>
                    <div class="history-info">
                        <p class="history-name"><?= htmlspecialchars($submission['document_type_label']) ?></p>
                        <p class="history-date"><?= htmlspecialchars(date('F j, Y', strtotime($submission['uploaded_at']))) ?></p>
                    </div>
                    <div class="history-side">
                        <span class="badge-status <?= htmlspecialchars($submission['status_class']) ?>"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $submission['status']))) ?></span>
                        <button type="button" class="history-download btn-preview">View</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>


      </div>

    </main>
  </div>

  <!-- preview modal -->
  <div class="preview-overlay" id="previewOverlay">
    <div class="preview-modal">
      <div class="preview-header">
        <div>
          <p class="preview-eyebrow">SUBMISSION DETAILS</p>
          <h3 class="preview-title" id="previewTitle">Title</h3>
        </div>
        <button type="button" class="preview-close" id="previewClose" aria-label="Close">
          <img src="<?= BASE_URL ?>/assets/icons/x-lg.svg" alt="" class="bi">
        </button>
      </div>

      <div class="preview-body">
        <div class="preview-meta">
          <div class="preview-meta-item">
            <span class="preview-meta-label">Date</span>
            <span class="preview-meta-value" id="previewDate">--</span>
          </div>
          <div class="preview-meta-item">
            <span class="preview-meta-label">Size</span>
            <span class="preview-meta-value" id="previewSize">--</span>
          </div>
          <div class="preview-meta-item">
            <span class="preview-meta-label">Uploaded by</span>
            <span class="preview-meta-value" id="previewUploader">--</span>
          </div>
          <div class="preview-meta-item">
            <span class="preview-meta-label">Status</span>
            <span class="badge-status" id="previewStatus">--</span>
          </div>
        </div>

        <div class="preview-section">
          <p class="preview-section-label">Document Preview</p>
          <div class="preview-file">
            <div class="preview-file-icon">
              <img src="<?= BASE_URL ?>/assets/icons/file-earmark-text.svg" alt="" class="bi">
            </div>
            <div class="preview-file-info">
              <p class="preview-file-name" id="previewFileName">document.pdf</p>
              <p class="preview-file-meta" id="previewFileMeta">PDF Document</p>
            </div>
          </div>
        </div>
      </div>

      <div class="preview-footer">
        <button type="button" class="preview-btn preview-btn-secondary" id="previewView">
          <img src="<?= BASE_URL ?>/assets/icons/eye.svg" alt="" class="bi">
          Preview
        </button>
        <button type="button" class="preview-btn preview-btn-secondary" id="previewDownload">
          <img src="<?= BASE_URL ?>/assets/icons/download.svg" alt="" class="bi">
          Download
        </button>
        <button type="button" class="preview-btn preview-btn-primary" id="previewCloseBtn">Close</button>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/js/student/theme.js"></script>
  <script src="<?= BASE_URL ?>/js/student/sidebar.js"></script>
  <script src="<?= BASE_URL ?>/js/student/submissions.js"></script>
  
</body>
</html>
