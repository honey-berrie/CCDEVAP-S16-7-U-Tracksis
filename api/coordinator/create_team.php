<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Require logged in coordinator
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'coordinator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Accept form-data or urlencoded
$group_name = trim($_POST['group_name'] ?? '');
$thesis_title = trim($_POST['thesis_title'] ?? '');
$section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
$abstract = trim($_POST['abstract'] ?? '');
$adviser_id = !empty($_POST['adviser_id']) ? (int)$_POST['adviser_id'] : null;
$academic_year = trim($_POST['academic_year'] ?? '');
$defense_date_raw = trim($_POST['defense_date'] ?? '');

// Parse defense date string into MySQL DATE (YYYY-MM-DD) if possible
$defense_date = null;
if ($defense_date_raw !== '') {
    // Try to parse using DateTime; allow many string formats
    try {
        $d = new DateTime($defense_date_raw);
        $defense_date = $d->format('Y-m-d');
    } catch (Exception $e) {
        // leave as null on parse failure
        $defense_date = null;
    }
}

if ($group_name === '' || $section_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $sql = "INSERT INTO teams (group_name, thesis_title, section_id, abstract, adviser_id, approval_status, progress_status, defense_date, submission_date, academic_year)
            VALUES (:group_name, :thesis_title, :section_id, :abstract, :adviser_id, :approval_status, :progress_status, :defense_date, :submission_date, :academic_year)";
    $stmt = $pdo->prepare($sql);
    $params = [
        ':group_name' => $group_name,
        ':thesis_title' => $thesis_title ?: null,
        ':section_id' => $section_id,
        ':abstract' => $abstract ?: null,
        ':adviser_id' => $adviser_id ?: null,
        ':approval_status' => 'pending',
        ':progress_status' => 'on track',
        ':defense_date' => $defense_date,
        ':submission_date' => date('Y-m-d H:i:s'),
        ':academic_year' => $academic_year ?: null,
    ];
    $stmt->execute($params);
    $id = (int)$pdo->lastInsertId();
    // fetch the inserted team with joins for client-side rendering
    $q = $pdo->prepare("SELECT t.*, s.section_code, c.course_code, CONCAT(u.firstname, ' ', u.lastname) AS adviser_name
                        FROM teams t
                        LEFT JOIN sections s ON t.section_id = s.id
                        LEFT JOIN courses c ON s.course_id = c.id
                        LEFT JOIN users u ON t.adviser_id = u.id
                        WHERE t.id = ? LIMIT 1");
    $q->execute([$id]);
    $team = $q->fetch();
    echo json_encode(['success' => true, 'id' => $id, 'team' => $team]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
