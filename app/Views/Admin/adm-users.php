<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../CSS/Admin/adm-links.css">
    <link rel="stylesheet" href="../../CSS/Admin/adm-users.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Create, edit, and manage student, adviser, and admin accounts.</p>
            </div>
            <div class="quick-actions">
                <button type="button" class="btn-quick-action" id="openCreateUserModal">
                    <img src="../../../assets/icons/grid-1x2-fill.svg" alt="" class="nav-icon">
                    Add Account
                </button>
            </div>
        </div>

        <div class="panel-card">
            <div class="toolbar">
                <div class="toolbar-filters" id="roleFilterChips">
                    <button type="button" class="filter-chip active" data-role="">All</button>
                    <button type="button" class="filter-chip" data-role="student">Students <span id="countStudent">(0)</span></button>
                    <button type="button" class="filter-chip" data-role="adviser">Advisers <span id="countAdviser">(0)</span></button>
                    <button type="button" class="filter-chip" data-role="admin">Admins <span id="countAdmin">(0)</span></button>
                </div>
                <div class="toolbar-filters">
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <input type="search" class="form-control-admin" id="userSearchInput" placeholder="Search by name or email…" style="max-width: 240px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="6" class="empty-state">Loading…</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-bar">
                <div class="rows-per-page">
                    Rows per page:
                    <select id="rowsPerPage">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <span id="paginationSummary">Showing 0 of 0</span>
                <div class="pagination-controls" id="paginationControls"></div>
            </div>
        </div>
    </div>

    <!-- Add / Edit Account Modal -->
    <div class="modal-overlay" id="userModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title" id="userModalTitle">Add Account</h3>
                <button type="button" class="modal-close" data-close-modal="userModalOverlay">&times;</button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId" value="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="userFirstname">First Name</label>
                        <input type="text" class="form-control-admin" id="userFirstname" required>
                    </div>
                    <div class="form-group">
                        <label for="userLastname">Last Name</label>
                        <input type="text" class="form-control-admin" id="userLastname" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input type="email" class="form-control-admin" id="userEmail" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="userRole">Role</label>
                        <select class="form-select-admin" id="userRole" required>
                            <option value="student">Student</option>
                            <option value="adviser">Adviser</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group" id="userStatusGroup" style="display:none;">
                        <label for="userStatus">Status</label>
                        <select class="form-select-admin" id="userStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="userPasswordGroup">
                    <label for="userPassword">Password</label>
                    <input type="text" class="form-control-admin" id="userPassword" placeholder="Temporary password for this account">
                </div>
                <p class="form-error" id="userFormError"></p>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-admin" data-close-modal="userModalOverlay">Cancel</button>
                <button type="button" class="btn-primary-admin" id="saveUserBtn">Save Account</button>
            </div>
        </div>
    </div>

    <!-- User Offcanvas (Bootstrap) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="userOffcanvas" aria-labelledby="userOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 id="userOffcanvasLabel"><span id="drawerUserName">User</span></h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex justify-content-end mb-2">
                <!-- message button removed for admin view -->
            </div>

            <div id="drawerKpis" class="kpi-row">
                <!-- KPI cards inserted here -->
            </div>

            <div id="drawerRoleDetails" class="mt-3">
                <!-- role-specific details inserted here -->
            </div>

            <h5 class="mt-3">Recent Activity</h5>
            <ul id="drawerRecentActivity" class="recent-activity">
                <li class="empty-state">No recent activity.</li>
            </ul>
        </div>
    </div>

    <style>
    .kpi-row { display:flex; gap:8px; flex-wrap:wrap; }
    .kpi-card { flex:1 1 120px; background:#f8f9fa; padding:10px; border-radius:6px; text-align:center; }
    .recent-activity { list-style:none; padding:0; margin:0; }
    .recent-activity li { padding:8px 0; border-bottom:1px solid #f1f1f1; }
    .link-button { background:none; border:0; color:#0d6efd; cursor:pointer; padding:0; font:inherit; }
    </style>

    <script src="../../../JS/bootstrap.bundle.min.js"></script>
    <script src="../../JS/Admin/adm-global.js"></script>
    <script src="../../JS/Admin/adm-users.js"></script>
</body>
</html>
