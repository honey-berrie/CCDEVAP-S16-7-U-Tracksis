<?php

require_once "session.php";

header("Content-Type: application/json");

if (isset($_GET['detail'])) {
    $teamId = (int) $_GET['detail'];

    $teamStmt = $conn->prepare("
        SELECT t.id, t.group_name, t.thesis_title, t.abstract, t.academic_year,
               t.defense_date, t.status, t.archived_at,
               CONCAT(u.firstname, ' ', u.lastname) adviser_name, u.email adviser_email
        FROM teams t
        LEFT JOIN users u ON u.id = t.adviser_id
        WHERE t.id = ?
    ");
    $teamStmt->bind_param("i", $teamId);
    $teamStmt->execute();
    $team = $teamStmt->get_result()->fetch_assoc();

    if (!$team) {
        http_response_code(404);
        echo json_encode(["error" => "Thesis group not found."]);
        exit;
    }

    $membersStmt = $conn->prepare("
        SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) name, gm.member_role
        FROM group_members gm JOIN users u ON u.id = gm.user_id
        WHERE gm.group_id = ? ORDER BY gm.member_role DESC, u.firstname
    ");
    $membersStmt->bind_param("i", $teamId);
    $membersStmt->execute();
    $members = [];
    $mr = $membersStmt->get_result();
    while ($row = $mr->fetch_assoc()) {
        $members[] = ["id" => (int) $row['id'], "name" => $row['name'], "role" => $row['member_role']];
    }

    $milestoneStmt = $conn->prepare("
        SELECT id, name, due_date, progress, status, completed_at
        FROM milestones WHERE group_id = ? ORDER BY display_order ASC, due_date ASC
    ");
    $milestoneStmt->bind_param("i", $teamId);
    $milestoneStmt->execute();
    $milestones = [];
    $mr = $milestoneStmt->get_result();
    while ($row = $mr->fetch_assoc()) {
        $milestones[] = [
            "id" => (int) $row['id'], "name" => $row['name'], "dueDate" => $row['due_date'],
            "progress" => (int) $row['progress'], "status" => $row['status'], "completedAt" => $row['completed_at'],
        ];
    }

    $subStmt = $conn->prepare("
        SELECT s.id, s.document_type, s.title, s.file_name, s.status, s.uploaded_at,
               CONCAT(u.firstname, ' ', u.lastname) uploaded_by
        FROM submissions s LEFT JOIN users u ON u.id = s.uploaded_by
        WHERE s.group_id = ? ORDER BY s.uploaded_at DESC
    ");
    $subStmt->bind_param("i", $teamId);
    $subStmt->execute();
    $submissions = [];
    $mr = $subStmt->get_result();
    while ($row = $mr->fetch_assoc()) {
        $submissions[] = [
            "id" => (int) $row['id'], "documentType" => $row['document_type'], "title" => $row['title'],
            "fileName" => $row['file_name'], "status" => $row['status'],
            "uploadedAt" => $row['uploaded_at'], "uploadedBy" => $row['uploaded_by'],
        ];
    }

    echo json_encode([
        "id" => (int) $team['id'], "groupName" => $team['group_name'], "thesisTitle" => $team['thesis_title'],
        "abstract" => $team['abstract'], "academicYear" => $team['academic_year'],
        "defenseDate" => $team['defense_date'], "status" => $team['status'], "archivedAt" => $team['archived_at'],
        "adviser" => $team['adviser_name'] ?: "Unassigned", "adviserEmail" => $team['adviser_email'],
        "members" => $members, "milestones" => $milestones, "submissions" => $submissions,
    ]);
    exit;
}

$section = $_GET['section'] ?? 'archived';

if ($section === 'ready') {
    $sql = "
        SELECT t.id, t.group_name, t.thesis_title, t.defense_date,
               CONCAT(u.firstname, ' ', u.lastname) adviser_name
        FROM teams t
        LEFT JOIN users u ON u.id = t.adviser_id
        WHERE t.status = 'active'
          AND EXISTS (SELECT 1 FROM milestones m WHERE m.group_id = t.id)
          AND NOT EXISTS (SELECT 1 FROM milestones m WHERE m.group_id = t.id AND m.status <> 'approved')
        ORDER BY t.updated_at DESC
    ";
    $result = $conn->query($sql);
    $ready = [];
    while ($row = $result->fetch_assoc()) {
        $ready[] = [
            "id" => (int) $row['id'], "groupName" => $row['group_name'], "thesisTitle" => $row['thesis_title'],
            "adviser" => $row['adviser_name'] ?: "Unassigned", "defenseDate" => $row['defense_date'],
        ];
    }
    echo json_encode(["ready" => $ready]);
    exit;
}

/* section === 'archived' */
$search  = isset($_GET['search']) ? trim($_GET['search']) : "";
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(3, (int) $_GET['perPage']) : 9;
$offset  = ($page - 1) * $perPage;

$where = ["t.status = 'archived'"];
$params = [];
$types = "";
if ($search !== "") {
    $where[] = "(t.group_name LIKE ? OR t.thesis_title LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like; $params[] = $like;
    $types .= "ss";
}
$whereSql = "WHERE " . implode(" AND ", $where);

$countStmt = $conn->prepare("SELECT COUNT(*) c FROM teams t $whereSql");
if ($types !== "") $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = (int) $countStmt->get_result()->fetch_assoc()['c'];

$listSql = "
    SELECT t.id, t.group_name, t.thesis_title, t.archived_at, t.defense_date,
           CONCAT(u.firstname, ' ', u.lastname) adviser_name
    FROM teams t
    LEFT JOIN users u ON u.id = t.adviser_id
    $whereSql
    ORDER BY t.archived_at DESC
    LIMIT ? OFFSET ?
";
$listStmt = $conn->prepare($listSql);
$listTypes = $types . "ii";
$listParams = array_merge($params, [$perPage, $offset]);
$listStmt->bind_param($listTypes, ...$listParams);
$listStmt->execute();
$result = $listStmt->get_result();
$archived = [];
while ($row = $result->fetch_assoc()) {
    $archived[] = [
        "id" => (int) $row['id'], "groupName" => $row['group_name'], "thesisTitle" => $row['thesis_title'],
        "adviser" => $row['adviser_name'] ?: "Unassigned", "archivedAt" => $row['archived_at'],
        "defenseDate" => $row['defense_date'],
    ];
}

echo json_encode(["archived" => $archived, "total" => $total, "page" => $page, "perPage" => $perPage]);
