<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Feedback History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../CSS/Admin/adm-links.css">
    <link rel="stylesheet" href="../../CSS/Admin/adm-feedback.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Feedback History</h1>
                <p class="page-subtitle">Browse feedback submitted on thesis groups across the system.</p>
            </div>
        </div>

        <div class="panel-card">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <select class="filter-select" id="feedbackSortSelect">
                        <option value="created_at:desc">Newest first</option>
                        <option value="created_at:asc">Oldest first</option>
                        <option value="author_name:asc">Author (A-Z)</option>
                        <option value="group_name:asc">Thesis group (A-Z)</option>
                    </select>
                </div>
                <div class="toolbar-filters">
                    <input type="search" class="form-control-admin" id="feedbackSearchInput" placeholder="Search by author, group, or message…" style="max-width: 260px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Submitted By</th>
                            <th>Thesis Group</th>
                            <th>Feedback</th>
                            <th>Related To</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="feedbackTableBody">
                        <tr><td colspan="6" class="empty-state">Loading…</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-bar">
                <div class="rows-per-page">
                    Rows per page:
                    <select id="feedbackRowsPerPage">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <span id="feedbackPaginationSummary">Showing 0 of 0</span>
                <div class="pagination-controls" id="feedbackPaginationControls"></div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="feedbackModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Feedback Details</h3>
                <button type="button" class="modal-close" data-close-modal="feedbackModalOverlay">&times;</button>
            </div>
            <div class="detail-section">
                <h4>Submitted By</h4>
                <p id="feedbackDetailAuthor"></p>
            </div>
            <div class="detail-section">
                <h4>Thesis Group</h4>
                <p id="feedbackDetailGroup"></p>
            </div>
            <div class="detail-section" id="feedbackDetailRelatedWrap">
                <h4>Related To</h4>
                <p id="feedbackDetailRelated"></p>
            </div>
            <div class="detail-section">
                <h4>Submitted</h4>
                <p id="feedbackDetailDate"></p>
            </div>
            <div class="detail-section">
                <h4>Message</h4>
                <p id="feedbackDetailMessage"></p>
            </div>
        </div>
    </div>

    <script src="../../JS/Admin/adm-global.js"></script>
    <script src="../../JS/Admin/adm-feedback.js"></script>
</body>
</html>
