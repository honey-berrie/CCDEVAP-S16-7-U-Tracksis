<?php

require_once "session.php";
requireAdminApi();

$search  = isset($_GET['search']) ? trim($_GET['search']) : "";
$role    = isset($_GET['role']) ? trim($_GET['role']) : "";
$status  = isset($_GET['status']) ? trim($_GET['status']) : "";
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(5, (int) $_GET['perPage']) : 10;
$offset  = ($page - 1) * $perPage;

$allowedRoles = ['student', 'adviser', 'coordinator', 'admin'];

$where = [];
$params = [];
$types = "";

if ($search !== "") {
    $where[] = "(firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

if ($role !== "" && in_array($role, $allowedRoles, true)) {
    $where[] = "role = ?";
    $params[] = $role;
    $types .= "s";
}

if ($status === "active") {
    $where[] = "is_active = 1";
} elseif ($status === "inactive") {
    $where[] = "is_active = 0";
}

$whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

/* Total count for pagination */
$countSql = "SELECT COUNT(*) c FROM users $whereSql";
$countStmt = $conn->prepare($countSql);
if ($types !== "") {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = (int) $countStmt->get_result()->fetch_assoc()['c'];

/* Page of results */
$listSql = "
    SELECT id, role, firstname, lastname, email, is_active, last_login_at, created_at
    FROM users
    $whereSql
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
";
$listStmt = $conn->prepare($listSql);
$listTypes = $types . "ii";
$listParams = array_merge($params, [$perPage, $offset]);
$listStmt->bind_param($listTypes, ...$listParams);
$listStmt->execute();
$result = $listStmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        "id" => (int) $row['id'],
        "role" => $row['role'],
        "firstname" => $row['firstname'],
        "lastname" => $row['lastname'],
        "email" => $row['email'],
        "isActive" => (bool) $row['is_active'],
        "lastLogin" => $row['last_login_at'],
        "createdAt" => $row['created_at'],
    ];
}

/* Counts per role, for the filter chips */
$roleCounts = ["student" => 0, "adviser" => 0, "coordinator" => 0, "admin" => 0];
$rcResult = $conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role");
while ($row = $rcResult->fetch_assoc()) {
    $roleCounts[$row['role']] = (int) $row['c'];
}

/* Lookup lists for the Create Thesis Group modal */
$advisers = [];
$adviserResult = $conn->query("
    SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) name, COUNT(t.id) AS `load`
    FROM users u
    LEFT JOIN teams t ON t.adviser_id = u.id AND t.status = 'active'
    WHERE u.role = 'adviser' AND u.is_active = 1
    GROUP BY u.id
    ORDER BY u.firstname
");
while ($row = $adviserResult->fetch_assoc()) {
    $advisers[] = [
        "id" => (int) $row['id'],
        "name" => $row['name'],
        "load" => (int) $row['load'],
    ];
}

$students = [];
$studentResult = $conn->query("
    SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) name
    FROM users u
    WHERE u.role = 'student' AND u.is_active = 1
      AND u.id NOT IN (SELECT user_id FROM group_members)
    ORDER BY u.firstname
");
while ($row = $studentResult->fetch_assoc()) {
    $students[] = ["id" => (int) $row['id'], "name" => $row['name']];
}

header("Content-Type: application/json");
echo json_encode([
    "users" => $users,
    "total" => $total,
    "page" => $page,
    "perPage" => $perPage,
    "roleCounts" => $roleCounts,
    "advisers" => $advisers,
    "unassignedStudents" => $students,
]);
