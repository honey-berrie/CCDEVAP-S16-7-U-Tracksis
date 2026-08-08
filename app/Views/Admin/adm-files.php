<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Uploaded Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-links.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-files.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Uploaded Files</h1>
                <p class="page-subtitle">Browse every thesis-related file submitted across all groups.</p>
            </div>
        </div>

        <div class="panel-card">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <select class="filter-select" id="fileTypeFilter">
                        <option value="">All Types</option>
                    </select>
                    <select class="filter-select" id="fileSortSelect">
                        <option value="uploaded_at:desc">Newest first</option>
                        <option value="uploaded_at:asc">Oldest first</option>
                        <option value="file_name:asc">File name (A-Z)</option>
                        <option value="file_name:desc">File name (Z-A)</option>
                        <option value="group_name:asc">Thesis group (A-Z)</option>
                        <option value="document_type:asc">Submission type</option>
                    </select>
                </div>
                <div class="toolbar-filters">
                    <input type="search" class="form-control-admin" id="fileSearchInput" placeholder="Search by file, group, or uploader…" style="max-width: 260px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Thesis Group</th>
                            <th>Uploaded By</th>
                            <th>Type / Milestone</th>
                            <th>Adviser</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="filesTableBody">
                        <tr><td colspan="7" class="empty-state">Loading…</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-bar">
                <div class="rows-per-page">
                    Rows per page:
                    <select id="filesRowsPerPage">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <span id="filesPaginationSummary">Showing 0 of 0</span>
                <div class="pagination-controls" id="filesPaginationControls"></div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/Admin/adm-global.js"></script>
    <script src="<?= BASE_URL ?>/js/Admin/adm-files.js"></script>
</body>
</html>
