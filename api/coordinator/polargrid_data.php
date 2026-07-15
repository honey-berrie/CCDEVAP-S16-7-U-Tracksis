<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'coordinator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid session user']);
    exit;
}

try {
    // Count thesis groups per adviser for courses handled by this coordinator
    $sql = "SELECT u.id AS adviser_id, CONCAT(u.firstname, ' ', u.lastname) AS name, COUNT(t.id) AS thesis_count
            FROM teams t
            JOIN users u ON t.adviser_id = u.id
            JOIN sections s ON t.section_id = s.id
            JOIN courses c ON s.course_id = c.id
            WHERE c.coordinator_id = :coord_id
            GROUP BY u.id
            ORDER BY thesis_count DESC, name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':coord_id' => $userId]);
    $rows = $stmt->fetchAll();

    $advisers = [];
    $thesisData = [];
    foreach ($rows as $r) {
        $advisers[] = $r['name'];
        $thesisData[] = (int)$r['thesis_count'];
    }

    echo json_encode(['success' => true, 'advisers' => $advisers, 'thesisData' => $thesisData]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
