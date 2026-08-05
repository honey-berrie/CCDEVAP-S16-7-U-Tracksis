<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Thesis Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-links.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/Admin/adm-records.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Thesis Records</h1>
                <p class="page-subtitle">Search, filter, and review every active thesis group's progress.</p>
            </div>
            <div class="quick-actions">
                <button type="button" class="btn-quick-action" id="openCreateTeamModal">
                    <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="nav-icon">
                    Create Thesis Group
                </button>
            </div>
        </div>

        <div class="panel-card">
            <div class="toolbar">
                <div class="toolbar-filters" id="statusFilterChips">
                    <button type="button" class="filter-chip active" data-status="">All</button>
                    <button type="button" class="filter-chip" data-status="on-track">On Track</button>
                    <button type="button" class="filter-chip" data-status="behind">Behind</button>
                    <button type="button" class="filter-chip" data-status="completed">Completed</button>
                </div>
                <div class="toolbar-filters">
                    <select class="filter-select" id="adviserFilter">
                        <option value="">All Advisers</option>
                    </select>
                    <input type="search" class="form-control-admin" id="recordsSearchInput" placeholder="Search group or thesis title…" style="max-width: 240px;">
                </div>
            </div>

            <div class="card-grid" id="recordsGrid">
                <p class="empty-state">Loading…</p>
            </div>

            <div class="pagination-bar">
                <span id="recordsPaginationSummary">Showing 0 of 0</span>
                <div class="pagination-controls" id="recordsPaginationControls"></div>
            </div>
        </div>
    </div>

    <!-- Record Detail Modal -->
    <div class="modal-overlay" id="recordModalOverlay">
        <div class="modal-box modal-wide">
            <div class="modal-header">
                <h3 class="modal-title" id="recordModalTitle">Thesis Group</h3>
                <button type="button" class="modal-close" data-close-modal="recordModalOverlay">&times;</button>
            </div>
            <div id="recordModalBody">
                <p class="empty-state">Loading…</p>
            </div>
        </div>
    </div>

    <!-- Create Thesis Group Modal -->
    <div class="modal-overlay" id="teamModalOverlay">
        <div class="modal-box modal-wide">
            <div class="modal-header">
                <h3 class="modal-title">Create Thesis Group</h3>
                <button type="button" class="modal-close" data-close-modal="teamModalOverlay">&times;</button>
            </div>
            <form id="teamForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="teamGroupName">Group Name</label>
                        <input type="text" class="form-control-admin" id="teamGroupName" required>
                    </div>
                    <div class="form-group">
                        <label for="teamAcademicYear">Academic Year</label>
                        <input type="text" class="form-control-admin" id="teamAcademicYear" placeholder="e.g. 2025-2026">
                    </div>
                </div>
                <div class="form-group">
                    <label for="teamThesisTitle">Thesis Title</label>
                    <input type="text" class="form-control-admin" id="teamThesisTitle" required>
                </div>
                <div class="form-group">
                    <label for="teamAbstract">Abstract</label>
                    <textarea class="form-control-admin" id="teamAbstract" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="teamAdviser">Adviser</label>
                        <select class="form-select-admin" id="teamAdviser">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="teamDefenseDate">Defense Date</label>
                        <input type="date" class="form-control-admin" id="teamDefenseDate">
                    </div>
                </div>
                <div class="form-group">
                    <label>Members</label>
                    <div id="teamMembersList" class="member-checklist">
                        <p class="empty-state">Loading students…</p>
                    </div>
                </div>
                <p class="form-error" id="teamFormError"></p>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-admin" data-close-modal="teamModalOverlay">Cancel</button>
                <button type="button" class="btn-primary-admin" id="saveTeamBtn">Create Group</button>
            </div>
        </div>
    </div>

    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/js/Admin/adm-global.js"></script>
    <script src="<?= BASE_URL ?>/js/Admin/adm-records.js"></script>
</body>
</html>
