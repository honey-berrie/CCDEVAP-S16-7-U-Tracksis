<?php
// api/coordinator/assign-adviser.php
require_once '../db.php'; // your PDO $pdo connection

header('Content-Type: application/json');

session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'coordinator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$teamId    = filter_var($input['team_id'] ?? null, FILTER_VALIDATE_INT);
$adviserId = array_key_exists('adviser_id', $input) ? $input['adviser_id'] : null;
$adviserId = ($adviserId === null || $adviserId === '') ? null : filter_var($adviserId, FILTER_VALIDATE_INT);

if (!$teamId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid team_id']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock the team row and get its current adviser
    $stmt = $pdo->prepare("SELECT adviser_id FROM teams WHERE id = ? FOR UPDATE");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Team not found']);
        exit;
    }

    $oldAdviserId = $team['adviser_id'];

    // No-op if nothing actually changes
    if ($oldAdviserId == $adviserId) {
        $pdo->rollBack();
        echo json_encode(['success' => true, 'unchanged' => true]);
        exit;
    }

    // Validate the new adviser exists and is actually an adviser
    if ($adviserId !== null) {
        $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'adviser' AND is_active = 1 FOR UPDATE");
        $check->execute([$adviserId]);
        if (!$check->fetch()) {
            $pdo->rollBack();
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid or inactive adviser']);
            exit;
        }
    }

    // Decrement the outgoing adviser's load (never below 0)
    if ($oldAdviserId !== null) {
        $pdo->prepare(
            "UPDATE users SET adviser_thesis_load = GREATEST(adviser_thesis_load - 1, 0) WHERE id = ?"
        )->execute([$oldAdviserId]);
    }

    // Increment the incoming adviser's load
    if ($adviserId !== null) {
        $pdo->prepare(
            "UPDATE users SET adviser_thesis_load = adviser_thesis_load + 1 WHERE id = ?"
        )->execute([$adviserId]);
    }

    // Update the team itself
    $pdo->prepare(
        "UPDATE teams SET adviser_id = ?, approval_status = 'approved' WHERE id = ?"
    )->execute([$adviserId, $teamId]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}