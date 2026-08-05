<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-file-model.php";

use App\Models\Admin\FileModel;

$fileModel = new FileModel($conn);

$search       = isset($_GET['search']) ? trim($_GET['search']) : "";
$documentType = isset($_GET['documentType']) ? trim($_GET['documentType']) : "";
$sort         = isset($_GET['sort']) ? trim($_GET['sort']) : "uploaded_at";
$dir          = isset($_GET['dir']) ? trim($_GET['dir']) : "desc";
$page         = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage      = isset($_GET['perPage']) ? max(5, (int) $_GET['perPage']) : 10;
$offset       = ($page - 1) * $perPage;

$paginated = $fileModel->getFiltered($search, $documentType, $sort, $dir, $perPage, $offset);
$typeCounts = $fileModel->getDocumentTypeCounts();

echo json_encode([
    "files" => $paginated['files'],
    "total" => $paginated['total'],
    "page" => $page,
    "perPage" => $perPage,
    "typeCounts" => $typeCounts,
]);
