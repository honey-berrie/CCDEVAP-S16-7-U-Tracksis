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

    // Get user's group
    $g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
    $g->execute([$userId]);
    $gid = $g->fetchColumn();

    $sql = "SELECT a.id, a.sender_id, a.group_id, a.is_broadcast,
                a.title, a.message, a.created_at,
                u.firstname, u.lastname, u.role AS sender_role,
                (ar.announcement_id IS NOT NULL) AS is_read
            FROM announcements a
            JOIN users u ON u.id = a.sender_id
            LEFT JOIN announcement_reads ar ON ar.announcement_id = a.id AND ar.user_id = $userId
            WHERE (a.is_broadcast = 1 OR a.group_id = ?)
            ORDER BY a.created_at DESC";


    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        $r['sender_name'] = trim(($r['firstname'] ?? '') . ' ' . ($r['lastname'] ?? ''));
        $r['sender_initials'] = initialsOf($r['firstname'] ?? '', $r['lastname'] ?? '');
        $r['time_ago'] = timeAgo(strtotime($r['created_at']));
        $r['is_read'] = (bool) $r['is_read'];
        unset($r['firstname'], $r['lastname']);
    }
    unset($r);

    json([
        'items' => $rows,
        'total' => count($rows),
    ]);
}

// mark as read in db
if ($method === 'POST') {
    $action = $_GET['action'] ?? null;
    $announcementId = !empty($_GET['id']) ? (int) $_GET['id'] : null;

    if ($action === 'read' && $announcementId) {
        $ins = $pdo->prepare(
            "INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)"
        );
        $ins->execute([$announcementId, $user['id']]);
        json(['message' => 'Marked as read']);
    }

    error('Invalid action', 422);
}
