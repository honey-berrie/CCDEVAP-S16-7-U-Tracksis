<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-dashboard-model.php";

use App\Models\Admin\DashboardModel;

$dashboardModel = new DashboardModel($conn);

$response = [];

$admin = getCurrentAdmin($conn);
$response['currentUser'] = [
    "firstname" => $admin['firstname'],
    "lastname"  => $admin['lastname'],
];

$response['stats'] = $dashboardModel->getStats();
$response['submissionData'] = $dashboardModel->getSubmissionChartData();
$response['progressData'] = $dashboardModel->getProgressChartData();
$response['adviserData'] = $dashboardModel->getAdviserWorkload();
$response['pendingApprovals'] = $dashboardModel->getPendingApprovals();
$response['recentActivity'] = $dashboardModel->getRecentActivity();
$response['recentSubmissions'] = $dashboardModel->getRecentSubmissions();

header("Content-Type: application/json");
echo json_encode($response);
