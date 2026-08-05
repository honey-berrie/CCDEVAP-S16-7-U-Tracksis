<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-archive-model.php";

use App\Models\Admin\ArchiveModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$archiveModel = new ArchiveModel($conn);

$action = $_POST['action'] ?? "";
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "Missing thesis group id."]);
    exit;
}

if ($action === "archive") {

    if (!$archiveModel->canArchive($id)) {
        echo json_encode(["success" => false, "error" => "This group cannot be archived until every milestone is approved by its adviser."]);
        exit;
    }

    echo json_encode(["success" => $archiveModel->archive($id)]);
    exit;
}

if ($action === "restore") {
    echo json_encode(["success" => $archiveModel->restore($id)]);
    exit;
}

echo json_encode(["success" => false, "error" => "Unknown action."]);
