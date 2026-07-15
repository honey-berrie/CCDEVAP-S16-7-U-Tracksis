<?php
require_once __DIR__ . "/../configuration/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../admincss/adm-links.css">
    <link rel="stylesheet" href="../admincss/adm-analytics.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Analytics</h1>
                <p class="page-subtitle">System-wide submission and progress trends across all thesis groups.</p>
            </div>
        </div>

        <div class="panel-card">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <select class="filter-select" id="filterGroup">
                        <option value="">All Groups</option>
                    </select>
                    <select class="filter-select" id="filterAdviser">
                        <option value="">All Advisers</option>
                    </select>
                    <select class="filter-select" id="filterStatus">
                        <option value="">All Statuses</option>
                        <option value="in-review">In Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="revision-requested">Revision Requested</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">On-Time Rate</span>
                <span class="stat-value" id="statOnTimeRate">--%</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Groups</span>
                <span class="stat-value" id="statActiveGroups">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Archived Theses</span>
                <span class="stat-value" id="statArchived">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Avg. Progress</span>
                <span class="stat-value" id="statAvgProgress">--%</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h2 class="chart-title">Submissions Over Time</h2>
                    <hr class="chart-divider">
                    <canvas id="submissionsOverTimeChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <h2 class="chart-title">Submission Status Breakdown</h2>
                    <hr class="chart-divider">
                    <canvas id="statusBreakdownChart"></canvas>
                </div>
            </div>
            <div class="col-12">
                <div class="chart-card">
                    <h2 class="chart-title">Group Progress Comparison</h2>
                    <hr class="chart-divider">
                    <canvas id="groupProgressChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../adminjs/adm-global.js"></script>
    <script src="../adminjs/adm-analytics.js"></script>
</body>
</html>
