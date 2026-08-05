<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-record-model.php";

use App\Models\Admin\RecordModel;

$recordModel = new RecordModel($conn);

if (isset($_GET['detail'])) {
    $teamId = (int) $_GET['detail'];

    $detail = $recordModel->getTeamDetail($teamId);

    if (!$detail) {
        http_response_code(404);
        echo json_encode(["error" => "Thesis group not found."]);
        exit;
    }

    echo json_encode($detail);
    exit;
}

/* --- List mode ------------------------------------------------------------- */

$search  = isset($_GET['search']) ? trim($_GET['search']) : "";
$adviser = isset($_GET['adviser']) ? (int) $_GET['adviser'] : 0;
$status  = isset($_GET['status']) ? trim($_GET['status']) : "";
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(3, (int) $_GET['perPage']) : 9;
$offset  = ($page - 1) * $perPage;

$records = $recordModel->getFiltered($search, $adviser);

if ($status !== "") {
    $records = array_values(array_filter($records, function ($record) use ($status) {
        return $record['status'] === $status;
    }));
}

$total = count($records);
$paged = array_slice($records, $offset, $perPage);

$adviserList = $recordModel->getAdviserList();

echo json_encode([
    "records" => $paged,
    "total" => $total,
    "page" => $page,
    "perPage" => $perPage,
    "advisers" => $adviserList,
]);
