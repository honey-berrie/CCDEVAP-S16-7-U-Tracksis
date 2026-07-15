<?php

require_once "session.php";
requireAdminApi();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$action = $_POST['action'] ?? "";
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "Missing thesis group id."]);
    exit;
}

if ($action === "archive") {
    
    $check = $conn->prepare("
        SELECT
            (SELECT COUNT(*) FROM milestones WHERE group_id = ?) total,
            (SELECT COUNT(*) FROM milestones WHERE group_id = ? AND status <> 'approved') incomplete
    ");
    $check->bind_param("ii", $id, $id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();

    if ((int) $row['total'] === 0 || (int) $row['incomplete'] > 0) {
        echo json_encode(["success" => false, "error" => "This group cannot be archived until every milestone is approved by its adviser."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE teams SET status='archived', archived_at=NOW() WHERE id=? AND status='active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => $stmt->affected_rows > 0]);
    exit;
}

if ($action === "restore") {
    $stmt = $conn->prepare("UPDATE teams SET status='active', archived_at=NULL WHERE id=? AND status='archived'");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => $stmt->affected_rows > 0]);
    exit;
}

echo json_encode(["success" => false, "error" => "Unknown action."]);
