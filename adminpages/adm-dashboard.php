<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../admincss/adm-links.css">
    <link rel="stylesheet" href="../admincss/adm-dashboard.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div class="time-Header">
                <span id="currentDay"></span>
                <span>,</span>
                <span id="currentDate"></span>
            </div>
            <div class="greeting-Header">
                <div id="partDay"></div>
                <div id="namePerson"></div>
            </div>
            <div class="quick-actions" id="quickActions">
                <a href="adm-announcements.php" class="btn-quick-action">
                    <img src="../assets/icons/megaphone-fill.svg" alt="" class="nav-icon">
                    New Announcement
                </a>
                <a href="adm-records.php?action=create-group" class="btn-quick-action">
                    <img src="../assets/icons/people-fill.svg" alt="" class="nav-icon">
                    Create Thesis Group
                </a>
            </div>
        </div>

        <div class="stat-grid" id="statGrid">
            <div class="stat-card">
                <span class="stat-label">Students</span>
                <span class="stat-value" id="statStudents">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Advisers</span>
                <span class="stat-value" id="statAdvisers">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Groups</span>
                <span class="stat-value" id="statActiveGroups">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Archived Theses</span>
                <span class="stat-value" id="statApproved">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Ready to Archive</span>
                <span class="stat-value" id="statPendingArchive">--</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Avg. Progress</span>
                <span class="stat-value" id="statAvgProgress">--%</span>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h2 class="chart-title">Submission Statistics</h2>
                    <hr class="chart-divider">
                    <canvas id="submissionStatsChart"></canvas>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="chart-card">
                    <h2 class="chart-title">Progress Statistics</h2>
                    <hr class="chart-divider">
                    <canvas id="progressStatsChart"></canvas>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-card">
                    <h2 class="chart-title">Adviser Workload Distribution</h2>
                    <hr class="chart-divider">
                    <canvas id="adviserWorkloadChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pending Approvals / Recent Activity -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Pending Approvals</h2>
                        <a href="adm-records.php" class="panel-link">View all</a>
                    </div>
                    <p class="panel-subtitle">Thesis groups awaiting admin review</p>
                    <div id="pendingApprovalsList" class="panel-list">
                        <p class="empty-state">Loading…</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Recent Activity</h2>
                    </div>
                    <div id="recentActivityList" class="panel-list">
                        <p class="empty-state">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../adminjs/adm-chart.js"></script>
    <script src="../adminjs/adm-global.js"></script>
    <script src="../adminjs/adm-dashboard.js"></script>
</body>
</html>
