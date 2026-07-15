<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'coordinator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = trim($_POST['status'] ?? '');
$allowed = ['approved','rejected','pending','coordinator-created'];
if (!$id || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Verify that the team belongs to a course handled by this coordinator
    $q = $pdo->prepare("SELECT c.coordinator_id FROM teams t JOIN sections s ON t.section_id = s.id JOIN courses c ON s.course_id = c.id WHERE t.id = ? LIMIT 1");
    $q->execute([$id]);
    $row = $q->fetch();
    $userId = (int)($_SESSION['user']['id'] ?? 0);
    if (!$row || (int)$row['coordinator_id'] !== $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE teams SET approval_status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);

    echo json_encode(['success' => true, 'id' => $id, 'status' => $status]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
