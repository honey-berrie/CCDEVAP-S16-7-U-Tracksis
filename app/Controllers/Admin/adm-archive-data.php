<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-archive-model.php";

use App\Models\Admin\ArchiveModel;

$archiveModel = new ArchiveModel($conn);

if (isset($_GET['detail'])) {
    $teamId = (int) $_GET['detail'];

    $detail = $archiveModel->getTeamDetail($teamId);

    if (!$detail) {
        http_response_code(404);
        echo json_encode(["error" => "Thesis group not found."]);
        exit;
    }

    echo json_encode($detail);
    exit;
}

$section = $_GET['section'] ?? 'archived';

if ($section === 'ready') {
    echo json_encode(["ready" => $archiveModel->getReadyToArchive()]);
    exit;
}

/* section === 'archived' */
$search  = isset($_GET['search']) ? trim($_GET['search']) : "";
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(3, (int) $_GET['perPage']) : 9;
$offset  = ($page - 1) * $perPage;

$paginated = $archiveModel->getArchivedPaginated($search, $perPage, $offset);

echo json_encode([
    "archived" => $paginated['archived'],
    "total" => $paginated['total'],
    "page" => $page,
    "perPage" => $perPage,
]);
