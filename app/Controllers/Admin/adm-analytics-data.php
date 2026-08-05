<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-analytics-model.php";

use App\Models\Admin\AnalyticsModel;

$analyticsModel = new AnalyticsModel($conn);

$groupId   = isset($_GET['group']) ? (int) $_GET['group'] : 0;
$adviserId = isset($_GET['adviser']) ? (int) $_GET['adviser'] : 0;
$status    = isset($_GET['status']) ? trim($_GET['status']) : "";

$response = [];
$response['summary'] = $analyticsModel->getSummary($groupId, $adviserId, $status);
$response['statusBreakdown'] = $analyticsModel->getStatusBreakdown($groupId, $adviserId, $status);
$response['submissionsOverTime'] = $analyticsModel->getSubmissionsOverTime($groupId, $adviserId, $status);
$response['groupProgress'] = $analyticsModel->getGroupProgress($groupId, $adviserId);
$response['filters'] = [
    "groups" => $analyticsModel->getFilterGroups(),
    "advisers" => $analyticsModel->getFilterAdvisers(),
];

header("Content-Type: application/json");
echo json_encode($response);
