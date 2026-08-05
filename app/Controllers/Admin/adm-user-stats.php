<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-user-model.php";
use App\Models\Admin\UserModel;

$userModel = new UserModel($conn);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user id']);
    exit;
}

$stmt = $conn->prepare("SELECT id, firstname, lastname, email, role, is_active, last_login_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// KPIs: submissions count, milestones completed for user's group(s), completion percent (aggregate)
$kpis = [
    'submissions' => 0,
    'milestonesCompleted' => 0,
    'completionPercent' => 0,
];

// Recent activity (limit 10)
$recent = [];

// Submissions count
$subsStmt = $conn->prepare("SELECT COUNT(*) c FROM submissions WHERE uploaded_by = ?");
$subsStmt->bind_param("i", $id);
$subsStmt->execute();
$kpis['submissions'] = (int) $subsStmt->get_result()->fetch_assoc()['c'];

// Milestones completed for groups where user is a member
$milStmt = $conn->prepare("SELECT COUNT(m.id) c FROM milestones m JOIN group_members gm ON gm.group_id = m.group_id WHERE gm.user_id = ? AND m.status = 'approved'");
$milStmt->bind_param("i", $id);
$milStmt->execute();
$kpis['milestonesCompleted'] = (int) $milStmt->get_result()->fetch_assoc()['c'];

// Simple completion percent: avg(progress) across milestones in user's groups
$compStmt = $conn->prepare("SELECT AVG(m.progress) avgp FROM milestones m JOIN group_members gm ON gm.group_id = m.group_id WHERE gm.user_id = ?");
$compStmt->bind_param("i", $id);
$compStmt->execute();
$avg = $compStmt->get_result()->fetch_assoc()['avgp'];
$kpis['completionPercent'] = $avg === null ? 0 : (int) round($avg);

// Recent activity from activities table and submissions
$actStmt = $conn->prepare("SELECT type, description, created_at FROM activities WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
$actStmt->bind_param("i", $id);
$actStmt->execute();
$res = $actStmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recent[] = ['message' => $row['description'], 'created_at' => $row['created_at']];
}

$subActStmt = $conn->prepare("SELECT title as description, uploaded_at FROM submissions WHERE uploaded_by = ? ORDER BY uploaded_at DESC LIMIT 6");
$subActStmt->bind_param("i", $id);
$subActStmt->execute();
$res2 = $subActStmt->get_result();
while ($row = $res2->fetch_assoc()) {
    $recent[] = ['message' => 'Uploaded: ' . $row['description'], 'created_at' => $row['uploaded_at']];
}

// sort recent by created_at desc and limit 10
usort($recent, function($a, $b){ return strcmp($b['created_at'], $a['created_at']); });
$recent = array_slice($recent, 0, 10);

// Role-specific additional info
$extra = [];
if ($user['role'] === 'student') {
    // find the student's active group (if any)
    $gStmt = $conn->prepare("SELECT t.id, t.group_name, t.defense_date, t.progress_status FROM teams t JOIN group_members gm ON gm.group_id = t.id WHERE gm.user_id = ? AND t.status = 'active' LIMIT 1");
    $gStmt->bind_param("i", $id);
    $gStmt->execute();
    $group = $gStmt->get_result()->fetch_assoc();

    if ($group) {
        $groupId = (int) $group['id'];
        // days until defense
        $daysUntil = null;
        if ($group['defense_date']) {
            $d1 = new DateTime($group['defense_date']);
            $d2 = new DateTime();
            $interval = $d2->diff($d1);
            $daysUntil = (int) $interval->format('%r%a');
        }

        // thesis progress: avg milestone progress
        $mpStmt = $conn->prepare("SELECT AVG(progress) avgp FROM milestones WHERE group_id = ?");
        $mpStmt->bind_param("i", $groupId);
        $mpStmt->execute();
        $avgp = $mpStmt->get_result()->fetch_assoc()['avgp'];
        $thesisProgress = $avgp === null ? 0 : (int) round($avgp);

        // milestones list
        $msStmt = $conn->prepare("SELECT id, name, status, progress, due_date, completed_at FROM milestones WHERE group_id = ? ORDER BY display_order ASC");
        $msStmt->bind_param("i", $groupId);
        $msStmt->execute();
        $msRes = $msStmt->get_result();
        $milestones = [];
        while ($r = $msRes->fetch_assoc()) {
            $milestones[] = [
                'id' => (int) $r['id'],
                'name' => $r['name'],
                'status' => $r['status'],
                'progress' => (int) $r['progress'],
                'due_date' => $r['due_date'],
                'completed_at' => $r['completed_at'],
            ];
        }

        $extra['student'] = [
            'group' => [ 'id' => $groupId, 'name' => $group['group_name'], 'defense_date' => $group['defense_date'], 'progress_status' => $group['progress_status'] ],
            'daysUntilDefense' => $daysUntil,
            'thesisProgress' => $thesisProgress,
            'milestones' => $milestones,
        ];
    } else {
        $extra['student'] = [ 'group' => null, 'daysUntilDefense' => null, 'thesisProgress' => 0, 'milestones' => [] ];
    }

} elseif ($user['role'] === 'adviser') {
    // adviser: assigned thesis groups
    $agStmt = $conn->prepare("SELECT id, group_name, progress_status, status, defense_date FROM teams WHERE adviser_id = ? ORDER BY created_at DESC");
    $agStmt->bind_param("i", $id);
    $agStmt->execute();
    $agRes = $agStmt->get_result();
    $assignedGroups = [];
    while ($r = $agRes->fetch_assoc()) {
        $assignedGroups[] = [ 'id' => (int)$r['id'], 'name' => $r['group_name'], 'progress_status' => $r['progress_status'], 'status' => $r['status'], 'defense_date' => $r['defense_date'] ];
    }

    // upcoming consultations where adviser is recipient
    $cStmt = $conn->prepare("SELECT id, topic, proposed_schedule, status, created_at FROM consultations WHERE recipient_id = ? ORDER BY proposed_schedule DESC LIMIT 10");
    $cStmt->bind_param("i", $id);
    $cStmt->execute();
    $cRes = $cStmt->get_result();
    $consultations = [];
    while ($r = $cRes->fetch_assoc()) {
        $consultations[] = [ 'id' => (int)$r['id'], 'topic' => $r['topic'], 'proposed_schedule' => $r['proposed_schedule'], 'status' => $r['status'], 'created_at' => $r['created_at'] ];
    }

    $extra['adviser'] = [ 'assignedGroups' => $assignedGroups, 'consultations' => $consultations ];
}

header('Content-Type: application/json');
echo json_encode([
    'user' => [
        'id' => (int) $user['id'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['email'],
        'role' => $user['role'],
        'isActive' => (bool) $user['is_active'],
        'lastLogin' => $user['last_login_at'],
    ],
    'kpis' => $kpis,
    'recent' => $recent,
    'extra' => $extra,
]);
