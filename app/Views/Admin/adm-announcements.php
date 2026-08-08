<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-links.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-announcements.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Announcements</h1>
                <p class="page-subtitle">Post system-wide updates. Announcements replace notifications for the Admin role.</p>
            </div>
        </div>

        <div class="announcements-layout">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">All Announcements</h2>
                    <span class="panel-subtitle" id="announcementsCount">0 total</span>
                </div>
                <div id="announcementsList" class="panel-list">
                    <p class="empty-state">Loading…</p>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title" id="composeTitle">New Announcement</h2>
                </div>
                <form id="announcementForm">
                    <input type="hidden" id="annId" value="">
                    <div class="form-group">
                        <label for="annTitleInput">Title</label>
                        <input type="text" class="form-control-admin" id="annTitleInput" required>
                    </div>
                    <div class="form-group">
                        <label for="annMessage">Message</label>
                        <textarea class="form-control-admin" id="annMessage" rows="5" required></textarea>
                    </div>
                    <p class="form-error" id="annFormError"></p>
                </form>
                <div class="modal-footer" style="justify-content: flex-start;">
                    <button type="button" class="btn-secondary-admin" id="previewAnnBtn">Preview</button>
                    <button type="button" class="btn-primary-admin" id="postAnnBtn">Post Announcement</button>
                    <button type="button" class="btn-danger-admin" id="cancelEditBtn" style="display:none;">Cancel Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal-overlay" id="previewModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Preview</h3>
                <button type="button" class="modal-close" data-close-modal="previewModalOverlay">&times;</button>
            </div>
            <div id="previewBody"></div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/Admin/adm-global.js"></script>
    <script src="<?= BASE_URL ?>/js/Admin/adm-announcements.js"></script>
</body>
</html>
