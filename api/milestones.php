<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $userId = $user['id'];

    // user group
    $g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
    $g->execute([$userId]);
    $gid = $g->fetchColumn();

    if (!$gid) {
        json(['items' => [], 'total' => 0]);
    }

    $stmt = $pdo->prepare(
        "SELECT m.id, m.group_id, m.name, m.description, m.due_date,
                m.progress, m.status, m.display_order, m.completed_at,
                m.created_at, m.updated_at
        FROM milestones m
        WHERE m.group_id = ?
        ORDER BY m.display_order ASC"
    );
    $stmt->execute([$gid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        $r['progress'] = (int) $r['progress'];
        $r['display_order'] = (int) $r['display_order'];
        $r['status_label'] = statusLabel($r['status']);
        $r['status_class'] = statusClass($r['status']);

        $r['due_date_formatted'] = $r['due_date'] ? date('M j, Y', strtotime($r['due_date'])) : null;

        $r['is_overdue'] = $r['due_date'] ? strtotime($r['due_date']) < time() && $r['status'] !== 'approved' : false;
    }
    unset($r);

    json([
        'items' => $rows,
        'total' => count($rows),
    ]);
}
