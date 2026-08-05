<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-feedback-model.php";

use App\Models\Admin\FeedbackModel;

$feedbackModel = new FeedbackModel($conn);

$search  = isset($_GET['search']) ? trim($_GET['search']) : "";
$sort    = isset($_GET['sort']) ? trim($_GET['sort']) : "created_at";
$dir     = isset($_GET['dir']) ? trim($_GET['dir']) : "desc";
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(5, (int) $_GET['perPage']) : 10;
$offset  = ($page - 1) * $perPage;

$paginated = $feedbackModel->getFiltered($search, $sort, $dir, $perPage, $offset);

echo json_encode([
    "feedback" => $paginated['feedback'],
    "total" => $paginated['total'],
    "page" => $page,
    "perPage" => $perPage,
]);
