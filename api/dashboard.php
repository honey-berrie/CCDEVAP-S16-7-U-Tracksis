<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    error('Method not allowed', 405);
}

$userId = $user['id'];

// user group
$g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
$g->execute([$userId]);
$gid = $g->fetchColumn();

if (!$gid) {
    json([
        'milestones' => [],
        'overall_progress' => 0,
        'done_count' => 0,
        'total_count' => 0,
        'next_deadline_days' => null,
        'defense_date' => null,
        'feedback' => null,
    ]);
}


$stmt = $pdo->prepare(
    "SELECT id, name, description, due_date, progress, status, display_order
     FROM milestones
     WHERE group_id = ?
     ORDER BY display_order ASC"
);
$stmt->execute([$gid]);
$milestones = $stmt->fetchAll();

$totalCount = count($milestones);
$doneCount = 0;
$progressSum = 0;
$nextDeadlineDays = null;

foreach ($milestones as &$m) {
    $m['progress'] = (int) $m['progress'];
    $m['status_label'] = statusLabel($m['status']);
    $m['status_class'] = statusClass($m['status']);
    $m['due_date_formatted'] = $m['due_date'] ? date('M j, Y', strtotime($m['due_date'])) : null;

    $progressSum += $m['progress'];

    if ($m['status'] === 'approved') {
        $doneCount++;
    }

    // find the next upcoming due date
    if ($m['due_date'] && $m['status'] !== 'approved') {
        
        $dueTs = strtotime($m['due_date']);
        $daysLeft = (int) ceil(($dueTs - time()) / 86400);

        if ($nextDeadlineDays === null || ($daysLeft >= 0 && $daysLeft < $nextDeadlineDays)) {
            $nextDeadlineDays = $daysLeft;
        } elseif ($nextDeadlineDays < 0 && $daysLeft > $nextDeadlineDays) {
            // if all are overdue, pick the least overdue
            $nextDeadlineDays = $daysLeft;
        }

    }
}
unset($m);

$overallProgress = $totalCount > 0 ? round($progressSum / $totalCount) : 0;

// group def date
$defStmt = $pdo->prepare("SELECT defense_date FROM teams WHERE id = ?");
$defStmt->execute([$gid]);
$defenseDate = $defStmt->fetchColumn();

// days to def
$daysToDefense = null;
$defenseDateFormatted = null;
if ($defenseDate) {
    $defTs = strtotime($defenseDate);
    $daysToDefense = (int) ceil(($defTs - time()) / 86400);
    $defenseDateFormatted = date('M j, Y', $defTs);
}

// get the latest adviser feedback
$fbStmt = $pdo->prepare(
    "SELECT f.id, f.message, f.created_at, f.author_role,
            u.firstname, u.lastname, u.role
     FROM feedback f
     JOIN users u ON u.id = f.given_by
     WHERE f.group_id = ? AND (f.author_role = 'adviser' OR u.role = 'adviser')
     ORDER BY f.created_at DESC
     LIMIT 1"
);
$fbStmt->execute([$gid]);
$fb = $fbStmt->fetch();

$latestFeedback = null;
if ($fb) {
    $latestFeedback = [
        'author' => trim(($fb['firstname'] ?? '') . ' ' . ($fb['lastname'] ?? '')),
        'initials' => initialsOf($fb['firstname'] ?? '', $fb['lastname'] ?? ''),
        'author_role' => $fb['author_role'],
        'message' => $fb['message'],
        'time_ago' => timeAgo(strtotime($fb['created_at'])),
        'date_formatted' => date('M j, Y', strtotime($fb['created_at'])),
    ];
}

json([
    'milestones' => $milestones,
    'overall_progress' => $overallProgress,
    'done_count' => $doneCount,
    'total_count' => $totalCount,
    'next_deadline_days' => $nextDeadlineDays,
    'days_to_defense' => $daysToDefense,
    'defense_date' => $defenseDateFormatted,
    'feedback' => $latestFeedback,
]);

