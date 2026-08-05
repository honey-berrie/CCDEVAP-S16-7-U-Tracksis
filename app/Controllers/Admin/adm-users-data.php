<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-user-model.php";

use App\Models\Admin\UserModel;

$userModel = new UserModel($conn);

$search  = isset($_GET['search']) ? trim($_GET['search']) : "";
$role    = isset($_GET['role']) ? trim($_GET['role']) : "";
$status  = isset($_GET['status']) ? trim($_GET['status']) : "";
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(5, (int) $_GET['perPage']) : 10;
$offset  = ($page - 1) * $perPage;

$paginated = $userModel->getPaginated($search, $role, $status, $perPage, $offset);
$roleCounts = $userModel->getRoleCounts();
$advisers = $userModel->getActiveAdvisersWithLoad();
$students = $userModel->getUnassignedStudents();

header("Content-Type: application/json");
echo json_encode([
    "users" => $paginated['users'],
    "total" => $paginated['total'],
    "page" => $page,
    "perPage" => $perPage,
    "roleCounts" => $roleCounts,
    "advisers" => $advisers,
    "unassignedStudents" => $students,
]);
