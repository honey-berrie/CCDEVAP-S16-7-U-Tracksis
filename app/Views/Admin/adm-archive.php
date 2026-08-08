<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Archive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-links.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-archive.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Archive</h1>
                <p class="page-subtitle">Review completed thesis groups and manage institutional records.</p>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <h2 class="panel-title">Ready to Archive</h2>
            </div>
            <p class="panel-subtitle">Every milestone has been approved by the adviser — these groups can now be archived.</p>
            <div id="readyToArchiveList" class="panel-list">
                <p class="empty-state">Loading…</p>
            </div>
        </div>

        <div class="panel-card">
            <div class="toolbar">
                <h2 class="panel-title" style="margin:0;">Archived Theses</h2>
                <input type="search" class="form-control-admin" id="archiveSearchInput" placeholder="Search archived groups…" style="max-width: 240px;">
            </div>

            <div class="card-grid" id="archiveGrid">
                <p class="empty-state">Loading…</p>
            </div>

            <div class="pagination-bar">
                <span id="archivePaginationSummary">Showing 0 of 0</span>
                <div class="pagination-controls" id="archivePaginationControls"></div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="archiveModalOverlay">
        <div class="modal-box modal-wide">
            <div class="modal-header">
                <h3 class="modal-title" id="archiveModalTitle">Thesis Group</h3>
                <button type="button" class="modal-close" data-close-modal="archiveModalOverlay">&times;</button>
            </div>
            <div id="archiveModalBody">
                <p class="empty-state">Loading…</p>
            </div>
        </div>
    </div>

    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/js/Admin/adm-global.js"></script>
    <script src="<?= BASE_URL ?>/js/Admin/adm-archive.js"></script>
</body>
</html>
